# Behat Testing Notes

## Test Organization

Tests are organized by feature area:
- `features/basic.feature` - Basic site functionality tests
- `features/bristol_stairs.feature` - Bristol Stairs map feature tests
- `features/chat/` - Chat room feature tests (including `room_videos.feature` for the room Videos tab)

## Running Tests

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"
```

To run specific feature files:
```bash
docker exec bristolian-php_fpm-1 bash -c "vendor/bin/behat features/chat/"
```

## Fail Aid (failure screenshots)

Behat uses [behat-fail-aid](https://github.com/forceedge01/behat-fail-aid) via `BristolianBehat\BristolianFailureContext`, which extends `FailAid\Context\FailureContext`. On step failure, `[SCREENSHOT]` lines in the exception output are rewritten to host `file://` URLs when `BRISTOLIAN_HOST_PATH` is set (same mapping as `containerPathToHostPath()` in `SiteContext.php`). Screenshots are written under `/var/app/tmp` in the container (see `behat.yml`).

**`BRISTOLIAN_HOST_PATH` in Docker:** `docker-compose.yml` already passes it into `php_fpm` / `php_fpm_debug` as `BRISTOLIAN_HOST_PATH=${BRISTOLIAN_HOST_PATH}`. Define the value in a **`.env` file next to `docker-compose.yml`** (Compose loads it automatically), using the **absolute path to the Bristolian repo on your host**, for example:

```env
BRISTOLIAN_HOST_PATH=/Users/yourname/projects/github/Bristolian
```

Restart the PHP containers after changing `.env`. Alternatively, export that variable in your shell before `docker compose up`.

## Excluded Test Cases

The following test cases are **not** covered by Behat tests for specific reasons:

### Uploading without authentication
- **Reason**: Behat tests are solely to test the UI that is there. There is already a test (`Upload button is not visible when not logged in`) that verifies the upload button is not shown when the user is not logged in, which is the appropriate UI-level test for this functionality.

### Large file uploads
- **Reason**: File size validation and large file handling should be tested with PHPUnit unit tests rather than Behat browser tests. Behat tests focus on UI functionality, not edge cases that require specific file size testing.

## Chat Feature Tests

The `features/chat/share_file_link.feature` tests the Share button functionality:
- Verifies Share button visibility based on login state
- Tests that clicking Share inserts a markdown link into the message input
- Tests cursor position insertion (text inserted at cursor, not appended)

**Note**: These tests are conditional - if no files exist in the room, the file-dependent assertions are skipped gracefully.

## Frontend JavaScript coverage from Behat

Behat browser tests can generate **line-based coverage** for the frontend TypeScript/React code. This measures which JS/TS lines executed in the browser while the scenarios ran.

### Generating coverage

1. **Build an instrumented JS bundle** in the `js_builder` container:

```bash
docker exec bristolian-js_builder-1 bash -c "cd app && npm run js:build:coverage"
```

2. **Run the Behat tests** (inside `php_fpm`):

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"
```

During each scenario, the Behat `SiteContext` collects `window.__coverage__` from the browser (if present) and writes Istanbul coverage JSON files to (at **project root**):

- **`tmp/behat-js-coverage/`** — e.g. from host: `<project>/tmp/behat-js-coverage/`; in containers: `/var/app/tmp/behat-js-coverage/`

3. **Generate a coverage report** (inside `js_builder`):

```bash
docker exec bristolian-js_builder-1 bash -c "cd app && npm run js:coverage:report"
```

This merges all coverage JSON files from `tmp/behat-js-coverage/` and produces reports in (at **project root**):

- **`tmp/behat-js-coverage-report/`** — e.g. from host: `<project>/tmp/behat-js-coverage-report/`; in containers: `/var/app/tmp/behat-js-coverage-report/`

**Report formats generated:**

| Format        | File / output        | Use case                          |
|---------------|----------------------|-----------------------------------|
| HTML          | `index.html` (+ dir) | Human browsing in a browser       |
| text-summary  | stdout               | Quick terminal summary            |
| Clover XML    | `clover.xml`         | CI (e.g. Jenkins, GitLab), tools  |

Open the HTML report with:

```bash
open tmp/behat-js-coverage-report/index.html
```

### Notes and caveats

- The report shows **frontend JS/TS lines exercised by Behat browser tests**; it does not include backend PHP coverage.
- Scenarios that do not load the React/Preact app will not contribute to JS coverage.
- As with any coverage tool, **UI coverage ≠ full behaviour coverage** – a component can render without all branches or edge cases being exercised. The report is a guide to untested areas, not a proof of correctness.

