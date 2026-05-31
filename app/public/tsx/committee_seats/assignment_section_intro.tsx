import {h} from "preact";
import {useState} from "preact/hooks";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";

export function AssignmentSectionIntro() {
    const [instructionsVisible, setInstructionsVisible] = useState(true);

    return (
        <div className="committee_seats_distribution_assignment_intro_block">
            {instructionsVisible && (
                <p className="committee_seats_note committee_seats_distribution_assignment_intro">
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_section_intro}
                </p>
            )}
            <button
                className="button_standard committee_seats_distribution_assignment_intro_toggle"
                type="button"
                onClick={() => setInstructionsVisible(!instructionsVisible)}
            >
                {instructionsVisible
                    ? EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.hide_assignment_instructions_button_label
                    : EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.show_assignment_instructions_button_label}
            </button>
        </div>
    );
}
