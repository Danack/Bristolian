import {
    mergePoliticalGroupsIntoCouncilSetupForm,
    STANDARD_POLITICAL_GROUP_NAMES,
} from "./political_groups_form";
import type {PoliticalGroup} from "./types";

export const STANDARD_VACANCY_GROUP_NAME = STANDARD_POLITICAL_GROUP_NAMES[6];

export const VACANCY_ALLOCATION_NOTE =
    "Vacant council seats are not included in the proportional committee seat calculation.";

const STANDARD_VACANCY_GROUP_INDEX = 6;

function councilSetupFormGroups(formGroups: PoliticalGroup[]): PoliticalGroup[] {
    if (formGroups.length >= STANDARD_POLITICAL_GROUP_NAMES.length) {
        return formGroups;
    }

    return mergePoliticalGroupsIntoCouncilSetupForm(formGroups);
}

export function getVacancyCouncillorCountFromForm(formGroups: PoliticalGroup[]): number {
    const mergedFormGroups = councilSetupFormGroups(formGroups);
    return mergedFormGroups[STANDARD_VACANCY_GROUP_INDEX]?.councillor_count ?? 0;
}

export function councilFormHasVacancies(formGroups: PoliticalGroup[]): boolean {
    return getVacancyCouncillorCountFromForm(formGroups) > 0;
}

export function formatVacancyCouncillorCountEnteredMessage(vacancyCouncillorCount: number): string {
    const seatWord = vacancyCouncillorCount === 1 ? "seat" : "seats";

    return (
        "Your council has " +
        vacancyCouncillorCount +
        " vacant council " +
        seatWord +
        "."
    );
}
