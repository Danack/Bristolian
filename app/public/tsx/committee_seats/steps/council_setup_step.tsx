import {h} from "preact";
import {validateCouncilSetup} from "../calculate_party_allocation";
import {
    CouncilSetupSubstep,
    DataSourceMode,
    formatGroupCouncillorTotalMessage,
    getCouncilSetupValidationInput,
    isCouncilTotalsCompleteForPoliticalGroups,
    type CommitteeSeatsPanelState,
} from "../panel_state";
import {getCouncilSetupIntroMessage, getPoliticalGroupsStepIntroMessage} from "../panel_wizard_chrome";
import {ChooseDataSourceStep} from "./choose_data_source_step";
import {CouncilTotalsStep} from "./council_totals_step";
import {IndependentAllocationStep} from "./independent_allocation_step";
import {PoliticalGroupsStep} from "./political_groups_step";

export interface CouncilSetupStepProps {
    state: CommitteeSeatsPanelState;
    onSelectedExampleCouncilChange: (exampleCouncilId: string) => void;
    onChooseExampleCouncil: () => void;
    onChooseCustomData: () => void;
    onExpectedTotalCouncillorsChange: (expectedTotalCouncillors: number) => void;
    onTotalCommitteeSeatsChange: (totalCommitteeSeats: number) => void;
    onBackToChooseDataSource: () => void;
    onContinueFromCouncilTotals: () => void;
    onGroupNameChange: (groupIndex: number, name: string) => void;
    onGroupCountChange: (groupIndex: number, councillorCount: number) => void;
    onGroupCountAdjust: (groupIndex: number, adjustment: number) => void;
    onContinueFromPoliticalGroups: () => void;
    onBackFromPoliticalGroups: () => void;
    onIndependentAllocationChoiceChange: (allocateSeatsToIndependents: boolean) => void;
    onBackFromIndependentAllocation: () => void;
    onContinueFromIndependentAllocation: () => void;
}

export function CouncilSetupStep(props: CouncilSetupStepProps) {
    const councilSetupIntroMessage = getCouncilSetupIntroMessage(props.state);

    if (props.state.council_setup_substep === CouncilSetupSubstep.ChooseDataSource) {
        return (
            <ChooseDataSourceStep
                selectedExampleCouncilId={props.state.selected_example_council_id}
                onSelectedExampleCouncilChange={props.onSelectedExampleCouncilChange}
                onChooseExampleCouncil={props.onChooseExampleCouncil}
                onChooseCustomData={props.onChooseCustomData}
            />
        );
    }

    if (props.state.council_setup_substep === CouncilSetupSubstep.EnterCouncilTotals) {
        return (
            <CouncilTotalsStep
                dataSourceMode={props.state.data_source_mode}
                selectedExampleCouncilId={props.state.selected_example_council_id}
                expectedTotalCouncillors={props.state.expected_total_councillors}
                totalCommitteeSeats={props.state.total_committee_seats}
                councilTotalsComplete={isCouncilTotalsCompleteForPoliticalGroups(props.state)}
                councilSetupIntroMessage={councilSetupIntroMessage}
                onExpectedTotalCouncillorsChange={props.onExpectedTotalCouncillorsChange}
                onTotalCommitteeSeatsChange={props.onTotalCommitteeSeatsChange}
                onBackToChooseDataSource={props.onBackToChooseDataSource}
                onContinueFromCouncilTotals={props.onContinueFromCouncilTotals}
            />
        );
    }

    if (props.state.council_setup_substep === CouncilSetupSubstep.ChooseIndependentAllocation) {
        return (
            <IndependentAllocationStep
                politicalGroups={props.state.political_groups}
                allocateSeatsToIndependents={props.state.allocate_seats_to_independents}
                onIndependentAllocationChoiceChange={props.onIndependentAllocationChoiceChange}
                onBackFromIndependentAllocation={props.onBackFromIndependentAllocation}
                onContinueFromIndependentAllocation={props.onContinueFromIndependentAllocation}
            />
        );
    }

    const councilSetupValidationInput = getCouncilSetupValidationInput(props.state);
    const validation = validateCouncilSetup(councilSetupValidationInput);
    const additionalGroupNamesEditable = props.state.data_source_mode === DataSourceMode.Custom;
    const councillorTotalMatches =
        validation.total_councillors === props.state.expected_total_councillors &&
        props.state.expected_total_councillors > 0;

    return (
        <PoliticalGroupsStep
            politicalGroups={props.state.political_groups}
            expectedTotalCouncillors={props.state.expected_total_councillors}
            additionalGroupNamesEditable={additionalGroupNamesEditable}
            groupTotalStatusMessage={formatGroupCouncillorTotalMessage(
                validation.total_councillors,
                props.state.expected_total_councillors
            )}
            groupTotalStatusMatches={councillorTotalMatches}
            canContinueFromPoliticalGroups={validation.valid}
            error={props.state.error}
            warning={props.state.warning}
            councilSetupIntroMessage={getPoliticalGroupsStepIntroMessage(props.state)}
            onGroupNameChange={props.onGroupNameChange}
            onGroupCountChange={props.onGroupCountChange}
            onGroupCountAdjust={props.onGroupCountAdjust}
            onContinueFromPoliticalGroups={props.onContinueFromPoliticalGroups}
            onBackFromPoliticalGroups={props.onBackFromPoliticalGroups}
        />
    );
}
