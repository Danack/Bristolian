import type {
    CouncilSetupInput,
    CouncilSetupValidationInput,
    GroupAllocationRow,
    PartyAllocationResult,
    PartyAllocationWorkbookStep,
    PoliticalGroup,
} from "./types";

interface GroupCalculation {
    group: PoliticalGroup;
    percentage_of_council: number;
    raw_entitlement: number;
    floored_seats: number;
    fractional_part: number;
    final_seats: number;
}

/** Lowercase roman numerals for workbook sub-steps under section B (i, ii, iii, …). */
export function formatLowerRomanNumeral(value: number): string {
    if (value <= 0 || value > 39 || !Number.isInteger(value)) {
        return String(value);
    }

    const tensDigit = Math.floor(value / 10);
    const onesDigit = value % 10;
    const tensPart = tensDigit === 0 ? "" : tensDigit === 1 ? "x" : tensDigit === 2 ? "xx" : "xxx";
    const onesPart = ["", "i", "ii", "iii", "iv", "v", "vi", "vii", "viii", "ix"][onesDigit];

    return tensPart + onesPart;
}

export function sumSeatsByGroupName(seatsByGroupName: Record<string, number>): number {
    return Object.values(seatsByGroupName).reduce((total, seats) => total + seats, 0);
}

export function calculateTotalCouncillors(politicalGroups: PoliticalGroup[]): number {
    return politicalGroups.reduce(
        (total, politicalGroup) => total + politicalGroup.councillor_count,
        0
    );
}

/** Largest group first; ties broken alphabetically by group name. */
export function sortGroupAllocationRowsByCouncillorCountDescending(
    rows: GroupAllocationRow[]
): GroupAllocationRow[] {
    return [...rows].sort((left, right) => {
        if (right.councillor_count !== left.councillor_count) {
            return right.councillor_count - left.councillor_count;
        }

        return left.group_name.localeCompare(right.group_name);
    });
}

export function calculatePartyAllocation(input: CouncilSetupInput): PartyAllocationResult {
    const politicalGroupsWithCouncillors = input.political_groups.filter(
        (politicalGroup) => politicalGroup.councillor_count > 0
    );
    const totalCouncillors = calculateTotalCouncillors(politicalGroupsWithCouncillors);
    const groupCalculations: GroupCalculation[] = politicalGroupsWithCouncillors.map((politicalGroup) => {
        const rawEntitlement =
            (input.total_committee_seats * politicalGroup.councillor_count) / totalCouncillors;
        const flooredSeats = Math.floor(rawEntitlement);

        return {
            group: politicalGroup,
            percentage_of_council: politicalGroup.councillor_count / totalCouncillors,
            raw_entitlement: rawEntitlement,
            floored_seats: flooredSeats,
            fractional_part: rawEntitlement - flooredSeats,
            final_seats: flooredSeats,
        };
    });

    let seatsRemaining =
        input.total_committee_seats -
        groupCalculations.reduce((total, groupCalculation) => total + groupCalculation.floored_seats, 0);

    const sortedByFraction = [...groupCalculations].sort((left, right) => {
        if (right.fractional_part !== left.fractional_part) {
            return right.fractional_part - left.fractional_part;
        }

        return left.group.name.localeCompare(right.group.name);
    });

    const integerSeatsAllocated = groupCalculations.reduce(
        (total, groupCalculation) => total + groupCalculation.floored_seats,
        0
    );

    let roundingSubstepIndex = 1;

    const workbookSteps: PartyAllocationWorkbookStep[] = [
        {
            step_number: roundingSubstepIndex,
            label: "Round each share down to whole seats",
            seats_by_group_name: seatsByGroupNameFromCalculations(
                groupCalculations,
                (groupCalculation) => groupCalculation.floored_seats
            ),
            total_seats_allocated: integerSeatsAllocated,
            description: buildIntegerStepDescription(
                integerSeatsAllocated,
                seatsRemaining,
                input.total_committee_seats
            ),
        },
    ];

    roundingSubstepIndex += 1;

    const seatsAfterEachStep = new Map<string, number>();
    for (const groupCalculation of groupCalculations) {
        seatsAfterEachStep.set(groupCalculation.group.name, groupCalculation.floored_seats);
    }

    let allCommitteeSeatsAllocatedMessage: string | null = null;
    let roundIndex = 0;
    while (seatsRemaining > 0 && roundIndex < sortedByFraction.length) {
        const groupCalculation = sortedByFraction[roundIndex];
        groupCalculation.final_seats += 1;

        const updatedSeats = seatsAfterEachStep.get(groupCalculation.group.name)! + 1;
        seatsAfterEachStep.set(groupCalculation.group.name, updatedSeats);

        const seatsRemainingAfterThisStep = seatsRemaining - 1;

        const roundingStepTitle = "One extra seat to " + groupCalculation.group.name;

        const seatsAllocatedBeforeThisStep = input.total_committee_seats - seatsRemaining;

        workbookSteps.push({
            step_number: roundingSubstepIndex,
            label: roundingStepTitle,
            seats_by_group_name: Object.fromEntries(seatsAfterEachStep.entries()),
            total_seats_allocated: sumSeatsByGroupName(
                Object.fromEntries(seatsAfterEachStep.entries())
            ),
            description: buildRoundingStepDescription(
                seatsAllocatedBeforeThisStep,
                input.total_committee_seats,
                groupCalculation.group.name,
                groupCalculation.fractional_part,
                seatsRemainingAfterThisStep
            ),
        });

        if (seatsRemainingAfterThisStep <= 0) {
            allCommitteeSeatsAllocatedMessage = "Every committee seat has now been assigned.";
        }

        seatsRemaining -= 1;
        roundIndex += 1;
        roundingSubstepIndex += 1;

        if (roundIndex >= sortedByFraction.length && seatsRemaining > 0) {
            roundIndex = 0;
        }
    }

    const rows: GroupAllocationRow[] = groupCalculations.map((groupCalculation) => ({
        group_name: groupCalculation.group.name,
        councillor_count: groupCalculation.group.councillor_count,
        percentage_of_council: groupCalculation.percentage_of_council,
        raw_entitlement: groupCalculation.raw_entitlement,
        floored_seats: groupCalculation.floored_seats,
        final_seats: groupCalculation.final_seats,
    }));

    const totalAllocatedSeats = rows.reduce((total, row) => total + row.final_seats, 0);
    const sortedRows = sortGroupAllocationRowsByCouncillorCountDescending(rows);

    return {
        total_councillors: totalCouncillors,
        total_committee_seats: input.total_committee_seats,
        rows: sortedRows,
        total_allocated_seats: totalAllocatedSeats,
        workbook_steps: workbookSteps,
        all_committee_seats_allocated_message: allCommitteeSeatsAllocatedMessage,
    };
}

function seatsByGroupNameFromCalculations(
    groupCalculations: GroupCalculation[],
    seatValue: (groupCalculation: GroupCalculation) => number
): Record<string, number> {
    const seatsByGroupName: Record<string, number> = {};
    for (const groupCalculation of groupCalculations) {
        seatsByGroupName[groupCalculation.group.name] = seatValue(groupCalculation);
    }
    return seatsByGroupName;
}

function buildIntegerStepDescription(
    integerSeatsAllocated: number,
    seatsRemaining: number,
    totalCommitteeSeats: number
): string | null {
    if (seatsRemaining <= 0) {
        return null;
    }

    const seatWord = seatsRemaining === 1 ? "seat" : "seats";
    const remainWord = seatsRemaining === 1 ? "remains" : "remain";
    return (
        "Round each group's exact entitlement down to whole seats (" +
        integerSeatsAllocated +
        " seats assigned in total). " +
        seatsRemaining +
        " " +
        seatWord +
        " " +
        remainWord +
        " out of " +
        totalCommitteeSeats +
        "; each following step gives one extra seat to whichever group has the largest amount left after rounding down."
    );
}

function buildRoundingStepDescription(
    seatsAllocatedBeforeThisStep: number,
    totalCommitteeSeats: number,
    groupName: string,
    fractionalPart: number,
    seatsRemainingAfterThisStep: number
): string {
    let description =
        "We have allocated " +
        seatsAllocatedBeforeThisStep +
        "/" +
        totalCommitteeSeats +
        " seats. To allocate the next, we look at which group has the largest fractional part left from their exact entitlement. " +
        groupName +
        " has the largest (" +
        formatNumber(fractionalPart) +
        "), so one extra seat is allocated to " +
        groupName +
        ".";

    if (seatsRemainingAfterThisStep <= 0) {
        return description;
    }

    const seatWord = seatsRemainingAfterThisStep === 1 ? "seat" : "seats";
    description +=
        " " +
        seatsRemainingAfterThisStep +
        " " +
        seatWord +
        " still to allocate.";

    return description;
}

interface CouncilSetupValidationResult {
    valid: boolean;
    error: string | null;
    warning: string | null;
    total_councillors: number;
}

export function validateCouncilSetup(input: CouncilSetupValidationInput): CouncilSetupValidationResult {
    const totalCouncillors = calculateTotalCouncillors(input.political_groups);

    if (input.political_groups.length === 0) {
        return {
            valid: false,
            error: "Add at least one political group.",
            warning: null,
            total_councillors: 0,
        };
    }

    for (const politicalGroup of input.political_groups) {
        if (politicalGroup.name.trim() === "") {
            return {
                valid: false,
                error: "Each political group needs a name.",
                warning: null,
                total_councillors: totalCouncillors,
            };
        }

        if (politicalGroup.councillor_count < 0 || !Number.isInteger(politicalGroup.councillor_count)) {
            return {
                valid: false,
                error: "Councillor counts must be whole numbers of zero or more.",
                warning: null,
                total_councillors: totalCouncillors,
            };
        }
    }

    if (totalCouncillors <= 0) {
        return {
            valid: false,
            error: "At least one councillor is required across all groups.",
            warning: null,
            total_councillors: totalCouncillors,
        };
    }

    if (
        input.expected_total_councillors <= 0 ||
        !Number.isInteger(input.expected_total_councillors)
    ) {
        return {
            valid: false,
            error: "Enter the total number of councillors on the council.",
            warning: null,
            total_councillors: totalCouncillors,
        };
    }

    if (totalCouncillors !== input.expected_total_councillors) {
        return {
            valid: false,
            error:
                "The councillor counts across groups must add up to " +
                input.expected_total_councillors +
                " (currently " +
                totalCouncillors +
                ").",
            warning: null,
            total_councillors: totalCouncillors,
        };
    }

    if (input.total_committee_seats <= 0 || !Number.isInteger(input.total_committee_seats)) {
        return {
            valid: false,
            error: "Total committee seats must be a whole number greater than zero.",
            warning: null,
            total_councillors: totalCouncillors,
        };
    }

    let warning: string | null = null;
    const groupsWithMembers = input.political_groups.filter(
        (politicalGroup) => politicalGroup.councillor_count > 0
    );
    if (groupsWithMembers.length === 1) {
        warning =
            "Only one group has councillors. This tool is meant for councils where seats are shared across several groups.";
    }

    return {
        valid: true,
        error: null,
        warning,
        total_councillors: totalCouncillors,
    };
}

export function formatPercentage(percentage: number): string {
    return (percentage * 100).toFixed(1) + "%";
}

export function formatNumber(value: number, decimalPlaces: number = 2): string {
    return value.toFixed(decimalPlaces);
}
