import {describe, expect, test} from "@jest/globals";
import {getExampleCouncilById} from "./example_councils";
import {NO_EXAMPLE_COUNCIL_SELECTED} from "./page_config";
import {
    formatCommitteeSeatsUrlSearch,
    restoreCommitteeSeatsStateFromUrl,
    type CommitteeSeatsUrlPanelState,
} from "./url_state";
function createBristolAllocationState(): CommitteeSeatsUrlPanelState {
    const bristol = getExampleCouncilById("bristol");
    expect(bristol).toBeDefined();

    const restored = restoreCommitteeSeatsStateFromUrl(
        "?step=allocation&source=example&example=bristol&seats=144&councillors=70&independents=excluded&groups=" +
            encodeURIComponent("Green|34") +
            "," +
            encodeURIComponent("Labour|19") +
            "," +
            encodeURIComponent("Liberal Democrat|9") +
            "," +
            encodeURIComponent("Conservative|7") +
            "," +
            encodeURIComponent("Independent|1")
    );
    expect(restored).not.toBeNull();
    return restored!;
}

describe("committee_seats url_state", () => {
    test("empty search returns null", () => {
        expect(restoreCommitteeSeatsStateFromUrl("")).toBeNull();
        expect(restoreCommitteeSeatsStateFromUrl("?")).toBeNull();
    });

    test("choose step round-trips example selection", () => {
        const state: CommitteeSeatsUrlPanelState = {
            wizard_step: 1,
            council_setup_substep: "choose_data_source",
            data_source_mode: "example",
            selected_example_council_id: "bristol",
            political_groups: [],
            committees: [],
            total_committee_seats: 0,
            expected_total_councillors: 0,
            allocate_seats_to_independents: null,
            error: null,
            warning: null,
            allocation_result: null,
        };

        const search = formatCommitteeSeatsUrlSearch(state);
        expect(search).toBe("?step=choose&source=example&example=bristol");

        const restored = restoreCommitteeSeatsStateFromUrl(search);
        expect(restored?.selected_example_council_id).toBe("bristol");
        expect(restored?.council_setup_substep).toBe("choose_data_source");
    });

    test("legacy setup step round-trips to political groups substep", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=setup&source=example&example=bristol&seats=144&councillors=70&groups=" +
                encodeURIComponent("Libdems|9") +
                "," +
                encodeURIComponent("Conservatives|7")
        );

        expect(restored?.council_setup_substep).toBe("enter_political_groups");
        expect(restored?.political_groups.find((group) => group.name === "Liberal Democrat")?.councillor_count).toBe(
            9
        );
        expect(restored?.political_groups.find((group) => group.name === "Conservative")?.councillor_count).toBe(7);
    });

    test("totals step round-trips council figures without groups", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=totals&source=custom&seats=100&councillors=70"
        );

        expect(restored?.council_setup_substep).toBe("enter_council_totals");
        expect(restored?.total_committee_seats).toBe(100);
        expect(restored?.expected_total_councillors).toBe(70);
        expect(restored?.political_groups).toHaveLength(0);
    });

    test("totals step for example council restores political group counts when groups omitted from URL", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=totals&source=example&example=bristol&seats=144&councillors=70"
        );

        expect(restored?.council_setup_substep).toBe("enter_council_totals");
        expect(restored?.political_groups.find((group) => group.name === "Green")?.councillor_count).toBe(34);
        expect(restored?.political_groups.find((group) => group.name === "Labour")?.councillor_count).toBe(19);
    });

    test("independents step pre-selects bristol example default", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=independents&source=example&example=bristol&seats=144&councillors=70&groups=" +
                encodeURIComponent("Green|34") +
                "," +
                encodeURIComponent("Independent|1")
        );

        expect(restored?.council_setup_substep).toBe("choose_independent_allocation");
        expect(restored?.allocate_seats_to_independents).toBe(false);
    });

    test("independents step pre-selects barnet example default", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=independents&source=example&example=barnet&seats=56&councillors=63&groups=" +
                encodeURIComponent("Conservative|31") +
                "," +
                encodeURIComponent("Labour|31") +
                "," +
                encodeURIComponent("Independent|1")
        );

        expect(restored?.council_setup_substep).toBe("choose_independent_allocation");
        expect(restored?.allocate_seats_to_independents).toBe(false);
    });

    test("independents step leaves choice unset when example has no default", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=independents&source=example&example=bcp&seats=111&councillors=76&groups=" +
                encodeURIComponent("Liberal Democrat|28") +
                "," +
                encodeURIComponent("Labour|8") +
                "," +
                encodeURIComponent("Independent|4")
        );

        expect(restored?.council_setup_substep).toBe("choose_independent_allocation");
        expect(restored?.allocate_seats_to_independents).toBeNull();
    });

    test("allocation URL can exclude independents from seat calculation", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=allocation&source=example&example=bristol&seats=144&councillors=70&independents=excluded&groups=" +
                encodeURIComponent("Green|34") +
                "," +
                encodeURIComponent("Labour|19") +
                "," +
                encodeURIComponent("Liberal Democrat|9") +
                "," +
                encodeURIComponent("Conservative|7") +
                "," +
                encodeURIComponent("Independent|1")
        );

        expect(restored?.allocate_seats_to_independents).toBe(false);
        expect(
            restored?.allocation_result?.rows.find((row) => row.group_name === "Independent")
        ).toBeUndefined();
        expect(restored?.allocation_result?.total_allocated_seats).toBe(144);
    });

    test("custom allocation URL with independents restores without error state", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=allocation&source=custom&seats=144&councillors=70&groups=" +
                encodeURIComponent("Labour|19") +
                "," +
                encodeURIComponent("Conservative|7") +
                "," +
                encodeURIComponent("Liberal Democrat|9") +
                "," +
                encodeURIComponent("Green|34") +
                "," +
                encodeURIComponent("Independent|1")
        );

        expect(restored).not.toBeNull();
        expect(restored?.wizard_step).toBe(2);
        expect(restored?.allocation_result).not.toBeNull();
        expect(restored?.allocate_seats_to_independents).toBe(true);
    });

    test("bristol allocation URL without independents param excludes independents by default", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=allocation&source=example&example=bristol&seats=144&councillors=70&groups=" +
                encodeURIComponent("Green|34") +
                "," +
                encodeURIComponent("Labour|19") +
                "," +
                encodeURIComponent("Liberal Democrat|9") +
                "," +
                encodeURIComponent("Conservative|7") +
                "," +
                encodeURIComponent("Independent|1")
        );

        expect(restored?.allocate_seats_to_independents).toBe(false);
        expect(restored?.allocation_result?.rows.find((row) => row.group_name === "Independent")).toBeUndefined();
        expect(restored?.allocation_result?.rows.find((row) => row.group_name === "Green")?.final_seats).toBe(71);
    });

    test("allocation step restores workbook result", () => {
        const restored = createBristolAllocationState();

        expect(restored.wizard_step).toBe(2);
        expect(restored.allocation_result).not.toBeNull();
        expect(restored.allocation_result?.total_allocated_seats).toBe(144);
        expect(restored.allocation_result?.rows.find((row) => row.group_name === "Green")?.final_seats).toBe(
            71
        );
    });

    test("allocation URL round-trips through format", () => {
        const restored = createBristolAllocationState();
        const search = formatCommitteeSeatsUrlSearch(restored);
        expect(search).toContain("step=allocation");

        const again = restoreCommitteeSeatsStateFromUrl(search);
        expect(again?.allocation_result?.total_allocated_seats).toBe(144);
    });

    test("next steps URL round-trips through format", () => {
        const restored = {
            ...createBristolAllocationState(),
            wizard_step: 3,
        };
        const search = formatCommitteeSeatsUrlSearch(restored);
        expect(search).toContain("step=next_steps");

        const again = restoreCommitteeSeatsStateFromUrl(search);
        expect(again?.wizard_step).toBe(3);
        expect(again?.allocation_result?.total_allocated_seats).toBe(144);
    });

    test("invalid allocation URL falls back to setup with error", () => {
        const restored = restoreCommitteeSeatsStateFromUrl(
            "?step=allocation&source=custom&seats=144&groups="
        );

        expect(restored?.wizard_step).toBe(1);
        expect(restored?.council_setup_substep).toBe("enter_political_groups");
        expect(restored?.allocation_result).toBeNull();
        expect(restored?.error).not.toBeNull();
    });

    test("malformed URL returns null", () => {
        expect(restoreCommitteeSeatsStateFromUrl("?step=allocation&source=custom&seats=0&groups=Labour|10")).toBeNull();
    });

    test("choose step with no council selected round-trips without example parameter", () => {
        const state: CommitteeSeatsUrlPanelState = {
            wizard_step: 1,
            council_setup_substep: "choose_data_source",
            data_source_mode: "example",
            selected_example_council_id: NO_EXAMPLE_COUNCIL_SELECTED,
            political_groups: [],
            committees: [],
            total_committee_seats: 0,
            expected_total_councillors: 0,
            allocate_seats_to_independents: null,
            error: null,
            warning: null,
            allocation_result: null,
        };

        expect(formatCommitteeSeatsUrlSearch(state)).toBe("");

        const restored = restoreCommitteeSeatsStateFromUrl("?step=choose&source=example");
        expect(restored?.selected_example_council_id).toBe(NO_EXAMPLE_COUNCIL_SELECTED);
        expect(restored?.council_setup_substep).toBe("choose_data_source");
    });

    test("default choose state produces empty search string", () => {
        const state: CommitteeSeatsUrlPanelState = {
            wizard_step: 1,
            council_setup_substep: "choose_data_source",
            data_source_mode: "example",
            selected_example_council_id: NO_EXAMPLE_COUNCIL_SELECTED,
            political_groups: [],
            committees: [],
            total_committee_seats: 0,
            expected_total_councillors: 0,
            allocate_seats_to_independents: null,
            error: null,
            warning: null,
            allocation_result: null,
        };

        expect(formatCommitteeSeatsUrlSearch(state)).toBe("");
    });
});
