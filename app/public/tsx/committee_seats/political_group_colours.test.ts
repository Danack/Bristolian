import {describe, expect, test} from "@jest/globals";
import {
    getDistributionMatrixChangedCellPresentation,
    getPoliticalGroupCellHighlightStyle,
} from "./political_group_colours";

describe("political_group_colours", () => {
    test("standard parties resolve to configured highlight colours", () => {
        expect(getPoliticalGroupCellHighlightStyle("Labour")?.background_color).toBe("#DC241F");
        expect(getPoliticalGroupCellHighlightStyle("Conservative")?.background_color).toBe("#0087DC");
        expect(getPoliticalGroupCellHighlightStyle("Liberal Democrat")?.background_color).toBe("#FDBB30");
        expect(getPoliticalGroupCellHighlightStyle("Green")?.background_color).toBe("#78B943");
    });

    test("aliases resolve to the same colours as standard names", () => {
        expect(getPoliticalGroupCellHighlightStyle("Lib Dems")?.background_color).toBe("#FDBB30");
        expect(getPoliticalGroupCellHighlightStyle("Conservatives")?.background_color).toBe("#0087DC");
    });

    test("unknown groups fall back to default changed-cell class", () => {
        const presentation = getDistributionMatrixChangedCellPresentation("Poole People");

        expect(presentation.className).toContain("value_cell_changed");
        expect(presentation.style).toBeUndefined();
    });

    test("vacancy uses white background with visible inset border", () => {
        const presentation = getDistributionMatrixChangedCellPresentation("Vacancy");

        expect(presentation.style?.backgroundColor).toBe("#FFFFFF");
        expect(presentation.style?.boxShadow).toContain("#c9a227");
    });
});
