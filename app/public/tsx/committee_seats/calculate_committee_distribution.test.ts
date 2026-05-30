import {describe, expect, test} from "@jest/globals";
import {calculatePartyAllocation} from "./calculate_party_allocation";
import {
    mergePoliticalGroupsIntoCouncilSetupForm,
    politicalGroupsForCouncilSetup,
} from "./political_groups_form";
import {
    assignCommitteeDistributionStep,
    buildCommitteeFloorCalculationExample,
    formatCommitteeFloorExampleCalculation,
    getDefaultCommitteeIndexForAssignmentStep,
    getEligibleCommitteeIndicesForAssignmentStep,
    getFirstUnassignedAssignmentStepIndex,
    getSuggestedCommitteeIndexForAssignmentStep,
    initializeCommitteeDistribution,
    isCommitteeDistributionComplete,
    rowTotalForGroup,
    undoCommitteeDistributionStep,
} from "./calculate_committee_distribution";
import {EXAMPLE_COUNCILS} from "./example_councils";
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

    test("floor matrix row totals do not exceed each group final seats", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);

        for (const groupName of distributionState.group_names) {
            const rowTotal = rowTotalForGroup(distributionState.floor_matrix, groupName);
            const finalSeats = distributionState.group_final_seats_by_name[groupName] ?? 0;

            expect(rowTotal).toBeLessThanOrEqual(finalSeats);
        }
    });

    test("extra seat steps interleave groups by global largest remainder", () => {
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
            "Green",
            "Labour",
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
        expect(getSuggestedCommitteeIndexForAssignmentStep(distributionState, testStepIndex)).toBe(0);
        expect(getDefaultCommitteeIndexForAssignmentStep(distributionState, testStepIndex)).toBe(0);
    });

    test("default committee index for assignment step is always eligible", () => {
        const distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);
        const firstUnassignedStepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);

        expect(firstUnassignedStepIndex).not.toBeNull();

        const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
            distributionState,
            firstUnassignedStepIndex!
        );
        const defaultCommitteeIndex = getDefaultCommitteeIndexForAssignmentStep(
            distributionState,
            firstUnassignedStepIndex!
        );

        expect(eligibleCommitteeIndices).toContain(defaultCommitteeIndex);
    });

    test("a group cannot place more than ceil(raw entitlement) seats on one committee", () => {
        const bristolExample = EXAMPLE_COUNCILS.find((council) => council.id === "bristol");
        expect(bristolExample).toBeDefined();

        const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
            bristolExample!.political_groups.map((group) =>
                group.name === "Conservative"
                    ? {...group, councillor_count: 14}
                    : group
            )
        );
        const totalCommitteeSeats = bristolExample!.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
        const allocation = calculatePartyAllocation({
            political_groups: politicalGroupsForCouncilSetup(politicalGroups),
            total_committee_seats: totalCommitteeSeats,
        });

        let distributionState = initializeCommitteeDistribution(
            bristolExample!.committees,
            allocation
        );

        const adultSocialCareCommitteeIndex = distributionState.committees.findIndex(
            (committee) => committee.name === "Adult Social Care Committee"
        );
        expect(adultSocialCareCommitteeIndex).toBeGreaterThanOrEqual(0);

        const conservativeStepIndices = distributionState.assignment_steps
            .map((step, stepIndex) => (step.group_name === "Conservative" ? stepIndex : -1))
            .filter((stepIndex) => stepIndex >= 0);

        expect(conservativeStepIndices.length).toBeGreaterThanOrEqual(2);

        const firstConservativeStepIndex = conservativeStepIndices[0];
        const secondConservativeStepIndex = conservativeStepIndices[1];

        distributionState = assignCommitteeDistributionStep(
            distributionState,
            firstConservativeStepIndex,
            adultSocialCareCommitteeIndex
        );

        const eligibleForSecondStep = getEligibleCommitteeIndicesForAssignmentStep(
            distributionState,
            secondConservativeStepIndex
        );

        expect(eligibleForSecondStep).not.toContain(adultSocialCareCommitteeIndex);

        const seatsOnAdultSocialCareBeforeInvalidAssign =
            distributionState.seats_matrix["Conservative"]?.["Adult Social Care Committee"] ?? 0;

        const afterInvalidAssign = assignCommitteeDistributionStep(
            distributionState,
            secondConservativeStepIndex,
            adultSocialCareCommitteeIndex
        );

        expect(afterInvalidAssign.assignment_choices[secondConservativeStepIndex]).toBeNull();
        expect(
            afterInvalidAssign.seats_matrix["Conservative"]?.["Adult Social Care Committee"]
        ).toBe(seatsOnAdultSocialCareBeforeInvalidAssign);
    });

    test("completing all assignment steps reaches each group final seat total", () => {
        let distributionState = initializeCommitteeDistribution(bristolCommittees, allocationResult);

        while (!isCommitteeDistributionComplete(distributionState)) {
            const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
            expect(stepIndex).not.toBeNull();
            const committeeIndex = getSuggestedCommitteeIndexForAssignmentStep(
                distributionState,
                stepIndex!
            );
            distributionState = assignCommitteeDistributionStep(
                distributionState,
                stepIndex!,
                committeeIndex
            );
        }

        for (const groupName of distributionState.group_names) {
            expect(rowTotalForGroup(distributionState.seats_matrix, groupName)).toBe(
                distributionState.group_final_seats_by_name[groupName]
            );
        }
    });
});
