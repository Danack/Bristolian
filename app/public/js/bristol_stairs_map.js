const bristol = [51.4545, -2.5879]; // Latitude, Longitude

const stairsById = {};

let crosshairDiv = null;

let lastClickedMarker = null;
let pending_stair_info_to_focus = null;
let markers_loaded = false;

var southWest = L.latLng(51.3325441, -2.8657612),
    northEast = L.latLng(51.6014432, -2.2960328),
    bounds = L.latLngBounds(southWest, northEast);

var map_options = {
    maxBounds: bounds,   // Then add it here..
    maxZoom: 22,
    minZoom: 11,
}

var tile_options = {
    maxNativeZoom: 18,  // highest zoom your tiles actually have
    maxZoom: 22         // allow Leaflet to zoom further
}

var map = L.map('bristol_stairs_map', map_options).setView(bristol, 12)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', tile_options).addTo(map);

var markers = L.markerClusterGroup({
    maxClusterRadius: function (zoom) {
        if (zoom < 12) {
            // At low zooms, cluster aggressively
            return 80;
        } else if (zoom < 16) {
            // Medium zooms, less aggressive
            return 40;
        } else {
            // At high zooms, barely cluster at all
            return 10;
        }
    }
});

function parse_response_json(response) {
    if (!response.ok) {
        throw new Error("HTTP error, status = " + response.status);
    }
    return response.json();
}



// Default Leaflet icons
const defaultIcon = L.icon({
    iconUrl: '/css/leaflet/images/marker-icon.png',
    iconRetinaUrl: '/css/leaflet/images/marker-icon-2x.png',
    shadowUrl: '/css/leaflet/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// Highlighted icons (provide your own images)
const highlightedIcon = L.icon({
    iconUrl: '/css/leaflet/images/marker-icon-highlighted.png',
    iconRetinaUrl: '/css/leaflet/images/marker-icon-highlighted-2x.png',
    shadowUrl: '/css/leaflet/images/marker-shadow.png', // you can reuse the same shadow
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

function process_response_data(data) {
    for (let stair_info of data.data) {
        stairsById[stair_info.id] = stair_info;

        // Create marker with default icon
        const marker = L.marker([stair_info.latitude, stair_info.longitude], { icon: defaultIcon });
        marker.stairId = stair_info.id;

        marker.on('click', (event) => {
            const info = stairsById[event.target.stairId];
            sendMessage("MAP_MARKER_CLICKED", info);
            focusOnMarker(marker);
        });

        markers.addLayer(marker);
    }
    map.addLayer(markers);

    markers_loaded = true;

    // If we received a message on page load to bring a stair into focus
    // we do that now we have the stair data.
    if (pending_stair_info_to_focus !== null) {
        console.log("pending_stair_info_to_focus !== null when stairs loaded");
        markers.eachLayer((marker) => {
            if (marker.stairId === pending_stair_info_to_focus.id) {
                focusOnMarker(marker);
            }
        });
        pending_stair_info_to_focus = null;
    }
}


function focusOnMarker(marker) {
    // Highlight marker
    if (lastClickedMarker) {
        lastClickedMarker.setIcon(defaultIcon);
    }
    marker.setIcon(highlightedIcon);
    lastClickedMarker = marker;

    // Center map on the marker
    const latlng = marker.getLatLng();
    const currentZoom = map.getZoom();
    const targetZoom = currentZoom < 15 ? 15 : currentZoom;

    console.log("Map should have moved and zoomed");
    map.setView(latlng, targetZoom);
}


function fetchData() {
    let url = '/api/bristol_stairs';

    fetch(url)
        .then(parse_response_json)
        .then(process_response_data)
        .catch(function(error) {
            console.error("Error fetching data:", error);
        });
}

function updateStairInfo(stair_info) {

    console.log("Updating stair:", stair_info);

    // Replace the entry in the lookup with the updated info
    stairsById[stair_info.id] = stair_info;

    // If you also want to move the marker when lat/lng changes:
    markers.eachLayer((marker) => {
        if (marker.stairId === stair_info.id) {
            marker.setLatLng([stair_info.latitude, stair_info.longitude]);
        }
    });
}


function bristol_stair_info_updated(data) {
    let stair_info = data.stair_info;
    updateStairInfo(stair_info);
}

function bristol_stair_position_updated(data) {
    let stair_info = data.stair_info;
    updateStairInfo(stair_info);
    bristol_stair_cancel_editing_position();
}

function bristol_stair_selected_on_load(data) {
    let stair_info = data.stair_info;

    if (markers_loaded === true) {
        markers.eachLayer((marker) => {
            if (marker.stairId === stair_info.id) {
                focusOnMarker(marker);
            }
        });
    }
    else {
        pending_stair_info_to_focus = stair_info;
    }
}

function bristol_stair_cancel_editing_position() {
    // Show all markers again
    if (!map.hasLayer(markers)) {
        map.addLayer(markers);
    }

    // Remove the crosshair
    if (crosshairDiv && crosshairDiv.parentNode) {
        crosshairDiv.parentNode.removeChild(crosshairDiv);
    }
}

function bristol_stair_start_editing_position(data) {
    let stair_info = data.stair_info;

    // Move map to the stair's position
    if (stair_info.latitude && stair_info.longitude) {
        map.setView([stair_info.latitude, stair_info.longitude], map.getZoom());
    }

    // Hide all markers
    if (map.hasLayer(markers)) {
        map.removeLayer(markers);
    }

    // Draw a full crosshair over the map
    if (!crosshairDiv) {
        crosshairDiv = L.DomUtil.create('div', 'crosshair', map.getContainer());
        Object.assign(crosshairDiv.style, {
            position: 'absolute',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            pointerEvents: 'none',
            zIndex: 1000,
        });

        // Vertical line
        const vLine = document.createElement('div');
        Object.assign(vLine.style, {
            position: 'absolute',
            top: '0',
            bottom: '0',
            left: '50%',
            width: '2px',
            marginLeft: '-1px',
            background: 'rgba(255, 0, 0, 0.7)', // semi-transparent red
        });

        // Horizontal line
        const hLine = document.createElement('div');
        Object.assign(hLine.style, {
            position: 'absolute',
            left: '0',
            right: '0',
            top: '50%',
            height: '2px',
            marginTop: '-1px',
            background: 'rgba(255, 0, 0, 0.7)',
        });

        crosshairDiv.appendChild(vLine);
        crosshairDiv.appendChild(hLine);
    }

    map.getContainer().appendChild(crosshairDiv);
}

// Listen to map move events
map.on('move', () => {
    const center = map.getCenter(); // L.LatLng object
    const positionData = {
        latitude: center.lat,
        longitude: center.lng,
        zoom: map.getZoom()
    };

    sendMessage("STAIRS_MAP_POSITION_CHANGED", positionData);
});



addEventListener("DOMContentLoaded", (event) => {
    registerMessageListener("STAIR_INFO_UPDATED", bristol_stair_info_updated)
    registerMessageListener("STAIR_POSITION_UPDATED", bristol_stair_position_updated)
    registerMessageListener("STAIR_START_EDITING_POSITION", bristol_stair_start_editing_position)
    registerMessageListener("STAIR_CANCEL_EDITING_POSITION", bristol_stair_cancel_editing_position)
    registerMessageListener("STAIR_SELECTED_ON_LOAD", bristol_stair_selected_on_load)

    fetchData();
})



