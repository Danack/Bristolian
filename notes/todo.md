# Project todos

Miscellaneous follow-ups that do not belong in a feature-specific doc.

## Frontend

### Migrate to Preact automatic JSX runtime

**Status:** done (`app/tsconfig.json` uses `"jsx": "react-jsx"` and `"jsxImportSource": "preact"`). Files that call `h(...)` explicitly still import `h`; panel JSX does not.
