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
