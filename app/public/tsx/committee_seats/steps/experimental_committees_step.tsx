import {h} from "preact";
import {CommitteesEditor} from "../committees_editor";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "../experimental_seat_distribution";
import type {Committee} from "../types";

export interface ExperimentalCommitteesStepProps {
    committees: Committee[];
    expectedTotalCommitteeSeats: number;
    committeeSeatsStatusMessage: string;
    committeeSeatsStatusMatches: boolean;
    canContinueFromCommittees: boolean;
    onCommitteeNameChange: (committeeIndex: number, name: string) => void;
    onCommitteeSeatCountChange: (committeeIndex: number, seatCount: number) => void;
    onAddCommittee: (committeeIndex: number, name: string) => void;
    onContinueFromCommittees: () => void;
    onBackToResults: () => void;
}

export function ExperimentalCommitteesStep(props: ExperimentalCommitteesStepProps) {
    return (
        <div className="committee_seats_step committee_seats_experimental_step">
            <h2 className="committee_seats_experimental_heading">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.page_heading}
            </h2>
            <p className="committee_seats_lead">{EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.committees_intro}</p>

            <CommitteesEditor
                committees={props.committees}
                expectedTotalCommitteeSeats={props.expectedTotalCommitteeSeats}
                committeeSeatsStatusMessage={props.committeeSeatsStatusMessage}
                committeeSeatsStatusMatches={props.committeeSeatsStatusMatches}
                canContinueFromCommittees={props.canContinueFromCommittees}
                onCommitteeNameChange={props.onCommitteeNameChange}
                onCommitteeSeatCountChange={props.onCommitteeSeatCountChange}
                onAddCommittee={props.onAddCommittee}
                onContinueFromCommittees={props.onContinueFromCommittees}
            />

            <div className="committee_seats_actions">
                <button className="button_standard" type="button" onClick={() => props.onBackToResults()}>
                    Back to results
                </button>
            </div>
        </div>
    );
}
