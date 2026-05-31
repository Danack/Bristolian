import {
    columnTotalForMatrix,
    matrixSeatCount,
    rowTotalForGroup,
} from "./committee_distribution_matrix";
import type {Committee, CommitteeDistributionState} from "./types";

export function getRawEntitlementForGroupOnCommittee(
    finalSeatsForGroup: number,
    committeeSeatCount: number,
    totalCommitteeSeats: number
): number {
    return (finalSeatsForGroup * committeeSeatCount) / totalCommitteeSeats;
}

/** At most one seat above the floor on a committee for a group (ceil of proportional share). */
export function getMaximumSeatsForGroupOnCommittee(
    finalSeatsForGroup: number,
    committeeSeatCount: number,
    totalCommitteeSeats: number
): number {
    const rawEntitlement = getRawEntitlementForGroupOnCommittee(
        finalSeatsForGroup,
        committeeSeatCount,
        totalCommitteeSeats
    );

    return Math.ceil(rawEntitlement);
}

/** Whole seats this group could still place on this committee (column space and per-group cap). */
export function getSeatCapacityForGroupOnCommittee(
    matrix: Record<string, Record<string, number>>,
    groupName: string,
    committee: Committee,
    finalSeatsForGroup: number,
    totalCommitteeSeats: number
): number {
    const columnTotal = columnTotalForMatrix(matrix, committee.name);

    if (columnTotal >= committee.seat_count) {
        return 0;
    }

    const currentSeats = matrixSeatCount(matrix[groupName]?.[committee.name]);
    const maximumSeats = getMaximumSeatsForGroupOnCommittee(
        finalSeatsForGroup,
        committee.seat_count,
        totalCommitteeSeats
    );
    const seatsBelowGroupCap = Math.max(0, maximumSeats - currentSeats);
    const columnRemaining = committee.seat_count - columnTotal;

    return Math.min(seatsBelowGroupCap, columnRemaining);
}

export function canGroupReceiveAnotherSeatOnCommittee(
    matrix: Record<string, Record<string, number>>,
    groupName: string,
    committee: Committee,
    finalSeatsForGroup: number,
    totalCommitteeSeats: number
): boolean {
    return (
        getSeatCapacityForGroupOnCommittee(
            matrix,
            groupName,
            committee,
            finalSeatsForGroup,
            totalCommitteeSeats
        ) > 0
    );
}

export function getGroupsAfterInAssignmentTurnOrder(
    distributionState: CommitteeDistributionState,
    groupName: string
): string[] {
    const turnOrderIndex = distributionState.established_assignment_turn_order.indexOf(groupName);

    if (turnOrderIndex < 0) {
        return [];
    }

    return distributionState.established_assignment_turn_order.slice(turnOrderIndex + 1);
}

/**
 * Whether a group can still place all of its remaining committee seats without exceeding
 * per-committee caps or filling committees past their size.
 */
export function canGroupCompleteRemainderAssignment(
    matrix: Record<string, Record<string, number>>,
    distributionState: CommitteeDistributionState,
    groupName: string
): boolean {
    const finalSeatsForGroup = distributionState.group_final_seats_by_name[groupName] ?? 0;
    const seatsStillNeeded = finalSeatsForGroup - rowTotalForGroup(matrix, groupName);

    if (seatsStillNeeded <= 0) {
        return true;
    }

    let totalSeatCapacity = 0;

    for (const committee of distributionState.committees) {
        totalSeatCapacity += getSeatCapacityForGroupOnCommittee(
            matrix,
            groupName,
            committee,
            finalSeatsForGroup,
            distributionState.total_committee_seats
        );
    }

    return totalSeatCapacity >= seatsStillNeeded;
}

/**
 * Parties that choose after {@link groupName} must still be able to finish within caps.
 * The largest party (e.g. Bristol Green) may only add one seat per committee; earlier
 * choices must not use up committee space so badly that they cannot.
 */
export function areLaterPartyRemainderAssignmentsFeasible(
    matrix: Record<string, Record<string, number>>,
    distributionState: CommitteeDistributionState,
    groupName: string
): boolean {
    for (const laterGroupName of getGroupsAfterInAssignmentTurnOrder(distributionState, groupName)) {
        if (!canGroupCompleteRemainderAssignment(matrix, distributionState, laterGroupName)) {
            return false;
        }
    }

    return true;
}
