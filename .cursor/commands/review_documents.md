# Review Documents

When this command is called, read all documents in the `.cursor` directory and in the `docs/` directory, and note any inconsistencies or errors in them.

## What I Do

1. **Discover documents**  
   List and read every document under:
   - **`.cursor/`** – e.g. `.cursor/commands/*.md` and any other markdown or text docs.
   - **`docs/`** – all markdown (and other doc) files, including subdirectories such as `docs/developing/`, `docs/features/`, `docs/refactoring/`, `docs/explanations/`, `docs/site/`, etc.

2. **Review each document**  
   For each document, check:
   - **Accuracy**: Are commands, paths, scripts, and tool names correct? Do they match how the project is actually run (e.g. Docker, `runPhpStan.sh`, `runUnitTests.sh`)?
   - **Internal links**: Do links to other docs or anchors work? Note broken or ambiguous links.
   - **Structure and clarity**: Is the doc coherent? Are sections duplicative or redundant?
   - **Staleness**: Does it reference deprecated tools, old paths, or outdated workflows?

3. **Compare across documents**  
   Look for:
   - **Inconsistencies**: Different instructions for the same task (e.g. how to run tests, PHPStan, or Behat) across `.cursor` vs `docs/` vs `docs/developing/`.
   - **Contradictions**: One doc saying X, another saying Y for the same topic.
   - **Overlap**: Duplicate or near-duplicate content (e.g. multiple “how to run tests” or “development setup” sections) that could drift out of sync.
   - **Gaps**: Missing docs for important workflows, or docs that reference other docs that don’t exist.

4. **Report**  
   Produce a concise report that:
   - Lists each document reviewed (grouped by `.cursor` vs `docs/` and subdirs).
   - Notes any **errors** (wrong commands, invalid paths, broken links, incorrect tool names).
   - Notes any **inconsistencies** (conflicting or differing instructions across documents).
   - Notes **duplication** that might cause future inconsistencies.
   - Suggests **fixes** where obvious (e.g. “Use the PHPStan command from `finalise_work` everywhere” or “Merge the two development setup docs”).
   - Distinguishes clear bugs from style or organisation suggestions.

## Scope

- **In scope**: All document content under `.cursor/` and `docs/` (including subdirectories).
- **Out of scope**: Source code and non-doc config, unless a doc explicitly references them and the reference is wrong.

## Output

Write the report in plain language. Use bullet points or short sections so the user can quickly see what’s wrong, what’s inconsistent, and what to change.
