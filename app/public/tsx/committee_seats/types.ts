export interface PoliticalGroup {
    name: string;
    councillor_count: number;
}

export interface Committee {
    name: string;
    seat_count: number;
}

export interface ExampleCouncil {
    id: string;
    display_name: string;
    political_groups: PoliticalGroup[];
    /** Present only when documented for that council; used for read-only display. */
    committees: Committee[];
    /**
     * When set without committees, prefills total committee seats.
     * When omitted, the user enters total committee seats on the form.
     */
    total_committee_seats?: number;
    /**
     * When set, pre-selects the independent seats step for this example council.
     * When omitted, the user chooses on that step.
     */
    allocate_seats_to_independents?: boolean;
    /**
     * When set, shown as a link to the council document that records political balance
     * and how committee seats are allocated.
     */
    seat_assignment_source_url?: string;
}

export interface CouncilSetupInput {
    political_groups: PoliticalGroup[];
    total_committee_seats: number;
}

export interface CouncilSetupValidationInput extends CouncilSetupInput {
    expected_total_councillors: number;
}

/** One row in the step-by-step party allocation workbook (all parties per step). */
export interface PartyAllocationWorkbookStep {
    step_number: number;
    label: string;
    seats_by_group_name: Record<string, number>;
    /** Sum of seats allocated to all groups after this step. */
    total_seats_allocated: number;
    /** Plain-language explanation shown under the rounding section heading when seats remain to allocate. */
    description: string | null;
}

export interface GroupAllocationRow {
    group_name: string;
    councillor_count: number;
    percentage_of_council: number;
    raw_entitlement: number;
    floored_seats: number;
    final_seats: number;
}

export interface PartyAllocationResult {
    total_councillors: number;
    total_committee_seats: number;
    rows: GroupAllocationRow[];
    total_allocated_seats: number;
    workbook_steps: PartyAllocationWorkbookStep[];
    /** Shown after the last rounding step when every committee seat has been allocated. */
    all_committee_seats_allocated_message: string | null;
}

/** One extra seat to assign: user picks which committee receives it for a group. */
export interface CommitteeDistributionAssignmentStep {
    step_number: number;
    group_name: string;
    /** 0-based index of this extra seat within the group's remainder seats. */
    extra_seat_index_within_group: number;
    /** How many extra seats this group needs after rounding committee shares down. */
    remainder_seats_for_group: number;
    group_final_seats: number;
    floored_total_for_group: number;
}

export interface CommitteeDistributionState {
    group_names: string[];
    /** Final committee seats per group from the proportional calculation. */
    group_final_seats_by_name: Record<string, number>;
    committees: Committee[];
    /** Seats allocated per group per committee (group name → committee name → count). */
    seats_matrix: Record<string, Record<string, number>>;
    /** Floored minimum seats before remainder assignments. */
    floor_matrix: Record<string, Record<string, number>>;
    assignment_steps: CommitteeDistributionAssignmentStep[];
    /** Committee index chosen for each step, or null if not yet assigned. */
    assignment_choices: Array<number | null>;
    total_committee_seats: number;
}
