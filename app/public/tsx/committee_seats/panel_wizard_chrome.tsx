import {h} from "preact";
import {getExampleCouncilById} from "./example_councils";
import {
    COMMITTEE_SEATS_PAGE,
    formatCouncilSetupExampleIntro,
    formatCouncilSetupExampleIntroForPoliticalGroups,
} from "./page_config";
import {
    DataSourceMode,
    type CommitteeSeatsPanelState,
} from "./panel_state";
import {
    getVisibleWizardDisplayStepPosition,
    getVisibleWizardDisplaySteps,
    getWizardDisplayStepLabel,
    getWizardDisplayStepNumber,
    getWizardDisplayStepsRemaining,
    type WizardDisplayStep,
} from "./wizard_display_step";

export function CommitteeSeatsAppHeader() {
    return (
        <header className="committee_seats_app_header">
            <h1>{COMMITTEE_SEATS_PAGE.title}</h1>
            <p className="committee_seats_app_tagline">{COMMITTEE_SEATS_PAGE.tagline}</p>
        </header>
    );
}

export function getCouncilSetupIntroMessage(state: CommitteeSeatsPanelState): string {
    if (state.data_source_mode === DataSourceMode.Custom) {
        return COMMITTEE_SEATS_PAGE.council_setup_custom_intro;
    }

    const exampleCouncil = getExampleCouncilById(state.selected_example_council_id);

    return formatCouncilSetupExampleIntro(exampleCouncil?.display_name ?? "an example council");
}

export function getPoliticalGroupsStepIntroMessage(state: CommitteeSeatsPanelState): string {
    if (state.data_source_mode === DataSourceMode.Custom) {
        return COMMITTEE_SEATS_PAGE.council_setup_custom_intro;
    }

    const exampleCouncil = getExampleCouncilById(state.selected_example_council_id);

    return formatCouncilSetupExampleIntroForPoliticalGroups(
        exampleCouncil?.display_name ?? "an example council"
    );
}

export function CommitteeSeatsCouncilSetupIntro(props: {introMessage: string}) {
    return <p className="committee_seats_council_setup_intro">{props.introMessage}</p>;
}

export interface CommitteeSeatsWizardStepTrailProps {
    state: CommitteeSeatsPanelState;
    onTrailStepClick: (targetDisplayStep: WizardDisplayStep) => void;
}

export function CommitteeSeatsWizardStepTrail(props: CommitteeSeatsWizardStepTrailProps) {
    const activeDisplayStep = getWizardDisplayStepNumber(props.state);
    const visibleWizardSteps = getVisibleWizardDisplaySteps(props.state).filter(
        (wizardDisplayStep) => wizardDisplayStep.step_number <= activeDisplayStep
    );

    return (
        <nav className="committee_seats_wizard_trail" aria-label="Wizard steps">
            <ol className="committee_seats_wizard_trail_list">
                {visibleWizardSteps.map((wizardDisplayStep, visibleStepIndex) => {
                    const isActive = wizardDisplayStep.step_number === activeDisplayStep;
                    const isPast = wizardDisplayStep.step_number < activeDisplayStep;
                    const stepClassName = isActive
                        ? "committee_seats_wizard_trail_step committee_seats_wizard_trail_step_active"
                        : "committee_seats_wizard_trail_step committee_seats_wizard_trail_step_past";

                    const stepLabel = wizardDisplayStep.label;
                    const targetDisplayStep = wizardDisplayStep.step_number as WizardDisplayStep;

                    return (
                        <li
                            key={wizardDisplayStep.step_number}
                            className="committee_seats_wizard_trail_item"
                            aria-current={isActive ? "step" : undefined}
                        >
                            {visibleStepIndex > 0 && (
                                <span
                                    className="committee_seats_wizard_trail_separator"
                                    aria-hidden="true"
                                >
                                    &gt;
                                </span>
                            )}
                            {isPast ? (
                                <button
                                    type="button"
                                    className={
                                        stepClassName + " committee_seats_wizard_trail_step_button"
                                    }
                                    onClick={() => props.onTrailStepClick(targetDisplayStep)}
                                >
                                    {stepLabel}
                                </button>
                            ) : (
                                <span className={stepClassName}>{stepLabel}</span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}

export function CommitteeSeatsStepIndicator(props: {state: CommitteeSeatsPanelState}) {
    const activeDisplayStep = getWizardDisplayStepNumber(props.state);
    const visibleWizardSteps = getVisibleWizardDisplaySteps(props.state);
    const activeStepLabel = getWizardDisplayStepLabel(props.state, activeDisplayStep);
    const activeStepPosition = getVisibleWizardDisplayStepPosition(props.state, activeDisplayStep);
    const stepsRemaining = getWizardDisplayStepsRemaining(props.state, activeDisplayStep);

    const stepsRemainingLabel =
        stepsRemaining === 0
            ? "Final step"
            : stepsRemaining + " step" + (stepsRemaining === 1 ? "" : "s") + " remaining";

    return (
        <div className="committee_seats_step_indicator" aria-live="polite">
            <span className="committee_seats_step_indicator_primary">
                {activeStepLabel} ({activeStepPosition} of {visibleWizardSteps.length})
            </span>
            <span className="committee_seats_step_indicator_secondary">{stepsRemainingLabel}</span>
        </div>
    );
}
