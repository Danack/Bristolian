import {describe, expect, test, beforeEach, afterEach} from "@jest/globals";
import {h, options, render} from "preact";
import {describeSlow, testSlow} from "../test/jest_slow_tests";
import {
    getFirstUnassignedAssignmentStepIndex,
    getPartyAssignmentBatch,
    getPendingCommitteeSelectionDisabledReason,
} from "./calculate_committee_distribution";
import {
    DistributionAssignmentSteps,
    formatPendingCommitteeSelectionDisabledReasonMessage,
} from "./distribution_assignment_steps";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import {getTestCouncilDistributionStateAtGreenBatchStart} from "./test_council_distribution_test_fixtures";
import {warmTestCouncilDistributionTestFixtures} from "./test_council_distribution_test_fixtures";

describe("formatPendingCommitteeSelectionDisabledReasonMessage", () => {
    test("formats committee-specific modal messages", () => {
        expect(
            formatPendingCommitteeSelectionDisabledReasonMessage(
                "committee_full",
                "Planning Committee",
                "Green",
                []
            )
        ).toBe(
            EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_committee_full_message(
                "Planning Committee"
            )
        );

        expect(
            formatPendingCommitteeSelectionDisabledReasonMessage(
                "group_cap_reached",
                "Audit Committee",
                "Conservative",
                []
            )
        ).toBe("The Conservative group has no more seats to allocate.");

        expect(
            formatPendingCommitteeSelectionDisabledReasonMessage(
                "would_block_current_party_batch",
                "Audit Committee",
                "Conservative",
                []
            )
        ).toBe("The Conservative group has no more seats to allocate.");

        expect(
            formatPendingCommitteeSelectionDisabledReasonMessage(
                "later_party_remainder",
                "Health Committee",
                "Conservative",
                ["Green"]
            )
        ).toContain("Health Committee");
        expect(
            formatPendingCommitteeSelectionDisabledReasonMessage(
                "later_party_remainder",
                "Health Committee",
                "Conservative",
                ["Green"]
            )
        ).toContain("Green");
    });
});

describeSlow("DistributionAssignmentSteps disabled committee modal", () => {
    let container: HTMLElement;
    let previousDebounceRendering: typeof options.debounceRendering;

    beforeEach(() => {
        warmTestCouncilDistributionTestFixtures();
        previousDebounceRendering = options.debounceRendering;
        options.debounceRendering = (callback) => callback();
        container = document.createElement("div");
        document.body.appendChild(container);
    });

    afterEach(() => {
        render(null, container);
        container.remove();
        options.debounceRendering = previousDebounceRendering;
    });

    testSlow("shows a modal when a disabled committee button is clicked", () => {
        const distributionState = getTestCouncilDistributionStateAtGreenBatchStart();
        const stepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
        expect(stepIndex).not.toBeNull();

        const batch = getPartyAssignmentBatch(distributionState, stepIndex!);
        expect(batch).not.toBeNull();

        let disabledCommitteeName: string | null = null;
        let disabledCommitteeIndex: number | null = null;

        for (
            let committeeIndex = 0;
            committeeIndex < distributionState.committees.length;
            committeeIndex += 1
        ) {
            const disabledReason = getPendingCommitteeSelectionDisabledReason(
                distributionState,
                batch!,
                [],
                committeeIndex
            );

            if (disabledReason !== null) {
                disabledCommitteeIndex = committeeIndex;
                disabledCommitteeName = distributionState.committees[committeeIndex].name;
                break;
            }
        }

        expect(disabledCommitteeIndex).not.toBeNull();
        expect(disabledCommitteeName).not.toBeNull();

        render(
            h(DistributionAssignmentSteps, {
                distributionState,
                pendingCommitteeSelections: [],
                onCommitteeChoiceClick: () => {},
                onClearPendingSelections: () => {},
                onConfirmAssignmentBatch: () => {},
                onGoBackToPreviousGroup: () => {},
            }),
            container
        );

        const disabledCommitteeButton = Array.from(container.querySelectorAll("button")).find(
            (button) => button.textContent?.trim().startsWith(disabledCommitteeName!)
        );
        expect(disabledCommitteeButton).toBeDefined();
        expect(disabledCommitteeButton?.getAttribute("aria-disabled")).toBe("true");

        disabledCommitteeButton?.click();

        expect(container.querySelector(".committee_seats_disabled_committee_modal")).not.toBeNull();
        expect(container.textContent).toContain(
            EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_disabled_committee_modal_heading(
                disabledCommitteeName!
            )
        );
    });
});
