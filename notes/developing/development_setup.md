# Bristolian project

This file contains everything an LLM needs to know about the project.

## Services and Ports

| Service | Port | Description |
|---------|------|-------------|
| Main App | 80 | Varnish cache (main entry point) |
| Caddy | 8000 | Web server |
| Debug Backend | 8001 | PHPUnit debugging backend |
| JS Builder | 8888 | Webpack dev server |
| Websocket | 8015 | Chat websocket server |
| Redis | 6379 | Redis cache |
| MySQL | 3306 | Database |
| Supervisord | 8002 | Process manager |

## Development URLs

- **Main Application**: http://local.bristolian.org
- **API**: http://local.api.bristolian.org
- **Debug Backend**: http://local.bristolian.org:8001 (PHPUnit debugging)
- **Websocket**: ws://localhost:8015/chat


## Main Code Directories

### PHP Backend Code
- **`/src`** - Main PHP source code
    - Contains the core Bristolian application classes
    - Organized into namespaces: `Bristolian\`, `BristolianChat\`, `ModernGov\`, `OpenApi\`
    - Includes controllers, services, repositories, models, and utilities
    - Main entry point and application logic

- **`/app/src`** - App-specific PHP code
    - Contains main web application routes, factories, and configuration
    - Files: `app_routes.php` (web routes), `app_serve_request.php`, `app_factories.php`, etc.
    - Entry point for the main web application

- **`/api/src`** - API-specific PHP code
    - Contains API routes, factories, and configuration
    - Files: `api_routes.php` (API routes), `api_serve_request.php`, `api_factories.php`, etc.
    - Entry point for the API endpoints

- **`/chat/src`** - Websocket backend PHP code
    - Real-time chat functionality using Amp/Websocket
    - Uses separate Composer dependencies (amphp/websocket-server, etc.)
    - Files: `index.php` (main entry point), `something_to_ask.php`
    - Handles websocket connections and Redis integration

### Dependency Injection Configuration
- **`/app/src/app_injection_params.php`** - DI configuration for web application
- **`/api/src/api_injection_params.php`** - DI configuration for API
- **`/cli/cli_injection_params.php`** - DI configuration for CLI commands

### Object Factories
- **`/app/src/app_factories.php`** - Object instantiation factories for web application
- **`/api/src/api_factories.php`** - Object instantiation factories for API
- **`/src/factories.php`** - Core object factories shared across the application

### Frontend Code
- **`/app/public/tsx`** - TypeScript/React source code
    - Main frontend components and logic
    - React components for various panels and features
    - TypeScript configuration and utilities

- **`/app/public/scss`** - Sass/CSS source code
    - Styling and theming for the application
    - Organized into component-specific SCSS files
    - Compiled to `/app/public/css`

- **`/app/public/js`** - Compiled JavaScript
    - Generated from TypeScript source
    - Webpack-bundled application code

### Configuration & Build
- **`/app`** - Frontend build configuration
    - `package.json` - Node.js dependencies and build scripts
    - `webpack.config.js` - Webpack bundling configuration
    - `tsconfig.json` - TypeScript configuration
    - `jest.config.json` - Testing configuration

### Database & Migrations
- **`/db/migrations`** - Database migration files
    - PHP files for database schema changes
    - Version-controlled database evolution

### Testing
- **`/test`** - PHP unit tests
    - Test files for the main PHP codebase
    - Organized to mirror the source structure

### Infrastructure & Deployment
- **`/containers`** - Docker container configurations
    - Various service configurations (PHP-FPM, Nginx, MySQL, Redis, etc.)
    - Development and production environment setups

- **`/cli`** - Command-line interface
    - CLI commands and utilities
    - Administrative and maintenance scripts

## Supporting Directories (Not Core Code)

- **`/vendor`** - Composer dependencies (main web/CLI app)
- **`/chat/vendor`** - Composer dependencies for the chat/websocket app (includes amphp packages)

When you need to inspect how a PHP dependency works (e.g. Amp interfaces, method signatures), **check the local vendor code** in `vendor/` or `chat/vendor/` rather than relying on online search. The installed package source is the authority for the version you are using.

- **`/node_modules`** - NPM dependencies
- **`/data`** - Runtime data and cache
- **`/var`** - Variable data and logs
- **`/temp`** - Temporary files
- **`/docs_not_relevant`** - Excluded from project understanding

## Entry Points

1. **Main Web App**: `/app/public/index.php`
2. **API**: `/api/public/index.php`
3. **Websocket Backend**: `/chat/src/index.php`
4. **CLI**: `/cli.php`

## Frontend Development

### Building Assets

All npm/node commands must be run inside the `js_builder` container. From the host:

```bash
# Development build
docker exec bristolian-js_builder-1 bash -c "npm run js:build:dev"
docker exec bristolian-js_builder-1 bash -c "npm run sass:build:dev"

# Production build
docker exec bristolian-js_builder-1 bash -c "npm run js:build:prod"
docker exec bristolian-js_builder-1 bash -c "npm run sass:build:prod"

# Watch mode (run in interactive shell for long-running watch)
docker exec -it bristolian-js_builder-1 bash -c "npm run js:build:dev:watch"
docker exec -it bristolian-js_builder-1 bash -c "npm run sass:build:watch"
```
In the normal development environment, the JavaScript and Sass is rebuilt continually by watchers.

Most of the time, you should just check the output of the `bristolian-js_builder-1` and `bristolian-sass_dev_builder-1` containers to check for errors, rather than invoking the build tools yourself.

### Frontend Structure
- **TypeScript/React**: `/app/public/tsx`
- **Sass Styles**: `/app/public/scss`
- **Compiled JS**: `/app/public/js`
- **Compiled CSS**: `/app/public/css`

## Database Access

### MySQL Connection
```bash
mysql -uroot -pPrJaGpnNSzSLW8p8 -h127.0.0.1
```

### Database Credentials
- **Host**: localhost
- **Port**: 3306
- **Database**: bristolian
- **User**: bristolian
- **Password**: p5ffrKSk4mPqN8vH
- **Root Password**: PrJaGpnNSzSLW8p8

### Database Migrations

Database migrations are located in `/db/migrations` directory. Each migration file is numbered sequentially (e.g., `23_migration_name.php`).

**Important**: Migrations cannot be run automatically by AI coding assistants. When a new migration is created or needs to be run, the developer must manually execute it.

To run migrations:
```bash
# Method depends on your setup - consult with team lead or check project-specific scripts
# Example (if using a CLI tool):
# php cli.php db:migrate
```

When creating new migration files:
1. Use the next sequential number in the filename
2. Follow the existing naming pattern: `{number}_{descriptive_name}.php`
3. Implement `getAllQueries_{number}()` function that returns an array of SQL statements
4. Implement `getDescription_{number}()` function that returns a description string

**Note to AI Assistants**: If you create a migration file, inform the developer that they need to run it manually. Do not attempt to execute migrations programmatically.

## Redis Access

```bash
# Connect to Redis
redis-cli -h localhost -p 6379
```

## Docker Management

### Clean Up Docker
```bash
# Stop all containers
docker update --restart=no $(docker ps -a -q)
docker rm $(docker ps -a -q)
docker rmi $(docker images -q)
docker network rm $(docker network ls -q)
```

### Reset Database
```bash
# Stop containers and delete data
docker-compose down
rm -rf data/mysql
```

## Environment Files

The project uses `docker-compose.override.yml` for local development configuration. Key settings:

- **Environment**: `ENV_DESCRIPTION=default,local`
- **API Base URL**: `http://local.api.bristolian.org`
- **Port Mappings**: Various services exposed on localhost

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 80, 8000, 8015, 3306, 6379 are available
2. **Permission issues**: Check Docker volume permissions
3. **Database connection**: Verify MySQL container is running
4. **Build failures**: Check Docker logs for specific error messages

### Debugging

- **PHP Debug**: Use `php_fpm_debug` container with Xdebug
- **Webpack**: Check `js_builder` container logs
- **Sass**: Check `sass_dev_builder` container logs
- **Websocket**: Check `php_websocket` container logs

### Logs
```bash
# View container logs
docker-compose logs [service_name]

# Follow logs
docker-compose logs -f [service_name]
```

## Project Structure

- **PHP Backend**: `/src` (main), `/app/src` (app), `/api/src` (API)
- **Frontend**: `/app/public/tsx` (TypeScript), `/app/public/scss` (Sass)
- **Websocket**: `/chat/src` (separate Composer dependencies)
- **Database**: `/db/migrations` (schema changes)
- **Tests**: `/test` (PHP), `/app/public/tsx` (Jest)
- **Docker**: `/containers` (service configurations)

# Code quality and testing Guidelines

As a general rule, all PHP code that runs in production should have 100% unit test coverage. The main exception to this rule is PHP code that is run in developlment that generates data/code for use by the application. The generated code is tested through integration tests, and so the code that does the generation does not need to be tested for correctness.

## Running PHP Tests

When developing PHP code, the tests should be run outside of the container in the root of the project: 

```
sh runUnitTestsFast.sh
```
This will run all of the tests that need to be tested due to modified source or test files.

If the test runner (or PHPStan / CodeSniffer / similar) behaves oddly — weird output, no real results, exit code that does not match what you see — **stop**. Report what you ran, what you expected, and what happened. Do not start debugging the tool, probing alternate commands, or working around it unless the user asks you to.

When those tests are passing, the next step is to run PHPStan:

```
sh runPhpStan.sh
```

When there are no PHPStan errors, the next step is to run the full suite inside the container.

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"
```
That will generate code coverage reports in both HTML and Clover formats.

To identify which lines of code need test coverage, run: 

```
php list_uncovered_lines.php
```

When the code coverage is at 100% and all the tests are passing, re-run the PHPStan tests to check that nothing broken.

Finally run CodeSniffer with:

```
sh runCodeSniffer.sh
```

to check that all code style rules are met. CodeSniffer will fix some issues itself. Re-run it if it said it fixed issues.


### Running a Specific Test

To run a specific test, e.g. to investigate a failure use: 

```bash
docker exec bristolian-php_fpm-1 bash -c "php vendor/bin/phpunit -c phpunit.xml --filter testRooms_addLink_working test/BristolianTest/AppController/RoomsTest.php"
```


**Note:** Some uncovered lines may be error-handling paths that are difficult to trigger in normal operation. When you have analyzed a path and concluded it is difficult to test, add a comment in the source code above that code explaining why it is difficult to test. Tell the user that these lines are difficult to test, and ask for guidance on how to handle them.

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



### Running Chat (WebSocket) PHPUnit Tests

The project has two PHP codebases that share code but use different `composer.json` files:

- **Web + CLI** – HTTP-based web app and CLI tools; uses root `composer.json`
- **WebSocket server** – `BristolianChat` and `src/functions_chat.php`; uses `chat/composer.json`

Shared code (e.g. `ChatMessagePayload`, `functions_chat.php`) can be used by both. The difference is which libraries are available via each `composer.json`.

Chat tests run in the same container as the main tests:

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runChatUnitTests.sh"
```

This uses `phpunit_chat.xml` and tests under `test/BristolianChatTest`, with coverage for `src/BristolianChat` and `src/functions_chat.php`. To run specific chat tests, pass `-c phpunit_chat.xml` instead of `-c phpunit.xml` to PHPUnit.


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



### Test Behaviour, Not Interfaces

**Only test behaviour, not that a class implements interfaces.** Do not assert `assertInstanceOf(DataType::class, ...)` or similar. Interface checks are implementation details; tests should verify that the code does what it's supposed to do (e.g. parses input correctly), not what contracts it declares.

## General Testing Guidelines

### CLI command handlers: use `CliOutput` instead of `echo` and `exit`

Code invoked from `cli.php` (typically under `src/Bristolian/CliController/`) should **not** use `echo` or bare `exit()` for output and termination if you want it covered by PHPUnit.

- Inject **`Bristolian\Service\CliOutput\CliOutput`** (usually via the controller constructor; see e.g. `BristolStairs`, `Rooms`, `MemeOcr`).
- Use **`$cliOutput->write(string)`** instead of `echo`.
- Use **`$cliOutput->exit(int $code)`** instead of `exit($code)`. In tests, **`CapturingCliOutput`** records writes and throws **`CliExitRequestedException`** (with the exit code) instead of ending the process.

Production CLI binds **`EchoCliOutput`** in `cli/cli_injection_params.php`. Tests use **`CapturingCliOutput`** and assert on captured output or caught exceptions.

**Do not** rely on `@codeCoverageIgnore` or comments such as “not unit-tested” to skip testing CLI code—prefer this pattern so behaviour is testable.

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


**PHP does not support complex/generic types as native parameter types.** For array parameters, use `array` as the native type and document the shape in PHPDoc: `@param array<string, mixed> $input`. Using `array<string, mixed>` as a native type causes a syntax error. The same applies to `@return` on data providers—use `array{array<string, mixed>, ...}` in the docblock.

### Use Real Objects, Not Mocks

**Never use mock objects in tests.** This project uses real objects and Fake implementations instead of mocking frameworks.

- ✅ **Use Fake implementations** - e.g., `FakeBristolStairsRepo`, `FakeAdminRepo`, `FakeUploadedFiles`
- ✅ **Use real objects** - Create actual instances of classes with test data
- ❌ **Do not use mocks** - Do not use `$this->createMock()`, `$this->getMock()`, or similar PHPUnit mocking features

**Prefer existing Fake repos over new test doubles.** The project has a Fake implementation for every repo (e.g. `FakeMemeStorageRepo`, `FakeAdminRepo`, `FakeBristolStairsRepo`). When writing tests that need a repo, use the existing Fake for that repo—seed it via its public API (e.g. `storeMeme`, `setUploaded`) and assert on observable behaviour—rather than introducing a new test double class that implements the same interface.

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
    $pdfPath = __DIR__ . '/../../fixtures/pdfs/sample.pdf';
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


**Prefer `create_test_uniqid()` over patterns like `'prefix_' . time() . '_' . random_int(1000, 9999)`** - it's specifically designed for tests and provides better uniqueness guarantees.

### Do Not Test for Initial Emptiness

**Never write tests that check for empty initial state** (e.g., "returns empty array initially", "returns null when queue is empty", "returns zero when queue is empty").

Databases can be seeded with data, so testing for initial emptiness is unreliable and not wanted. Tests should focus on:
- Creating data and verifying it can be retrieved
- Verifying behavior with data present
- Testing filtering/querying logic with specific data



# Front-end notes

## Buttons must have a CSS class

All `<button>` elements must have the `button_standard` class (or another explicit styling class). The global default button style in `standard_ui_objects.scss` is deliberately set to bright pink (`#ff00ff`) so that unstyled buttons are visually obvious as mistakes. Use `className="button_standard"` for standard buttons, and add `button_chat` as a secondary class for smaller inline action buttons (e.g. Edit, Play, Edit tags).

## Modals must close on Escape key

Any modal or overlay dialog must close when the user presses the Escape key. Add an `onKeyDown` handler on the modal overlay element that checks for `e.key === "Escape"` and calls the close function.

## Modal overlay structure

Modals follow a consistent two-div pattern: an outer overlay div that closes the modal on click, and an inner content div with `onClick={(e) => e.stopPropagation()}` to prevent clicks inside the modal from closing it. Use the classes `room_edit_tags_modal_overlay` for the overlay and `room_edit_tags_modal` for the inner content. When a save operation is in progress, disable closing: `onClick={() => !saveInProgress && this.close()}`.

## Error and success messages

Display errors using `<div className="error">` or `<span className="error">`. Display success messages using `<div className="success">`. Store error state as `error: string | null` in component state, and conditionally render: `{state.error && <div className="error">{state.error}</div>}`.

## Login-gated UI

Use the `store.ts` login state to conditionally show logged-in-only features. In class components, call `get_logged_in()` for the initial value and `subscribe_logged_in(callback)` in `componentDidMount` (unsubscribe in `componentWillUnmount`). In function components, use the `use_logged_in()` hook. Hide controls behind `{state.logged_in && (<button ...>)}`.

## API calls

Use the generated `api` object from `generated/api_routes` for standard GET endpoints (e.g. `api.rooms.links(room_id)`, `api.rooms.videos(room_id)`). Use raw `fetch` for POST/PUT endpoints or custom API calls. For tag mutations, use the helpers in `api_room_entity_tags.tsx` (`setLinkTags`, `setFileTags`, `setVideoTags`, `setAnnotationTags`).

## Optional initial data from PHP (widgety)

Panels are mounted by `bootstrap.tsx` + `widgety/widgety.tsx`. The PHP page renders a container whose `class` matches a bootstrap entry. **If** the server has data the client cannot derive, PHP may add `data-widgety_json`; `widgety.tsx` parses it and passes the object as constructor props (see `BristolStairs::render_stairs_page()`). If PHP only mounts an empty div, props are `{}` and the panel should take copy, defaults, and reference data from TypeScript (see `committee_seats/page_config.ts`, `committee_seats/example_councils.ts`, or `TwitterSplitterPanel`).

**When using `data-widgety_json`:**

- Declare a `*PanelProps` interface matching the PHP `$data` keys.
- Build `$data` with `convertToValue()`, `json_encode_safe()`, and `htmlspecialchars()` on the attribute.

**When not using it:**

- Keep configuration in `app/public/tsx/` (or generated types). PHP returns a mount point only, e.g. `Pages::committee_seats_page()`.

Do not use CSS parent selectors (e.g. `:has()`) to stand in for data or copy.

More detail: [creating_webpages.md](creating_webpages.md) — “PHP to TypeScript”.

## Component state initialization

Class components should define a `getDefaultState()` function that returns the initial state object, and set `this.state = getDefaultState()` in the constructor (or `getDefaultState(props)` when PHP passes initial props). This keeps defaults readable and separate from the constructor logic.

## Preact render return types

This project uses **Preact**, not React. Do not type `render()` or private render helpers with `h.JSX.Element` — that name comes from React’s JSX typings and is misleading here.

**Preferred:** omit the return type and let `Component`’s `render` signature infer it.

**If you need an explicit type** (e.g. a variable holding JSX before return), use Preact’s `VNode`:

```typescript
import type { VNode } from "preact";

private renderSection(): VNode | null {
    // ...
}
```

Event handlers can use `JSX.TargetedEvent<...>` via `import type { JSX } from "preact"`; that is only for DOM events, not for render output.

## CSS class naming

Use `snake_case` for CSS class names (e.g. `room_links_panel_react`, `room_edit_tags_modal_overlay`, `button_standard`). Panel root elements should use the pattern `{feature}_panel_react` (e.g. `room_videos_panel_react`, `meme_management_panel_react`).

## Tags display

Display entity tags using `<span className="room_entity_tags">` containing `<span className="room_entity_tag_chip">` elements for each tag. When there are no tags, render `<span className="room_entity_tags empty">—</span>`.

## Date/time for files, links and videos

When displaying timestamps for files, links and videos (e.g. in lists or panels), use the `formatDateTimeForContent` function from `functions.tsx`. It shows relative time within the last hour, "Today" + time for the same day, and date-only for older items.

## Empty states

When a list has no items, render a short descriptive `<p>` element (e.g. `<p>No videos.</p>`, `<p>No links.</p>`, `<p>No files.</p>`).


## Exceptions

Do not throw PHPs built-in exceptions. Each place where an exception is thrown in application code should have a custom exception generated in the appropriate directory e.g. src/Bristolian/Exception or src/BristolianChat/Exception

The message for an exception should be held inside a static constructor for the exception e.g.

```
class TooManyRoomTagsException extends BristolianException
{
    public static function forMaxReached(int $max): self
    {
        return new self("Maximum tags per room ($max) reached.");
    }
}
```
