import type {ExampleCouncilJsonCopyStatus} from "./submit_example_council";
import {politicalGroupsForSeatAllocation} from "./independent_allocation";
import {politicalGroupsForCouncilSetup} from "./political_groups_form";
import {NO_EXAMPLE_COUNCIL_SELECTED} from "./page_config";
import type {Committee, CommitteeDistributionState, PartyAllocationResult, PoliticalGroup} from "./types";
import {restoreCommitteeSeatsStateFromUrl} from "./url_state";
import type {CouncilSetupInput, CouncilSetupValidationInput} from "./types";

export interface CommitteeSeatsPanelProps {
    // PHP mounts the panel only; copy, examples, and wizard config live under committee_seats/*.ts
}

export enum DataSourceMode {
    Example = "example",
    Custom = "custom",
}

export enum WizardStep {
    CouncilSetup = 1,
    PartyAllocation = 2,
    NextSteps = 3,
    SeatDistributionExperimental = 4,
}

export enum ExperimentalSubstep {
    Committees = "committees",
    Distribution = "distribution",
    FinalSummary = "final_summary",
}

export enum CouncilSetupSubstep {
    ChooseDataSource = "choose_data_source",
    EnterCouncilTotals = "enter_council_totals",
    EnterPoliticalGroups = "enter_political_groups",
    ChooseIndependentAllocation = "choose_independent_allocation",
}

export interface CommitteeSeatsPanelState {
    wizard_step: WizardStep;
    council_setup_substep: CouncilSetupSubstep;
    data_source_mode: DataSourceMode;
    selected_example_council_id: string;
    political_groups: PoliticalGroup[];
    committees: Committee[];
    total_committee_seats: number;
    expected_total_councillors: number;
    allocate_seats_to_independents: boolean | null;
    error: string | null;
    warning: string | null;
    allocation_result: PartyAllocationResult | null;
    proposed_example_council_name: string;
    example_council_json_copy_status: ExampleCouncilJsonCopyStatus;
    experimental_substep: ExperimentalSubstep;
    committee_distribution_state: CommitteeDistributionState | null;
    /** Committee indices chosen for the current party's batch (not yet confirmed). */
    distribution_pending_committee_selections: number[];
}

export function getDefaultPanelState(): CommitteeSeatsPanelState {
    return {
        wizard_step: WizardStep.CouncilSetup,
        council_setup_substep: CouncilSetupSubstep.ChooseDataSource,
        data_source_mode: DataSourceMode.Example,
        selected_example_council_id: NO_EXAMPLE_COUNCIL_SELECTED,
        political_groups: [],
        committees: [],
        total_committee_seats: 0,
        expected_total_councillors: 0,
        allocate_seats_to_independents: null,
        error: null,
        warning: null,
        allocation_result: null,
        proposed_example_council_name: "",
        example_council_json_copy_status: "idle",
        experimental_substep: ExperimentalSubstep.Committees,
        committee_distribution_state: null,
        distribution_pending_committee_selections: [],
    };
}

export function getCouncilSetupInput(state: CommitteeSeatsPanelState): CouncilSetupInput {
    return {
        political_groups: politicalGroupsForCouncilSetup(state.political_groups),
        total_committee_seats: state.total_committee_seats,
    };
}

export function getCouncilSetupInputForAllocation(state: CommitteeSeatsPanelState): CouncilSetupInput {
    const allocateSeatsToIndependents = state.allocate_seats_to_independents !== false;

    return {
        political_groups: politicalGroupsForSeatAllocation(
            state.political_groups,
            allocateSeatsToIndependents
        ),
        total_committee_seats: state.total_committee_seats,
    };
}

export function getCouncilSetupValidationInput(state: CommitteeSeatsPanelState): CouncilSetupValidationInput {
    return {
        ...getCouncilSetupInput(state),
        expected_total_councillors: state.expected_total_councillors,
    };
}

export function isCouncilTotalsCompleteForPoliticalGroups(state: CommitteeSeatsPanelState): boolean {
    return (
        state.expected_total_councillors > 0 &&
        Number.isInteger(state.expected_total_councillors) &&
        state.total_committee_seats > 0 &&
        Number.isInteger(state.total_committee_seats)
    );
}

export function formatGroupCouncillorTotalMessage(totalFromGroups: number, expectedTotal: number): string {
    if (totalFromGroups === expectedTotal) {
        return "All Councillors are allocated to a political group.";
    }

    const difference = expectedTotal - totalFromGroups;
    const absoluteDifference = Math.abs(difference);
    const councillorWord = absoluteDifference === 1 ? "councillor" : "councillors";

    if (difference > 0) {
        return (
            "You need to add " +
            absoluteDifference +
            " more " +
            councillorWord +
            " to the groups to match the " +
            expectedTotal +
            " number of councillors in the council."
        );
    }

    return (
        "Your groups currently total " +
        totalFromGroups +
        " councillors. This council has only " +
        expectedTotal +
        ", so you have " +
        absoluteDifference +
        " " +
        councillorWord +
        " too many assigned. Lower one or more counts in the table above until everything adds up to " +
        expectedTotal +
        "."
    );
}

export function panelStateFromUrlRestore(
    urlState: ReturnType<typeof restoreCommitteeSeatsStateFromUrl>
): CommitteeSeatsPanelState {
    if (urlState === null) {
        return getDefaultPanelState();
    }

    return {
        ...urlState,
        wizard_step: urlState.wizard_step as WizardStep,
        council_setup_substep: urlState.council_setup_substep as CouncilSetupSubstep,
        data_source_mode: urlState.data_source_mode as DataSourceMode,
        proposed_example_council_name: "",
        example_council_json_copy_status: "idle",
        experimental_substep: ExperimentalSubstep.Committees,
        committee_distribution_state: null,
        distribution_pending_committee_selections: [],
    };
}
