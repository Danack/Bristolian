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

**Out of scope:** distributing party totals across individual committees (negotiated between groups).

**Per-committee list (UI):** Example councils may still define a `committees` array in `example_councils.ts` (used to prefill total committee seats via `getPrefilledTotalCommitteeSeats()`). The `CommitteeList` component in `committee_list.tsx` is **not mounted** anywhere at present; it may be reused later to show named committees on a step. Do not delete that file or example `committees` data without an explicit product decision.

## Primary goal: make the method understandable

The main purpose of this tool is not only to compute seat totals, but to help other people follow and understand the LGA largest-remainder process. **The party allocation workbook should teach the method step by step** — section headings and description rows should explain *what is happening and why*, in plain language, not opaque jargon.

When choosing or revising copy (workbook section titles, sub-step labels, wizard trail text, intro paragraphs), prefer wording that walks the reader through the calculation. Section **A** sets up exact proportional shares; section **B** shows floor-then-round; description rows spell out each roman sub-step (with progress text such as “We have allocated X/Y seats…” before each extra seat). Rounding row labels use the roman numeral only — no **“Sub-step i:”** prefix.

If a change makes the numbers correct but harder to follow, it is the wrong change unless the user explicitly prioritises something else.

## Architecture choices (read before changing)

- **PHP is a thin shell only.** `Pages::committee_seats_page()` renders `<div class="committee_seats_app"><div class="committee_seats_panel"></div></div>` with **no** `data-widgety_json`. All copy, example data, and wizard config live in TypeScript under `app/public/tsx/committee_seats/`. See `bootstrap.tsx` and **Optional initial data from PHP (widgety)** in `docs/developing/front_end_design_rules.md`.
- **SPA-style layout.** Full-width app shell via `.committee_seats_app` (negative margins against page padding), not by styling `.bristolian_content` with `:has()`.
- **Panel split.** `CommitteeSeatsPanel.tsx` (~570 lines) owns handlers, URL sync, and step routing. Step UI lives under `committee_seats/steps/`, shared editors, and `allocation_workbook.tsx`.
- **URL state.** Wizard progress and form data are serialised in `url_state.ts` (`step`, `source`, `example`, `seats`, `councillors`, `groups`, `independents`). Legacy `step=setup` round-trips to the political-groups substep.
- **Preact:** do not type `render()` with `h.JSX.Element`; see `docs/developing/front_end_design_rules.md`.
- **Tests:** Jest in `js_builder`, PHPUnit for page shell. Run:
  ```bash
  docker exec bristolian-js_builder-1 bash -c "npm run test -- --testPathPattern=committee_seats"
  docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit --filter test_committee_seats_page"
  ```
  After editing TypeScript, check compilation:
  ```bash
  docker logs bristolian-js_builder-1 --tail 20
  ```

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
| `app/public/tsx/committee_seats/panel_wizard_chrome.tsx` | Header, step trail, step indicator, council-setup intro message |
| `app/public/tsx/bootstrap.tsx` | Registers `committee_seats_panel` → `CommitteeSeatsPanel` |
| `app/public/scss/committee_seats.scss` | All styles (imported from `site.scss`) |
| `app/public/tsx/CommitteeSeatsPanel.test.tsx` | Jest: examples, validation, allocation |
| `app/public/tsx/CommitteeSeatsPanel.component.test.tsx` | Jest: mounted panel wizard transitions |

### Frontend (wizard steps & editors)

| File | Role |
|------|------|
| `steps/council_setup_step.tsx` | Routes council-setup substeps; shared intro on totals/groups/independents |
| `steps/choose_data_source_step.tsx` | Example first, custom second; dropdown + disabled example button |
| `steps/council_totals_step.tsx` | Council totals screen wrapper |
| `steps/political_groups_step.tsx` | Political groups screen wrapper |
| `steps/independent_allocation_step.tsx` | Independent radio step |
| `steps/next_steps_step.tsx` | Results, send-data, advice, Back / Start over |
| `council_totals_editor.tsx` | Councillor + committee seat inputs; political committees note |
| `political_groups_editor.tsx` | Groups table; single **Continue** in aside |
| `committee_list.tsx` | Named committees table + note — **unused in UI**; kept for possible future use |
| `allocation_workbook.tsx` | Sections A & B, rounding rows, total seats column |

### Frontend (`committee_seats/` logic & copy)

| File | Role |
|------|------|
| `page_config.ts` | Title, tagline, wizard labels, workbook headings, `NO_EXAMPLE_COUNCIL_SELECTED` |
| `wizard_display_step.ts` | Display steps 1–6; hides independent step when no independents |
| `url_state.ts` | Query-parameter encode/decode |
| `example_councils.ts` | Barnet, Bristol, Sheffield, `test_council`; `applyExampleCouncilToFormState()` |
| `political_groups_form.ts` | Standard rows, aliases, merge, export for allocation |
| `independent_allocation.ts` | Step copy; defaults from `ExampleCouncil.allocate_seats_to_independents` |
| `calculate_party_allocation.ts` | Validation, LGA calculation, `workbook_steps`, descriptions |
| `next_steps.ts` | Next-steps copy |
| `submit_example_council.ts` | Send-data section, JSON export, clipboard copy |
| `types.ts` | `ExampleCouncil`, `PartyAllocationWorkbookStep` (`total_seats_allocated`), etc. |

### Tests (`committee_seats/*.test.ts`)

| File | Role |
|------|------|
| `url_state.test.ts` | URL round-trip, legacy step names, choose-without-example |
| `wizard_display_step.test.ts` | Display step labels and visibility |
| `independent_allocation.test.ts` | Example defaults (Bristol, Sheffield), independent detection |
| `next_steps.test.ts` | Summary row filtering |
| `submit_example_council.test.ts` | JSON export, send-data eligibility |
| `political_groups_form.test.ts` | Group merge and alias matching |

### Docs & reference

| File | Role |
|------|------|
| `docs/features/committee_seat_calculator.md` | Feature index (user-facing overview) |
| `docs/committee_seats/improvement-suggestions.md` | Backlog and recently completed work |
| `docs/committee_seats/LGA guidance - Political Make Up of the Council Appendix B.pdf` | LGA guidance (Next steps copy) |
| `docs/developing/front_end_design_rules.md` | Widgety optional-data note |

## Wizard UX (current)

### Top-level page header

Title **“Committee seat allocation calculator”** and tagline in `page_config.ts` / `CommitteeSeatsAppHeader`. Tools menu uses the same title.

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

### Council setup substeps

| Substep | Screen |
|---------|--------|
| `choose_data_source` | `ChooseDataSourceStep` |
| `enter_council_totals` | `CouncilTotalsStep` + `CouncilTotalsEditor` |
| `enter_political_groups` | `PoliticalGroupsStep` + `PoliticalGroupsEditor` |
| `choose_independent_allocation` | `IndependentAllocationStep` |

Shared intro on totals / groups / independents (`getCouncilSetupIntroMessage()`): custom vs `formatCouncilSetupExampleIntro(display_name)` for examples.

**Council totals:** note on which **political committees** count toward the seat total (`council_setup_political_committees_note`). No section title above the two inputs (removed).

**Political groups:** one **Continue** in the aside (duplicate bottom Continue removed). Error/warning shown on this step only.

**Independent allocation:** radios + **Continue** (disabled until chosen). `INDEPENDENT_ALLOCATION_STEP_COPY.consequence_note` when choosing No. Example defaults from `allocate_seats_to_independents` on `ExampleCouncil` (not hardcoded per council id in UI code). URL restore uses the same defaults when `independents` is omitted (Bristol → excluded; Sheffield → included).

### Party allocation workbook (step 5)

Rendered by `PartyAllocationStepView` in `allocation_workbook.tsx`. Section headings use grey band (`.committee_seats_allocation_workbook_section_heading`).

| Section | Label | Contents |
|---------|-------|----------|
| **A** | Calculate each group's proportional share | Councillors; %; exact entitlement |
| **B** | Round each share down, then allocate the remaining seats | Roman sub-steps **i**, **ii**, …; description rows; per-party seats; **Total seats allocated** column |
| — | Final allocation | Larger font; includes total column on final row |

Rounding description rows (from `calculate_party_allocation.ts`) explain allocated progress (e.g. “We have allocated 142/144 seats. To allocate the next…”) before each extra seat.

Continue labelled **“Summary”** (`allocation_summary_button_label`).

**Not shown:** per-committee “actual allocation” / “variance” rows.

### Next steps (step 6)

- **The results** heading; **Final allocation by group** (Councillors / Committee seats columns right-aligned).
- **Send us your data** when custom or example data was modified — JSON + **Copy JSON** with feedback.
- LGA-style guidance: negotiation, monitoring officer (`next_steps.ts`). Nominations block removed.
- **Back** (to calculation), **Start over** (`handleStartOver` → `getDefaultPanelState()`, clears URL).

## Example council data

In [`example_councils.ts`](../../app/public/tsx/committee_seats/example_councils.ts). Optional fields on `ExampleCouncil`:

- `total_committee_seats` — when no `committees` list.
- `allocate_seats_to_independents` — pre-selects independent step (boolean).

| Id | Councillors | Committee seats | Independent default |
|----|-------------|-----------------|-------------------|
| `barnet` | 63 (incl. 1 Independent) | 56 (sum of 7 committees) | **Excluded** |
| `bristol` | 70 (incl. 1 Independent) | 144 | **Excluded** |
| `lambeth` | 63 (incl. 2 Vacancy; Green 27, Labour 26, Lib Dem 8) | 35 (7 committees) | N/A |
| `sheffield` | 84 (incl. 4 Independent; plus Sheffield Community Councillors 2) | 168 | **Included** |
| `test_council` | 49 | 80 | User chooses; dev-shaped entry still in `EXAMPLE_COUNCILS` |

To add a council: extend `EXAMPLE_COUNCILS`, run `npm run test -- --testPathPattern=committee_seats`, update this table and [`committee_seat_calculator.md`](../features/committee_seat_calculator.md) if needed.

## URL query parameters

| Param | Values | Notes |
|-------|--------|-------|
| `step` | `choose`, `totals`, `groups`, `independents`, `allocation`, `next_steps` | Legacy `setup` → groups |
| `source` | `example`, `custom` | |
| `example` | council id | Omitted on choose step when none selected |
| `seats`, `councillors` | integers | |
| `groups` | encoded `name\|count` pairs | |
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

## Internal enums (`panel_state.ts`)

- `WizardStep`: `CouncilSetup = 1`, `PartyAllocation = 2`, `NextSteps = 3`.
- `CouncilSetupSubstep`: `ChooseDataSource` | `EnterCouncilTotals` | `EnterPoliticalGroups` | `ChooseIndependentAllocation`.
- `WizardDisplayStep` 1–6 in `wizard_display_step.ts` (separate from wizard step enum).

## Likely next work

See [`improvement-suggestions.md`](improvement-suggestions.md). Highlights:

- Behat smoke test for `/tools/committee_seats`.
- Print/share summary on Next steps.
- Show validation warnings on later steps (not only political groups).
- Link to LGA PDF on calculation step.
- Document example-data workflow for “Send us your data” submissions.
