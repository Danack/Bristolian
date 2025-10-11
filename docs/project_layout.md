# Project Layout - Bristolian

This document outlines the main directories where code lives in the Bristolian project.

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

- **`/vendor`** - Composer dependencies
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

## Build Process

- **Frontend**: Webpack + TypeScript compilation
- **Backend**: PHP with Composer for dependency management
- **Database**: Migration-based schema management
- **Deployment**: Docker containerization

---

*This layout is based on analysis of the codebase structure. Please correct any inaccuracies.*
