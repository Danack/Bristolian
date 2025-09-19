
const bristol = [51.4545, -2.5879]; // Latitude, Longitude

var southWest = L.latLng(51.3325441, -2.8657612),
    northEast = L.latLng(51.6014432, -2.2960328),
    bounds = L.latLngBounds(southWest, northEast);

var options = {
    maxBounds: bounds,   // Then add it here..
    maxZoom: 18,
    minZoom: 11
}

var map = L.map('bristol_stairs_map', options).setView(bristol, 12)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

var markers = L.markerClusterGroup();


function parse_response_json(response) {
    if (!response.ok) {
        throw new Error("HTTP error, status = " + response.status);
    }
    return response.json();
}

const stairsById = {};

function process_response_data(data) {
    for (let stair_info of data.data) {
        stairsById[stair_info.id] = stair_info;

        const marker = L.marker([stair_info.latitude, stair_info.longitude]);
        marker.stairId = stair_info.id;

        marker.on('click', (event) => {
            const info = stairsById[event.target.stairId];
            sendMessage("MAP_MARKER_CLICKED", info);
        });

        markers.addLayer(marker);
    }

    map.addLayer(markers);
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

function bristol_stair_info_updated(data) {
    let stair_info = data.stairInfo;
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

registerMessageListener("STAIR_INFO_UPDATED", bristol_stair_info_updated)

fetchData();
