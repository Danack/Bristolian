
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

function fetchData() {
    let url = '/api/bristol_stairs';

    fetch(url)
        .then(function(response) {
            if (!response.ok) {
                throw new Error("HTTP error, status = " + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            let stairs_info = data.data;
            for (var i = 0; i < stairs_info.length; i++) {
                let stair_info = stairs_info[i];

                const marker = L.marker([
                    stair_info.latitude,
                    stair_info.longitude,
                ])

                marker.on('click', (event) => {
                    sendMessage("MAP_MARKER_CLICKED", stair_info)
                });

                markers.addLayer(marker)
            }

            map.addLayer(markers);
        })
        .catch(function(error) {
            console.error("Error fetching data:", error);
        });
}

fetchData();
