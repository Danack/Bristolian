import {formatNumber, sortGroupAllocationRowsByCouncillorCountDescending} from "./calculate_party_allocation";
import type {Committee, CommitteeDistributionState, GroupAllocationRow} from "./types";

export interface GroupFloorCalculation {
    group_name: string;
    final_seats: number;
    cells: {
        committee_index: number;
        committee_name: string;
        committee_seat_count: number;
        raw_entitlement: number;
        floored_seats: number;
        fractional_part: number;
    }[];
    remainder_seats: number;
}

export function buildGroupFloorCalculations(
    committees: Committee[],
    allocationRows: GroupAllocationRow[],
    totalCommitteeSeats: number
): GroupFloorCalculation[] {
    const rowsWithSeats = allocationRows.filter((row) => row.final_seats > 0);
    const sortedRows = sortGroupAllocationRowsByCouncillorCountDescending(rowsWithSeats);
    const groupCalculations: GroupFloorCalculation[] = [];

    for (const row of sortedRows) {
        const cells = committees.map((committee, committeeIndex) => {
            const rawEntitlement =
                (row.final_seats * committee.seat_count) / totalCommitteeSeats;
            const flooredSeats = Math.floor(rawEntitlement);

            return {
                committee_index: committeeIndex,
                committee_name: committee.name,
                committee_seat_count: committee.seat_count,
                raw_entitlement: rawEntitlement,
                floored_seats: flooredSeats,
                fractional_part: rawEntitlement - flooredSeats,
            };
        });

        const flooredTotal = cells.reduce((total, cell) => total + cell.floored_seats, 0);

        groupCalculations.push({
            group_name: row.group_name,
            final_seats: row.final_seats,
            cells,
            remainder_seats: row.final_seats - flooredTotal,
        });
    }

    return groupCalculations;
}

export interface CommitteeFloorExampleEntry {
    group_name: string;
    group_final_seats: number;
    raw_entitlement: number;
    floored_seats: number;
}

export interface CommitteeFloorCalculationExample {
    committee_name: string;
    committee_seat_count: number;
    total_committee_seats: number;
    /** First group in allocation order (largest councillor count), used for the worked example. */
    primary_entry: CommitteeFloorExampleEntry;
}

export function buildCommitteeFloorCalculationExample(
    distributionState: CommitteeDistributionState,
    committeeIndex: number = 0
): CommitteeFloorCalculationExample | null {
    const committee = distributionState.committees[committeeIndex];
    if (committee === undefined || distributionState.group_names.length === 0) {
        return null;
    }

    const primaryGroupName = distributionState.group_names[0];
    const groupFinalSeats = distributionState.group_final_seats_by_name[primaryGroupName] ?? 0;
    if (groupFinalSeats <= 0) {
        return null;
    }

    const rawEntitlement =
        (groupFinalSeats * committee.seat_count) / distributionState.total_committee_seats;
    const flooredSeats = Math.floor(rawEntitlement);

    return {
        committee_name: committee.name,
        committee_seat_count: committee.seat_count,
        total_committee_seats: distributionState.total_committee_seats,
        primary_entry: {
            group_name: primaryGroupName,
            group_final_seats: groupFinalSeats,
            raw_entitlement: rawEntitlement,
            floored_seats: flooredSeats,
        },
    };
}

export function formatCommitteeFloorExampleCalculation(example: CommitteeFloorCalculationExample): string {
    const entry = example.primary_entry;

    return (
        entry.group_final_seats +
        " × " +
        example.committee_seat_count +
        " ÷ " +
        example.total_committee_seats +
        " = " +
        formatNumber(entry.raw_entitlement) +
        ", rounded down to " +
        entry.floored_seats
    );
}
