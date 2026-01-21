We are finishing a piece of work. All the code quality tools should be run. Any problems reported should be fixed.

## Code Quality Tools

This project uses several code quality tools that must be run inside the `php_fpm` Docker container. All commands should be executed using `docker exec bristolian-php_fpm-1 bash -c "..."`.

### 1. PHPStan - Static Analysis

PHPStan performs static analysis to find bugs and type errors in PHP code.

**Command:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh"
```

This runs PHPStan with `--no-progress` by default to keep output clean.

**Additional flags:** You can pass extra flags to PHPStan:
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh --error-format=table"
```

**What it does:** Analyzes all PHP code in `src/` and `test/` directories using the configuration in `phpstan.neon`.

### 2. PHPUnit - Unit Tests

PHPUnit runs unit tests to verify code functionality.

**Command:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"
```

**Additional flags:** You can pass extra flags to PHPUnit:
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh --filter TestClassName"
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh --stop-on-failure"
```

**What it does:** Runs all PHPUnit tests defined in `test/` directory using the configuration in `test/phpunit.xml`.

### 3. CodeSniffer - Code Style Checking

CodeSniffer checks and fixes code style according to the project's coding standards.

**Command:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runCodeSniffer.sh"
```

**What it does:**
- First runs `phpcbf` (PHP Code Beautifier and Fixer) to automatically fix code style issues
- Then runs `phpcs` (PHP CodeSniffer) to check for remaining style violations
- Checks both `test/` and `src/` directories using separate standards files

**Note:** This script will automatically fix many style issues, but may report remaining violations that need manual fixes.

### 4. Behat - Browser/Acceptance Tests

Behat runs browser-based acceptance tests using Gherkin feature files.

**Command:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"
```

**Run specific features:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh features/chat"
```

**What it does:** Runs Behat tests defined in `features/` directory using the configuration in `behat.yml`. Tests run in a browser environment and verify end-to-end functionality.

### 5. Run All Tests (Recommended)

The `runAllTests.sh` script runs multiple code quality tools in sequence:

**Command:**
```bash
docker exec bristolian-php_fpm-1 bash -c "sh runAllTests.sh"
```

**What it runs:**
1. CodeSniffer (code style checking)
2. PHPStan (static analysis)
3. PHPUnit (unit tests)
4. Behat is commented out by default (uncomment if needed)

**Note:** This is the recommended command to run before finalizing work, as it checks code style, static analysis, and unit tests all at once.

## Recommended Workflow

Before finalizing work, run all code quality tools:

```bash
# Option 1: Run all tools at once (recommended)
docker exec bristolian-php_fpm-1 bash -c "sh runAllTests.sh"

# Option 2: Run tools individually
docker exec bristolian-php_fpm-1 bash -c "sh runCodeSniffer.sh"
docker exec bristolian-php_fpm-1 bash -c "sh runPhpStan.sh"
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"
docker exec bristolian-php_fpm-1 bash -c "sh runBehat.sh"  # Optional, if browser tests are needed
```

**Important:** All commands must be run inside the `php_fpm` container. Never run these scripts directly on the host machine.
