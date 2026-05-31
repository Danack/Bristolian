import {describe, expect, test} from "@jest/globals";
import {
    assignPartyAssignmentBatch,
    canAddPendingCommitteeSelectionOnCommittee,
    getAssignmentStepDataSummaryParts,
    getPendingCommitteeSelectionDisabledReason,
    isCommitteeDistributionComplete,
    rowTotalForGroup,
} from "./calculate_committee_distribution";
import {
    advanceTestCouncilToGreenAssignmentBatchUsingFirstAvailablePicks,
    assignTestCouncilRemainderSeatsUsingFirstAvailableCommitteeAtEachStep,
    buildFirstAvailablePendingSelectionsForBatch,
    getTestCouncilCommitteeIndexByNameFragment,
    pickFirstAvailableCommitteeIndexForBatch,
} from "./test_council_first_available_assignment";

describe("test_council first available remainder assignment", () => {
    test("assign remaining seats using the first available committee at each pick reaches every group final total", () => {
        const distributionState =
            assignTestCouncilRemainderSeatsUsingFirstAvailableCommitteeAtEachStep();

        expect(isCommitteeDistributionComplete(distributionState)).toBe(true);

        for (const groupName of distributionState.group_names) {
            expect(rowTotalForGroup(distributionState.seats_matrix, groupName)).toBe(
                distributionState.group_final_seats_by_name[groupName]
            );
        }
    });

    test("green batch offers three picks when three remainder seats are still needed", () => {
        const {distributionState, batch, stepIndex} =
            advanceTestCouncilToGreenAssignmentBatchUsingFirstAvailablePicks();

        const summaryBeforeAnyPicks = getAssignmentStepDataSummaryParts(
            distributionState,
            batch.first_step_index,
            []
        );
        expect(summaryBeforeAnyPicks?.seats_still_needed).toBe(3);
        expect(batch.seats_to_choose).toBe(3);

        const pendingCommitteeSelections = buildFirstAvailablePendingSelectionsForBatch(
            distributionState,
            batch
        );
        expect(pendingCommitteeSelections).toEqual([1, 2, 3]);

        expect(
            getAssignmentStepDataSummaryParts(
                distributionState,
                batch.first_step_index,
                pendingCommitteeSelections
            )
        ).toBeNull();

        const distributionStateAfterGreenBatch = assignPartyAssignmentBatch(
            distributionState,
            stepIndex,
            pendingCommitteeSelections
        );

        expect(rowTotalForGroup(distributionStateAfterGreenBatch.seats_matrix, "Green")).toBe(15);
    });

    test("economy and skills committee stays selectable for the second pick when only one green batch pick has been made", () => {
        const {distributionState, batch} =
            advanceTestCouncilToGreenAssignmentBatchUsingFirstAvailablePicks();

        const economyCommitteeIndex = getTestCouncilCommitteeIndexByNameFragment(
            distributionState,
            "Economy"
        );

        const pendingAfterFirstPick: number[] = [];
        const firstPickCommitteeIndex = pickFirstAvailableCommitteeIndexForBatch(
            distributionState,
            batch,
            pendingAfterFirstPick
        );
        expect(firstPickCommitteeIndex).toBe(1);
        pendingAfterFirstPick.push(firstPickCommitteeIndex!);

        expect(
            canAddPendingCommitteeSelectionOnCommittee(
                distributionState,
                batch,
                pendingAfterFirstPick,
                economyCommitteeIndex
            )
        ).toBe(true);
        expect(
            getPendingCommitteeSelectionDisabledReason(
                distributionState,
                batch,
                pendingAfterFirstPick,
                economyCommitteeIndex
            )
        ).toBeNull();
    });
});
