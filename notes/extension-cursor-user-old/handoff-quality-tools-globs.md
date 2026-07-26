# Handoff: `quality_tools` in `codeview-data.json` (SUPERSEDED)

> **Superseded.** The QC UI no longer matches dirty files to globs. Use the button contract in [`handoff-qc-buttons.md`](./handoff-qc-buttons.md): `quality_tools` is a flat list of `{ id, label, command }` buttons always shown in the left chrome.

Do not emit `globs`, `exclude_globs`, or `stage` for QC buttons. Historical glob × dirty-file planning should not be used for new generators.
