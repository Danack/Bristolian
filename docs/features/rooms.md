# Rooms Feature

Rooms contain files, links, and annotations. Room tags can be created in the Room Management panel and then assigned to individual files, links, and annotations. Tags are displayed alongside each entity and can be edited via an "Edit tags" button (only room tags created in Room Management can be assigned).

## Database

- `room` – rooms
- `room_tag` – tags belonging to a room (created in Room Management)
- `room_file` – files in a room (UNIQUE on room_id, stored_file_id)
- `room_link` – links in a room
- `room_annotation` – annotations in a room
- `room_file_tag` – junction: which tags are on which room file
- `room_link_tag` – junction: which tags are on which room link
- `room_annotation_tag` – junction: which tags are on which room annotation

Migration: `db/migrations/35_room_entity_tags.php`

## Backend

- `src/Bristolian/AppController/Rooms.php` – getFiles, getLinks, getAnnotations, getAnnotationsForFile (return *WithTags types), setFileTags, setLinkTags, setAnnotationTags
- `src/Bristolian/Repo/RoomTagRepo/` – RoomTagRepo (getTagsForRoom, createTag)
- `src/Bristolian/Repo/RoomFileTagRepo/` – RoomFileTagRepo (getTagIdsForRoomFile, setTagsForRoomFile)
- `src/Bristolian/Repo/RoomLinkTagRepo/` – RoomLinkTagRepo (getTagIdsForRoomLink, setTagsForRoomLink)
- `src/Bristolian/Repo/RoomAnnotationTagRepo/` – RoomAnnotationTagRepo (getTagIdsForRoomAnnotation, setTagsForRoomAnnotation)
- `src/Bristolian/Model/Types/RoomFileWithTags.php`, `RoomLinkWithTags.php`, `RoomAnnotationWithTags.php` – list response types including tags
- `src/Bristolian/Parameters/SetEntityTagsParam.php` – request body for PUT set-tags endpoints
- `api/src/api_routes.php` – GET list routes (with *WithTags type_info), PUT `/api/rooms/{room_id}/files/{file_id}/tags`, PUT links/tags, PUT annotations/tags
- `api/src/api_convert_exception_to_json_functions.php` – ContentNotFoundException → 404 JSON

## Frontend

- `app/public/tsx/RoomFilesPanel.tsx` – file list with tags column and Edit tags modal
- `app/public/tsx/RoomLinksPanel.tsx` – link list with tags and Edit tags modal
- `app/public/tsx/RoomAnnotationsPanel.tsx` – annotation list with tags and Edit tags modal
- `app/public/tsx/AnnotationPanel.tsx` – per-file annotations with tags and Edit tags
- `app/public/tsx/RoomManagementPanel.tsx` – create/manage room tags
- `app/public/tsx/api_room_entity_tags.tsx` – setFileTags, setLinkTags, setAnnotationTags (PUT helpers)
- `app/public/scss/room_entity_tags.scss` – tag chips and Edit tags modal styles

## API

- GET `/api/rooms/{room_id}/files` → `GetRoomsFilesResponse` (files: RoomFileWithTags[])
- GET `/api/rooms/{room_id}/links` → `GetRoomsLinksResponse` (links: RoomLinkWithTags[])
- GET `/api/rooms/{room_id}/annotations` → `GetRoomsAnnotationsResponse` (annotations: RoomAnnotationWithTags[])
- GET `/api/rooms/{room_id}/file/{file_id}/annotations` → `GetRoomsFileAnnotationsResponse` (annotations: RoomAnnotationWithTags[])
- PUT `/api/rooms/{room_id}/files/{file_id}/tags` – body `{ "tag_ids": string[] }`
- PUT `/api/rooms/{room_id}/links/{room_link_id}/tags` – body `{ "tag_ids": string[] }`
- PUT `/api/rooms/{room_id}/annotations/{room_annotation_id}/tags` – body `{ "tag_ids": string[] }`

Only tag_ids that belong to the room (from Room Management) are accepted; invalid ids are filtered out.
