# Front-end design rules

## Buttons must have a CSS class

All `<button>` elements must have the `button_standard` class (or another explicit styling class). The global default button style in `standard_ui_objects.scss` is deliberately set to bright pink (`#ff00ff`) so that unstyled buttons are visually obvious as mistakes. Use `className="button_standard"` for standard buttons, and add `button_chat` as a secondary class for smaller inline action buttons (e.g. Edit, Play, Edit tags).

## Modals must close on Escape key

Any modal or overlay dialog must close when the user presses the Escape key. Add an `onKeyDown` handler on the modal overlay element that checks for `e.key === "Escape"` and calls the close function.

## Modal overlay structure

Modals follow a consistent two-div pattern: an outer overlay div that closes the modal on click, and an inner content div with `onClick={(e) => e.stopPropagation()}` to prevent clicks inside the modal from closing it. Use the classes `room_edit_tags_modal_overlay` for the overlay and `room_edit_tags_modal` for the inner content. When a save operation is in progress, disable closing: `onClick={() => !saveInProgress && this.close()}`.

## Error and success messages

Display errors using `<div className="error">` or `<span className="error">`. Display success messages using `<div className="success">`. Store error state as `error: string | null` in component state, and conditionally render: `{state.error && <div className="error">{state.error}</div>}`.

## Login-gated UI

Use the `store.ts` login state to conditionally show logged-in-only features. In class components, call `get_logged_in()` for the initial value and `subscribe_logged_in(callback)` in `componentDidMount` (unsubscribe in `componentWillUnmount`). In function components, use the `use_logged_in()` hook. Hide controls behind `{state.logged_in && (<button ...>)}`.

## API calls

Use the generated `api` object from `generated/api_routes` for standard GET endpoints (e.g. `api.rooms.links(room_id)`, `api.rooms.videos(room_id)`). Use raw `fetch` for POST/PUT endpoints or custom API calls. For tag mutations, use the helpers in `api_room_entity_tags.tsx` (`setLinkTags`, `setFileTags`, `setVideoTags`, `setAnnotationTags`).

## Optional initial data from PHP (widgety)

Panels are mounted by `bootstrap.tsx` + `widgety/widgety.tsx`. The PHP page renders a container whose `class` matches a bootstrap entry. **If** the server has data the client cannot derive, PHP may add `data-widgety_json`; `widgety.tsx` parses it and passes the object as constructor props (see `BristolStairs::render_stairs_page()`). If PHP only mounts an empty div, props are `{}` and the panel should take copy, defaults, and reference data from TypeScript (see `committee_seats/page_config.ts`, `committee_seats/example_councils.ts`, or `TwitterSplitterPanel`).

**When using `data-widgety_json`:**

- Declare a `*PanelProps` interface matching the PHP `$data` keys.
- Build `$data` with `convertToValue()`, `json_encode_safe()`, and `htmlspecialchars()` on the attribute.

**When not using it:**

- Keep configuration in `app/public/tsx/` (or generated types). PHP returns a mount point only, e.g. `Pages::committee_seats_page()`.

Do not use CSS parent selectors (e.g. `:has()`) to stand in for data or copy.

More detail: [creating_webpages.md](creating_webpages.md) — “PHP to TypeScript”.

## Component state initialization

Class components should define a `getDefaultState()` function that returns the initial state object, and set `this.state = getDefaultState()` in the constructor (or `getDefaultState(props)` when PHP passes initial props). This keeps defaults readable and separate from the constructor logic.

## Preact render return types

This project uses **Preact**, not React. Do not type `render()` or private render helpers with `h.JSX.Element` — that name comes from React’s JSX typings and is misleading here.

**Preferred:** omit the return type and let `Component`’s `render` signature infer it (see examples in [creating_webpages.md](creating_webpages.md)).

**If you need an explicit type** (e.g. a variable holding JSX before return), use Preact’s `VNode`:

```typescript
import type { VNode } from "preact";

private renderSection(): VNode | null {
    // ...
}
```

`h.JSX.TargetedEvent<...>` on event handlers is fine; that is only for DOM events, not for render output.

## CSS class naming

Use `snake_case` for CSS class names (e.g. `room_links_panel_react`, `room_edit_tags_modal_overlay`, `button_standard`). Panel root elements should use the pattern `{feature}_panel_react` (e.g. `room_videos_panel_react`, `meme_management_panel_react`).

## Tags display

Display entity tags using `<span className="room_entity_tags">` containing `<span className="room_entity_tag_chip">` elements for each tag. When there are no tags, render `<span className="room_entity_tags empty">—</span>`.

## Date/time for files, links and videos

When displaying timestamps for files, links and videos (e.g. in lists or panels), use the `formatDateTimeForContent` function from `functions.tsx`. It shows relative time within the last hour, "Today" + time for the same day, and date-only for older items.

## Empty states

When a list has no items, render a short descriptive `<p>` element (e.g. `<p>No videos.</p>`, `<p>No links.</p>`, `<p>No files.</p>`).
