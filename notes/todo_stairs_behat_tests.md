# Bristol Stairs Map - Testable Actions and Capabilities

This document lists all testable actions and capabilities identified in `app/public/js/bristol_stairs_map.js` that need to be covered by Behat tests.

## Map Initialization and Display

1. Map initializes with correct bounds (minZoom 11, maxZoom 22)
2. Map centers on Bristol coordinates (51.4545, -2.5879) at zoom level 12 on page load
3. Map displays OpenStreetMap tile layer correctly
4. Map respects maxBounds (prevents panning outside Bristol area)

## Data Loading

5. Fetches stairs data from `/api/bristol_stairs` endpoint on page load
6. Processes API response and creates markers for each stair in the response
7. Handles API fetch errors gracefully (console error logged, no crash)
8. Stores stair data in `stairsById` lookup object

## Marker Display and Clustering

9. Markers are created for all stairs from API response
10. Markers are added to marker cluster group
11. Markers use default icon initially
12. Marker clustering behavior changes based on zoom level:
    - Zoom < 12: clusters aggressively (radius 80)
    - Zoom 12-15: medium clustering (radius 40)
    - Zoom >= 16: minimal clustering (radius 10)
13. Markers are visible on the map after data loads

## Marker Interaction

14. Clicking a marker sends "MAP_MARKER_CLICKED" message with stair info
15. Clicking a marker highlights it (changes to highlighted icon)
16. Clicking a marker centers the map on that marker's position
17. Clicking a marker zooms to at least level 15 (if currently zoomed out)
18. Only one marker can be highlighted at a time (previous highlight is removed)
19. Marker positions correspond to stair latitude/longitude from API

## Focus on Specific Stair

20. Focusing on a marker (via `focusOnMarker`) highlights it and centers map
21. Focus functionality works when markers are already loaded
22. Focus functionality works when triggered before markers are loaded (pending_stair_info_to_focus)
23. When a stair is selected on load before markers load, it focuses once markers become available

## Stair Info Updates

24. When "STAIR_INFO_UPDATED" message is received, stair info is updated in lookup
25. When stair position (lat/lng) is updated, the marker moves to new position
26. When "STAIR_POSITION_UPDATED" message is received, stair info is updated AND editing mode is cancelled

## Position Editing Mode

27. When "STAIR_START_EDITING_POSITION" message is received:
    - All markers are hidden
    - A crosshair is displayed over the map
    - Map moves to the stair's current position (if it exists)
    - Map view is invalidated and repositioned

28. When "STAIR_CANCEL_EDITING_POSITION" message is received:
    - All markers are shown again
    - Crosshair is removed from the map

29. Crosshair is properly styled (red, centered, full width/height, non-interactive)
30. Crosshair is only created once and reused

## Map Position Tracking

31. When map moves (pan or zoom), "STAIRS_MAP_POSITION_CHANGED" message is sent with:
    - Current latitude
    - Current longitude
    - Current zoom level

## Edge Cases and State Management

32. Handles stair selection when no latitude/longitude exists (doesn't crash)
33. Multiple rapid marker clicks (only last clicked marker is highlighted)
34. Editing position for stair with no existing coordinates (handles gracefully)

## Message Listener Registration

35. All message listeners are registered on DOMContentLoaded:
    - "STAIR_INFO_UPDATED" → `bristol_stair_info_updated`
    - "STAIR_POSITION_UPDATED" → `bristol_stair_position_updated`
    - "STAIR_START_EDITING_POSITION" → `bristol_stair_start_editing_position`
    - "STAIR_CANCEL_EDITING_POSITION" → `bristol_stair_cancel_editing_position`
    - "STAIR_SELECTED_ON_LOAD" → `bristol_stair_selected_on_load`

---

**Summary:** This list covers the main behaviors in `bristol_stairs_map.js`. For Behat testing purposes, focus on testing visible/interactive behaviors (map display, marker clicks, UI changes) and integrations with the message system, as these are what can be verified through browser automation.
