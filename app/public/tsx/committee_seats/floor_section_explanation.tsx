import {h} from "preact";
import {
    buildCommitteeFloorCalculationExample,
    formatCommitteeFloorExampleCalculation,
} from "./calculate_committee_distribution";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import type {CommitteeDistributionState} from "./types";

export interface FloorSectionExplanationProps {
    distributionState: CommitteeDistributionState;
}

export function FloorSectionExplanation(props: FloorSectionExplanationProps) {
    const example = buildCommitteeFloorCalculationExample(props.distributionState, 0);
    if (example === null) {
        return null;
    }

    const exampleHeading =
        EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_explanation_example_heading +
        " " +
        example.committee_name +
        " (" +
        example.committee_seat_count +
        " seats out of " +
        example.total_committee_seats +
        " committee seats) — " +
        example.primary_entry.group_name +
        ":";

    return (
        <div className="committee_seats_floor_section_explanation">
            <p className="committee_seats_note">{EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_explanation_lead}</p>
            <p className="committee_seats_note">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_explanation_how}{" "}
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_explanation_formula}
            </p>
            <p className="committee_seats_floor_example_heading">{exampleHeading}</p>
            <p className="committee_seats_floor_example_calculation">
                {formatCommitteeFloorExampleCalculation(example)}
            </p>
            <p className="committee_seats_note">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_explanation_table_note}
            </p>
        </div>
    );
}
