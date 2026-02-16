# Testing Guidelines

## DataType Parameter Classes Testing

For classes in the `Bristolian\Parameters` namespace that implement the `DataType` interface (commonly referred to as "Params" classes), follow these testing guidelines:

### Required Tests

1. **Basic functionality test** - Test with valid data to ensure the class works correctly
2. **Validation error tests** - Test that appropriate validation exceptions are thrown for:
   - Missing required parameters
   - Invalid data types (e.g., passing integers when strings are expected)
   - Null values for required parameters

### Optional Parameter Testing

For classes with optional parameters:
- **One test with no optional parameters** - Test that the class works when all optional parameters are omitted
- **One test with all optional parameters** - Test that the class works when all optional parameters are provided
- **Individual optional parameter tests are NOT needed** - Avoid testing each optional parameter separately

### Tests to Avoid

The following test types are **NOT needed** for DataType parameter classes:
- JSON-like string content tests
- Special character handling tests
- Unicode/emoji content tests
- Long string boundary tests
- Whitespace handling tests
- Numeric string tests

These edge cases are handled by the underlying DataType validation framework and don't need to be tested at the parameter class level.

### Example Test Structure

```php
class ExampleParamTest extends BaseTestCase
{
    public function testWorks()
    {
        // Basic functionality test with valid data
    }

    public function testWorksWithAllOptionalParameters()
    {
        // Test with all optional parameters provided
    }

    public function testWorksWithNoOptionalParameters()
    {
        // Test with no optional parameters (only required ones)
    }

    public function testFailsWithMissingRequiredParameter()
    {
        // Validation error test
    }

    public function testFailsWithInvalidDataType()
    {
        // Validation error test
    }
}
```

### Test Behaviour, Not Interfaces

**Only test behaviour, not that a class implements interfaces.** Do not assert `assertInstanceOf(DataType::class, ...)` or similar. Interface checks are implementation details; tests should verify that the code does what it's supposed to do (e.g. parses input correctly), not what contracts it declares.

## General Testing Guidelines

### PHPUnit Coverage Annotations

- **Test class:** Use `@coversNothing` on the class docblock. This prevents coverage from being attributed to the class as a whole.
- **Each test method:** Add specific `@covers` annotations listing the classes/methods that test exercises. This ensures coverage is attributed correctly when tests run.
- **Constructor coverage:** Always include coverage for the class constructor in the first test of the class. Add `@covers \Full\Class\Name::__construct` to that test’s docblock so the constructor is included in coverage. Example: `test/BristolianTest/Service/BccTroFetcher/StandardBccTroFetcherTest.php` (first test covers `StandardBccTroFetcher::__construct`).
- **Example:**
  ```php
  /**
   * @coversNothing
   */
  class BarcodeLookupParamsTest extends BaseTestCase
  {
      /**
       * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
       * @covers \Bristolian\Parameters\PropertyType\OptionalBoolDefaultTrue
       * @dataProvider provides_fetch_external_input_and_expected_output
       */
      public function test_fetch_external_parses_input_to_expected_output(...): void
      ```

### DataProviders

When tests have multiple input/output cases, use PHPUnit DataProviders to separate test data from test logic. Use a **generic test method** that receives input and expected output, rather than separate test methods per case.

**Naming convention:**
- DataProvider method name should be `provides_` + test method name (without `test_` prefix)
- Example: Test method `test_fetch_external_parses_input_to_expected_output` → DataProvider `provides_fetch_external_input_and_expected_output`

**Placement:** Put the data provider method **above/before** the test method that uses it in the file.

**Use `yield` instead of returning arrays.** Optional string keys (e.g. `'missing key defaults to true'`) improve failure messages—PHPUnit includes them when a case fails.

```php
/**
 * @return \Generator<string, array{array, bool}>
 */
public static function provides_fetch_external_input_and_expected_output(): \Generator
{
    yield 'missing key defaults to true' => [[], true];
    yield 'true string' => [['fetch_external' => 'true'], true];
    yield 'false string' => [['fetch_external' => 'false'], false];
    yield '1 string' => [['fetch_external' => '1'], true];
    yield '0 string' => [['fetch_external' => '0'], false];
}

/**
 * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
 * @covers \Bristolian\Parameters\PropertyType\OptionalBoolDefaultTrue
 * @dataProvider provides_fetch_external_input_and_expected_output
 */
public function test_fetch_external_parses_input_to_expected_output(
    array $input,
    bool $expectedFetchExternal
): void {
    $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap($input));
    $this->assertSame($expectedFetchExternal, $params->fetch_external);
}
```

**Benefits of using `yield`:**
- Cleaner syntax with less nesting
- Each case is clearly labeled (optional keys improve failure messages)
- Easier to add/remove cases
- Better memory efficiency for large datasets

**PHP does not support complex/generic types as native parameter types.** For array parameters, use `array` as the native type and document the shape in PHPDoc: `@param array<string, mixed> $input`. Using `array<string, mixed>` as a native type causes a syntax error. The same applies to `@return` on data providers—use `array{array<string, mixed>, ...}` in the docblock.

### Use Real Objects, Not Mocks

**Never use mock objects in tests.** This project uses real objects and Fake implementations instead of mocking frameworks.

- ✅ **Use Fake implementations** - e.g., `FakeBristolStairsRepo`, `FakeAdminRepo`, `FakeUploadedFiles`
- ✅ **Use real objects** - Create actual instances of classes with test data
- ❌ **Do not use mocks** - Do not use `$this->createMock()`, `$this->getMock()`, or similar PHPUnit mocking features

#### Finding or Requesting Fake Objects

When writing tests, if you cannot find an appropriate Fake implementation for a dependency:

1. **Search for existing Fakes** - Check the relevant namespace for Fake classes (e.g., `Bristolian\Repo\*\Fake*Repo`)
2. **Ask for one to be created** - If no suitable Fake exists, inform the developer that a Fake implementation is needed rather than creating a mock

Example of proper Fake usage:

```php
public function testWithFakeRepo(): void
{
    $adminUser = AdminUser::fromPartial('test@example.com', 'password123');
    $adminRepo = new FakeAdminRepo([
        ['test@example.com', 'password123', $adminUser]
    ]);
    
    // Use the real Fake implementation in your test
    $result = $adminRepo->getAdminUser('test@example.com', 'password123');
    $this->assertInstanceOf(AdminUser::class, $result);
}
```

### Use Test Fixtures Instead of Creating Temporary Files

When tests require file inputs (images, PDFs, etc.), **use the existing test fixture files** rather than creating temporary files.

Available test fixtures in the `/test` directory:
- **`sample.pdf`** - PDF file for testing PDF handling
- **`sample.jpeg`** - JPEG image for testing image handling
- **`sample copy.pdf`** - Additional PDF file if multiple PDFs are needed

Example of using test fixtures:

```php
public function testWithPdfFile(): void
{
    $pdfPath = __DIR__ . '/../../sample.pdf';
    $uploadedFile = UploadedFile::fromFile($pdfPath);
    
    // Use the real test fixture file
    $result = $processor->processFile($uploadedFile);
    $this->assertInstanceOf(ProcessedFile::class, $result);
}
```

**Do not create temporary files with `sys_get_temp_dir()` or similar** - use the existing test fixtures instead.

### Testing Insertion and Retrieval of Items

When testing methods that insert items and then retrieve them (e.g., `createFoiRequest()` followed by `getAllFoiRequests()`), follow this pattern:

1. **Use `create_test_uniqid()` for unique strings** - Generate unique identifiers for fields that can be used to identify specific items:
   ```php
   $text1 = 'Request text ' . create_test_uniqid();
   $url1 = 'https://example.com/' . create_test_uniqid();
   $description1 = 'First request ' . create_test_uniqid();
   ```

2. **Create items with unique values** - Use the unique strings when creating test data:
   ```php
   $param1 = FoiRequestParams::createFromVarMap(new ArrayVarMap([
       'text' => $text1,
       'url' => $url1,
       'description' => $description1,
   ]));
   $repo->createFoiRequest($param1);
   ```

3. **Assert by finding items by their unique values** - Don't just check counts; find specific items by their unique identifiers and verify all fields:
   ```php
   $requests = $repo->getAllFoiRequests();
   
   // Find the request by its unique text
   $found1 = null;
   foreach ($requests as $request) {
       if ($request->getText() === $text1) {
           $found1 = $request;
           break;
       }
   }
   
   $this->assertNotNull($found1, 'Request should be found by unique text');
   $this->assertSame($text1, $found1->getText());
   $this->assertSame($url1, $found1->getUrl());
   $this->assertSame($description1, $found1->getDescription());
   ```

**Why this approach:**
- Works even when the database is seeded with existing data
- Verifies that the specific items you created are actually stored and retrievable
- Tests all fields of the retrieved objects, not just presence
- More robust than checking counts, which can be affected by other tests' data

**Prefer `create_test_uniqid()` over patterns like `'prefix_' . time() . '_' . random_int(1000, 9999)`** - it's specifically designed for tests and provides better uniqueness guarantees.

### Do Not Test for Initial Emptiness

**Never write tests that check for empty initial state** (e.g., "returns empty array initially", "returns null when queue is empty", "returns zero when queue is empty").

Databases can be seeded with data, so testing for initial emptiness is unreliable and not wanted. Tests should focus on:
- Creating data and verifying it can be retrieved
- Verifying behavior with data present
- Testing filtering/querying logic with specific data

**Examples of tests to avoid:**
- `test_getAll_returns_empty_array_initially()`
- `test_getMessagesForRoom_returns_empty_array_initially()`
- `test_getEmailToSendAndUpdateState_returns_null_when_queue_is_empty()`
- `test_clearQueue_returns_zero_when_queue_is_empty()`

**Instead, write tests that:**
- Create data and verify retrieval: `test_getAll_returns_saved_items()`
- Test behavior with data: `test_getMessagesForRoom_returns_messages_after_adding()`
- Test filtering logic: `test_getMessagesForRoom_returns_only_messages_for_specified_room()`

## Running PHP Tests

### phpunit.xml: fast vs full runs (db group and HTML coverage)

`phpunit.xml` can have the `@group db` tests excluded and HTML coverage disabled so normal test runs stay fast. Tests marked `@group db` depend on PDO/database.

**Toggle scripts** (run from project root, pass path to `phpunit.xml` when not using default):

- **Enable slow tests and HTML coverage** (for finalise_work / full coverage):  
  `php scripts/streamdeck/toggle_restore_content.php phpunit.xml`
- **Disable them again** (fast runs):  
  `php scripts/streamdeck/toggle_remove_content.php phpunit.xml`

When finalising work, run the restore script first so PHPUnit runs the db tests and produces the HTML coverage report.

### Running All PHPUnit Tests

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"
```

### Running Specific Tests

Use the `--filter` option to run specific tests by name:

```bash
# Run a specific test method
docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit -c test/phpunit.xml --filter testRooms_addLink_working"

# Run all tests in a specific test class
docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit -c test/phpunit.xml --filter RoomsTest"

# Run tests matching a pattern
docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit -c test/phpunit.xml --filter 'testRooms_'"
```

### Running a Specific Test File

```bash
docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit -c test/phpunit.xml test/BristolianTest/AppController/RoomsTest.php"
```

### Finding Uncovered Lines of Code

To identify which lines of code need test coverage, first run the unit tests to generate a coverage report:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh --no-progress"
```

Then use the `list_uncovered_lines.php` script to find uncovered lines. You can filter by namespace or directory:

```bash
# Find all uncovered lines in a specific namespace
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep Bristolian/Response"

# Find all uncovered lines in a specific directory
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep Bristolian/Model"

# Count uncovered lines for a namespace
docker exec bristolian-php_fpm-1 bash -c "php list_uncovered_lines.php clover.xml | grep Bristolian/Response | wc -l"
```

The output shows the file path and line numbers that are not covered by tests. Use this to identify which methods and code paths need additional test coverage.

**Note:** Some uncovered lines may be error-handling paths that are difficult to trigger in normal operation. Tell the user that these lines are difficult to test, and ask for guidance on how to handle them.

## JavaScript/TypeScript Testing

### Running Jest Tests

Jest tests for the frontend TypeScript/JavaScript code can be run using:

```bash
docker exec bristolian-js_builder-1 npm run test
```

Or from within the `bristolian-js_builder-1` container:

```bash
npm run test
```

Test files should be placed alongside the code they test and follow the naming convention `*.test.tsx` or `*.test.ts`.

### Running Node Commands

All Node.js commands (including `npm`, `node`, etc.) must be run inside the `bristolian-js_builder-1` container:

```bash
docker exec bristolian-js_builder-1 node <script>
docker exec bristolian-js_builder-1 npm <command>
```
