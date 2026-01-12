# Creating Webpages in Bristolian

This document explains how to create a new webpage in the Bristolian project, from defining routes to connecting React/Preact components.

## Overview

The Bristolian project uses a hybrid architecture:
- **Backend**: PHP controllers render HTML and pass initial data
- **Frontend**: React/Preact components provide interactivity
- **Integration**: A custom "widgety" system connects PHP-rendered HTML to React/Preact components

## Architecture Flow

```
Route Definition (app_routes.php)
    ↓
PHP Controller Method (AppController namespace)
    ↓
Render HTML with Widget Container
    ↓
Widget Registration (bootstrap.tsx)
    ↓
React/Preact Component (*.tsx)
    ↓
Styling (*.scss)
```

## Step-by-Step Guide

### 1. Define the Route

Routes are defined in `app/src/app_routes.php`. Each route is an array containing:
- Path to match
- HTTP method (GET, POST, etc.)
- Controller class and method (as a string)

**Example:**
```php
['/tools/bristol_stairs', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page'],
['/tools/bristol_stairs/{stair_id:.*}', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page_stair_selected'],
```

**Route Parameters:**
- Use `{param_name}` for required parameters
- Use `{param_name:.*}` for parameters that can contain special characters
- Parameters are automatically passed to the controller method

### 2. Create the PHP Controller

Controllers live in `src/Bristolian/AppController/`. Each controller method:
- Receives route parameters and dependencies via dependency injection
- Prepares data for the frontend
- Renders HTML that includes a widget container

**Example Controller Method:**
```php
public function stairs_page(
    ExtraAssets $extraAssets, 
    BristolStairsRepo $bristolStairsRepo
): string {
    return $this->render_stairs_page(
        $extraAssets,
        $bristolStairsRepo,
        null
    );
}

private function render_stairs_page(
    ExtraAssets $extraAssets,
    BristolStairsRepo $bristolStairsRepo,
    BristolStairInfo $selected_stair = null
): string {
    // Add any external CSS/JS dependencies
    $extraAssets->addCSS("/css/leaflet/leaflet.1.7.1.css");
    $extraAssets->addJS("/js/leaflet/leaflet.1.7.1.js");

    // Prepare data for the React/Preact component
    $data = [
        'selected_stair_info' => $selected_stair
    ];

    // Convert data to JSON and escape for HTML
    [$error, $values] = convertToValue($data);
    $widget_json = json_encode_safe($values);
    $widget_data = htmlspecialchars($widget_json);

    // Render HTML with widget container
    $content = "<h1>A map of Bristol Stairs</h1>";
    $content .= <<< HTML
<div class="bristol_stairs_container">
  <div class="bristol_stairs_map" id="bristol_stairs_map"></div>
  <div class="bristol_stairs_panel" data-widgety_json="$widget_data"></div>
</div>
HTML;

    return $content;
}
```

**Key Points:**
- The `class` attribute (e.g., `bristol_stairs_panel`) is used to identify the widget
- The `data-widgety_json` attribute contains the initial props for the React/Preact component
- Data in the widget JSON must be escaped with `htmlspecialchars()` for the attribute value
- Use `convertToValue()` to convert PHP objects to arrays suitable for JSON
- Use `json_encode_safe()` to ensure proper JSON encoding
- For other HTML output, use `esprintf()` with appropriate placeholder prefixes

### 3. Create the TypeScript/TSX Component

React/Preact components live in `app/public/tsx/`. Each component:
- Receives initial props from the `data-widgety_json` attribute
- Manages its own state
- Can communicate with other components via messages
- Can call API endpoints for dynamic updates

**Example Component Structure:**
```typescript
import {h, Component} from "preact";

// Define the props interface - matches the data from PHP
export interface BristolStairsPanelProps {
    selected_stair_info: BristolStairInfo|null;
}

// Define the state interface - internal component state
interface BristolStairsPanelState {
    error: string|null,
    selected_stair_info: BristolStairInfo|null,
    changes_made: boolean,
}

// Component class
export class BristolStairsPanel extends Component<BristolStairsPanelProps, BristolStairsPanelState> {

    constructor(props: BristolStairsPanelProps) {
        super(props);
        this.state = {
            error: null,
            selected_stair_info: props.selected_stair_info,
            changes_made: false,
        };
    }

    componentDidMount() {
        // Register message listeners, set up subscriptions, etc.
    }

    componentWillUnmount() {
        // Clean up listeners, subscriptions, etc.
    }

    render(props: BristolStairsPanelProps, state: BristolStairsPanelState) {
        return <div class='bristol_stairs_panel_react'>
            {/* Component JSX */}
        </div>;
    }
}
```

**Key Points:**
- Props interface must match the data structure from PHP
- Use TypeScript interfaces for type safety
- Components are registered by class name in `bootstrap.tsx`
- The outer container div's class name should match the PHP class name with `_react` suffix

### 4. Register the Component in Bootstrap

Open `app/public/tsx/bootstrap.tsx` and add your component to the `panels` array:

```typescript
import { BristolStairsPanel } from "./BristolStairsPanel";

let panels: WidgetClassBinding[] = [
    {
        class: 'bristol_stairs_panel',  // Must match the class in PHP HTML
        component: BristolStairsPanel   // The React/Preact component
    },
    // ... other panels
];
```

**Key Points:**
- The `class` value must exactly match the class attribute in your PHP-rendered HTML
- Import the component at the top of the file
- The widgety system will automatically find all elements with that class and render the component

### 5. Create the SCSS Styling

Styles live in `app/public/scss/`. Create a new SCSS file for your component:

**Example: `bristol_stairs.scss`**
```scss
.bristol_stairs_container {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #aaa;

    .bristol_stairs_panel {
        width: 100%;
        display: flex;
        flex-direction: column;
        
        img {
            max-width: 90vw;
            max-height: 40vh;
        }
    }

    // Responsive design
    @media (orientation: landscape) {
        flex-direction: row;
        
        .bristol_stairs_panel {
            width: 50%;
        }
    }
}
```

**Key Points:**
- Name the file after your component (e.g., `component_name.scss`)
- Use nested selectors to scope styles to your component
- Consider mobile-first responsive design
- The SCSS files are compiled and bundled by the build system

### Styling Guidelines

**DO NOT use inline styles unless explicitly required.** Always use SCSS classes for styling components.

**Why:**
- Inline styles cannot be overridden by stylesheets
- They make the code harder to maintain and update
- They mix styling concerns with component logic
- They cannot use CSS preprocessor features (variables, mixins, etc.)

**Bad Example:**
```typescript
<div style="display: flex; flex-direction: column; align-items: flex-end;">
    <span style="font-size: 0.75rem;">Content</span>
</div>
```

**Good Example:**
```typescript
<div className="user-signature">
    <span className="user-name">Content</span>
</div>
```

```scss
.user-signature {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    
    .user-name {
        font-size: 0.75rem;
    }
}
```

**When inline styles are acceptable:**
- Dynamic values that cannot be predetermined (e.g., `width: ${percentage}%`)
- Styles calculated at runtime based on user data or API responses
- Third-party library integration that requires inline styles

## Database Operations

### Using Database Helper Classes

The project uses auto-generated helper classes for database operations. These are located in `src/Bristolian/Database/` and provide consistent SQL templates for each table.

**DO NOT write raw SQL directly in repository classes.** Instead, use the Database helper classes.

**Pattern:**
```php
use Bristolian\Database\user_display_name;

// SELECT - fetch single object or null
$sql = user_display_name::SELECT;
$sql .= " WHERE user_id = :user_id ORDER BY version DESC LIMIT 1";

$result = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
    $sql,
    [':user_id' => $user_id],
    UserDisplayName::class
);

// SELECT - fetch multiple objects
$sql = user_display_name::SELECT;
$sql .= " WHERE user_id = :user_id ORDER BY version DESC";

$results = $this->pdo_simple->fetchAllAsObjectConstructor(
    $sql,
    [':user_id' => $user_id],
    UserDisplayName::class
);

// INSERT operations
$sql = user_display_name::INSERT;
$params = [
    ':user_id' => $user_id,
    ':display_name' => $display_name,
    ':version' => $next_version
];
$insert_id = $this->pdo_simple->insert($sql, $params);

// UPDATE operations
$sql = user_display_name::UPDATE;
// Add WHERE clause if needed
```

**Important - Fetch Directly Into Objects:**

Always use `fetchOneAsObjectOrNullConstructor()` or `fetchAllAsObjectConstructor()` to fetch data directly into model objects. These methods:
- Call the object's constructor with the fetched data
- Automatically convert datetime columns to `\DateTimeInterface`
- Provide type safety

**Do NOT** fetch as arrays (`fetchOneAsDataOrNull()`) and manually construct objects unless you have a specific reason. Let PDOSimple handle object construction.

**Key Points:**
- Each table has a corresponding helper class in `src/Bristolian/Database/`
- Helper classes provide `INSERT`, `SELECT`, and `UPDATE` constants
- Append WHERE, ORDER BY, LIMIT clauses to SELECT statements as needed
- These files are auto-generated - **DO NOT edit them manually**
- **Always fetch directly into objects** - use `fetchOneAsObjectOrNullConstructor()` or `fetchAllAsObjectConstructor()`
- Avoid fetching as arrays and manually constructing objects - let PDOSimple handle it

**Generating Database Helper Classes:**

After creating a new migration:
1. Run the migration to create the table
2. Bounce the Docker containers (this regenerates all Database helper classes)
3. The helper class will be automatically created for your new table

### Generating TypeScript Constants

When you add validation constants to parameter type classes (like length limits), you should:

1. Add them to the constants array in `src/Bristolian/CliController/GenerateFiles::generateJavaScriptConstants()`
2. Run the generation command in a PHP container:
   ```bash
   docker exec bristolian-php_fpm-1 bash -c "php cli.php generate:javascript_constants"
   ```
3. The constants will be available in `app/public/tsx/generated/constants.tsx`
4. Import and use them in your TypeScript components

**Example:**
```php
// In PHP: src/Bristolian/Parameters/PropertyType/DisplayName.php
class DisplayName implements HasInputType
{
    const MINIMUM_DISPLAY_NAME_LENGTH = 4;
    const MAXIMUM_DISPLAY_NAME_LENGTH = 32;
}

// In generator: src/Bristolian/CliController/GenerateFiles.php
$constants = [
    'MINIMUM_DISPLAY_NAME_LENGTH' => \Bristolian\Parameters\PropertyType\DisplayName::MINIMUM_DISPLAY_NAME_LENGTH,
    'MAXIMUM_DISPLAY_NAME_LENGTH' => \Bristolian\Parameters\PropertyType\DisplayName::MAXIMUM_DISPLAY_NAME_LENGTH,
];
```

```typescript
// In TypeScript: Import generated constants
import {
    MINIMUM_DISPLAY_NAME_LENGTH,
    MAXIMUM_DISPLAY_NAME_LENGTH
} from "./generated/constants";

// Use in component
<input 
    minLength={MINIMUM_DISPLAY_NAME_LENGTH}
    maxLength={MAXIMUM_DISPLAY_NAME_LENGTH}
/>
```

**Why share constants?**
- Ensures validation limits match between frontend and backend
- Single source of truth for validation rules
- Changes to limits automatically propagate to both sides

## Data Flow

### PHP to TypeScript

Data flows from PHP to TypeScript via the `data-widgety_json` attribute:

```php
// PHP side
$data = ['user_id' => 123, 'username' => 'john'];
[$error, $values] = convertToValue($data);
$widget_json = json_encode_safe($values);
$widget_data = htmlspecialchars($widget_json);
```

```html
<div class="my_panel" data-widgety_json="$widget_data"></div>
```

```typescript
// TypeScript side
export interface MyPanelProps {
    user_id: number;
    username: string;
}

export class MyPanel extends Component<MyPanelProps, MyPanelState> {
    constructor(props: MyPanelProps) {
        super(props);
        // props.user_id and props.username are available
    }
}
```

### TypeScript to PHP (API Calls)

For dynamic updates, TypeScript components call PHP API endpoints:

```typescript
// TypeScript side
const endpoint = `/api/bristol_stairs_update/${stair_info.id}`;
const form_data = new FormData();
form_data.append("description", stair_info.description);

call_api(endpoint, form_data)
    .then((data: any) => this.processData(data))
    .catch((err: any) => this.processError(err));
```

```php
// PHP side - define the route
['/api/bristol_stairs_update/{id:.*}', 'POST', 'Bristolian\AppController\BristolStairs::update_stairs_info'],

// PHP controller method - automatically protected because UserSession is in signature
public function update_stairs_info(
    UserSession $userSession,  // Authentication required!
    BristolStairsInfoParams $stairs_info_params
): JsonResponse {
    // This only executes if user is logged in
    $this->repo->updateStairInfo($stairs_info_params);
    return new JsonResponse(['success' => true]);
}
```

**Note:** Adding `UserSession` to the method signature automatically requires authentication. If the user is not logged in, an exception is thrown before this code runs.

## Inter-Component Communication

Components can communicate using the message system:

```typescript
import {registerMessageListener, sendMessage} from "./message/message";

// Sending a message
sendMessage("STAIR_INFO_UPDATED", {stair_info: stair_info});

// Receiving a message
componentDidMount() {
    this.message_listener = registerMessageListener(
        "STAIR_INFO_UPDATED",
        (data) => this.handleStairUpdated(data)
    );
}

componentWillUnmount() {
    unregisterListener(this.message_listener);
}
```

## Authentication and User Sessions

The project uses session-based authentication. Here's how to check if a user is logged in and conditionally show UI based on authentication status.

### Backend: Requiring Authentication

**The Simple Way - Automatic Protection:**

To require authentication for a controller method, simply add `UserSession` to the method signature:

```php
use Bristolian\Session\UserSession;
use SlimDispatcher\Response\JsonResponse;

public function update_stairs_info(
    UserSession $userSession,
    BristolStairsRepo $bristolStairsRepo,
    BristolStairsInfoParams $stairs_info_params
): JsonResponse {
    // This code will ONLY execute if user is logged in
    // If not logged in, an UnauthorisedException is thrown automatically
    
    $bristolStairsRepo->updateStairInfo($stairs_info_params);
    return new JsonResponse(['success' => true]);
}
```

**How it works:**
- When the dependency injector sees `UserSession` (or `AppSession`) in the method signature, it calls a factory function
- That factory checks if the user is logged in
- If not logged in: throws `\Bristolian\Exception\UnauthorisedException` before your code runs
- If logged in: injects the `AppSession` instance into your method

**Getting User Information:**
```php
public function myProtectedMethod(UserSession $userSession): JsonResponse {
    $userId = $userSession->getUserId();
    $username = $userSession->getUsername();
    
    // Your protected logic here
    return new JsonResponse(['user_id' => $userId]);
}
```

### Backend: Optional Authentication

If you need to check authentication but not require it, inject `AppSessionManager`:

```php
use Bristolian\Session\AppSessionManager;
use SlimDispatcher\Response\RedirectResponse;

public function showLoginPage(
    AppSessionManager $appSessionManager
): string|RedirectResponse {
    $appSession = $appSessionManager->getCurrentAppSession();
    
    // If $appSession is truthy, the user is logged in
    if ($appSession) {
        return new RedirectResponse('/?message=You are logged in');
    }
    
    // User is not logged in - show login form
    return $this->renderLoginForm();
}
```

**Key Points:**
- `getCurrentAppSession()` returns `AppSession|null`
- If it returns an `AppSession` instance, the user is logged in
- If it returns `null`, the user is not logged in
- Use this pattern when you want to show different content based on login status

### Additional Protection: POST Request Middleware

The project also has middleware that automatically protects all POST requests:
- All POST endpoints require authentication by default
- Exceptions are configured in `PermissionsCheckHtmlMiddleware::$allowed_paths`
- If you need an unauthenticated POST endpoint, add it to the allowed paths list

This means:
1. **For authenticated-only endpoints**: Just add `UserSession` to your method signature
2. **For optional authentication**: Use `AppSessionManager` and check manually
3. **POST requests**: Already protected by middleware (no extra code needed)

### Frontend: Checking Authentication

On the frontend, use the `use_logged_in()` hook from the store:

```typescript
import {use_logged_in} from "./store";

export class MyPanel extends Component<MyPanelProps, MyPanelState> {
    render(props: MyPanelProps, state: MyPanelState) {
        const logged_in = use_logged_in();
        
        if (logged_in) {
            return <div>
                <button onClick={this.handleEdit}>Edit</button>
            </div>;
        } else {
            return <div>
                <span>Please log in to edit</span>
            </div>;
        }
    }
}
```

**How it works:**
- The `use_logged_in()` hook returns the current login status
- It automatically updates when the login status changes
- Login status is fetched via the `/api/user/login_status` endpoint
- The `LoginStatusPanel` component manages fetching and updating this state

### React Pattern: Default Sections with Conditional Updates

A useful pattern for React/Preact components is to initialize sections with default/empty content, then conditionally replace them with actual content based on state or props. This pattern is used in `ChatBottomPanel.tsx`:

```typescript
export function ChatBottomPanel(props: ChatBottomPanelProps) {
  // Initialize sections with default/empty content
  let avatar_section = <div className="avatar-section"></div>;
  let interactive_section = <div>
    <span>You must be <a href="/login">logged in</a> to talk.</span>
  </div>;
  let replying_section = <div className="reply-indicator-top"></div>;

  // Conditionally update sections based on state/props
  if (logged_in === true) {
    interactive_section = <div>
      <div className="input-row">
        <textarea
          className="message-input"
          placeholder={props.replyingToMessage ? "Reply..." : "Write a message..."}
          onInput={handleInputChange}
          value={messageToSend}>
        </textarea>
        <button className="send-btn" onClick={handleMessageSend}>Send</button>
        <button className="upload-btn">Upload</button>
      </div>
    </div>;
  }

  if (user_info.user_id && user_info.avatar_image_id) {
    avatar_section = <div className="avatar-section">
      <img
        className="avatar"
        src={`/users/${user_info.user_id}/avatar`}
        alt="User avatar"
      />;
    </div>;
  }

  if (props.replyingToMessage) {
    replying_section = <div className="reply-indicator-top">
      <span>Replying to message {props.replyingToMessage.id}</span>
      <button className="cancel-reply-btn" onClick={props.onCancelReply}>×</button>
    </div>;
  }

  return (
    <div className="bottom-bar">
      {avatar_section}
      <div className="interactive_section">
        {replying_section}
        {interactive_section}
      </div>
      <div className="right-section">
        Please report bugs to Danack
      </div>
    </div>
  );
}
```

**Benefits of this pattern:**
- **Readability**: The final JSX structure is clear and matches the visual layout
- **Maintainability**: Each section's logic is grouped together at the top
- **Performance**: Avoids unnecessary conditional rendering in the JSX
- **Consistency**: Makes it easy to see all possible states for each section

**When to use:**
- Components with multiple conditional sections
- When you want to avoid deeply nested conditional JSX
- For components where the structure is more important than the conditional logic

**Conditional Rendering Example:**
```typescript
render(props: MyPanelProps, state: MyPanelState) {
    const logged_in = use_logged_in();
    
    let edit_button = null;
    if (logged_in === true) {
        edit_button = <button onClick={this.startEditing}>Edit</button>;
    }
    
    return <div class='my_panel_react'>
        {this.state.content}
        {edit_button}
    </div>;
}
```

### API Endpoints for Authentication

**Get login status:**
```
GET /api/user/login_status
Returns: { "logged_in": true|false }
```

**Login:**
```
POST /login
Body: username, password
Redirects on success
```

**Logout:**
```
GET /logout
Clears session and redirects
```

## Dependency Injection Configuration

The project uses separate dependency injection (DI) configuration for the main app and the API:

### App DI Configuration (`app/src/app_injection_params.php`)
Used for web pages and user-facing features.

### API DI Configuration (`api/src/api_injection_params.php`)
Used for API endpoints. Has a separate, smaller set of dependencies.

**Important:** When you create a new repository, service, or dependency:
1. Add it to `app/src/app_injection_params.php` in the `$aliases` array
2. If your feature is used by API endpoints, ALSO add it to `api/src/api_injection_params.php`

**Example:**
```php
// In both app_injection_params.php AND api_injection_params.php:
\Bristolian\Repo\UserProfileRepo\UserProfileRepo::class =>
    \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::class,
```

**Why separate configs?**
- The API runs with minimal dependencies for better performance
- Web pages may need additional services (rendering, assets, etc.)
- Keeps API lightweight and focused

**Common mistake:** Forgetting to add repository/service to API config when API endpoints use it. This results in "Injection definition required" errors.

## Best Practices

### 1. Naming Conventions
- **PHP class in HTML**: `snake_case_panel` (e.g., `bristol_stairs_panel`)
- **TypeScript component**: `PascalCase` (e.g., `BristolStairsPanel`)
- **SCSS file**: `snake_case.scss` (e.g., `bristol_stairs.scss`)
- **Route paths**: `kebab-case` (e.g., `/bristol-stairs`)

### 2. Component Structure
- Keep components focused on a single responsibility
- Use TypeScript interfaces for all props and state
- Clean up listeners in `componentWillUnmount()`
- Extract complex logic into separate functions or utility modules

### 2.1. Event Handlers for Input Fields

**Use `onInput` instead of `onChange` for real-time input handling in Preact.**

In Preact, `onChange` may only fire when an input field loses focus, not on every keystroke. For real-time input handling (e.g., search-as-you-type, live validation), use `onInput` which fires on every keystroke.

**Example:**
```typescript
// Good - fires on every keystroke
<input
    type="text"
    value={this.state.searchQuery}
    onInput={(e) => this.handleSearchQueryChange(e)}
    placeholder="Search..."
/>

// Avoid - may only fire on blur
<input
    type="text"
    value={this.state.searchQuery}
    onChange={(e) => this.handleSearchQueryChange(e)}  // May not fire while typing
    placeholder="Search..."
/>
```

**When to use each:**
- **`onInput`**: For real-time updates (search, live validation, character counting)
- **`onChange`**: For form fields where you only need to know when the user finishes editing (though `onInput` works for this too)

### 3. Styling
- Scope styles to your component to avoid conflicts
- Use responsive design principles (mobile-first)
- Consider both portrait and landscape orientations
- Use CSS variables for colors and spacing (defined in `colors.scss` and `variables.scss`)

### 4. Security

**Input Validation:**
- Use DataType parameter classes with PHP 8 attributes for type-safe input parsing
- Never manually validate request parameters - let the framework handle it
- Example parameter class:

```php
use DataType\Create\CreateFromRequest;
use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\BasicInteger;

class MyParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('description')]
        public readonly string $description,
        #[BasicInteger('count')]
        public readonly int $count,
    ) {
    }
}
```

Then inject it into your controller method:
```php
public function update(MyParams $params): JsonResponse {
    // $params is already validated and type-safe
    $this->repo->update($params);
    return new JsonResponse(['success' => true]);
}
```

**Output Escaping:**
- Use `esprintf()` for all HTML output containing dynamic data
- The placeholder prefix indicates the escaping type:
  - `:html_*` - HTML content escaping (for text within tags)
  - `:attr_*` - HTML attribute escaping (for attribute values)
  - `:uri_*` - URI encoding (for URLs)
  - `:raw_*` - No escaping (for already-safe HTML)

Example:
```php
$template = '<a href="/users/:attr_username">:html_display_name</a>';
$params = [
    ':attr_username' => $user->username,
    ':html_display_name' => $user->display_name,
];
$html = esprintf($template, $params);
```

**Never:**
- Manually escape with `htmlspecialchars()` - use `esprintf()` instead
- Manually validate request parameters - use DataType parameter classes
- Concatenate user input directly into HTML strings

### 5. Performance
- Only load external CSS/JS when needed using `ExtraAssets`
- Minimize the initial data passed via `data-widgety_json`
- Use lazy loading for large datasets
- Consider caching strategies for frequently accessed data
- **Never fetch data inside render loops** - this causes performance issues and multiple API calls on every render. Use `componentDidMount()` or `componentDidUpdate()` instead, or implement proper caching strategies

### 6. PHP Coding Guidelines

**Regular Expressions - Always use the `u` modifier:**

When writing regex patterns in PHP, always include the `u` modifier to enable proper UTF-8 handling. Without this modifier, multi-byte characters (like accented letters `é`, `ü`, `ñ`) may not match correctly.

```php
// Good - UTF-8 safe
preg_match('/[ée]goutt[ée]/u', $text, $matches);

// Bad - may fail with UTF-8 characters
preg_match('/[ée]goutt[ée]/', $text, $matches);
```

The `u` modifier:
- Treats the pattern and subject strings as UTF-8
- Ensures character classes like `[ée]` work correctly with multi-byte characters
- Required when matching any non-ASCII characters (accents, umlauts, etc.)

## Example: Creating a Simple "Hello World" Page

### 1. Add the route
```php
// app/src/app_routes.php
['/tools/hello', 'GET', 'Bristolian\AppController\Tools::helloPage'],
```

### 2. Create the controller method
```php
// src/Bristolian/AppController/Tools.php
public function helloPage(): string {
    $data = ['message' => 'Hello, World!'];
    [$error, $values] = convertToValue($data);
    $widget_json = json_encode_safe($values);
    $widget_data = htmlspecialchars($widget_json);

    return <<< HTML
<h1>Hello World Demo</h1>
<div class="hello_panel" data-widgety_json="$widget_data"></div>
HTML;
}
```

### 3. Create the TypeScript component
```typescript
// app/public/tsx/HelloPanel.tsx
import {h, Component} from "preact";

export interface HelloPanelProps {
    message: string;
}

interface HelloPanelState {
    count: number;
}

export class HelloPanel extends Component<HelloPanelProps, HelloPanelState> {
    constructor(props: HelloPanelProps) {
        super(props);
        this.state = { count: 0 };
    }

    render(props: HelloPanelProps, state: HelloPanelState) {
        return (
            <div class="hello_panel_react">
                <p>{props.message}</p>
                <p>Count: {state.count}</p>
                <button onClick={() => this.setState({count: state.count + 1})}>
                    Increment
                </button>
            </div>
        );
    }
}
```

### 4. Register in bootstrap
```typescript
// app/public/tsx/bootstrap.tsx
import { HelloPanel } from "./HelloPanel";

let panels: WidgetClassBinding[] = [
    // ... existing panels
    {
        class: 'hello_panel',
        component: HelloPanel
    },
];
```

### 5. Create the SCSS
```scss
// app/public/scss/hello.scss
.hello_panel {
    padding: 1rem;
    border: 1px solid #ccc;
    
    .hello_panel_react {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        
        button {
            max-width: 200px;
        }
    }
}
```

## Troubleshooting

### Component Not Rendering
- Check that the class name in PHP HTML matches the class in `bootstrap.tsx`
- Verify the component is imported in `bootstrap.tsx`
- Check browser console for JavaScript errors
- Ensure the TypeScript code compiles without errors

### Data Not Passing from PHP to TypeScript
- Verify `data-widgety_json` attribute is properly set in HTML
- Check that JSON is properly escaped with `htmlspecialchars()` for the attribute value
- Ensure JSON is valid using `json_encode_safe()`
- Use `convertToValue()` to prepare data structures for JSON encoding
- Check TypeScript interface matches PHP data structure

### Styles Not Applied
- Verify SCSS file is imported or included in the build
- Check that class names match between HTML and SCSS
- Clear browser cache
- Inspect element to see which styles are actually applied

### Route Not Found
- Check route path and method in `app_routes.php`
- Verify controller class and method exist
- Check for typos in the route definition
- Ensure route parameters are properly defined

## Related Documentation

- [Development Setup](development_setup.md) - How to set up the development environment
- [Testing Guidelines](testing_guidelines.md) - How to test your components
- [API Documentation](api_documentation.md) - API endpoint conventions
- [Project Layout](project_layout.md) - Overview of the project structure

