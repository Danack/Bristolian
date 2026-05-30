import {calculatePartyAllocation, calculateTotalCouncillors, validateCouncilSetup} from "./calculate_party_allocation";
import {
    applyExampleCouncilPoliticalGroupsIfMissing,
    applyExampleCouncilToFormState,
    EXAMPLE_COUNCILS,
    getExampleCouncilById,
} from "./example_councils";
import {
    councilFormHasIndependentCouncillors,
    getDefaultAllocateSeatsToIndependentsForExampleCouncil,
    getInitialIndependentAllocationChoice,
    politicalGroupsForSeatAllocation,
} from "./independent_allocation";
import {NO_EXAMPLE_COUNCIL_SELECTED} from "./page_config";
import {
    mergePoliticalGroupsIntoCouncilSetupForm,
    politicalGroupsForCouncilSetup,
} from "./political_groups_form";
import type {Committee, PoliticalGroup} from "./types";
import {getWizardDisplayStepNumber, WizardDisplayStep} from "./wizard_display_step";

/** Serializable panel state used for URL query parameters. */
export interface CommitteeSeatsUrlPanelState {
    wizard_step: number;
    council_setup_substep: string;
    data_source_mode: string;
    selected_example_council_id: string;
    political_groups: PoliticalGroup[];
    committees: Committee[];
    total_committee_seats: number;
    expected_total_councillors: number;
    allocate_seats_to_independents: boolean | null;
    error: string | null;
    warning: string | null;
    allocation_result: ReturnType<typeof calculatePartyAllocation> | null;
}

const URL_PARAM_STEP = "step";
const URL_PARAM_SOURCE = "source";
const URL_PARAM_EXAMPLE = "example";
const URL_PARAM_SEATS = "seats";
const URL_PARAM_COUNCILLORS = "councillors";
const URL_PARAM_GROUPS = "groups";
const URL_PARAM_INDEPENDENTS = "independents";

const URL_STEP_CHOOSE = "choose";
const URL_STEP_TOTALS = "totals";
const URL_STEP_GROUPS = "groups";
const URL_STEP_INDEPENDENTS = "independents";
const URL_STEP_SETUP = "setup";
const URL_STEP_ALLOCATION = "allocation";
const URL_STEP_NEXT_STEPS = "next_steps";

const URL_INDEPENDENTS_INCLUDED = "included";
const URL_INDEPENDENTS_EXCLUDED = "excluded";

const URL_SOURCE_EXAMPLE = "example";
const URL_SOURCE_CUSTOM = "custom";

const WIZARD_STEP_COUNCIL_SETUP = 1;
const WIZARD_STEP_PARTY_ALLOCATION = 2;
const WIZARD_STEP_NEXT_STEPS = 3;
const COUNCIL_SETUP_SUBSTEP_CHOOSE_DATA_SOURCE = "choose_data_source";
const COUNCIL_SETUP_SUBSTEP_ENTER_COUNCIL_TOTALS = "enter_council_totals";
const COUNCIL_SETUP_SUBSTEP_ENTER_POLITICAL_GROUPS = "enter_political_groups";
const COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION = "choose_independent_allocation";

const DATA_SOURCE_MODE_EXAMPLE = "example";
const DATA_SOURCE_MODE_CUSTOM = "custom";

const GROUP_NAME_COUNT_SEPARATOR = "|";
const GROUP_ENTRY_SEPARATOR = ",";

function encodePoliticalGroups(politicalGroups: PoliticalGroup[]): string {
    return politicalGroups
        .map((politicalGroup) => {
            return (
                encodeURIComponent(politicalGroup.name) +
                GROUP_NAME_COUNT_SEPARATOR +
                String(politicalGroup.councillor_count)
            );
        })
        .join(GROUP_ENTRY_SEPARATOR);
}

function decodePoliticalGroups(groupsParameter: string): PoliticalGroup[] | null {
    if (groupsParameter.trim() === "") {
        return [];
    }

    const politicalGroups: PoliticalGroup[] = [];

    for (const groupEntry of groupsParameter.split(GROUP_ENTRY_SEPARATOR)) {
        const separatorIndex = groupEntry.indexOf(GROUP_NAME_COUNT_SEPARATOR);
        if (separatorIndex === -1) {
            return null;
        }

        const encodedName = groupEntry.slice(0, separatorIndex);
        const councillorCountText = groupEntry.slice(separatorIndex + 1);
        const councillorCount = Number(councillorCountText);

        if (encodedName === "" || councillorCountText === "" || !Number.isFinite(councillorCount)) {
            return null;
        }

        politicalGroups.push({
            name: decodeURIComponent(encodedName),
            councillor_count: councillorCount,
        });
    }

    return politicalGroups;
}

function parseAllocateSeatsToIndependentsParameter(parameter: string | null): boolean | null {
    if (parameter === null) {
        return null;
    }

    if (parameter === URL_INDEPENDENTS_INCLUDED) {
        return true;
    }

    if (parameter === URL_INDEPENDENTS_EXCLUDED) {
        return false;
    }

    return null;
}

function encodeAllocateSeatsToIndependents(allocateSeatsToIndependents: boolean): string {
    return allocateSeatsToIndependents ? URL_INDEPENDENTS_INCLUDED : URL_INDEPENDENTS_EXCLUDED;
}

function wizardDisplayStepToUrlStep(displayStep: WizardDisplayStep): string {
    if (displayStep === WizardDisplayStep.ChooseDataSource) {
        return URL_STEP_CHOOSE;
    }

    if (displayStep === WizardDisplayStep.CouncilTotals) {
        return URL_STEP_TOTALS;
    }

    if (displayStep === WizardDisplayStep.PoliticalGroups) {
        return URL_STEP_GROUPS;
    }

    if (displayStep === WizardDisplayStep.IndependentAllocation) {
        return URL_STEP_INDEPENDENTS;
    }

    if (displayStep === WizardDisplayStep.NextSteps) {
        return URL_STEP_NEXT_STEPS;
    }

    return URL_STEP_ALLOCATION;
}

function urlStepToWizardFields(urlStep: string): {
    wizard_step: number;
    council_setup_substep: string;
} | null {
    if (urlStep === URL_STEP_CHOOSE) {
        return {
            wizard_step: WIZARD_STEP_COUNCIL_SETUP,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_DATA_SOURCE,
        };
    }

    if (urlStep === URL_STEP_TOTALS) {
        return {
            wizard_step: WIZARD_STEP_COUNCIL_SETUP,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_ENTER_COUNCIL_TOTALS,
        };
    }

    if (urlStep === URL_STEP_GROUPS || urlStep === URL_STEP_SETUP) {
        return {
            wizard_step: WIZARD_STEP_COUNCIL_SETUP,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_ENTER_POLITICAL_GROUPS,
        };
    }

    if (urlStep === URL_STEP_INDEPENDENTS) {
        return {
            wizard_step: WIZARD_STEP_COUNCIL_SETUP,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION,
        };
    }

    if (urlStep === URL_STEP_ALLOCATION) {
        return {
            wizard_step: WIZARD_STEP_PARTY_ALLOCATION,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION,
        };
    }

    if (urlStep === URL_STEP_NEXT_STEPS) {
        return {
            wizard_step: WIZARD_STEP_NEXT_STEPS,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION,
        };
    }

    return null;
}

function isDefaultStateForUrl(state: CommitteeSeatsUrlPanelState): boolean {
    const displayStep = getWizardDisplayStepNumber(state);

    return (
        displayStep === WizardDisplayStep.ChooseDataSource &&
        state.data_source_mode === DATA_SOURCE_MODE_EXAMPLE &&
        state.selected_example_council_id === NO_EXAMPLE_COUNCIL_SELECTED &&
        state.political_groups.length === 0 &&
        state.total_committee_seats === 0 &&
        state.expected_total_councillors === 0 &&
        state.allocate_seats_to_independents === null &&
        state.allocation_result === null &&
        state.error === null &&
        state.warning === null
    );
}

export function formatCommitteeSeatsUrlSearch(state: CommitteeSeatsUrlPanelState): string {
    if (isDefaultStateForUrl(state)) {
        return "";
    }

    const displayStep = getWizardDisplayStepNumber(state);
    const parameters = new URLSearchParams();
    parameters.set(URL_PARAM_STEP, wizardDisplayStepToUrlStep(displayStep));

    if (state.data_source_mode === DATA_SOURCE_MODE_EXAMPLE) {
        parameters.set(URL_PARAM_SOURCE, URL_SOURCE_EXAMPLE);
        if (state.selected_example_council_id !== NO_EXAMPLE_COUNCIL_SELECTED) {
            parameters.set(URL_PARAM_EXAMPLE, state.selected_example_council_id);
        }
    } else {
        parameters.set(URL_PARAM_SOURCE, URL_SOURCE_CUSTOM);
    }

    if (displayStep !== WizardDisplayStep.ChooseDataSource) {
        parameters.set(URL_PARAM_SEATS, String(state.total_committee_seats));
        parameters.set(URL_PARAM_COUNCILLORS, String(state.expected_total_councillors));
    }

    if (
        displayStep === WizardDisplayStep.PoliticalGroups ||
        displayStep === WizardDisplayStep.IndependentAllocation ||
        displayStep === WizardDisplayStep.PartyAllocation ||
        displayStep === WizardDisplayStep.NextSteps
    ) {
        parameters.set(
            URL_PARAM_GROUPS,
            encodePoliticalGroups(politicalGroupsForCouncilSetup(state.political_groups))
        );
    }

    if (state.allocate_seats_to_independents !== null) {
        parameters.set(
            URL_PARAM_INDEPENDENTS,
            encodeAllocateSeatsToIndependents(state.allocate_seats_to_independents)
        );
    }

    const query = parameters.toString();
    return query === "" ? "" : "?" + query;
}

function createEmptyPanelState(): CommitteeSeatsUrlPanelState {
    return {
        wizard_step: WIZARD_STEP_COUNCIL_SETUP,
        council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_DATA_SOURCE,
        data_source_mode: DATA_SOURCE_MODE_EXAMPLE,
        selected_example_council_id: NO_EXAMPLE_COUNCIL_SELECTED,
        political_groups: [],
        committees: [],
        total_committee_seats: 0,
        expected_total_councillors: 0,
        allocate_seats_to_independents: null,
        error: null,
        warning: null,
        allocation_result: null,
    };
}

function committeesForExampleCouncil(exampleCouncilId: string): Committee[] {
    const exampleCouncil = getExampleCouncilById(exampleCouncilId);
    if (exampleCouncil === undefined) {
        return [];
    }

    return applyExampleCouncilToFormState(exampleCouncil).committees;
}

function buildAllocationState(
    baseState: CommitteeSeatsUrlPanelState,
    wizardStep: number = WIZARD_STEP_PARTY_ALLOCATION
): CommitteeSeatsUrlPanelState {
    const councilSetupValidationInput = {
        political_groups: politicalGroupsForCouncilSetup(baseState.political_groups),
        total_committee_seats: baseState.total_committee_seats,
        expected_total_councillors: baseState.expected_total_councillors,
    };
    const validation = validateCouncilSetup(councilSetupValidationInput);

    if (!validation.valid) {
        return {
            ...baseState,
            wizard_step: WIZARD_STEP_COUNCIL_SETUP,
            council_setup_substep: COUNCIL_SETUP_SUBSTEP_ENTER_POLITICAL_GROUPS,
            allocation_result: null,
            error: validation.error,
            warning: validation.warning,
        };
    }

    const allocateSeatsToIndependents = baseState.allocate_seats_to_independents !== false;

    const councilSetupInput = {
        political_groups: politicalGroupsForSeatAllocation(
            baseState.political_groups,
            allocateSeatsToIndependents
        ),
        total_committee_seats: councilSetupValidationInput.total_committee_seats,
    };

    return {
        ...baseState,
        wizard_step: wizardStep,
        council_setup_substep: COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION,
        allocation_result: calculatePartyAllocation(councilSetupInput),
        error: null,
        warning: validation.warning,
    };
}

export function restoreCommitteeSeatsStateFromUrl(search: string): CommitteeSeatsUrlPanelState | null {
    const query = search.startsWith("?") ? search.slice(1) : search;
    if (query.trim() === "") {
        return null;
    }

    const parameters = new URLSearchParams(query);
    const urlStep = parameters.get(URL_PARAM_STEP);
    if (urlStep === null) {
        return null;
    }

    const wizardFields = urlStepToWizardFields(urlStep);
    if (wizardFields === null) {
        return null;
    }

    const sourceParameter = parameters.get(URL_PARAM_SOURCE);
    if (sourceParameter !== URL_SOURCE_EXAMPLE && sourceParameter !== URL_SOURCE_CUSTOM) {
        return null;
    }

    let state = createEmptyPanelState();
    state = {
        ...state,
        ...wizardFields,
    };

    if (sourceParameter === URL_SOURCE_EXAMPLE) {
        const exampleCouncilId = parameters.get(URL_PARAM_EXAMPLE);
        if (
            urlStep === URL_STEP_CHOOSE &&
            (exampleCouncilId === null || exampleCouncilId === NO_EXAMPLE_COUNCIL_SELECTED)
        ) {
            state = {
                ...state,
                data_source_mode: DATA_SOURCE_MODE_EXAMPLE,
                selected_example_council_id: NO_EXAMPLE_COUNCIL_SELECTED,
                committees: [],
            };
        } else if (exampleCouncilId === null || getExampleCouncilById(exampleCouncilId) === undefined) {
            return null;
        } else {
            state = {
                ...state,
                data_source_mode: DATA_SOURCE_MODE_EXAMPLE,
                selected_example_council_id: exampleCouncilId,
                committees: committeesForExampleCouncil(exampleCouncilId),
            };
        }
    } else {
        state = {
            ...state,
            data_source_mode: DATA_SOURCE_MODE_CUSTOM,
            committees: [],
        };
    }

    if (urlStep === URL_STEP_CHOOSE) {
        return state;
    }

    if (urlStep === URL_STEP_TOTALS) {
        const seatsParameter = parameters.get(URL_PARAM_SEATS);
        const councillorsParameter = parameters.get(URL_PARAM_COUNCILLORS);

        if (seatsParameter !== null) {
            const totalCommitteeSeats = Number(seatsParameter);
            if (!Number.isInteger(totalCommitteeSeats) || totalCommitteeSeats < 0) {
                return null;
            }

            state = {
                ...state,
                total_committee_seats: totalCommitteeSeats,
            };
        }

        if (councillorsParameter !== null) {
            const expectedTotalCouncillors = Number(councillorsParameter);
            if (!Number.isInteger(expectedTotalCouncillors) || expectedTotalCouncillors < 0) {
                return null;
            }

            state = {
                ...state,
                expected_total_councillors: expectedTotalCouncillors,
            };
        }

        return applyExampleCouncilPoliticalGroupsIfMissing(state);
    }

    const seatsParameter = parameters.get(URL_PARAM_SEATS);
    const groupsParameter = parameters.get(URL_PARAM_GROUPS);
    if (seatsParameter === null || groupsParameter === null) {
        return null;
    }

    const totalCommitteeSeats = Number(seatsParameter);
    if (!Number.isInteger(totalCommitteeSeats) || totalCommitteeSeats <= 0) {
        return null;
    }

    const politicalGroups = decodePoliticalGroups(groupsParameter);
    if (politicalGroups === null) {
        return null;
    }

    const groupsForSetup = politicalGroupsForCouncilSetup(
        mergePoliticalGroupsIntoCouncilSetupForm(politicalGroups)
    );
    const councillorsParameter = parameters.get(URL_PARAM_COUNCILLORS);
    let expectedTotalCouncillors = calculateTotalCouncillors(groupsForSetup);
    if (councillorsParameter !== null) {
        const parsedExpectedTotalCouncillors = Number(councillorsParameter);
        if (
            !Number.isInteger(parsedExpectedTotalCouncillors) ||
            parsedExpectedTotalCouncillors <= 0
        ) {
            return null;
        }

        expectedTotalCouncillors = parsedExpectedTotalCouncillors;
    }

    state = {
        ...state,
        total_committee_seats: totalCommitteeSeats,
        expected_total_councillors: expectedTotalCouncillors,
        political_groups: mergePoliticalGroupsIntoCouncilSetupForm(politicalGroups),
        error: null,
        warning: null,
    };

    if (urlStep === URL_STEP_GROUPS || urlStep === URL_STEP_SETUP || urlStep === URL_STEP_INDEPENDENTS) {
        if (urlStep === URL_STEP_INDEPENDENTS) {
            return applyExampleCouncilPoliticalGroupsIfMissing({
                ...state,
                allocate_seats_to_independents: getInitialIndependentAllocationChoice(
                    state.data_source_mode,
                    state.selected_example_council_id,
                    state.allocate_seats_to_independents
                ),
            });
        }

        return applyExampleCouncilPoliticalGroupsIfMissing(state);
    }

    const independentsParameter = parameters.get(URL_PARAM_INDEPENDENTS);
    const parsedAllocateSeatsToIndependents =
        parseAllocateSeatsToIndependentsParameter(independentsParameter);
    if (independentsParameter !== null && parsedAllocateSeatsToIndependents === null) {
        return null;
    }

    const defaultAllocateSeatsToIndependents =
        state.data_source_mode === DATA_SOURCE_MODE_EXAMPLE
            ? getDefaultAllocateSeatsToIndependentsForExampleCouncil(state.selected_example_council_id)
            : null;

    state = {
        ...state,
        allocate_seats_to_independents:
            parsedAllocateSeatsToIndependents ?? defaultAllocateSeatsToIndependents ?? true,
    };

    const wizardStep =
        urlStep === URL_STEP_NEXT_STEPS ? WIZARD_STEP_NEXT_STEPS : WIZARD_STEP_PARTY_ALLOCATION;

    return buildAllocationState(applyExampleCouncilPoliticalGroupsIfMissing(state), wizardStep);
}
