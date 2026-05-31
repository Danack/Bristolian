import {beforeAll, describe, expect, test} from "@jest/globals";
import {describeSlow, testSlow} from "../test/jest_slow_tests";
import {calculatePartyAllocation} from "./calculate_party_allocation";
import {
    mergePoliticalGroupsIntoCouncilSetupForm,
    politicalGroupsForCouncilSetup,
} from "./political_groups_form";
import {
    assignCommitteeDistributionStep,
    getPendingCommitteeSelectionDisabledReasonKinds,
    canGroupCompleteRemainderAssignment,
    buildCommitteeFloorCalculationExample,
    columnTotalForMatrix,
    formatCommitteeFloorExampleCalculation,
    getAssignmentStepDataSummaryParts,
    getEligibleCommitteeIndicesForAssignmentStep,
    getFirstUnassignedAssignmentStepIndex,
    getPartyAssignmentBatch,
    getPreviousGroupInAssignmentTurnOrder,
    goBackToLastAssignmentGroupWhenComplete,
    goBackToPreviousAssignmentGroup,
    initializeCommitteeDistribution,
    resolveAssignmentStepGroupName,
    isCommitteeDistributionComplete,
    rowTotalForGroup,
    undoCommitteeDistributionStep,
    assignPartyAssignmentBatch,
} from "./calculate_committee_distribution";
import {
    buildSeatsMatrixThroughAssignmentStep,
    pickGlobalLargestRemainderCandidate,
} from "./committee_distribution_assignment";
import {
    buildFirstAvailablePendingSelectionsForBatch,
    pickFirstAvailableCommitteeIndexForBatch,
    assignCurrentStepWithFirstAvailableCommittee,
    assignCurrentPartyBatchWithFirstAvailableCommittees,
    countRemainingUnassignedStepsForGroup,
    expectPartyBatchSizingMatchesMatrixRemainder,
} from "./test_council_first_available_assignment";
import {politicalGroupsForSeatAllocation} from "./independent_allocation";
import {
    getTestCouncilAtLabourBatchWithSpreadPicks,
    getTestCouncilCompleteDistributionState,
    getTestCouncilDistributionBatchStartCheckpoints,
    getTestCouncilDistributionStateAtGreenBatchStart,
    TEST_COUNCIL_LABOUR_PENDING_WITH_LATER_PARTY_BLOCK,
    warmTestCouncilDistributionTestFixtures,
} from "./test_council_distribution_test_fixtures";
import {getExampleCouncilById} from "./example_councils";
import type {Committee} from "./types";

describe("calculate_committee_distribution", () => {
    const bristolCommittees: Committee[] = [
        {name: "Planning A", seat_count: 9},
        {name: "Planning B", seat_count: 9},
    ];

    const bristolGroups = mergePoliticalGroupsIntoCouncilSetupForm([
        {name: "Green", councillor_count: 34},
        {name: "Labour", councillor_count: 19},
        {name: "Liberal Democrat", councillor_count: 9},
        {name: "Conservative", councillor_count: 7},
        {name: "Independent", councillor_count: 1},
    ]);

    const allocationResult = calculatePartyAllocation({
        political_groups: politicalGroupsForCouncilSetup(bristolGroups),
        total_committee_seats: 18,
    });

    test("floor example uses first committee and largest group with correct formula", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        const example = buildCommitteeFloorCalculationExample(distributionState, 0);

        expect(example).not.toBeNull();
        expect(example?.committee_name).toBe("Planning A");
        expect(example?.primary_entry.group_name).toBe("Green");
        const greenFinalSeats = distributionState.group_final_seats_by_name["Green"] ?? 0;
        expect(example?.primary_entry.group_final_seats).toBe(greenFinalSeats);
        expect(example?.primary_entry.floored_seats).toBe(Math.floor((greenFinalSeats * 9) / 18));
        expect(formatCommitteeFloorExampleCalculation(example!)).toContain("rounded down to ");
    });

    test("row and column totals add numeric matrix cells not concatenate strings", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        const greenGroupName = "Green";
        const firstCommitteeName = distributionState.committees[0]?.name ?? "";

        const matrixWithStringCells: Record<string, Record<string, number>> = {
            ...distributionState.floor_matrix,
            [greenGroupName]: {
                ...distributionState.floor_matrix[greenGroupName],
            },
        };

        for (const committee of distributionState.committees) {
            (matrixWithStringCells[greenGroupName] as Record<string, unknown>)[committee.name] = "4";
        }

        expect(rowTotalForGroup(matrixWithStringCells, greenGroupName)).toBe(
            distributionState.committees.length * 4
        );
        expect(columnTotalForMatrix(matrixWithStringCells, firstCommitteeName)).toBeGreaterThan(0);
        expect(typeof columnTotalForMatrix(matrixWithStringCells, firstCommitteeName)).toBe("number");
    });

    test("floor matrix row totals do not exceed each group final seats", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);

        for (const groupName of distributionState.group_names) {
            const rowTotal = rowTotalForGroup(distributionState.floor_matrix, groupName);
            const finalSeats = distributionState.group_final_seats_by_name[groupName] ?? 0;

            expect(rowTotal).toBeLessThanOrEqual(finalSeats);
        }
    });

    test("test council assignment turn order is smallest party first by councillor count", () => {
        const testCouncilExample = getExampleCouncilById("test_council");
        expect(testCouncilExample).toBeDefined();

        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            testCouncilExample!.political_groups
        );
        const totalCommitteeSeats = testCouncilExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForSeatAllocation(politicalGroups, false),
            total_committee_seats: totalCommitteeSeats,
        });

        const distributionState = initializeCommitteeDistribution(
            testCouncilExample!.committees,
            allocation
        );

        expect(distributionState.established_assignment_turn_order.slice(0, 3)).toEqual([
            "Conservative",
            "Labour",
            "Green",
        ]);
    });

    test("test council smallest party keeps turn until conservative extras are placed", () => {
        const testCouncilExample = getExampleCouncilById("test_council");
        expect(testCouncilExample).toBeDefined();

        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            testCouncilExample!.political_groups
        );
        const totalCommitteeSeats = testCouncilExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForSeatAllocation(politicalGroups, false),
            total_committee_seats: totalCommitteeSeats,
        });

        let distributionState = initializeCommitteeDistribution(
            testCouncilExample!.committees,
            allocation
        );
        const adultSocialCareCommitteeIndex = distributionState.committees.findIndex(
            (committee) => committee.name === "Adult Social Care Committee"
        );
        expect(adultSocialCareCommitteeIndex).toBeGreaterThanOrEqual(0);

        const turnBeforeFirstSeat = resolveAssignmentStepGroupName(distributionState, 0);
        expect(turnBeforeFirstSeat).toBe("Conservative");

        distributionState = assignCommitteeDistributionStep(
            distributionState,
            0,
            adultSocialCareCommitteeIndex
        );

        const conservativeFinalSeats =
            distributionState.group_final_seats_by_name["Conservative"] ?? 0;
        const conservativeRemaining =
            conservativeFinalSeats - rowTotalForGroup(distributionState.seats_matrix, "Conservative");
        const greenFinalSeats = distributionState.group_final_seats_by_name["Green"] ?? 0;
        const greenRemaining =
            greenFinalSeats - rowTotalForGroup(distributionState.seats_matrix, "Green");

        expect(conservativeRemaining).toBeGreaterThan(0);
        expect(greenRemaining).toBeGreaterThan(conservativeRemaining);
        expect(resolveAssignmentStepGroupName(distributionState, 1)).toBe("Conservative");
        expect(getFirstUnassignedAssignmentStepIndex(distributionState)).toBe(1);
    });

    test("whose turn is the smallest party still placing extras not the largest", () => {
        const committees: Committee[] = [
            {name: "Committee A", seat_count: 9},
            {name: "Committee B", seat_count: 9},
            {name: "Committee C", seat_count: 9},
            {name: "Committee D", seat_count: 9},
        ];
        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Green", councillor_count: 40},
            {name: "Conservative", councillor_count: 10},
        ]);
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForCouncilSetup(politicalGroups),
            total_committee_seats: 36,
        });
        const distributionState = initializeCommitteeDistribution(committees, allocation);
        const greenFinalSeats = distributionState.group_final_seats_by_name["Green"] ?? 0;
        const conservativeFinalSeats =
            distributionState.group_final_seats_by_name["Conservative"] ?? 0;
        const greenRemaining =
            greenFinalSeats - rowTotalForGroup(distributionState.floor_matrix, "Green");
        const conservativeRemaining =
            conservativeFinalSeats -
            rowTotalForGroup(distributionState.floor_matrix, "Conservative");

        expect(conservativeRemaining).toBeGreaterThan(greenRemaining);
        expect(resolveAssignmentStepGroupName(distributionState, 0)).toBe("Conservative");
    });

    test("when seats left are tied the party furthest behind on remainder quota has turn", () => {
        const committees: Committee[] = [
            {name: "Committee A", seat_count: 9},
            {name: "Committee B", seat_count: 9},
        ];
        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Alpha", councillor_count: 30},
            {name: "Beta", councillor_count: 30},
        ]);
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForCouncilSetup(politicalGroups),
            total_committee_seats: 18,
        });
        let distributionState = initializeCommitteeDistribution(committees, allocation);

        const alphaGroupName = "Alpha";
        const betaGroupName = "Beta";
        const alphaFinalSeats = distributionState.group_final_seats_by_name[alphaGroupName] ?? 0;
        const betaFinalSeats = distributionState.group_final_seats_by_name[betaGroupName] ?? 0;

        expect(resolveAssignmentStepGroupName(distributionState, 0)).toBe(alphaGroupName);

        distributionState = assignCommitteeDistributionStep(distributionState, 0, 0);

        const alphaRemaining =
            alphaFinalSeats - rowTotalForGroup(distributionState.seats_matrix, alphaGroupName);
        const betaRemaining =
            betaFinalSeats - rowTotalForGroup(distributionState.seats_matrix, betaGroupName);

        expect(alphaRemaining).toBe(0);
        expect(betaRemaining).toBeGreaterThan(0);
        expect(resolveAssignmentStepGroupName(distributionState, 1)).toBe(betaGroupName);
    });

    test("extra seat steps follow smallest party first turn order", () => {
        const committees: Committee[] = [
            {name: "Committee A", seat_count: 9},
            {name: "Committee B", seat_count: 9},
        ];
        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Green", councillor_count: 55},
            {name: "Labour", councillor_count: 35},
        ]);
        const balancedAllocation = calculatePartyAllocation({
            political_groups: politicalGroupsForCouncilSetup(politicalGroups),
            total_committee_seats: 18,
        });
        const distributionState = initializeCommitteeDistribution(committees, balancedAllocation);

        expect(distributionState.assignment_steps).toHaveLength(2);
        expect(distributionState.assignment_steps.map((step) => step.group_name)).toEqual([
            "Labour",
            "Green",
        ]);
    });

    test("undo clears the chosen step and all later steps", () => {
        let distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        distributionState = assignCommitteeDistributionStep(distributionState, 0, 0);
        distributionState = assignCommitteeDistributionStep(distributionState, 1, 1);

        expect(distributionState.assignment_choices[0]).not.toBeNull();
        expect(distributionState.assignment_choices[1]).not.toBeNull();

        distributionState = undoCommitteeDistributionStep(distributionState, 0);

        expect(distributionState.assignment_choices[0]).toBeNull();
        expect(distributionState.assignment_choices[1]).toBeNull();
    });

    test("go back to previous group clears that party batch and all later assignments", () => {
        let distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        const firstBatch = getPartyAssignmentBatch(distributionState, 0);
        expect(firstBatch).not.toBeNull();
        const firstPartyName = firstBatch!.group_name;

        while (getFirstUnassignedAssignmentStepIndex(distributionState) !== null) {
            const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState)!;
            const batch = getPartyAssignmentBatch(distributionState, stepIndex);
            if (batch === null || batch.group_name !== firstPartyName) {
                break;
            }

            distributionState = assignCurrentPartyBatchWithFirstAvailableCommittees(
                distributionState
            );
        }

        const secondPartyStepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
        expect(secondPartyStepIndex).not.toBeNull();

        const secondBatch = getPartyAssignmentBatch(distributionState, secondPartyStepIndex!);
        expect(secondBatch).not.toBeNull();
        expect(getPreviousGroupInAssignmentTurnOrder(distributionState, secondBatch!.group_name)).toBe(
            firstPartyName
        );

        const goBackResult = goBackToPreviousAssignmentGroup(
            distributionState,
            secondPartyStepIndex!
        );
        distributionState = goBackResult.distributionState;

        const activeBatch = getPartyAssignmentBatch(
            distributionState,
            getFirstUnassignedAssignmentStepIndex(distributionState)!
        );
        expect(activeBatch?.group_name).toBe(firstPartyName);
        expect(distributionState.assignment_choices.every((choice) => choice === null)).toBe(true);
        expect(goBackResult.pendingCommitteeSelections.length).toBeGreaterThan(0);
        expect(goBackResult.pendingCommitteeSelections.length).toBe(activeBatch!.seats_to_choose);
    });

    test("tie-break for equal remainder prefers earlier committee table order", () => {
        const committees: Committee[] = [
            {name: "Zebra Committee", seat_count: 9},
            {name: "Alpha Committee", seat_count: 9},
        ];
        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Other", councillor_count: 11},
            {name: "Test", councillor_count: 7},
        ]);
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForCouncilSetup(politicalGroups),
            total_committee_seats: 18,
        });
        const distributionState = initializeCommitteeDistribution(committees, allocation);
        const testStepIndex = distributionState.assignment_steps.findIndex(
            (step) => step.group_name === "Test"
        );

        expect(testStepIndex).toBeGreaterThanOrEqual(0);

        const matrix = buildSeatsMatrixThroughAssignmentStep(distributionState, testStepIndex);
        const candidate = pickGlobalLargestRemainderCandidate(
            distributionState.committees,
            distributionState.group_names,
            distributionState.group_final_seats_by_name,
            distributionState.floor_matrix,
            matrix,
            distributionState.total_committee_seats,
            distributionState.established_assignment_turn_order
        );

        expect(candidate?.committee_index).toBe(0);
    });

    test("assignment step data summary reflects pending committee selections", () => {
        const testCouncilExample = getExampleCouncilById("test_council");
        expect(testCouncilExample).toBeDefined();

        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            testCouncilExample!.political_groups
        );
        const totalCommitteeSeats = testCouncilExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForSeatAllocation(politicalGroups, false),
            total_committee_seats: totalCommitteeSeats,
        });

        const distributionState = initializeCommitteeDistribution(
            testCouncilExample!.committees,
            allocation
        );
        const batch = getPartyAssignmentBatch(distributionState, 0);
        expect(batch).not.toBeNull();

        const seatsStillNeededBefore =
            (distributionState.group_final_seats_by_name[batch!.group_name] ?? 0) -
            rowTotalForGroup(distributionState.seats_matrix, batch!.group_name);

        const floorAllocatedBeforeBatch = rowTotalForGroup(
            distributionState.floor_matrix,
            batch!.group_name
        );

        const summaryBefore = getAssignmentStepDataSummaryParts(distributionState, 0, []);
        expect(summaryBefore?.current_allocated).toBe(floorAllocatedBeforeBatch);
        expect(summaryBefore?.seats_still_needed).toBe(seatsStillNeededBefore);

        const summaryAfterTwoPicks = getAssignmentStepDataSummaryParts(distributionState, 0, [0, 0]);
        expect(summaryAfterTwoPicks?.current_allocated).toBe(floorAllocatedBeforeBatch + 2);
        expect(summaryAfterTwoPicks?.seats_still_needed).toBe(seatsStillNeededBefore - 2);

        const fullPendingSelections = Array.from({length: batch!.seats_to_choose}, () => 0);
        expect(
            getAssignmentStepDataSummaryParts(distributionState, 0, fullPendingSelections)
        ).toBeNull();
    });

    test("first available committee for batch is eligible", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        const firstUnassignedStepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);

        expect(firstUnassignedStepIndex).not.toBeNull();

        const batch = getPartyAssignmentBatch(distributionState, firstUnassignedStepIndex!);
        expect(batch).not.toBeNull();

        const committeeIndex = pickFirstAvailableCommitteeIndexForBatch(
            distributionState,
            batch!,
            []
        );
        expect(committeeIndex).not.toBeNull();

        const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
            distributionState,
            firstUnassignedStepIndex!
        );

        expect(eligibleCommitteeIndices).toContain(committeeIndex);
    });

    test("a group cannot place more than ceil(raw entitlement) seats on one committee", () => {
        const testCouncilExample = getExampleCouncilById("test_council");
        expect(testCouncilExample).toBeDefined();

        const greenBoostedGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            testCouncilExample!.political_groups.map((group) =>
                group.name === "Green" ? {...group, councillor_count: 20} : group
            )
        );
        const totalCommitteeSeats = testCouncilExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForSeatAllocation(greenBoostedGroups, false),
            total_committee_seats: totalCommitteeSeats,
        });

        let distributionState = initializeCommitteeDistribution(
            testCouncilExample!.committees,
            allocation
        );

        const adultSocialCareCommitteeIndex = distributionState.committees.findIndex(
            (committee) => committee.name === "Adult Social Care Committee"
        );
        expect(adultSocialCareCommitteeIndex).toBeGreaterThanOrEqual(0);

        const greenStepIndices = distributionState.assignment_steps
            .map((step, stepIndex) => (step.group_name === "Green" ? stepIndex : -1))
            .filter((stepIndex) => stepIndex >= 0);

        expect(greenStepIndices.length).toBeGreaterThanOrEqual(2);

        const firstGreenStepIndex = greenStepIndices[0];
        const secondGreenStepIndex = greenStepIndices[1];

        for (let stepIndex = 0; stepIndex < firstGreenStepIndex; stepIndex += 1) {
            distributionState = assignCurrentStepWithFirstAvailableCommittee(distributionState);
        }

        distributionState = assignCommitteeDistributionStep(
            distributionState,
            firstGreenStepIndex,
            adultSocialCareCommitteeIndex
        );

        while (getFirstUnassignedAssignmentStepIndex(distributionState)! < secondGreenStepIndex) {
            distributionState = assignCurrentStepWithFirstAvailableCommittee(distributionState);
        }

        const eligibleForSecondStep = getEligibleCommitteeIndicesForAssignmentStep(
            distributionState,
            secondGreenStepIndex
        );

        expect(eligibleForSecondStep).not.toContain(adultSocialCareCommitteeIndex);

        const seatsOnAdultSocialCareBeforeInvalidAssign =
            distributionState.seats_matrix["Green"]?.["Adult Social Care Committee"] ?? 0;

        const afterInvalidAssign = assignCommitteeDistributionStep(
            distributionState,
            secondGreenStepIndex,
            adultSocialCareCommitteeIndex
        );

        expect(afterInvalidAssign.assignment_choices[secondGreenStepIndex]).toBeNull();
        expect(
            afterInvalidAssign.seats_matrix["Green"]?.["Adult Social Care Committee"]
        ).toBe(seatsOnAdultSocialCareBeforeInvalidAssign);
    });

    test("canGroupCompleteRemainderAssignment detects when green cannot place remainder extras", () => {
        const testCouncilExample = getExampleCouncilById("test_council");
        expect(testCouncilExample).toBeDefined();

        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            testCouncilExample!.political_groups
        );
        const totalCommitteeSeats = testCouncilExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForSeatAllocation(politicalGroups, false),
            total_committee_seats: totalCommitteeSeats,
        });

        const distributionState = initializeCommitteeDistribution(
            testCouncilExample!.committees,
            allocation
        );
        const packedMatrix: Record<string, Record<string, number>> = {};

        for (const groupName of distributionState.group_names) {
            packedMatrix[groupName] = {...distributionState.floor_matrix[groupName]};
        }

        for (let committeeIndex = 0; committeeIndex < distributionState.committees.length; committeeIndex += 1) {
            const committeeName = distributionState.committees[committeeIndex]?.name ?? "";
            packedMatrix["Conservative"][committeeName] = 1;
            packedMatrix["Labour"][committeeName] = 3;
            packedMatrix["Liberal Democrat"][committeeName] = committeeIndex < 3 ? 2 : 1;
        }

        expect(
            canGroupCompleteRemainderAssignment(packedMatrix, distributionState, "Green")
        ).toBe(false);
        expect(
            canGroupCompleteRemainderAssignment(distributionState.floor_matrix, distributionState, "Green")
        ).toBe(true);
    });

    test("party batch seats_to_choose follows matrix remainder when steps are interleaved", () => {
        let distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);

        distributionState = assignCommitteeDistributionStep(distributionState, 0, 0);

        const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
        expect(stepIndex).not.toBeNull();

        const batch = getPartyAssignmentBatch(distributionState, stepIndex!);
        expect(batch).not.toBeNull();

        const matrixSeatsStillNeeded =
            (distributionState.group_final_seats_by_name[batch!.group_name] ?? 0) -
            rowTotalForGroup(distributionState.seats_matrix, batch!.group_name);
        const unassignedStepsForParty = countRemainingUnassignedStepsForGroup(
            distributionState,
            batch!.group_name
        );

        expect(matrixSeatsStillNeeded).toBeGreaterThan(0);
        expect(batch!.seats_to_choose).toBe(matrixSeatsStillNeeded);
        expect(unassignedStepsForParty).toBeGreaterThanOrEqual(batch!.step_indices.length);

        if (matrixSeatsStillNeeded > batch!.step_indices.length) {
            const summaryAfterOnePendingPick = getAssignmentStepDataSummaryParts(
                distributionState,
                batch!.first_step_index,
                [0]
            );
            expect(summaryAfterOnePendingPick).not.toBeNull();
            expect(summaryAfterOnePendingPick!.seats_still_needed).toBe(matrixSeatsStillNeeded - 1);
        }
    });

    test("completing all assignment steps reaches each group final seat total", () => {
        let distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);

        while (!isCommitteeDistributionComplete(distributionState)) {
            distributionState = assignCurrentStepWithFirstAvailableCommittee(distributionState);
        }

        for (const groupName of distributionState.group_names) {
            expect(rowTotalForGroup(distributionState.seats_matrix, groupName)).toBe(
                distributionState.group_final_seats_by_name[groupName]
            );
        }
    });

    describeSlow("test council full assignment fixtures", () => {
        beforeAll(() => {
            warmTestCouncilDistributionTestFixtures();
        });

        testSlow("disabled reason kinds include later party block when a committee pick would block green", () => {
            const {distribution_state: distributionState, labour_batch: labourBatch} =
                getTestCouncilAtLabourBatchWithSpreadPicks();

            expect(
                getPendingCommitteeSelectionDisabledReasonKinds(
                    distributionState,
                    labourBatch,
                    TEST_COUNCIL_LABOUR_PENDING_WITH_LATER_PARTY_BLOCK
                )
            ).toContain("later_party_remainder");
        });

        testSlow("disabled reason kinds include committee full when a committee has no seats left", () => {
            const distributionState = getTestCouncilDistributionStateAtGreenBatchStart();
            const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
            expect(stepIndex).not.toBeNull();

            const batch = getPartyAssignmentBatch(distributionState, stepIndex!);
            expect(batch).not.toBeNull();
            expect(batch!.group_name).toBe("Green");

            const reasonKinds = getPendingCommitteeSelectionDisabledReasonKinds(
                distributionState,
                batch!,
                []
            );

            expect(reasonKinds).toContain("committee_full");
        });

        testSlow("green party batch seats_to_choose matches remaining unassigned steps for the party", () => {
            for (const checkpoint of getTestCouncilDistributionBatchStartCheckpoints()) {
                expectPartyBatchSizingMatchesMatrixRemainder(checkpoint.distribution_state);
            }

            expect(isCommitteeDistributionComplete(getTestCouncilCompleteDistributionState())).toBe(true);
        });

        testSlow("partial green pending selections still report seats still needed", () => {
            const distributionState = getTestCouncilDistributionStateAtGreenBatchStart();
            const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
            expect(stepIndex).not.toBeNull();

            const batch = getPartyAssignmentBatch(distributionState, stepIndex!);
            expect(batch).not.toBeNull();
            expect(batch!.group_name).toBe("Green");
            expect(batch!.seats_to_choose).toBeGreaterThanOrEqual(2);

            const summaryBefore = getAssignmentStepDataSummaryParts(
                distributionState,
                batch!.first_step_index,
                []
            );
            expect(summaryBefore?.seats_still_needed).toBeGreaterThanOrEqual(2);

            const committeeIndexForPick = 1;

            const summaryAfterOnePick = getAssignmentStepDataSummaryParts(
                distributionState,
                batch!.first_step_index,
                [committeeIndexForPick]
            );
            expect(summaryAfterOnePick).not.toBeNull();
            expect(summaryAfterOnePick!.seats_still_needed).toBe(
                (summaryBefore?.seats_still_needed ?? 0) - 1
            );
        });

        testSlow("goBackToLastAssignmentGroupWhenComplete reopens the last party batch on test council", () => {
            let distributionState = getTestCouncilCompleteDistributionState();

            expect(isCommitteeDistributionComplete(distributionState)).toBe(true);

            const goBackResult = goBackToLastAssignmentGroupWhenComplete(distributionState);
            distributionState = goBackResult.distributionState;

            expect(isCommitteeDistributionComplete(distributionState)).toBe(false);

            const firstUnassignedStepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
            expect(firstUnassignedStepIndex).not.toBeNull();

            const reopenedBatch = getPartyAssignmentBatch(
                distributionState,
                firstUnassignedStepIndex!
            );

            expect(reopenedBatch?.group_name).toBe("Green");
            expect(reopenedBatch!.seats_to_choose).toBeGreaterThan(0);
            expect(goBackResult.pendingCommitteeSelections.length).toBe(
                reopenedBatch!.seats_to_choose
            );
        });
    });
});
