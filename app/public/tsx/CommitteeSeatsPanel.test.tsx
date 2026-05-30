import {describe, expect, test} from "@jest/globals";
import {
    calculatePartyAllocation,
    calculateTotalCouncillors,
    formatLowerRomanNumeral,
    validateCouncilSetup,
} from "./committee_seats/calculate_party_allocation";
import {
    applyExampleCouncilToFormState,
    EXAMPLE_COUNCILS,
    getExampleCouncilById,
    getPrefilledTotalCommitteeSeats,
} from "./committee_seats/example_councils";
import {
    getVisibleWizardDisplaySteps,
    getWizardDisplayStepsRemaining,
    WizardDisplayStep,
} from "./committee_seats/wizard_display_step";
import {COUNCIL_SETUP_POLITICAL_GROUP_ROW_COUNT} from "./committee_seats/political_groups_form";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./committee_seats/political_groups_form";
import {
    politicalGroupsForSeatAllocation,
    STANDARD_INDEPENDENT_GROUP_NAME,
} from "./committee_seats/independent_allocation";
import type {ExampleCouncil} from "./committee_seats/types";

describe("example_councils", () => {
    test("barnet has groups and committees from documented data", () => {
        const barnet = getExampleCouncilById("barnet");
        expect(barnet).toBeDefined();
        expect(barnet?.committees.length).toBe(7);
        expect(getPrefilledTotalCommitteeSeats(barnet!)).toBe(56);
        expect(calculateTotalCouncillors(barnet!.political_groups)).toBe(63);
    });

    test("bristol prefills political groups and total committee seats", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();
        expect(bristol?.committees.length).toBe(0);
        expect(bristol?.total_committee_seats).toBe(144);
        expect(getPrefilledTotalCommitteeSeats(bristol!)).toBe(144);
        expect(calculateTotalCouncillors(bristol!.political_groups)).toBe(70);

        const applied = applyExampleCouncilToFormState(bristol!);
        expect(applied.total_committee_seats).toBe(144);
        expect(applied.political_groups.length).toBe(COUNCIL_SETUP_POLITICAL_GROUP_ROW_COUNT);
        expect(applied.political_groups.find((group) => group.name === "Green")?.councillor_count).toBe(34);
    });

    test("lambeth prefills political groups, vacancies, and 35 committee seats", () => {
        const lambeth = getExampleCouncilById("lambeth");
        expect(lambeth).toBeDefined();
        expect(getPrefilledTotalCommitteeSeats(lambeth!)).toBe(35);
        expect(lambeth?.committees.length).toBe(7);
        expect(calculateTotalCouncillors(lambeth!.political_groups)).toBe(63);

        const applied = applyExampleCouncilToFormState(lambeth!);
        expect(applied.total_committee_seats).toBe(35);
        expect(applied.expected_total_councillors).toBe(63);
        expect(applied.political_groups.find((group) => group.name === "Vacancy")?.councillor_count).toBe(2);
        expect(applied.political_groups.find((group) => group.name === "Green")?.councillor_count).toBe(27);

        expect(
            validateCouncilSetup({
                political_groups: lambeth!.political_groups,
                total_committee_seats: 35,
                expected_total_councillors: 63,
            }).valid
        ).toBe(true);
    });

    test("sheffield prefills political groups and 168 committee seats", () => {
        const sheffield = getExampleCouncilById("sheffield");
        expect(sheffield).toBeDefined();
        expect(getPrefilledTotalCommitteeSeats(sheffield!)).toBe(168);
        expect(calculateTotalCouncillors(sheffield!.political_groups)).toBe(84);
        expect(sheffield?.allocate_seats_to_independents).toBe(true);

        const applied = applyExampleCouncilToFormState(sheffield!);
        expect(applied.total_committee_seats).toBe(168);
        expect(applied.expected_total_councillors).toBe(84);
        expect(applied.political_groups.find((group) => group.name === "Labour")?.councillor_count).toBe(25);
        expect(applied.political_groups.find((group) => group.name === "Independent")?.councillor_count).toBe(4);
        expect(
            applied.political_groups.find((group) => group.name === "Sheffield Community Councillors")
                ?.councillor_count
        ).toBe(2);
    });

    test("bristol and barnet pass council setup validation with documented figures", () => {
        for (const exampleCouncilId of ["bristol", "barnet"] as const) {
            const exampleCouncil = getExampleCouncilById(exampleCouncilId)!;
            const totalCommitteeSeats = getPrefilledTotalCommitteeSeats(exampleCouncil)!;

            expect(
                validateCouncilSetup({
                    political_groups: exampleCouncil.political_groups,
                    total_committee_seats: totalCommitteeSeats,
                    expected_total_councillors: calculateTotalCouncillors(exampleCouncil.political_groups),
                }).valid
            ).toBe(true);
        }
    });

    test("example with group counts only requires entering committee seats", () => {
        const testCouncil: ExampleCouncil = {
            id: "test_council",
            display_name: "Test council",
            political_groups: [
                {name: "Labour", councillor_count: 20},
                {name: "Conservative", councillor_count: 15},
                {name: "Green", councillor_count: 10},
            ],
            committees: [],
        };
        expect(getPrefilledTotalCommitteeSeats(testCouncil)).toBeNull();

        const applied = applyExampleCouncilToFormState(testCouncil);
        expect(applied.total_committee_seats).toBe(0);
        expect(applied.expected_total_councillors).toBe(45);
        expect(applied.political_groups.find((group) => group.name === "Labour")?.councillor_count).toBe(20);
    });

    test("when example has committees, seat total is sum of committee sizes", () => {
        for (const exampleCouncil of EXAMPLE_COUNCILS) {
            if (exampleCouncil.committees.length === 0) {
                continue;
            }

            const committeeSeatTotal = exampleCouncil.committees.reduce(
                (total, committee) => total + committee.seat_count,
                0
            );
            expect(getPrefilledTotalCommitteeSeats(exampleCouncil)).toBe(committeeSeatTotal);
        }
    });
});

describe("validateCouncilSetup", () => {
    test("accepts valid custom input", () => {
        const validation = validateCouncilSetup({
            political_groups: [{name: "Labour", councillor_count: 30}],
            total_committee_seats: 100,
            expected_total_councillors: 30,
        });

        expect(validation.valid).toBe(true);
        expect(validation.error).toBeNull();
        expect(validation.total_councillors).toBe(30);
    });

    test("rejects empty group name", () => {
        const validation = validateCouncilSetup({
            political_groups: [{name: "  ", councillor_count: 1}],
            total_committee_seats: 10,
            expected_total_councillors: 1,
        });

        expect(validation.valid).toBe(false);
        expect(validation.error).toContain("name");
    });

    test("rejects zero total committee seats", () => {
        const validation = validateCouncilSetup({
            political_groups: [{name: "Labour", councillor_count: 10}],
            total_committee_seats: 0,
            expected_total_councillors: 10,
        });

        expect(validation.valid).toBe(false);
    });

    test("rejects councillor total that does not match council total", () => {
        const validation = validateCouncilSetup({
            political_groups: [
                {name: "Labour", councillor_count: 30},
                {name: "Green", councillor_count: 10},
            ],
            total_committee_seats: 100,
            expected_total_councillors: 50,
        });

        expect(validation.valid).toBe(false);
        expect(validation.error).toContain("must add up to 50");
        expect(validation.error).toContain("40");
    });

    test("warns when only one group has councillors", () => {
        const validation = validateCouncilSetup({
            political_groups: [{name: "Labour", councillor_count: 50}],
            total_committee_seats: 100,
            expected_total_councillors: 50,
        });

        expect(validation.valid).toBe(true);
        expect(validation.warning).not.toBeNull();
    });
});

describe("calculatePartyAllocation", () => {
    test("bristol party counts with 144 committee seats", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const result = calculatePartyAllocation({
            political_groups: bristol!.political_groups,
            total_committee_seats: getPrefilledTotalCommitteeSeats(bristol!)!,
        });

        expect(result.total_allocated_seats).toBe(144);

        const green = result.rows.find((row) => row.group_name === "Green");
        const labour = result.rows.find((row) => row.group_name === "Labour");
        const liberalDemocrat = result.rows.find((row) => row.group_name === "Liberal Democrat");
        const conservative = result.rows.find((row) => row.group_name === "Conservative");
        const independent = result.rows.find((row) => row.group_name === "Independent");

        expect(green?.floored_seats).toBe(69);
        expect(labour?.floored_seats).toBe(39);
        expect(liberalDemocrat?.floored_seats).toBe(18);
        expect(conservative?.floored_seats).toBe(14);
        expect(independent?.floored_seats).toBe(2);

        expect(green?.final_seats).toBe(70);
        expect(labour?.final_seats).toBe(39);
        expect(liberalDemocrat?.final_seats).toBe(19);
        expect(conservative?.final_seats).toBe(14);
        expect(independent?.final_seats).toBe(2);

        expect(result.workbook_steps.length).toBe(3);
        expect(result.workbook_steps[0].label).toBe("i. Round each share down to whole seats");
        expect(result.workbook_steps[0].seats_by_group_name.Green).toBe(green!.floored_seats);
        expect(result.workbook_steps[0].total_seats_allocated).toBe(142);
        expect(result.workbook_steps[1].label).toBe("ii. One extra seat to Green");
        expect(result.workbook_steps[1].seats_by_group_name.Green).toBe(70);
        expect(result.workbook_steps[1].total_seats_allocated).toBe(143);
        expect(result.workbook_steps[2].label).toBe("iii. One extra seat to Liberal Democrat");
        expect(result.workbook_steps[2].seats_by_group_name["Liberal Democrat"]).toBe(19);
        expect(result.workbook_steps[2].total_seats_allocated).toBe(144);

        expect(result.workbook_steps[0].description).toContain(
            "Round each group's exact entitlement down to whole seats"
        );
        expect(result.workbook_steps[0].description).toContain("2 seats remain out of 144");

        expect(result.workbook_steps[1].description).toContain("We have allocated 142/144 seats");
        expect(result.workbook_steps[1].description).toContain(
            "To allocate the next, we look at which group has the largest fractional part left from their exact entitlement"
        );
        expect(result.workbook_steps[1].description).toContain("Green has the largest (0.94)");
        expect(result.workbook_steps[1].description).not.toContain("Sub-step");
        expect(result.workbook_steps[1].description).toContain(
            "Liberal Democrat has the next largest fractional part (0.51)"
        );

        expect(result.workbook_steps[2].description).toContain("We have allocated 143/144 seats");
        expect(result.workbook_steps[2].description).toContain("Liberal Democrat has the largest (0.51)");
        expect(result.workbook_steps[2].description).not.toContain("Sub-step");
        expect(result.workbook_steps[2].description).not.toContain("Every committee seat has now been assigned");
        expect(result.all_committee_seats_allocated_message).toBe(
            "Every committee seat has now been assigned."
        );
    });

    test("bristol excludes independent councillors from allocation when configured", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const formGroups = mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups);
        const groupsForAllocation = politicalGroupsForSeatAllocation(formGroups, false);

        const result = calculatePartyAllocation({
            political_groups: groupsForAllocation,
            total_committee_seats: getPrefilledTotalCommitteeSeats(bristol!)!,
        });

        expect(result.rows.find((row) => row.group_name === STANDARD_INDEPENDENT_GROUP_NAME)).toBeUndefined();
        expect(result.total_allocated_seats).toBe(144);
        expect(result.rows).toHaveLength(4);
    });

    test("excludes groups with zero councillors from allocation rows", () => {
        const result = calculatePartyAllocation({
            political_groups: [
                {name: "Labour", councillor_count: 40},
                {name: "Conservative", councillor_count: 30},
                {name: "Reform UK", councillor_count: 0},
                {name: "Green", councillor_count: 0},
            ],
            total_committee_seats: 10,
        });

        expect(result.rows.map((row) => row.group_name)).toEqual(["Labour", "Conservative"]);
        expect(result.total_allocated_seats).toBe(10);
    });

    test("lambeth allocates all committee seats when vacancies are excluded from proportion", () => {
        const lambeth = getExampleCouncilById("lambeth");
        expect(lambeth).toBeDefined();

        const applied = applyExampleCouncilToFormState(lambeth!);
        const groupsForAllocation = politicalGroupsForSeatAllocation(applied.political_groups, true);

        const result = calculatePartyAllocation({
            political_groups: groupsForAllocation,
            total_committee_seats: applied.total_committee_seats,
        });

        expect(result.total_allocated_seats).toBe(35);
        expect(result.total_councillors).toBe(61);
        const seatsByGroup = Object.fromEntries(
            result.rows.map((row) => [row.group_name, row.final_seats])
        );
        expect(seatsByGroup).toEqual({
            Green: 15,
            Labour: 15,
            "Liberal Democrat": 5,
        });
    });

    test("barnet allocates all committee seats", () => {
        const barnet = getExampleCouncilById("barnet");
        expect(barnet).toBeDefined();

        const totalCommitteeSeats = getPrefilledTotalCommitteeSeats(barnet!);
        expect(totalCommitteeSeats).toBe(56);

        const result = calculatePartyAllocation({
            political_groups: barnet!.political_groups,
            total_committee_seats: totalCommitteeSeats!,
        });

        expect(result.total_allocated_seats).toBe(56);
        expect(result.rows.length).toBe(3);
        expect(result.workbook_steps.length).toBeGreaterThan(1);
        expect(result.workbook_steps[0].description).toContain(
            "Round each group's exact entitlement down to whole seats"
        );
        expect(result.workbook_steps[0].description).toContain("2 seats remain out of 56");
    });
});

describe("formatLowerRomanNumeral", () => {
    test("formats rounding sub-step indices", () => {
        expect(formatLowerRomanNumeral(1)).toBe("i");
        expect(formatLowerRomanNumeral(2)).toBe("ii");
        expect(formatLowerRomanNumeral(3)).toBe("iii");
        expect(formatLowerRomanNumeral(10)).toBe("x");
        expect(formatLowerRomanNumeral(11)).toBe("xi");
    });
});

describe("wizard display steps", () => {
    test("six visible steps for councils with independents", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const state = {
            wizard_step: 1,
            council_setup_substep: "choose_data_source",
            political_groups: mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups),
        };

        expect(getVisibleWizardDisplaySteps(state)).toHaveLength(6);
        expect(getWizardDisplayStepsRemaining(state, WizardDisplayStep.ChooseDataSource)).toBe(5);
        expect(getWizardDisplayStepsRemaining(state, WizardDisplayStep.PartyAllocation)).toBe(1);
        expect(getWizardDisplayStepsRemaining(state, WizardDisplayStep.NextSteps)).toBe(0);
    });
});
