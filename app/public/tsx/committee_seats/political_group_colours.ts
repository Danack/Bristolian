import {findStandardGroupIndex} from "./political_groups_form";

export interface PoliticalGroupHighlightStyle {
    background_color: string;
    text_color: string;
}

/** Background colours for standard parties on changed distribution matrix cells. */
const STANDARD_POLITICAL_GROUP_HIGHLIGHT_STYLES: readonly PoliticalGroupHighlightStyle[] = [
    {background_color: "#DC241F", text_color: "#FFFFFF"},
    {background_color: "#0087DC", text_color: "#FFFFFF"},
    {background_color: "#FDBB30", text_color: "#000000"},
    {background_color: "#78B943", text_color: "#000000"},
    {background_color: "#12B6CF", text_color: "#000000"},
    {background_color: "#B9B9B9", text_color: "#000000"},
    {background_color: "#FFFFFF", text_color: "#000000"},
];

export function getPoliticalGroupCellHighlightStyle(
    groupName: string
): PoliticalGroupHighlightStyle | null {
    const standardGroupIndex = findStandardGroupIndex(groupName);
    if (standardGroupIndex < 0) {
        return null;
    }

    return STANDARD_POLITICAL_GROUP_HIGHLIGHT_STYLES[standardGroupIndex] ?? null;
}

export function getDistributionMatrixChangedCellPresentation(groupName: string): {
    className: string;
    style: Record<string, string> | undefined;
} {
    const partyHighlightStyle = getPoliticalGroupCellHighlightStyle(groupName);

    if (partyHighlightStyle === null) {
        return {
            className:
                "committee_seats_allocation_workbook_value committee_seats_allocation_workbook_value_cell_changed",
            style: undefined,
        };
    }

    const style: Record<string, string> = {
        backgroundColor: partyHighlightStyle.background_color,
        color: partyHighlightStyle.text_color,
        fontWeight: "700",
    };

    if (partyHighlightStyle.background_color === "#FFFFFF") {
        style.boxShadow = "inset 0 0 0 2px #c9a227";
    } else {
        style.boxShadow = `inset 0 0 0 2px ${partyHighlightStyle.background_color}`;
    }

    return {
        className:
            "committee_seats_allocation_workbook_value committee_seats_distribution_value_cell_party_highlight",
        style,
    };
}
