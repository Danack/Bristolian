# Committee seat allocation calculator — improvement suggestions

Ideas for future work on the tool at `/tools/committee_seats`. The feature already does the hard part well: it walks people through the LGA largest-remainder method rather than only outputting numbers.

See also [`agent-handoff-notes.md`](agent-handoff-notes.md) for current architecture and conventions. User-facing behaviour is summarised in [`committee_seat_calculator.md`](../features/committee_seat_calculator.md).

## Recently completed

Work already shipped (removed from the backlog below):

- **Panel split** — `CommitteeSeatsPanel.tsx` is orchestration only (~570 lines). Wizard views live under `app/public/tsx/committee_seats/` (`panel_state.ts`, `panel_wizard_chrome.tsx`, `steps/*`, `political_groups_editor.tsx`, `allocation_workbook.tsx`, etc.). See the file table in `agent-handoff-notes.md`.
- **Choose data source** — Example council first; dropdown default “Choose a Council”; example button disabled until a council is selected; URL state matches.
- **Council totals** — Political committees note; example intro names the selected council (`formatCouncilSetupExampleIntro`).
- **Political groups** — Shared council intro with totals step; single **Continue** (aside only); clearer councillor-total status copy.
- **Independent step** — Consequence note when choosing No (excluded from calculation).
- **Proportional calculation** — **Total seats allocated** column on rounding workbook rows; progress copy before each rounding sub-step.
- **Next steps** — **Start over**; summary table numbers right-aligned; removed “groups nominate who fills seats” block; **Copy JSON** with clipboard feedback on send-data section.
- **Examples** — Sheffield added; Bristol / Sheffield default independent inclusion via `allocate_seats_to_independents` on `ExampleCouncil` (no hardcoded Bristol special case in UI code).
- **Copy / title** — Page title “Committee seat allocation calculator” (menu and `page_config.ts`).
- **Docs** — `agent-handoff-notes.md` and `committee_seat_calculator.md` brought in line with the six-step wizard and modules.
- **Component-level tests** — [`CommitteeSeatsPanel.component.test.tsx`](../../app/public/tsx/CommitteeSeatsPanel.component.test.tsx) mounts the panel (Preact ref + synchronous `options.debounceRendering` in tests) and covers choose source → Bristol totals → groups → independents → allocation workbook.
- **Dead code tidy-up** — Removed legacy `enter_details` wizard substep, unused `rounding_steps` on allocation rows, `exampleCouncilHasCompleteSetup()`, and exports only used internally (`formatWorkbookRoundingSubstepLabel`, `Step1ValidationResult`).

## High impact for users

### 1. Link to LGA guidance on the calculation step

Next steps already paraphrases Appendix B. A direct link to the PDF (or an official LGA page) on the proportional calculation step would help people verify the method and use the results in negotiations.

Reference: [`LGA guidance - Political Make Up of the Council Appendix B.pdf`](LGA%20guidance%20-%20Political%20Make%20Up%20of%20the%20Council%20Appendix%20B.pdf)

### 2. Printable / shareable summary

URL state is good for bookmarking, but councillors often want a PDF or printout. A “Print results” button on Next steps (or a simple print stylesheet hiding the wizard chrome) would make the summary table and final allocation easy to take to a meeting.

### 3. Show warnings where they matter

The single-group warning is set during validation but only shown on the political groups step. If someone continues, it disappears. Surfacing it on the allocation step (or next steps) would stop a misleading result going unnoticed.

### 4. Clarify which committees to count (worked example)

The political committees note on the council totals step is in place. A short worked example (e.g. “if your council has 7 political committees with 10, 10, 8… seats, enter 56”) might still help more than abstract wording, especially for custom data.

## Technical / maintainability

### 5. Behat smoke test

Still on the handoff “not done” list. Even one scenario — load page, pick Bristol, reach allocation — would protect the PHP shell and webpack bundle wiring.

## Documentation

### 6. Example data workflow

Document how to add a council from submitted JSON (edit `example_councils.ts`, run tests, which fields are required). That closes the loop on “Send us your data”.

### 7. Sync `committee_seat_calculator.md` file table

Extend the feature doc’s “Important files” table to list the panel split (`steps/*`, `allocation_workbook.tsx`, etc.) — same structure as `agent-handoff-notes.md`.

## Probably out of scope (but often asked)

- **Per-committee allocation** — explicitly out of scope; keep saying so, maybe with a FAQ line.

## Suggested priorities

If picking three items first:

1. **Print/share summary** — practical for meetings and negotiations.
2. **Behat smoke test** — protects the integrated page without a large testing investment.
3. **Link to LGA guidance** on the calculation step — helps users trust and cite the method.

Also worthwhile early: **show warnings on later steps**.

Project-wide (not committee-seats-specific): Preact automatic JSX runtime — see [`docs/todo.md`](../todo.md).
