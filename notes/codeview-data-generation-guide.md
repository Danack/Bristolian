# CodeView data — goal and how to build `codeview-data.json`

This note is for **Cursor (or a human) working in the application project** (e.g. Bristolian), not only in the CodeView extension repo. Use it when generating or updating `codeview-data.json`.

Hard-coding path names, category keys, and Bristolian-specific shapes is fine for now. The priority is a useful map of **this** app. Generic / multi-project design can wait.

Related:

- Extension UI behaviour: [`codeview-extension-overview.md`](./codeview-extension-overview.md)
- Later (not now): [`todo-decouple-category-behaviour.md`](./todo-decouple-category-behaviour.md)

---

## Goal of the app

**CodeView** is an interactive map of how the application is wired: entry points, UI surfaces, services, repos, and data stores — with click-through to real source files.

It is not a generic file tree and not a full call graph. It answers questions like:

- What CLI / supervisor / HTTP / API entry points exist?
- Which controllers and dependencies sit behind an entry point?
- Which data sources does a repo read/write?
- Which entry points touch a given data source or repo?
- (Next) Which webpages or widgets call which API endpoints?

The extension only **displays** data. All intelligence lives in **`codeview-data.json`** at the **workspace root of the app** being inspected. If a layer or edge is missing from the JSON, it will not appear in the UI.

### Mental model: layers + views

Think of the app as **layers** (or node types). Each category in the UI is a **view** into one layer. Selecting something drills into related nodes in other layers.

| Layer (concept) | Example in Bristolian | Role in CodeView |
|-----------------|----------------------|------------------|
| Entry: CLI | `cli_commands` | Runnable commands |
| Entry: batch / daemon | `supervisord_tasks` | Supervisor programs |
| Entry: HTTP pages | `http_endpoints` | Browser routes / HTML pages |
| Entry: API | `api_endpoints` | JSON / machine routes |
| Entry: features (optional) | `features` | Product features (display / grouping) |
| Controllers | `controllers` + `code-map` | Handler classes / methods |
| Dependencies / repos | `dependencies` | Injected interfaces (often `*Repo`) |
| Data sources | `datasources` | Tables, queues, external stores |
| UI: pages / widgets | *(not in JSON yet)* | Front-end surfaces that call APIs |

Today the UI supports two main **drill-down views**:

1. **Entry → deps → data sources**  
   Pick CLI / supervisord / API / HTTP → controller item → dependencies + datasources (with read/write arrows).

2. **Data source → repo → entry points**  
   Pick Data Sources → repo → commands & endpoints that use that repo.

The long-term shape is: **pick a layer as the starting view**, then walk edges to other layers. New categories (pages, widgets, queues, workers, etc.) are more coverage of the same idea — not a different product.

Hard-coding new category keys and edge types in the extension is acceptable until the Bristolian map feels complete.

---

## Where the file lives

| Location | Purpose |
|----------|---------|
| `<app workspace>/codeview-data.json` | **Live** file the extension loads |
| CodeView repo `example-data/` or root copy | Samples / fixtures for extension development only |

Filename is fixed: **`codeview-data.json`** in the workspace root (first folder). No config for path yet.

---

## How to create / update the file

There is no required generator in-repo. Typical approach for Bristolian:

1. Scan known registration points (route tables, CLI command lists, supervisord conf, DI wiring, repo annotations, etc.).
2. Emit the JSON sections below.
3. Write/overwrite `codeview-data.json` at the app root.
4. Reopen or reload the CodeView panel in the Extension Development Host (or Cursor window that has the app open).

When Cursor is asked to “update CodeView data”, it should:

- Prefer **existing generators / scripts in the app** if present.
- Otherwise **derive from source** using the schemas below.
- Keep symbols consistent with PHP FQNs: `Namespace\\Class::method`.
- Prefer completeness of edges (controller → deps → datasources) over pretty labels.
- Leave unknown sections as empty arrays rather than inventing fake links.

It is OK to hard-code Bristolian paths (e.g. `src/Bristolian/...`, supervisord conf dirs) in the generator.

---

## Top-level document shape

```json
{
  "root": [ { "name": "…", "path": "/…" } ],
  "root_explanations": [ … ],
  "quality_tools": [ … ],
  "cursor_commands": [ … ],
  "workflows": [ … ],
  "cli_commands": [ … ],
  "supervisord_tasks": [ … ],
  "features": [ … ],
  "api_endpoints": [ … ],
  "http_endpoints": [ … ],
  "datasources": [ … ],
  "controllers": [ … ],
  "dependencies": [ … ],
  "code-map": [ … ]
}
```

Rules:

- Every `root[].path` of the form `/foo` expects a top-level key `foo` (path without leading `/`).
- Categories that are only listed in `root` but have no array (or an empty array) show as empty in the UI.
- Keys not referenced by `root` can still exist (e.g. `controllers`, `dependencies`, `code-map`, `quality_tools`, `cursor_commands`, `workflows`, `root_explanations`) — they are **indexes / graphs / workflow metadata**, not category lists.

---

## Section reference (current)

### `root`

Category buttons. Order = UI order.

```json
{ "name": "CLI commands", "path": "/cli_commands" }
```

**Currently hard-coded as navigable** in the extension (select item → deps graph):

- `/cli_commands`
- `/supervisord_tasks`
- `/api_endpoints`
- `/http_endpoints`

**Special:**

- `/datasources` — catalog drill-down (sources → repos → commands & endpoints), not the deps graph.

**Present but not navigable yet:**

- `/features` — listed; no special drill-down until the UI is extended.

Adding a new category for pages/widgets will require both JSON **and** a small hard-coded UI path (or temporary reuse of an existing navigable shape) until category behaviour is data-driven.

### `cli_commands`

```json
{
  "command": "debug:hello",
  "controller": "Bristolian\\CliController\\Debug::hello",
  "description": "Test cli commands are working."
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `command` | yes | Collapsed label |
| `controller` | yes for drill-down | `Class::method` must match `code-map` / `controllers` |
| `description` | no | Expand panel |

### `supervisord_tasks`

```json
{
  "command": "php cli.php process:meme_ocr",
  "controller": "Bristolian\\CliController\\MemeOcr::process",
  "program_name": "meme_ocr",
  "src_file": "containers/supervisord/tasks/php_meme_ocr.conf"
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `program_name` | preferred | Collapsed label (falls back to `command`) |
| `controller` | yes for drill-down | |
| `src_file` | no | Expand: double-click opens file |
| `command` | no | Expand: display-only |

### `api_endpoints` / `http_endpoints`

```json
{
  "path": "/api/…",
  "method": "GET",
  "controller": "Bristolian\\ApiController\\…::method",
  "return_types": ["string"],
  "response_mappers": [
    {
      "return_type": "string",
      "mapper": "Bristolian\\…::…"
    }
  ]
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `method`, `path` | yes | Collapsed label: `"GET /api/…"` |
| `controller` | yes for drill-down | |
| `return_types`, `response_mappers` | no | Expand panel |

Treat **HTTP** as page/HTML routes and **API** as machine JSON routes when both exist — that split matters for future “page → API” views.

### `datasources`

Catalog of stores (DB tables, etc.):

```json
{ "name": "meme_tag", "type": "database" }
```

Referenced from `dependencies[].methods[].datasources[].path` as `/datasources/{name}`.

### `controllers`

Class-level dependency list. Used for the Dependencies column and for reverse indexes (repo → entry points).

```json
{
  "name": "Bristolian\\ApiController\\Log",
  "dependencies": [
    "Bristolian\\PdoSimple\\PdoSimple",
    "Bristolian\\Repo\\ProcessorRunRecordRepo\\ProcessorRunRecordRepo"
  ]
}
```

Only dependency names that also appear in top-level `dependencies` are shown in the deps column.

Match `controller` on entry items by **class name** (strip `::method`).

### `dependencies`

Repo / service interfaces:

```json
{
  "name": "Bristolian\\Repo\\ApiTokenRepo\\ApiTokenRepo",
  "implementations": [
    "Bristolian\\Repo\\ApiTokenRepo\\FakeApiTokenRepo",
    "Bristolian\\Repo\\ApiTokenRepo\\PdoApiTokenRepo"
  ],
  "methods": [
    {
      "name": "getByToken",
      "datasources": [
        { "path": "/datasources/api_token", "reads": true, "writes": false }
      ]
    }
  ]
}
```

| Field | Notes |
|-------|--------|
| `implementations` | Shown when a dep/repo is expanded |
| `methods[].datasources` | Per-method; UI unions them for the graph today |
| `path` | Must be `/datasources/{name}` matching `datasources[].name` |
| `reads` / `writes` | Drive green/orange arrows |

Do **not** put a top-level `datasources` array on the dependency object; use per-method entries.

### `code-map`

Jump-to-source index:

```json
{
  "name": "Bristolian\\ApiController\\Csp::get_reports_for_page",
  "file": "src/Bristolian/ApiController/Csp.php",
  "line-start": 14,
  "line-end": 41,
  "dependencies": [ "…" ]
}
```

| Field | Notes |
|-------|--------|
| `name` | Prefer `Class::method`; class-only entries help fallbacks |
| `file` | Path relative to app workspace root |
| `line-start` | 1-based |
| `dependencies` | Optional; class-level `controllers` is the primary source for the UI |

Missing files are highlighted pastel-red in the panel.

### `quality_tools`

Always-visible QC buttons for CodeView left chrome. **Not** a category button. Flat list — no dirty-file glob matching.

Contract: [`extension-cursor-user/handoff-qc-buttons.md`](./extension-cursor-user/handoff-qc-buttons.md). (Older glob plan: [`handoff-quality-tools-globs.md`](./extension-cursor-user/handoff-quality-tools-globs.md) — superseded.)

```json
{
  "id": "phpstan",
  "label": "PHPStan",
  "command": "docker exec bristolian-php_fpm-1 bash -c \"sh runPhpStan.sh\""
}
```

| Field | Notes |
|-------|--------|
| `id` | Stable key for status lights |
| `label` | Button text |
| `command` | Exact shell string from app workspace **host** root. Bristolian uses `docker exec …`. |
| `description` | Optional hover/details text |

Array order = display order. Emitted by `ExplorerDataBuilder` / `php cli.php generate:codeview-data`.

### `cursor_commands`

Workflow metadata for CodeView action buttons that expose Cursor slash commands from `.cursor/commands/*.md`. **Not** a category button.

Source of truth for labels and order: `.cursor/commands/command.meta.json`. Generation **fails** if:

- a meta entry is missing a non-empty `name` or integer `priority`
- meta lists a markdown file that is not on disk
- a `.md` file exists in the directory with no meta entry

```json
{
  "id": "commit",
  "label": "Commit",
  "file": ".cursor/commands/commit.md",
  "priority": 1
}
```

| Field | Notes |
|-------|--------|
| `id` | Filename without `.md` (slash-command name) |
| `label` | Button text from meta `name` |
| `file` | Repo-relative path to the command markdown |
| `priority` | Sort key ascending; ties keep `command.meta.json` list order |

Emitted by `CursorCommandsEntryTypeFinder` / `php cli.php generate:codeview-data`.

### `workflows`

Config-driven workflow machines for CodeView (e.g. Work on selection). **Not** a category button.

Bristolian configures steps, copy, templates, and which catalog effects/guards fire. The CodeView extension interprets that config. Unknown effect/guard names need an extension capability — see [`extension-cursor-user/capability-request-template.md`](./extension-cursor-user/capability-request-template.md) and [`request-stateful-boot-chrome.md`](./extension-cursor-user/request-stateful-boot-chrome.md). Do not invent host behaviour in JSON.

Contract: [`extension-cursor-user/handoff-workflow-machine.md`](./extension-cursor-user/handoff-workflow-machine.md).

```json
{
  "id": "work-on-selection",
  "initial": "boot",
  "ui": { "startLabel": "Work on selection", "title": "Work on selection" },
  "steps": ["work", "qc", "checkin"],
  "templates": {
    "workStart": { "kind": "selectionContext" },
    "checkin": { "kind": "checkin" }
  },
  "states": { }
}
```

Emitted by `ExplorerDataBuilder` / `php cli.php generate:codeview-data`.

### `features`

Optional product groupings. UI does not drill into them yet; keep generating if useful for later.

---

## Edges the UI understands today

```
entry item.controller ──class──► controllers[].name
controllers[].dependencies[] ──► dependencies[].name
dependencies[].methods[].datasources[].path ──► datasources[].name
```

Reverse (Data Sources view):

```
datasources ←── dependencies (repos)
repos ←── controllers that list that dep
controllers ←── entry items whose Class::method maps to that class
```

If an edge is wrong or missing in JSON, the UI will show empty columns or incomplete graphs. Fix the data, not the panel first.

---

## Planned coverage (generate when ready; UI may lag)

These layers are part of the **goal** even if the webview does not fully support them yet. Prefer adding honest JSON early; hard-code UI for each new view as needed.

### Webpages / widgets → API endpoints

**Intent:** From a page or widget, see which API routes it calls (and later the reverse: which UIs call an API).

Suggested shape (illustrative — not yet consumed by the extension):

```json
{
  "root": [
    { "name": "Web pages", "path": "/web_pages" },
    { "name": "Widgets", "path": "/widgets" }
  ],
  "web_pages": [
    {
      "name": "Room page",
      "route": "/rooms/{id}",
      "http_endpoint": "Bristolian\\AppController\\…::…",
      "widgets": ["chat_panel", "video_list"],
      "api_calls": [
        { "method": "GET", "path": "/api/rooms/{room_id}/messages" }
      ]
    }
  ],
  "widgets": [
    {
      "name": "chat_panel",
      "source": "js/…",
      "api_calls": [
        { "method": "GET", "path": "/api/…" },
        { "method": "POST", "path": "/api/…" }
      ]
    }
  ]
}
```

Link `api_calls` to existing `api_endpoints` by **method + path** (or a stable id if you introduce one). Prefer reusing the same path strings already in `api_endpoints`.

When implementing the UI: Categories → Pages/Widgets → API endpoints (and optionally on into controllers / deps / datasources).

### Other layers worth mapping later

| Layer | Why |
|-------|-----|
| Queues / jobs | Who publishes, who consumes |
| External HTTP clients | Outbound integrations |
| Auth / middleware | Cross-cutting entry wrappers |
| Config / env keys | What a feature depends on at runtime |

Same pattern: catalog + edges to controllers/repos/entry points. Hard-code category behaviour per layer until a general schema exists.

---

## Checklist for a useful regenerate

1. **`root`** lists every category you care about; arrays exist for each path.
2. Every navigable entry has a **`controller`** string that matches a **`code-map`** name (or at least class + method present in code-map).
3. Every controller class used by entries appears in **`controllers`** with accurate **`dependencies`**.
4. Every shown dependency appears in **`dependencies`** with **`implementations`** and per-method **`datasources`** where known.
5. Every datasource path `/datasources/X` has a matching **`datasources`** entry `{ "name": "X", … }`.
6. Paths in **`code-map`** are relative to the app root and exist on disk.
7. Sorting is not required in JSON; the UI sorts alphabetically.
8. Do not invent edges you cannot justify from code — empty is better than wrong.

---

## What “done for me” looks like

Short term (Bristolian-first):

- All real entry points (CLI, supervisord, HTTP, API) listed and openable.
- Deps + datasources graphs accurate enough to debug “who touches this table?”.
- Data-source reverse view lists the right commands/endpoints.
- Next slice: pages/widgets and their API calls, even if the first UI pass is hard-coded.

Longer term (optional):

- Data-driven categories (see todo note).
- Generators that run in CI or on demand so the JSON stays fresh.

Until then: **keep the JSON as the source of truth for the map**, and treat the extension as a specialised viewer for Bristolian’s layers.
