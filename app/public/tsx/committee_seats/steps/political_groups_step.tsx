import {h} from "preact";
import {PoliticalGroupsEditor} from "../political_groups_editor";
import {CommitteeSeatsCouncilSetupIntro} from "../panel_wizard_chrome";
import type {PoliticalGroup} from "../types";

export interface PoliticalGroupsStepProps {
    politicalGroups: PoliticalGroup[];
    expectedTotalCouncillors: number;
    groupTotalStatusMessage: string;
    groupTotalStatusMatches: boolean;
    canContinueFromPoliticalGroups: boolean;
    error: string | null;
    warning: string | null;
    councilSetupIntroMessage: string;
    onGroupNameChange: (groupIndex: number, name: string) => void;
    onGroupCountChange: (groupIndex: number, councillorCount: number) => void;
    onGroupCountAdjust: (groupIndex: number, adjustment: number) => void;
    onContinueFromPoliticalGroups: () => void;
    onBackFromPoliticalGroups: () => void;
}

export function PoliticalGroupsStep(props: PoliticalGroupsStepProps) {
    return (
        <div className="committee_seats_step">
            <CommitteeSeatsCouncilSetupIntro introMessage={props.councilSetupIntroMessage} />

            <PoliticalGroupsEditor
                politicalGroups={props.politicalGroups}
                expectedTotalCouncillors={props.expectedTotalCouncillors}
                groupTotalStatusMessage={props.groupTotalStatusMessage}
                groupTotalStatusMatches={props.groupTotalStatusMatches}
                canContinueFromPoliticalGroups={props.canContinueFromPoliticalGroups}
                onGroupNameChange={props.onGroupNameChange}
                onGroupCountChange={props.onGroupCountChange}
                onGroupCountAdjust={props.onGroupCountAdjust}
                onContinueFromPoliticalGroups={props.onContinueFromPoliticalGroups}
            />

            {props.error && <div className="error">{props.error}</div>}
            {props.warning && <div className="committee_seats_warning">{props.warning}</div>}

            <div className="committee_seats_actions">
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onBackFromPoliticalGroups()}
                >
                    Back
                </button>
            </div>
        </div>
    );
}
