# Default Test Scenarios and Worlds

## Default room world

For most tests that touch **Rooms**, the default world should have:

- **Two rooms**: "Housing" and "Off-topic"
- **Documents in each room**: Each room has some documents (room files)
- **Some documents with highlights**: Some of those documents have source links with `highlights_json` (e.g. PDF highlight regions)

This matches a typical “room with content” setup. Tests describe facts (e.g. “Housing has documents”) rather than creating everything from scratch.

## Session axis: logged in vs not

Two opposite setups that many tests need:

1. **With logged-in user**  
   The current request has a logged-in user (e.g. `testing@example.com`). Use when testing behaviour that depends on auth.

2. **Without logged-in user**  
   The current request is anonymous. Use when testing public/unauthenticated behaviour.

These are **orthogonal** to the room world. You can have:

- Default room world + logged-in user
- Default room world + anonymous user
- (Or custom worlds + either session state)

## How tests use this

- **`ensureStandardSetup()`**  
  Idempotent. Ensures the default room world exists (users, Housing, Off-topic, documents, some with highlights). Matches `createPlaceholders.sh` (which now uses "Off-topic" instead of "Misc").

- **`useLoggedInUser()`**  
  Configures the test injector so the “current” user session is logged in as the standard test user. Call in `setUp()` or at the start of a test when you need an authenticated user.

- **`useAnonymousUser()`**  
  Configures the test injector so the “current” user session is not logged in. Use for tests that require an anonymous session.

Tests that only need the default room world (e.g. repo tests) call `ensureStandardSetup()` and use `getHousingRoom()`, `getOffTopicRoom()`, etc. They do **not** need to call `useLoggedInUser` / `useAnonymousUser` unless the code under test depends on session.

DB tests that use the default world should use `DbTransactionIsolation` and clear only test-specific tables (e.g. `chat_message`) in `dbTransactionClearTables()`, so the standard setup (users, rooms, documents) is created inside the transaction and rolled back per test.

## Abstract repo fixtures: required scenario data

Repo tests often extend an **abstract fixture** that defines shared behaviour for both Pdo and Fake implementations. Those fixture tests need existing entities (e.g. a room id, a file id). The fixture must not use hardcoded IDs or assume a schema—that would leak infrastructure and break on one implementation (e.g. Pdo FKs) while working on another (Fake).

**Pattern: fixture declares what it needs; concrete tests provide it.**

- The abstract fixture defines **abstract methods** that represent the scenario data it needs, e.g. `getValidRoomId(): string`, `getValidFileId(): string`. The contract is: “an id that is valid in this implementation’s world” (room exists, file exists and can be attached to a room).
- Fixture tests call these methods instead of literals. They describe behaviour (“add file to room, then get details”) without knowing how the room or file came to exist.
- **Pdo** concrete test implements the methods using the default world and/or TestPlaceholders: e.g. `ensureStandardSetup()`, then `getHousingRoom()->id` and a file created via `roomFileObjectInfoRepo` (or `createTestFile`).
- **Fake** concrete test implements the methods by returning constants (e.g. `'room_456'`, `'file_123'`); the Fake has no FKs to satisfy.

No single “world” is mandated. The fixture stays schema-agnostic; each implementation supplies the required scenario in the way that fits its world (standard setup, createTest* helpers, or literal ids).

## Summary

| Concept        | Purpose                                                        |
|----------------|----------------------------------------------------------------|
| Default world  | Housing + Off-topic, with documents, some with highlights    |
| Session axis   | Logged-in vs anonymous; orthogonal to the room world           |
| Fixture scenario data | Abstract methods for “valid X id”; concrete tests provide them |
