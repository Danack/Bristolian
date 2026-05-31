# Committee seat allocation calculator

Public tool at **`/tools/committee_seats`**. It helps calculate how committee seats should be allocated to political groups on a council, using LGA proportional allocation (largest-remainder rounding).

The tool is designed to walk users through the method step by step, not only to output totals. Overall party totals are calculated on the main wizard. **Splitting those totals across named committees** is available as an **advanced** branch from Next steps (not in the six-step trail).

**For developers:** [`docs/committee_seats/agent-handoff-notes.md`](../committee_seats/agent-handoff-notes.md) — architecture, workbook structure, URL parameters, conventions. [`docs/committee_seats/improvement-suggestions.md`](../committee_seats/improvement-suggestions.md) — planned enhancements.

## Process flow (six steps)

The step trail shows up to six steps. Step 4 is hidden when there are no independent councillors.

| Step | Label | What the user does |
|------|-------|-------------------|
| 1 | Choose data source | Pick an example council from the dropdown or enter custom data |
| 2 | Council total and seats | Enter total councillors and political committee seats to allocate |
| 3 | Councillors by group | Enter counts per political group (standard rows including **Vacancy** + optional extra groups) |
| 4 | Independent seats | Choose whether independents receive committee seats *(skipped if none)* |
| 5 | Proportional calculation | Review the workbook: proportional shares, rounding steps, final totals |
| 6 | Next steps | Summary table, optional “send us your data”, LGA guidance, **Start over** |

Past steps in the trail are clickable to go back (allocation is recalculated when needed).

### Choose data source

- Example councils load prefilled data; the user still walks through setup steps (nothing skips straight to allocation).
- Dropdown default: **Choose a Council**; the example button stays disabled until a council is selected.
- Custom data starts with empty group rows.

### Council totals

- Copy names the selected example council (e.g. “You are using data for Sheffield…”).
- Note explains which **political committees** count toward the seat total.

### Vacancies

- **Vacancy** is a fixed standard row (like Labour or Green). Vacant seats count toward the council total on step 2 but are always left out of the proportional calculation; a note appears on step 3 when the vacancy count is greater than zero.

### Independent seats

- Radio choice: include or exclude independents from the proportional calculation.
- Example councils can set a default via `allocate_seats_to_independents` in [`example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts) (Bristol: excluded; Sheffield: included).

### Proportional calculation

Two workbook sections:

- **A** — Calculate each group's proportional share (councillors, percentages, exact entitlement).
- **B** — Round each share down, then allocate remaining seats (roman sub-steps with explanation rows and a **Total seats allocated** column).

**Summary** continues to the Next steps screen.

### Next steps

- **Final allocation by group** table (numeric columns right-aligned).
- **Send us your data** — shown for custom data or when an example was edited; generates JSON and **Copy JSON**.
- Guidance on negotiation and the monitoring officer.
- **Advanced — seat distribution** — optional branch to edit committee names and seat counts, then assign each group's remaining seats across committees by party batch (in-memory only; not encoded in the URL).
- **Back** returns to the calculation; **Start over** resets the wizard and clears the URL.

### Advanced seat distribution

From **Next steps**, **Advanced — seat distribution** opens a three-part flow (hidden from the main step trail; not stored in the URL):

1. **Committees** — edit committee names and seat counts (prefilled from example councils; custom councils add committees one at a time, same pattern as **Add group**). The sum of committee seats must match the total from step 2 before continuing.
2. **Distribution** — floor matrix (minimum seats per group per committee, proportional to committee size), then interactive remainder assignment. One **party batch** at a time: the active party places all of their remaining seats before the next party's turn. The user clicks committee buttons once per seat needed (multi-select with counts), then **Confirm choice**. **Clear selection** resets pending picks; **Go back to {previous group} group** reopens an earlier party's batch. Disabled buttons show why a committee cannot be chosen (committee full, per-committee cap, or would block a later party's remainder seats).
3. **Final summary** — proportional calculation table and committee allocation matrix. **Back to distribution** returns to step 2; **Back to results** returns to Next steps.

**Remainder turn order:** parties place their extra seats in order from **smallest to largest** by councillor count (e.g. Bristol: Conservative, then Liberal Democrat, then Labour, then Green). Smaller groups choose committees first; the largest group goes last. This governs **who picks which committee** for each extra seat — it is separate from the LGA largest-remainder *calculation* of how many seats each group gets overall.

When every group's seats are placed, the assignment section shows **Final distribution** with **Go back to {last group} group choices** to reopen the largest party's batch without undoing earlier parties.

**Back to results** (from committees or distribution) returns to Next steps without changing the main wizard allocation.

## URL state

Progress and form data are stored in query parameters so links can be bookmarked or shared. Implemented in [`url_state.ts`](../../app/public/tsx/committee_seats/url_state.ts).

| Parameter | Purpose |
|-----------|---------|
| `step` | `choose`, `totals`, `groups`, `independents`, `allocation`, `next_steps` (legacy `setup` → groups) |
| `source` | `example` or `custom` |
| `example` | Example council id when `source=example` |
| `seats`, `councillors` | Committee seat total and council councillor total |
| `groups` | Encoded political group name/count pairs (from groups step onward; omitted on totals for example councils — counts restored from example data) |
| `independents` | `included` or `excluded` |

Advanced seat distribution (committees, assignment progress) is **not** encoded in the URL — it is in-memory only for the current session.

## Example councils

Data lives in [`app/public/tsx/committee_seats/example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts).

| Id | Display name | Councillors | Committee seats | Independents in calculation |
|----|--------------|-------------|-----------------|----------------------------|
| `barnet` | Barnet Council | 63 (incl. 1 Independent) | 56 (sum of committees) | Default **excluded** |
| `bristol` | Bristol | 70 (incl. 1 Independent) | 144 (sum of 16 committees) | Default **excluded** |
| `bcp` | Bournemouth, Christchurch and Poole | 76 (12 groups; 2 on standard Independent row) | 111 (sum of 11 committees) | User chooses |
| `lambeth` | Lambeth | 63 (incl. 2 Vacancy) | 35 (sum of 7 committees) | N/A (no independents) |
| `sheffield` | Sheffield | 84 (incl. 4 Independent) | 168 | Default **included** |

`test_council` is in the array for automated tests only (omitted from the public dropdown). Distribution tests use it instead of Bristol to keep Jest fast.

**Vacancy** is a fixed row on the councillors-by-group step. Vacant seats count toward the council total but are always excluded from the proportional committee seat calculation (see `vacancy_allocation.ts`).

Human-readable reference: keep example figures documented when adding councils (optional markdown under `docs/committee_seats/`).

Optional `seat_assignment_source_url` on each example points at the council document that records political balance and committee seat allocation; on the council totals step, a note with a “here” link appears below the political-committees guidance when that example is selected.

## Architecture

- **PHP** mounts an empty widget shell only — no `data-widgety_json`. See [`Pages::committee_seats_page()`](../../src/Bristolian/AppController/Pages.php).
- **TypeScript / Preact** — all copy, examples, validation, calculation, and URL logic under `app/public/tsx/committee_seats/`.
- **Styles** — [`app/public/scss/committee_seats.scss`](../../app/public/scss/committee_seats.scss).

## Important files

All paths under `app/public/tsx/committee_seats/` unless noted.

### Web page shell (PHP)

- [`app/src/app_routes.php`](../../app/src/app_routes.php) — `GET /tools/committee_seats`
- [`src/Bristolian/AppController/Pages.php`](../../src/Bristolian/AppController/Pages.php) — `committee_seats_page()`
- [`src/Bristolian/AppController/Tools.php`](../../src/Bristolian/AppController/Tools.php) — tools menu link

### Panel and wizard

| File | Role |
|------|------|
| [`CommitteeSeatsPanel.tsx`](../../app/public/tsx/CommitteeSeatsPanel.tsx) | Handlers, URL sync, wizard routing, advanced distribution state |
| [`panel_state.ts`](../../app/public/tsx/committee_seats/panel_state.ts) | `WizardStep`, `ExperimentalSubstep`, default panel state |
| [`panel_wizard_chrome.tsx`](../../app/public/tsx/committee_seats/panel_wizard_chrome.tsx) | Header, step trail, step indicator |
| [`page_config.ts`](../../app/public/tsx/committee_seats/page_config.ts) | Page copy and wizard step labels |
| [`wizard_display_step.ts`](../../app/public/tsx/committee_seats/wizard_display_step.ts) | Display steps 1–6, trail visibility |
| [`url_state.ts`](../../app/public/tsx/committee_seats/url_state.ts) | URL encode/decode |

### Wizard steps (`steps/`)

| File | Role |
|------|------|
| [`choose_data_source_step.tsx`](../../app/public/tsx/committee_seats/steps/choose_data_source_step.tsx) | Example vs custom data source |
| [`council_totals_step.tsx`](../../app/public/tsx/committee_seats/steps/council_totals_step.tsx) | Council totals screen |
| [`political_groups_step.tsx`](../../app/public/tsx/committee_seats/steps/political_groups_step.tsx) | Political groups screen |
| [`independent_allocation_step.tsx`](../../app/public/tsx/committee_seats/steps/independent_allocation_step.tsx) | Independent radio step |
| [`next_steps_step.tsx`](../../app/public/tsx/committee_seats/steps/next_steps_step.tsx) | Results, send-data, **Advanced — seat distribution** entry |
| [`experimental_committees_step.tsx`](../../app/public/tsx/committee_seats/steps/experimental_committees_step.tsx) | Advanced flow — committees intro + editor |
| [`final_summary_step.tsx`](../../app/public/tsx/committee_seats/steps/final_summary_step.tsx) | Advanced flow — final summary tables |

### Editors and allocation workbook

| File | Role |
|------|------|
| [`council_totals_editor.tsx`](../../app/public/tsx/committee_seats/council_totals_editor.tsx) | Councillor and committee seat inputs; council paper link |
| [`political_groups_editor.tsx`](../../app/public/tsx/committee_seats/political_groups_editor.tsx) | Groups table and **Continue** |
| [`allocation_workbook.tsx`](../../app/public/tsx/committee_seats/allocation_workbook.tsx) | Proportional calculation workbook (sections A & B) |
| [`committees_editor.tsx`](../../app/public/tsx/committee_seats/committees_editor.tsx) | Advanced flow — committees table |
| [`committee_distribution_workbook.tsx`](../../app/public/tsx/committee_seats/committee_distribution_workbook.tsx) | Floor matrix + assignment matrix |
| [`distribution_assignment_steps.tsx`](../../app/public/tsx/committee_seats/distribution_assignment_steps.tsx) | Party batch UI: summary, committee buttons, Clear / Go back / Confirm |
| [`assignment_section_intro.tsx`](../../app/public/tsx/committee_seats/assignment_section_intro.tsx) | Collapsible assignment instructions |
| [`floor_section_explanation.tsx`](../../app/public/tsx/committee_seats/floor_section_explanation.tsx) | Floor matrix formula explanation |

### Logic and copy

| File | Role |
|------|------|
| [`example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts) | Example council data |
| [`political_groups_form.ts`](../../app/public/tsx/committee_seats/political_groups_form.ts) | Standard group rows and merge logic |
| [`committees_form.ts`](../../app/public/tsx/committee_seats/committees_form.ts) | Committee slot array, merge, validation |
| [`independent_allocation.ts`](../../app/public/tsx/committee_seats/independent_allocation.ts) | Independent step copy and defaults |
| [`vacancy_allocation.ts`](../../app/public/tsx/committee_seats/vacancy_allocation.ts) | Vacancy row helpers and allocation exclusion |
| [`calculate_party_allocation.ts`](../../app/public/tsx/committee_seats/calculate_party_allocation.ts) | Validation, LGA calculation, workbook steps |
| [`calculate_committee_distribution.ts`](../../app/public/tsx/committee_seats/calculate_committee_distribution.ts) | **Barrel** — distribution API (imports unchanged for callers) |
| [`committee_distribution_matrix.ts`](../../app/public/tsx/committee_seats/committee_distribution_matrix.ts) | Matrix helpers |
| [`committee_distribution_caps.ts`](../../app/public/tsx/committee_seats/committee_distribution_caps.ts) | Per-committee caps, later-party feasibility |
| [`committee_distribution_floor.ts`](../../app/public/tsx/committee_seats/committee_distribution_floor.ts) | Floor matrix build |
| [`committee_distribution_assignment.ts`](../../app/public/tsx/committee_seats/committee_distribution_assignment.ts) | Assignment step queue, turn order, batch helpers |
| [`committee_distribution_pending_selection.ts`](../../app/public/tsx/committee_seats/committee_distribution_pending_selection.ts) | Pending picks, disabled reasons, batch confirm |
| [`committee_distribution_navigation.ts`](../../app/public/tsx/committee_seats/committee_distribution_navigation.ts) | Undo, go back, completion |
| [`experimental_seat_distribution.ts`](../../app/public/tsx/committee_seats/experimental_seat_distribution.ts) | Advanced seat distribution copy |
| [`political_group_colours.ts`](../../app/public/tsx/committee_seats/political_group_colours.ts) | Party highlight colours on matrix and buttons |
| [`next_steps.ts`](../../app/public/tsx/committee_seats/next_steps.ts) | Next steps copy |
| [`submit_example_council.ts`](../../app/public/tsx/committee_seats/submit_example_council.ts) | Send-data section and JSON export |
| [`types.ts`](../../app/public/tsx/committee_seats/types.ts) | Shared types |
| [`bootstrap.tsx`](../../app/public/tsx/bootstrap.tsx) | Widget registration (`committee_seats_panel`) |
| [`app/public/scss/committee_seats.scss`](../../app/public/scss/committee_seats.scss) | All styles |

### Tests

| File | Role |
|------|------|
| [`CommitteeSeatsPanel.test.tsx`](../../app/public/tsx/CommitteeSeatsPanel.test.tsx) | Examples, validation, allocation |
| [`CommitteeSeatsPanel.component.test.tsx`](../../app/public/tsx/CommitteeSeatsPanel.component.test.tsx) | Mounted panel wizard transitions and advanced batch confirm |
| [`url_state.test.ts`](../../app/public/tsx/committee_seats/url_state.test.ts) | URL round-trip |
| [`wizard_display_step.test.ts`](../../app/public/tsx/committee_seats/wizard_display_step.test.ts) | Step labels |
| [`calculate_committee_distribution.test.ts`](../../app/public/tsx/committee_seats/calculate_committee_distribution.test.ts) | Floor, turn order, batch assignment, caps, go-back, completion |
| [`test_council_first_available_assignment.test.ts`](../../app/public/tsx/committee_seats/test_council_first_available_assignment.test.ts) | End-to-end `test_council` assignment flow |
| [`test_council_distribution_test_fixtures.ts`](../../app/public/tsx/committee_seats/test_council_distribution_test_fixtures.ts) | Cached distribution state for tests |
| [`test_council_first_available_assignment.ts`](../../app/public/tsx/committee_seats/test_council_first_available_assignment.ts) | Shared test helpers for batch assignment |
| [`independent_allocation.test.ts`](../../app/public/tsx/committee_seats/independent_allocation.test.ts) | Independent defaults |
| [`next_steps.test.ts`](../../app/public/tsx/committee_seats/next_steps.test.ts) | Summary rows |
| [`submit_example_council.test.ts`](../../app/public/tsx/committee_seats/submit_example_council.test.ts) | JSON export |
| [`political_groups_form.test.ts`](../../app/public/tsx/committee_seats/political_groups_form.test.ts) | Group merge |
| [`committees_form.test.ts`](../../app/public/tsx/committee_seats/committees_form.test.ts) | Committee merge and validation |
| [`political_group_colours.test.ts`](../../app/public/tsx/committee_seats/political_group_colours.test.ts) | Highlight styles |
| [`vacancy_allocation.test.ts`](../../app/public/tsx/committee_seats/vacancy_allocation.test.ts) | Vacancy exclusion |
| [`test/BristolianTest/AppController/PagesTest.php`](../../test/BristolianTest/AppController/PagesTest.php) | `test_committee_seats_page` |

Run Jest tests:

```bash
docker exec bristolian-js_builder-1 bash -c "npm run test -- --testPathPattern=committee_seats"
```

Slow distribution fixture tests (using `test_council`) are skipped by default. Include them with:

```bash
docker exec bristolian-js_builder-1 bash -c "npm run test:all -- --testPathPattern=committee_seats"
```

### Reference data

- [`docs/committee_seats/LGA guidance - Political Make Up of the Council Appendix B.pdf`](../committee_seats/LGA%20guidance%20-%20Political%20Make%20Up%20of%20the%20Council%20Appendix%20B.pdf) — LGA guidance
