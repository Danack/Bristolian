# Improve Test Coverage

Improves test coverage for a specified directory or namespace by identifying uncovered lines and creating tests for them.

## Usage

You can either:
1. **Provide a directory or namespace** - I will improve test coverage for that specific area
2. **Ask me to suggest a namespace** - I will analyze coverage and suggest which namespace needs the most improvement

## How It Works

### Step 1: Generate Coverage Report

First, I run the unit tests to generate a coverage report:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh --no-progress"
```

### Step 2: Find Uncovered Lines

Then I identify uncovered lines for the specified directory/namespace:

```bash
# For a namespace (e.g., Bristolian/Response)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep Bristolian/Response"

# For a directory (e.g., src/Bristolian/Response)
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep src/Bristolian/Response"
```

### Step 3: Analyze Existing Tests

I review existing test files in the `test/` directory to understand:
- Test structure and patterns
- How similar classes are tested
- What's already covered

### Step 4: Create Missing Tests

I create test files following the project's testing guidelines:
- Use `BaseTestCase` as the base class
- Follow existing test patterns and structure
- Use real objects, not mocks (per project guidelines)
- Test all uncovered methods and code paths
- Include `@covers` annotations

### Step 5: Verify Coverage Improvement

After creating tests, I run the tests again to verify:
- All new tests pass
- Coverage has improved
- No regressions were introduced

**Running only the tests you mean to test:** When invoking PHPUnit for a specific test class, **use `--filter`** with the test class name or with specific test method names. Running only the test file path (e.g. `php vendor/bin/phpunit test/BristolianTest/PageTest.php`) also executes **inherited** tests from the base class. For example, `BaseTestCase` defines `testPHPUnitApparentlyGetsConfused`; running a subclass such as `PageTest` that way runs that base-class test as well, so the reported test count can be higher than the number of tests you added. To run only the tests you intend to verify, filter by the test class (e.g. `--filter 'BristolianTest\\\\PageTest'`) or by specific methods (e.g. `--filter 'BristolianTest\\\\PageTest::testGetQrShareMessage'`). Note: filtering by class still runs inherited tests; to run *only* your new tests, filter by those methods explicitly.

**Unexpected behaviour from tools:** If you see unexpected behaviour from a tool (e.g. test count, failures, or odd output), tell the user what happened—we probably need to clean that up.

## Examples

### Example 1: Improve Coverage for a Namespace

**User:** `@improve_test_coverage Bristolian/Response`

**What I do:**
1. Run tests to generate coverage
2. Find all uncovered lines in `Bristolian/Response` namespace
3. Review existing Response test files
4. Create tests for uncovered Response classes
5. Run tests to verify improvement

### Example 2: Suggest a Namespace

**User:** `@improve_test_coverage suggest`

**What I do:**
1. Run tests to generate coverage
2. Analyze coverage across different namespaces
3. Identify which namespace has the most uncovered lines
4. Suggest that namespace for improvement
5. Optionally start improving it if you approve

### Example 3: Improve Coverage for a Directory

**User:** `@improve_test_coverage src/Bristolian/Model`

**What I do:**
1. Run tests to generate coverage
2. Find all uncovered lines in that directory
3. Review existing Model test files
4. Create tests for uncovered Model classes
5. Run tests to verify improvement

## Notes

- **Test structure**: I follow the patterns established in existing test files, using the same naming conventions, structure, and testing approaches.

- **No mocks**: Per project guidelines, I use real objects and Fake implementations instead of mock objects.

- **Coverage goal**: 100% coverage is the goal. It's okay to take our time. If something is difficult to test, ask for guidance. If a small refactor would make the code easier to test, that may be a better path than leaving it uncovered.

- **Error-handling paths**: Some uncovered lines may be defensive error-handling code that's difficult to trigger. These are acceptable to leave uncovered if they represent edge cases that are difficult to test for.

- **Scope boundaries**: If I encounter a situation where I cannot write passing tests without modifying code outside the specified namespace or directory, I will **stop and describe the problem** to you rather than making changes outside the requested scope. This ensures that improvements stay focused on the target area and any necessary broader changes can be discussed and approved first.
