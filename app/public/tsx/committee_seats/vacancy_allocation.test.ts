import {describe, expect, test} from "@jest/globals";
import {getExampleCouncilById} from "./example_councils";
import {politicalGroupsForSeatAllocation} from "./independent_allocation";
import {mergePoliticalGroupsIntoCouncilSetupForm, politicalGroupsForCouncilSetup} from "./political_groups_form";
import {
    councilFormHasVacancies,
    formatVacancyCouncillorCountEnteredMessage,
    getVacancyCouncillorCountFromForm,
    STANDARD_VACANCY_GROUP_NAME,
} from "./vacancy_allocation";

describe("vacancy_allocation", () => {
    test("lambeth example maps Vacancy to the standard vacancy row", () => {
        const lambeth = getExampleCouncilById("lambeth");
        expect(lambeth).toBeDefined();

        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(lambeth!.political_groups);
        expect(formGroups.find((group) => group.name === "Vacancy")?.councillor_count).toBe(2);
        expect(getVacancyCouncillorCountFromForm(formGroups)).toBe(2);
        expect(councilFormHasVacancies(formGroups)).toBe(true);
    });

    test("politicalGroupsForSeatAllocation always excludes Vacancy", () => {
        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm([
            {name: "Labour", councillor_count: 26},
            {name: "Green", councillor_count: 27},
            {name: "Vacancy", councillor_count: 2},
        ]);

        const groupsForCouncilSetup = politicalGroupsForCouncilSetup(formGroups);
        expect(groupsForCouncilSetup.find((group) => group.name === STANDARD_VACANCY_GROUP_NAME)).toEqual({
            name: "Vacancy",
            councillor_count: 2,
        });

        const groupsForAllocation = politicalGroupsForSeatAllocation(formGroups, true);
        expect(groupsForAllocation.find((group) => group.name === STANDARD_VACANCY_GROUP_NAME)).toBeUndefined();
        expect(groupsForAllocation).toHaveLength(2);
    });

    test("formatVacancyCouncillorCountEnteredMessage uses singular and plural seat", () => {
        expect(formatVacancyCouncillorCountEnteredMessage(1)).toBe(
            "Your council has 1 vacant council seat."
        );
        expect(formatVacancyCouncillorCountEnteredMessage(2)).toBe(
            "Your council has 2 vacant council seats."
        );
    });
});
