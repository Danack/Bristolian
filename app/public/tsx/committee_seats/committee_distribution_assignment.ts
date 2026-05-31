import {sortGroupAllocationRowsByCouncillorCountDescending} from "./calculate_party_allocation";
import {
    canGroupReceiveAnotherSeatOnCommittee,
    getRawEntitlementForGroupOnCommittee,
} from "./committee_distribution_caps";
import {buildGroupFloorCalculations, type GroupFloorCalculation} from "./committee_distribution_floor";
import {
    buildEmptyMatrix,
    cloneFloorMatrix,
    matrixSeatCount,
    rowTotalForGroup,
} from "./committee_distribution_matrix";
import type {
    Committee,
    CommitteeDistributionAssignmentStep,
    CommitteeDistributionState,
    GroupAllocationRow,
    PartyAllocationResult,
} from "./types";

interface GlobalRemainderCandidate {
    group_name: string;
    committee_index: number;
    committee_name: string;
    unallocated_share: number;
}

function maxUnallocatedShareForGroupOnEligibleCommittees(
    committees: Committee[],
    groupName: string,
    finalSeatsForGroup: number,
    matrix: Record<string, Record<string, number>>,
    totalCommitteeSeats: number
): number {
    let maxUnallocatedShare = 0;

    for (const committee of committees) {
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
        const currentSeats = matrixSeatCount(matrix[groupName]?.[committee.name]);
        const unallocatedShare = rawEntitlement - currentSeats;

        if (unallocatedShare > maxUnallocatedShare) {
            maxUnallocatedShare = unallocatedShare;
        }
    }

    return maxUnallocatedShare;
}

function assignmentTurnOrderIndex(assignmentTurnOrder: string[], groupName: string): number {
    const orderIndex = assignmentTurnOrder.indexOf(groupName);

    return orderIndex >= 0 ? orderIndex : assignmentTurnOrder.length;
}

/**
 * Remainder-round turn order: smallest political group first (by councillors), then next smallest,
 * so the largest group chooses last and takes committees others did not fill.
 */
export function buildAssignmentTurnOrderSmallestPartyFirst(
    allocationRows: GroupAllocationRow[],
    groupCalculations: GroupFloorCalculation[]
): string[] {
    const remainderSeatsByGroupName: Record<string, number> = {};

    for (const groupCalculation of groupCalculations) {
        remainderSeatsByGroupName[groupCalculation.group_name] = groupCalculation.remainder_seats;
    }

    return [...allocationRows]
        .filter(
            (row) =>
                row.final_seats > 0 && (remainderSeatsByGroupName[row.group_name] ?? 0) > 0
        )
        .sort((left, right) => {
            if (left.councillor_count !== right.councillor_count) {
                return left.councillor_count - right.councillor_count;
            }

            if (left.final_seats !== right.final_seats) {
                return left.final_seats - right.final_seats;
            }

            return left.group_name.localeCompare(right.group_name);
        })
        .map((row) => row.group_name);
}

/**
 * Which party receives the next extra seat: the smallest group (by councillors) that still has
 * remainder seats to place, then the next smallest, and so on. The largest group goes last.
 */
function pickGroupForNextExtraSeat(
    committees: Committee[],
    groupNames: string[],
    groupFinalSeatsByName: Record<string, number>,
    floorMatrix: Record<string, Record<string, number>>,
    matrix: Record<string, Record<string, number>>,
    totalCommitteeSeats: number,
    assignmentTurnOrder: string[]
): string | null {
    let bestGroupName: string | null = null;
    let bestTurnOrderIndex = assignmentTurnOrder.length;
    let lowestExtrasPlaced = Number.POSITIVE_INFINITY;
    let bestMaxUnallocatedShare = -1;
    let bestGroupOrderIndex = groupNames.length;

    for (let groupOrderIndex = 0; groupOrderIndex < groupNames.length; groupOrderIndex += 1) {
        const groupName = groupNames[groupOrderIndex];
        const finalSeatsForGroup = groupFinalSeatsByName[groupName] ?? 0;
        const flooredTotal = rowTotalForGroup(floorMatrix, groupName);
        const rowTotal = rowTotalForGroup(matrix, groupName);

        if (rowTotal >= finalSeatsForGroup) {
            continue;
        }

        const extrasToPlaceTotal = finalSeatsForGroup - flooredTotal;
        if (extrasToPlaceTotal <= 0) {
            continue;
        }

        const extrasPlaced = rowTotal - flooredTotal;
        const maxUnallocatedShare = maxUnallocatedShareForGroupOnEligibleCommittees(
            committees,
            groupName,
            finalSeatsForGroup,
            matrix,
            totalCommitteeSeats
        );

        if (maxUnallocatedShare <= 0) {
            continue;
        }

        const turnOrderIndex = assignmentTurnOrderIndex(assignmentTurnOrder, groupName);

        let isBetterCandidate = false;

        if (turnOrderIndex < bestTurnOrderIndex) {
            isBetterCandidate = true;
        } else if (turnOrderIndex === bestTurnOrderIndex && extrasPlaced < lowestExtrasPlaced) {
            isBetterCandidate = true;
        } else if (
            turnOrderIndex === bestTurnOrderIndex &&
            extrasPlaced === lowestExtrasPlaced &&
            maxUnallocatedShare > bestMaxUnallocatedShare
        ) {
            isBetterCandidate = true;
        } else if (
            turnOrderIndex === bestTurnOrderIndex &&
            extrasPlaced === lowestExtrasPlaced &&
            maxUnallocatedShare === bestMaxUnallocatedShare &&
            groupOrderIndex < bestGroupOrderIndex
        ) {
            isBetterCandidate = true;
        }

        if (isBetterCandidate) {
            bestGroupName = groupName;
            lowestExtrasPlaced = extrasPlaced;
            bestTurnOrderIndex = turnOrderIndex;
            bestMaxUnallocatedShare = maxUnallocatedShare;
            bestGroupOrderIndex = groupOrderIndex;
        }
    }

    return bestGroupName;
}

/**
 * Next whole remainder seat in the global largest-remainder sequence (LGA Appendix B).
 *
 * Two steps:
 * 1. {@link pickGroupForNextExtraSeat} — which party receives the seat (smallest party
 *    first in turn order, then largest remainder gap among that party's eligible cells).
 * 2. Among that party's committees, pick the cell with the largest unallocated share
 *    (exact proportional entitlement minus seats already placed there), respecting
 *    per-committee caps and column space.
 *
 * Used when building the simulated `assignment_steps` queue and when replaying confirmed
 * choices into `seats_matrix`. Returns null when every group has reached its final total
 * or no eligible cell still has a positive remainder gap.
 */
export function pickGlobalLargestRemainderCandidate(
    committees: Committee[],
    groupNames: string[],
    groupFinalSeatsByName: Record<string, number>,
    floorMatrix: Record<string, Record<string, number>>,
    matrix: Record<string, Record<string, number>>,
    totalCommitteeSeats: number,
    establishedAssignmentTurnOrder: string[]
): GlobalRemainderCandidate | null {
    const groupName = pickGroupForNextExtraSeat(
        committees,
        groupNames,
        groupFinalSeatsByName,
        floorMatrix,
        matrix,
        totalCommitteeSeats,
        establishedAssignmentTurnOrder
    );

    if (groupName === null) {
        return null;
    }

    const finalSeatsForGroup = groupFinalSeatsByName[groupName] ?? 0;
    let bestCandidate: GlobalRemainderCandidate | null = null;

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
        const currentSeats = matrixSeatCount(matrix[groupName]?.[committee.name]);
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

        // Largest remainder wins; equal shares prefer earlier committee table order.
        if (
            bestCandidate === null ||
            candidate.unallocated_share > bestCandidate.unallocated_share ||
            (candidate.unallocated_share === bestCandidate.unallocated_share &&
                candidate.committee_index < bestCandidate.committee_index)
        ) {
            bestCandidate = candidate;
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
    groupCalculations: GroupFloorCalculation[],
    assignmentTurnOrder: string[]
): {
    assignment_steps: CommitteeDistributionAssignmentStep[];
} {
    const assignmentSteps: CommitteeDistributionAssignmentStep[] = [];
    const simulatedMatrix = cloneFloorMatrix(floorMatrix);
    const remainderSeatsByGroupName: Record<string, number> = {};

    for (const groupCalculation of groupCalculations) {
        remainderSeatsByGroupName[groupCalculation.group_name] = groupCalculation.remainder_seats;
    }

    while (true) {
        const candidate = pickGlobalLargestRemainderCandidate(
            committees,
            groupNames,
            groupFinalSeatsByName,
            floorMatrix,
            simulatedMatrix,
            totalCommitteeSeats,
            assignmentTurnOrder
        );

        if (candidate === null) {
            break;
        }

        const groupRow = simulatedMatrix[candidate.group_name];
        if (groupRow !== undefined) {
            groupRow[candidate.committee_name] = matrixSeatCount(groupRow[candidate.committee_name]) + 1;
        }

        assignmentSteps.push({
            group_name: candidate.group_name,
        });
    }

    supplementAssignmentStepsForMissingRemainderSeats(
        assignmentSteps,
        assignmentTurnOrder,
        remainderSeatsByGroupName
    );

    return {
        assignment_steps: assignmentSteps,
    };
}

function supplementAssignmentStepsForMissingRemainderSeats(
    assignmentSteps: CommitteeDistributionAssignmentStep[],
    assignmentTurnOrder: string[],
    remainderSeatsByGroupName: Record<string, number>
): void {
    const stepCountByGroupName: Record<string, number> = {};

    for (const assignmentStep of assignmentSteps) {
        stepCountByGroupName[assignmentStep.group_name] =
            (stepCountByGroupName[assignmentStep.group_name] ?? 0) + 1;
    }

    for (const groupName of assignmentTurnOrder) {
        const remainderSeats = remainderSeatsByGroupName[groupName] ?? 0;
        const existingStepCount = stepCountByGroupName[groupName] ?? 0;

        for (
            let extraSeatIndexWithinGroup = existingStepCount;
            extraSeatIndexWithinGroup < remainderSeats;
            extraSeatIndexWithinGroup += 1
        ) {
            assignmentSteps.push({
                group_name: groupName,
            });
        }
    }
}

/** Returns the party recorded on an assignment step at init (not recomputed from the matrix). */
export function resolveAssignmentStepGroupName(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): string | null {
    return distributionState.assignment_steps[stepIndex]?.group_name ?? null;
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

        const committee = distributionState.committees[committeeIndex];
        const groupName = distributionState.assignment_steps[stepIndex]?.group_name ?? null;
        if (committee === undefined || groupName === null) {
            continue;
        }

        const groupRow = matrix[groupName];
        if (groupRow === undefined) {
            continue;
        }

        groupRow[committee.name] = matrixSeatCount(groupRow[committee.name]) + 1;
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

export interface AssignmentStepDataSummaryParts {
    group_name: string;
    current_allocated: number;
    seats_still_needed: number;
}

export function getAssignmentStepDataSummaryParts(
    distributionState: CommitteeDistributionState,
    stepIndex: number,
    pendingCommitteeSelections: number[] = []
): AssignmentStepDataSummaryParts | null {
    const assignmentStep = distributionState.assignment_steps[stepIndex];
    const groupName = resolveAssignmentStepGroupName(distributionState, stepIndex);
    if (assignmentStep === undefined || groupName === null) {
        return null;
    }

    const matrixBeforeStep = buildMatrixWithPendingPartySelections(
        distributionState,
        stepIndex,
        pendingCommitteeSelections
    );
    const groupFinalSeats = distributionState.group_final_seats_by_name[groupName] ?? 0;
    const currentAllocated = rowTotalForGroup(matrixBeforeStep, groupName);
    const seatsStillNeeded = groupFinalSeats - currentAllocated;

    if (seatsStillNeeded <= 0) {
        return null;
    }

    return {
        group_name: groupName,
        current_allocated: currentAllocated,
        seats_still_needed: seatsStillNeeded,
    };
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
            floorMatrix[groupCalculation.group_name][cell.committee_name] = matrixSeatCount(
                cell.floored_seats
            );
        }
    }

    const seatsMatrix = buildEmptyMatrix(groupNames, committeeNames);
    for (const groupName of groupNames) {
        for (const committeeName of committeeNames) {
            seatsMatrix[groupName][committeeName] = matrixSeatCount(
                floorMatrix[groupName][committeeName]
            );
        }
    }

    const groupFinalSeatsByName: Record<string, number> = {};

    for (const row of allocationResult.rows) {
        if (row.final_seats > 0) {
            groupFinalSeatsByName[row.group_name] = row.final_seats;
        }
    }

    const assignmentTurnOrder = buildAssignmentTurnOrderSmallestPartyFirst(
        allocationResult.rows,
        groupCalculations
    );
    const {assignment_steps: assignmentSteps} = buildAssignmentStepsFromGlobalLargestRemainder(
        committeesForDistribution,
        groupNames,
        groupFinalSeatsByName,
        floorMatrix,
        allocationResult.total_committee_seats,
        groupCalculations,
        assignmentTurnOrder
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
        established_assignment_turn_order: assignmentTurnOrder,
        total_committee_seats: allocationResult.total_committee_seats,
    };
}

export function getEligibleCommitteeIndicesForAssignmentStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): number[] {
    const assignmentStep = distributionState.assignment_steps[stepIndex];
    const groupName = resolveAssignmentStepGroupName(distributionState, stepIndex);
    if (assignmentStep === undefined || groupName === null) {
        return [];
    }

    const matrix = buildSeatsMatrixThroughAssignmentStep(distributionState, stepIndex);
    const finalSeatsForGroup = distributionState.group_final_seats_by_name[groupName] ?? 0;
    const eligibleIndices: number[] = [];

    for (let committeeIndex = 0; committeeIndex < distributionState.committees.length; committeeIndex += 1) {
        const committee = distributionState.committees[committeeIndex];

        if (
            canGroupReceiveAnotherSeatOnCommittee(
                matrix,
                groupName,
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

export interface PartyAssignmentBatch {
    group_name: string;
    first_step_index: number;
    step_indices: number[];
    seats_to_choose: number;
}

export function getUnassignedStepIndicesForGroup(
    distributionState: CommitteeDistributionState,
    groupName: string
): number[] {
    const stepIndices: number[] = [];

    for (let stepIndex = 0; stepIndex < distributionState.assignment_steps.length; stepIndex += 1) {
        if (distributionState.assignment_choices[stepIndex] !== null) {
            continue;
        }

        if (distributionState.assignment_steps[stepIndex]?.group_name !== groupName) {
            continue;
        }

        stepIndices.push(stepIndex);
    }

    return stepIndices;
}

/**
 * When user choices diverge from the simulated step queue, a party may still need more matrix
 * remainder seats than there are unassigned steps left for that group. Append steps so confirm
 * can commit every pick in the batch.
 */
export function ensureAssignmentStepsForPartyBatch(
    distributionState: CommitteeDistributionState,
    groupName: string,
    requiredStepCount: number
): CommitteeDistributionState {
    const unassignedStepIndices = getUnassignedStepIndicesForGroup(distributionState, groupName);

    if (unassignedStepIndices.length >= requiredStepCount) {
        return distributionState;
    }

    const stepsToAdd = requiredStepCount - unassignedStepIndices.length;
    const assignmentSteps = [...distributionState.assignment_steps];
    const assignmentChoices = [...distributionState.assignment_choices];

    for (let stepOffset = 0; stepOffset < stepsToAdd; stepOffset += 1) {
        assignmentSteps.push({
            group_name: groupName,
        });
        assignmentChoices.push(null);
    }

    return {
        ...distributionState,
        assignment_steps: assignmentSteps,
        assignment_choices: assignmentChoices,
    };
}

export function getPartyAssignmentBatch(
    distributionState: CommitteeDistributionState,
    firstUnassignedStepIndex: number
): PartyAssignmentBatch | null {
    const groupName = resolveAssignmentStepGroupName(distributionState, firstUnassignedStepIndex);
    if (groupName === null) {
        return null;
    }

    if (distributionState.assignment_steps[firstUnassignedStepIndex]?.group_name !== groupName) {
        return null;
    }

    const unassignedStepIndicesForGroup = getUnassignedStepIndicesForGroup(
        distributionState,
        groupName
    );

    const matrix = buildSeatsMatrixThroughAssignmentStep(
        distributionState,
        firstUnassignedStepIndex
    );
    const finalSeatsForGroup = distributionState.group_final_seats_by_name[groupName] ?? 0;
    const seatsStillNeeded = finalSeatsForGroup - rowTotalForGroup(matrix, groupName);

    if (unassignedStepIndicesForGroup.length === 0 || seatsStillNeeded <= 0) {
        return null;
    }

    const seatsToChoose = seatsStillNeeded;
    const stepIndicesForBatch = unassignedStepIndicesForGroup.slice(0, seatsToChoose);

    return {
        group_name: groupName,
        first_step_index: firstUnassignedStepIndex,
        step_indices: stepIndicesForBatch,
        seats_to_choose: seatsToChoose,
    };
}

export function buildMatrixWithPendingPartySelections(
    distributionState: CommitteeDistributionState,
    batchFirstStepIndex: number,
    pendingCommitteeSelections: number[]
): Record<string, Record<string, number>> {
    const matrix = buildSeatsMatrixThroughAssignmentStep(distributionState, batchFirstStepIndex);
    const groupName = resolveAssignmentStepGroupName(distributionState, batchFirstStepIndex);

    if (groupName === null) {
        return matrix;
    }

    const groupRow = matrix[groupName];
    if (groupRow === undefined) {
        return matrix;
    }

    for (const committeeIndex of pendingCommitteeSelections) {
        const committee = distributionState.committees[committeeIndex];
        if (committee === undefined) {
            continue;
        }

        groupRow[committee.name] = matrixSeatCount(groupRow[committee.name]) + 1;
    }

    return matrix;
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

