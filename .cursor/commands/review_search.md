# Review Search

When this command is called, review where an agent struggled to locate relevant code, identify why the search path was slow or indirect, and add durable breadcrumbs (preferably in generated artifacts or generation sources) so future agents can find the right code faster.

## What I Do

1. **Collect evidence of search struggle**  
   Start by identifying what was hard to find:
   - Which feature/bug area the agent was working on.
   - Which files were opened/read before the correct files were found.
   - Which "target" files or symbols were eventually needed.
   - Any clues that were missing (e.g. route -> param mapping, generated file missing source pointer, unclear naming).

2. **Map the discovery gap**  
   For each struggle, document:
   - **Start point** (where an agent would naturally begin).
   - **True source of truth** (where the relevant logic/params/rules actually live).
   - **Missing breadcrumb** (what link/metadata/comment/constant was absent).
   - **Best insertion point** for a breadcrumb, preferring:
     - code generation source files (e.g. `src/Bristolian/CliController/GenerateFiles.php`)
     - generated outputs that agents commonly read (with clear "generated from X" hints)
     - route/config registries (e.g. `api/src/api_routes.php`) where relationships are declared.

3. **Implement improvements (small, durable, low-noise)**  
   Add minimal but explicit breadcrumbs, such as:
   - route metadata linking endpoint to request param class.
   - generated maps/constants that expose backend parsing classes/types to frontend code readers.
   - comments in generated files that point to generator method and source definition files.
   - validation guards in generators so breadcrumb metadata cannot silently drift.

   Prefer additions that:
   - are machine-readable where possible (constants/maps over prose-only comments),
   - reduce future "guessing" by search tools,
   - do not add runtime behavior changes unless required.

4. **Regenerate and verify**  
   If generation sources changed:
   - run the relevant generator command(s),
   - verify generated outputs include the new breadcrumb(s),
   - run quick lint/type checks for edited files where practical.

5. **Report clearly**  
   Output:
   - what search gap(s) were found,
   - what breadcrumb(s) were added,
   - where they were added (files),
   - how a future agent should use them ("search X, then jump to Y"),
   - any remaining gaps not yet automated.

## Scope

- **In scope**: search/discoverability improvements, especially around generated code and API wiring.
- **Out of scope**: broad refactors unrelated to discoverability, behavior changes not needed for breadcrumbs.

## Output format

Keep the final report short and actionable:
- **Problem** – where search got stuck.
- **Fix** – breadcrumb mechanism added.
- **Files** – touched files.
- **How to use** – one-liner for future lookup.
