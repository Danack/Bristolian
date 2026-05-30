# Committee seat allocation calculator

Public tool at **`/tools/committee_seats`**. It helps calculate how committee seats should be allocated to political groups on a council, using LGA proportional allocation (largest-remainder rounding).

The tool is designed to walk users through the method step by step, not only to output totals. Overall party totals are calculated on the main wizard. **Splitting those totals across named committees** is available as an **experimental** branch from Next steps (not in the six-step trail).

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
- **Experimental — seat distribution** — optional branch to edit committee names and seat counts, then work through a proportional floor matrix and assign each group's remaining seats to committees one at a time (in-memory only in v1; not encoded in the URL).
- **Back** returns to the calculation; **Start over** resets the wizard and clears the URL.

### Experimental seat distribution

From **Next steps**, **Experimental — seat distribution** opens a two-part flow (hidden from the main step trail; labelled as experimental in the UI):

1. **Committees** — edit committee names and seat counts (prefilled from example councils; custom councils add committees one at a time, same pattern as **Add group**). The sum of committee seats must match the total from step 2 before continuing.
2. **Distribution** — table of minimum seats per group per committee (proportional to committee size), then interactive sub-steps to place each extra seat (user picks the committee; suggestion defaults to the largest remaining entitlement).

**Back to results** returns to Next steps without changing the main wizard allocation.

## URL state

Progress and form data are stored in query parameters so links can be bookmarked or shared. Implemented in [`url_state.ts`](../../app/public/tsx/committee_seats/url_state.ts).

| Parameter | Purpose |
|-----------|---------|
| `step` | `choose`, `totals`, `groups`, `independents`, `allocation`, `next_steps` (legacy `setup` → groups) |
| `source` | `example` or `custom` |
| `example` | Example council id when `source=example` |
| `seats`, `councillors` | Committee seat total and council councillor total |
| `groups` | Encoded political group name/count pairs |
| `independents` | `included` or `excluded` |

## Example councils

Data lives in [`app/public/tsx/committee_seats/example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts).

| Id | Display name | Councillors | Committee seats | Independents in calculation |
|----|--------------|-------------|-----------------|----------------------------|
| `barnet` | Barnet Council | 63 (incl. 1 Independent) | 56 (sum of committees) | Default **excluded** |
| `bristol` | Bristol | 70 (incl. 1 Independent) | 144 (sum of 16 committees) | Default **excluded** |
| `bcp` | Bournemouth, Christchurch and Poole | 76 (12 groups; 2 on standard Independent row) | 111 (sum of 11 committees) | User chooses |
| `lambeth` | Lambeth | 63 (incl. 2 Vacancy) | 35 (sum of 7 committees) | N/A (no independents) |
| `sheffield` | Sheffield | 84 (incl. 4 Independent) | 168 | Default **included** |

`test_council` remains in the array for automated tests (groups-only shape); prefer not to rely on it in production UI.

**Vacancy** is a fixed row on the councillors-by-group step. Vacant seats count toward the council total but are always excluded from the proportional committee seat calculation (see `vacancy_allocation.ts`).

Human-readable reference: keep example figures documented when adding councils (optional markdown under `docs/committee_seats/`).

Optional `seat_assignment_source_url` on each example points at the council document that records political balance and committee seat allocation; on the council totals step, a note with a “here” link appears below the political-committees guidance when that example is selected.

## Architecture

- **PHP** mounts an empty widget shell only — no `data-widgety_json`. See [`Pages::committee_seats_page()`](../../src/Bristolian/AppController/Pages.php).
- **TypeScript / Preact** — all copy, examples, validation, calculation, and URL logic under `app/public/tsx/committee_seats/`.
- **Styles** — [`app/public/scss/committee_seats.scss`](../../app/public/scss/committee_seats.scss).

## Important files

### Web page shell (PHP)

- [`app/src/app_routes.php`](../../app/src/app_routes.php) — `GET /tools/committee_seats`
- [`src/Bristolian/AppController/Pages.php`](../../src/Bristolian/AppController/Pages.php) — `committee_seats_page()`
- [`src/Bristolian/AppController/Tools.php`](../../src/Bristolian/AppController/Tools.php) — tools menu link

### Frontend (TypeScript / Preact)

| File | Role |
|------|------|
| [`CommitteeSeatsPanel.tsx`](../../app/public/tsx/CommitteeSeatsPanel.tsx) | Main wizard UI |
| [`page_config.ts`](../../app/public/tsx/committee_seats/page_config.ts) | Page copy and wizard step labels |
| [`wizard_display_step.ts`](../../app/public/tsx/committee_seats/wizard_display_step.ts) | Display steps 1–6, trail visibility |
| [`url_state.ts`](../../app/public/tsx/committee_seats/url_state.ts) | URL encode/decode |
| [`example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts) | Example council data |
| [`political_groups_form.ts`](../../app/public/tsx/committee_seats/political_groups_form.ts) | Standard group rows and merge logic |
| [`independent_allocation.ts`](../../app/public/tsx/committee_seats/independent_allocation.ts) | Independent step copy and defaults |
| [`vacancy_allocation.ts`](../../app/public/tsx/committee_seats/vacancy_allocation.ts) | Vacancy row helpers and allocation exclusion |
| [`calculate_party_allocation.ts`](../../app/public/tsx/committee_seats/calculate_party_allocation.ts) | Validation, LGA calculation, workbook steps |
| [`next_steps.ts`](../../app/public/tsx/committee_seats/next_steps.ts) | Next steps copy |
| [`submit_example_council.ts`](../../app/public/tsx/committee_seats/submit_example_council.ts) | Send-data section and JSON export |
| [`types.ts`](../../app/public/tsx/committee_seats/types.ts) | Shared types |
| [`bootstrap.tsx`](../../app/public/tsx/bootstrap.tsx) | Widget registration (`committee_seats_panel`) |

### Tests

| File | Role |
|------|------|
| [`CommitteeSeatsPanel.test.tsx`](../../app/public/tsx/CommitteeSeatsPanel.test.tsx) | Examples, validation, allocation |
| [`CommitteeSeatsPanel.component.test.tsx`](../../app/public/tsx/CommitteeSeatsPanel.component.test.tsx) | Mounted panel wizard step transitions |
| [`url_state.test.ts`](../../app/public/tsx/committee_seats/url_state.test.ts) | URL round-trip |
| [`wizard_display_step.test.ts`](../../app/public/tsx/committee_seats/wizard_display_step.test.ts) | Step labels |
| [`independent_allocation.test.ts`](../../app/public/tsx/committee_seats/independent_allocation.test.ts) | Independent defaults |
| [`next_steps.test.ts`](../../app/public/tsx/committee_seats/next_steps.test.ts) | Summary rows |
| [`submit_example_council.test.ts`](../../app/public/tsx/committee_seats/submit_example_council.test.ts) | JSON export |
| [`political_groups_form.test.ts`](../../app/public/tsx/committee_seats/political_groups_form.test.ts) | Group merge |
| [`test/BristolianTest/AppController/PagesTest.php`](../../test/BristolianTest/AppController/PagesTest.php) | `test_committee_seats_page` |

Run Jest tests:

```bash
docker exec bristolian-js_builder-1 bash -c "npm run test -- --testPathPattern=committee_seats"
```

### Reference data

- [`docs/committee_seats/LGA guidance - Political Make Up of the Council Appendix B.pdf`](../committee_seats/LGA%20guidance%20-%20Political%20Make%20Up%20of%20the%20Council%20Appendix%20B.pdf) — LGA guidance
