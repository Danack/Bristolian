import {h} from "preact";
import {useEffect, useState} from "preact/hooks";
import {
    canAddPendingCommitteeSelectionOnCommittee,
    countPendingSelectionsForCommittee,
    getAssignmentStepDataSummaryParts,
    getFirstUnassignedAssignmentStepIndex,
    getLaterPartyGroupNamesForRemainderAssignment,
    getPartyAssignmentBatch,
    getGroupsAfterInAssignmentTurnOrder,
    getPendingCommitteeSelectionDisabledReason,
    getPendingCommitteeSelectionDisabledReasonKinds,
    getPreviousGroupInAssignmentTurnOrder,
    isPendingPartyAssignmentBatchReadyToConfirm,
    type PendingCommitteeSelectionDisabledReason,
} from "./calculate_committee_distribution";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import type {CommitteeDistributionState} from "./types";

export function formatPendingCommitteeSelectionDisabledReasonMessage(
    reasonKind: PendingCommitteeSelectionDisabledReason,
    committeeName: string,
    batchGroupName: string,
    laterPartyGroupNames: string[]
): string {
    switch (reasonKind) {
        case "committee_full":
            return EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_committee_full_message(
                committeeName
            );
        case "group_cap_reached":
            return EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_group_cap_message(
                batchGroupName
            );
        case "later_party_remainder":
            return EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_later_party_message(
                committeeName,
                laterPartyGroupNames
            );
        case "would_block_current_party_batch":
            return EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_batch_spread_message(
                batchGroupName
            );
    }
}

function getDisabledCommitteeSelectionNotes(
    batchGroupName: string,
    laterPartyGroupNames: string[],
    disabledReasonKinds: PendingCommitteeSelectionDisabledReason[]
): string[] {
    const notes: string[] = [];

    for (const reasonKind of disabledReasonKinds) {
        switch (reasonKind) {
            case "committee_full":
                notes.push(
                    EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_committee_full_note
                );
                break;
            case "group_cap_reached":
                notes.push(
                    EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_group_cap_note(
                        batchGroupName
                    )
                );
                break;
            case "later_party_remainder":
                notes.push(
                    EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_later_party_note(
                        laterPartyGroupNames
                    )
                );
                break;
            case "would_block_current_party_batch":
                notes.push(
                    EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_batch_spread_note(
                        batchGroupName
                    )
                );
                break;
        }
    }

    return notes;
}

interface DisabledCommitteeModalState {
    committeeName: string;
    message: string;
}

export interface DistributionAssignmentStepsProps {
    distributionState: CommitteeDistributionState;
    pendingCommitteeSelections: number[];
    onCommitteeChoiceClick: (committeeIndex: number) => void;
    onClearPendingSelections: () => void;
    onConfirmAssignmentBatch: () => void;
    onGoBackToPreviousGroup: () => void;
}

export function DistributionAssignmentSteps(props: DistributionAssignmentStepsProps) {
    const [disabledCommitteeModal, setDisabledCommitteeModal] =
        useState<DisabledCommitteeModalState | null>(null);

    useEffect(() => {
        if (disabledCommitteeModal === null) {
            return;
        }

        const handleEscapeKey = (event: KeyboardEvent): void => {
            if (event.key === "Escape") {
                setDisabledCommitteeModal(null);
            }
        };

        window.addEventListener("keydown", handleEscapeKey);

        return () => {
            window.removeEventListener("keydown", handleEscapeKey);
        };
    }, [disabledCommitteeModal]);

    const firstUnassignedStepIndex = getFirstUnassignedAssignmentStepIndex(props.distributionState);

    if (props.distributionState.assignment_steps.length === 0) {
        return (
            <p className="committee_seats_note">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_none_needed_message}
            </p>
        );
    }

    if (firstUnassignedStepIndex === null) {
        return null;
    }

    const batch = getPartyAssignmentBatch(props.distributionState, firstUnassignedStepIndex);
    if (batch === null) {
        return null;
    }

    const previousGroupName = getPreviousGroupInAssignmentTurnOrder(
        props.distributionState,
        batch.group_name
    );
    const summaryParts = getAssignmentStepDataSummaryParts(
        props.distributionState,
        batch.first_step_index,
        props.pendingCommitteeSelections
    );
    const confirmEnabled = isPendingPartyAssignmentBatchReadyToConfirm(
        props.distributionState,
        batch,
        props.pendingCommitteeSelections
    );
    const allCommitteeChoicesMade =
        batch.seats_to_choose > 0 &&
        props.pendingCommitteeSelections.length >= batch.seats_to_choose &&
        summaryParts === null;

    const laterPartyGroupNames = getLaterPartyGroupNamesForRemainderAssignment(
        props.distributionState,
        batch.group_name
    );
    const groupsAfterInTurnOrder = getGroupsAfterInAssignmentTurnOrder(
        props.distributionState,
        batch.group_name
    );
    const nextGroupName = groupsAfterInTurnOrder[0] ?? null;
    const disabledCommitteeNotes = getDisabledCommitteeSelectionNotes(
        batch.group_name,
        laterPartyGroupNames,
        getPendingCommitteeSelectionDisabledReasonKinds(
            props.distributionState,
            batch,
            props.pendingCommitteeSelections
        )
    );

    const handleCommitteeButtonClick = (committeeIndex: number, committeeName: string): void => {
        const selectionCount = countPendingSelectionsForCommittee(
            props.pendingCommitteeSelections,
            committeeIndex
        );
        const canAddSelection = canAddPendingCommitteeSelectionOnCommittee(
            props.distributionState,
            batch,
            props.pendingCommitteeSelections,
            committeeIndex
        );

        if (selectionCount > 0 || canAddSelection) {
            props.onCommitteeChoiceClick(committeeIndex);
            return;
        }

        const disabledReason = getPendingCommitteeSelectionDisabledReason(
            props.distributionState,
            batch,
            props.pendingCommitteeSelections,
            committeeIndex
        );

        if (disabledReason === null) {
            return;
        }

        setDisabledCommitteeModal({
            committeeName,
            message: formatPendingCommitteeSelectionDisabledReasonMessage(
                disabledReason,
                committeeName,
                batch.group_name,
                laterPartyGroupNames
            ),
        });
    };

    return (
        <div className="committee_seats_distribution_assignment_steps">
            <div className="committee_seats_distribution_assignment_current">
                {allCommitteeChoicesMade ? (
                    <p className="committee_seats_distribution_assignment_step_data">
                        {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_group_chosen_committees_message(
                            batch.group_name
                        )}
                    </p>
                ) : (
                    summaryParts !== null && (
                        <p className="committee_seats_distribution_assignment_step_data">
                            The {summaryParts.group_name} Group currently has{" "}
                            {summaryParts.current_allocated} committee seats allocated, and needs to choose{" "}
                            <strong>{summaryParts.seats_still_needed}</strong> more.
                        </p>
                    )
                )}

                <div
                    className="committee_seats_distribution_assignment_committee_buttons"
                    role="group"
                    aria-label={EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.choose_committee_prompt}
                >
                    {props.distributionState.committees.map((committee, committeeIndex) => {
                        const selectionCount = countPendingSelectionsForCommittee(
                            props.pendingCommitteeSelections,
                            committeeIndex
                        );
                        const isSelected = selectionCount > 0;
                        const canAddSelection = canAddPendingCommitteeSelectionOnCommittee(
                            props.distributionState,
                            batch,
                            props.pendingCommitteeSelections,
                            committeeIndex
                        );
                        const isDisabled = !isSelected && !canAddSelection;
                        const buttonClass =
                            "button_standard committee_seats_distribution_assignment_committee_button" +
                            (isSelected
                                ? " committee_seats_distribution_assignment_committee_button_selected"
                                : "") +
                            (isDisabled
                                ? " committee_seats_distribution_assignment_committee_button_disabled"
                                : "");

                        return (
                            <button
                                key={committee.name}
                                className={buttonClass}
                                type="button"
                                aria-pressed={isSelected}
                                aria-disabled={isDisabled ? "true" : undefined}
                                onClick={() =>
                                    handleCommitteeButtonClick(committeeIndex, committee.name)
                                }
                            >
                                {committee.name}
                                {selectionCount > 1 ? " (" + selectionCount + ")" : ""}
                            </button>
                        );
                    })}
                </div>

                {disabledCommitteeNotes.length > 0 && (
                    <div className="committee_seats_distribution_assignment_disabled_notes">
                        {disabledCommitteeNotes.map((note) => (
                            <p
                                key={note}
                                className="committee_seats_note committee_seats_distribution_assignment_disabled_note"
                            >
                                {note}
                            </p>
                        ))}
                    </div>
                )}

                <div className="committee_seats_distribution_assignment_footer">
                    <div className="committee_seats_distribution_assignment_footer_start">
                        {previousGroupName !== null && (
                            <button
                                className="button_standard committee_seats_distribution_assignment_go_back_button"
                                type="button"
                                onClick={() => props.onGoBackToPreviousGroup()}
                            >
                                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_go_back_to_group_button_label(
                                    previousGroupName
                                )}
                            </button>
                        )}
                    </div>
                    <button
                        className="button_standard committee_seats_distribution_assignment_clear_button"
                        type="button"
                        disabled={props.pendingCommitteeSelections.length === 0}
                        onClick={() => props.onClearPendingSelections()}
                    >
                        {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_clear_selection_button_label(
                            batch.group_name
                        )}
                    </button>
                    <button
                        className="button_standard committee_seats_distribution_assignment_confirm_button"
                        type="button"
                        disabled={!confirmEnabled}
                        onClick={() => props.onConfirmAssignmentBatch()}
                    >
                        {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_confirm_choice_button_label(
                            batch.group_name,
                            nextGroupName
                        )}
                    </button>
                </div>
            </div>

            {disabledCommitteeModal !== null && (
                <div
                    className="committee_seats_disabled_committee_modal_overlay"
                    onClick={() => setDisabledCommitteeModal(null)}
                >
                    <div
                        className="committee_seats_disabled_committee_modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="committee_seats_disabled_committee_modal_heading"
                        onClick={(event) => event.stopPropagation()}
                    >
                        <h4
                            id="committee_seats_disabled_committee_modal_heading"
                            className="committee_seats_disabled_committee_modal_heading"
                        >
                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_heading(
                                disabledCommitteeModal.committeeName
                            )}
                        </h4>
                        <p className="committee_seats_disabled_committee_modal_message">
                            {disabledCommitteeModal.message}
                        </p>
                        <button
                            className="button_standard committee_seats_disabled_committee_modal_close_button"
                            type="button"
                            onClick={() => setDisabledCommitteeModal(null)}
                        >
                            {
                                EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_close_button_label
                            }
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
