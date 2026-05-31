import {
    buildSeatsMatrixThroughAssignmentStep,
    getFirstUnassignedAssignmentStepIndex,
    getPartyAssignmentBatch,
    rebuildSeatsMatrixFromAssignmentChoices,
    resolveAssignmentStepGroupName,
} from "./committee_distribution_assignment";
import {rowTotalForGroup} from "./committee_distribution_matrix";
import type {CommitteeDistributionState} from "./types";

export function getGroupNameForCompletedAssignmentStep(
    distributionState: CommitteeDistributionState,
    stepIndex: number
): string | null {
    if (distributionState.assignment_choices[stepIndex] === null) {
        return null;
    }

    const matrixBeforeStep = buildSeatsMatrixThroughAssignmentStep(distributionState, stepIndex);
    const matrixAfterStep = buildSeatsMatrixThroughAssignmentStep(
        distributionState,
        stepIndex + 1
    );

    for (const groupName of distributionState.group_names) {
        const rowTotalBefore = rowTotalForGroup(matrixBeforeStep, groupName);
        const rowTotalAfter = rowTotalForGroup(matrixAfterStep, groupName);

        if (rowTotalAfter > rowTotalBefore) {
            return groupName;
        }
    }

    return null;
}

export function getPreviousGroupInAssignmentTurnOrder(
    distributionState: CommitteeDistributionState,
    groupName: string
): string | null {
    const turnOrderIndex = distributionState.established_assignment_turn_order.indexOf(groupName);

    if (turnOrderIndex <= 0) {
        return null;
    }

    return distributionState.established_assignment_turn_order[turnOrderIndex - 1] ?? null;
}

export function getLastGroupInAssignmentTurnOrder(
    distributionState: CommitteeDistributionState
): string | null {
    const turnOrderLength = distributionState.established_assignment_turn_order.length;

    if (turnOrderLength === 0) {
        return null;
    }

    return distributionState.established_assignment_turn_order[turnOrderLength - 1] ?? null;
}

/** Contiguous assigned steps at the end of the queue that belong to {@link groupName}. */
export function getTrailingAssignmentStepIndicesForGroup(
    distributionState: CommitteeDistributionState,
    groupName: string
): number[] {
    const trailingStepIndices: number[] = [];

    for (
        let stepIndex = distributionState.assignment_steps.length - 1;
        stepIndex >= 0;
        stepIndex -= 1
    ) {
        if (distributionState.assignment_choices[stepIndex] === null) {
            break;
        }

        const groupNameForStep = getGroupNameForCompletedAssignmentStep(
            distributionState,
            stepIndex
        );

        if (groupNameForStep !== groupName) {
            break;
        }

        trailingStepIndices.unshift(stepIndex);
    }

    return trailingStepIndices;
}

export interface GoBackToAssignmentGroupResult {
    distributionState: CommitteeDistributionState;
    pendingCommitteeSelections: number[];
}

/** Confirmed committee indices for a group's assigned steps, in step order. */
export function getAssignedCommitteeSelectionsForGroup(
    distributionState: CommitteeDistributionState,
    groupName: string
): number[] {
    const selections: number[] = [];

    for (let stepIndex = 0; stepIndex < distributionState.assignment_steps.length; stepIndex += 1) {
        const committeeIndex = distributionState.assignment_choices[stepIndex];
        if (committeeIndex === null) {
            continue;
        }

        const groupNameForStep = getGroupNameForCompletedAssignmentStep(
            distributionState,
            stepIndex
        );

        if (groupNameForStep === groupName) {
            selections.push(committeeIndex);
        }
    }

    return selections;
}

export function getFirstAssignmentStepIndexForGroup(
    distributionState: CommitteeDistributionState,
    groupName: string
): number | null {
    let firstStepIndex: number | null = null;

    for (let stepIndex = 0; stepIndex < distributionState.assignment_steps.length; stepIndex += 1) {
        const groupNameForStep =
            getGroupNameForCompletedAssignmentStep(distributionState, stepIndex) ??
            resolveAssignmentStepGroupName(distributionState, stepIndex);

        if (groupNameForStep !== groupName) {
            continue;
        }

        if (firstStepIndex === null || stepIndex < firstStepIndex) {
            firstStepIndex = stepIndex;
        }
    }

    return firstStepIndex;
}

export function goBackToPreviousAssignmentGroup(
    distributionState: CommitteeDistributionState,
    firstUnassignedStepIndex: number
): GoBackToAssignmentGroupResult {
    const batch = getPartyAssignmentBatch(distributionState, firstUnassignedStepIndex);
    if (batch === null) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    const previousGroupName = getPreviousGroupInAssignmentTurnOrder(
        distributionState,
        batch.group_name
    );
    if (previousGroupName === null) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    const firstPreviousGroupStepIndex = getFirstAssignmentStepIndexForGroup(
        distributionState,
        previousGroupName
    );
    if (
        firstPreviousGroupStepIndex === null ||
        distributionState.assignment_choices[firstPreviousGroupStepIndex] === null
    ) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    const pendingCommitteeSelections = getAssignedCommitteeSelectionsForGroup(
        distributionState,
        previousGroupName
    );

    return {
        distributionState: undoCommitteeDistributionStep(
            distributionState,
            firstPreviousGroupStepIndex
        ),
        pendingCommitteeSelections,
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

/**
 * After every seat is placed, reopen the last party's committee batch (largest group on Bristol)
 * so their choices can be changed without undoing earlier parties.
 */
export function goBackToLastAssignmentGroupWhenComplete(
    distributionState: CommitteeDistributionState
): GoBackToAssignmentGroupResult {
    if (!isCommitteeDistributionComplete(distributionState)) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    const lastGroupName = getLastGroupInAssignmentTurnOrder(distributionState);
    if (lastGroupName === null) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    const pendingCommitteeSelections = getAssignedCommitteeSelectionsForGroup(
        distributionState,
        lastGroupName
    );

    const trailingStepIndices = getTrailingAssignmentStepIndicesForGroup(
        distributionState,
        lastGroupName
    );

    if (trailingStepIndices.length > 0) {
        return {
            distributionState: undoCommitteeDistributionStep(
                distributionState,
                trailingStepIndices[0]
            ),
            pendingCommitteeSelections,
        };
    }

    const firstLastGroupStepIndex = getFirstAssignmentStepIndexForGroup(
        distributionState,
        lastGroupName
    );

    if (
        firstLastGroupStepIndex === null ||
        distributionState.assignment_choices[firstLastGroupStepIndex] === null
    ) {
        return {distributionState, pendingCommitteeSelections: []};
    }

    return {
        distributionState: undoCommitteeDistributionStep(
            distributionState,
            firstLastGroupStepIndex
        ),
        pendingCommitteeSelections,
    };
}
