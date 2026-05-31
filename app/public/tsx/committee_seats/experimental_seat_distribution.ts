/** Copy for the advanced seat distribution branch (not in the main six-step trail). */
export const EXPERIMENTAL_SEAT_DISTRIBUTION_COPY = {
    page_heading: "Advanced — seat distribution",
    page_intro_paragraphs: [
        "The previous section, the allocation of committee seats, is based in law.",
        "This section, the distribution of committee seats, to individual committees is probably best practice; it is much more of a guideline than what you'd call actual rules.",
        "Follow the instructions to allow each group in turn to choose which committees their remaining seats are on.",
    ],
    committees_intro:
        "List each political committee and how many seats it has. Example councils are prefilled where we " +
        "have that data. The seat counts here must add up to the total committee seats you entered earlier.",
    distribution_intro:
        "Each group's fair share of seats is split across committees in proportion to committee size. " +
        "The table below shows the minimum (rounded-down) seats per committee. The second table is where " +
        "you place any seats left after rounding down.",
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
        "after rounding are assigned in the next section.",
    assignment_section_title: "Assign remaining seats",
    assignment_section_intro:
        "The matrix above shows minimum seats after rounding down. Some groups still need whole seats on " +
        "committees — you assign those here. One party at a time places all of their remaining seats: the " +
        "smallest group (by councillors) chooses first, then the next smallest, and so on until the largest " +
        "group goes last. Click a committee button once for each seat they still need in this round (buttons " +
        "highlight when selected), then confirm the batch. Use Go back to return to the previous party's " +
        "batch and choose their committees again.",
    hide_assignment_instructions_button_label: "Hide instructions",
    show_assignment_instructions_button_label: "Show instructions",
    assignment_group_chosen_committees_message: (groupName: string) =>
        "The " + groupName + " group have chosen their committee seats.",
    assignment_go_back_to_group_button_label: (groupName: string) =>
        "Go back to " + groupName + " group",
    assignment_clear_selection_button_label: (groupName: string) =>
        "Clear '" + groupName + "' Selection",
    assignment_confirm_choice_button_label: (
        groupName: string,
        nextGroupName: string | null
    ) =>
        nextGroupName === null
            ? "Confirm " + groupName + " group choice."
            : "Confirm " + groupName + " group choice, proceed to '" + nextGroupName + "'",
    assignment_none_needed_message:
        "Rounding down already placed every group's seats on committees; no extra seats are needed.",
    assignment_complete_message: "Every group's seats have been placed on committees.",
    go_to_final_summary_button_label: "Go to final summary",
    final_summary_page_heading: "Final summary",
    final_summary_intro:
        "Below is a summary of the proportional calculation for committee seats overall, and how those seats " +
        "are allocated across the committees you entered.",
    final_summary_proportional_section_title: "Proportional calculation",
    final_summary_committee_allocation_section_title: "Committee seat allocation",
    back_to_distribution_button_label: "Back to distribution",
    back_to_committees_button_label: "Back",
    back_to_results_button_label: "Back to results",
    results_experimental_button_label: "Advanced — seat distribution",
    choose_committee_prompt: "Choose a committee.",
    assignment_current_turn_row_suffix: "(current turn)",
    assignment_remainder_table_seats_left_column: "Seats left to assign",
    assignment_disabled_committee_committee_full_note:
        "Some committees are disabled because that committee already has all of its seats assigned.",
    assignment_disabled_committee_group_cap_note: (groupName: string) =>
        "Some committees are disabled because the " +
        groupName +
        " group has no more seats to allocate on those committees.",
    assignment_disabled_committee_later_party_note: (laterGroupNames: string[]) => {
        if (laterGroupNames.length === 0) {
            return (
                "Some committees are disabled because this choice would leave no room for a later " +
                "party's remainder seats."
            );
        }

        if (laterGroupNames.length === 1) {
            return (
                "Some committees are disabled because this choice would leave no room for the " +
                laterGroupNames[0] +
                " group to place their remaining seats (within the per-committee limits for each party)."
            );
        }

        return (
            "Some committees are disabled because this choice would leave no room for a later party " +
            "(" +
            laterGroupNames.join(", ") +
            ") to place their remaining seats (within the per-committee limits for each party)."
        );
    },
    assignment_disabled_committee_batch_spread_note: (groupName: string) =>
        "Some committees are disabled because the " +
        groupName +
        " group has no more seats to allocate on those committees.",
    assignment_disabled_committee_modal_close_button_label: "Close",
    assignment_disabled_committee_modal_heading: (committeeName: string) =>
        committeeName + " cannot be chosen",
    assignment_disabled_committee_modal_committee_full_message: (committeeName: string) =>
        committeeName + " already has all of its seats assigned.",
    assignment_disabled_committee_modal_group_cap_message: (groupName: string) =>
        "The " + groupName + " group has no more seats to allocate.",
    assignment_disabled_committee_modal_later_party_message: (
        committeeName: string,
        laterGroupNames: string[]
    ) => {
        if (laterGroupNames.length === 0) {
            return (
                "Choosing " +
                committeeName +
                " would leave no room for a later party's remainder seats."
            );
        }

        if (laterGroupNames.length === 1) {
            return (
                "Choosing " +
                committeeName +
                " would leave no room for the " +
                laterGroupNames[0] +
                " group to place their remaining seats (within the per-committee limits for each party)."
            );
        }

        return (
            "Choosing " +
            committeeName +
            " would leave no room for a later party (" +
            laterGroupNames.join(", ") +
            ") to place their remaining seats (within the per-committee limits for each party)."
        );
    },
    assignment_disabled_committee_modal_batch_spread_message: (groupName: string) =>
        "The " + groupName + " group has no more seats to allocate.",
} as const;
