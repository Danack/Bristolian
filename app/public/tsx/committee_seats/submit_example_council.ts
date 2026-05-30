import {applyExampleCouncilToFormState, getExampleCouncilById} from "./example_councils";
import {committeesForSetup} from "./committees_form";
import {politicalGroupsForCouncilSetup} from "./political_groups_form";
import type {Committee, ExampleCouncil, PoliticalGroup} from "./types";

export const SEND_COUNCIL_DATA_COPY = {
    section_heading: "Send us your data",
    section_intro:
        "You are using council figures that are not one of our built-in examples — either because you entered " +
        "your own data, or because you changed an example council's numbers. We want to grow the list of " +
        "councils people can load as worked examples.",
    council_name_label: "Please enter the council name",
    council_name_input_id: "proposed_example_council_name",
    json_description:
        "Copy this JSON and send it to us if you would like your council added as an example in the tool.",
    json_placeholder: "Enter a council name above to generate the JSON.",
    copy_json_button_label: "Copy JSON",
    copy_json_button_copied_label: "Copied!",
    copy_json_button_failed_label: "Copy failed — select the JSON below",
} as const;

export type ExampleCouncilJsonCopyStatus = "idle" | "copied" | "failed";

/** Copy text to the clipboard, with a fallback when the async Clipboard API is unavailable. */
export async function copyTextToClipboard(text: string): Promise<boolean> {
    if (typeof navigator !== "undefined" && navigator.clipboard?.writeText !== undefined) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch {
            // Fall through to execCommand fallback.
        }
    }

    if (typeof document === "undefined") {
        return false;
    }

    const temporaryTextarea = document.createElement("textarea");
    temporaryTextarea.value = text;
    temporaryTextarea.setAttribute("readonly", "true");
    temporaryTextarea.style.position = "fixed";
    temporaryTextarea.style.top = "0";
    temporaryTextarea.style.left = "0";
    temporaryTextarea.style.opacity = "0";

    document.body.appendChild(temporaryTextarea);
    temporaryTextarea.focus();
    temporaryTextarea.select();

    let copied = false;
    try {
        copied = document.execCommand("copy");
    } catch {
        copied = false;
    }

    document.body.removeChild(temporaryTextarea);
    return copied;
}

export interface SendCouncilDataPanelSnapshot {
    data_source_mode: string;
    selected_example_council_id: string;
    political_groups: PoliticalGroup[];
    committees: Committee[];
    total_committee_seats: number;
}

/** URL- and file-friendly id slug from a council display name. */
export function slugifyExampleCouncilId(displayName: string): string {
    const slug = displayName
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");

    return slug === "" ? "council" : slug;
}

export function buildExampleCouncilExportData(
    politicalGroups: PoliticalGroup[],
    committees: Committee[],
    totalCommitteeSeats: number
): Pick<ExampleCouncil, "political_groups" | "committees" | "total_committee_seats"> {
    const exportData: Pick<ExampleCouncil, "political_groups" | "committees" | "total_committee_seats"> = {
        political_groups: politicalGroupsForCouncilSetup(politicalGroups).map((politicalGroup) => ({
            name: politicalGroup.name,
            councillor_count: politicalGroup.councillor_count,
        })),
        committees: committeesForSetup(committees).map((committee) => ({
            name: committee.name,
            seat_count: committee.seat_count,
        })),
    };

    if (committees.length === 0 && totalCommitteeSeats > 0) {
        exportData.total_committee_seats = totalCommitteeSeats;
    }

    return exportData;
}

export function buildExampleCouncilExportDataFromExample(exampleCouncil: ExampleCouncil) {
    const applied = applyExampleCouncilToFormState(exampleCouncil);

    return buildExampleCouncilExportData(
        applied.political_groups,
        applied.committees,
        applied.total_committee_seats
    );
}

export function panelExampleCouncilDataMatchesExample(
    panelSnapshot: SendCouncilDataPanelSnapshot,
    exampleCouncil: ExampleCouncil
): boolean {
    const currentExportData = buildExampleCouncilExportData(
        panelSnapshot.political_groups,
        panelSnapshot.committees,
        panelSnapshot.total_committee_seats
    );
    const originalExportData = buildExampleCouncilExportDataFromExample(exampleCouncil);

    return JSON.stringify(currentExportData) === JSON.stringify(originalExportData);
}

export function shouldOfferSendCouncilData(panelSnapshot: SendCouncilDataPanelSnapshot): boolean {
    if (panelSnapshot.data_source_mode === "custom") {
        return true;
    }

    const exampleCouncil = getExampleCouncilById(panelSnapshot.selected_example_council_id);
    if (exampleCouncil === undefined) {
        return true;
    }

    return !panelExampleCouncilDataMatchesExample(panelSnapshot, exampleCouncil);
}

export function buildExampleCouncilSubmission(
    councilDisplayName: string,
    panelSnapshot: SendCouncilDataPanelSnapshot
): ExampleCouncil {
    const exportData = buildExampleCouncilExportData(
        panelSnapshot.political_groups,
        panelSnapshot.committees,
        panelSnapshot.total_committee_seats
    );

    return {
        id: slugifyExampleCouncilId(councilDisplayName),
        display_name: councilDisplayName.trim(),
        ...exportData,
    };
}

export function formatExampleCouncilSubmissionJson(
    councilDisplayName: string,
    panelSnapshot: SendCouncilDataPanelSnapshot
): string | null {
    const trimmedCouncilDisplayName = councilDisplayName.trim();
    if (trimmedCouncilDisplayName === "") {
        return null;
    }

    return JSON.stringify(
        buildExampleCouncilSubmission(trimmedCouncilDisplayName, panelSnapshot),
        null,
        2
    );
}
