import {formatNumber, sortGroupAllocationRowsByCouncillorCountDescending} from "./calculate_party_allocation";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import type {
    Committee,
    CommitteeDistributionAssignmentStep,
    CommitteeDistributionState,
    GroupAllocationRow,
    PartyAllocationResult,
} from "./types";

interface GroupFloorCalculation {
    group_name: string;
    final_seats: number;
    cells: {
        committee_index: number;
        committee_name: string;
        committee_seat_count: number;
        raw_entitlement: number;
        floored_seats: number;
        fractional_part: number;
    }[];
    remainder_seats: number;
}

function buildEmptyMatrix(
    groupNames: string[],
    committeeNames: string[]
): Record<string, Record<string, number>> {
    const matrix: Record<string, Record<string, number>> = {};

    for (const groupName of groupNames) {
        matrix[groupName] = {};
        for (const committeeName of committeeNames) {
            matrix[groupName][committeeName] = 0;
        }
    }

    return matrix;
}

function columnTotalForMatrix(
    matrix: Record<string, Record<string, number>>,
    committeeName: string
): number {
    let total = 0;

    for (const groupName of Object.keys(matrix)) {
        total += matrix[groupName][committeeName] ?? 0;
    }

    return total;
}

function getRawEntitlementForGroupOnCommittee(
    finalSeatsForGroup: number,
    committeeSeatCount: number,
    totalCommitteeSeats: number
): number {
    return (finalSeatsForGroup * committeeSeatCount) / totalCommitteeSeats;
}

/** At most one seat above the floor on a committee for a group (ceil of proportional share). */
function getMaximumSeatsForGroupOnCommittee(
    finalSeatsForGroup: number,
    committeeSeatCount: number,
    totalCommitteeSeats: number
): number {
    const rawEntitlement = getRawEntitlementForGroupOnCommittee(
        finalSeatsForGroup,
        committeeSeatCount,
        totalCommitteeSeats
    );

    return Math.ceil(rawEntitlement);
}

function canGroupReceiveAnotherSeatOnCommittee(
    matrix: Record<string, Record<string, number>>,
    groupName: string,
    committee: Committee,
    finalSeatsForGroup: number,
    totalCommitteeSeats: number
): boolean {
    const columnTotal = columnTotalForMatrix(matrix, committee.name);

    if (columnTotal >= committee.seat_count) {
        return false;
    }

    const currentSeats = matrix[groupName]?.[committee.name] ?? 0;
    const maximumSeats = getMaximumSeatsForGroupOnCommittee(
        finalSeatsForGroup,
        committee.seat_count,
        totalCommitteeSeats
    );

    return currentSeats < maximumSeats;
}

function buildGroupFloorCalculations(
    committees: Committee[],
    allocationRows: GroupAllocationRow[],
    totalCommitteeSeats: number
): GroupFloorCalculation[] {
    const rowsWithSeats = allocationRows.filter((row) => row.final_seats > 0);
    const sortedRows = sortGroupAllocationRowsByCouncillorCountDescending(rowsWithSeats);
    const groupCalculations: GroupFloorCalculation[] = [];

    for (const row of sortedRows) {
        const cells = committees.map((committee, committeeIndex) => {
            const rawEntitlement =
                (row.final_seats * committee.seat_count) / totalCommitteeSeats;
            const flooredSeats = Math.floor(rawEntitlement);

            return {
                committee_index: committeeIndex,
                committee_name: committee.name,
                committee_seat_count: committee.seat_count,
                raw_entitlement: rawEntitlement,
                floored_seats: flooredSeats,
                fractional_part: rawEntitlement - flooredSeats,
            };
        });

        const flooredTotal = cells.reduce((total, cell) => total + cell.floored_seats, 0);

        groupCalculations.push({
            group_name: row.group_name,
            final_seats: row.final_seats,
            cells,
            remainder_seats: row.final_seats - flooredTotal,
        });
    }

    return groupCalculations;
}

interface GlobalRemainderCandidate {
    group_name: string;
    committee_index: number;
    committee_name: string;
    unallocated_share: number;
}

function pickGlobalLargestRemainderCandidate(
    committees: Committee[],
    groupNames: string[],
    groupFinalSeatsByName: Record<string, number>,
    matrix: Record<string, Record<string, number>>,
    totalCommitteeSeats: number
): GlobalRemainderCandidate | null {
    let bestCandidate: GlobalRemainderCandidate | null = null;

    for (const groupName of groupNames) {
        const finalSeatsForGroup = groupFinalSeatsByName[groupName] ?? 0;
        const rowTotal = rowTotalForGroup(matrix, groupName);

        if (rowTotal >= finalSeatsForGroup) {
            continue;
        }

        for (let committeeIndex = 0; committeeIndex < committees.length; committeeIndex += 1) {
            const committee = committees[committeeIndex];

            if (
                !canGroupReceiveAnotherSeatOnCommittee(
                    matrix,
                    groupName,
                    committee,
                    finalSeatsForGroup,
                    totalCommitteeSeats
                )
            ) {
                continue;
            }

            const rawEntitlement = getRawEntitlementForGroupOnCommittee(
                finalSeatsForGroup,
                committee.seat_count,
                totalCommitteeSeats
            );
            const currentSeats = matrix[groupName]?.[committee.name] ?? 0;
            const unallocatedShare = rawEntitlement - currentSeats;

            if (unallocatedShare <= 0) {
                continue;
            }
            const candidate: GlobalRemainderCandidate = {
                group_name: groupName,
                committee_index: committeeIndex,
                committee_name: committee.name,
                unallocated_share: unallocatedShare,
            };

            if (bestCandidate === null) {
                bestCandidate = candidate;
                continue;
            }

            if (candidate.unallocated_share > bestCandidate.unallocated_share) {
                bestCandidate = candidate;
                continue;
            }

            if (candidate.unallocated_share !== bestCandidate.unallocated_share) {
                continue;
            }

            if (candidate.group_name.localeCompare(bestCandidate.group_name) < 0) {
                bestCandidate = candidate;
                continue;
            }

            if (
                candidate.group_name === bestCandidate.group_name &&
                candidate.committee_index < bestCandidate.committee_index
            ) {
                bestCandidate = candidate;
            }
        }
    }

    return bestCandidate;
}

/**
 * Order extra-seat steps by global largest remainder (same principle as LGA Appendix B council-wide
 * rounding): each step gives one seat to whichever group–committee cell still has the largest gap
 * between exact entitlement and seats already placed, not all of one party's extras in a row.
 */
function buildAssignmentStepsFromGlobalLargestRemainder(
    committees: Committee[],
    groupNames: string[],
    groupFinalSeatsByName: Record<string, number>,
    floorMatrix: Record<string, Record<string, number>>,
    totalCommitteeSeats: number,
    groupCalculations: GroupFloorCalculation[]
): CommitteeDistributionAssignmentStep[] {
    const assignmentSteps: CommitteeDistributionAssignmentStep[] = [];
    const simulatedMatrix = cloneFloorMatrix(floorMatrix);
    const extraSeatsPlacedPerGroup: Record<string, number> = {};
    const flooredTotalByGroupName: Record<string, number> = {};
    const remainderSeatsByGroupName: Record<string, number> = {};

    for (const groupCalculation of groupCalculations) {
        extraSeatsPlacedPerGroup[groupCalculation.group_name] = 0;
        flooredTotalByGroupName[groupCalculation.group_name] = groupCalculation.cells.reduce(
            (total, cell) => total + cell.floored_seats,
            0
        );
        remainderSeatsByGroupName[groupCalculation.group_name] = groupCalculation.remainder_seats;
    }

    let stepNumber = 1;

    while (true) {
        const candidate = pickGlobalLargestRemainderCandidate(
            committees,
            groupNames,
            groupFinalSeatsByName,
            simulatedMatrix,
            totalCommitteeSeats
        );

        if (candidate === null) {
            break;
        }

        const groupRow = simulatedMatrix[candidate.group_name];
        if (groupRow !== undefined) {
            groupRow[candidate.committee_name] = (groupRow[candidate.committee_name] ?? 0) + 1;
        }

        const extraSeatIndexWithinGroup = extraSeatsPlacedPerGroup[candidate.group_name] ?? 0;
        extraSeatsPlacedPerGroup[candidate.group_name] = extraSeatIndexWithinGroup + 1;

        assignmentSteps.push({
            step_number: stepNumber,
            group_name: candidate.group_name,
            extra_seat_index_within_group: extraSeatIndexWithinGroup,
            remainder_seats_for_group: remainderSeatsByGroupName[candidate.group_name] ?? 0,
            group_final_seats: groupFinalSeatsByName[candidate.group_name] ?? 0,
            floored_total_for_group: flooredTotalByGroupName[candidate.group_name] ?? 0,
        });

        stepNumber += 1;
    }

    return assignmentSteps;
}

function cloneFloorMatrix(
    floorMatrix: Record<string, Record<string, number>>
): Record<string, Record<string, number>> {
    const matrix: Record<string, Record<string, number>> = {};

    for (const groupName of Object.keys(floorMatrix)) {
        matrix[groupName] = {...floorMatrix[groupName]};
    }

    return matrix;
}

export function buildSeatsMatrixThroughAssignmentStep(
    distributionState: CommitteeDistributionState,
    throughStepIndexExclusive: number
): Record<string, Record<string, number>> {
    const matrix = cloneFloorMatrix(distributionState.floor_matrix);

    for (let stepIndex = 0; stepIndex < throughStepIndexExclusive; stepIndex += 1) {
        const committeeIndex = distributionState.assignment_choices[stepIndex];
        if (committeeIndex === null) {
            continue;
        }

        const assignmentStep = distributionState.assignment_steps[stepIndex];
        const committee = distributionState.committees[committeeIndex];
        if (assignmentStep === undefined || committee === undefined) {
            continue;
        }

        const groupRow = matrix[assignmentStep.group_name];
        if (groupRow === undefined) {
            continue;
        }

        groupRow[committee.name] = (groupRow[committee.name] ?? 0) + 1;
    }

    return matrix;
}

export function rebuildSeatsMatrixFromAssignmentChoices(
    distributionState: CommitteeDistributionState
): Record<string, Record<string, number>> {
    return buildSeatsMatrixThroughAssignmentStep(
        distributionState,
        distributionState.assignment_steps.length
    );
}

export function getFirstUnassignedAssignmentStepIndex(
    distributionState: CommitteeDistributionState
): number | null {
    for (let stepIndex = 0; stepIndex < distributionState.assignment_choices.length; stepIndex += 1) {
        if (distributionState.assignment_choices[stepIndex] === null) {
            return stepIndex;
        }
    }

    return null;
}

export function canAssignDistributionStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): boolean {
    if (distributionState.assignment_choices[stepIndex] !== null) {
        return false;
    }

    return getFirstUnassignedAssignmentStepIndex(distributionState) === stepIndex;
}

export function formatAssignmentStepHeading(stepNumber: number, groupName: string): string {
    return (
        "Extra seat " +
        stepNumber +
        " - " +
        groupName +
        " " +
        EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_step_heading_suffix
    );
}

export function getAssignmentStepDataSummary(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): string {
    const assignmentStep = distributionState.assignment_steps[stepIndex];
    if (assignmentStep === undefined) {
        return "";
    }

    const matrixBeforeStep = buildSeatsMatrixThroughAssignmentStep(distributionState, stepIndex);
    const currentAllocated = rowTotalForGroup(matrixBeforeStep, assignmentStep.group_name);
    const seatsStillNeeded = assignmentStep.group_final_seats - currentAllocated;

    if (seatsStillNeeded <= 0) {
        return "";
    }

    const seatsStillNeededLabel = seatsStillNeeded === 1 ? "one more" : seatsStillNeeded + " more";

    return (
        "The " +
        assignmentStep.group_name +
        " Group currently has " +
        currentAllocated +
        " committee seats allocated, and needs to allocate " +
        seatsStillNeededLabel +
        ". " +
        EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.choose_committee_prompt
    );
}

export function getSuggestedCommitteeIndexForAssignmentStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): number {
    const assignmentStep = distributionState.assignment_steps[stepIndex];
    if (assignmentStep === undefined) {
        return 0;
    }

    const matrix = buildSeatsMatrixThroughAssignmentStep(distributionState, stepIndex);
    const globalCandidate = pickGlobalLargestRemainderCandidate(
        distributionState.committees,
        distributionState.group_names,
        distributionState.group_final_seats_by_name,
        matrix,
        distributionState.total_committee_seats
    );

    if (globalCandidate !== null && globalCandidate.group_name === assignmentStep.group_name) {
        return globalCandidate.committee_index;
    }

    const finalSeatsForGroup =
        distributionState.group_final_seats_by_name[assignmentStep.group_name] ?? 0;
    const cells = distributionState.committees.map((committee, committeeIndex) => {
        const rawEntitlement = getRawEntitlementForGroupOnCommittee(
            finalSeatsForGroup,
            committee.seat_count,
            distributionState.total_committee_seats
        );
        const currentSeats = matrix[assignmentStep.group_name]?.[committee.name] ?? 0;

        return {
            committee_index: committeeIndex,
            committee_name: committee.name,
            fractional_part: rawEntitlement - currentSeats,
        };
    });

    const eligibleCells = cells.filter((cell) => {
        const committee = distributionState.committees[cell.committee_index];
        if (committee === undefined) {
            return false;
        }

        return canGroupReceiveAnotherSeatOnCommittee(
            matrix,
            assignmentStep.group_name,
            committee,
            finalSeatsForGroup,
            distributionState.total_committee_seats
        );
    });

    if (eligibleCells.length === 0) {
        return 0;
    }

    const suggestedCell = eligibleCells.reduce((bestCell, cell) => {
        if (cell.fractional_part > bestCell.fractional_part) {
            return cell;
        }

        if (
            cell.fractional_part === bestCell.fractional_part &&
            cell.committee_index < bestCell.committee_index
        ) {
            return cell;
        }

        return bestCell;
    }, eligibleCells[0]);

    return suggestedCell.committee_index;
}

export function initializeCommitteeDistribution(
    committees: Committee[],
    allocationResult: PartyAllocationResult
): CommitteeDistributionState {
    const committeesForDistribution = committees.filter(
        (committee) => committee.name.trim() !== "" && committee.seat_count > 0
    );
    const groupNames = sortGroupAllocationRowsByCouncillorCountDescending(
        allocationResult.rows.filter((row) => row.final_seats > 0)
    ).map((row) => row.group_name);
    const committeeNames = committeesForDistribution.map((committee) => committee.name);

    const floorMatrix = buildEmptyMatrix(groupNames, committeeNames);
    const groupCalculations = buildGroupFloorCalculations(
        committeesForDistribution,
        allocationResult.rows,
        allocationResult.total_committee_seats
    );

    for (const groupCalculation of groupCalculations) {
        for (const cell of groupCalculation.cells) {
            floorMatrix[groupCalculation.group_name][cell.committee_name] = cell.floored_seats;
        }
    }

    const seatsMatrix = buildEmptyMatrix(groupNames, committeeNames);
    for (const groupName of groupNames) {
        for (const committeeName of committeeNames) {
            seatsMatrix[groupName][committeeName] = floorMatrix[groupName][committeeName];
        }
    }

    const groupFinalSeatsByName: Record<string, number> = {};

    for (const row of allocationResult.rows) {
        if (row.final_seats > 0) {
            groupFinalSeatsByName[row.group_name] = row.final_seats;
        }
    }

    const assignmentSteps = buildAssignmentStepsFromGlobalLargestRemainder(
        committeesForDistribution,
        groupNames,
        groupFinalSeatsByName,
        floorMatrix,
        allocationResult.total_committee_seats,
        groupCalculations
    );
    const assignmentChoices = assignmentSteps.map(() => null);

    return {
        group_names: groupNames,
        group_final_seats_by_name: groupFinalSeatsByName,
        committees: committeesForDistribution,
        seats_matrix: seatsMatrix,
        floor_matrix: floorMatrix,
        assignment_steps: assignmentSteps,
        assignment_choices: assignmentChoices,
        total_committee_seats: allocationResult.total_committee_seats,
    };
}

export function getEligibleCommitteeIndicesForAssignmentStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): number[] {
    const assignmentStep = distributionState.assignment_steps[stepIndex];
    if (assignmentStep === undefined) {
        return [];
    }

    const matrix = buildSeatsMatrixThroughAssignmentStep(distributionState, stepIndex);
    const finalSeatsForGroup =
        distributionState.group_final_seats_by_name[assignmentStep.group_name] ?? 0;
    const eligibleIndices: number[] = [];

    for (let committeeIndex = 0; committeeIndex < distributionState.committees.length; committeeIndex += 1) {
        const committee = distributionState.committees[committeeIndex];

        if (
            canGroupReceiveAnotherSeatOnCommittee(
                matrix,
                assignmentStep.group_name,
                committee,
                finalSeatsForGroup,
                distributionState.total_committee_seats
            )
        ) {
            eligibleIndices.push(committeeIndex);
        }
    }

    return eligibleIndices;
}

/**
 * Committee index for the assignment dropdown: suggested if still eligible, otherwise first eligible.
 */
export function getDefaultCommitteeIndexForAssignmentStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number,
    preferredCommitteeIndex?: number
): number {
    const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
        distributionState,
        stepIndex
    );

    if (eligibleCommitteeIndices.length === 0) {
        return 0;
    }

    if (
        preferredCommitteeIndex !== undefined &&
        eligibleCommitteeIndices.includes(preferredCommitteeIndex)
    ) {
        return preferredCommitteeIndex;
    }

    const suggestedCommitteeIndex = getSuggestedCommitteeIndexForAssignmentStep(
        distributionState,
        stepIndex
    );

    if (eligibleCommitteeIndices.includes(suggestedCommitteeIndex)) {
        return suggestedCommitteeIndex;
    }

    return eligibleCommitteeIndices[0];
}

export function assignCommitteeDistributionStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number,
    committeeIndex: number
): CommitteeDistributionState {
    if (!canAssignDistributionStep(distributionState, stepIndex)) {
        return distributionState;
    }

    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined) {
        return distributionState;
    }

    const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
        distributionState,
        stepIndex
    );

    if (!eligibleCommitteeIndices.includes(committeeIndex)) {
        return distributionState;
    }

    const assignmentChoices = [...distributionState.assignment_choices];
    assignmentChoices[stepIndex] = committeeIndex;

    const updatedState: CommitteeDistributionState = {
        ...distributionState,
        assignment_choices: assignmentChoices,
        seats_matrix: distributionState.seats_matrix,
    };

    return {
        ...updatedState,
        seats_matrix: rebuildSeatsMatrixFromAssignmentChoices(updatedState),
    };
}

export function undoCommitteeDistributionStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): CommitteeDistributionState {
    if (distributionState.assignment_choices[stepIndex] === null) {
        return distributionState;
    }

    const assignmentChoices = [...distributionState.assignment_choices];
    for (let index = stepIndex; index < assignmentChoices.length; index += 1) {
        assignmentChoices[index] = null;
    }

    const updatedState: CommitteeDistributionState = {
        ...distributionState,
        assignment_choices: assignmentChoices,
        seats_matrix: distributionState.seats_matrix,
    };

    return {
        ...updatedState,
        seats_matrix: rebuildSeatsMatrixFromAssignmentChoices(updatedState),
    };
}

export function isCommitteeDistributionComplete(distributionState: CommitteeDistributionState): boolean {
    return getFirstUnassignedAssignmentStepIndex(distributionState) === null;
}

export function buildInitialDistributionPendingCommitteeSelections(
    distributionState: CommitteeDistributionState
): number[] {
    return distributionState.assignment_steps.map((_assignmentStep, stepIndex) =>
        getDefaultCommitteeIndexForAssignmentStep(distributionState, stepIndex)
    );
}

/** @deprecated Use assignCommitteeDistributionStep — assigns the next unassigned step only. */
export function applyCommitteeDistributionAssignment(
    distributionState: CommitteeDistributionState,
    committeeIndex: number
): CommitteeDistributionState {
    const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
    if (stepIndex === null) {
        return distributionState;
    }

    return assignCommitteeDistributionStep(distributionState, stepIndex, committeeIndex);
}

export interface CommitteeFloorExampleEntry {
    group_name: string;
    group_final_seats: number;
    raw_entitlement: number;
    floored_seats: number;
}

export interface CommitteeFloorCalculationExample {
    committee_name: string;
    committee_seat_count: number;
    total_committee_seats: number;
    /** First group in allocation order (largest councillor count), used for the worked example. */
    primary_entry: CommitteeFloorExampleEntry;
}

export function buildCommitteeFloorCalculationExample(
    distributionState: CommitteeDistributionState,
    committeeIndex: number = 0
): CommitteeFloorCalculationExample | null {
    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined || distributionState.group_names.length === 0) {
        return null;
    }

    const primaryGroupName = distributionState.group_names[0];
    const groupFinalSeats = distributionState.group_final_seats_by_name[primaryGroupName] ?? 0;
    if (groupFinalSeats <= 0) {
        return null;
    }

    const rawEntitlement =
        (groupFinalSeats * committee.seat_count) / distributionState.total_committee_seats;
    const flooredSeats = Math.floor(rawEntitlement);

    return {
        committee_name: committee.name,
        committee_seat_count: committee.seat_count,
        total_committee_seats: distributionState.total_committee_seats,
        primary_entry: {
            group_name: primaryGroupName,
            group_final_seats: groupFinalSeats,
            raw_entitlement: rawEntitlement,
            floored_seats: flooredSeats,
        },
    };
}

export function formatCommitteeFloorExampleCalculation(example: CommitteeFloorCalculationExample): string {
    const entry = example.primary_entry;

    return (
        entry.group_final_seats +
        " × " +
        example.committee_seat_count +
        " ÷ " +
        example.total_committee_seats +
        " = " +
        formatNumber(entry.raw_entitlement) +
        ", rounded down to " +
        entry.floored_seats
    );
}

export function rowTotalForGroup(
    matrix: Record<string, Record<string, number>>,
    groupName: string
): number {
    const groupRow = matrix[groupName];
    if (groupRow === undefined) {
        return 0;
    }

    return Object.values(groupRow).reduce((total, seats) => total + seats, 0);
}
