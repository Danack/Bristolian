import {buildMatrixWithPendingPartySelections} from "./committee_distribution_assignment";
import {
    assignCommitteeDistributionStep,
    assignPartyAssignmentBatch,
    canAddPendingCommitteeSelectionOnCommittee,
    getFirstUnassignedAssignmentStepIndex,
    getPartyAssignmentBatch,
    getSeatCapacityForGroupOnCommittee,
    isCommitteeDistributionComplete,
    isPendingPartyAssignmentBatchReadyToConfirm,
    rowTotalForGroup,
    type PartyAssignmentBatch,
} from "./calculate_committee_distribution";
import {initializeTestCouncilDistributionState} from "./test_council_distribution_test_fixtures";
import type {CommitteeDistributionState} from "./types";
import {expect} from "@jest/globals";

export function pickFirstAvailableCommitteeIndexForBatch(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[]
): number | null {
    for (
        let committeeIndex = 0;
        committeeIndex < distributionState.committees.length;
        committeeIndex += 1
    ) {
        if (
            canAddPendingCommitteeSelectionOnCommittee(
                distributionState,
                batch,
                pendingCommitteeSelections,
                committeeIndex
            )
        ) {
            return committeeIndex;
        }
    }

    return null;
}

export function buildFirstAvailablePendingSelectionsForBatch(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch
): number[] {
    const pendingCommitteeSelections: number[] = [];

    while (pendingCommitteeSelections.length < batch.seats_to_choose) {
        const committeeIndex = pickFirstAvailableCommitteeIndexForBatch(
            distributionState,
            batch,
            pendingCommitteeSelections
        );

        if (committeeIndex === null) {
            throw new Error(
                `No committee available for ${batch.group_name} pick ${pendingCommitteeSelections.length + 1} of ${batch.seats_to_choose}`
            );
        }

        pendingCommitteeSelections.push(committeeIndex);
    }

    return pendingCommitteeSelections;
}

export function assignTestCouncilRemainderSeatsUsingFirstAvailableCommitteeAtEachStep(
    initialDistributionState: CommitteeDistributionState = initializeTestCouncilDistributionState()
): CommitteeDistributionState {
    let distributionState = initialDistributionState;

    while (!isCommitteeDistributionComplete(distributionState)) {
        const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
        if (stepIndex === null) {
            break;
        }

        const batch = getPartyAssignmentBatch(distributionState, stepIndex);
        if (batch === null) {
            throw new Error("Expected party assignment batch while assigning test_council remainder seats");
        }

        const pendingCommitteeSelections = buildFirstAvailablePendingSelectionsForBatch(
            distributionState,
            batch
        );

        if (
            !isPendingPartyAssignmentBatchReadyToConfirm(
                distributionState,
                batch,
                pendingCommitteeSelections
            )
        ) {
            throw new Error(
                `${batch.group_name} batch picks are not ready to confirm: ${pendingCommitteeSelections.join(",")}`
            );
        }

        const updatedState = assignPartyAssignmentBatch(
            distributionState,
            stepIndex,
            pendingCommitteeSelections
        );

        if (updatedState === distributionState) {
            throw new Error(`${batch.group_name} batch did not advance`);
        }

        distributionState = updatedState;
    }

    return distributionState;
}

export function getTestCouncilCommitteeIndexByNameFragment(
    distributionState: CommitteeDistributionState,
    nameFragment: string
): number {
    const committeeIndex = distributionState.committees.findIndex((committee) =>
        committee.name.includes(nameFragment)
    );

    if (committeeIndex < 0) {
        throw new Error(`Committee matching "${nameFragment}" not found on test_council`);
    }

    return committeeIndex;
}

export function advanceTestCouncilToGreenAssignmentBatchUsingFirstAvailablePicks(
    initialDistributionState: CommitteeDistributionState = initializeTestCouncilDistributionState()
): {
    distributionState: CommitteeDistributionState;
    batch: PartyAssignmentBatch;
    stepIndex: number;
} {
    let distributionState = initialDistributionState;

    while (!isCommitteeDistributionComplete(distributionState)) {
        const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
        if (stepIndex === null) {
            break;
        }

        const batch = getPartyAssignmentBatch(distributionState, stepIndex);
        if (batch === null) {
            throw new Error("Expected party assignment batch");
        }

        if (batch.group_name === "Green") {
            return {distributionState, batch, stepIndex};
        }

        const pendingCommitteeSelections = buildFirstAvailablePendingSelectionsForBatch(
            distributionState,
            batch
        );
        distributionState = assignPartyAssignmentBatch(
            distributionState,
            stepIndex,
            pendingCommitteeSelections
        );
    }

    throw new Error("Green assignment batch was not reached");
}

export function getGreenSeatCapacityOnCommittee(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[],
    committeeIndex: number
): number {
    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined) {
        return 0;
    }

    const matrix = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingCommitteeSelections
    );
    const finalSeatsForGroup = distributionState.group_final_seats_by_name[batch.group_name] ?? 0;

    return getSeatCapacityForGroupOnCommittee(
        matrix,
        batch.group_name,
        committee,
        finalSeatsForGroup,
        distributionState.total_committee_seats
    );
}

export function countRemainingUnassignedStepsForGroup(
    distributionState: CommitteeDistributionState,
    groupName: string
): number {
    return distributionState.assignment_steps.filter(
        (step, stepIndex) =>
            step.group_name === groupName &&
            distributionState.assignment_choices[stepIndex] === null
    ).length;
}

export function expectPartyBatchSizingMatchesMatrixRemainder(
    distributionState: CommitteeDistributionState
): void {
    const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
    expect(stepIndex).not.toBeNull();

    const batch = getPartyAssignmentBatch(distributionState, stepIndex!);
    expect(batch).not.toBeNull();

    const seatsStillNeeded =
        (distributionState.group_final_seats_by_name[batch!.group_name] ?? 0) -
        rowTotalForGroup(distributionState.seats_matrix, batch!.group_name);

    expect(batch!.seats_to_choose).toBe(seatsStillNeeded);
    expect(batch!.step_indices.length).toBeGreaterThan(0);
    expect(batch!.step_indices.length).toBeLessThanOrEqual(batch!.seats_to_choose);
}

export function assignCurrentStepWithFirstAvailableCommittee(
    distributionState: CommitteeDistributionState
): CommitteeDistributionState {
    const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
    if (stepIndex === null) {
        throw new Error("Expected an unassigned assignment step");
    }

    const batch = getPartyAssignmentBatch(distributionState, stepIndex);
    if (batch === null) {
        throw new Error("Expected party assignment batch for current step");
    }

    const committeeIndex = pickFirstAvailableCommitteeIndexForBatch(
        distributionState,
        batch,
        []
    );
    if (committeeIndex === null) {
        throw new Error("Expected an available committee for current step");
    }

    return assignCommitteeDistributionStep(distributionState, stepIndex, committeeIndex);
}

export function assignCurrentPartyBatchWithFirstAvailableCommittees(
    distributionState: CommitteeDistributionState
): CommitteeDistributionState {
    const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
    if (stepIndex === null) {
        throw new Error("Expected an unassigned assignment step");
    }

    const batch = getPartyAssignmentBatch(distributionState, stepIndex);
    if (batch === null) {
        throw new Error("Expected party assignment batch for current step");
    }

    return assignPartyAssignmentBatch(
        distributionState,
        stepIndex,
        buildFirstAvailablePendingSelectionsForBatch(distributionState, batch)
    );
}
