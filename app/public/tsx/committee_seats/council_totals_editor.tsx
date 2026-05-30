import {h} from "preact";
import {COMMITTEE_SEATS_PAGE} from "./page_config";

export interface CouncilTotalsEditorProps {
    expectedTotalCouncillors: number;
    totalCommitteeSeats: number;
    showExampleCommitteeSeatsNote: boolean;
    onExpectedTotalCouncillorsChange: (expectedTotalCouncillors: number) => void;
    onTotalCommitteeSeatsChange: (totalCommitteeSeats: number) => void;
}

export function CouncilTotalsEditor(props: CouncilTotalsEditorProps) {
    return (
        <div className="committee_seats_section committee_seats_section_fit">
            <div className="committee_seats_table_scroll committee_seats_table_scroll_fit">
                <table className="committee_seats_groups_table committee_seats_council_totals_table">
                    <tbody>
                        <tr>
                            <td className="committee_seats_group_name_fixed">
                                {COMMITTEE_SEATS_PAGE.council_setup_how_many_councillors_question}
                            </td>
                            <td>
                                <label
                                    className="committee_seats_visually_hidden"
                                    htmlFor="expected_total_councillors"
                                >
                                    {COMMITTEE_SEATS_PAGE.council_setup_how_many_councillors_question}
                                </label>
                                <input
                                    id="expected_total_councillors"
                                    type="number"
                                    min="1"
                                    step="1"
                                    value={props.expectedTotalCouncillors}
                                    onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                                        props.onExpectedTotalCouncillorsChange(
                                            parseInt(event.currentTarget.value, 10) || 0
                                        )
                                    }
                                />
                            </td>
                        </tr>
                        <tr>
                            <td className="committee_seats_group_name_fixed">
                                {COMMITTEE_SEATS_PAGE.council_setup_how_many_committee_seats_question}
                            </td>
                            <td>
                                <label
                                    className="committee_seats_visually_hidden"
                                    htmlFor="total_committee_seats"
                                >
                                    {COMMITTEE_SEATS_PAGE.council_setup_how_many_committee_seats_question}
                                </label>
                                <input
                                    id="total_committee_seats"
                                    type="number"
                                    min="1"
                                    step="1"
                                    value={props.totalCommitteeSeats}
                                    onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                                        props.onTotalCommitteeSeatsChange(
                                            parseInt(event.currentTarget.value, 10) || 0
                                        )
                                    }
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p className="committee_seats_note">{COMMITTEE_SEATS_PAGE.council_setup_political_committees_note}</p>
            {props.showExampleCommitteeSeatsNote && (
                <p className="committee_seats_note">
                    This example only includes political group counts. Enter the total committee seats for your
                    council.
                </p>
            )}
        </div>
    );
}
