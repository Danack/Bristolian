import {describe, expect, test} from "@jest/globals";
import {
    applyExampleCouncilPoliticalGroupsIfMissing,
    getExampleCouncilById,
} from "./example_councils";

describe("applyExampleCouncilPoliticalGroupsIfMissing", () => {
    test("fills form groups from bristol example when counts are missing", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const restored = applyExampleCouncilPoliticalGroupsIfMissing({
            data_source_mode: "example",
            selected_example_council_id: "bristol",
            political_groups: [],
        });

        expect(restored.political_groups.find((group) => group.name === "Green")?.councillor_count).toBe(34);
    });

    test("does not overwrite custom council empty groups", () => {
        const restored = applyExampleCouncilPoliticalGroupsIfMissing({
            data_source_mode: "custom",
            selected_example_council_id: "bristol",
            political_groups: [],
        });

        expect(restored.political_groups).toHaveLength(0);
    });
});
