# Improve PHPStan Analysis

Runs PHPStan static analysis and fixes errors it reports.

## Usage

You can either:
1. **Run PHPStan on the entire codebase** - I will run PHPStan and fix all reported errors
2. **Provide a specific directory or file** - I will run PHPStan on that area and fix errors there

## How It Works

### Step 1: Run PHPStan Analysis

First, I run PHPStan to identify static analysis errors:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh"
```

For a specific directory or file:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh <path>"
```

### Step 2: Analyze Errors

I review the PHPStan output to understand:
- What types of errors are reported (type mismatches, undefined methods, etc.)
- Which files are affected
- The severity and context of each error

### Step 3: Fix Errors

I fix the reported errors by:
- Adding proper type hints and PHPDoc annotations
- Fixing type mismatches
- Handling null/undefined cases appropriately
- Adding necessary type checks or assertions
- Following existing code patterns and style

### Step 4: Verify Fixes

After making fixes, I run PHPStan again to verify:
- All errors are resolved
- No new errors were introduced
- The code still works correctly (run tests if needed)

## Examples

### Example 1: Fix All PHPStan Errors

**User:** `@improve_phpstan_analysis`

**What I do:**
1. Run PHPStan on the entire codebase
2. Analyze all reported errors
3. Fix errors systematically
4. Re-run PHPStan to verify all errors are resolved

### Example 2: Fix Errors in a Specific Directory

**User:** `@improve_phpstan_analysis src/Bristolian/Response`

**What I do:**
1. Run PHPStan on the specified directory
2. Analyze errors in that area
3. Fix the errors
4. Re-run PHPStan to verify fixes

### Example 3: Fix Errors in a Specific File

**User:** `@improve_phpstan_analysis src/Bristolian/Page.php`

**What I do:**
1. Run PHPStan on the specified file
2. Analyze errors in that file
3. Fix the errors
4. Re-run PHPStan to verify fixes

## Notes

- **Type safety**: PHPStan helps ensure type safety. Fixes should maintain or improve type safety, not weaken it.

- **Code style**: I follow existing code patterns and style when adding type hints and annotations.

- **Backward compatibility**: Fixes should not break existing functionality. If a fix might change behavior, I'll ask for guidance.

- **PHPStan levels**: The project uses a specific PHPStan level (configured in `phpstan.neon`). Fixes should meet that level's requirements.

- **Suppressions**: If an error cannot be fixed without significant refactoring, I may add a PHPStan suppression comment (`@phpstan-ignore` or similar) with an explanation, but I'll prefer fixing the underlying issue when possible.

- **Scope boundaries**: If fixing an error requires changes outside the specified directory or file, I will **stop and describe the problem** to you rather than making changes outside the requested scope. This ensures that improvements stay focused on the target area and any necessary broader changes can be discussed and approved first.

- **Unexpected behaviour from tools**: If you see unexpected behaviour from a tool (e.g. PHPStan crashes, reports errors that don't make sense, or odd output), tell the user what happened—we probably need to clean that up.
