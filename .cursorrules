# Container-Based Development Instructions

## Important: All Commands Must Run Inside Containers

This project uses Docker containers for development. **All commands, scripts, and tools must be executed inside the appropriate container**, not on the host machine.

## Running Commands Inside Containers

### PHP Commands (php_fpm container)

All PHP-related commands (tests, Behat, PHPUnit, Composer, etc.) must run inside the `php_fpm` container:

```bash
# Run PHP unit tests
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"

# Run Behat browser tests
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"

# Run PHPStan
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh"

# Run Composer commands
docker exec bristolian-php_fpm-1 bash -c "composer install"

# Run PHP CLI commands
docker exec bristolian-php_fpm-1 bash -c "php cli.php <command>"
```

**Note**: Use `bash -c` without `-it` flags to avoid TTY errors when running non-interactive commands.

### Interactive Shell Access

To get an interactive shell inside a container:

```bash
# PHP container
docker exec -it bristolian-php_fpm-1 bash

# Then run commands directly:
sh runUnitTests.sh
sh runBehat.sh
composer install
```

### Node.js/JavaScript Commands (js_builder container)

All Node.js, npm, and JavaScript-related commands must run inside the `js_builder` container:

```bash
# Run Jest tests
docker exec bristolian-js_builder-1 bash -c "npm run test"

# Run npm commands
docker exec bristolian-js_builder-1 bash -c "npm install"
docker exec bristolian-js_builder-1 bash -c "npm run build"

# Check TypeScript compilation logs after editing TypeScript files
docker logs bristolian-js_builder-1 --tail 100
```

### Script Files

Script files in the project root (e.g., `runBehat.sh`, `runUnitTests.sh`) are designed to be executed **inside** the container, not on the host. They contain the actual commands without docker-compose exec calls.

## Container Names

Common container names (may vary based on Docker Compose project name):
- `bristolian-php_fpm-1` - PHP application container
- `bristolian-js_builder-1` - Node.js/JavaScript build container
- `bristolian-php_fpm_debug-1` - PHP debug container (with Xdebug)

## Key Points

1. **Never run PHP/Composer commands on the host** - they must run inside `php_fpm` container
2. **Never run npm/node commands on the host** - they must run inside `js_builder` container
3. **Script files don't call docker-compose exec** - they're meant to be executed inside containers
4. **Use `docker exec` from the host** to run commands inside containers
5. **Use `bash -c` for non-interactive commands** to avoid TTY issues
6. **Check js_builder logs after editing TypeScript files** - Use `docker logs bristolian-js_builder-1 --tail 100` to verify TypeScript compilation succeeded (look for "webpack compiled" message)
7. Frontend and backend are deployed together; avoid legacy/fallback response handling—prefer updating both sides in tandem.

## Example Workflow

```bash
# From host machine:
# 1. Start containers
./runLocal.sh

# 2. Run tests inside container
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"

# 3. Or get interactive shell and run commands
docker exec -it bristolian-php_fpm-1 bash
# Inside container:
sh runBehat.sh
```

