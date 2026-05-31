import {describe, expect, test, beforeEach, afterEach} from "@jest/globals";
import {createRef, h, options, render} from "preact";
import {testSlow} from "./test/jest_slow_tests";
import {CommitteeSeatsPanel} from "./CommitteeSeatsPanel";
import type {CommitteeSeatsPanelProps} from "./committee_seats/panel_state";
import {applyExampleCouncilToFormState, getExampleCouncilById} from "./committee_seats/example_councils";
import {INDEPENDENT_ALLOCATION_STEP_COPY} from "./committee_seats/independent_allocation";
import {
    canAddPendingCommitteeSelectionOnCommittee,
    getPartyAssignmentBatch,
    isPendingPartyAssignmentBatchReadyToConfirm,
    type PartyAssignmentBatch,
} from "./committee_seats/calculate_committee_distribution";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./committee_seats/experimental_seat_distribution";
import {getTestCouncilCompleteDistributionState} from "./committee_seats/test_council_distribution_test_fixtures";
import {COMMITTEE_SEATS_PAGE, formatCouncilSetupExampleIntro} from "./committee_seats/page_config";
import {ExperimentalSubstep} from "./committee_seats/panel_state";
import {
    CouncilSetupSubstep,
    DataSourceMode,
    WizardStep,
} from "./committee_seats/panel_state";
import {formatCommitteeSeatsUrlSearch} from "./committee_seats/url_state";

const COMMITTEE_SEATS_PATH = "/tools/committee_seats";

interface MountedCommitteeSeatsPanel {
    panel: CommitteeSeatsPanel;
    container: HTMLElement;
}

function setWindowLocation(search: string): void {
    const pathAndSearch = search === "" ? COMMITTEE_SEATS_PATH : COMMITTEE_SEATS_PATH + search;
    window.history.replaceState(null, "", pathAndSearch);
}

function mountCommitteeSeatsPanel(search: string = ""): MountedCommitteeSeatsPanel {
    setWindowLocation(search);
    const container = document.createElement("div");
    document.body.appendChild(container);

    const panelRef = createRef<CommitteeSeatsPanel>();

    render(h(CommitteeSeatsPanel, {ref: panelRef} as CommitteeSeatsPanelProps), container);

    const panel = panelRef.current;
    if (panel === null) {
        throw new Error("CommitteeSeatsPanel ref was not set");
    }

    return {panel, container};
}

function unmountCommitteeSeatsPanel(mounted: MountedCommitteeSeatsPanel): void {
    render(null, mounted.container);
    mounted.container.remove();
}

function getExampleUseDataButton(container: HTMLElement): HTMLButtonElement {
    const exampleChoice = container.querySelector(".committee_seats_example_choice");
    if (exampleChoice === null) {
        throw new Error("Example council choice block not found");
    }
    const button = exampleChoice.querySelector("button.button_standard");
    if (button === null || !(button instanceof HTMLButtonElement)) {
        throw new Error("Example use-data button not found");
    }
    return button;
}

function getNumberInputValue(container: HTMLElement, inputId: string): number {
    const input = container.querySelector("#" + inputId);
    if (input === null || !(input instanceof HTMLInputElement)) {
        throw new Error("Input #" + inputId + " not found");
    }
    return parseInt(input.value, 10);
}

function getConfirmAssignmentBatchButton(container: HTMLElement): HTMLButtonElement {
    const confirmButton = container.querySelector(
        ".committee_seats_distribution_assignment_confirm_button"
    );
    if (confirmButton === null || !(confirmButton instanceof HTMLButtonElement)) {
        throw new Error("Confirm assignment batch button not found");
    }
    return confirmButton;
}

function selectValidCommitteesForBatch(
    panel: CommitteeSeatsPanel,
    batch: PartyAssignmentBatch
): void {
    const distributionState = panel.state.committee_distribution_state;
    if (distributionState === null) {
        throw new Error("committee_distribution_state is null");
    }

    while (panel.state.distribution_pending_committee_selections.length < batch.seats_to_choose) {
        let addedSelection = false;

        for (
            let committeeIndex = 0;
            committeeIndex < distributionState.committees.length;
            committeeIndex += 1
        ) {
            if (
                !canAddPendingCommitteeSelectionOnCommittee(
                    distributionState,
                    batch,
                    panel.state.distribution_pending_committee_selections,
                    committeeIndex
                )
            ) {
                continue;
            }

            panel.handleDistributionCommitteeChoiceClick(committeeIndex);
            addedSelection = true;
            break;
        }

        expect(addedSelection).toBe(true);
    }

    expect(
        isPendingPartyAssignmentBatchReadyToConfirm(
            distributionState,
            batch,
            panel.state.distribution_pending_committee_selections
        )
    ).toBe(true);
}

function getTextInputValue(container: HTMLElement, inputId: string): string {
    const input = container.querySelector("#" + inputId);
    if (input === null || !(input instanceof HTMLInputElement)) {
        throw new Error("Input #" + inputId + " not found");
    }
    return input.value;
}

describe("CommitteeSeatsPanel component", () => {
    let mounted: MountedCommitteeSeatsPanel;
    let previousDebounceRendering: typeof options.debounceRendering;

    beforeEach(() => {
        previousDebounceRendering = options.debounceRendering;
        options.debounceRendering = (callback) => callback();
        mounted = mountCommitteeSeatsPanel();
    });

    afterEach(() => {
        unmountCommitteeSeatsPanel(mounted);
        setWindowLocation("");
        options.debounceRendering = previousDebounceRendering;
    });

    test("mounts on choose data source with example button disabled", () => {
        const titleLink = mounted.container.querySelector("h1 a");
        expect(titleLink?.textContent).toBe(COMMITTEE_SEATS_PAGE.title);
        expect(titleLink?.getAttribute("href")).toBe(COMMITTEE_SEATS_PAGE.base_path);
        expect(mounted.container.textContent).toContain(COMMITTEE_SEATS_PAGE.choose_source_lead);
        expect(getExampleUseDataButton(mounted.container).disabled).toBe(true);
        expect(mounted.panel.state.council_setup_substep).toBe(CouncilSetupSubstep.ChooseDataSource);
    });

    test("enables example button and updates label when a council is selected", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");

        const useDataButton = getExampleUseDataButton(mounted.container);
        expect(useDataButton.disabled).toBe(false);
        expect(useDataButton.textContent).toContain("Bristol");
        expect(mounted.panel.state.selected_example_council_id).toBe("bristol");
    });

    test("loading bristol example prefills council totals", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();
        const applied = applyExampleCouncilToFormState(bristol!);

        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();

        expect(mounted.panel.state.council_setup_substep).toBe(CouncilSetupSubstep.EnterCouncilTotals);
        expect(mounted.panel.state.data_source_mode).toBe(DataSourceMode.Example);
        expect(mounted.container.textContent).toContain(
            formatCouncilSetupExampleIntro(bristol!.display_name)
        );
        expect(getNumberInputValue(mounted.container, "expected_total_councillors")).toBe(
            applied.expected_total_councillors
        );
        expect(getNumberInputValue(mounted.container, "total_committee_seats")).toBe(
            applied.total_committee_seats
        );
        expect(formatCommitteeSeatsUrlSearch(mounted.panel.state)).toContain("example=bristol");
    });

    test("custom data opens council totals with empty figures", () => {
        mounted.panel.handleChooseCustomData();

        expect(mounted.panel.state.council_setup_substep).toBe(CouncilSetupSubstep.EnterCouncilTotals);
        expect(mounted.panel.state.data_source_mode).toBe(DataSourceMode.Custom);
        expect(mounted.container.textContent).toContain(COMMITTEE_SEATS_PAGE.council_setup_custom_intro);
        expect(getNumberInputValue(mounted.container, "expected_total_councillors")).toBe(0);
        expect(getNumberInputValue(mounted.container, "total_committee_seats")).toBe(0);

        const continueButtons = Array.from(mounted.container.querySelectorAll("button")).filter(
            (button) => button.textContent?.trim() === "Continue"
        );
        expect(continueButtons.every((button) => (button as HTMLButtonElement).disabled)).toBe(true);
    });

    test("continuing from bristol totals shows political groups step", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();

        expect(mounted.panel.state.council_setup_substep).toBe(
            CouncilSetupSubstep.EnterPoliticalGroups
        );
        expect(mounted.container.textContent).toContain(
            COMMITTEE_SEATS_PAGE.council_setup_political_groups_section_title
        );
        expect(mounted.container.querySelector(".committee_seats_political_groups_fieldset")).not.toBeNull();
        expect(mounted.container.textContent).toContain(
            "All Councillors are allocated to a political group."
        );
        expect(getNumberInputValue(mounted.container, "political_group_count_0")).toBe(19);
        expect(getNumberInputValue(mounted.container, "political_group_count_3")).toBe(34);

        const firstAdditionalGroupNameInput = mounted.container.querySelector(
            "#committee_seats_add_political_group_name"
        );
        expect(firstAdditionalGroupNameInput).not.toBeNull();
        expect(firstAdditionalGroupNameInput?.getAttribute("placeholder")).toBe(
            COMMITTEE_SEATS_PAGE.additional_political_group_name_placeholder
        );
        expect(mounted.container.querySelector("#political_group_name_7")).toBeNull();

        (firstAdditionalGroupNameInput as HTMLInputElement).value = "Poole People";
        firstAdditionalGroupNameInput?.dispatchEvent(new Event("input", {bubbles: true}));
        expect(mounted.container.querySelector("#political_group_name_7")).toBeNull();

        const addGroupButton = Array.from(mounted.container.querySelectorAll("button")).find(
            (button) => button.textContent?.trim() === COMMITTEE_SEATS_PAGE.add_political_group_button_label
        );
        expect(addGroupButton).toBeDefined();
        expect((addGroupButton as HTMLButtonElement).disabled).toBe(false);
        addGroupButton?.click();

        expect(mounted.container.querySelector("#political_group_name_7")).toBeNull();
        expect(
            mounted.container.querySelector(".committee_seats_groups_table_first_additional_group_row")
                ?.textContent
        ).toContain("Poole People");
        expect(mounted.container.querySelector("#political_group_count_7")).not.toBeNull();
    });

    test("bristol political groups step continues to independent allocation", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleContinueFromPoliticalGroups();

        expect(mounted.panel.state.council_setup_substep).toBe(
            CouncilSetupSubstep.ChooseIndependentAllocation
        );
        expect(mounted.container.textContent).toContain(INDEPENDENT_ALLOCATION_STEP_COPY.lead);
    });

    test("proceeding to party allocation renders workbook for bristol", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleContinueFromPoliticalGroups();
        mounted.panel.handleIndependentAllocationChoiceChange(false);
        mounted.panel.handleContinueFromIndependentAllocation();

        expect(mounted.panel.state.wizard_step).toBe(WizardStep.PartyAllocation);
        expect(mounted.panel.state.allocation_result).not.toBeNull();
        expect(mounted.container.textContent).toContain(
            COMMITTEE_SEATS_PAGE.allocation_proportional_share_section_title
        );
        expect(mounted.container.textContent).toContain("Final allocation");
    });

    test("advanced seat distribution prefills bristol committees", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleContinueFromPoliticalGroups();
        mounted.panel.handleIndependentAllocationChoiceChange(false);
        mounted.panel.handleContinueFromIndependentAllocation();
        mounted.panel.handleContinueFromPartyAllocation();

        mounted.panel.handleStartExperimentalSeatDistribution();

        expect(mounted.panel.state.wizard_step).toBe(WizardStep.SeatDistributionExperimental);
        expect(mounted.panel.state.experimental_substep).toBe(ExperimentalSubstep.Committees);
        expect(getTextInputValue(mounted.container, "committee_name_0")).toBe("Adult Social Care Committee");
        expect(getNumberInputValue(mounted.container, "committee_seat_count_0")).toBe(9);
        expect(getTextInputValue(mounted.container, "committee_name_8")).toBe("Planning Committee A");
    });

    test("experimental committees add row requires name and seat count before listing committee", () => {
        mounted.panel.handleChooseCustomData();
        mounted.panel.handleExpectedTotalCouncillorsChange(10);
        mounted.panel.handleTotalCommitteeSeatsChange(16);
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleGroupCountChange(0, 6);
        mounted.panel.handleGroupCountChange(1, 4);
        mounted.panel.handleContinueFromPoliticalGroups();
        mounted.panel.handleContinueFromPartyAllocation();
        mounted.panel.handleStartExperimentalSeatDistribution();

        expect(mounted.container.querySelector("#committee_name_0")).toBeNull();

        const addCommitteeButton = Array.from(mounted.container.querySelectorAll("button")).find(
            (button) => button.textContent?.trim() === COMMITTEE_SEATS_PAGE.add_committee_button_label
        );
        expect(addCommitteeButton).toBeDefined();
        expect((addCommitteeButton as HTMLButtonElement).disabled).toBe(true);

        const addNameInput = mounted.container.querySelector("#committee_seats_add_committee_name");
        const addSeatInput = mounted.container.querySelector("#committee_seats_add_committee_seat_count");
        expect(addNameInput).not.toBeNull();
        expect(addSeatInput).not.toBeNull();

        (addNameInput as HTMLInputElement).value = "Planning Committee";
        addNameInput?.dispatchEvent(new Event("input", {bubbles: true}));
        expect((addCommitteeButton as HTMLButtonElement).disabled).toBe(true);

        (addSeatInput as HTMLInputElement).value = "9";
        addSeatInput?.dispatchEvent(new Event("input", {bubbles: true}));
        expect((addCommitteeButton as HTMLButtonElement).disabled).toBe(false);

        addCommitteeButton?.click();

        expect(getTextInputValue(mounted.container, "committee_name_0")).toBe("Planning Committee");
        expect(getNumberInputValue(mounted.container, "committee_seat_count_0")).toBe(9);
    });

    testSlow("experimental distribution assigns one remainder seat", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleContinueFromPoliticalGroups();
        mounted.panel.handleIndependentAllocationChoiceChange(false);
        mounted.panel.handleContinueFromIndependentAllocation();
        mounted.panel.handleContinueFromPartyAllocation();
        mounted.panel.handleStartExperimentalSeatDistribution();
        mounted.panel.handleContinueFromExperimentalCommittees();

        expect(mounted.panel.state.experimental_substep).toBe(ExperimentalSubstep.Distribution);
        expect(mounted.panel.state.committee_distribution_state).not.toBeNull();

        const distributionState = mounted.panel.state.committee_distribution_state!;
        const batch = getPartyAssignmentBatch(distributionState, 0);
        expect(batch).not.toBeNull();
        expect(batch!.seats_to_choose).toBeGreaterThan(0);

        const confirmButton = getConfirmAssignmentBatchButton(mounted.container);
        expect(confirmButton.disabled).toBe(true);

        selectValidCommitteesForBatch(mounted.panel, batch!);

        const confirmButtonAfterSelection = getConfirmAssignmentBatchButton(mounted.container);
        expect(confirmButtonAfterSelection.disabled).toBe(false);
        confirmButtonAfterSelection.click();

        expect(mounted.panel.state.committee_distribution_state?.assignment_choices[0]).not.toBeNull();
        expect(mounted.panel.state.distribution_pending_committee_selections).toEqual([]);
    });

    test("completed experimental distribution offers final summary with both allocation tables", () => {
        mounted.panel.handleSelectedExampleCouncilChange("bristol");
        mounted.panel.handleChooseExampleCouncil();
        mounted.panel.handleContinueFromCouncilTotals();
        mounted.panel.handleContinueFromPoliticalGroups();
        mounted.panel.handleIndependentAllocationChoiceChange(false);
        mounted.panel.handleContinueFromIndependentAllocation();
        mounted.panel.handleContinueFromPartyAllocation();
        mounted.panel.handleStartExperimentalSeatDistribution();
        mounted.panel.handleContinueFromExperimentalCommittees();

        mounted.panel.setState({
            committee_distribution_state: getTestCouncilCompleteDistributionState(),
        });

        const goToFinalSummaryButton = Array.from(mounted.container.querySelectorAll("button")).find(
            (button) =>
                button.textContent?.trim() ===
                EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.go_to_final_summary_button_label
        );
        expect(goToFinalSummaryButton).toBeDefined();
        goToFinalSummaryButton?.click();

        expect(mounted.panel.state.experimental_substep).toBe(ExperimentalSubstep.FinalSummary);
        expect(mounted.container.textContent).toContain(
            EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_page_heading
        );
        expect(mounted.container.textContent).toContain(
            EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_proportional_section_title
        );
        expect(mounted.container.textContent).toContain(
            EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_committee_allocation_section_title
        );
        expect(mounted.container.textContent).toContain("Conservative");
        expect(mounted.container.textContent).toContain("Adult Social Care Committee");
    });
});
