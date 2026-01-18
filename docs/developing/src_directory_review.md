# Source Directory Code Review

## Purpose
This document tracks a comprehensive review of the `/src` directory, documenting:
1. All directories and their purposes
2. Code patterns and inconsistencies

## Progress Tracking

### Step 1: Directory Listing
- [x] List all directories under `src/`

### Step 2: Code Purpose Summary (First Pass)
For each directory, document what the code does:
- [x] Bristolian/
  - [x] ApiController/
  - [x] AppController/
  - [x] Basic/
  - [x] ChatMessage/
  - [x] CliController/
  - [x] Config/
  - [x] CSPViolation/
  - [x] Data/
  - [x] Database/
  - [x] Exception/
  - [x] ExternalMarkdownRenderer/
  - [x] Filesystem/
  - [x] JsonInput/
  - [x] Keys/
  - [x] MarkdownRenderer/
  - [x] Middleware/
  - [x] Model/
  - [x] Moderation/
  - [x] MoonAlert/
  - [x] Parameters/
  - [x] PdoSimple/
  - [x] Repo/
  - [x] Response/
  - [x] Service/
  - [x] Session/
  - [x] SiteHtml/
  - [x] Types/
  - [x] UploadedFiles/
  - [x] UserNotifier/
  - [x] UserUploadedFile/
- [x] Root-level directories:
  - [x] BristolianBehat/
  - [x] BristolianChat/
  - [x] Key/
  - [x] OpenApi/
  - [x] Response/Typed/
  - [x] UrlFetcher/
- [x] Root-level files:
  - [x] check_composer_command.php
  - [x] error_functions.php
  - [x] factories.php
  - [x] functions_chat.php
  - [x] functions_common.php
  - [x] functions_exif.php
  - [x] functions_tinned_fish.php
  - [x] functions.php
  - [x] react_widgets.php
  - [x] site_html.php

### Step 3: Pattern Analysis and Inconsistencies (Second Pass)
- [x] Analyze code patterns for each directory
- [x] Document inconsistencies found

---

## Directory Structure

### Complete Directory Listing

#### Bristolian/ (Main Application Namespace)
The primary application namespace containing most of the application logic:

1. **ApiController/** - API endpoint controllers (JSON responses)
2. **AppController/** - Web page controllers (HTML responses)
3. **Basic/** - Core infrastructure (Dispatcher, ErrorLogger)
4. **ChatMessage/** - Chat message type definitions
5. **CliController/** - Command-line interface controllers
6. **Config/** - Configuration management
7. **CSPViolation/** - Content Security Policy violation handling
8. **Data/** - Data transfer objects and value objects
9. **Database/** - Database schema definitions and migrations
10. **Exception/** - Custom exception classes
11. **ExternalMarkdownRenderer/** - External markdown rendering services
12. **Filesystem/** - Filesystem abstraction (Flysystem wrappers)
13. **JsonInput/** - JSON input parsing utilities
14. **Keys/** - Key management for Redis/storage
15. **MarkdownRenderer/** - Markdown rendering services
16. **Middleware/** - HTTP middleware components
17. **Model/** - Domain models and types
18. **Moderation/** - (Empty directory)
19. **MoonAlert/** - Moon phase alert system
20. **Parameters/** - Request parameter validation types
21. **PdoSimple/** - PDO wrapper for database operations
22. **Repo/** - Repository pattern implementations
23. **Response/** - HTTP response objects
24. **Service/** - Business logic services
25. **Session/** - Session management
26. **SiteHtml/** - HTML generation utilities
27. **Types/** - Type definitions and enums
28. **UploadedFiles/** - File upload handling
29. **UserNotifier/** - User notification services
30. **UserUploadedFile/** - User file upload handling

#### Root-level Directories
1. **BristolianBehat/** - Behat test context classes
2. **BristolianChat/** - WebSocket/chat related classes
3. **Key/** - (Empty directory)
4. **OpenApi/** - OpenAPI specification generation
5. **Response/Typed/** - Typed response classes
6. **UrlFetcher/** - URL fetching utilities

#### Root-level Files
- **check_composer_command.php** - Composer command checker
- **error_functions.php** - Error handling functions
- **factories.php** - Object factory functions
- **functions_chat.php** - Chat-related helper functions
- **functions_common.php** - Common utility functions
- **functions_exif.php** - EXIF image metadata functions
- **functions_tinned_fish.php** - Tinned fish product functions
- **functions.php** - Core utility functions
- **react_widgets.php** - React widget rendering functions
- **site_html.php** - HTML generation functions

---

## Findings

### Code Purpose Summaries

#### Bristolian/ Directory Summaries

##### ApiController/
**Purpose:** Controllers that handle API requests and return JSON responses.

**Files:**
- `Csp.php` - Content Security Policy violation reporting API
- `Debug.php` - Debugging API endpoints
- `HealthCheck.php` - Health check endpoint
- `Index.php` - API index/root endpoint
- `Log.php` - Log viewing API (processor run records)
- `MailgunEmailHandler.php` - Mailgun webhook handler
- `TinnedFish.php` - Tinned fish product API

**Pattern:** Controllers use dependency injection, return typed responses (JsonResponse or custom Typed responses).

##### AppController/
**Purpose:** Controllers that handle web page requests and return HTML responses.

**Files:** (20 files total)
- `Admin.php` - Admin interface
- `BristolStairs.php` - Bristol stairs data pages
- `Chat.php` - Chat interface pages
- `ContentSecurityPolicy.php` - CSP report viewing pages
- `Debug.php` - Debug pages
- `Docs.php` - Documentation pages
- `FoiRequests.php` - Freedom of Information request pages
- `Images.php` - Image viewing pages
- `Login.php` - Login pages
- `MemeUpload.php` - Meme upload interface
- `Notifications.php` - User notification pages
- `Pages.php` - Main site pages (index, etc.)
- `QRCode.php` - QR code generation pages
- `Rooms.php` - Chat room pages
- `System.php` - System information pages
- `Tags.php` - Tag management pages
- `Tools.php` - Utility tools pages
- `Topics.php` - Topic pages
- `User.php` - User profile pages
- `Users.php` - User listing pages

**Pattern:** Return HTML strings or use response objects (StubResponse implementations).

##### Basic/
**Purpose:** Core infrastructure components.

**Files:**
- `Dispatcher.php` - Request dispatcher using DI container
- `ErrorLogger.php` - Error logging interface
- `FakeErrorLogger.php` - Fake error logger for testing
- `StandardErrorLogger.php` - Standard error logger implementation

**Pattern:** Interfaces with standard and fake implementations for testing.

##### ChatMessage/
**Purpose:** Chat message type definitions.

**Files:**
- `ChatType.php` - Enum defining chat message types (USER_MESSAGE, SYSTEM_MESSAGE, MESSAGE_DELETED)

##### CliController/
**Purpose:** Command-line interface controllers for background tasks and administrative operations.

**Files:** (14 files)
- `Admin.php` - Admin CLI commands
- `BccTroFetcherCliController.php` - BCC TRO data fetcher CLI
- `BristolStairs.php` - Bristol stairs CLI operations
- `CodeGen.php` - Code generation CLI
- `Database.php` - Database migration/management CLI
- `Debug.php` - Debug CLI commands
- `Email.php` - Email sending CLI
- `GenerateFiles.php` - File generation CLI (large file, 1592 lines)
- `Meme.php` - Meme management CLI
- `MemeOcr.php` - Meme OCR processing CLI
- `MoonInfo.php` - Moon phase information CLI
- `OpenApi.php` - OpenAPI generation CLI
- `Rooms.php` - Room management CLI
- `SystemInfo.php` - System information CLI

**Pattern:** CLI controllers that execute background tasks, some run via supervisord.

##### Config/
**Purpose:** Configuration management and environment settings.

**Files:**
- `AssetLinkEmitterConfig.php` - Asset link configuration interface
- `Config.php` - Main configuration class (reads from generated config)
- `EnvironmentName.php` - Environment name interface
- `ForceAssetRefresh.php` - Force asset refresh configuration
- `GetCommitSha.php` - Git commit SHA retrieval
- `HardCodedAssetLinkConfig.php` - Hardcoded asset link configuration
- `RedisConfig.php` - Redis configuration

**Pattern:** Configuration classes that read from generated config file (`config.generated.php`).

##### CSPViolation/
**Purpose:** Content Security Policy violation storage and management.

**Files:**
- `CSPViolationManager.php` - CSP violation manager interface
- `CSPViolationReporter.php` - Interface for reporting violations
- `CSPViolationStorage.php` - Interface for storing violations
- `FakeCSPViolationStorage.php` - Fake implementation for testing
- `RedisCSPViolationStorage.php` - Redis-based storage implementation

**Pattern:** Interfaces with Redis and Fake implementations. Stores violation reports from browser CSP reports.

##### Data/
**Purpose:** Data transfer objects and value objects.

**Files:** (4 files)
- Data classes for various domain concepts

**Pattern:** Value objects used for data transfer between layers.

##### Database/
**Purpose:** Database schema definitions, table structures, and migration-related code.

**Files:** Contains table definition files and migration code.

**Pattern:** Database schema is defined in PHP files that generate model classes.

##### Exception/
**Purpose:** Custom exception classes for the application.

**Files:** (9 files)
- `BristolianException.php` - Base application exception
- `BristolianResponseException.php` - Response-related exceptions
- `ContentNotFoundException.php` - Content not found exceptions
- `DataEncodingException.php` - Data encoding exceptions
- `DebuggingCaughtException.php` - Debugging exceptions (for tests)
- `DebuggingUncaughtException.php` - Uncaught exception testing
- `InvalidPermissionsException.php` - Permission exceptions
- `JsonException.php` - JSON parsing exceptions
- `UnauthorisedException.php` - Authentication exceptions

**Pattern:** Custom exception hierarchy extending base PHP exceptions.

##### Filesystem/
**Purpose:** Filesystem abstraction layer using Flysystem.

**Files:** (7 files)
- `AvatarImageFilesystem.php` - Avatar image filesystem
- `BristolStairsFilesystem.php` - Bristol stairs images filesystem
- `LocalCacheFilesystem.php` - Local cache filesystem (with location tracking)
- `LocalFilesystem.php` - Basic local filesystem wrapper
- `MemeFilesystem.php` - Meme image filesystem
- `RoomFileFilesystem.php` - Chat room file filesystem
- `UserDocumentsFilesystem.php` - User document filesystem

**Pattern:** Thin wrappers around Flysystem, providing typed filesystems for different use cases.

##### PdoSimple/
**Purpose:** PDO wrapper providing simplified database operations with automatic datetime conversion.

**Files:**
- `PdoSimple.php` - Main PDO wrapper class
- `PdoSimpleException.php` - Base exception
- `PdoSimpleWithPreviousException.php` - Exception with previous PDO exception
- `RowNotFoundException.php` - Row not found exception

**Key Features:**
- Automatic conversion of datetime columns to DateTimeInterface objects
- Methods: `execute()`, `insert()`, `fetchOneAsObject()`, `fetchAllAsObject()`, etc.
- Template-based generic object fetching

##### Repo/
**Purpose:** Repository pattern implementations for data access.

**Structure:** Each repository has:
- Interface (e.g., `UserRepo.php`)
- PDO implementation (e.g., `PdoUserRepo.php`)
- Fake implementation for testing (e.g., `FakeUserRepo.php`)

**Repositories:** (30+ repositories)
- AdminRepo, AvatarImageStorageInfoRepo, BccTroRepo, BristolStairImageStorageInfoRepo, BristolStairsRepo, ChatMessageRepo, DbInfo, EmailIncoming, EmailQueue, FoiRequestRepo, LinkRepo, MemeStorageRepo, MemeTagRepo, MemeTextRepo, ProcessorRepo, ProcessorRunRecordRepo, RoomFileObjectInfoRepo, RoomFileRepo, RoomLinkRepo, RoomRepo, RoomSourceLinkRepo, RunTimeRecorderRepo, SourceLinkRepo, TagRepo, TinnedFishProductRepo, UserDisplayNameRepo, UserProfileRepo, UserRepo, UserSearch, WebPushSubscriptionRepo

**Pattern:** Consistent repository pattern with interface, PDO implementation, and fake for testing.

##### Session/
**Purpose:** Session management for user authentication and session state.

**Files:**
- `AppSession.php` - Application session (represents logged-in user)
- `AppSessionManager.php` - Session manager implementation
- `AppSessionManagerInterface.php` - Session manager interface
- `FakeAppSessionManager.php` - Fake for testing
- `FakeUserSession.php` - Fake user session for testing
- `OptionalUserSession.php` - Interface for optional user sessions
- `StandardOptionalUserSession.php` - Standard implementation
- `UserSession.php` - User session interface

**Pattern:** Wraps Asm session library, provides application-level session abstraction.

##### Model/
**Purpose:** Domain models and value objects.

**Subdirectories:**
- `Chat/` - Chat message models
- `Generated/` - Auto-generated model classes from database schema
- `TinnedFish/` - Tinned fish product models
- `Types/` - Type definitions and value objects

**Pattern:** Models use readonly properties, some implement ToArray trait for serialization.

##### Service/
**Purpose:** Business logic services and domain services.

**Services:** (20+ services)
- AvatarImageStorage, BccTroFetcher, BristolStairImageStorage, ChatMessageService, DeployLogRenderer, EmailReceiver (Mailgun), EmailSender, FileCacheService, FileStorageProcessor, MemeStorageProcessor, MemoryWarningCheck, MoonAlertNotifier, ObjectStore, RoomFileStorage, RoomMessageService, TinnedFish, TooMuchMemoryNotifier, WebPushService

**Pattern:** Services encapsulate business logic, often have Standard implementations and may have Fake implementations for testing.

##### Parameters/
**Purpose:** Request parameter validation and type conversion.

**Files:** (50+ files)
- Parameter classes for validating and converting HTTP request parameters
- `PropertyType/` subdirectory - Property type validators
- `ProcessRule/` subdirectory - Processing rules for parameters

**Pattern:** Uses DataType library for validation, provides strongly-typed parameter objects.

##### Response/
**Purpose:** HTTP response objects.

**Files:** (37 files)
- Various response classes implementing StubResponse
- `Typed/` subdirectory - Strongly typed response classes
- `TinnedFish/` subdirectory - Tinned fish API responses

**Pattern:** Response classes implement StubResponse interface, some return JSON, some HTML.

##### Middleware/
**Purpose:** HTTP middleware for cross-cutting concerns.

**Files:** (8 files)
- `AllowAllCors.php` - CORS middleware
- `AppSessionMiddleware.php` - Session initialization middleware
- `ContentSecurityPolicyMiddleware.php` - CSP header middleware
- `ExceptionToErrorPageResponseMiddleware.php` - Exception to HTML error page
- `ExceptionToJsonResponseMiddleware.php` - Exception to JSON error
- `MemoryCheckMiddleware.php` - Memory usage monitoring
- `MiddlewareException.php` - Middleware exceptions
- `PermissionsCheckHtmlMiddleware.php` - Permission checking for HTML routes

**Pattern:** Middleware processes requests before controllers, handles cross-cutting concerns.

##### Root-level Directories

##### BristolianBehat/
**Purpose:** Behat test context classes for browser testing.

**Files:**
- `AdminContext.php` - Admin-specific test context
- `SiteContext.php` - General site test context

##### BristolianChat/
**Purpose:** WebSocket/chat system components.

**Files:**
- `ChatSpammer.php` - Chat spam prevention
- `ClientHandler.php` - WebSocket client handler
- `FallbackHandler.php` - Fallback handler for chat
- `RedisWatcherRoomMessages.php` - Redis watcher for room messages
- `RoomFilesWatcher.php` - Room file watcher
- `RoomMessagesWatcher.php` - Room message watcher

##### Root-level Files

##### factories.php
**Purpose:** Factory functions for creating objects from configuration and dependencies.

**Key Functions:**
- `createPDOForUser()` - Database connection factory
- `createSessionConfig()` - Session configuration
- `createLocalFilesystem()` - Local filesystem factory
- `createRedis()` - Redis connection factory
- Various other object factories

**Pattern:** Factory functions used by DI container for object creation.

##### functions.php
**Purpose:** Core utility functions used throughout the application.

**Key Functions:**
- `formatLinesWithCount()` - Format lines with numbers
- `checkSignalsForExit()` - Signal handling for graceful shutdown
- Various other utility functions (1217 lines total)

##### functions_common.php
**Purpose:** Common utility functions for value conversion.

**Key Functions:**
- `convertToValue()` - Convert objects/arrays to values for JSON serialization

##### functions_chat.php
**Purpose:** Chat-related helper functions for sending messages to clients.

**Key Functions:**
- `send_user_message_to_clients()` - Send user chat messages via WebSocket
- `send_system_message_to_clients()` - Send system messages via WebSocket

##### functions_tinned_fish.php
**Purpose:** Tinned fish product-related helper functions.

##### functions_exif.php
**Purpose:** EXIF image metadata extraction functions.

##### react_widgets.php
**Purpose:** React widget rendering helper functions.

##### site_html.php
**Purpose:** HTML generation helper functions for creating page layouts.

##### check_composer_command.php
**Purpose:** Validates Composer command availability.

##### error_functions.php
**Purpose:** Error handling and logging functions.

#### Remaining Bristolian/ Subdirectories

##### MarkdownRenderer/
**Purpose:** Markdown rendering using CommonMark library.

**Files:**
- `MarkdownRenderer.php` - Interface for markdown rendering
- `CommonMarkRenderer.php` - CommonMark implementation with extensions (footnotes, tables, task lists, etc.)
- `MarkdownRendererException.php` - Markdown rendering exceptions

**Pattern:** Interface with CommonMark implementation. Supports GFM features, footnotes, heading permalinks.

##### ExternalMarkdownRenderer/
**Purpose:** Render markdown from external URLs.

**Files:**
- `ExternalMarkdownRenderer.php` - Interface for external markdown rendering
- `StandardExternalMarkdownRenderer.php` - Implementation that fetches URL and renders markdown

**Pattern:** Interface with standard implementation that uses UrlFetcher and MarkdownRenderer.

##### JsonInput/
**Purpose:** JSON input parsing from request body.

**Files:**
- `JsonInput.php` - Interface for JSON input
- `InputJsonInput.php` - Reads from `php://input`
- `FakeJsonInput.php` - Fake for testing

**Pattern:** Interface with implementation and fake for testing.

##### Keys/
**Purpose:** Redis key generation utilities.

**Files:** (4 files)
- `ContentSecurityPolicyKey.php` - CSP violation report keys
- `RoomMessageKey.php` - Chat room message keys
- `UrlCacheKey.php` - URL cache keys (uses SHA256 hashing)
- `PhpBugsMaxCommentStorageKey.php` - PHP bugs comment storage keys

**Pattern:** Static classes with `getAbsoluteKeyName()` methods that generate namespaced Redis keys.

##### SiteHtml/
**Purpose:** HTML page generation utilities.

**Files:** (6 files)
- `AssetLinkEmitter.php` - Generates asset version suffixes
- `ExtraAssets.php` - Manages additional CSS/JS assets
- `HeaderLink.php` - Header link value object
- `HeaderLinks.php` - Header links collection
- `PageResponseGenerator.php` - Generates HTML page responses
- `PageStubResponseGenerator.php` - Generates StubResponse HTML pages

**Pattern:** Utilities for building HTML pages with asset versioning and response generation.

##### Types/
**Purpose:** Type definitions and utility types.

**Files:**
- `DocumentType.php` - Document type definitions
- `UserList.php` - User list type definitions

##### UploadedFiles/
**Purpose:** HTTP file upload handling.

**Files:**
- `UploadedFiles.php` - Interface for uploaded files
- `UploadedFile.php` - Uploaded file value object
- `ServerFilesUploadedFiles.php` - Server-side uploaded files implementation
- `FakeUploadedFiles.php` - Fake for testing

**Pattern:** Interface with server implementation and fake for testing.

##### UserNotifier/
**Purpose:** User notification services.

**Files:**
- `UserNotifier.php` - User notification interface
- `StandardUserNotifier.php` - Standard implementation

##### UserUploadedFile/
**Purpose:** User file upload handling.

**Files:**
- `UserUploadedFile.php` - User uploaded file value object
- `UserSessionFileUploadHandler.php` - File upload handler for user sessions

##### MoonAlert/
**Purpose:** Moon phase alert system.

**Files:**
- `MoonAlertRepo.php` - Moon alert repository interface
- `StandardMoonAlertRepo.php` - Standard implementation

##### Moderation/
**Purpose:** (Empty directory - placeholder for future moderation features)

##### Data/
**Purpose:** Data transfer objects and value objects for various domain concepts.

##### Database/
**Purpose:** Database schema definitions and migration code.

**Files:** Contains table definition files and migration code. Used to generate model classes.

**Pattern:** Database schema defined in PHP files that generate `Model/Generated/` classes.

#### Root-level Directories

##### OpenApi/
**Purpose:** OpenAPI specification generation.

**Files:**
- `OpenApiGenerator.php` - Generates OpenAPI 3.1.0 specification from routes

**Note:** Currently incomplete (marked `@codeCoverageIgnore`, "isn't functioning well enough yet to test").

##### UrlFetcher/
**Purpose:** URL fetching utilities.

**Files:**
- `UrlFetcher.php` - URL fetching interface
- `CurlUrlFetcher.php` - cURL implementation
- `RedisCachedUrlFetcher.php` - Redis-cached implementation
- `FakeUrlFetcher.php` - Fake for testing
- `UrlFetcherException.php` - URL fetching exceptions
- `UrlNotOkException.php` - HTTP error exceptions

**Pattern:** Interface with cURL implementation, caching wrapper, and fake for testing.

##### Response/Typed/
**Purpose:** Strongly-typed response classes for API endpoints.

**Files:** (15 files)
Typed response classes for specific API endpoints:
- `GetTfdV1ProductsBarcodeResponse.php`
- `GetRoomsSourcelinksResponse.php`
- `GetRoomsFileSourcelinksResponse.php`
- `GetRoomsLinksResponse.php`
- `PostBristolStairsCreateResponse.php`
- `GetChatRoomMessagesResponse.php`
- `GetRoomsFilesResponse.php`
- `GetUsersResponse.php`
- `PostUserProfileResponse.php`
- `GetMemesTagsResponse.php`
- `GetMemesSearchResponse.php`
- `GetMemesResponse.php`
- `GetLogProcessorRunRecordsResponse.php`
- `GetBristolStairsResponse.php`

**Pattern:** Typed response classes implementing StubResponse, used for API endpoints (newer pattern).

##### Key/
**Purpose:** (Empty directory)

##### BristolianBehat/
**Purpose:** Behat test context classes for browser testing.

**Files:**
- `AdminContext.php` - Admin-specific test context
- `SiteContext.php` - General site test context

**Pattern:** Behat context classes for feature tests.

##### BristolianChat/
**Purpose:** WebSocket/chat system components.

**Files:**
- `ChatSpammer.php` - Chat spam prevention
- `ClientHandler.php` - WebSocket client handler
- `FallbackHandler.php` - Fallback handler for chat
- `RedisWatcherRoomMessages.php` - Redis watcher for room messages
- `RoomFilesWatcher.php` - Room file watcher
- `RoomMessagesWatcher.php` - Room message watcher

**Pattern:** Chat/WebSocket handling classes, uses Redis for pub/sub.

---

## Step 2 Complete: All Directories Summarized

All directories under `src/` have been documented above.

### Patterns and Inconsistencies

#### 1. Declare Strict Types Inconsistency

**Issue:** Inconsistent use of spaces around `=` in `declare(strict_types = 1)` statements.

**Patterns Found:**
- `declare(strict_types = 1);` (with spaces) - Most common pattern
- `declare(strict_types=1);` (without spaces) - Less common

**Files with spaces:**
- Most files in `ApiController/`, `AppController/`, `Middleware/`
- `Csp.php`, `Debug.php`, `HealthCheck.php`, `Index.php`, `Log.php`
- `System.php`, `ContentSecurityPolicy.php`, `Pages.php`, `Topics.php`
- Various files in `Middleware/`, `Repo/`

**Files without spaces:**
- `Response/GetMemeTextResponse.php`
- `Response/GetMemeTagSuggestionsResponse.php`
- `Response/TinnedFish/GetAllProductsResponse.php`
- `Repo/TinnedFishProductRepo/FakeTinnedFishProductRepo.php`

**Recommendation:** Standardize on `declare(strict_types = 1);` (with spaces) as it's the more common pattern.

#### 2. Missing Declare Strict Types

**Issue:** Not all files declare strict types.

**Examples:**
- `ApiController/Log.php` - Missing `declare(strict_types = 1);`
- `AppController/Rooms.php` - Missing `declare(strict_types = 1);`
- `Session/FakeUserSession.php` - Missing `declare(strict_types = 1);`
- Many other files throughout the codebase

**Recommendation:** Add `declare(strict_types = 1);` to all PHP files that are missing it.

#### 3. Response Pattern Evolution

**Issue:** Two different response patterns coexist:

**Old Pattern (Non-typed responses):**
- Located in `Response/` directory
- Directly implements `StubResponse`
- Manual JSON encoding in constructor
- Examples: `GetMemeTextResponse.php`, `UploadAvatarResponse.php`

**New Pattern (Typed responses):**
- Located in `Response/Typed/` directory
- Auto-generated (note: "Auto-generated file do not edit")
- Uses `convertToValue()` helper for serialization
- Consistent structure with `'result' => 'success'` and `'data' => ...`
- Examples: `GetRoomsFilesResponse.php`, `GetUsersResponse.php`, `GetMemesResponse.php`

**Observation:** The typed response pattern appears to be the newer, preferred approach. Old responses should be migrated to the typed pattern when touched.

#### 4. Repository Pattern Consistency

**Pattern:** Very consistent repository pattern implementation.

**Structure:**
- Each repository has an interface (e.g., `UserRepo.php`)
- PDO implementation: `PdoUserRepo.php` (or `Pdo*Repo.php`)
- Fake implementation for testing: `FakeUserRepo.php` (or `Fake*Repo.php`)

**Naming Convention:**
- Interface: `{Name}Repo`
- PDO implementation: `Pdo{Name}Repo`
- Fake implementation: `Fake{Name}Repo`

**Consistency:** ✅ Excellent - All repositories follow this pattern consistently.

#### 5. Fake Implementation Pattern

**Pattern:** Consistent fake implementation pattern for testing.

**Characteristics:**
- All fake classes implement the same interface as the real implementation
- Located in the same directory as the real implementation
- Named `Fake{Name}` or `Fake{Name}Repo`
- Some have test helper methods (e.g., `FakeCSPViolationStorage::getClearCalls()`)

**Examples:**
- `FakeAdminRepo`, `FakeBristolStairsRepo`, `FakeUserSession`, `FakeJsonInput`
- `FakeUploadedFiles`, `FakeUrlFetcher`, `FakeMemoryWarningCheck`

**Consistency:** ✅ Excellent - All fakes follow naming and interface pattern consistently.

**Note:** Project guidelines explicitly prefer Fake implementations over mocks (see `docs/developing/testing_guidelines.md`).

#### 6. Service Pattern Naming

**Pattern:** Intentional naming convention based on implementation type.

**Naming Convention:**
- Interface: `{Name}Service` or `{Name}` (e.g., `MemoryWarningCheck`, `WebPushService`)
- Standard implementation: `Standard{Name}` or `Standard{Name}Service` - used when there's a single, default implementation
- Environment-specific implementations: `Local{Name}`, `Prod{Name}`, `Dev{Name}` - used when implementations differ by environment
- Fake implementation: `Fake{Name}` or `Fake{Name}Service` - for testing
- Vendor/technology-specific: `{Vendor}{Name}` (e.g., `MailgunEmailClient`) - when tied to specific technology

**Pattern Rules:**
1. **"Standard" prefix**: Use when there's a single implementation that works for all environments
   - Examples: `StandardBccTroFetcher`, `StandardChatMessageService`, `StandardWebPushService`
   - These represent the normal/production implementation

2. **Environment-specific names**: Use when implementations differ by environment and are selected via factory functions
   - Examples: `LocalDeployLogRenderer` / `ProdDeployLogRenderer`, `DevEnvironmentMemoryWarning` / `ProdMemoryWarningCheck`
   - Factory functions (in `factories.php`) select the appropriate implementation based on `Config::isProductionEnv()`
   - Names clearly indicate which environment they're for

3. **Vendor/technology-specific names**: When implementation is tied to a specific technology
   - Example: `MailgunEmailClient` (not StandardMailgunEmailClient)

**Consistency:** ✅ Intentional pattern - naming clearly communicates implementation type and usage context.

**See also:** Service implementation naming is documented in `docs/developing/development_setup.md`.

#### 7. Response Type Usage in API Controllers

**Issue:** Some API controllers use mixed return types.

**Pattern:**
- Most controllers return typed responses (e.g., `GetLogProcessorRunRecordsResponse`)
- Some use union types with `JsonResponse` (e.g., `Log::get_processor_run_records()` returns `GetLogProcessorRunRecordsResponse|JsonResponse`)
- Old commented-out error handling code in some controllers

**Example from `ApiController/Log.php`:**
```php
public function get_processor_run_records(...): GetLogProcessorRunRecordsResponse|JsonResponse {
    // ... old commented-out error handling code using JsonResponse ...
    return new GetLogProcessorRunRecordsResponse($db_data);
}
```

**Observation:** The union type suggests legacy error handling, but currently only typed responses are returned. Consider removing union type if no error cases use `JsonResponse`.

#### 8. Controller Method Naming

**Pattern:** Mixed naming conventions for controller methods.

**Conventions:**
- Snake_case: `get_processor_run_records()`, `get_route_list()`, `get_product_by_barcode()`
- camelCase: `getRouteList()`, `get()`, `getProductByBarcode()`

**Observation:**
- `ApiController` uses both snake_case and camelCase
- `AppController` primarily uses camelCase
- CLI controllers use snake_case (e.g., `CliController`)

**Inconsistency:** Methods in the same controller class may use different conventions.

**Recommendation:** Standardize on one convention per controller type (suggest camelCase for API/App controllers, snake_case for CLI).

#### 9. Empty Directories

**Directories Found Empty:**
- `Bristolian/Moderation/` - Placeholder for future moderation features
- `Response/Typed/` - Appears empty but actually contains 15+ files (may be a listing issue)
- `Key/` (root level) - Empty directory

**Recommendation:** Remove empty directories or add a `.gitkeep` file with a comment explaining purpose.

#### 10. Parameter Validation Pattern

**Pattern:** Consistent use of DataType library for parameter validation.

**Structure:**
- Parameter classes in `Parameters/` directory
- Use `CreateFromVarMap` trait
- Use `GetInputTypesFromAttributes` trait
- Property validation via attributes in `PropertyType/` subdirectory

**Consistency:** ✅ Excellent - All parameter classes follow this pattern.

#### 11. Model Class Patterns

**Two Model Directories:**

1. **`Model/Generated/`** - Auto-generated from database schema
   - Generated by code generation (see `CliController/GenerateFiles.php`)
   - Contains database row representations
   - Files marked "Auto-generated file do not edit"

2. **`Model/Types/`** - Manually created domain models
   - Value objects and domain types
   - Some use `ToArray` trait for serialization
   - Examples: `UserProfile`, `Tag`, `BccTro`, `Meme`

**Pattern:** Clear separation between generated database models and manually created domain models.

**Consistency:** ✅ Good - Clear separation maintained.

#### 12. Filesystem Pattern

**Pattern:** Consistent filesystem abstraction using Flysystem.

**Structure:**
- Each filesystem extends `League\Flysystem\Filesystem`
- Type-specific filesystems (e.g., `MemeFilesystem`, `AvatarImageFilesystem`)
- Location-aware filesystems (e.g., `LocalCacheFilesystem` stores root location)

**Naming:** All filesystem classes end with "Filesystem" suffix.

**Consistency:** ✅ Excellent - All filesystems follow Flysystem pattern.

#### 13. Exception Handling Pattern

**Pattern:** Custom exception hierarchy.

**Structure:**
- Base exception: `BristolianException`
- Specific exceptions extend base or `BristolianResponseException`
- Debugging exceptions for tests: `DebuggingCaughtException`, `DebuggingUncaughtException`

**Naming:** All exceptions end with "Exception" suffix.

**Consistency:** ✅ Good - Consistent naming and hierarchy.

#### 14. Key Generation Pattern

**Pattern:** Static classes for Redis key generation.

**Structure:**
- Static classes with `getAbsoluteKeyName()` methods
- Use class name in key (e.g., `__CLASS__`)
- Some hash parameters (e.g., `UrlCacheKey` uses SHA256)

**Naming:** All key classes end with "Key" suffix.

**Consistency:** ✅ Good - Consistent pattern.

#### 15. Configuration Pattern

**Pattern:** Configuration objects read from generated config file.

**Structure:**
- `Config.php` reads from `config.generated.php`
- Separate config classes for specific concerns (e.g., `RedisConfig`, `AssetLinkEmitterConfig`)
- Configuration interfaces for dependency injection

**Consistency:** ✅ Good - Consistent configuration approach.

#### 16. API vs App Controller Response Types

**Pattern:** Different response types for different controller types.

**API Controllers (`ApiController/`):**
- Return `StubResponse` or typed responses (`Get*Response`, `Post*Response`)
- All responses are JSON

**App Controllers (`AppController/`):**
- Return HTML strings or `StubResponse` implementations
- May use `PageStubResponseGenerator` for HTML responses

**Consistency:** ✅ Good - Clear separation maintained.

#### 17. CLI Controller Pattern

**Pattern:** CLI controllers for background tasks.

**Structure:**
- Located in `CliController/` directory
- Methods use snake_case naming
- Some run via supervisord (see `containers/supervisord/tasks/`)
- Large code generation file: `GenerateFiles.php` (1592 lines)

**Observation:** `GenerateFiles.php` is very large and handles multiple code generation tasks. Could potentially be split into separate generators.

#### 18. Generated Files

**Auto-Generated Files:**
- `Response/Typed/*.php` - Generated by `GenerateFiles::generateResponseClassContent()`
- `Model/Generated/*.php` - Generated from database schema
- `config.generated.php` - Generated configuration

**Pattern:** All generated files have comments indicating they are auto-generated and should not be edited.

**Consistency:** ✅ Good - Generated files clearly marked.

#### Summary of Key Inconsistencies

1. ⚠️ **Strict types spacing** - Mix of `declare(strict_types = 1);` and `declare(strict_types=1);`
2. ⚠️ **Missing strict types** - Some files don't declare strict types at all
3. ⚠️ **Response pattern evolution** - Two different response patterns coexist
4. ⚠️ **Controller method naming** - Mix of snake_case and camelCase
5. ⚠️ **Service naming** - Inconsistent use of "Standard" prefix
6. ✅ **Repository pattern** - Highly consistent
7. ✅ **Fake pattern** - Highly consistent
8. ✅ **Parameter validation** - Highly consistent
9. ✅ **Filesystem pattern** - Highly consistent
