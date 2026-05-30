import {h, Component} from "preact";
import {calculatePartyAllocation, validateCouncilSetup} from "./committee_seats/calculate_party_allocation";
import {
    applyExampleCouncilToFormState,
    getExampleCouncilById,
} from "./committee_seats/example_councils";
import {
    councilFormHasIndependentCouncillors,
    getInitialIndependentAllocationChoice,
} from "./committee_seats/independent_allocation";
import {PartyAllocationStepView} from "./committee_seats/allocation_workbook";
import {
    clampGroupCountAfterAdjustForPanel,
    clampGroupCountForPanel,
    resolveFormPoliticalGroups,
} from "./committee_seats/political_groups_editor";
import {
    createEmptyCouncilSetupPoliticalGroups,
    isStandardPoliticalGroupRowIndex,
} from "./committee_seats/political_groups_form";
import {
    CouncilSetupSubstep,
    DataSourceMode,
    getCouncilSetupInputForAllocation,
    getCouncilSetupValidationInput,
    getDefaultPanelState,
    isCouncilTotalsCompleteForPoliticalGroups,
    panelStateFromUrlRestore,
    WizardStep,
    type CommitteeSeatsPanelProps,
    type CommitteeSeatsPanelState,
} from "./committee_seats/panel_state";
import {
    CommitteeSeatsAppHeader,
    CommitteeSeatsStepIndicator,
    CommitteeSeatsWizardStepTrail,
} from "./committee_seats/panel_wizard_chrome";
import {CouncilSetupStep} from "./committee_seats/steps/council_setup_step";
import {NextStepsStep} from "./committee_seats/steps/next_steps_step";
import {copyTextToClipboard} from "./committee_seats/submit_example_council";
import {
    formatCommitteeSeatsUrlSearch,
    restoreCommitteeSeatsStateFromUrl,
} from "./committee_seats/url_state";
import {
    getWizardDisplayStepNumber,
    WizardDisplayStep,
} from "./committee_seats/wizard_display_step";

export type {CommitteeSeatsPanelProps} from "./committee_seats/panel_state";

export class CommitteeSeatsPanel extends Component<CommitteeSeatsPanelProps, CommitteeSeatsPanelState> {
    private popstateListener: (() => void) | null = null;
    private exampleCouncilJsonCopyFeedbackTimeout: number | null = null;
    private exampleCouncilJsonTextarea: HTMLTextAreaElement | null = null;

    constructor(props: CommitteeSeatsPanelProps) {
        super(props);
        const restoredFromUrl = restoreCommitteeSeatsStateFromUrl(window.location.search);
        this.state = panelStateFromUrlRestore(restoredFromUrl);
    }

    componentDidMount(): void {
        this.syncUrlFromState();

        this.popstateListener = () => {
            const restoredFromUrl = restoreCommitteeSeatsStateFromUrl(window.location.search);
            this.setState(panelStateFromUrlRestore(restoredFromUrl));
        };
        window.addEventListener("popstate", this.popstateListener);
    }

    componentWillUnmount(): void {
        if (this.popstateListener !== null) {
            window.removeEventListener("popstate", this.popstateListener);
        }

        if (this.exampleCouncilJsonCopyFeedbackTimeout !== null) {
            window.clearTimeout(this.exampleCouncilJsonCopyFeedbackTimeout);
        }
    }

    componentDidUpdate(_previousProps: CommitteeSeatsPanelProps, previousState: CommitteeSeatsPanelState): void {
        if (formatCommitteeSeatsUrlSearch(previousState) !== formatCommitteeSeatsUrlSearch(this.state)) {
            this.syncUrlFromState();
        }
    }

    syncUrlFromState(): void {
        const desiredSearch = formatCommitteeSeatsUrlSearch(this.state);
        const currentSearch = window.location.search;
        if (desiredSearch === currentSearch) {
            return;
        }

        const path = window.location.pathname;
        window.history.replaceState(null, "", desiredSearch === "" ? path : path + desiredSearch);
    }

    handleSelectedExampleCouncilChange(exampleCouncilId: string): void {
        this.setState({
            selected_example_council_id: exampleCouncilId,
            error: null,
            warning: null,
        });
    }

    handleChooseCustomData(): void {
        this.setState({
            council_setup_substep: CouncilSetupSubstep.EnterCouncilTotals,
            data_source_mode: DataSourceMode.Custom,
            political_groups: createEmptyCouncilSetupPoliticalGroups(),
            committees: [],
            total_committee_seats: 0,
            expected_total_councillors: 0,
            allocate_seats_to_independents: null,
            error: null,
            warning: null,
        });
    }

    handleChooseExampleCouncil(): void {
        const exampleCouncil = getExampleCouncilById(this.state.selected_example_council_id);
        if (exampleCouncil === undefined) {
            return;
        }

        const applied = applyExampleCouncilToFormState(exampleCouncil);

        this.setState({
            council_setup_substep: CouncilSetupSubstep.EnterCouncilTotals,
            data_source_mode: DataSourceMode.Example,
            political_groups: applied.political_groups,
            committees: applied.committees,
            total_committee_seats: applied.total_committee_seats,
            expected_total_councillors: applied.expected_total_councillors,
            allocate_seats_to_independents: null,
            error: null,
            warning: null,
        });
    }

    handleBackToChooseDataSource(): void {
        this.setState({
            council_setup_substep: CouncilSetupSubstep.ChooseDataSource,
            error: null,
            warning: null,
        });
    }

    handleGroupNameChange(groupIndex: number, name: string): void {
        if (isStandardPoliticalGroupRowIndex(groupIndex)) {
            return;
        }

        const politicalGroups = this.state.political_groups.map((politicalGroup, index) => {
            if (index !== groupIndex) {
                return politicalGroup;
            }

            return {
                ...politicalGroup,
                name,
            };
        });

        this.setState({political_groups: politicalGroups, error: null, warning: null});
    }

    handleGroupCountChange(groupIndex: number, councillorCount: number): void {
        const clampedCount = clampGroupCountForPanel(
            this.state.political_groups,
            groupIndex,
            councillorCount,
            this.state.expected_total_councillors
        );

        const formGroups = resolveFormPoliticalGroups(this.state.political_groups);

        const politicalGroups = formGroups.map((politicalGroup, index) => {
            if (index !== groupIndex) {
                return politicalGroup;
            }

            return {
                ...politicalGroup,
                councillor_count: clampedCount,
            };
        });

        this.setState({political_groups: politicalGroups, error: null, warning: null});
    }

    handleGroupCountAdjust(groupIndex: number, adjustment: number): void {
        const clampedCount = clampGroupCountAfterAdjustForPanel(
            this.state.political_groups,
            groupIndex,
            adjustment,
            this.state.expected_total_councillors
        );

        this.handleGroupCountChange(groupIndex, clampedCount);
    }

    handleTotalCommitteeSeatsChange(totalCommitteeSeats: number): void {
        this.setState({
            total_committee_seats: totalCommitteeSeats,
            error: null,
            warning: null,
        });
    }

    handleExpectedTotalCouncillorsChange(expectedTotalCouncillors: number): void {
        this.setState({
            expected_total_councillors: expectedTotalCouncillors,
            error: null,
            warning: null,
        });
    }

    handleContinueFromCouncilTotals(): void {
        if (!isCouncilTotalsCompleteForPoliticalGroups(this.state)) {
            return;
        }

        this.setState({
            council_setup_substep: CouncilSetupSubstep.EnterPoliticalGroups,
            error: null,
            warning: null,
        });
    }

    handleBackFromPoliticalGroups(): void {
        this.setState({
            council_setup_substep: CouncilSetupSubstep.EnterCouncilTotals,
            error: null,
            warning: null,
        });
    }

    handleContinueFromPoliticalGroups(): void {
        const councilSetupValidationInput = getCouncilSetupValidationInput(this.state);
        const validation = validateCouncilSetup(councilSetupValidationInput);
        if (!validation.valid) {
            this.setState({
                error: validation.error,
                warning: validation.warning,
            });
            return;
        }

        if (councilFormHasIndependentCouncillors(this.state.political_groups)) {
            this.setState({
                council_setup_substep: CouncilSetupSubstep.ChooseIndependentAllocation,
                allocate_seats_to_independents: getInitialIndependentAllocationChoice(
                    this.state.data_source_mode,
                    this.state.selected_example_council_id,
                    this.state.allocate_seats_to_independents
                ),
                error: null,
                warning: validation.warning,
            });
            return;
        }

        this.proceedToPartyAllocation(true, validation.warning);
    }

    handleIndependentAllocationChoiceChange(allocateSeatsToIndependents: boolean): void {
        this.setState({
            allocate_seats_to_independents: allocateSeatsToIndependents,
            error: null,
        });
    }

    handleContinueFromIndependentAllocation(): void {
        if (this.state.allocate_seats_to_independents === null) {
            return;
        }

        const councilSetupValidationInput = getCouncilSetupValidationInput(this.state);
        const validation = validateCouncilSetup(councilSetupValidationInput);
        if (!validation.valid) {
            this.setState({
                error: validation.error,
                warning: validation.warning,
            });
            return;
        }

        this.proceedToPartyAllocation(this.state.allocate_seats_to_independents, validation.warning);
    }

    handleBackFromIndependentAllocation(): void {
        this.setState({
            council_setup_substep: CouncilSetupSubstep.EnterPoliticalGroups,
            allocate_seats_to_independents: null,
            allocation_result: null,
            error: null,
            warning: null,
        });
    }

    proceedToPartyAllocation(
        allocateSeatsToIndependents: boolean,
        warning: string | null,
        wizardStep: WizardStep = WizardStep.PartyAllocation
    ): void {
        const councilSetupInput = getCouncilSetupInputForAllocation({
            ...this.state,
            allocate_seats_to_independents: allocateSeatsToIndependents,
        });

        this.setState({
            wizard_step: wizardStep,
            council_setup_substep: CouncilSetupSubstep.ChooseIndependentAllocation,
            allocate_seats_to_independents: allocateSeatsToIndependents,
            error: null,
            warning,
            allocation_result: calculatePartyAllocation(councilSetupInput),
        });
    }

    navigateToWizardDisplayStep(targetDisplayStep: WizardDisplayStep): void {
        if (targetDisplayStep === WizardDisplayStep.ChooseDataSource) {
            this.setState({
                wizard_step: WizardStep.CouncilSetup,
                council_setup_substep: CouncilSetupSubstep.ChooseDataSource,
                allocate_seats_to_independents: null,
                allocation_result: null,
                error: null,
                warning: null,
            });
            return;
        }

        if (targetDisplayStep === WizardDisplayStep.CouncilTotals) {
            this.setState({
                wizard_step: WizardStep.CouncilSetup,
                council_setup_substep: CouncilSetupSubstep.EnterCouncilTotals,
                allocate_seats_to_independents: null,
                allocation_result: null,
                error: null,
                warning: null,
            });
            return;
        }

        if (targetDisplayStep === WizardDisplayStep.PoliticalGroups) {
            this.setState({
                wizard_step: WizardStep.CouncilSetup,
                council_setup_substep: CouncilSetupSubstep.EnterPoliticalGroups,
                allocate_seats_to_independents: null,
                allocation_result: null,
                error: null,
                warning: null,
            });
            return;
        }

        if (targetDisplayStep === WizardDisplayStep.IndependentAllocation) {
            this.setState({
                wizard_step: WizardStep.CouncilSetup,
                council_setup_substep: CouncilSetupSubstep.ChooseIndependentAllocation,
                allocate_seats_to_independents: getInitialIndependentAllocationChoice(
                    this.state.data_source_mode,
                    this.state.selected_example_council_id,
                    this.state.allocate_seats_to_independents
                ),
                allocation_result: null,
                error: null,
                warning: null,
            });
            return;
        }

        if (targetDisplayStep === WizardDisplayStep.PartyAllocation) {
            if (this.state.allocation_result === null && this.state.allocate_seats_to_independents !== null) {
                this.proceedToPartyAllocation(this.state.allocate_seats_to_independents, this.state.warning);
                return;
            }

            this.setState({
                wizard_step: WizardStep.PartyAllocation,
                error: null,
                warning: null,
            });
            return;
        }

        if (targetDisplayStep === WizardDisplayStep.NextSteps) {
            if (this.state.allocation_result === null && this.state.allocate_seats_to_independents !== null) {
                this.proceedToPartyAllocation(
                    this.state.allocate_seats_to_independents,
                    this.state.warning,
                    WizardStep.NextSteps
                );
                return;
            }

            this.setState({
                wizard_step: WizardStep.NextSteps,
                error: null,
                warning: null,
            });
        }
    }

    handleWizardTrailStepClick(targetDisplayStep: WizardDisplayStep): void {
        const activeDisplayStep = getWizardDisplayStepNumber(this.state);
        if (targetDisplayStep >= activeDisplayStep) {
            return;
        }

        this.navigateToWizardDisplayStep(targetDisplayStep);
    }

    handleBackFromPartyAllocation(): void {
        if (councilFormHasIndependentCouncillors(this.state.political_groups)) {
            this.navigateToWizardDisplayStep(WizardDisplayStep.IndependentAllocation);
            return;
        }

        this.navigateToWizardDisplayStep(WizardDisplayStep.PoliticalGroups);
    }

    handleContinueFromPartyAllocation(): void {
        if (this.state.allocation_result === null) {
            return;
        }

        this.setState({
            wizard_step: WizardStep.NextSteps,
            error: null,
        });
    }

    handleBackFromNextSteps(): void {
        this.setState({
            wizard_step: WizardStep.PartyAllocation,
            error: null,
        });
    }

    handleStartOver(): void {
        this.clearExampleCouncilJsonCopyFeedbackTimeout();
        this.setState(getDefaultPanelState());
    }

    handleProposedExampleCouncilNameChange(councilDisplayName: string): void {
        this.setState({
            proposed_example_council_name: councilDisplayName,
            example_council_json_copy_status: "idle",
        });
    }

    clearExampleCouncilJsonCopyFeedbackTimeout(): void {
        if (this.exampleCouncilJsonCopyFeedbackTimeout !== null) {
            window.clearTimeout(this.exampleCouncilJsonCopyFeedbackTimeout);
            this.exampleCouncilJsonCopyFeedbackTimeout = null;
        }
    }

    scheduleExampleCouncilJsonCopyFeedbackReset(): void {
        this.clearExampleCouncilJsonCopyFeedbackTimeout();
        this.exampleCouncilJsonCopyFeedbackTimeout = window.setTimeout(() => {
            this.setState({example_council_json_copy_status: "idle"});
            this.exampleCouncilJsonCopyFeedbackTimeout = null;
        }, 2000);
    }

    async handleCopyExampleCouncilSubmissionJson(json: string): Promise<void> {
        const copied = await copyTextToClipboard(json);

        if (copied) {
            this.setState({example_council_json_copy_status: "copied"});
            this.scheduleExampleCouncilJsonCopyFeedbackReset();
            return;
        }

        this.setState({example_council_json_copy_status: "failed"});
        if (this.exampleCouncilJsonTextarea !== null) {
            this.exampleCouncilJsonTextarea.focus();
            this.exampleCouncilJsonTextarea.select();
        }
        this.scheduleExampleCouncilJsonCopyFeedbackReset();
    }

    render(_props: CommitteeSeatsPanelProps, state: CommitteeSeatsPanelState) {
        return (
            <div className="committee_seats_panel_react">
                <CommitteeSeatsAppHeader />
                <div className="committee_seats_main">
                    <CommitteeSeatsWizardStepTrail
                        state={state}
                        onTrailStepClick={(targetDisplayStep) =>
                            this.handleWizardTrailStepClick(targetDisplayStep)
                        }
                    />
                    {state.wizard_step === WizardStep.CouncilSetup && (
                        <CouncilSetupStep
                            state={state}
                            onSelectedExampleCouncilChange={(exampleCouncilId) =>
                                this.handleSelectedExampleCouncilChange(exampleCouncilId)
                            }
                            onChooseExampleCouncil={() => this.handleChooseExampleCouncil()}
                            onChooseCustomData={() => this.handleChooseCustomData()}
                            onExpectedTotalCouncillorsChange={(expectedTotalCouncillors) =>
                                this.handleExpectedTotalCouncillorsChange(expectedTotalCouncillors)
                            }
                            onTotalCommitteeSeatsChange={(totalCommitteeSeats) =>
                                this.handleTotalCommitteeSeatsChange(totalCommitteeSeats)
                            }
                            onBackToChooseDataSource={() => this.handleBackToChooseDataSource()}
                            onContinueFromCouncilTotals={() => this.handleContinueFromCouncilTotals()}
                            onGroupNameChange={(groupIndex, name) =>
                                this.handleGroupNameChange(groupIndex, name)
                            }
                            onGroupCountChange={(groupIndex, councillorCount) =>
                                this.handleGroupCountChange(groupIndex, councillorCount)
                            }
                            onGroupCountAdjust={(groupIndex, adjustment) =>
                                this.handleGroupCountAdjust(groupIndex, adjustment)
                            }
                            onContinueFromPoliticalGroups={() => this.handleContinueFromPoliticalGroups()}
                            onBackFromPoliticalGroups={() => this.handleBackFromPoliticalGroups()}
                            onIndependentAllocationChoiceChange={(allocateSeatsToIndependents) =>
                                this.handleIndependentAllocationChoiceChange(allocateSeatsToIndependents)
                            }
                            onBackFromIndependentAllocation={() => this.handleBackFromIndependentAllocation()}
                            onContinueFromIndependentAllocation={() =>
                                this.handleContinueFromIndependentAllocation()
                            }
                        />
                    )}
                    {state.wizard_step === WizardStep.PartyAllocation &&
                        state.allocation_result !== null && (
                            <PartyAllocationStepView
                                allocationResult={state.allocation_result}
                                onBack={() => this.handleBackFromPartyAllocation()}
                                onContinue={() => this.handleContinueFromPartyAllocation()}
                            />
                        )}
                    {state.wizard_step === WizardStep.NextSteps && state.allocation_result !== null && (
                        <NextStepsStep
                            state={state}
                            allocationResult={state.allocation_result}
                            onProposedExampleCouncilNameChange={(councilDisplayName) =>
                                this.handleProposedExampleCouncilNameChange(councilDisplayName)
                            }
                            onCopyExampleCouncilSubmissionJson={(json) => {
                                void this.handleCopyExampleCouncilSubmissionJson(json);
                            }}
                            onRegisterExampleCouncilJsonTextarea={(element) => {
                                this.exampleCouncilJsonTextarea = element;
                            }}
                            onBackFromNextSteps={() => this.handleBackFromNextSteps()}
                            onStartOver={() => this.handleStartOver()}
                        />
                    )}
                </div>
                <CommitteeSeatsStepIndicator state={state} />
            </div>
        );
    }
}
