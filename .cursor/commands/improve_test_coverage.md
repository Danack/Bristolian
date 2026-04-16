# Improve Test Coverage

Improves test coverage for a specified directory or namespace by identifying uncovered lines and creating tests for them. It can also be used to get the whole project back to 100% coverage when we have drifted slightly below it.

## Usage

You can either:
1. **Provide a directory or namespace** - I will improve test coverage for that specific area
2. **Ask me to suggest a namespace** - I will analyze coverage and suggest which namespace needs the most improvement
3. **Ask me to get the project back to 100% coverage** - I will run the full suite, inspect the clover report and uncovered-line output, and then cover the remaining gaps directly

## How It Works

### Step 1: Read Testing Guidelines

Read `docs/developing/testing_guidelines.md` before doing any of the steps below. It defines PHPUnit coverage annotations, data providers, the no-mocks rule, use of test fixtures, and other patterns you must follow when writing tests.

### Step 2: Generate Coverage Report

Run the unit tests to generate a coverage report:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh --no-progress"
```

### Step 3: Find Uncovered Lines

Identify uncovered lines for the specified directory/namespace, or inspect the whole project if the goal is to get back to 100% coverage:

```bash
# For a namespace (e.g., Bristolian/Response)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep Bristolian/Response"

# For a directory (e.g., src/Bristolian/Response)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep src/Bristolian/Response"

# For the whole project when aiming for 100% coverage
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml"
```

### Step 4: Analyze Existing Tests

Review existing test files in the `test/` directory to understand:
- Test structure and patterns
- How similar classes are tested
- What's already covered

### Step 5: Create Missing Tests

Create test files following the project's testing guidelines:
- Use `BaseTestCase` as the base class
- Follow existing test patterns and structure
- Use real objects, not mocks (per project guidelines)
- Test all uncovered methods and code paths
- Include `@covers` annotations

### Step 6: Verify Coverage Improvement

After creating tests, run the tests again to verify:
- All new tests pass
- Coverage has improved
- No regressions were introduced

**Running only the tests you mean to test:** When invoking PHPUnit for a specific test class, **use `--filter`** with the test class name or with specific test method names. Running only the test file path (e.g. `php vendor/bin/phpunit test/BristolianTest/PageTest.php`) also executes **inherited** tests from the base class. For example, `BaseTestCase` defines `testPHPUnitApparentlyGetsConfused`; running a subclass such as `PageTest` that way runs that base-class test as well, so the reported test count can be higher than the number of tests you added. To run only the tests you intend to verify, filter by the test class (e.g. `--filter 'BristolianTest\\\\PageTest'`) or by specific methods (e.g. `--filter 'BristolianTest\\\\PageTest::testGetQrShareMessage'`). Note: filtering by class still runs inherited tests; to run *only* your new tests, filter by those methods explicitly.

**Unexpected behaviour from tools:** If you see unexpected behaviour from a tool (e.g. test count, failures, or odd output), tell the user what happened—we probably need to clean that up.

## Examples

### Example 1: Improve Coverage for a Namespace

**User:** `@improve_test_coverage Bristolian/Response`

**What I do:**
1. Read testing guidelines
2. Run tests to generate coverage
3. Find all uncovered lines in `Bristolian/Response` namespace
4. Review existing Response test files
5. Create tests for uncovered Response classes
6. Run tests to verify improvement

### Example 2: Suggest a Namespace

**User:** `@improve_test_coverage suggest`

**What I do:**
1. Read testing guidelines
2. Run tests to generate coverage
3. Analyze coverage across different namespaces
4. Identify which namespace has the most uncovered lines
5. Suggest that namespace for improvement
6. Optionally start improving it if you approve

### Example 3: Improve Coverage for a Directory

**User:** `@improve_test_coverage src/Bristolian/Model`

**What I do:**
1. Read testing guidelines
2. Run tests to generate coverage
3. Find all uncovered lines in that directory
4. Review existing Model test files
5. Create tests for uncovered Model classes
6. Run tests to verify improvement

### Example 4: Get Back to 100% Coverage

**User:** `@improve_test_coverage get everything back to 100%`

**What I do:**
1. Read testing guidelines
2. Run the full test suite to generate a fresh coverage report
3. Inspect uncovered lines across the whole project from `clover.xml` and `list_uncovered_lines.php`
4. Review the affected code and nearby tests
5. Add the missing tests needed to cover the remaining gaps
6. Run the relevant tests during development, then rerun the full suite to confirm we are back at 100%

## Notes

- **Test structure**: I follow the patterns established in existing test files, using the same naming conventions, structure, and testing approaches.

- **No mocks**: Per project guidelines, I use real objects and Fake implementations instead of mock objects.

- **Coverage goal**: 100% coverage is the goal. In this project that usually means a small number of missing lines, not a huge campaign, so just run the suite, inspect the uncovered lines, and cover them. Do not faff about worrying about "large refactors" unless the code genuinely proves that one is required. If a small refactor is truly needed to make a line testable, keep it minimal and explain why.

- **Error-handling paths**: Some uncovered lines may be defensive error-handling code that's difficult to trigger. These are acceptable to leave uncovered if they represent edge cases that are difficult to test for.

- **Scope boundaries**: If I encounter a situation where I cannot write passing tests without modifying code outside the specified namespace or directory, I will **stop and describe the problem** to you rather than making changes outside the requested scope. This ensures that improvements stay focused on the target area and any necessary broader changes can be discussed and approved first.
