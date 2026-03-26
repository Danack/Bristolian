# Tag display and editing (UI patterns)

This document describes how tags are **displayed**, **selected**, and **edited** in the memes management UI. Use it as a reference when building tag UX for **other content types** (not memes): reuse the same visual language, state shapes, and interaction patterns where they fit; swap meme-specific API paths and entity names for your feature’s endpoints.

**Primary reference implementation:** `app/public/tsx/MemeManagementPanel.tsx`  
**Shared tag “pill” styles:** `app/public/scss/standard_ui_objects.scss` (classes listed below)  
**Panel-specific layout:** `app/public/scss/meme_upload_panel.scss` (search bar, upload tag panel, bulk panel, meme edit layout)

Related product context for memes: `docs/features/memes.md`.

---

## Concepts

### Tag types (user vs system)

User-editable tags use a stable type string (memes: `user_tag` from `app/public/tsx/MemeTagType.ts`). **Add**, **edit**, and **delete** handlers check `tag.type === MemeTagType.USER_TAG` before mutating; system tags are intended to be read-only. When you add system tags later, **hide** edit/delete affordances for those rows (the meme table still renders Edit/Delete for every row; handlers no-op for non–user tags—prefer explicit UI for new features).

### Tag suggestion rows

Suggestions are `{ text: string; count: number }`: label text plus how many items already use that tag. They drive “pick from popular tags” UX, not the authoritative tag list on a single item.

### Minimum tag length

Client-side validation uses **`MINIMUM_TAG_LENGTH = 4`** (characters) for creating or renaming tag text in this panel. Keep the same constant in sync with **server-side** validation for your feature.

---

## Visual building blocks (reuse these classes)

From `standard_ui_objects.scss`:

| Class | Role |
|--------|------|
| `selected_tags_box` | Container for “tags you have chosen” with heading |
| `suggested_tags_box` | Container for clickable suggestions |
| `tag_list` | Flex row wrap of pills |
| `tag` | Base pill (padding, radius, cursor) |
| `suggested_tag` | Blue pill for suggestions; add `tag_selected` when chosen |
| `selected_tag` | Green pill for committed selection; show `×` in text for “click to remove” |

**Interaction conventions:**

- **Suggested pill:** click toggles membership in the selection array; selected state = `suggested_tag` + `tag_selected`.
- **Selected pill:** click removes that tag from the selection; suffix `×` in the label; `title="Click to remove"`.
- **Tooltips on suggestions:** e.g. `` `${count} items (Click to add/remove)` `` (memes use “memes” in the string).

---

## Pattern A — Filter/search by tags (list view)

**State:** `selectedTags: string[]` (tag texts), `suggestedTags: TagSuggestion[]`, plus any free-text query fields your search needs.

**Data loading:**

1. **Global suggestions:** `GET` with a limit, e.g. `/api/memes/tag-suggestions?limit=10`.
2. **Contextual suggestions:** when the current result set is non-empty, pass entity ids, e.g. `/api/memes/tag-suggestions?meme_ids=…&limit=10`; if the response is empty, fall back to global suggestions (see `loadSuggestedTagsForMemes`).

**UI layout:**

1. If `selectedTags.length > 0`, render **`selected_tags_box`** with heading (“Selected Tags:”) and **`tag_list`** of **`tag selected_tag`** chips (remove on click).
2. If `suggestedTags.length > 0`, render **`suggested_tags_box`** with heading (“Suggested Tags:”) and pills **`tag suggested_tag`** + **`tag_selected`** when that text is in `selectedTags`.
3. Toggling a suggestion updates `selectedTags` and **re-runs search** (memes: `performSearch()` after `setState` callback).

**Applying filters to the API:** memes join selected tag texts with commas: `tags=foo,bar` on `/api/memes/search`. Your feature should define an equivalent query contract.

---

## Pattern B — Choose tags before create/upload (no server tag until upload/save)

Used when uploading memes: tags are accumulated in **`uploadSelectedTags: string[]`** only; each successful upload then calls the same “add tag” API per tag.

**UI blocks (same as bulk, below):**

- Selected tags box (green pills).
- “Search tags” input — **client-side filter** over `suggestedTags` (trimmed empty = show all).
- Suggested tags (toggle into `uploadSelectedTags`).
- “Create new tag”: text input + “Add Tag” button; **Enter** submits; validate min length and duplicate-in-selection.

**After primary action:** for each new item id, POST add-tag requests (memes fire several `fetch` in parallel inside `addTagsToMeme`; bulk path sequences per meme with a short delay—see Pattern D).

---

## Pattern C — Edit tags on one open item (detail / slide-over)

**Open editor:** set “current item” in state; **`GET`** tags for that id (memes: `/api/memes/{id}/tags` → `data.meme_tags`).

**Display:**

- While `null`: show loading copy (`loading_message`).
- Empty array: show empty copy (`no_tags_message`).
- Else: **table** listing tag text and actions (`meme_tags_table` / `renderMemeTag`). Memes use **Edit** and **Delete** `button_standard` controls per row.

**Add tag:**

- Controlled input + “Add tag” button; **Enter** if text long enough.
- **POST** `FormData`: parent id, `type` (e.g. `user_tag`), `text`.
- On success, replace local tag list from response (`processTagAddData`).

**Edit tag:**

- Click Edit → open **modal** (`modal` / `modal-content`) with textarea, Save/Cancel.
- **PUT** `FormData`: tag id, `type`, new `text`.
- On success, update local list and close modal; validate min length client-side.

**Delete tag:**

- Click Delete → set `confirmTagDelete` state → **confirmation modal** (Cancel / Delete).
- **POST** delete endpoint with parent id + tag id; on success refresh tag list from response.
- **Escape** on document closes delete confirmation (`keydown` in `componentDidMount`).

Errors: show `.error` spans next to the relevant form (add / edit).

---

## Pattern D — Bulk apply tags to many items

**Selection:** memes use Shift+click on cards to toggle `selectedMemeIds`; normal click clears selection and opens single-item edit.

**Panel:** `bulk_add_tags_panel` — same structure as upload tag picker:

- “Tags to add to all N items” (`selected_tags_box` / green pills).
- Search input filtering suggestions client-side.
- Suggested pills toggling `bulkSelectedTags`.
- Create-new-tag row with validation.
- Primary action: disabled until at least one tag and not in progress; memes run **`addTagsToMeme` per selected id** with a small delay between memes to avoid hammering the server; then refresh list and clear bulk tag state.

---

## API shape (meme endpoints — generalize for your feature)

| Action | Meme endpoint | Method | Body (conceptual) |
|--------|----------------|--------|-------------------|
| List tags on item | `/api/memes/{id}/tags` | GET | — |
| Add tag | `/api/meme-tag-add/` | POST | `FormData`: parent id, `type`, `text` |
| Update tag | `/api/meme-tag-update/` | PUT | `FormData`: tag id, `type`, `text` |
| Delete tag | `/api/meme-tag-delete/` | POST | `FormData`: parent id, tag id |

Typed responses are imported from `./generated/api_routes` in the panel. For a new feature, add routes and generated types the same way; keep **FormData** field names aligned with PHP param objects.

---

## Room content tags (files/links/annotations/videos)

Rooms use a simpler model than memes: each room has a fixed tag set, and content items in that room reference those tag ids.

**Authoritative tag source for UI pickers:**

- `GET /api/rooms/{room_id}/tags` returns the room tag list.
- In TS, use generated helper `api.rooms.tags(room_id)`.
- For room content editing, render the full room tag set as clickable pills (no suggestion search endpoint needed for normal room sizes).

**Assignment endpoints (id-based membership):**

- `PUT /api/rooms/{room_id}/files/{file_id}/tags`
- `PUT /api/rooms/{room_id}/links/{room_link_id}/tags`
- `PUT /api/rooms/{room_id}/annotations/{room_annotation_id}/tags`
- `PUT /api/rooms/{room_id}/videos/{room_video_id}/tags`

Body for all four: `{ "tag_ids": string[] }`.

**UI interaction pattern used by room content panels:**

1. Keep local `selectedTagIds` state for the current entity.
2. Render two visual sections with shared classes:
   - `selected_tags_box` (green `selected_tag` pills, click to remove)
   - `suggested_tags_box` (all room tags as blue `suggested_tag` pills; selected tags also get `tag_selected`)
3. On pill toggle, update `selectedTagIds`, then immediately persist with the relevant `PUT .../tags` endpoint.
4. Refresh the list view after save/toggle (typically with cache-bust on room list endpoints) so chips and filters stay consistent with server state.

**Current concrete frontend references:**

- `app/public/tsx/AnnotationPanel.tsx` (inline title/text edit + room-tag pill picker)
- `app/public/tsx/RoomAnnotationsPanel.tsx`
- `app/public/tsx/RoomFilesPanel.tsx`
- `app/public/tsx/RoomLinksPanel.tsx`
- `app/public/tsx/RoomVideosPanel.tsx`
- `app/public/tsx/api_room_entity_tags.tsx`

---

## Checklist for a new content type

1. Reuse **`standard_ui_objects.scss`** tag classes for pills and boxes.
2. Keep **selected vs suggested** distinction: green committed chips vs blue toggle suggestions.
3. Enforce **minimum tag length** on client and server.
4. Restrict **edit/delete** to user-owned tag types; display system tags read-only (or hide actions).
5. Use **modals** for destructive actions (delete) and focused text edit (rename).
6. Provide **tag suggestions** endpoint(s): global + optional “for these ids” for smarter lists.
7. After mutations, **refresh** the tag list from the server response when the API returns the full list (memes do this for add/delete); edit currently patches local state after PUT.

When instructing an agent, you can say: **read `docs/features/tag_display_and_editing_patterns.md` and mirror the meme tag UX and classes unless the product spec differs.**
