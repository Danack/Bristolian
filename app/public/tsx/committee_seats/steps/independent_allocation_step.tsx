import {h} from "preact";
import {
    formatIndependentCouncillorCountEnteredMessage,
    getIndependentCouncillorCountFromForm,
    INDEPENDENT_ALLOCATION_STEP_COPY,
} from "../independent_allocation";
import type {PoliticalGroup} from "../types";

export interface IndependentAllocationStepProps {
    politicalGroups: PoliticalGroup[];
    allocateSeatsToIndependents: boolean | null;
    onIndependentAllocationChoiceChange: (allocateSeatsToIndependents: boolean) => void;
    onBackFromIndependentAllocation: () => void;
    onContinueFromIndependentAllocation: () => void;
}

export function IndependentAllocationStep(props: IndependentAllocationStepProps) {
    const independentAllocationChoiceMade = props.allocateSeatsToIndependents !== null;

    return (
        <div className="committee_seats_step committee_seats_choose_source_screen">
            <p className="committee_seats_choose_source_lead">{INDEPENDENT_ALLOCATION_STEP_COPY.lead}</p>
            <fieldset className="committee_seats_independent_allocation_fieldset">
                <legend className="committee_seats_visually_hidden">
                    {INDEPENDENT_ALLOCATION_STEP_COPY.lead}
                </legend>
                <label className="committee_seats_independent_allocation_option">
                    <input
                        type="radio"
                        name="allocate_seats_to_independents"
                        checked={props.allocateSeatsToIndependents === true}
                        onChange={() => props.onIndependentAllocationChoiceChange(true)}
                    />
                    {INDEPENDENT_ALLOCATION_STEP_COPY.yes_label}
                </label>
                <label className="committee_seats_independent_allocation_option">
                    <input
                        type="radio"
                        name="allocate_seats_to_independents"
                        checked={props.allocateSeatsToIndependents === false}
                        onChange={() => props.onIndependentAllocationChoiceChange(false)}
                    />
                    {INDEPENDENT_ALLOCATION_STEP_COPY.no_label}
                </label>
            </fieldset>
            <p className="committee_seats_note committee_seats_independent_allocation_consequence_note">
                {INDEPENDENT_ALLOCATION_STEP_COPY.consequence_note}
            </p>
            <p className="committee_seats_independent_allocation_count">
                {formatIndependentCouncillorCountEnteredMessage(
                    getIndependentCouncillorCountFromForm(props.politicalGroups)
                )}
            </p>
            <div className="committee_seats_actions">
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onBackFromIndependentAllocation()}
                >
                    Back
                </button>
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onContinueFromIndependentAllocation()}
                    disabled={!independentAllocationChoiceMade}
                >
                    Continue
                </button>
            </div>
        </div>
    );
}
