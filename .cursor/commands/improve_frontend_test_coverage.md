# Improve Frontend Test Coverage

> **Deferred:** Browser tests moved from Behat to Playwright (`app/e2e/`). Istanbul/`window.__coverage__` collection during e2e is **not wired yet**. Do not run this command expecting a live Clover report until Playwright coverage dump + nyc report are restored (see `notes/developing/development_setup.md` § Deferred: frontend coverage).

When restored, the intended flow is:

1. Build instrumented bundle: `docker exec bristolian-js_builder-1 bash -c "cd /var/app/app && npm run js:build:coverage"`
2. Run e2e (must dump `__coverage__`): `sh runPlaywright.sh`
3. Report: `docker exec bristolian-js_builder-1 bash -c "cd /var/app/app && npm run js:coverage:report"`
4. Inspect: `tmp/behat-js-coverage-report/clover.xml` via `php list_uncovered_frontend_lines.php …`
5. Extend Playwright specs under `app/e2e/` (not Gherkin) to cover uncovered frontend lines

Until then, prefer adding meaningful Playwright specs for the target UI without relying on coverage numbers.
