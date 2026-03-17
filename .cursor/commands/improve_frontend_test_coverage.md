# Improve Frontend Test Coverage

Improves frontend (JavaScript/TypeScript) test coverage for a specified file, directory, or component by identifying uncovered lines from Behat-driven Istanbul/nyc coverage and adding or extending Behat scenarios so more of the frontend is exercised in the browser.

## Usage

You can either:
1. **Provide a file or directory** - I will improve Behat coverage for that part of the frontend (e.g. `app/public/tsx/RoomFilesPanel.tsx` or `app/public/tsx/`)
2. **Ask me to suggest an area** - I will analyze the frontend coverage Clover report and suggest which file or area has the most uncovered lines

**Expectations:** All frontend Behat tests are expected to pass. All services required by the app are available in the development environment, and the tests are not flaky. A failing test is a bug to fix—do not skip, ignore, or mark scenarios as broken; fix the test or the code so the suite passes.

## How It Works

### Step 1: Read Behat and Frontend Coverage Notes

Read `docs/developing/behat_testing_notes.md` before doing any of the steps below. It describes how to run Behat, how frontend coverage is collected, and where reports (including the Clover file) are produced.

### Step 2: Generate Frontend Coverage Report

Ensure a frontend coverage report exists so the Clover file is available. If it is not already up to date, run:

1. **Build an instrumented JS bundle** (in `js_builder`):

```bash
docker exec bristolian-js_builder-1 bash -c "cd app && npm run js:build:coverage"
```

2. **Run the Behat tests** (in `php_fpm`):

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"
```

3. **Generate the coverage report** (in `js_builder`), which produces the Clover XML among other formats:

```bash
docker exec bristolian-js_builder-1 bash -c "cd app && npm run js:coverage:report"
```

The Clover file is written to **`tmp/behat-js-coverage-report/clover.xml`** at the project root.

### Step 3: Find Uncovered Lines from the Clover File

Use the same tool as for PHP coverage: **`list_uncovered_frontend_lines.php`** (project root). It reads any Clover XML and prints uncovered statement lines as `path:LINE`. Point it at the frontend Clover file and optionally filter by path:

```bash
# From project root (e.g. in php_fpm container)
php list_uncovered_frontend_lines.php tmp/behat-js-coverage-report/clover.xml | grep app/public/tsx

# For a specific file (e.g. RoomFilesPanel.tsx)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_frontend_lines.php tmp/behat-js-coverage-report/clover.xml | grep RoomFilesPanel"

# For a directory (e.g. all frontend tsx)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_frontend_lines.php tmp/behat-js-coverage-report/clover.xml | grep app/public/tsx"
```

- **Clover location**: `tmp/behat-js-coverage-report/clover.xml` at project root. Paths in the output are typically under `app/public/tsx/` for frontend sources.

### Step 4: Analyze Existing Behat Scenarios

Review existing Behat feature files in `features/` and steps in `src/BristolianBehat/SiteContext.php` (and related contexts) to understand:

- Which scenarios already load the pages or components that use the target frontend code
- What steps are available (visits, clicks, form filling, etc.) and how they map to UI behaviour
- Where new steps or scenarios would be needed to exercise the uncovered lines

### Step 5: Add or Extend Behat Scenarios

Add or extend Behat scenarios (and steps if needed) so that the uncovered frontend code is executed during a scenario:

- Prefer reusing existing steps and feature structure; add new steps in `SiteContext` only when necessary.
- Ensure new scenarios run against the same app (instrumented build) so coverage is collected; run the full flow (build coverage → Behat → report) after changes to verify.

### Step 6: Verify Coverage Improvement

Re-run the full frontend coverage flow and confirm:

- All Behat scenarios pass
- The Clover file (and HTML report if desired) shows improved coverage for the target file(s)
- No regressions in other areas

**Re-running the flow:** After editing features or steps, run: (1) `npm run js:build:coverage` in `js_builder`, (2) `sh runBehat.sh` in `php_fpm`, (3) `npm run js:coverage:report` in `js_builder`. Then re-read `tmp/behat-js-coverage-report/clover.xml` (and optionally `tmp/behat-js-coverage-report/index.html`) to confirm uncovered lines have decreased for the target code.

**Unexpected behaviour:** If Behat fails, the report is missing, or the Clover file does not reflect the expected files/lines, describe what happened to the user so we can fix the setup or commands.

## Examples

### Example 1: Improve Coverage for a Component

**User:** `@improve_frontend_test_coverage app/public/tsx/RoomFilesPanel.tsx`

**What I do:**
1. Read Behat and frontend coverage notes
2. Ensure coverage report exists (build instrumented bundle → Behat → report)
3. From `tmp/behat-js-coverage-report/clover.xml`, find uncovered lines for `RoomFilesPanel.tsx`
4. Review existing room/chat features and steps that load the files panel
5. Add or extend scenarios so the uncovered lines are exercised (e.g. search, tags, refresh)
6. Re-run the coverage flow and verify improvement in the Clover file

### Example 2: Suggest an Area

**User:** `@improve_frontend_test_coverage suggest`

**What I do:**
1. Read Behat and frontend coverage notes
2. Ensure coverage report exists
3. Parse or analyze `tmp/behat-js-coverage-report/clover.xml` to find files (e.g. under `app/public/tsx/`) with the most uncovered lines
4. Suggest that file or directory for improvement
5. Optionally start improving it if you approve

### Example 3: Improve Coverage for a Directory

**User:** `@improve_frontend_test_coverage app/public/tsx/`

**What I do:**
1. Read Behat and frontend coverage notes
2. Ensure coverage report exists
3. From the Clover file, find all uncovered lines for files under `app/public/tsx/`
4. Review existing features and steps
5. Add or extend Behat scenarios to cover the highest-impact uncovered code
6. Re-run the coverage flow and verify improvement

## Notes

- **Coverage source**: Coverage is **line-based frontend coverage** produced by Istanbul/nyc from `window.__coverage__` collected during Behat runs. It reflects which JS/TS lines ran in the browser, not PHP or server-side code.

- **UI vs line coverage**: A component can render without executing every branch. Use the Clover (and HTML) report as a guide to under-exercised code; add scenarios that trigger the missing behaviour (clicks, form inputs, errors, etc.) rather than only loading the page.

- **Scope**: Prefer adding or changing Behat feature files and step definitions; avoid changing production frontend behaviour solely to make coverage numbers look better. If covering a line would require large or fragile scenario changes, say so and suggest a smaller target or ask for guidance.

- **Scenarios that don’t load the app**: Behat scenarios that never load the React/Preact app do not contribute to frontend coverage. Focus new scenarios on flows that load the relevant pages and components.
