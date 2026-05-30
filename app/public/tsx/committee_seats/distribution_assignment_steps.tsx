import {h} from "preact";
import {
    canAssignDistributionStep,
    formatAssignmentStepHeading,
    getAssignmentStepDataSummary,
    getDefaultCommitteeIndexForAssignmentStep,
    getEligibleCommitteeIndicesForAssignmentStep,
    getFirstUnassignedAssignmentStepIndex,
} from "./calculate_committee_distribution";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import type {CommitteeDistributionState} from "./types";

export interface DistributionAssignmentStepsProps {
    distributionState: CommitteeDistributionState;
    pendingCommitteeByStepIndex: number[];
    onPendingCommitteeChange: (stepIndex: number, committeeIndex: number) => void;
    onAssignStep: (stepIndex: number) => void;
    onUndoStep: (stepIndex: number) => void;
}

export function DistributionAssignmentSteps(props: DistributionAssignmentStepsProps) {
    const firstUnassignedStepIndex = getFirstUnassignedAssignmentStepIndex(props.distributionState);

    if (props.distributionState.assignment_steps.length === 0) {
        return (
            <p className="committee_seats_note">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_none_needed_message}
            </p>
        );
    }

    return (
        <ol className="committee_seats_distribution_assignment_steps">
            {props.distributionState.assignment_steps.map((assignmentStep, stepIndex) => {
                const chosenCommitteeIndex = props.distributionState.assignment_choices[stepIndex];
                const isCompleted = chosenCommitteeIndex !== null;
                const isActive = firstUnassignedStepIndex === stepIndex;
                const isLocked = !isCompleted && !isActive;
                const eligibleCommitteeIndices = getEligibleCommitteeIndicesForAssignmentStep(
                    props.distributionState,
                    stepIndex
                );
                const pendingCommitteeIndex = getDefaultCommitteeIndexForAssignmentStep(
                    props.distributionState,
                    stepIndex,
                    props.pendingCommitteeByStepIndex[stepIndex]
                );
                const chosenCommittee =
                    chosenCommitteeIndex === null
                        ? null
                        : props.distributionState.committees[chosenCommitteeIndex];
                const selectId = "committee_distribution_committee_choice_" + stepIndex;

                let stepStatusClass = "committee_seats_distribution_assignment_step_locked";
                if (isCompleted) {
                    stepStatusClass = "committee_seats_distribution_assignment_step_completed";
                } else if (isActive) {
                    stepStatusClass = "committee_seats_distribution_assignment_step_active";
                }

                if (isCompleted && chosenCommittee !== null) {
                    return (
                        <li
                            key={assignmentStep.step_number}
                            className={
                                "committee_seats_distribution_assignment_step " + stepStatusClass
                            }
                        >
                            <div className="committee_seats_distribution_assignment_step_completed_line">
                                <span className="committee_seats_distribution_assignment_step_completed_title">
                                    {formatAssignmentStepHeading(
                                        assignmentStep.step_number,
                                        assignmentStep.group_name
                                    )}
                                </span>
                                <span className="committee_seats_distribution_assignment_step_completed_separator">
                                    —
                                </span>
                                <span className="committee_seats_distribution_assignment_step_completed_assignment">
                                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_chosen_label}{" "}
                                    <strong>{chosenCommittee.name}</strong>
                                </span>
                                <button
                                    className="button_standard committee_seats_distribution_assignment_undo_button"
                                    type="button"
                                    onClick={() => props.onUndoStep(stepIndex)}
                                >
                                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.undo_assignment_button_label}
                                </button>
                            </div>
                        </li>
                    );
                }

                return (
                    <li
                        key={assignmentStep.step_number}
                        className={
                            "committee_seats_distribution_assignment_step " + stepStatusClass
                        }
                    >
                        <p className="committee_seats_distribution_assignment_step_heading">
                            {formatAssignmentStepHeading(
                                assignmentStep.step_number,
                                assignmentStep.group_name
                            )}
                        </p>
                        <p className="committee_seats_distribution_assignment_step_data">
                            {getAssignmentStepDataSummary(props.distributionState, stepIndex)}
                        </p>

                        <div className="committee_seats_distribution_assignment_step_controls">
                            {isLocked && (
                                <p className="committee_seats_note committee_seats_distribution_assignment_step_locked_note">
                                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_locked_note}
                                </p>
                            )}
                            {isActive && (
                                <div className="committee_seats_distribution_assignment_committee_row">
                                        <label
                                            className="committee_seats_visually_hidden"
                                            htmlFor={selectId}
                                        >
                                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.choose_committee_prompt}
                                        </label>
                                        <select
                                            id={selectId}
                                            value={String(pendingCommitteeIndex)}
                                            onChange={(event: h.JSX.TargetedEvent<HTMLSelectElement, Event>) =>
                                                props.onPendingCommitteeChange(
                                                    stepIndex,
                                                    parseInt(event.currentTarget.value, 10)
                                                )
                                            }
                                        >
                                            {eligibleCommitteeIndices.map((committeeIndex) => {
                                                const committee =
                                                    props.distributionState.committees[committeeIndex];

                                                return (
                                                    <option
                                                        key={committeeIndex}
                                                        value={String(committeeIndex)}
                                                    >
                                                        {committee.name}
                                                    </option>
                                                );
                                            })}
                                        </select>
                                        <button
                                            className="button_standard"
                                            type="button"
                                            disabled={!canAssignDistributionStep(
                                                props.distributionState,
                                                stepIndex
                                            )}
                                            onClick={() => props.onAssignStep(stepIndex)}
                                        >
                                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assign_button_label}
                                        </button>
                                </div>
                            )}
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
