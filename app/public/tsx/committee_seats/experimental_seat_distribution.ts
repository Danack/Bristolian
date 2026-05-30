/** Copy for the experimental seat distribution branch (not in the main six-step trail). */
export const EXPERIMENTAL_SEAT_DISTRIBUTION_COPY = {
    page_heading: "Experimental — seat distribution",
    committees_intro:
        "List each political committee and how many seats it has. Example councils are prefilled where we " +
        "have that data. The seat counts here must add up to the total committee seats you entered earlier.",
    distribution_intro:
        "Each group's fair share of seats is split across committees in proportion to committee size. " +
        "The table below shows the minimum (rounded-down) seats per committee. Extra seats are allocated in " +
        "largest-remainder order across all groups and committees (the same principle as the main calculation), " +
        "not by giving one party every choice before the next.",
    floor_section_title: "Minimum seats per group on each committee",
    floor_section_explanation_lead:
        "For each political group, we take that group's total committee seats from the proportional " +
        "calculation and split them across committees in proportion to each committee's size.",
    floor_section_explanation_how:
        "On each committee, a group's minimum seats are:",
    floor_section_explanation_formula:
        "(group's total committee seats) × (this committee's seats ÷ all committee seats), rounded down " +
        "to a whole number.",
    floor_section_explanation_example_heading: "Example —",
    floor_section_explanation_table_note:
        "The table shows the rounded-down minimum for every group on every committee. Any seats left over " +
        "after rounding are assigned below in largest-remainder order (biggest entitlement gap first, across " +
        "all groups and committees).",
    assignment_section_title: "Assign remaining seats",
    assignment_section_intro:
        "Each step below is one extra seat that rounding could not place automatically. Steps follow the " +
        "largest-remainder method used in LGA guidance for council-wide totals: whichever group on whichever " +
        "committee has the biggest gap after rounding gets the next seat, so parties take turns rather than " +
        "one group using up every choice first. Work through in order; confirmed choices stay visible and you " +
        "can undo (which also clears later steps).",
    assignment_step_heading_suffix: "choice",
    assignment_chosen_label: "Assigned to",
    assignment_locked_note: "Complete the steps above before assigning this seat.",
    assignment_none_needed_message:
        "Rounding down already placed every group's seats on committees; no extra seats are needed.",
    assign_button_label: "Assign seat",
    undo_assignment_button_label: "Undo",
    assignment_complete_message: "Every group's seats have been placed on committees.",
    back_to_committees_button_label: "Back",
    back_to_results_button_label: "Back to results",
    start_over_from_experimental_label: "Start over",
    results_experimental_button_label: "Experimental — seat distribution",
    choose_committee_prompt: "Choose a committee.",
} as const;
