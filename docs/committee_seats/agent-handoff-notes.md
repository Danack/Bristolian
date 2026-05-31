# Committee seat allocation calculator — agent handoff notes

Public tool at **`/tools/committee_seats`**. This file is for agents continuing work on the feature.

- **User-facing overview:** [`docs/features/committee_seat_calculator.md`](../features/committee_seat_calculator.md)
- **Backlog / ideas:** [`improvement-suggestions.md`](improvement-suggestions.md)

## What the tool does

Six-step wizard (LGA Appendix B largest-remainder):

1. **Choose data source** — example council (dropdown) or custom data.
2. **Council total and seats** — total councillors and political committee seats to allocate.
3. **Councillors by group** — counts per political group (standard rows + optional extra rows).
4. **Independent seats** — whether independents receive committee seats *(skipped when there are no independent councillors)*.
5. **Proportional calculation** — step-by-step allocation workbook (proportional shares, rounding, final totals).
6. **Next steps** — summary table, optional “send us your data”, LGA guidance, **Start over**.

**Main wizard out of scope:** negotiating which councillor sits on which committee.

**Advanced seat distribution:** From Next steps, users can edit committees and assign remainder seats interactively (in-memory only; not in the URL). Extra-seat **step order** uses **global largest remainder** (biggest entitlement gap across all group×committee cells), matching the council-wide rounding principle in LGA Appendix B — not “all of party A’s extras, then all of party B’s”. LGA’s separate note that the largest group may **choose** which committee slots to take is a negotiation privilege; the tool teaches the mathematical remainder method. v1 does not serialise committees or distribution progress in the URL. See **Advanced seat distribution** below. Committees are edited in `committees_editor.tsx`; example `committees` in `example_councils.ts` still prefill totals via `applyExampleCouncilCommitteesIfMissing`.

## Primary goal: make the method understandable

The main purpose of this tool is not only to compute seat totals, but to help other people follow and understand the LGA largest-remainder process. **The party allocation workbook should teach the method step by step** — section headings and description rows should explain *what is happening and why*, in plain language, not opaque jargon.

When choosing or revising copy (workbook section titles, sub-step labels, wizard trail text, intro paragraphs), prefer wording that walks the reader through the calculation. Section **A** sets up exact proportional shares; section **B** shows floor-then-round; description rows spell out each roman sub-step (with progress text such as “We have allocated X/Y seats…” before each extra seat). Rounding row labels use the roman numeral only — no **“Sub-step i:”** prefix.

If a change makes the numbers correct but harder to follow, it is the wrong change unless the user explicitly prioritises something else.

## Architecture choices (read before changing)

- **PHP is a thin shell only.** `Pages::committee_seats_page()` renders `<div class="committee_seats_app"><div class="committee_seats_panel"></div></div>` with **no** `data-widgety_json`. All copy, example data, and wizard config live in TypeScript under `app/public/tsx/committee_seats/`. See `bootstrap.tsx` and **Optional initial data from PHP (widgety)** in `docs/developing/front_end_design_rules.md`.
- **SPA-style layout.** Full-width app shell via `.committee_seats_app` (negative margins against page padding), not by styling `.bristolian_content` with `:has()`.
- **Panel split.** `CommitteeSeatsPanel.tsx` owns handlers, URL sync, wizard routing, and experimental distribution state. Step UI lives under `committee_seats/steps/`, shared editors, `allocation_workbook.tsx`, and the experimental distribution components. Distribution **logic** is split under `committee_distribution_*.ts` with a barrel at `calculate_committee_distribution.ts` (existing imports unchanged).
- **URL state.** Wizard progress and form data are serialised in `url_state.ts` (`step`, `source`, `example`, `seats`, `councillors`, `groups`, `independents`). Legacy `step=setup` round-trips to the political-groups substep. Example councils at the totals step do not put `groups` in the URL; see `applyExampleCouncilPoliticalGroupsIfMissing`.
- **Preact:** do not type `render()` with `h.JSX.Element`; see `docs/developing/front_end_design_rules.md`.
- **Tests:** Jest in `js_builder`, PHPUnit for page shell. Run:
  ```bash
  docker exec bristolian-js_builder-1 bash -c "npm run test -- --testPathPattern=committee_seats"
  docker exec bristolian-js_builder-1 bash -c "npm run test:all -- --testPathPattern=committee_seats"
  docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit --filter test_committee_seats_page"
  ```
  Default `npm run test` skips slow distribution fixture tests. **`npm run test:all`** (`JEST_RUN_SLOW_TESTS=1`) runs all **154** Jest tests (including slow); full suite ~15s in `js_builder` (March 2026). Slow tests use **`test_council`** (4 committees, 36 seats), not Bristol — see **Distribution tests** below. Helper: `app/public/tsx/test/jest_slow_tests.ts` (`describeSlow`, `testSlow`).
  After editing TypeScript, check compilation:
  ```bash
  docker logs bristolian-js_builder-1 --tail 20
  ```
  Jest maps `preact`, `preact/hooks`, and `preact/compat` to their CommonJS builds in `app/jest.config.json` (required for component tests that import hook-using components).

## Files edited / owned by this feature

### PHP & routing

| File | Role |
|------|------|
| `app/src/app_routes.php` | `GET /tools/committee_seats` |
| `src/Bristolian/AppController/Pages.php` | `committee_seats_page()` — mount divs only |
| `src/Bristolian/AppController/Tools.php` | Tools menu link (“Committee seat allocation calculator”) |
| `test/BristolianTest/AppController/PagesTest.php` | `test_committee_seats_page` |

### Frontend (orchestration & chrome)

| File | Role |
|------|------|
| `app/public/tsx/CommitteeSeatsPanel.tsx` | Panel class: handlers, URL sync, wizard navigation |
| `app/public/tsx/committee_seats/panel_state.ts` | State type, enums, validation helpers, default/URL restore |
| `app/public/tsx/committee_seats/panel_wizard_chrome.tsx` | Header (title link to `base_path`), step trail, step indicator, council-setup intro, `ExampleCouncilSeatAssignmentSourceLink` |
| `app/public/tsx/bootstrap.tsx` | Registers `committee_seats_panel` → `CommitteeSeatsPanel` |
| `app/public/scss/committee_seats.scss` | All styles (imported from `site.scss`) |
| `app/public/tsx/CommitteeSeatsPanel.test.tsx` | Jest: examples, validation, allocation |
| `app/public/tsx/CommitteeSeatsPanel.component.test.tsx` | Jest: mounted panel wizard transitions and experimental batch confirm |

### Frontend (wizard steps & editors)

| File | Role |
|------|------|
| `steps/council_setup_step.tsx` | Routes council-setup substeps; shared intro on totals/groups/independents |
| `steps/choose_data_source_step.tsx` | Example first, custom second; dropdown + disabled example button |
| `steps/council_totals_step.tsx` | Council totals screen wrapper |
| `steps/political_groups_step.tsx` | Political groups screen wrapper |
| `steps/independent_allocation_step.tsx` | Independent radio step |
| `steps/next_steps_step.tsx` | Results, send-data, advice, Back / Start over / Advanced entry |
| `steps/experimental_committees_step.tsx` | Experimental committees intro + editor wrapper |
| `committees_form.ts` | Committee slot array, merge, validation vs `total_committee_seats` |
| `committees_editor.tsx` | Committees table + add committee row |
| `committee_distribution_workbook.tsx` | Floor matrix + assignment matrix (with pending selections); wraps assignment UI |
| `distribution_assignment_steps.tsx` | One active party batch: summary, committee buttons, Clear / Go back / Confirm |
| `assignment_section_intro.tsx` | Collapsible assignment instructions |
| `floor_section_explanation.tsx` | Floor matrix formula explanation |
| `calculate_committee_distribution.ts` | **Barrel re-export** — import distribution API from here (unchanged for callers) |
| `experimental_seat_distribution.ts` | Advanced seat distribution branch copy |
| `political_group_colours.ts` | Party highlight colours on changed distribution matrix cells and pending committee buttons |
| `council_totals_editor.tsx` | Councillor + committee seat inputs; political committees note; council paper link when example has `seat_assignment_source_url` |
| `political_groups_editor.tsx` | Groups table; other-group name inputs always editable; count/adjust only after name entered; **Continue** in aside |
| `allocation_workbook.tsx` | Sections A & B, rounding rows, total seats column; amber highlight on cells that change between rounding sub-steps |

### Frontend (`committee_seats/` logic & copy)

| File | Role |
|------|------|
| `page_config.ts` | Title, `base_path`, tagline, wizard labels, workbook headings, council-paper link copy, other-group placeholder, `NO_EXAMPLE_COUNCIL_SELECTED` |
| `wizard_display_step.ts` | Display steps 1–6; hides independent step when no independents |
| `url_state.ts` | Query-parameter encode/decode; `applyExampleCouncilPoliticalGroupsIfMissing` when example URL omits group counts |
| `example_councils.ts` | Barnet, Bristol, BCP, Lambeth, Sheffield, **`test_council`** (dev/test only); `applyExampleCouncilToFormState()`, `applyExampleCouncilPoliticalGroupsIfMissing()` |
| `political_groups_form.ts` | Standard rows, aliases, merge, export for allocation, `additionalPoliticalGroupRowHasEnteredName()` |
| `independent_allocation.ts` | Step copy; defaults from `ExampleCouncil.allocate_seats_to_independents` |
| `vacancy_allocation.ts` | Vacancy row helpers; exclusion from allocation; note on political groups step |
| `calculate_party_allocation.ts` | Validation, LGA calculation, `workbook_steps`, descriptions |
| `next_steps.ts` | Next-steps copy |
| `submit_example_council.ts` | Send-data section, JSON export, clipboard copy |
| `types.ts` | `ExampleCouncil` (`seat_assignment_source_url`, `allocate_seats_to_independents`, …), `PartyAllocationWorkbookStep`, etc. |

### Distribution logic (`committee_distribution_*.ts`)

Split from the former monolithic `calculate_committee_distribution.ts` (March 2026). Dependency order: matrix → caps → floor → assignment → pending / navigation. UI and tests import via the barrel only.

| File | Role |
|------|------|
| `committee_distribution_matrix.ts` | `matrixSeatCount`, `buildEmptyMatrix`, row/column totals, `cloneFloorMatrix` |
| `committee_distribution_caps.ts` | Per-committee cap (`getMaximumSeatsForGroupOnCommittee`), `canGroupReceiveAnotherSeatOnCommittee`, later-party feasibility (`canGroupCompleteRemainderAssignment`, `areLaterPartyRemainderAssignmentsFeasible`) |
| `committee_distribution_floor.ts` | Floor matrix build (`buildGroupFloorCalculations`), worked-example types/formatting for `floor_section_explanation.tsx` |
| `committee_distribution_assignment.ts` | `initializeCommitteeDistribution`, internal `assignment_steps` queue (simulated global LR + **`supplementAssignmentStepsForMissingRemainderSeats`** so each party gets `remainder_seats` steps), turn order, batch helpers, `assignCommitteeDistributionStep`, **`resolveAssignmentStepGroupName`** (reads `assignment_steps[stepIndex].group_name` — the party recorded at init, not recomputed from the matrix) |
| `committee_distribution_pending_selection.ts` | Pending committee picks, disabled-reason kinds, batch confirm (`assignPartyAssignmentBatch`) |
| `committee_distribution_navigation.ts` | Undo, go back to previous/last party, `isCommitteeDistributionComplete`, `getGroupNameForCompletedAssignmentStep` |

### Tests (`committee_seats/*.test.ts`)

| File | Role |
|------|------|
| `url_state.test.ts` | URL round-trip, legacy step names, choose-without-example, example totals without `groups` param |
| `wizard_display_step.test.ts` | Display step labels and visibility |
| `independent_allocation.test.ts` | Example defaults (Bristol, Sheffield), independent detection |
| `example_councils.test.ts` | `applyExampleCouncilPoliticalGroupsIfMissing` |
| `next_steps.test.ts` | Summary row filtering |
| `submit_example_council.test.ts` | JSON export, send-data eligibility |
| `political_groups_form.test.ts` | Group merge, alias matching, other-group row visibility |
| `committees_form.test.ts` | Committee merge, sum validation |
| `calculate_committee_distribution.test.ts` | Floor totals, turn order, batch assignment, per-committee max, disabled reasons, go-back, completion. Uses **`test_council`** for integration scenarios (not Bristol). Slow block: `describeSlow("test council full assignment fixtures")`. |
| `test_council_distribution_test_fixtures.ts` | Lazy cached distribution state for test_council (batch checkpoints, complete state, Labour spread-pick fixture). |
| `test_council_first_available_assignment.ts` | Helpers: assign remainder seats picking first eligible committee at each batch click. |
| `test_council_first_available_assignment.test.ts` | End-to-end test_council assignment flow (including Green batch with 3 picks → Economy and Skills). |
| `political_group_colours.test.ts` | Highlight styles for standard parties |
| `vacancy_allocation.test.ts` | Vacancy row exclusion helpers |

### Docs & reference

| File | Role |
|------|------|
| `docs/features/committee_seat_calculator.md` | Feature index (user-facing overview) |
| `docs/committee_seats/improvement-suggestions.md` | Backlog and improvement ideas |
| `docs/committee_seats/LGA guidance - Political Make Up of the Council Appendix B.pdf` | LGA guidance (Next steps copy) |
| `docs/committee_seats/example-councils.md` | Human-readable figures when adding councils |
| `docs/developing/front_end_design_rules.md` | Widgety optional-data note |

## Wizard UX (current)

### Top-level page header

Title **“Committee seat allocation calculator”** links to `/tools/committee_seats` (`COMMITTEE_SEATS_PAGE.base_path`, no query parameters) via `CommitteeSeatsAppHeader`. Tagline is plain text. Tools menu uses the same title as a normal link.

### Six **display** steps (trail: `Step 1 > … > Step 6`)

Mapped in `wizard_display_step.ts`. Labels in `page_config.ts` → `WIZARD_DISPLAY_STEPS`:

| Display step | Label | When active |
|--------------|-------|-------------|
| 1 | Choose data source | `CouncilSetup` + `ChooseDataSource` |
| 2 | Council total and seats | `CouncilSetup` + `EnterCouncilTotals` |
| 3 | Councillors by group | `CouncilSetup` + `EnterPoliticalGroups` |
| 4 | Independent seats | `CouncilSetup` + `ChooseIndependentAllocation` *(hidden when no independent councillors)* |
| 5 | Proportional calculation | `WizardStep.PartyAllocation` |
| 6 | Next steps | `WizardStep.NextSteps` |

- **Past steps in the trail are clickable** — navigates back; clears `allocation_result` when leaving allocation.
- **Examples do not skip setup.** Choosing an example loads prefilled data but starts at step 2. Totals remain editable.
- Bottom-right indicator: “Step X of N” (`N` is 5 or 6 depending on independents).
- **Semi-transparent text** (`--content_panel_text_color_semi_transparent`) is used **only** on the step indicator.

### Choose data source (step 1)

- **Example council first** (dropdown + button), then **OR**, then **Enter Council Data**.
- Dropdown placeholder: **Choose a Council** (`NO_EXAMPLE_COUNCIL_SELECTED`).
- Example button disabled until a council is selected; label becomes `Use data for '{display_name}'`.
- **No council paper link** on this step (link appears on council totals only; see below).

### Council setup substeps

| Substep | Screen |
|---------|--------|
| `choose_data_source` | `ChooseDataSourceStep` |
| `enter_council_totals` | `CouncilTotalsStep` + `CouncilTotalsEditor` |
| `enter_political_groups` | `PoliticalGroupsStep` + `PoliticalGroupsEditor` |
| `choose_independent_allocation` | `IndependentAllocationStep` |

Shared intro on totals (`getCouncilSetupIntroMessage()`): custom vs `formatCouncilSetupExampleIntro(display_name)` for examples. Councillors-by-group step uses `getPoliticalGroupsStepIntroMessage()` — same example wording plus “, or press continue.”

**Council totals:** note on which **political committees** count toward the seat total (`council_setup_political_committees_note`). No section title above the two inputs (removed). When the example has `seat_assignment_source_url`, a note appears **below** that political-committees paragraph: “These numbers were taken from what we believe to be the correct Council Paper, which is **here**. (opens in a new tab)” — rendered by `ExampleCouncilSeatAssignmentSourceLink` in `council_totals_editor.tsx`.

**Political groups:** standard rows always editable. **Other groups:** name input always shown (example and custom); placeholder `additional_political_group_name_placeholder`; councillor count and +/- buttons only after a non-empty name (`additionalPoliticalGroupRowHasEnteredName()`). Extra rows grow as names or counts are used (up to 20). Note under table: “You can add up to 20 other groups.” One **Continue** in the aside (duplicate bottom Continue removed). Error/warning shown on this step only.

**Example group counts and URL sync:** choosing an example loads merged form groups in panel state, but the URL at the **totals** step only serialises `seats` and `councillors` — not `groups`. After refresh or remount, group counts would be empty unless restored. `applyExampleCouncilPoliticalGroupsIfMissing()` in `example_councils.ts` refills from the built-in example when `source=example`, a council id is set, and every group count is zero. Called from `url_state.ts` (totals and later steps) and `handleContinueFromCouncilTotals()` in `CommitteeSeatsPanel.tsx`.

**Independent allocation:** radios + **Continue** (disabled until chosen). `INDEPENDENT_ALLOCATION_STEP_COPY.consequence_note` when choosing No. Example defaults from `allocate_seats_to_independents` on `ExampleCouncil` (not hardcoded per council id in UI code). URL restore uses the same defaults when `independents` is omitted (Bristol → excluded; Sheffield → included). BCP has no default (user chooses). Independent step is driven by the standard **Independent** row only — local parties on additional rows (e.g. BCP’s “Christchurch Independents”) do not trigger it.

### Party allocation workbook (step 5)

Rendered by `PartyAllocationStepView` in `allocation_workbook.tsx`. Section headings use grey band (`.committee_seats_allocation_workbook_section_heading`).

| Section | Label | Contents |
|---------|-------|----------|
| **A** | Calculate each group's proportional share | Councillors; %; exact entitlement |
| **B** | Round each share down, then allocate the remaining seats | Roman sub-steps **i**, **ii**, …; description rows; per-party seats; **Total seats allocated** column |
| — | Final allocation | Larger font; includes total column on final row |

Rounding description rows (from `calculate_party_allocation.ts`) explain allocated progress (e.g. “We have allocated 142/144 seats. To allocate the next…”) before each extra seat.

From the second rounding sub-step onward, seat counts that **change** from the previous sub-step are highlighted on the whole cell (`.committee_seats_allocation_workbook_value_cell_changed` — amber background, inset border, heavier type). Compare uses `previousSeatsByGroupName` / `previousTotalSeatsAllocated` on `PartyAllocationWorkbookRow`.

Continue labelled **“Summary”** (`allocation_summary_button_label`).

**Not shown:** per-committee “actual allocation” / “variance” rows.

### Next steps (step 6)

- **The results** heading; **Final allocation by group** (Councillors / Committee seats columns right-aligned).
- **Send us your data** when custom or example data was modified — JSON + **Copy JSON** with feedback.
- LGA-style guidance: negotiation, monitoring officer (`next_steps.ts`). Nominations block removed.
- **Back** (to calculation), **Start over** (`handleStartOver` → `getDefaultPanelState()`, clears URL).
- **Advanced — seat distribution** — enters `WizardStep.SeatDistributionExperimental` (not in the six-step trail).

## Advanced seat distribution

Hidden branch from Next steps (`WizardStep.SeatDistributionExperimental`). Two substeps (`ExperimentalSubstep` in `panel_state.ts`):

1. **Committees** — `experimental_committees_step.tsx` + `committees_editor.tsx`. Prefilled from example councils; sum of committee seats must match step-2 total.
2. **Distribution** — `committee_distribution_workbook.tsx` + `distribution_assignment_steps.tsx`.

### Floor matrix

For each group, split that group’s **final committee seats** (from the main calculation) across committees in proportion to committee size; **round down** per cell. Shown in the first table with formula explanation (`floor_section_explanation.tsx`).

### Remainder assignment — turn order

Parties place **all** of their remaining extra seats in **one batch** before the next party’s turn. Turn order is fixed at init as `established_assignment_turn_order`: parties with remainder seats, sorted **smallest to largest** by councillor count. Example Bristol: Conservative → Liberal Democrat → Labour → Green. Example **`test_council`** (distribution tests): Conservative → Labour → Green. Smaller groups choose committees first; the largest group goes last.

There is **no separate “Whose turn” panel**. The assignment matrix highlights the current party’s row with “(current turn)” and a **Seats left to assign** column.

### Remainder assignment — UI (one batch at a time)

Only the **active party’s** batch is shown (`distribution_assignment_steps.tsx`):

- Summary: “The {Group} Group currently has N committee seats allocated, and needs to choose **M** more.” Updates live as committee buttons are clicked (pending selections included). When all picks for the batch are made, shows “The {Group} group have chosen their committee seats.”
- **Committee buttons** — multi-select with counts; click again to remove one pick from that committee. Disabled when the party cannot add another seat on that committee (see caps below). Selected buttons and matrix cells use party colours while pending.
- **Clear selection** (footer left) — clears pending picks for the current batch.
- **Go back to {previous group} group** (footer left, hidden for the first party in turn order) — clears pending picks and reverts that previous party’s confirmed batch (and all later assignments) so they can choose again.
- **Confirm choice** (footer right) — commits the batch via `assignPartyAssignmentBatch()`.
- **Disabled committee buttons** — when a button cannot be clicked, `distribution_assignment_steps.tsx` lists reason kinds below the buttons (`getPendingCommitteeSelectionDisabledReasonKinds`; copy in `experimental_seat_distribution.ts`): committee full, group at per-committee cap, would block the current party’s remaining picks, or would block a later party’s remainder seats.

Panel state: `distribution_pending_committee_selections: number[]` (committee indices, one entry per seat in the current batch). Cleared on confirm, go-back, or leaving the experimental flow.

Copy lives in `experimental_seat_distribution.ts`. Intro paragraph is collapsible (`assignment_section_intro.tsx`).

### Completion state

When `isCommitteeDistributionComplete()` is true (`committee_distribution_workbook.tsx`):

- Assignment section title becomes **“Final distribution”**; the matrix hides the “Seats left to assign” column and current-turn row label.
- `AssignmentSectionIntro` and `DistributionAssignmentSteps` are hidden (wrapped in `<span>`, not a Preact fragment).
- A completion message is shown with **Go back to {last group} group choices** (`goBackToLastAssignmentGroupWhenComplete`) — reopens the largest party’s batch (last in turn order, e.g. Green on Bristol) without undoing earlier parties.

### Remainder assignment — logic (`committee_distribution_*.ts`, barrel `calculate_committee_distribution.ts`)

**Internal step queue:** `assignment_steps` is built by simulating global largest-remainder placement (fractional cell gaps). The simulation can stop before every party has placed all remainder seats (per-committee **ceil caps** can leave integer capacity with no positive fractional gap). **`supplementAssignmentStepsForMissingRemainderSeats`** appends steps so each party in turn order gets exactly `remainder_seats` from the floor calculation (integer count). User choices can diverge from the simulation; confirmed picks replay using **`assignment_steps[stepIndex].group_name`**, not a live global-LR recompute per step.

**Party batch:** `getPartyAssignmentBatch()` — all unassigned steps for the current party at the front of the queue (`getUnassignedStepIndicesForGroup`). `seats_to_choose` is the integer matrix remainder for that group (not capped by step count). Steps are appended on confirm via `ensureAssignmentStepsForPartyBatch` when the queue is short. Batch size should match integer remainder seats after supplement (see `test_council_first_available_assignment.test.ts`).

**Per-committee cap:** a group may not exceed **ceil(proportional share)** seats on any one committee (`getMaximumSeatsForGroupOnCommittee`). Also blocked when the committee column is full. Enforced in `canGroupReceiveAnotherSeatOnCommittee`, committee button disabled state, and `isPendingPartyAssignmentBatchReadyToConfirm()` (simulates sequential assign before confirm).

**Later-party feasibility (Bristol / largest group last):** the largest party (e.g. Green with 71 seats on 16×9 committees) may only add **one** extra seat per committee (floor 4, cap 5). Earlier parties must not use committee space in a way that leaves them unable to place all remainders within those caps. Implemented in `committee_distribution_caps.ts` (`canGroupCompleteRemainderAssignment`, `areLaterPartyRemainderAssignmentsFeasible`) and enforced in `committee_distribution_pending_selection.ts` via `canAddPendingCommitteeSelectionOnCommittee()` and confirm simulation.

**Matrix display:** `buildMatrixWithPendingPartySelections()` (assignment module) — assignment table includes pending picks before confirm so counts, “Seats left”, and highlights stay in sync. Changed cells use party colours from `political_group_colours.ts`.

**Key exports (barrel):** `initializeCommitteeDistribution`, `getPartyAssignmentBatch`, `assignPartyAssignmentBatch`, `goBackToPreviousAssignmentGroup`, `goBackToLastAssignmentGroupWhenComplete`, `getPreviousGroupInAssignmentTurnOrder`, `getAssignmentStepDataSummaryParts`, `canAddPendingCommitteeSelectionOnCommittee`, `getPendingCommitteeSelectionDisabledReasonKinds`, `canGroupCompleteRemainderAssignment`, `isCommitteeDistributionComplete`.

**Removed / do not restore:** `distribution_assignment_turn_panel.tsx` (deleted). `shouldShowLaterPartyRemainderAssignmentBlockNote`, `applyCommitteeDistributionAssignment`, and turn-summary helpers (`buildAssignmentTurnSummary`, `formatAssignmentTurnReason`) were removed in the March 2026 cleanup.

**Preact:** this project imports `h` only — do not use `<>` fragments; use `<span>` (or similar) for inline grouping.

**Back to results** returns to Next steps without changing the main wizard allocation.

## Example council data

In [`example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts). Optional fields on `ExampleCouncil`:

- `committees` — prefills total committee seats via sum of `seat_count` (preferred when committee names are known).
- `total_committee_seats` — when no `committees` list (e.g. Sheffield).
- `allocate_seats_to_independents` — pre-selects independent step (boolean); omit → user chooses on that step.
- `seat_assignment_source_url` — council PDF/report for political balance and seat allocation; link on **council totals** step only (see above).

| Id | Councillors | Committee seats | Independent default | Council paper URL |
|----|-------------|-----------------|-------------------|-------------------|
| `barnet` | 63 (incl. 1 Independent) | 56 (7 committees) | **Excluded** | — |
| `bristol` | 70 (incl. 1 Independent) | 144 (16 × 9) | **Excluded** | democracy.bristol.gov.uk |
| `bcp` | 76 (12 groups; many on additional rows) | 111 (11 committees) | User chooses | democracy.bcpcouncil.gov.uk |
| `lambeth` | 63 (incl. 2 Vacancy) | 35 (7 committees) | N/A | moderngov.lambeth.gov.uk |
| `sheffield` | 84 (incl. 4 Independent; Sheffield Community Councillors 2) | 168 | **Included** | democracy.sheffield.gov.uk |
| `test_council` | 29 (Labour 8, Conservative 6, Green 12, Libdem 3) | 36 (4 × 9) | N/A | — *(in `EXAMPLE_COUNCILS` for tests; omitted from public dropdown)* |

**`test_council`** is a small fixture council (same shape as production examples). Distribution tests and cached fixtures use it instead of Bristol so Jest stays fast (~15s for `test:all`). UI wizard tests still use Bristol in `CommitteeSeatsPanel.component.test.tsx`.

To add a council: extend `EXAMPLE_COUNCILS`, document figures in [`example-councils.md`](example-councils.md), run `npm run test -- --testPathPattern=committee_seats`, update this table and [`committee_seat_calculator.md`](../features/committee_seat_calculator.md).

## URL query parameters

| Param | Values | Notes |
|-------|--------|-------|
| `step` | `choose`, `totals`, `groups`, `independents`, `allocation`, `next_steps` | Legacy `setup` → groups |
| `source` | `example`, `custom` | |
| `example` | council id | Omitted on choose step when none selected |
| `seats`, `councillors` | integers | Present from step 2 onward |
| `groups` | encoded `name\|count` pairs | From **groups** step onward only; **omitted on `totals`** — example group counts restored via `applyExampleCouncilPoliticalGroupsIfMissing` |
| `independents` | `included`, `excluded` | Omitted → example default from `allocate_seats_to_independents` |

## Calculation

- **Validation:** `validateCouncilSetup()` — group names, counts, councillor total vs expected, committee seats must be a positive integer.
- **Allocation:** floor each group's exact share, then assign remaining seats to largest fractional parts (LGA Appendix B).
- **Independents:** when excluded, removed via `politicalGroupsForSeatAllocation()` before `calculatePartyAllocation()`.
- **Vacancies:** always excluded in `politicalGroupsForSeatAllocation()`; standard row in `STANDARD_POLITICAL_GROUP_NAMES`; UI note in `political_groups_editor.tsx` via `vacancy_allocation.ts`.

## UI copy and styling (user preferences)

- Do **not** push page copy through PHP `data-widgety_json` unless server-side data is required.
- Body text, tagline, `.committee_seats_note`: **black** (`#000`); semi-transparent colour only on step indicator.
- Choose-data-source: black descriptions; buttons natural width, centred.
- Council totals step centred (`committee_seats_council_totals_screen`).
- Workbook explanation rows: body text colour; grey background distinguishes them from data rows.
- Workbook rounding cells that change between sub-steps: amber highlight (`.committee_seats_allocation_workbook_value_cell_changed`).

## Internal enums (`panel_state.ts`)

- `WizardStep`: `CouncilSetup = 1`, `PartyAllocation = 2`, `NextSteps = 3`, `SeatDistributionExperimental = 4`.
- `ExperimentalSubstep`: `Committees` | `Distribution`.
- `CouncilSetupSubstep`: `ChooseDataSource` | `EnterCouncilTotals` | `EnterPoliticalGroups` | `ChooseIndependentAllocation`.
- `WizardDisplayStep` 1–6 in `wizard_display_step.ts` (separate from wizard step enum; experimental branch is not a display step).

## Likely next work

See [`improvement-suggestions.md`](improvement-suggestions.md). Highlights:

- Behat smoke test for `/tools/committee_seats`.
- Print/share summary on Next steps.
- Link to LGA PDF on calculation step.
- Document example-data workflow for “Send us your data” submissions.

## Distribution tests (March 2026)

**Fixture council:** `test_council` in `example_councils.ts` — 4 parties, 4 committees, 36 seats. Turn order for remainder assignment: Conservative → Labour → Green (Lib Dem has no remainder seats after floor).

**Why not Bristol in distribution tests:** Bristol (16 committees, 144 seats) made slow tests and fixture warm-up expensive (~20s `test:all` before migration). Bristol remains appropriate for UI/component wizard tests.

**Key test files:**

| File | Purpose |
|------|---------|
| `test_council_distribution_test_fixtures.ts` | `warmTestCouncilDistributionTestFixtures()`, cached batch checkpoints, complete state, Labour spread-pick disabled-reason fixture |
| `test_council_first_available_assignment.test.ts` | Full “Assign remaining seats” flow: first eligible committee at each pick; asserts Green reaches 15/15 including Economy and Skills |
| `calculate_committee_distribution.test.ts` | Unit/integration tests; slow block gated by `describeSlow` |

**Green batch bug (fixed March 2026):** Simulation produced only 2 Green steps while Green needed 3 integer remainder seats → batch offered 2 picks then UI blocked the last seat. Fix: supplement missing steps from `remainder_seats`; replay confirmed picks on recorded step party. Do not gate batch sizing on fractional largest-remainder alone — UI legality already uses integer `getSeatCapacityForGroupOnCommittee` in `committee_distribution_pending_selection.ts`.
