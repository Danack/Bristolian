# Project todos

Miscellaneous follow-ups that do not belong in a feature-specific doc.

## Frontend

### Migrate to Preact automatic JSX runtime

**Status:** todo

Today `app/tsconfig.json` uses the classic JSX transform:

- `"jsx": "react"`
- `"jsxFactory": "h"`
- `"jsxFragmentFactory": "Fragment"`

Every `.tsx` file that contains JSX must `import { h } from "preact"` even when `h` is never called directly. Jest mirrors the same settings in `app/jest.config.json` (`globals.ts-jest.tsconfig`).

**Proposed change:** switch to Preact 10’s automatic runtime, for example:

```json
"jsx": "react-jsx",
"jsxImportSource": "preact"
```

Remove `jsxFactory` and `jsxFragmentFactory`.

**Effects (project-wide, not only committee seats):**

- Redundant `import { h }` can be removed from files that only use JSX syntax.
- Files that call `h(...)` explicitly (e.g. `app/public/tsx/widgety/widgety.tsx`) still need `import { h } from "preact"`.
- Event handler types using `h.JSX.TargetedEvent<...>` may stay as-is or move to `import type { JSX } from "preact"` and `JSX.TargetedEvent<...>`. Render return types should remain `VNode` per [developing/front_end_design_rules.md](developing/front_end_design_rules.md), not `h.JSX.Element`.
- `<>...</>` fragments no longer require importing `Fragment` for the factory (e.g. `RoomVideosPanel.tsx`).
- Update `app/jest.config.json` to match `app/tsconfig.json`.
- Full webpack rebuild and `npm run test` in `js_builder`; update examples in [developing/creating_webpages.md](developing/creating_webpages.md) if panel boilerplate changes.

**Trade-off:** one coordinated migration across all panels/components vs. ongoing “forgot to import `h`” compile errors on new TSX files.
