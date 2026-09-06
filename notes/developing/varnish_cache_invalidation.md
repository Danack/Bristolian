# Varnish Cache Invalidation

Automatic Varnish cache invalidation (banning) by tracking which database tables are read and written during each request.

## How it works

1. `PdoSimpleWithTableTracking` extends `PdoSimple` and intercepts every DB call. It looks up the SQL query in a hard-coded mapping (`QueryTagMapping`) to determine which tables are involved, then records them via `TableAccessRecorder`.

2. `CacheTagMiddleware` runs after the handler. For 200 GET/HEAD responses, it sets an `X-Cache-Tags` header with the tables read (e.g. `table:room,table:bristol_stair_info`). For any request that wrote to tables, it sends a Varnish BAN per written table.

3. Varnish stores the `X-Cache-Tags` header on cached objects but strips it in `vcl_deliver` so it's never sent to clients. BAN requests match `obj.http.X-Cache-Tags ~ table:<name>` to invalidate all cached objects that used data from that table.

## Whitespace normalization

The lookup in `PdoSimpleWithTableTracking` normalizes all whitespace before matching: it collapses all runs of whitespace (newlines, tabs, multiple spaces) into a single space and trims. This means mapping entries in `QueryTagMapping` do not need to match the exact indentation of the SQL in repo classes.

## Adding new queries

When a new repo method introduces a SQL query, it must be added to the mapping. In local dev and tests, `ThrowOnUnknownQuery` will throw a `RuntimeException` with instructions on how to fix it.

- **Static queries**: Add an entry to `QueryTagMapping::getExactMappings()`. Use the generated Database constant classes (e.g. `Bristolian\Database\table_name::SELECT`) where possible.
- **Dynamic queries** (e.g. variable IN clauses): Add a regex pattern to `QueryTagMapping::getPatternMappings()`.
- **Production**: Unmapped queries are logged to Redis via `RedisLogUnknownQuery`. The admin page at `/admin/unknown_cache_queries` shows these.

## Scope

The decorator is wired in app (`app/src/app_injection_params.php`), API (`api/src/api_injection_params.php`), and tests (`test/test_injection_params.php`). CLI commands do **not** use the decorator — they use plain `PdoSimple`.

Tests for the base `PdoSimple` class (`PdoSimpleTest`, `PdoSimpleDateTimeFunctionsTest`) re-delegate `PdoSimple::class` in their `setup()` to construct a plain `PdoSimple`, bypassing the tracking decorator, since they exercise ad-hoc SQL not in the mapping.

## Key files

### Core

- `src/Bristolian/PdoSimple/PdoSimpleWithTableTracking.php` — Decorator extending PdoSimple
- `src/Bristolian/Cache/QueryTagMapping.php` — Maps SQL queries to table tags (read/write)
- `src/Bristolian/Middleware/CacheTagMiddleware.php` — Sets X-Cache-Tags / sends BANs
- `src/Bristolian/Cache/TableAccessRecorder.php` — Interface for recording table access
- `src/Bristolian/Cache/RequestTableAccessRecorder.php` — Per-request implementation

### Unknown query handling

- `src/Bristolian/Cache/UnknownQueryHandler.php` — Interface for handling unmapped queries
- `src/Bristolian/Cache/ThrowOnUnknownQuery.php` — Throws in local dev / tests
- `src/Bristolian/Cache/RedisLogUnknownQuery.php` — Logs to Redis in production
- `src/Bristolian/Keys/UnknownCacheQueryKey.php` — Redis key class for unknown queries
- `src/Bristolian/AppController/Admin.php` — Admin page for unknown queries
- `app/src/app_routes.php` — `/admin/unknown_cache_queries` route

### Wiring

- `src/functions.php` — `banVarnishByTag()` and `purgeVarnish()` functions
- `src/factories.php` — `createUnknownQueryHandler()` and `createPdoSimpleWithTableTracking()` factories
- `app/src/app_injection_params.php` — App DI wiring
- `api/src/api_injection_params.php` — API DI wiring
- `test/test_injection_params.php` — Test DI wiring
- `app/src/app_factories.php` — App middleware registration
- `api/src/api_factories.php` — API middleware registration
- `containers/varnish/config/default.vcl.php` — VCL with BAN method support

### Tests

- `test/BristolianTest/PdoSimple/PdoSimpleWithTableTrackingTest.php`
- `test/BristolianTest/Middleware/CacheTagMiddlewareTest.php`
- `test/BristolianTest/Cache/RequestTableAccessRecorderTest.php`
- `test/BristolianTest/Cache/TestTableAccessRecorderTest.php`
- `test/BristolianTest/Cache/ThrowOnUnknownQueryTest.php`
- `test/BristolianTest/Cache/RedisLogUnknownQueryTest.php`
- `test/BristolianTest/Cache/QueryTagMappingTest.php`
- `test/BristolianTest/Keys/UnknownCacheQueryKeyTest.php`
- `src/Bristolian/Cache/TestTableAccessRecorder.php` — Test double for assertions
