import type {Committee} from "./types";

export const COMMITTEE_FORM_SLOT_COUNT = 30;

export function createEmptyCommitteesForm(): Committee[] {
    return Array.from({length: COMMITTEE_FORM_SLOT_COUNT}, () => ({
        name: "",
        seat_count: 0,
    }));
}

export function committeeRowHasEnteredName(committee: Committee): boolean {
    return committee.name.trim() !== "";
}

function isCommitteeSlotUsed(committee: Committee): boolean {
    return committee.name.trim() !== "" || committee.seat_count > 0;
}

export function countUsedCommitteeSlots(formCommittees: Committee[]): number {
    let usedSlotCount = 0;

    for (const committee of formCommittees) {
        if (isCommitteeSlotUsed(committee)) {
            usedSlotCount += 1;
        }
    }

    return usedSlotCount;
}

export function hasReachedMaximumCommitteeSlots(formCommittees: Committee[]): boolean {
    return countUsedCommitteeSlots(formCommittees) >= COMMITTEE_FORM_SLOT_COUNT;
}

export function getNextEmptyCommitteeSlotIndex(formCommittees: Committee[]): number | null {
    for (let slotIndex = 0; slotIndex < formCommittees.length; slotIndex += 1) {
        const committee = formCommittees[slotIndex];
        if (committee.name.trim() === "" && committee.seat_count <= 0) {
            return slotIndex;
        }
    }

    return null;
}

export function getListedCommittees(
    formCommittees: Committee[]
): {committeeIndex: number; committee: Committee}[] {
    const listedCommittees: {committeeIndex: number; committee: Committee}[] = [];

    for (let committeeIndex = 0; committeeIndex < formCommittees.length; committeeIndex += 1) {
        const committee = formCommittees[committeeIndex];
        if (committeeRowHasEnteredName(committee)) {
            listedCommittees.push({committeeIndex, committee});
        }
    }

    return listedCommittees;
}

/** Map stored or example committees onto the fixed-slot form layout. */
export function mergeCommitteesIntoForm(sourceCommittees: Committee[]): Committee[] {
    const formCommittees = createEmptyCommitteesForm();

    for (let slotIndex = 0; slotIndex < sourceCommittees.length && slotIndex < COMMITTEE_FORM_SLOT_COUNT; slotIndex += 1) {
        const sourceCommittee = sourceCommittees[slotIndex];
        formCommittees[slotIndex] = {
            name: sourceCommittee.name,
            seat_count: sourceCommittee.seat_count,
        };
    }

    return formCommittees;
}

export function resolveFormCommittees(committees: Committee[]): Committee[] {
    if (committees.length >= COMMITTEE_FORM_SLOT_COUNT) {
        return committees;
    }

    return mergeCommitteesIntoForm(committees);
}

/** Committees used for totals and distribution (named rows with at least one seat). */
export function committeesForSetup(formCommittees: Committee[]): Committee[] {
    const committeesForSetupList: Committee[] = [];

    for (const committee of formCommittees) {
        const trimmedName = committee.name.trim();
        if (trimmedName === "" || committee.seat_count <= 0) {
            continue;
        }

        committeesForSetupList.push({
            name: trimmedName,
            seat_count: Number(committee.seat_count) || 0,
        });
    }

    return committeesForSetupList;
}

export function calculateTotalCommitteeSeatsFromForm(formCommittees: Committee[]): number {
    return committeesForSetup(formCommittees).reduce(
        (total, committee) => total + Number(committee.seat_count),
        0
    );
}

export function formatCommitteeSeatsTotalMessage(
    totalFromCommittees: number,
    expectedTotalCommitteeSeats: number
): string {
    if (totalFromCommittees === expectedTotalCommitteeSeats) {
        return (
            "Committee seats in the table add up to " +
            expectedTotalCommitteeSeats +
            " — matching the total from the earlier step."
        );
    }

    const difference = expectedTotalCommitteeSeats - totalFromCommittees;
    const absoluteDifference = Math.abs(difference);
    const seatWord = absoluteDifference === 1 ? "seat" : "seats";

    if (difference > 0) {
        return (
            "You need " +
            absoluteDifference +
            " more committee " +
            seatWord +
            " in the table to reach the total of " +
            expectedTotalCommitteeSeats +
            " from the earlier step."
        );
    }

    return (
        "The committees in the table total " +
        totalFromCommittees +
        " seats. The earlier step used " +
        expectedTotalCommitteeSeats +
        ", so you have " +
        absoluteDifference +
        " " +
        seatWord +
        " too many. Lower one or more seat counts above."
    );
}

export interface CommitteesSetupValidationResult {
    valid: boolean;
    total_from_committees: number;
    error: string | null;
}

export function validateCommitteesSetup(
    formCommittees: Committee[],
    expectedTotalCommitteeSeats: number
): CommitteesSetupValidationResult {
    const committeesForValidation = committeesForSetup(formCommittees);
    const totalFromCommittees = calculateTotalCommitteeSeatsFromForm(formCommittees);

    if (committeesForValidation.length === 0) {
        return {
            valid: false,
            total_from_committees: totalFromCommittees,
            error: "Enter at least one committee with a name and a seat count greater than zero.",
        };
    }

    if (expectedTotalCommitteeSeats <= 0) {
        return {
            valid: false,
            total_from_committees: totalFromCommittees,
            error: "Total committee seats must be greater than zero.",
        };
    }

    if (totalFromCommittees !== expectedTotalCommitteeSeats) {
        return {
            valid: false,
            total_from_committees: totalFromCommittees,
            error: formatCommitteeSeatsTotalMessage(totalFromCommittees, expectedTotalCommitteeSeats),
        };
    }

    return {
        valid: true,
        total_from_committees: totalFromCommittees,
        error: null,
    };
}

export function clampCommitteeSeatCountValue(
    formCommittees: Committee[],
    committeeIndex: number,
    proposedSeatCount: number
): number {
    if (committeeIndex < 0 || committeeIndex >= formCommittees.length) {
        return 0;
    }

    return Math.max(0, proposedSeatCount);
}
