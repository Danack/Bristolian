# Behat Testing Notes

## Test Organization

Tests are organized by feature area:
- `features/basic.feature` - Basic site functionality tests
- `features/bristol_stairs.feature` - Bristol Stairs map feature tests
- `features/chat/` - Chat room feature tests

## Running Tests

```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"
```

To run specific feature files:
```bash
docker exec bristolian-php_fpm-1 bash -c "vendor/bin/behat features/chat/"
```

## Excluded Test Cases

The following test cases are **not** covered by Behat tests for specific reasons:

### Uploading without authentication
- **Reason**: Behat tests are solely to test the UI that is there. There is already a test (`Upload button is not visible when not logged in`) that verifies the upload button is not shown when the user is not logged in, which is the appropriate UI-level test for this functionality.

### Large file uploads
- **Reason**: File size validation and large file handling should be tested with PHPUnit unit tests rather than Behat browser tests. Behat tests focus on UI functionality, not edge cases that require specific file size testing.

## Chat Feature Tests

The `features/chat/share_file_link.feature` tests the Share button functionality:
- Verifies Share button visibility based on login state
- Tests that clicking Share inserts a markdown link into the message input
- Tests cursor position insertion (text inserted at cursor, not appended)

**Note**: These tests are conditional - if no files exist in the room, the file-dependent assertions are skipped gracefully.


