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
- `src/Bristolian/Parameters/MemeTagParams.php` - Add tag params
- `src/Bristolian/Parameters/MemeTagUpdateParams.php` - Update tag params
- `src/Bristolian/Parameters/MemeSearchParams.php` - Search params

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
  - Filter by tag type (text, type, source)
  - Clear button to reset search and show all memes
- Edit tags functionality for each meme:
  - View existing tags for a meme
  - Add new tags with type (text, type, or source) and text content
  - Edit existing tags via modal dialog
  - Delete existing tags (with confirmation modal)

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

## Not Yet Implemented

- Meme deletion (soft-delete by setting state to 'deleted')