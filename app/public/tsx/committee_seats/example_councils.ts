import {calculateTotalCouncillors} from "./calculate_party_allocation";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./political_groups_form";
import type {Committee, ExampleCouncil} from "./types";

export const EXAMPLE_COUNCILS: ExampleCouncil[] = [
    {
        id: "barnet",
        display_name: "Barnet Council",
        political_groups: [
            {name: "Labour", councillor_count: 31},
            {name: "Conservative", councillor_count: 31},
            {name: "Independent", councillor_count: 1},
        ],
        committees: [
            {name: "Licensing and General Purposes Committee", seat_count: 10},
            {name: "Strategic Planning Committee", seat_count: 10},
            {name: "Overview & Scrutiny Committee", seat_count: 8},
            {name: "Call-In Sub-Committee", seat_count: 4},
            {name: "Finance and Growth Overview & Scrutiny SubCommittee", seat_count: 8},
            {name: "Environment Overview & Scrutiny Sub-Committee", seat_count: 8},
            {name: "Planning Committee", seat_count: 8},
        ],
        allocate_seats_to_independents: false,
    },
    {
        id: "bristol",
        display_name: "Bristol",
        political_groups: [
            {name: "Green", councillor_count: 34},
            {name: "Labour", councillor_count: 19},
            {name: "Liberal Democrat", councillor_count: 9},
            {name: "Conservative", councillor_count: 7},
            {name: "Independent", councillor_count: 1},
        ],
        committees: [],
        total_committee_seats: 144,
        allocate_seats_to_independents: false,
    },
    {
        id: "lambeth",
        display_name: "Lambeth",
        political_groups: [
            {name: "Green", councillor_count: 27},
            {name: "Labour", councillor_count: 26},
            {name: "Liberal Democrats", councillor_count: 8},
            {name: "Vacancy", councillor_count: 2},
        ],
        committees: [
            {name: "Appointments", seat_count: 5},
            {name: "Audit and Risk", seat_count: 5},
            {name: "General Purposes", seat_count: 5},
            {name: "Planning", seat_count: 7},
            {name: "Standards", seat_count: 5},
            {name: "Pensions Committee", seat_count: 5},
            {name: "Investigating Committee", seat_count: 3},
        ],
    },
    {
        id: "sheffield",
        display_name: "Sheffield",
        political_groups: [
            {name: "Labour", councillor_count: 25},
            {name: "Liberal Democrat", councillor_count: 22},
            {name: "Green", councillor_count: 20},
            {name: "Reform UK", councillor_count: 11},
            {name: "Sheffield Community Councillors", councillor_count: 2},
            {name: "Independent", councillor_count: 4},
        ],
        committees: [],
        total_committee_seats: 168,
        allocate_seats_to_independents: true,
    },
    // Dev-only example: political group counts only (no committee total). Omitted from the
    // public dropdown; tests use an inline ExampleCouncil with the same shape.
    {
        id: "test_council",
        display_name: "Test council",
        political_groups: [
            {name: "Labour", councillor_count: 20},
            {name: "Conservative", councillor_count: 15},
            {name: "Green", councillor_count: 10},
            {name: "Independent", councillor_count: 4},
        ],
        committees: [],
        total_committee_seats: 80,
    },
];

export function getExampleCouncilById(exampleCouncilId: string): ExampleCouncil | undefined {
    return EXAMPLE_COUNCILS.find((exampleCouncil) => exampleCouncil.id === exampleCouncilId);
}

/** Total committee seats known from example data, or null if the user must enter it. */
export function getPrefilledTotalCommitteeSeats(exampleCouncil: ExampleCouncil): number | null {
    if (exampleCouncil.committees.length > 0) {
        return exampleCouncil.committees.reduce(
            (total, committee) => total + committee.seat_count,
            0
        );
    }

    if (
        exampleCouncil.total_committee_seats !== undefined &&
        exampleCouncil.total_committee_seats > 0
    ) {
        return exampleCouncil.total_committee_seats;
    }

    return null;
}

/** Total councillors documented for an example council (sum of its example group counts). */
export function getExampleCouncilTotalCouncillors(exampleCouncil: ExampleCouncil): number {
    return calculateTotalCouncillors(exampleCouncil.political_groups);
}

export function applyExampleCouncilToFormState(exampleCouncil: ExampleCouncil): {
    political_groups: ExampleCouncil["political_groups"];
    committees: Committee[];
    total_committee_seats: number;
    expected_total_councillors: number;
} {
    const prefilledTotal = getPrefilledTotalCommitteeSeats(exampleCouncil);

    return {
        political_groups: mergePoliticalGroupsIntoCouncilSetupForm(exampleCouncil.political_groups),
        committees: exampleCouncil.committees.map((committee) => ({...committee})),
        total_committee_seats: prefilledTotal ?? 0,
        expected_total_councillors: getExampleCouncilTotalCouncillors(exampleCouncil),
    };
}
