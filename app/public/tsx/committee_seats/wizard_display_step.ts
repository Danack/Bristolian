import type {PoliticalGroup} from "./types";
import {councilFormHasIndependentCouncillors} from "./independent_allocation";
import {TOTAL_WIZARD_DISPLAY_STEPS, WIZARD_DISPLAY_STEPS} from "./page_config";

/** Visible wizard steps: choose source → council totals → political groups → independents → allocation → next steps. */
export enum WizardDisplayStep {
    ChooseDataSource = 1,
    CouncilTotals = 2,
    PoliticalGroups = 3,
    IndependentAllocation = 4,
    PartyAllocation = 5,
    NextSteps = 6,
}

export interface WizardDisplayStepState {
    wizard_step: number;
    council_setup_substep: string;
    political_groups: PoliticalGroup[];
}

const WIZARD_STEP_COUNCIL_SETUP = 1;
const WIZARD_STEP_PARTY_ALLOCATION = 2;
const WIZARD_STEP_NEXT_STEPS = 3;
const COUNCIL_SETUP_SUBSTEP_CHOOSE_DATA_SOURCE = "choose_data_source";
const COUNCIL_SETUP_SUBSTEP_ENTER_COUNCIL_TOTALS = "enter_council_totals";
const COUNCIL_SETUP_SUBSTEP_ENTER_POLITICAL_GROUPS = "enter_political_groups";
const COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION = "choose_independent_allocation";

const WIZARD_STEP_SEAT_DISTRIBUTION_EXPERIMENTAL = 4;

export function getWizardDisplayStepNumber(state: WizardDisplayStepState): WizardDisplayStep {
    if (state.wizard_step === WIZARD_STEP_SEAT_DISTRIBUTION_EXPERIMENTAL) {
        return WizardDisplayStep.NextSteps;
    }

    if (state.wizard_step === WIZARD_STEP_NEXT_STEPS) {
        return WizardDisplayStep.NextSteps;
    }

    if (state.wizard_step === WIZARD_STEP_PARTY_ALLOCATION) {
        return WizardDisplayStep.PartyAllocation;
    }

    if (state.council_setup_substep === COUNCIL_SETUP_SUBSTEP_CHOOSE_DATA_SOURCE) {
        return WizardDisplayStep.ChooseDataSource;
    }

    if (state.council_setup_substep === COUNCIL_SETUP_SUBSTEP_ENTER_COUNCIL_TOTALS) {
        return WizardDisplayStep.CouncilTotals;
    }

    if (state.council_setup_substep === COUNCIL_SETUP_SUBSTEP_ENTER_POLITICAL_GROUPS) {
        return WizardDisplayStep.PoliticalGroups;
    }

    if (state.council_setup_substep === COUNCIL_SETUP_SUBSTEP_CHOOSE_INDEPENDENT_ALLOCATION) {
        return WizardDisplayStep.IndependentAllocation;
    }

    return WizardDisplayStep.CouncilTotals;
}

export function getVisibleWizardDisplaySteps(state: WizardDisplayStepState) {
    if (!councilFormHasIndependentCouncillors(state.political_groups)) {
        return WIZARD_DISPLAY_STEPS.filter(
            (wizardDisplayStep) => wizardDisplayStep.step_number !== WizardDisplayStep.IndependentAllocation
        );
    }

    return WIZARD_DISPLAY_STEPS;
}

export function getWizardDisplayStepLabel(
    state: WizardDisplayStepState,
    activeDisplayStep: WizardDisplayStep
): string {
    const visibleWizardSteps = getVisibleWizardDisplaySteps(state);
    const visibleStep = visibleWizardSteps.find(
        (wizardDisplayStep) => wizardDisplayStep.step_number === activeDisplayStep
    );
    if (visibleStep !== undefined) {
        return visibleStep.label;
    }

    const wizardStep = WIZARD_DISPLAY_STEPS.find(
        (wizardDisplayStep) => wizardDisplayStep.step_number === activeDisplayStep
    );
    if (wizardStep !== undefined) {
        return wizardStep.label;
    }

    return WIZARD_DISPLAY_STEPS[WIZARD_DISPLAY_STEPS.length - 1].label;
}

export function getVisibleWizardDisplayStepPosition(
    state: WizardDisplayStepState,
    activeDisplayStep: WizardDisplayStep
): number {
    const visibleWizardSteps = getVisibleWizardDisplaySteps(state);
    const stepIndex = visibleWizardSteps.findIndex(
        (wizardDisplayStep) => wizardDisplayStep.step_number === activeDisplayStep
    );

    return stepIndex === -1 ? activeDisplayStep : stepIndex + 1;
}

export function getWizardDisplayStepsRemaining(
    state: WizardDisplayStepState,
    activeDisplayStep: WizardDisplayStep
): number {
    const visibleWizardSteps = getVisibleWizardDisplaySteps(state);
    const stepIndex = visibleWizardSteps.findIndex(
        (wizardDisplayStep) => wizardDisplayStep.step_number === activeDisplayStep
    );

    if (stepIndex === -1) {
        return TOTAL_WIZARD_DISPLAY_STEPS - activeDisplayStep;
    }

    return visibleWizardSteps.length - stepIndex - 1;
}
