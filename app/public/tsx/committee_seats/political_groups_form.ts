import type {PoliticalGroup} from "./types";

/** Fixed party rows on the council setup form (names are not editable). */
export const STANDARD_POLITICAL_GROUP_NAMES: readonly string[] = [
    "Labour",
    "Conservative",
    "Liberal Democrat",
    "Green",
    "Reform UK",
    "Independent",
    "Vacancy",
];

export const ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT = 20;

export const GROUP_COUNCILLOR_COUNT_ADJUSTMENTS: readonly number[] = [-10, -1, 1, 10];

export const COUNCIL_SETUP_POLITICAL_GROUP_ROW_COUNT =
    STANDARD_POLITICAL_GROUP_NAMES.length + ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT;

function normalizeGroupNameForMatching(groupName: string): string {
    return groupName.trim().toLowerCase();
}

/** Common council labels mapped to a standard form row index. */
const STANDARD_GROUP_NAME_ALIAS_TO_INDEX: Record<string, number> = {
    conservatives: 1,
    conservativeparty: 1,
    libdems: 2,
    libdem: 2,
    "lib dems": 2,
    "lib dem": 2,
    "liberal democrats": 2,
    "liberal democrat party": 2,
    ungrouped: 5,
    "no party": 5,
    noparty: 5,
};

function findStandardGroupIndex(groupName: string): number {
    const normalized = normalizeGroupNameForMatching(groupName);

    const directIndex = STANDARD_POLITICAL_GROUP_NAMES.findIndex(
        (standardName) => normalizeGroupNameForMatching(standardName) === normalized
    );
    if (directIndex !== -1) {
        return directIndex;
    }

    const aliasIndex = STANDARD_GROUP_NAME_ALIAS_TO_INDEX[normalized];
    if (aliasIndex !== undefined) {
        return aliasIndex;
    }

    return -1;
}

export function createEmptyCouncilSetupPoliticalGroups(): PoliticalGroup[] {
    const standardGroups = STANDARD_POLITICAL_GROUP_NAMES.map((standardName) => ({
        name: standardName,
        councillor_count: 0,
    }));
    const additionalGroups = Array.from({length: ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT}, () => ({
        name: "",
        councillor_count: 0,
    }));

    return [...standardGroups, ...additionalGroups];
}

export function isStandardPoliticalGroupRowIndex(groupIndex: number): boolean {
    return groupIndex < STANDARD_POLITICAL_GROUP_NAMES.length;
}

/**
 * How many "other group" rows to show. Starts at one; each named (or counted) row reveals the next, up to the slot limit.
 */
export function getVisibleAdditionalPoliticalGroupSlotCount(formGroups: PoliticalGroup[]): number {
    const additionalGroups = formGroups.slice(STANDARD_POLITICAL_GROUP_NAMES.length);
    let visibleCount = 1;

    for (let additionalIndex = 0; additionalIndex < additionalGroups.length; additionalIndex += 1) {
        const additionalGroup = additionalGroups[additionalIndex];
        if (additionalGroup.name.trim() !== "" || additionalGroup.councillor_count > 0) {
            visibleCount = Math.min(
                ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT,
                additionalIndex + 2
            );
        }
    }

    return visibleCount;
}

/** Map example or URL party rows onto the fixed council setup form layout. */
export function mergePoliticalGroupsIntoCouncilSetupForm(
    sourceGroups: PoliticalGroup[]
): PoliticalGroup[] {
    const formGroups = createEmptyCouncilSetupPoliticalGroups();
    let nextAdditionalSlot = 0;

    for (const sourceGroup of sourceGroups) {
        const standardIndex = findStandardGroupIndex(sourceGroup.name);
        if (standardIndex !== -1) {
            formGroups[standardIndex] = {
                name: STANDARD_POLITICAL_GROUP_NAMES[standardIndex],
                councillor_count: sourceGroup.councillor_count,
            };
            continue;
        }

        if (nextAdditionalSlot >= ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT) {
            continue;
        }

        const additionalIndex = STANDARD_POLITICAL_GROUP_NAMES.length + nextAdditionalSlot;
        formGroups[additionalIndex] = {
            name: sourceGroup.name,
            councillor_count: sourceGroup.councillor_count,
        };
        nextAdditionalSlot += 1;
    }

    return formGroups;
}

function totalCouncillorsInOtherGroups(formGroups: PoliticalGroup[], groupIndex: number): number {
    let total = 0;
    for (let index = 0; index < formGroups.length; index += 1) {
        if (index !== groupIndex) {
            total += formGroups[index].councillor_count;
        }
    }
    return total;
}

/** Highest count allowed for one group without exceeding the council total or going below zero. */
export function clampGroupCouncillorCountValue(
    formGroups: PoliticalGroup[],
    groupIndex: number,
    proposedCount: number,
    expectedTotalCouncillors: number
): number {
    if (groupIndex < 0 || groupIndex >= formGroups.length) {
        return 0;
    }

    const otherGroupsTotal = totalCouncillorsInOtherGroups(formGroups, groupIndex);
    const maximumForGroup = Math.max(0, expectedTotalCouncillors - otherGroupsTotal);
    const flooredProposedCount = Math.max(0, proposedCount);

    return Math.min(flooredProposedCount, maximumForGroup);
}

export function clampGroupCouncillorCountAfterAdjustment(
    formGroups: PoliticalGroup[],
    groupIndex: number,
    adjustment: number,
    expectedTotalCouncillors: number
): number {
    const currentCount = formGroups[groupIndex]?.councillor_count ?? 0;
    return clampGroupCouncillorCountValue(
        formGroups,
        groupIndex,
        currentCount + adjustment,
        expectedTotalCouncillors
    );
}

/** True when the adjustment would not change the group's count (already at min or max). */
export function isGroupCouncillorCountAdjustmentDisabled(
    formGroups: PoliticalGroup[],
    groupIndex: number,
    adjustment: number,
    expectedTotalCouncillors: number
): boolean {
    const currentCount = formGroups[groupIndex]?.councillor_count ?? 0;
    const countAfterAdjustment = clampGroupCouncillorCountAfterAdjustment(
        formGroups,
        groupIndex,
        adjustment,
        expectedTotalCouncillors
    );

    return countAfterAdjustment === currentCount;
}

/** Groups passed to validation and seat allocation (omits unused additional rows). */
export function politicalGroupsForCouncilSetup(formGroups: PoliticalGroup[]): PoliticalGroup[] {
    const groupsForSetup: PoliticalGroup[] = [];

    for (let groupIndex = 0; groupIndex < formGroups.length; groupIndex += 1) {
        const formGroup = formGroups[groupIndex];

        if (isStandardPoliticalGroupRowIndex(groupIndex)) {
            if (formGroup.councillor_count > 0) {
                groupsForSetup.push({
                    name: STANDARD_POLITICAL_GROUP_NAMES[groupIndex],
                    councillor_count: formGroup.councillor_count,
                });
            }
            continue;
        }

        const trimmedName = formGroup.name.trim();
        if (trimmedName === "" || formGroup.councillor_count <= 0) {
            continue;
        }

        groupsForSetup.push({
            name: trimmedName,
            councillor_count: formGroup.councillor_count,
        });
    }

    return groupsForSetup;
}
