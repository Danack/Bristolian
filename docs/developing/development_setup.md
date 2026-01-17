# Development Setup - Bristolian

This document explains how to set up and run the Bristolian project in a development environment.

## Prerequisites

- Docker and Docker Compose
- Git

## Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Bristolian
   ```

2. **Run the development environment**
   ```bash
   ./runLocal.sh
   ```

This script will:
- Start the MySQL database
- Run the installer to set up dependencies
- Install NPM packages
- Build and start all services
- Clean up when done

## Manual Setup

If you prefer to run services individually:

### 1. Start the Database
```bash
docker-compose up --build -d db
```

### 2. Run the Installer
```bash
docker-compose up --build installer --exit-code-from installer
```

### 3. Install NPM Dependencies
```bash
docker-compose up --build installer_npm
```

### 4. Start All Services
```bash
docker-compose up --build \
  varnish \
  caddy \
  db \
  php_fpm \
  php_fpm_debug \
  php_websocket \
  sass_dev_builder \
  js_builder \
  redis \
  --remove-orphans
```

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

## Debug Backend

The debug backend on port 8001 provides:
- PHPUnit test execution with debugging capabilities
- Xdebug integration for step-by-step debugging
- Enhanced error reporting and logging
- Development-specific PHP configuration

Access the debug backend at: http://local.bristolian.org:8001

## Running Tests

### PHP Unit Tests
```bash
# Run all PHP tests (recommended for regular testing)
docker exec -it bristolian-php_fpm-1 bash
sh runUnitTests.sh

# Run tests with debug backend (for debugging)
docker exec -it bristolian-php_fpm_debug-1 bash
sh runUnitTests.sh

# Run specific test group
sh runUnitTests.sh --group wip
```

**Note**: Always use `docker exec` without the `-it` flags to avoid TTY errors:
```bash
# Correct way to run tests (without -it flags)
docker exec bristolian-php_fpm-1 bash -c "sh runUnitTests.sh"
docker exec bristolian-php_fpm_debug-1 bash -c "sh runUnitTests.sh"
```

### JavaScript Tests
```bash
# Run Jest tests
docker exec -it bristolian-js_builder-1 bash
npm run test
```

## Code Quality Tools

### PHPStan Static Analysis
PHPStan is used for static analysis of PHP code to catch potential bugs and type issues.

```bash
# Run PHPStan analysis
docker exec -it bristolian-php_fpm_debug-1 bash
sh runPhpStan.sh
```

**Configuration**: `phpstan.neon`
- **Analysis Level**: 6 (strict)
- **Target Paths**: `src/` and `test/` directories
- **Bootstrap Files**: Configuration and factory files for dependency injection
- **Excluded Paths**: Generated files and specific problematic files

**Key Features**:
- Type checking and inference
- Dead code detection
- Unreachable code identification
- Method signature validation
- Property access validation

## Code Coverage

The project generates HTML code coverage reports for PHP unit tests. Coverage reports are generated in the `tmp/coverage/` directory.

### Viewing Coverage Reports
```bash
# Open the main coverage dashboard
open tmp/coverage/index.html

# Or view in browser
# http://local.bristolian.org/tmp/coverage/index.html
```

### Coverage Report Structure
- **Main Dashboard**: `tmp/coverage/index.html` - Overall coverage statistics
- **File-by-file Coverage**: Individual HTML files for each PHP class showing line-by-line coverage
- **Interactive Navigation**: Browse through different namespaces and components
- **Color-coded Coverage**: 
  - Green: Covered lines
  - Red: Uncovered lines
  - Yellow: Partially covered branches

### Coverage Configuration
Coverage is configured in PHPUnit with HTML output format:
- **Low threshold**: 50% (yellow warning)
- **High threshold**: 90% (green target)
- **Output directory**: `tmp/coverage/`

### Coverage Analysis
The HTML reports allow you to:
- Identify untested code paths
- See which specific lines need test coverage
- Understand coverage patterns across the codebase
- Navigate between related files and components

## Frontend Development

### Building Assets
```bash
# Development build
npm run js:build:dev
npm run sass:build:dev

# Production build
npm run js:build:prod
npm run sass:build:prod

# Watch mode
npm run js:build:dev:watch
npm run sass:build:watch
```

### Frontend Structure
- **TypeScript/React**: `/app/public/tsx`
- **Sass Styles**: `/app/public/scss`
- **Compiled JS**: `/app/public/js`
- **Compiled CSS**: `/app/public/css`

## Database Access

### Accessing from Inside Containers (Recommended)

All database commands should be run inside containers, following the container-based development approach.

**From php_fpm container:**
```bash
# Using docker exec (non-interactive)
docker exec bristolian-php_fpm-1 bash -c "mysql -h db -u bristolian -pp5ffrKSk4mPqN8vH bristolian"

# Or get an interactive shell first
docker exec -it bristolian-php_fpm-1 bash
# Then inside the container:
mysql -h db -u bristolian -pp5ffrKSk4mPqN8vH bristolian
```

**Run SQL queries:**
```bash
# List all tables
docker exec bristolian-php_fpm-1 bash -c "mysql -h db -u bristolian -pp5ffrKSk4mPqN8vH bristolian -e 'SHOW TABLES;'"

# Run any SQL query
docker exec bristolian-php_fpm-1 bash -c "mysql -h db -u bristolian -pp5ffrKSk4mPqN8vH bristolian -e 'SELECT COUNT(*) FROM room;'"
```

**Note**: Use `bash -c` without `-it` flags for non-interactive commands to avoid TTY errors.

### Accessing from Host Machine

If you need to access the database directly from your host machine (not recommended for regular development):

```bash
mysql -uroot -pPrJaGpnNSzSLW8p8 -h127.0.0.1 -P3306
```

### Database Credentials

**From Inside Containers:**
- **Host**: `db` (internal Docker network hostname)
- **Database**: bristolian
- **User**: bristolian
- **Password**: p5ffrKSk4mPqN8vH
- **Root Password**: PrJaGpnNSzSLW8p8

**From Host Machine:**
- **Host**: localhost (127.0.0.1)
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

## Service Implementation Naming Convention

The project uses a consistent naming convention for service implementations to clearly communicate when implementations differ and how they're selected.

### Naming Rules

#### 1. "Standard" Prefix
Use the `Standard` prefix when there's a **single, default implementation** that works for all environments.

**Examples:**
- `StandardBccTroFetcher` - fetches BCC TRO data
- `StandardChatMessageService` - handles chat messages
- `StandardWebPushService` - sends web push notifications
- `StandardAvatarImageStorage` - stores avatar images

These represent the normal/production implementation that's used across all environments.

#### 2. Environment-Specific Names
Use environment names (`Local`, `Prod`, `Dev`) when implementations **differ by environment** and are selected via factory functions.

**Examples:**
- `LocalDeployLogRenderer` / `ProdDeployLogRenderer` - different log sources for local vs production
- `DevEnvironmentMemoryWarning` / `ProdMemoryWarningCheck` - different memory monitoring strategies

**Factory Function Pattern:**
Factory functions in `factories.php` select the appropriate implementation based on `Config::isProductionEnv()`:

```php
function createDeployLogRenderer(Config $config): DeployLogRenderer
{
    if ($config->isProductionEnv()) {
        return new \Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer();
    }
    return new \Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer();
}
```

The naming clearly indicates which environment each implementation is for, making the code more maintainable.

#### 3. Vendor/Technology-Specific Names
When an implementation is tied to a specific technology or vendor, use the vendor/technology name directly.

**Examples:**
- `MailgunEmailClient` - Mailgun email service implementation
- `MailgunEmailReceiver` - Mailgun webhook receiver

These don't use "Standard" prefix because they're already specific to a technology.

#### 4. Fake Implementations
All fake implementations for testing use the `Fake` prefix:

**Examples:**
- `FakeAdminRepo` - fake admin repository for tests
- `FakeUserSession` - fake user session for tests
- `FakeMemoryWarningCheck` - fake memory check for tests

**Why This Pattern?**
- **Clarity**: The naming immediately tells you if there's one implementation or multiple environment-specific ones
- **Discoverability**: Easy to find environment-specific implementations
- **Maintainability**: Factory functions clearly show which implementation is used where
- **Consistency**: Matches common patterns (Standard Library, Standard Template Library)

## Additional Resources

- [Jest Testing](https://jestjs.io/)
- [Webpack Documentation](https://webpack.js.org/)
- [Sass Documentation](https://sass-lang.com/)
- [Docker Compose](https://docs.docker.com/compose/)
