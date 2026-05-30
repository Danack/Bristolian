import type {GroupAllocationRow} from "./types";

export const NEXT_STEPS_COPY = {
    results_heading: "The results",
    intro:
        "You have worked through the proportional calculation. The figures below are the number of committee " +
        "seats each political group should receive in total across all decision-making bodies on the council.",
    summary_section_title: "Final allocation by group",
    negotiation_heading: "Splitting seats across committees is a matter for negotiation",
    negotiation_lead:
        "This tool calculates each group's fair share of committee places overall. It does not decide which " +
        "councillor sits on which committee.",
    negotiation_body:
        "LGA guidance on political proportionality explains that groups are unlikely to hold the same number " +
        "of places on every committee when committees differ in size. Once the overall totals are agreed, group " +
        "leaders usually negotiate between themselves where those places should fall — which committees each " +
        "group's seats are on, and how any rounding differences are spread across bodies.",
    monitoring_officer_heading: "Check the council's figures",
    monitoring_officer_body:
        "The monitoring officer has a statutory responsibility to ensure the council implements proportionality " +
        "correctly. If your group's entitlement does not match what the council proposes, ask to see how the " +
        "calculation was done and insist on your fair share.",
    out_of_scope_note:
        "This calculator does not allocate party totals to individual committees. That distribution is for " +
        "groups to agree through political negotiation.",
    start_over_button_label: "Start over",
} as const;

export function getNextStepsAllocationRows(rows: GroupAllocationRow[]): GroupAllocationRow[] {
    return rows.filter((row) => row.councillor_count > 0);
}
