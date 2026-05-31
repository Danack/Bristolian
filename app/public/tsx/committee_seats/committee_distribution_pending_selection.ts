import {
    areLaterPartyRemainderAssignmentsFeasible,
    canGroupReceiveAnotherSeatOnCommittee,
    getGroupsAfterInAssignmentTurnOrder,
    getSeatCapacityForGroupOnCommittee,
} from "./committee_distribution_caps";
import {columnTotalForMatrix} from "./committee_distribution_matrix";
import {
    assignCommitteeDistributionStep,
    buildMatrixWithPendingPartySelections,
    canAssignDistributionStep,
    ensureAssignmentStepsForPartyBatch,
    getEligibleCommitteeIndicesForAssignmentStep,
    getPartyAssignmentBatch,
    type PartyAssignmentBatch,
} from "./committee_distribution_assignment";
import type {CommitteeDistributionState} from "./types";

export function countPendingSelectionsForCommittee(
    pendingCommitteeSelections: number[],
    committeeIndex: number
): number {
    let count = 0;

    for (const pendingCommitteeIndex of pendingCommitteeSelections) {
        if (pendingCommitteeIndex === committeeIndex) {
            count += 1;
        }
    }

    return count;
}

function canAddPendingCommitteeSelectionOnCommitteeBeforeLaterPartyFeasibility(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[],
    committeeIndex: number
): boolean {
    if (pendingCommitteeSelections.length >= batch.seats_to_choose) {
        return false;
    }

    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined) {
        return false;
    }

    const matrix = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingCommitteeSelections
    );
    const finalSeatsForGroup =
        distributionState.group_final_seats_by_name[batch.group_name] ?? 0;

    if (
        !canGroupReceiveAnotherSeatOnCommittee(
            matrix,
            batch.group_name,
            committee,
            finalSeatsForGroup,
            distributionState.total_committee_seats
        )
    ) {
        return false;
    }

    const pendingAfterSelection = [...pendingCommitteeSelections, committeeIndex];
    const matrixAfterSelection = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingAfterSelection
    );
    const picksStillNeededInBatch =
        batch.seats_to_choose - pendingAfterSelection.length;

    if (picksStillNeededInBatch > 0) {
        let remainingBatchCapacity = 0;

        for (const committeeForCapacity of distributionState.committees) {
            remainingBatchCapacity += getSeatCapacityForGroupOnCommittee(
                matrixAfterSelection,
                batch.group_name,
                committeeForCapacity,
                finalSeatsForGroup,
                distributionState.total_committee_seats
            );
        }

        if (remainingBatchCapacity < picksStillNeededInBatch) {
            return false;
        }
    }

    return true;
}

export function canAddPendingCommitteeSelectionOnCommittee(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[],
    committeeIndex: number
): boolean {
    if (
        !canAddPendingCommitteeSelectionOnCommitteeBeforeLaterPartyFeasibility(
            distributionState,
            batch,
            pendingCommitteeSelections,
            committeeIndex
        )
    ) {
        return false;
    }

    const pendingAfterSelection = [...pendingCommitteeSelections, committeeIndex];
    const matrixAfterSelection = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingAfterSelection
    );

    return areLaterPartyRemainderAssignmentsFeasible(
        matrixAfterSelection,
        distributionState,
        batch.group_name
    );
}

export function isPendingCommitteeSelectionBlockedForLaterPartyRemainders(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[],
    committeeIndex: number
): boolean {
    if (countPendingSelectionsForCommittee(pendingCommitteeSelections, committeeIndex) > 0) {
        return false;
    }

    if (
        canAddPendingCommitteeSelectionOnCommittee(
            distributionState,
            batch,
            pendingCommitteeSelections,
            committeeIndex
        )
    ) {
        return false;
    }

    return canAddPendingCommitteeSelectionOnCommitteeBeforeLaterPartyFeasibility(
        distributionState,
        batch,
        pendingCommitteeSelections,
        committeeIndex
    );
}

export function isPendingPartyAssignmentBatchBlockedForLaterPartyRemainders(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[]
): boolean {
    if (pendingCommitteeSelections.length !== batch.seats_to_choose) {
        return false;
    }

    if (getGroupsAfterInAssignmentTurnOrder(distributionState, batch.group_name).length === 0) {
        return false;
    }

    const matrixAfterSelection = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingCommitteeSelections
    );

    return !areLaterPartyRemainderAssignmentsFeasible(
        matrixAfterSelection,
        distributionState,
        batch.group_name
    );
}

export function getLaterPartyGroupNamesForRemainderAssignment(
    distributionState: CommitteeDistributionState,
    groupName: string
): string[] {
    return getGroupsAfterInAssignmentTurnOrder(distributionState, groupName);
}

export type PendingCommitteeSelectionDisabledReason =
    | "committee_full"
    | "group_cap_reached"
    | "later_party_remainder"
    | "would_block_current_party_batch";

export function getPendingCommitteeSelectionDisabledReason(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[],
    committeeIndex: number
): PendingCommitteeSelectionDisabledReason | null {
    if (countPendingSelectionsForCommittee(pendingCommitteeSelections, committeeIndex) > 0) {
        return null;
    }

    if (
        canAddPendingCommitteeSelectionOnCommittee(
            distributionState,
            batch,
            pendingCommitteeSelections,
            committeeIndex
        )
    ) {
        return null;
    }

    if (
        isPendingCommitteeSelectionBlockedForLaterPartyRemainders(
            distributionState,
            batch,
            pendingCommitteeSelections,
            committeeIndex
        )
    ) {
        return "later_party_remainder";
    }

    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined) {
        return null;
    }

    const matrix = buildMatrixWithPendingPartySelections(
        distributionState,
        batch.first_step_index,
        pendingCommitteeSelections
    );
    const finalSeatsForGroup =
        distributionState.group_final_seats_by_name[batch.group_name] ?? 0;

    if (
        !canGroupReceiveAnotherSeatOnCommittee(
            matrix,
            batch.group_name,
            committee,
            finalSeatsForGroup,
            distributionState.total_committee_seats
        )
    ) {
        if (columnTotalForMatrix(matrix, committee.name) >= committee.seat_count) {
            return "committee_full";
        }

        return "group_cap_reached";
    }

    return "would_block_current_party_batch";
}

export function getPendingCommitteeSelectionDisabledReasonKinds(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[]
): PendingCommitteeSelectionDisabledReason[] {
    const reasonKinds = new Set<PendingCommitteeSelectionDisabledReason>();

    if (
        isPendingPartyAssignmentBatchBlockedForLaterPartyRemainders(
            distributionState,
            batch,
            pendingCommitteeSelections
        )
    ) {
        reasonKinds.add("later_party_remainder");
    }

    if (pendingCommitteeSelections.length < batch.seats_to_choose) {
        for (
            let committeeIndex = 0;
            committeeIndex < distributionState.committees.length;
            committeeIndex += 1
        ) {
            const reason = getPendingCommitteeSelectionDisabledReason(
                distributionState,
                batch,
                pendingCommitteeSelections,
                committeeIndex
            );

            if (reason !== null) {
                reasonKinds.add(reason);
            }
        }
    }

    const orderedReasons: PendingCommitteeSelectionDisabledReason[] = [
        "committee_full",
        "group_cap_reached",
        "later_party_remainder",
        "would_block_current_party_batch",
    ];

    return orderedReasons.filter((reason) => reasonKinds.has(reason));
}

export function isPendingPartyAssignmentBatchReadyToConfirm(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch,
    pendingCommitteeSelections: number[]
): boolean {
    if (pendingCommitteeSelections.length !== batch.seats_to_choose) {
        return false;
    }

    let state = ensureAssignmentStepsForPartyBatch(
        distributionState,
        batch.group_name,
        pendingCommitteeSelections.length
    );

    const updatedBatch = getPartyAssignmentBatch(state, batch.first_step_index);
    if (
        updatedBatch === null ||
        updatedBatch.step_indices.length !== pendingCommitteeSelections.length
    ) {
        return false;
    }

    for (let selectionIndex = 0; selectionIndex < pendingCommitteeSelections.length; selectionIndex += 1) {
        const stepIndex = updatedBatch.step_indices[selectionIndex];
        const committeeIndex = pendingCommitteeSelections[selectionIndex];

        if (!canAssignDistributionStep(state, stepIndex)) {
            return false;
        }

        const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
            state,
            stepIndex
        );

        if (!eligibleCommitteeIndices.includes(committeeIndex)) {
            return false;
        }

        state = assignCommitteeDistributionStep(state, stepIndex, committeeIndex);
    }

    return true;
}

export function assignPartyAssignmentBatch(
    distributionState: CommitteeDistributionState,
    batchFirstStepIndex: number,
    pendingCommitteeSelections: number[]
): CommitteeDistributionState {
    const batch = getPartyAssignmentBatch(distributionState, batchFirstStepIndex);
    if (batch === null) {
        return distributionState;
    }

    if (!isPendingPartyAssignmentBatchReadyToConfirm(distributionState, batch, pendingCommitteeSelections)) {
        return distributionState;
    }

    let state = ensureAssignmentStepsForPartyBatch(
        distributionState,
        batch.group_name,
        pendingCommitteeSelections.length
    );

    const updatedBatch = getPartyAssignmentBatch(state, batchFirstStepIndex);
    if (
        updatedBatch === null ||
        updatedBatch.step_indices.length !== pendingCommitteeSelections.length
    ) {
        return distributionState;
    }

    for (let selectionIndex = 0; selectionIndex < pendingCommitteeSelections.length; selectionIndex += 1) {
        state = assignCommitteeDistributionStep(
            state,
            updatedBatch.step_indices[selectionIndex],
            pendingCommitteeSelections[selectionIndex]
        );
    }

    return state;
}
