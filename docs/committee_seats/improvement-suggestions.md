# Committee seat allocation calculator — improvement suggestions

Ideas for future work on the tool at `/tools/committee_seats`. The feature already does the hard part well: it walks people through the LGA largest-remainder method rather than only outputting numbers.

See also [`agent-handoff-notes.md`](agent-handoff-notes.md) for current architecture and conventions. User-facing behaviour is summarised in [`committee_seat_calculator.md`](../features/committee_seat_calculator.md).

## High impact for users

### 1. Link to LGA guidance on the calculation step

Next steps already paraphrases Appendix B. A direct link to the PDF (or an official LGA page) on the proportional calculation step would help people verify the method and use the results in negotiations.

Reference: [`LGA guidance - Political Make Up of the Council Appendix B.pdf`](LGA%20guidance%20-%20Political%20Make%20Up%20of%20the%20Council%20Appendix%20B.pdf)

### 2. Printable / shareable summary

URL state is good for bookmarking, but councillors often want a PDF or printout. A “Print results” button on Next steps (or a simple print stylesheet hiding the wizard chrome) would make the summary table and final allocation easy to take to a meeting.

### 3. Clarify which committees to count (worked example)

The political committees note on the council totals step is in place. A short worked example (e.g. “if your council has 7 political committees with 10, 10, 8… seats, enter 56”) might still help more than abstract wording, especially for custom data.

## Code cleanup and maintainability

### Medium priority

#### `calculate_committee_distribution.test.ts` — fixed (March 2026)

Previously two tests **hung indefinitely** (invalid `pickIndex => pickIndex` batch picks that never advanced state). All 21 tests now pass in ~14s inside `js_builder` (fixtures warmed once in `beforeAll`).

| Test | Notes |
|------|--------|
| `green batch offers three picks when three remainder seats are still needed` | `test_council_first_available_assignment.test.ts` — Green remainder seats must match batch size (fixed). |

| `partial green pending selections still report seats still needed` | `getTestCouncilDistributionStateAtGreenBatchStart()`. |
| `goBackToLastAssignmentGroupWhenComplete reopens the last party batch on test council` | `getTestCouncilCompleteDistributionState()`. |
| `disabled reason kinds include later party block…` / `committee full…` | `getTestCouncilAtLabourBatchWithSpreadPicks()` / `getTestCouncilDistributionStateAtGreenBatchStart()`; assertions are fast once fixtures are warm. |

Fixtures: `test_council_distribution_test_fixtures.ts` — `warmTestCouncilDistributionTestFixtures()`, `getTestCouncilDistributionBatchStartCheckpoints()`, `getTestCouncilCompleteDistributionState()`, `getTestCouncilDistributionStateAtGreenBatchStart()`, `getTestCouncilAtLabourBatchWithSpreadPicks()`.

**Run (fast, default — skips slow test_council fixture tests):** `docker exec bristolian-js_builder-1 bash -c "npm run test -- --testPathPattern=calculate_committee_distribution.test.ts"`

**Run including slow tests:** `docker exec bristolian-js_builder-1 bash -c "npm run test:all -- --testPathPattern=calculate_committee_distribution.test.ts"`

Slow tests live in `describeSlow("test council full assignment fixtures")` and run when `JEST_RUN_SLOW_TESTS=1` (`npm run test:all`). Helper: `public/tsx/test/jest_slow_tests.ts`.

**Do not** use invalid modulo/`pickIndex` shortcuts for batch confirmation unless verified with `isPendingPartyAssignmentBatchReadyToConfirm` — they leave state unchanged and cause infinite loops.

Shared test helpers (`assignCurrentStepWithFirstAvailableCommittee`, `assignCurrentPartyBatchWithFirstAvailableCommittees`, `expectPartyBatchSizingMatchesMatrixRemainder`, etc.) live in `test_council_first_available_assignment.ts`.

### Low priority

- **Disabled-reason test_council tests** — still ~600–900 ms each due to `getPendingCommitteeSelectionDisabledReasonKinds` over Labour’s batch; fixture removes setup cost only.

### Do not change without a product decision

- **Internal `assignment_steps` vs user batches** — simulation uses global largest-remainder; UI batches by party in turn order. Intentional; documented in handoff, do not “simplify” without re-checking test_council scenarios.

## Technical / maintainability

### 5. Behat smoke test

Still on the handoff “not done” list. Even one scenario — load page, pick Bristol, reach allocation — would protect the PHP shell and webpack bundle wiring.

## Documentation

### 6. Example data workflow

Document how to add a council from submitted JSON (edit `example_councils.ts`, run tests, which fields are required). That closes the loop on “Send us your data”.

### 7. Sync `committee_seat_calculator.md` file table — done (May 2026)

Feature doc “Important files” section now lists the panel split (`steps/*`, distribution modules, test fixtures) — aligned with `agent-handoff-notes.md`.

## Advanced seat distribution (shipped)

Available from Next steps as **Advanced — seat distribution**. v1 is in-memory only (no URL encoding). Auto-solving remainders without user input remains out of scope.

| Area | Behaviour |
|------|-----------|
| **Modules** | `committees_form.ts`, `calculate_committee_distribution.ts` (barrel), `committee_distribution_matrix.ts`, `committee_distribution_caps.ts`, `committee_distribution_floor.ts`, `committee_distribution_assignment.ts`, `committee_distribution_pending_selection.ts`, `committee_distribution_navigation.ts`, `committee_distribution_workbook.tsx`, `distribution_assignment_steps.tsx`, `experimental_seat_distribution.ts` |
| **Batch assignment** | Each party places all remainder seats in one confirm step; turn order smallest-to-largest by councillor count |
| **Constraints** | Per-committee cap (ceil of proportional share); later-party feasibility; disabled-button explanation notes |
| **Navigation** | Go back to previous party during assignment; go back to last party after completion; Back to committees / Back to results |

## Probably out of scope (but often asked)

## Suggested priorities

If picking three items first:

1. **Print/share summary** — practical for meetings and negotiations.
2. **Behat smoke test** — protects the integrated page without a large testing investment.
3. **Link to LGA guidance** on the calculation step — helps users trust and cite the method.

Project-wide (not committee-seats-specific): Preact automatic JSX runtime — see [`docs/todo.md`](../todo.md).
