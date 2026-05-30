import {h} from "preact";
import type {Committee} from "./types";

export function CommitteeList(props: {committees: Committee[]}) {
    if (props.committees.length === 0) {
        return null;
    }

    return (
        <div className="committee_seats_section">
            <h3 className="committee_seats_section_title">Committees</h3>
            <p className="committee_seats_note">
                Committee lists are shown for example councils only. Distributing seats across committees is
                negotiated between groups and is not part of this calculator.
            </p>
            <div className="committee_seats_table_scroll">
                <table className="committee_seats_committees_table">
                    <thead>
                        <tr>
                            <th>Committee</th>
                            <th>Seats</th>
                        </tr>
                    </thead>
                    <tbody>
                        {props.committees.map((committee, committeeIndex) => (
                            <tr key={committeeIndex}>
                                <td>{committee.name}</td>
                                <td>{committee.seat_count}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
