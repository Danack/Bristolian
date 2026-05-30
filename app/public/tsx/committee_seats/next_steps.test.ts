import {describe, expect, test} from "@jest/globals";
import {calculatePartyAllocation} from "./calculate_party_allocation";
import {getNextStepsAllocationRows} from "./next_steps";

describe("next_steps", () => {
    test("getNextStepsAllocationRows omits groups with zero councillors", () => {
        const result = calculatePartyAllocation({
            political_groups: [
                {name: "Labour", councillor_count: 30},
                {name: "Conservative", councillor_count: 20},
                {name: "Green", councillor_count: 0},
            ],
            total_committee_seats: 10,
        });

        expect(getNextStepsAllocationRows(result.rows).map((row) => row.group_name)).toEqual([
            "Labour",
            "Conservative",
        ]);
    });
});
