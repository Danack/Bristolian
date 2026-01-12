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

### Interface Implementation Tests

Include tests to verify the class implements required interfaces:
- `DataType\DataType`
- `Bristolian\StaticFactory` (if applicable)

These tests ensure the class properly implements the expected contracts.

## General Testing Guidelines

### DataProviders

When tests have multiple input/output cases, use PHPUnit DataProviders to separate test data from test logic.

**Naming convention:**
- DataProvider method name should be `provides_` + test method name (without `test_` prefix)
- Example: Test method `test_parses_weight_formats` → DataProvider `provides_parses_weight_formats`

**Use `yield` instead of returning arrays:**

```php
public static function provides_parses_weight_formats(): \Generator
{
    yield 'with space' => ['125 g', 125.0];
    yield 'without space' => ['125g', 125.0];
    yield 'decimal with comma' => ['125,5 g', 125.5];
}

/**
 * @dataProvider provides_parses_weight_formats
 */
public function test_parses_weight_formats(string $input, float $expected): void
{
    // Test implementation
}
```

**Benefits of using `yield`:**
- Cleaner syntax with less nesting
- Each case is clearly labeled
- Easier to add/remove cases
- Better memory efficiency for large datasets

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

## Running PHP Tests

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
