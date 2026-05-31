import {h, Component} from "preact";
import {COMMITTEE_SEATS_PAGE} from "./page_config";
import type {Committee} from "./types";
import {
    clampCommitteeSeatCountValue,
    getListedCommittees,
    getNextEmptyCommitteeSlotIndex,
    hasReachedMaximumCommitteeSlots,
    resolveFormCommittees,
} from "./committees_form";

interface AddCommitteeRowProps {
    slotIndex: number;
    onAddCommittee: (committeeIndex: number, name: string, seatCount: number) => void;
}

interface AddCommitteeRowState {
    draftCommitteeName: string;
    draftSeatCount: number;
}

class AddCommitteeRow extends Component<AddCommitteeRowProps, AddCommitteeRowState> {
    constructor(props: AddCommitteeRowProps) {
        super(props);
        this.state = {
            draftCommitteeName: "",
            draftSeatCount: 0,
        };
    }

    handleDraftCommitteeNameChange(event: h.JSX.TargetedEvent<HTMLInputElement, Event>): void {
        this.setState({
            draftCommitteeName: event.currentTarget.value,
        });
    }

    handleDraftSeatCountChange(event: h.JSX.TargetedEvent<HTMLInputElement, Event>): void {
        this.setState({
            draftSeatCount: parseInt(event.currentTarget.value, 10) || 0,
        });
    }

    handleAddCommittee(): void {
        const trimmedCommitteeName = this.state.draftCommitteeName.trim();
        if (trimmedCommitteeName === "" || this.state.draftSeatCount <= 0) {
            return;
        }

        this.props.onAddCommittee(
            this.props.slotIndex,
            trimmedCommitteeName,
            this.state.draftSeatCount
        );
        this.setState({
            draftCommitteeName: "",
            draftSeatCount: 0,
        });
    }

    handleDraftCommitteeNameKeyDown(event: KeyboardEvent): void {
        if (event.key === "Enter") {
            event.preventDefault();
            this.handleAddCommittee();
        }
    }

    handleDraftSeatCountKeyDown(event: KeyboardEvent): void {
        if (event.key === "Enter") {
            event.preventDefault();
            this.handleAddCommittee();
        }
    }

    render() {
        const addCommitteeDisabled =
            this.state.draftCommitteeName.trim() === "" || this.state.draftSeatCount <= 0;

        return (
            <tr className="committee_seats_groups_table_add_group_row">
                <td>
                    <label
                        className="committee_seats_visually_hidden"
                        htmlFor="committee_seats_add_committee_name"
                    >
                        Committee name
                    </label>
                    <input
                        id="committee_seats_add_committee_name"
                        type="text"
                        className="committee_seats_additional_group_name_input"
                        value={this.state.draftCommitteeName}
                        placeholder={COMMITTEE_SEATS_PAGE.add_committee_name_placeholder}
                        onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                            this.handleDraftCommitteeNameChange(event)
                        }
                        onKeyDown={(event: KeyboardEvent) => this.handleDraftCommitteeNameKeyDown(event)}
                    />
                </td>
                <td className="committee_seats_group_count_cell">
                    <div className="committee_seats_add_committee_controls">
                        <label
                            className="committee_seats_visually_hidden"
                            htmlFor="committee_seats_add_committee_seat_count"
                        >
                            Number of seats
                        </label>
                        <input
                            id="committee_seats_add_committee_seat_count"
                            type="number"
                            min="0"
                            step="1"
                            className="committee_seats_add_committee_seat_count_input"
                            value={this.state.draftSeatCount}
                            onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                                this.handleDraftSeatCountChange(event)
                            }
                            onKeyDown={(event: KeyboardEvent) => this.handleDraftSeatCountKeyDown(event)}
                        />
                        <button
                            className="button_standard committee_seats_add_committee_button"
                            type="button"
                            disabled={addCommitteeDisabled}
                            onClick={() => this.handleAddCommittee()}
                        >
                            {COMMITTEE_SEATS_PAGE.add_committee_button_label}
                        </button>
                    </div>
                </td>
            </tr>
        );
    }
}

export interface CommitteesEditorProps {
    committees: Committee[];
    expectedTotalCommitteeSeats: number;
    committeeSeatsStatusMessage: string;
    committeeSeatsStatusMatches: boolean;
    canContinueFromCommittees: boolean;
    onCommitteeNameChange: (committeeIndex: number, name: string) => void;
    onCommitteeSeatCountChange: (committeeIndex: number, seatCount: number) => void;
    onAddCommittee: (committeeIndex: number, name: string, seatCount: number) => void;
    onContinueFromCommittees: () => void;
}

export function CommitteesEditor(props: CommitteesEditorProps) {
    const formCommittees = resolveFormCommittees(props.committees);
    const listedCommittees = getListedCommittees(formCommittees);
    const maximumCommitteesReached = hasReachedMaximumCommitteeSlots(formCommittees);
    const nextEmptyCommitteeSlotIndex = getNextEmptyCommitteeSlotIndex(formCommittees);

    return (
        <div className="committee_seats_section committee_seats_section_fit">
            <h3 className="committee_seats_section_title">
                {COMMITTEE_SEATS_PAGE.experimental_committees_section_title}
            </h3>
            <fieldset
                className="committee_seats_political_groups_fieldset"
                aria-labelledby="committee_seats_experimental_committees_title"
            >
                <div className="committee_seats_political_groups_panel">
                    <div className="committee_seats_political_groups_body">
                        <div className="committee_seats_table_scroll committee_seats_table_scroll_fit committee_seats_political_groups_table">
                            <table className="committee_seats_groups_table committee_seats_committees_table">
                                <thead>
                                    <tr>
                                        <th scope="col">Committee</th>
                                        <th scope="col">Seats</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {listedCommittees.map(({committeeIndex, committee}, listedIndex) => {
                                        const nameInputId = "committee_name_" + committeeIndex;
                                        const seatInputId = "committee_seat_count_" + committeeIndex;

                                        return (
                                            <tr
                                                key={"committee_" + committeeIndex}
                                                className={
                                                    listedIndex === 0
                                                        ? "committee_seats_groups_table_first_listed_row"
                                                        : undefined
                                                }
                                            >
                                                <td>
                                                    <label
                                                        className="committee_seats_visually_hidden"
                                                        htmlFor={nameInputId}
                                                    >
                                                        Committee name
                                                    </label>
                                                    <input
                                                        id={nameInputId}
                                                        type="text"
                                                        value={committee.name}
                                                        onInput={(
                                                            event: h.JSX.TargetedEvent<HTMLInputElement, Event>
                                                        ) =>
                                                            props.onCommitteeNameChange(
                                                                committeeIndex,
                                                                event.currentTarget.value
                                                            )
                                                        }
                                                    />
                                                </td>
                                                <td className="committee_seats_group_count_cell">
                                                    <label
                                                        className="committee_seats_visually_hidden"
                                                        htmlFor={seatInputId}
                                                    >
                                                        Number of seats
                                                    </label>
                                                    <input
                                                        id={seatInputId}
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        value={committee.seat_count}
                                                        onInput={(
                                                            event: h.JSX.TargetedEvent<HTMLInputElement, Event>
                                                        ) =>
                                                            props.onCommitteeSeatCountChange(
                                                                committeeIndex,
                                                                parseInt(event.currentTarget.value, 10) || 0
                                                            )
                                                        }
                                                    />
                                                </td>
                                            </tr>
                                        );
                                    })}
                                    {maximumCommitteesReached ? (
                                        <tr className="committee_seats_groups_table_add_group_row">
                                            <td className="committee_seats_additional_group_name_cell" colSpan={2}>
                                                <input
                                                    type="text"
                                                    className="committee_seats_additional_group_name_input"
                                                    value=""
                                                    disabled
                                                    aria-disabled="true"
                                                    aria-label={
                                                        COMMITTEE_SEATS_PAGE.maximum_committees_reached
                                                    }
                                                    placeholder={
                                                        COMMITTEE_SEATS_PAGE.maximum_committees_reached
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    ) : (
                                        nextEmptyCommitteeSlotIndex !== null && (
                                            <AddCommitteeRow
                                                slotIndex={nextEmptyCommitteeSlotIndex}
                                                onAddCommittee={props.onAddCommittee}
                                            />
                                        )
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <div className="committee_seats_political_groups_aside">
                            <p
                                className={
                                    props.committeeSeatsStatusMatches
                                        ? "committee_seats_total_councillors"
                                        : "committee_seats_total_councillors committee_seats_total_councillors_mismatch"
                                }
                            >
                                {props.committeeSeatsStatusMessage}
                            </p>
                            <button
                                className="button_standard committee_seats_political_groups_aside_continue"
                                type="button"
                                onClick={() => props.onContinueFromCommittees()}
                                disabled={!props.canContinueFromCommittees}
                            >
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    );
}

export function clampCommitteeSeatCountForPanel(
    committees: Committee[],
    committeeIndex: number,
    seatCount: number
): number {
    const formCommittees = resolveFormCommittees(committees);
    return clampCommitteeSeatCountValue(formCommittees, committeeIndex, seatCount);
}

export function updateFormCommitteesAtIndex(
    committees: Committee[],
    committeeIndex: number,
    updates: Partial<Committee>
): Committee[] {
    const formCommittees = resolveFormCommittees(committees);

    return formCommittees.map((committee, index) => {
        if (index !== committeeIndex) {
            return committee;
        }

        return {
            ...committee,
            ...updates,
        };
    });
}
