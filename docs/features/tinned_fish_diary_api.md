# Tinned Fish Diary API

API for looking up tinned fish product information by barcode.

## Feature Description

This feature provides a REST API for the Tinned Fish Diary mobile app to look up product information by barcode. It first checks the canonical database, then optionally fetches from external APIs (OpenFoodFacts) with proper copyright attribution.

## API Endpoint

- **Path**: `GET /api/tfd/v1/products/barcode/{barcode}`
- **Query Parameters**: `fetch_external` (boolean, optional, default: true)
- **Authentication**: Session-based (cookies required)

## Authentication

The API uses session-based authentication via HTTP cookies. All requests must include valid session cookies obtained through the login process.

### Login Process

To authenticate, clients must:

1. **POST to the login endpoint** with credentials:
   - **URL**: `http://local.bristolian.org/login` (development) or `https://bristolian.org/login` (production)
   - **Method**: `POST`
   - **Content-Type**: `application/x-www-form-urlencoded`
   - **Body**: 
     - `username`: User email address
     - `password`: User password

2. **Capture the session cookies** from the response:
   - The server will set **two** session cookies in the `Set-Cookie` headers:
     - `john_is_my_name` - Session identifier
     - `john_is_my_name_key` - Session encryption key
   - **Both cookies must be included** in all subsequent API requests

3. **Include the cookies in API requests**:
   - Send both cookies in the `Cookie` header with all API requests
   - The browser will handle this automatically if using `fetch()` with `credentials: 'include'`

### Test Credentials

For development and testing, use these credentials:

- **Username**: `testing@example.com`
- **Password**: `testing`

### JavaScript Example

```javascript
// Step 1: Login and capture session cookies
async function login(username, password) {
  const formData = new URLSearchParams();
  formData.append('username', username);
  formData.append('password', password);
  
  const response = await fetch('http://local.bristolian.org/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: formData,
    credentials: 'include' // Important: include cookies
  });
  
  // The session cookies (john_is_my_name and john_is_my_name_key) are 
  // automatically stored by the browser when credentials: 'include' is set
  return response.ok;
}

// Step 2: Make authenticated API requests
async function lookupProduct(barcode) {
  const response = await fetch(
    `http://local.api.bristolian.org/api/tfd/v1/products/barcode/${barcode}?fetch_external=true`,
    {
      credentials: 'include' // Include both session cookies
    }
  );
  
  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }
  
  return await response.json();
}

// Usage
await login('testing@example.com', 'testing');
const product = await lookupProduct('3017620422003');
console.log(product);
```

### Important Notes

- **Required Cookies**: Both `john_is_my_name` and `john_is_my_name_key` cookies must be present in API requests. Missing either cookie will result in authentication failure.
- **Cookie Domain**: Ensure your JavaScript client is making requests to the same domain or a subdomain that shares cookies
- **CORS**: If making cross-origin requests, the server must be configured to allow credentials
- **Session Expiry**: Sessions may expire after a period of inactivity; clients should handle 401/403 responses and re-authenticate
- **Browser vs. Node.js**: 
  - In browser environments, cookies are handled automatically when using `credentials: 'include'`
  - For Node.js or other environments, you'll need to manually extract both `Set-Cookie` header values and include them in subsequent requests:
    ```javascript
    // Example for Node.js (using node-fetch or similar)
    const cookies = response.headers.get('set-cookie');
    // Extract both john_is_my_name and john_is_my_name_key values
    // Include them in Cookie header: "john_is_my_name=...; john_is_my_name_key=..."
    ```

## Important Files

### Database
- `db/migrations/29_tinned_fish_product.php` - Migration for the canonical product table

### Models
- `src/Bristolian/Model/TinnedFish/Product.php` - Product data model
- `src/Bristolian/Model/TinnedFish/Copyright.php` - Copyright attribution model
- `src/Bristolian/Model/TinnedFish/ProductError.php` - Error response model

### Repository
- `src/Bristolian/Repo/TinnedFishProductRepo/TinnedFishProductRepo.php` - Repository interface
- `src/Bristolian/Repo/TinnedFishProductRepo/PdoTinnedFishProductRepo.php` - PDO implementation

### Services
- `src/Bristolian/Service/TinnedFish/OpenFoodFactsFetcher.php` - External API fetcher
- `src/Bristolian/Service/TinnedFish/OpenFoodFactsApiException.php` - API exception
- `src/functions_tinned_fish.php` - Data normalization functions

### Parameters
- `src/Bristolian/Parameters/TinnedFish/BarcodeLookupParams.php` - Request parameters
- `src/Bristolian/Parameters/PropertyType/Barcode.php` - Barcode validation type
- `src/Bristolian/Parameters/PropertyType/OptionalBoolDefaultTrue.php` - Bool param type
- `src/Bristolian/Parameters/ProcessRule/StringToBoolDefaultTrue.php` - Bool process rule

### Responses
- `src/Bristolian/Response/TinnedFish/ProductLookupResponse.php` - Success response
- `src/Bristolian/Response/TinnedFish/ProductNotFoundResponse.php` - 404 response
- `src/Bristolian/Response/TinnedFish/InvalidBarcodeResponse.php` - 400 response
- `src/Bristolian/Response/TinnedFish/ExternalApiErrorResponse.php` - 502 response

### Controller
- `src/Bristolian/ApiController/TinnedFish.php` - Main controller

### Configuration
- `api/src/api_routes.php` - Route registration
- `api/src/api_injection_params.php` - Dependency injection config
- `test/test_injection_params.php` - Test dependency injection config

### Tests
- `test/BristolianTest/Repo/TinnedFishProductRepo/PdoTinnedFishProductRepoTest.php` - Repository tests
- `test/BristolianTest/Service/TinnedFish/ProductNormalizerTest.php` - Normalizer tests
- `test/BristolianTest/Parameters/TinnedFish/BarcodeLookupParamsTest.php` - Parameter tests
- `test/BristolianTest/Parameters/ProcessRule/StringToBoolDefaultTrueTest.php` - ProcessRule tests
- `test/BristolianTest/Model/TinnedFish/ProductErrorTest.php` - ProductError model tests
- `test/BristolianTest/Model/TinnedFish/CopyrightTest.php` - Copyright model tests
