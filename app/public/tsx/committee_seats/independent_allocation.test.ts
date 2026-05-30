import {describe, expect, test} from "@jest/globals";
import {getExampleCouncilById} from "./example_councils";
import {
    councilFormHasIndependentCouncillors,
    formatIndependentCouncillorCountEnteredMessage,
    getDefaultAllocateSeatsToIndependentsForExampleCouncil,
    getInitialIndependentAllocationChoice,
    getIndependentCouncillorCountFromForm,
    politicalGroupsForSeatAllocation,
    STANDARD_INDEPENDENT_GROUP_NAME,
} from "./independent_allocation";
import {
    createEmptyCouncilSetupPoliticalGroups,
    mergePoliticalGroupsIntoCouncilSetupForm,
} from "./political_groups_form";

describe("independent_allocation", () => {
    test("detects independent councillors on the council setup form", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups);
        expect(getIndependentCouncillorCountFromForm(formGroups)).toBe(1);
        expect(councilFormHasIndependentCouncillors(formGroups)).toBe(true);
    });

    test("politicalGroupsForSeatAllocation excludes Vacancy even when including independents", () => {
        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Labour", councillor_count: 10},
            {name: "Vacancy", councillor_count: 1},
        ]);

        const groupsForAllocation = politicalGroupsForSeatAllocation(formGroups, true);
        expect(groupsForAllocation).toEqual([{name: "Labour", councillor_count: 10}]);
    });

    test("politicalGroupsForSeatAllocation can exclude Independent", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups);
        const groupsForAllocation = politicalGroupsForSeatAllocation(formGroups, false);

        expect(groupsForAllocation.find((group) => group.name === STANDARD_INDEPENDENT_GROUP_NAME)).toBeUndefined();
        expect(groupsForAllocation).toHaveLength(4);
    });

    test("council without independent councillors does not require the step", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        formGroups[0].councillor_count = 30;
        formGroups[1].councillor_count = 20;

        expect(councilFormHasIndependentCouncillors(formGroups)).toBe(false);
    });

    test("formatIndependentCouncillorCountEnteredMessage uses singular and plural councillor", () => {
        expect(formatIndependentCouncillorCountEnteredMessage(1)).toBe(
            "Your council has 1 Independent councillor."
        );
        expect(formatIndependentCouncillorCountEnteredMessage(3)).toBe(
            "Your council has 3 Independent councillors."
        );
    });

    test("bristol and barnet examples default to excluding independents", () => {
        expect(getDefaultAllocateSeatsToIndependentsForExampleCouncil("bristol")).toBe(false);
        expect(getDefaultAllocateSeatsToIndependentsForExampleCouncil("barnet")).toBe(false);
    });

    test("sheffield example defaults to allocating seats to independents", () => {
        expect(getDefaultAllocateSeatsToIndependentsForExampleCouncil("sheffield")).toBe(true);

        const sheffield = getExampleCouncilById("sheffield");
        expect(sheffield).toBeDefined();
        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(sheffield!.political_groups);
        expect(getIndependentCouncillorCountFromForm(formGroups)).toBe(4);
        expect(getInitialIndependentAllocationChoice("example", "sheffield", null)).toBe(true);
    });

    test("getInitialIndependentAllocationChoice applies example defaults", () => {
        expect(getInitialIndependentAllocationChoice("example", "bristol", null)).toBe(false);
        expect(getInitialIndependentAllocationChoice("example", "sheffield", null)).toBe(true);
        expect(getInitialIndependentAllocationChoice("example", "barnet", null)).toBe(false);
        expect(getInitialIndependentAllocationChoice("custom", "bristol", null)).toBeNull();
        expect(getInitialIndependentAllocationChoice("example", "bristol", true)).toBe(true);
    });
});
