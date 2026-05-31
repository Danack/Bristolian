import {calculateTotalCouncillors} from "./calculate_party_allocation";
import {NO_EXAMPLE_COUNCIL_SELECTED} from "./page_config";
import {committeesForSetup, mergeCommitteesIntoForm, resolveFormCommittees} from "./committees_form";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./political_groups_form";
import type {Committee, ExampleCouncil, PoliticalGroup} from "./types";

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
        committees: [
            {name: "Adult Social Care Committee", seat_count: 9},
            {name: "Homes and Housing Delivery Committee", seat_count: 9},
            {name: "Public Health and Communities Committee", seat_count: 9},
            {name: "Economy and Skills Committee", seat_count: 9},
            {name: "Strategy and Resources Committee", seat_count: 9},
            {name: "Children and Young people Committee", seat_count: 9},
            {name: "Transport and Connectivity Committee", seat_count: 9},
            {name: "Environment and Sustainability Committee", seat_count: 9},
            {name: "Planning Committee A", seat_count: 9},
            {name: "Planning Committee B", seat_count: 9},
            {name: "Public Safety and Protection Committee", seat_count: 9},
            {name: "Public Rights of Way and Greens Committee", seat_count: 9},
            {name: "Audit Committee", seat_count: 9},
            {name: "Human Resources Committee", seat_count: 9},
            {name: "Health Scrutiny Sub Committee", seat_count: 9},
            {name: "Finance Sub Committee", seat_count: 9},
        ],
        allocate_seats_to_independents: false,
        seat_assignment_source_url:
            "https://democracy.bristol.gov.uk/documents/s126155/Allocation%20of%20committee%20seats%202026-27.pdf",
    },
    {
        id: "bcp",
        display_name: "Bournemouth, Christchurch and Poole",
        political_groups: [
            {name: "Liberal Democrat", councillor_count: 28},
            {name: "Conservative", councillor_count: 9},
            {name: "Christchurch Independents", councillor_count: 8},
            {name: "Labour", councillor_count: 8},
            {name: "Green", councillor_count: 5},
            {name: "BCP Independents", councillor_count: 4},
            {name: "Poole People", councillor_count: 4},
            {name: "BCP Reform UK", councillor_count: 3},
            {name: "Independents", councillor_count: 2},
            {name: "Moordown Independents", councillor_count: 2},
            {name: "Poole Engage", councillor_count: 2},
            {name: "Other", councillor_count: 1},
        ],
        committees: [
            {name: "Western BCP Planning Committee", seat_count: 11},
            {name: "Eastern BCP Planning Committee", seat_count: 11},
            {name: "Licensing Committee", seat_count: 14},
            {name: "Standards Committee", seat_count: 7},
            {name: "Appeals Committee", seat_count: 7},
            {name: "Audit & Governance Committee", seat_count: 9},
            {name: "Overview and Scrutiny Board", seat_count: 13},
            {name: "Health & Adult Social Care O&S Committee", seat_count: 10},
            {name: "Children's Services O&S Committee", seat_count: 11},
            {name: "Environment and Place O&S Committee", seat_count: 11},
            {name: "Investigation and Disciplinary Committee", seat_count: 7},
        ],
        seat_assignment_source_url:
            "https://democracy.bcpcouncil.gov.uk/documents/s66265/Supplementary%20Update%20on%20the%20Calculation%20of%20Political%20Balance%20and%20the%20Allocation%20of%20Seats.pdf",
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
        seat_assignment_source_url:
            "https://moderngov.lambeth.gov.uk/documents/s176894/Review%20of%20allocation%20of%20seats%20report.pdf",
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
        seat_assignment_source_url:
            "https://democracy.sheffield.gov.uk/documents/b32757/Motion%20-%20Item%2013%20-%20Establishment%20Membership%20of%20Council%20Committees%20in%202026-27%20Thursday%2021-May-2026.pdf?T=9",
    },
    // Dev-only example: political group counts only (no committee total). Omitted from the
    // public dropdown; tests use an inline ExampleCouncil with the same shape.
    {
        id: "test_council",
        display_name: "Test council",
        political_groups: [
            {name: "Labour", councillor_count: 8},
            {name: "Conservative", councillor_count: 6},
            {name: "Green", councillor_count: 12},
            {name: "Libdem", councillor_count: 3},
        ],
        committees: [
            {name: "Adult Social Care Committee", seat_count: 9},
            {name: "Homes and Housing Delivery Committee", seat_count: 9},
            {name: "Public Health and Communities Committee", seat_count: 9},
            {name: "Economy and Skills Committee", seat_count: 9},
        ],
        total_committee_seats: 36,
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

export function panelFormHasNoPoliticalGroupCounts(politicalGroups: PoliticalGroup[]): boolean {
    return !politicalGroups.some((politicalGroup) => politicalGroup.councillor_count > 0);
}

/**
 * When an example council is selected but group counts are missing (e.g. URL at the totals
 * step omits the groups parameter), restore figures from the built-in example data.
 */
export function panelFormHasNoCommittees(committees: Committee[]): boolean {
    return committeesForSetup(resolveFormCommittees(committees)).length === 0;
}

/**
 * When an example council is selected but committee rows are missing, restore from example data.
 */
export function applyExampleCouncilCommitteesIfMissing<
    T extends {
        data_source_mode: string;
        selected_example_council_id: string;
        committees: Committee[];
    },
>(state: T): T {
    if (state.data_source_mode !== "example") {
        return state;
    }

    if (state.selected_example_council_id === NO_EXAMPLE_COUNCIL_SELECTED) {
        return state;
    }

    if (!panelFormHasNoCommittees(state.committees)) {
        return state;
    }

    const exampleCouncil = getExampleCouncilById(state.selected_example_council_id);
    if (exampleCouncil === undefined) {
        return state;
    }

    const applied = applyExampleCouncilToFormState(exampleCouncil);

    return {
        ...state,
        committees: applied.committees,
    };
}

export function applyExampleCouncilPoliticalGroupsIfMissing<
    T extends {
        data_source_mode: string;
        selected_example_council_id: string;
        political_groups: PoliticalGroup[];
    },
>(state: T): T {
    if (state.data_source_mode !== "example") {
        return state;
    }

    if (state.selected_example_council_id === NO_EXAMPLE_COUNCIL_SELECTED) {
        return state;
    }

    if (!panelFormHasNoPoliticalGroupCounts(state.political_groups)) {
        return state;
    }

    const exampleCouncil = getExampleCouncilById(state.selected_example_council_id);
    if (exampleCouncil === undefined) {
        return state;
    }

    const applied = applyExampleCouncilToFormState(exampleCouncil);

    return {
        ...state,
        political_groups: applied.political_groups,
    };
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
