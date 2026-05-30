import {h} from "preact";
import {CouncilTotalsEditor} from "../council_totals_editor";
import {getExampleCouncilById, getPrefilledTotalCommitteeSeats} from "../example_councils";
import {
    CommitteeSeatsCouncilSetupIntro,
} from "../panel_wizard_chrome";
import {DataSourceMode} from "../panel_state";

export interface CouncilTotalsStepProps {
    dataSourceMode: DataSourceMode;
    selectedExampleCouncilId: string;
    expectedTotalCouncillors: number;
    totalCommitteeSeats: number;
    councilTotalsComplete: boolean;
    councilSetupIntroMessage: string;
    onExpectedTotalCouncillorsChange: (expectedTotalCouncillors: number) => void;
    onTotalCommitteeSeatsChange: (totalCommitteeSeats: number) => void;
    onBackToChooseDataSource: () => void;
    onContinueFromCouncilTotals: () => void;
}

export function CouncilTotalsStep(props: CouncilTotalsStepProps) {
    const exampleCouncil =
        props.dataSourceMode === DataSourceMode.Example
            ? getExampleCouncilById(props.selectedExampleCouncilId)
            : undefined;
    const prefilledTotalCommitteeSeats =
        exampleCouncil !== undefined ? getPrefilledTotalCommitteeSeats(exampleCouncil) : null;

    return (
        <div className="committee_seats_step committee_seats_council_totals_screen">
            <CommitteeSeatsCouncilSetupIntro introMessage={props.councilSetupIntroMessage} />

            <CouncilTotalsEditor
                expectedTotalCouncillors={props.expectedTotalCouncillors}
                totalCommitteeSeats={props.totalCommitteeSeats}
                seatAssignmentSourceUrl={exampleCouncil?.seat_assignment_source_url}
                showExampleCommitteeSeatsNote={
                    props.dataSourceMode === DataSourceMode.Example &&
                    prefilledTotalCommitteeSeats === null
                }
                onExpectedTotalCouncillorsChange={props.onExpectedTotalCouncillorsChange}
                onTotalCommitteeSeatsChange={props.onTotalCommitteeSeatsChange}
            />

            <div className="committee_seats_actions">
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onBackToChooseDataSource()}
                >
                    Back
                </button>
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onContinueFromCouncilTotals()}
                    disabled={!props.councilTotalsComplete}
                >
                    Continue
                </button>
            </div>
        </div>
    );
}
