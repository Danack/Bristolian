# Varnish Cache Invalidation

Automatic Varnish cache invalidation (banning) by tracking which database tables are read and written during each request.

## How it works

1. `PdoSimpleWithTableTracking` wraps `PdoSimple` and intercepts every DB call. It looks up the SQL query in a hard-coded mapping (`QueryTagMapping`) to determine which tables are involved, then records them via `TableAccessRecorder`.

2. `CacheTagMiddleware` runs after the handler. For 200 GET/HEAD responses, it sets an `X-Cache-Tags` header with the tables read (e.g. `table:room,table:bristol_stair_info`). For any request that wrote to tables, it sends a Varnish BAN per written table.

3. Varnish stores the `X-Cache-Tags` header on cached objects but strips it in `vcl_deliver` so it's never sent to clients. BAN requests match `obj.http.X-Cache-Tags ~ table:<name>` to invalidate all cached objects that used data from that table.

## Key files

- `src/Bristolian/Cache/QueryTagMapping.php` - Maps SQL queries to table tags (read/write)
- `src/Bristolian/Cache/TableAccessRecorder.php` - Interface for recording table access
- `src/Bristolian/Cache/RequestTableAccessRecorder.php` - Per-request implementation
- `src/Bristolian/Cache/TestTableAccessRecorder.php` - Test double for assertions
- `src/Bristolian/Cache/UnknownQueryHandler.php` - Interface for handling unmapped queries
- `src/Bristolian/Cache/ThrowOnUnknownQuery.php` - Throws in local dev / tests
- `src/Bristolian/Cache/RedisLogUnknownQuery.php` - Logs to Redis in production
- `src/Bristolian/PdoSimple/PdoSimpleWithTableTracking.php` - Decorator extending PdoSimple
- `src/Bristolian/Middleware/CacheTagMiddleware.php` - Sets X-Cache-Tags / sends BANs
- `src/Bristolian/Keys/UnknownCacheQueryKey.php` - Redis key class for unknown queries
- `src/functions.php` - `banVarnishByTag()` and `purgeVarnish()` functions
- `src/factories.php` - `createUnknownQueryHandler()` and `createPdoSimpleWithTableTracking()` factories
- `containers/varnish/config/default.vcl.php` - VCL with BAN method support
- `app/src/app_injection_params.php` - App DI wiring
- `api/src/api_injection_params.php` - API DI wiring
- `app/src/app_factories.php` - App middleware registration
- `api/src/api_factories.php` - API middleware registration
- `app/src/app_routes.php` - `/admin/unknown_cache_queries` route
- `src/Bristolian/AppController/Admin.php` - Admin page for unknown queries
