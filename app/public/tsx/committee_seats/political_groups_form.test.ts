import {describe, expect, test} from "@jest/globals";
import {getExampleCouncilById} from "./example_councils";
import {
    clampGroupCouncillorCountAfterAdjustment,
    clampGroupCouncillorCountValue,
    isGroupCouncillorCountAdjustmentDisabled,
    createEmptyCouncilSetupPoliticalGroups,
    COUNCIL_SETUP_POLITICAL_GROUP_ROW_COUNT,
    getVisibleAdditionalPoliticalGroupSlotCount,
    mergePoliticalGroupsIntoCouncilSetupForm,
    politicalGroupsForCouncilSetup,
    STANDARD_POLITICAL_GROUP_NAMES,
} from "./political_groups_form";

describe("political_groups_form", () => {
    test("empty form has seven standard rows and twenty additional slots", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        expect(formGroups).toHaveLength(COUNCIL_SETUP_POLITICAL_GROUP_ROW_COUNT);
        expect(formGroups[0].name).toBe("Labour");
        expect(formGroups[5].name).toBe("Independent");
        expect(formGroups[6].name).toBe("Vacancy");
        expect(formGroups[7].name).toBe("");
    });

    test("bristol example maps known parties to standard rows and others to additional slots", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups);
        expect(formGroups.find((group) => group.name === "Green")?.councillor_count).toBe(34);
        expect(formGroups.find((group) => group.name === "Labour")?.councillor_count).toBe(19);
        expect(formGroups.find((group) => group.name === "Independent")?.councillor_count).toBe(1);

        expect(formGroups.find((group) => group.name === "Liberal Democrat")?.councillor_count).toBe(9);
        expect(formGroups.find((group) => group.name === "Conservative")?.councillor_count).toBe(7);

        const additionalNames = formGroups
            .slice(STANDARD_POLITICAL_GROUP_NAMES.length)
            .map((group) => group.name)
            .filter((name) => name !== "");
        expect(additionalNames).toHaveLength(0);
    });

    test("legacy aliases map onto standard rows when merging", () => {
        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Libdems", councillor_count: 9},
            {name: "Conservatives", councillor_count: 7},
            {name: "Ungrouped", councillor_count: 1},
        ]);

        expect(formGroups.find((group) => group.name === "Liberal Democrat")?.councillor_count).toBe(9);
        expect(formGroups.find((group) => group.name === "Conservative")?.councillor_count).toBe(7);
        expect(formGroups.find((group) => group.name === "Independent")?.councillor_count).toBe(1);
    });

    test("visible additional group rows start at one and grow when a row is used", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        expect(getVisibleAdditionalPoliticalGroupSlotCount(formGroups)).toBe(1);

        formGroups[7].name = "Ungrouped";
        expect(getVisibleAdditionalPoliticalGroupSlotCount(formGroups)).toBe(2);

        formGroups[8] = {name: "Other", councillor_count: 2};
        expect(getVisibleAdditionalPoliticalGroupSlotCount(formGroups)).toBe(3);
    });

    test("clampGroupCouncillorCountValue prevents negative counts and exceeding council total", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        formGroups[0].councillor_count = 30;
        formGroups[1].councillor_count = 20;

        expect(clampGroupCouncillorCountValue(formGroups, 0, -5, 70)).toBe(0);
        expect(clampGroupCouncillorCountAfterAdjustment(formGroups, 0, -10, 70)).toBe(20);
        expect(clampGroupCouncillorCountValue(formGroups, 2, 100, 70)).toBe(20);
        expect(clampGroupCouncillorCountAfterAdjustment(formGroups, 2, 10, 70)).toBe(10);
    });

    test("isGroupCouncillorCountAdjustmentDisabled when adjustment would not change count", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        formGroups[0].councillor_count = 0;
        formGroups[1].councillor_count = 70;

        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 0, -1, 70)).toBe(true);
        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 0, -10, 70)).toBe(true);
        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 0, 1, 70)).toBe(true);
        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 1, 1, 70)).toBe(true);
        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 1, 10, 70)).toBe(true);

        formGroups[0].councillor_count = 5;
        formGroups[1].councillor_count = 65;

        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 0, -1, 70)).toBe(false);
        expect(isGroupCouncillorCountAdjustmentDisabled(formGroups, 1, 1, 70)).toBe(true);
    });

    test("politicalGroupsForCouncilSetup omits empty additional rows and zero-count groups", () => {
        const formGroups = createEmptyCouncilSetupPoliticalGroups();
        formGroups[0].councillor_count = 10;
        formGroups[7] = {name: "Ungrouped", councillor_count: 1};
        formGroups[8] = {name: "Unused", councillor_count: 0};

        const groupsForSetup = politicalGroupsForCouncilSetup(formGroups);
        expect(groupsForSetup).toHaveLength(2);
        expect(groupsForSetup[0]).toEqual({name: "Labour", councillor_count: 10});
        expect(groupsForSetup[1]).toEqual({name: "Ungrouped", councillor_count: 1});
    });
});
