# API Documentation - Bristolian

This document describes the REST API endpoints available in the Bristolian application.

## Base URL

- **Development**: `http://local.api.bristolian.org`
- **Production**: `https://api.bristolian.org`

## Authentication

The API uses session-based authentication. Most endpoints require a valid user session.

## Response Format

All API responses are JSON formatted. Error responses include appropriate HTTP status codes and error details.

## Response Type Implementation

When creating new API endpoints, **always create specific response classes** rather than using generic `JsonResponse`. This ensures:

- **Type safety**: Response structure is enforced at the PHP level
- **Consistency**: All responses follow the same pattern
- **Maintainability**: Response format is centralized in one class
- **Documentation**: Response structure is self-documenting

### Creating Response Classes

Response classes should:
1. Be placed in `src/Bristolian/Response/` (or a subdirectory like `src/Bristolian/Response/TinnedFish/`)
2. Implement `SlimDispatcher\Response\StubResponse`
3. Define the response structure in the constructor
4. Return appropriate HTTP status codes via `getStatus()`
5. Set `Content-Type: application/json` header

### Example Response Class

```php
<?php

declare(strict_types=1);

namespace Bristolian\Response\TinnedFish;

use SlimDispatcher\Response\StubResponse;

class GetAllProductsResponse implements StubResponse
{
    private string $body;

    /**
     * @param Product[] $products
     */
    public function __construct(array $products)
    {
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = [
                'barcode' => $product->barcode,
                'name' => $product->name,
                // ... other fields
            ];
        }

        $response = [
            'success' => true,
            'products' => $productsData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
```

### Using Response Classes

In your API controller, return the response class instead of `JsonResponse`:

```php
public function getAllProducts(
    TinnedFishProductRepo $productRepo
): StubResponse {
    $products = $productRepo->getAll();
    return new GetAllProductsResponse($products);
}
```

See existing examples in:
- `src/Bristolian/Response/TinnedFish/ProductLookupResponse.php`
- `src/Bristolian/Response/TinnedFish/ProductNotFoundResponse.php`
- `src/Bristolian/Response/TinnedFish/GetAllProductsResponse.php`

## Endpoints

### System & Health

#### Health Check
- **GET** `/api/status`
- **Description**: Check API health status
- **Authentication**: None required
- **Response**: Health status information

#### API Index
- **GET** `/api`
- **Description**: List all available API routes
- **Authentication**: None required
- **Response**: Array of available routes

### Content Security Policy

#### CSP Report
- **POST** `/csp-report`
- **Description**: Submit Content Security Policy violation reports
- **Authentication**: None required
- **Request Body**: CSP violation report data

### User Management

#### Login Status
- **GET** `/api/login-status`
- **Description**: Get current user login status
- **Authentication**: Session required
- **Response**: User login information

#### Search Users
- **GET** `/api/search_users`
- **Description**: Search for users
- **Authentication**: Admin required
- **Query Parameters**: Search term
- **Response**: Array of matching users

#### Ping User
- **GET** `/api/ping_user`
- **Description**: Ping a specific user
- **Authentication**: Admin required
- **Query Parameters**: User identifier
- **Response**: Ping status

### Notifications

#### Save Subscription
- **POST** `/api/save-subscription/`
- **Description**: Save push notification subscription
- **Authentication**: Session required
- **Request Body**: Subscription data
- **Response**: Success status

- **GET** `/api/save-subscription/`
- **Description**: Get subscription information
- **Authentication**: Session required
- **Response**: Current subscription data

### Bristol Stairs

#### Get Stairs Data
- **GET** `/api/bristol_stairs`
- **Description**: Get all Bristol stairs information
- **Authentication**: None required
- **Response**: Array of stairs data

#### Get Stair Details
- **GET** `/api/bristol_stairs/{bristol_stairs_image_id}`
- **Description**: Get details for a specific stair
- **Authentication**: None required
- **Path Parameters**: `bristol_stairs_image_id` - Stair identifier
- **Response**: Stair details

#### Update Stair Info
- **GET** `/api/bristol_stairs_update/{bristol_stair_info_id}`
- **Description**: Get form for updating stair information
- **Authentication**: Session required
- **Path Parameters**: `bristol_stair_info_id` - Stair identifier
- **Response**: Update form data

- **POST** `/api/bristol_stairs_update/{bristol_stair_info_id}`
- **Description**: Update stair information
- **Authentication**: Session required
- **Path Parameters**: `bristol_stair_info_id` - Stair identifier
- **Request Body**: Updated stair data
- **Response**: Success status

#### Update Stair Position
- **POST** `/api/bristol_stairs_update_position/{bristol_stair_info_id}`
- **Description**: Update stair position coordinates
- **Authentication**: Session required
- **Path Parameters**: `bristol_stair_info_id` - Stair identifier
- **Request Body**: Position data (lat, lng)
- **Response**: Success status

#### Upload Stair Image
- **POST** `/api/bristol_stairs_image`
- **Description**: Upload image for Bristol stairs
- **Authentication**: Session required
- **Request Body**: Multipart form data with image file
- **Response**: Upload success and image details

#### Create New Stair
- **POST** `/api/bristol_stairs_create`
- **Description**: Create new Bristol stair entry
- **Authentication**: Session required
- **Request Body**: Stair data and image
- **Response**: Created stair information

### Memes

#### Upload Meme
- **POST** `/api/meme-upload/`
- **Description**: Upload a meme image
- **Authentication**: Session required
- **Request Body**: Multipart form data with image file
- **Response**: Upload success and meme details

- **GET** `/api/meme-upload/`
- **Description**: Get meme upload form
- **Authentication**: Session required
- **Response**: Upload form data

#### List Memes
- **GET** `/api/memes`
- **Description**: Get list of user's memes
- **Authentication**: Session required
- **Response**: Array of user's memes

#### Get Meme Tags
- **GET** `/api/memes/{meme_id}/tags`
- **Description**: Get tags for a specific meme
- **Authentication**: Session required
- **Path Parameters**: `meme_id` - Meme identifier
- **Response**: Array of tags

#### Add Meme Tag
- **POST** `/api/meme-tag-add/`
- **Description**: Add tag to a meme
- **Authentication**: Session required
- **Request Body**: Tag data
- **Response**: Success status

- **GET** `/api/meme-tag-add/`
- **Description**: Get tag addition form
- **Authentication**: Session required
- **Response**: Tag form data

#### Delete Meme Tag
- **DELETE** `/api/meme-tag-delete/`
- **Description**: Delete tag from a meme
- **Authentication**: Session required
- **Request Body**: Tag deletion data
- **Response**: Success status

- **GET** `/api/meme-tag-delete/`
- **Description**: Get tag deletion form
- **Authentication**: Session required
- **Response**: Tag deletion form data

### Chat System

#### Send Message
- **POST** `/api/chat/message`
- **Description**: Send a chat message
- **Authentication**: Session required
- **Request Body**: Message data
- **Response**: Message sent confirmation

- **GET** `/api/chat/message`
- **Description**: Get message sending form
- **Authentication**: Session required
- **Response**: Message form data

#### Get Room Messages
- **GET** `/api/chat/room_messages/{room_id}/`
- **Description**: Get messages for a specific room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Response**: Array of room messages

### Rooms & Files

#### Get Room Files
- **GET** `/api/rooms/{room_id}/files`
- **Description**: Get files for a specific room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Response**: Array of room files

#### Upload Room File
- **POST** `/api/rooms/{room_id}/file-upload`
- **Description**: Upload file to a room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Request Body**: Multipart form data with file
- **Response**: Upload success and file details

- **GET** `/api/rooms/{room_id}/file-upload`
- **Description**: Get file upload form for room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Response**: Upload form data

#### Room Links
- **POST** `/api/rooms/{room_id}/links`
- **Description**: Add link to a room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Request Body**: Link data
- **Response**: Success status

- **GET** `/api/rooms/{room_id}/links`
- **Description**: Get links for a room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Response**: Array of room links

#### Source Links
- **POST** `/api/rooms/{room_id}/source_link/{file_id}`
- **Description**: Add source link to a file
- **Authentication**: Session required
- **Path Parameters**: 
  - `room_id` - Room identifier
  - `file_id` - File identifier
- **Request Body**: Source link data
- **Response**: Success status

- **GET** `/api/rooms/{room_id}/file/{file_id}/sourcelinks`
- **Description**: Get source links for a specific file
- **Authentication**: Session required
- **Path Parameters**: 
  - `room_id` - Room identifier
  - `file_id` - File identifier
- **Response**: Array of source links

- **GET** `/api/rooms/{room_id}/sourcelinks`
- **Description**: Get all source links for a room
- **Authentication**: Session required
- **Path Parameters**: `room_id` - Room identifier
- **Response**: Array of all room source links

### System & Debug

#### CSP Reports for Page
- **GET** `/api/system/csp/reports_for_page`
- **Description**: Get CSP violation reports for a specific page
- **Authentication**: Admin required
- **Query Parameters**: Page identifier
- **Response**: Array of CSP reports

#### Processor Run Records
- **GET** `/api/log/processor_run_records`
- **Description**: Get processor run log records
- **Authentication**: Admin required
- **Response**: Array of processor run records

### Debug Endpoints

#### Test Caught Exception
- **GET** `/api/test/caught_exception`
- **Description**: Test caught exception handling
- **Authentication**: Debug required
- **Response**: Exception test result

#### Test Uncaught Exception
- **GET** `/api/test/uncaught_exception`
- **Description**: Test uncaught exception handling
- **Authentication**: Debug required
- **Response**: Exception test result

#### Test Xdebug
- **GET** `/api/test/xdebug`
- **Description**: Test Xdebug functionality
- **Authentication**: Debug required
- **Response**: Xdebug test result

### External Services

#### Mailgun Email Handler
- **POST** `/api/services/email/mailgun`
- **Description**: Handle incoming emails from Mailgun
- **Authentication**: Service key required
- **Request Body**: Mailgun webhook data
- **Response**: Processing status

## Error Handling

The API uses standard HTTP status codes:

- **200**: Success
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **500**: Internal Server Error

Error responses include:
```json
{
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": "Additional error details"
}
```

## Rate Limiting

Currently no rate limiting is implemented, but it may be added in the future.

## CORS

The API includes CORS headers to allow cross-origin requests from the frontend application.

## WebSocket

Real-time chat functionality is available via WebSocket at:
- **Development**: `ws://localhost:8015/chat`
- **Production**: `wss://chat.bristolian.org/chat`

The WebSocket connection handles:
- Real-time chat messages
- Room management
- User presence
- File sharing notifications
