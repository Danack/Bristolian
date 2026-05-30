import {getExampleCouncilById} from "./example_councils";
import {
    mergePoliticalGroupsIntoCouncilSetupForm,
    politicalGroupsForCouncilSetup,
    STANDARD_POLITICAL_GROUP_NAMES,
} from "./political_groups_form";
import type {PoliticalGroup} from "./types";
import {STANDARD_VACANCY_GROUP_NAME} from "./vacancy_allocation";

export const STANDARD_INDEPENDENT_GROUP_NAME = STANDARD_POLITICAL_GROUP_NAMES[5];

export const INDEPENDENT_ALLOCATION_STEP_COPY = {
    lead:
        "Some councils do not allocate committee seats to independent councillors. Does your council allocate seats to independents?",
    yes_label: "Yes - independents are allocated seats",
    no_label: "No - independents are excluded",
    consequence_note:
        "If you choose No, independents are left out of the proportional calculation entirely.",
} as const;

export function getDefaultAllocateSeatsToIndependentsForExampleCouncil(
    exampleCouncilId: string
): boolean | null {
    const exampleCouncil = getExampleCouncilById(exampleCouncilId);
    if (exampleCouncil === undefined) {
        return null;
    }

    if (exampleCouncil.allocate_seats_to_independents !== undefined) {
        return exampleCouncil.allocate_seats_to_independents;
    }

    return null;
}

export function getInitialIndependentAllocationChoice(
    dataSourceMode: string,
    selectedExampleCouncilId: string,
    currentChoice: boolean | null
): boolean | null {
    if (currentChoice !== null) {
        return currentChoice;
    }

    if (dataSourceMode === "example") {
        return getDefaultAllocateSeatsToIndependentsForExampleCouncil(selectedExampleCouncilId);
    }

    return null;
}

export function formatIndependentCouncillorCountEnteredMessage(independentCouncillorCount: number): string {
    const councillorWord = independentCouncillorCount === 1 ? "councillor" : "councillors";

    return (
        "Your council has " +
        independentCouncillorCount +
        " Independent " +
        councillorWord +
        "."
    );
}

const STANDARD_INDEPENDENT_GROUP_INDEX = 5;

function councilSetupFormGroups(formGroups: PoliticalGroup[]): PoliticalGroup[] {
    if (formGroups.length >= STANDARD_POLITICAL_GROUP_NAMES.length) {
        return formGroups;
    }

    return mergePoliticalGroupsIntoCouncilSetupForm(formGroups);
}

export function getIndependentCouncillorCountFromForm(formGroups: PoliticalGroup[]): number {
    const mergedFormGroups = councilSetupFormGroups(formGroups);
    return mergedFormGroups[STANDARD_INDEPENDENT_GROUP_INDEX]?.councillor_count ?? 0;
}

export function councilFormHasIndependentCouncillors(formGroups: PoliticalGroup[]): boolean {
    return getIndependentCouncillorCountFromForm(formGroups) > 0;
}

/** Groups included in the proportional seat calculation (may omit Independent; never includes Vacancy). */
export function politicalGroupsForSeatAllocation(
    formGroups: PoliticalGroup[],
    allocateSeatsToIndependents: boolean
): PoliticalGroup[] {
    const groupsForSetup = politicalGroupsForCouncilSetup(formGroups);

    return groupsForSetup.filter((politicalGroup) => {
        if (politicalGroup.name === STANDARD_VACANCY_GROUP_NAME) {
            return false;
        }

        if (!allocateSeatsToIndependents && politicalGroup.name === STANDARD_INDEPENDENT_GROUP_NAME) {
            return false;
        }

        return true;
    });
}
