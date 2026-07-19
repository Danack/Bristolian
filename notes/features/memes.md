# Memes Feature

The url for memes is http://local.bristolian.org/memes

A login for testing is:

username: testing@example.com
password: testing

## Frontend Files

- `app/public/tsx/MemeManagementPanel.tsx` - Meme list, tag editing, and search
- `app/public/tsx/MemeUploadPanel.tsx` - File upload with drag-and-drop
- `app/public/scss/meme_upload_panel.scss` - Styles for both panels

## Backend Files

- `api/src/api_routes.php` - API route definitions
- `src/Bristolian/AppController/User.php` - Controller methods for meme operations
- `src/Bristolian/Repo/MemeStorageRepo/PdoMemeStorageRepo.php` - Meme database operations
- `src/Bristolian/Repo/MemeTagRepo/PdoMemeTagRepo.php` - Meme tag database operations
- `src/Bristolian/Repo/MemeTextRepo/PdoMemeTextRepo.php` - Meme text (OCR) database operations
- `src/Bristolian/Parameters/MemeTagParams.php` - Add tag params
- `src/Bristolian/Parameters/MemeTagUpdateParams.php` - Update tag params
- `src/Bristolian/Parameters/MemeSearchParams.php` - Search params

## Data Model

### Meme Text Storage

- Meme text (OCR-extracted text from images) is stored in the `meme_text` table
- Each meme can have associated text stored for search purposes
- The `meme_text` table links to `stored_meme` via `meme_id`

### Tag Types

- **User Tags**: All tags created by users are of type `"user_tag"`
  - Users can create, edit, and delete any `user_tag` tags on their memes (not just tags they created)
  - These are the only tags that users can modify
  - The `user_id` field in the `meme_tag` table tracks which user created each tag
  - **Note**: The main purpose of tracking `user_id` when adding tags is to make certain types of sabotage easy to undo (e.g., mass tag adding by a malicious user)
- **System Tags** (future): Tags like NSFW, age rating, etc. that are managed by the system
  - Users cannot edit or delete system tags
  - These will be added in future updates

## Current Functionality

### Meme Upload (MemeUploadPanel.tsx)

- File selection via file input or drag-and-drop
- Visual feedback during drag (border color change, background highlight)
- Upload button that POSTs the selected file to `/api/meme-upload/`
- Upload status feedback (uploading, success, error states)
- Validates dropped files are images before accepting

### Meme Management (MemeManagementPanel.tsx)

- Lists all memes the user has uploaded in a table
- Refresh button to reload the meme list
- Search/filter functionality:
  - Search by tag text content (partial match)
  - Only searches tags of type `"user_tag"` - system tags are not included in search results and are not visible to users in search
  - Clear button to reset search and show all memes
- Edit tags functionality for each meme:
  - View existing tags for a meme (both user tags and system tags, if any)
  - Add new `user_tag` tags with text content
  - Edit existing `user_tag` tags via modal dialog (only user tags can be edited)
  - Delete existing `user_tag` tags (with confirmation modal; only user tags can be deleted)
  - System tags are displayed but cannot be edited or deleted by users

### Backend API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/memes` | GET | Returns list of user's memes |
| `/api/memes/search` | GET | Search memes by tag text and/or type |
| `/api/memes/{meme_id}/tags` | GET | Returns tags for a specific meme |
| `/api/meme-upload/` | POST | Upload a new meme image |
| `/api/meme-tag-add/` | POST | Add a tag to a meme |
| `/api/meme-tag-update/` | PUT | Update an existing meme tag |
| `/api/meme-tag-delete/` | DELETE | Delete a meme tag |

## Future Features

- **Meme deletion**: Soft-delete by setting state to 'deleted'
- **Tag suggestions**: When editing tags for a meme, suggest similar `user_tag` tags based on tags that other memes have that share some tags with the current meme
- **System tags**: Implementation of non-editable system tags (e.g., NSFW, age rating) that are managed by the system rather than users