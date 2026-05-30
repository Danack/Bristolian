import {describe, expect, test} from "@jest/globals";
import {applyExampleCouncilToFormState, getExampleCouncilById} from "./example_councils";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./political_groups_form";
import {
    buildExampleCouncilSubmission,
    formatExampleCouncilSubmissionJson,
    panelExampleCouncilDataMatchesExample,
    shouldOfferSendCouncilData,
    slugifyExampleCouncilId,
} from "./submit_example_council";

describe("submit_example_council", () => {
    test("slugifyExampleCouncilId creates a file-friendly id", () => {
        expect(slugifyExampleCouncilId("Bristol City Council")).toBe("bristol_city_council");
        expect(slugifyExampleCouncilId("   ")).toBe("council");
    });

    test("shouldOfferSendCouncilData is true for custom councils", () => {
        expect(
            shouldOfferSendCouncilData({
                data_source_mode: "custom",
                selected_example_council_id: "bristol",
                political_groups: [{name: "Labour", councillor_count: 30}],
                committees: [],
                total_committee_seats: 50,
            })
        ).toBe(true);
    });

    test("shouldOfferSendCouncilData is false for unmodified example councils", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const applied = applyExampleCouncilToFormState(bristol!);

        expect(
            shouldOfferSendCouncilData({
                data_source_mode: "example",
                selected_example_council_id: "bristol",
                political_groups: applied.political_groups,
                committees: applied.committees,
                total_committee_seats: applied.total_committee_seats,
            })
        ).toBe(false);
    });

    test("shouldOfferSendCouncilData is true when an example council was modified", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const applied = applyExampleCouncilToFormState(bristol!);
        applied.political_groups[0].councillor_count = 33;

        expect(
            shouldOfferSendCouncilData({
                data_source_mode: "example",
                selected_example_council_id: "bristol",
                political_groups: applied.political_groups,
                committees: applied.committees,
                total_committee_seats: applied.total_committee_seats,
            })
        ).toBe(true);
    });

    test("formatExampleCouncilSubmissionJson matches example council shape", () => {
        const json = formatExampleCouncilSubmissionJson("Example Town Council", {
            data_source_mode: "custom",
            selected_example_council_id: "bristol",
            political_groups: [
                {name: "Labour", councillor_count: 20},
                {name: "Conservative", councillor_count: 15},
            ],
            committees: [],
            total_committee_seats: 40,
        });

        expect(json).not.toBeNull();
        const parsed = JSON.parse(json!);

        expect(parsed).toEqual({
            id: "example_town_council",
            display_name: "Example Town Council",
            political_groups: [
                {name: "Labour", councillor_count: 20},
                {name: "Conservative", councillor_count: 15},
            ],
            committees: [],
            total_committee_seats: 40,
        });
    });

    test("panelExampleCouncilDataMatchesExample compares barnet committees", () => {
        const barnet = getExampleCouncilById("barnet");
        expect(barnet).toBeDefined();

        const appliedGroups = mergePoliticalGroupsIntoCouncilSetupForm(barnet!.political_groups);

        expect(
            panelExampleCouncilDataMatchesExample(
                {
                    data_source_mode: "example",
                    selected_example_council_id: "barnet",
                    political_groups: appliedGroups,
                    committees: barnet!.committees,
                    total_committee_seats: 56,
                },
                barnet!
            )
        ).toBe(true);

        expect(buildExampleCouncilSubmission("Barnet Council", {
            data_source_mode: "example",
            selected_example_council_id: "barnet",
            political_groups: appliedGroups,
            committees: barnet!.committees,
            total_committee_seats: 56,
        }).committees).toHaveLength(7);
    });
});
