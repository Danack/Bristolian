/** Empty `selected_example_council_id` on the choose-data-source step before a council is picked. */
export const NO_EXAMPLE_COUNCIL_SELECTED = "";

/** Page copy and wizard constants for the committee seats tool (client-only; PHP only mounts the widget). */
export const COMMITTEE_SEATS_PAGE = {
    title: "Committee seat allocation calculator",
    tagline:
        "A tool to help people calculate committee seat allocations for each political group, based on " +
        "councillor numbers.",
    choose_source_lead: "How would you like to start?",
    choose_source_custom_description:
        "Enter your own council's figures — total councillors, how many belong to each political group, and " +
        "how many committee seats to allocate.",
    choose_source_example_description:
        "Pick a council with real figures already filled in, then follow the calculation step by step. You can " +
        "change any number before continuing.",
    choose_source_council_placeholder: "Choose a Council",
    choose_source_example_button_label: "Use this council's data",
    choose_source_or_label: "OR",
    council_setup_custom_intro: "You have chosen to enter data for a council.",
    council_setup_how_many_councillors_question: "How many councillors are on the council?",
    council_setup_how_many_committee_seats_question: "How many committee seats are to be allocated?",
    council_setup_political_committees_note:
        "Only count seats on political committees — the decision-making bodies where membership is shared " +
        "between political groups under the political allocation rules. Add together the seats on each of those " +
        "committees to get this total.",
    council_setup_political_groups_section_title: "How many councillors does each group have?",
    allocation_proportional_share_section_title: "Calculate each group's proportional share",
    allocation_rounding_section_title: "Round each share down, then allocate the remaining seats",
    allocation_workbook_total_seats_allocated_heading: "Total seats allocated",
    allocation_step_intro:
        "You have entered all the figures we need. Committee seats are shared in proportion to each " +
        "group's councillors — the tables below take you through that calculation step by step, from each " +
        "group's exact share to rounding down and distributing any remaining seats.",
    allocation_summary_button_label: "Summary",
} as const;

export const WIZARD_DISPLAY_STEPS = [
    {step_number: 1, label: "Choose data source"},
    {step_number: 2, label: "Council total and seats"},
    {step_number: 3, label: "Councillors by group"},
    {step_number: 4, label: "Independent seats"},
    {step_number: 5, label: "Proportional calculation"},
    {step_number: 6, label: "Next steps"},
] as const;

export const TOTAL_WIZARD_DISPLAY_STEPS = WIZARD_DISPLAY_STEPS.length;

export function formatCouncilSetupExampleIntro(councilDisplayName: string): string {
    return "You are using data for " + councilDisplayName + ". Adjust the figures if needed.";
}
