import {calculatePartyAllocation} from "./calculate_party_allocation";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./political_groups_form";
import {politicalGroupsForSeatAllocation} from "./independent_allocation";
import {getExampleCouncilById} from "./example_councils";
import {
    assignPartyAssignmentBatch,
    canAddPendingCommitteeSelectionOnCommittee,
    getFirstUnassignedAssignmentStepIndex,
    getPartyAssignmentBatch,
    getPendingCommitteeSelectionDisabledReasonKinds,
    initializeCommitteeDistribution,
    isCommitteeDistributionComplete,
    type PartyAssignmentBatch,
} from "./calculate_committee_distribution";
import type {CommitteeDistributionState} from "./types";

export interface TestCouncilDistributionBatchStartCheckpoint {
    party_name: string;
    distribution_state: CommitteeDistributionState;
}

export interface TestCouncilLabourBatchWithSpreadPicksFixture {
    distribution_state: CommitteeDistributionState;
    labour_batch: PartyAssignmentBatch;
    labour_step_index: number;
}

/** Labour pending picks that surface `later_party_remainder` after Conservative spread batch. */
export let TEST_COUNCIL_LABOUR_PENDING_WITH_LATER_PARTY_BLOCK: number[] = [];

function cloneDistributionState(
    distributionState: CommitteeDistributionState
): CommitteeDistributionState {
    return JSON.parse(JSON.stringify(distributionState)) as CommitteeDistributionState;
}

export function initializeTestCouncilDistributionState(): CommitteeDistributionState {
    const testCouncilExample = getExampleCouncilById("test_council");
    if (testCouncilExample === undefined) {
        throw new Error("test_council example council not found");
    }

    const politicalGroups = mergePoliticalGroupsIntoCouncilSetupForm(
        testCouncilExample.political_groups
    );
    const totalCommitteeSeats = testCouncilExample.committees.reduce(
        (total, committee) => total + committee.seat_count,
        0
    );
    const allocation = calculatePartyAllocation({
        political_groups: politicalGroupsForSeatAllocation(politicalGroups, false),
        total_committee_seats: totalCommitteeSeats,
    });

    return initializeCommitteeDistribution(testCouncilExample.committees, allocation);
}

export function buildValidPendingSelectionsForBatch(
    distributionState: CommitteeDistributionState,
    batch: PartyAssignmentBatch
): number[] {
    const pendingCommitteeSelections: number[] = [];

    while (pendingCommitteeSelections.length < batch.seats_to_choose) {
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
                    pendingCommitteeSelections,
                    committeeIndex
                )
            ) {
                continue;
            }

            pendingCommitteeSelections.push(committeeIndex);
            addedSelection = true;
            break;
        }

        if (!addedSelection) {
            throw new Error(
                `Could not build valid pending selections for ${batch.group_name} batch`
            );
        }
    }

    return pendingCommitteeSelections;
}

function discoverLabourPendingWithLaterPartyBlock(
    distributionState: CommitteeDistributionState,
    labourBatch: PartyAssignmentBatch
): number[] {
    function search(pendingCommitteeSelections: number[]): number[] | null {
        if (
            getPendingCommitteeSelectionDisabledReasonKinds(
                distributionState,
                labourBatch,
                pendingCommitteeSelections
            ).includes("later_party_remainder")
        ) {
            return pendingCommitteeSelections;
        }

        if (pendingCommitteeSelections.length >= labourBatch.seats_to_choose) {
            return null;
        }

        for (
            let committeeIndex = 0;
            committeeIndex < distributionState.committees.length;
            committeeIndex += 1
        ) {
            const found = search([...pendingCommitteeSelections, committeeIndex]);
            if (found !== null) {
                return found;
            }
        }

        return null;
    }

    const discovered = search([]);
    if (discovered === null) {
        throw new Error(
            "Could not discover Labour pending selections with later_party_remainder on test_council"
        );
    }

    return discovered;
}

let cachedBatchStartCheckpoints: TestCouncilDistributionBatchStartCheckpoint[] | null = null;
let cachedCompleteDistributionState: CommitteeDistributionState | null = null;
let cachedLabourSpreadFixture: TestCouncilLabourBatchWithSpreadPicksFixture | null = null;

function ensureTestCouncilValidAssignmentFixturesBuilt(): void {
    if (cachedCompleteDistributionState !== null && cachedBatchStartCheckpoints !== null) {
        return;
    }

    const batchStartCheckpoints: TestCouncilDistributionBatchStartCheckpoint[] = [];
    let state = initializeTestCouncilDistributionState();

    while (!isCommitteeDistributionComplete(state)) {
        const stepIndex = getFirstUnassignedAssignmentStepIndex(state);
        if (stepIndex === null) {
            break;
        }

        const batch = getPartyAssignmentBatch(state, stepIndex);
        if (batch === null) {
            throw new Error(
                "Expected party assignment batch while completing test_council distribution"
            );
        }

        batchStartCheckpoints.push({
            party_name: batch.group_name,
            distribution_state: cloneDistributionState(state),
        });

        const pendingSelections = buildValidPendingSelectionsForBatch(state, batch);
        const updatedState = assignPartyAssignmentBatch(state, stepIndex, pendingSelections);

        if (updatedState === state) {
            throw new Error(
                `test_council ${batch.group_name} batch did not advance during fixture completion`
            );
        }

        state = updatedState;
    }

    if (!isCommitteeDistributionComplete(state)) {
        throw new Error("test_council distribution fixture did not reach completion");
    }

    cachedBatchStartCheckpoints = batchStartCheckpoints;
    cachedCompleteDistributionState = state;
}

function ensureTestCouncilLabourSpreadFixtureBuilt(): void {
    if (cachedLabourSpreadFixture !== null) {
        return;
    }

    let state = initializeTestCouncilDistributionState();

    const firstStepIndex = getFirstUnassignedAssignmentStepIndex(state);
    if (firstStepIndex === null) {
        throw new Error(
            "Expected unassigned step while advancing test_council with spread picks"
        );
    }

    const firstBatch = getPartyAssignmentBatch(state, firstStepIndex);
    if (firstBatch === null) {
        throw new Error(
            "Expected party assignment batch while advancing test_council with spread picks"
        );
    }

    state = assignPartyAssignmentBatch(
        state,
        firstStepIndex,
        Array.from(
            {length: firstBatch.seats_to_choose},
            (_, pickIndex) => pickIndex % state.committees.length
        )
    );

    const labourStepIndex = getFirstUnassignedAssignmentStepIndex(state);
    if (labourStepIndex === null) {
        throw new Error("Expected Labour batch after Conservative spread picks");
    }

    const labourBatch = getPartyAssignmentBatch(state, labourStepIndex);
    if (labourBatch === null || labourBatch.group_name !== "Labour") {
        throw new Error("Expected Labour party assignment batch after Conservative spread picks");
    }

    TEST_COUNCIL_LABOUR_PENDING_WITH_LATER_PARTY_BLOCK = discoverLabourPendingWithLaterPartyBlock(
        state,
        labourBatch
    );

    cachedLabourSpreadFixture = {
        distribution_state: state,
        labour_batch: labourBatch,
        labour_step_index: labourStepIndex,
    };
}

/**
 * Snapshot before each party batch during valid test_council completion (Conservative,
 * Labour, Green). Built once per test run together with
 * {@link getTestCouncilCompleteDistributionState}.
 */
export function getTestCouncilDistributionBatchStartCheckpoints(): TestCouncilDistributionBatchStartCheckpoint[] {
    ensureTestCouncilValidAssignmentFixturesBuilt();

    return cachedBatchStartCheckpoints!.map((checkpoint) => ({
        party_name: checkpoint.party_name,
        distribution_state: cloneDistributionState(checkpoint.distribution_state),
    }));
}

/**
 * test_council remainder assignment with every batch confirmed. Built once per test run;
 * each call returns a clone so tests can mutate without affecting the cache.
 */
export function getTestCouncilCompleteDistributionState(): CommitteeDistributionState {
    ensureTestCouncilValidAssignmentFixturesBuilt();

    return cloneDistributionState(cachedCompleteDistributionState!);
}

/**
 * Distribution state immediately before Green's batch, after Conservative and Labour valid batches.
 */
export function getTestCouncilDistributionStateAtGreenBatchStart(): CommitteeDistributionState {
    ensureTestCouncilValidAssignmentFixturesBuilt();

    const greenCheckpoint = cachedBatchStartCheckpoints!.find(
        (checkpoint) => checkpoint.party_name === "Green"
    );
    if (greenCheckpoint === undefined) {
        throw new Error("test_council fixture did not include a Green batch start checkpoint");
    }

    return cloneDistributionState(greenCheckpoint.distribution_state);
}

/**
 * test_council at Labour's batch after Conservative spread (modulo) picks.
 * Used for disabled-reason tests that need a specific Labour pending shape.
 */
export function getTestCouncilAtLabourBatchWithSpreadPicks(): TestCouncilLabourBatchWithSpreadPicksFixture {
    ensureTestCouncilLabourSpreadFixtureBuilt();

    return {
        distribution_state: cloneDistributionState(cachedLabourSpreadFixture!.distribution_state),
        labour_batch: {...cachedLabourSpreadFixture!.labour_batch},
        labour_step_index: cachedLabourSpreadFixture!.labour_step_index,
    };
}

/** Warm all test_council assignment fixtures in one call (optional; otherwise lazy on first access). */
export function warmTestCouncilDistributionTestFixtures(): void {
    ensureTestCouncilValidAssignmentFixturesBuilt();
    ensureTestCouncilLabourSpreadFixtureBuilt();
}
