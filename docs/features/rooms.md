# Rooms Feature

Rooms contain files, links, annotations, and videos. Room tags can be created in the Room Management panel and then assigned to individual files, links, annotations, and videos. Tags are displayed alongside each entity and can be edited via an "Edit tags" button (only room tags created in Room Management can be assigned).

## Database

- `room` – rooms
- `room_tag` – tags belonging to a room (created in Room Management)
- `room_file` – files in a room (UNIQUE on room_id, stored_file_id)
- `room_link` – links in a room
- `room_annotation` – annotations in a room
- `video` – canonical YouTube video (youtube_video_id)
- `room_video` – videos/clips in a room (full video or clip with start_seconds/end_seconds, parent_room_video_id for clips)
- `room_video_transcript` – stored VTT transcripts per room_video (transcript_number, vtt_content)
- `room_file_tag` – junction: which tags are on which room file
- `room_link_tag` – junction: which tags are on which room link
- `room_annotation_tag` – junction: which tags are on which room annotation
- `room_video_tag` – junction: which tags are on which room video

Migrations: `db/migrations/35_room_entity_tags.php`, `db/migrations/36_room_videos.php`

## Backend

- `src/Bristolian/AppController/Rooms.php` – getFiles, getLinks, getVideos, getAnnotations, getAnnotationsForFile, addVideo, createClip, getTranscripts, getTranscript, fetchTranscript, setFileTags, setLinkTags, setVideoTags, setAnnotationTags
- `src/Bristolian/Repo/RoomTagRepo/` – RoomTagRepo (getTagsForRoom, createTag)
- `src/Bristolian/Repo/RoomFileTagRepo/` – RoomFileTagRepo (getTagIdsForRoomFile, setTagsForRoomFile)
- `src/Bristolian/Repo/RoomLinkTagRepo/` – RoomLinkTagRepo (getTagIdsForRoomLink, setTagsForRoomLink)
- `src/Bristolian/Repo/RoomAnnotationTagRepo/` – RoomAnnotationTagRepo (getTagIdsForRoomAnnotation, setTagsForRoomAnnotation)
- `src/Bristolian/Repo/VideoRepo/` – VideoRepo (create, getById)
- `src/Bristolian/Repo/RoomVideoRepo/` – RoomVideoRepo (getVideosForRoom, getRoomVideo, addVideo)
- `src/Bristolian/Repo/RoomVideoTranscriptRepo/` – RoomVideoTranscriptRepo (getTranscriptsForRoomVideo, addTranscript, getTranscriptById)
- `src/Bristolian/Repo/RoomVideoTagRepo/` – RoomVideoTagRepo (getTagIdsForRoomVideo, setTagsForRoomVideo)
- `src/functions.php` – `extract_youtube_video_id()` extracts YouTube video ID from URL
- `src/Bristolian/Service/YouTube/YouTubeTranscriptFetcher.php` – fetch captions via unofficial timedtext, return VTT
- `src/Bristolian/Model/Types/RoomFileWithTags.php`, `RoomLinkWithTags.php`, `RoomVideoWithTags.php`, `RoomAnnotationWithTags.php` – list response types including tags
- `src/Bristolian/Parameters/SetEntityTagsParam.php`, `AddVideoParam.php`, `CreateClipParam.php` – request body types
- `api/src/api_routes.php` – GET list routes (with *WithTags type_info), video and transcript routes, PUT tags
- `api/src/api_convert_exception_to_json_functions.php` – ContentNotFoundException → 404 JSON

## Frontend

- `app/public/tsx/RoomFilesPanel.tsx` – file list with tags column and Edit tags modal
- `app/public/tsx/RoomLinksPanel.tsx` – link list with tags and Edit tags modal
- `app/public/tsx/RoomVideosPanel.tsx` – video list, add video, create clip, embedded player, transcript fetch/list/VTT viewer with click-to-seek
- `app/public/tsx/RoomAnnotationsPanel.tsx` – annotation list with tags and Edit tags modal
- `app/public/tsx/AnnotationPanel.tsx` – per-file annotations with tags and Edit tags
- `app/public/tsx/RoomManagementPanel.tsx` – create/manage room tags
- `app/public/tsx/api_room_entity_tags.tsx` – setFileTags, setLinkTags, setVideoTags, setAnnotationTags (PUT helpers)
- `app/public/scss/room_entity_tags.scss` – tag chips and Edit tags modal styles
- `app/public/scss/room_videos.scss` – video panel, embed, transcript section styles

## API

- GET `/api/rooms/{room_id}/files` → `GetRoomsFilesResponse` (files: RoomFileWithTags[])
- GET `/api/rooms/{room_id}/links` → `GetRoomsLinksResponse` (links: RoomLinkWithTags[])
- GET `/api/rooms/{room_id}/videos` → `GetRoomsVideosResponse` (videos: RoomVideoWithTags[])
- POST `/api/rooms/{room_id}/videos` – body `{ "url", "title?", "description?" }`
- POST `/api/rooms/{room_id}/videos/clips` – body `{ "room_video_id", "start_seconds", "end_seconds", "description?" }`
- GET `/api/room-videos/{room_video_id}/transcripts` – list transcripts (no room in path; transcripts are not secret)
- GET `/api/room-videos/{room_video_id}/transcripts/{transcript_id}` – get one transcript VTT
- POST `/api/room-videos/{room_video_id}/transcripts/fetch` – fetch transcript from YouTube (unofficial API)
- PUT `/api/rooms/{room_id}/videos/{room_video_id}/tags` – body `{ "tag_ids": string[] }`
- GET `/api/rooms/{room_id}/annotations` → `GetRoomsAnnotationsResponse` (annotations: RoomAnnotationWithTags[])
- GET `/api/rooms/{room_id}/file/{file_id}/annotations` → `GetRoomsFileAnnotationsResponse` (annotations: RoomAnnotationWithTags[])
- PUT `/api/rooms/{room_id}/files/{file_id}/tags` – body `{ "tag_ids": string[] }`
- PUT `/api/rooms/{room_id}/links/{room_link_id}/tags` – body `{ "tag_ids": string[] }`
- PUT `/api/rooms/{room_id}/annotations/{room_annotation_id}/tags` – body `{ "tag_ids": string[] }`

Only tag_ids that belong to the room (from Room Management) are accepted; invalid ids are filtered out.
