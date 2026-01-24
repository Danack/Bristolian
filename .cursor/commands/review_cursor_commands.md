# Review Cursor Commands

When this command is called, read all commands in the `.cursor` directory and note any inconsistencies or errors in them.

## What I Do

1. **Discover commands**  
   List and read every command file under `.cursor/`, including `.cursor/commands/*.md` and any other markdown or config files that define Cursor commands.

2. **Review each command**  
   For each command file, check:
   - Structure: Does it have a clear purpose, usage, and steps?
   - Commands and scripts: Are any shell commands, paths, or tool invocations wrong or outdated?
   - Consistency with project rules: Do commands use `docker exec bristolian-php_fpm-1 bash -c "..."` (or the appropriate container) for PHP tooling, per `.cursorrules`?
   - Cross-references: If one command mentions another, or shared scripts (e.g. `runPhpStan.sh`, `runUnitTests.sh`), are those references correct and consistent?

3. **Compare across commands**  
   Look for:
   - **Inconsistencies**: Different ways to run the same tool (e.g. PHPStan, PHPUnit, Behat) or conflicting instructions.
   - **Contradictions**: One command saying X, another saying Y for the same scenario.
   - **Duplication**: Overlapping instructions that could get out of sync.
   - **Gaps**: Missing commands for common workflows, or steps that assume scripts/options that don’t exist.

4. **Report**  
   Produce a concise report that:
   - Lists each command file reviewed.
   - Notes any **errors** (wrong commands, invalid paths, broken references).
   - Notes any **inconsistencies** (conflicting or differing instructions across commands).
   - Suggests **fixes** where obvious (e.g. “Use `runPhpStan.sh` as in finalise_work” or “Align PHPUnit invocation with improve_test_coverage”).
   - Distinguishes clear bugs from style/preference suggestions.

## Scope

- **In scope**: All command-related content under `.cursor/` (typically `.cursor/commands/`).
- **Out of scope**: Source code, `docs/`, and other non-command docs unless a command explicitly references them.

## Output

Write the report in plain language. Use bullet points or short sections so the user can quickly see what’s wrong, what’s inconsistent, and what to change.
