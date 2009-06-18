/* functions in here are convenience functions against the CloudMade web-maps-lite API */

var map;
var markers = {}; // woeid -> CM.Marker

function initMap(lat, lon, zoom) {
    if (typeof lat == 'undefined') var lat = 51.5;
    if (typeof lon == 'undefined') var lon = -0.126;
    if (typeof zoom == 'undefined') var zoom = 1;
    var cloudmade = new CM.Tiles.CloudMade.Web({ /* styleId: '998',*/ key: '1a914755a77758e49e19a26e799268b7' });
    map = new CM.Map('map', cloudmade);
    map.setCenter(new CM.LatLng(lat, lon), zoom);
}

function clearMarkers() {
    for (var woeid in markers) {
        removeMarkerForWoeId(woeid);
    }
}

function showAllMarkers() {
    var locs = [];
    for (var woeid in markers) {
        locs.push(markers[woeid].getLatLng());
    }
    if (locs.length > 1) {
        map.zoomToBounds(new CM.LatLngBounds(locs));
    }
    else if (locs.length == 1) {
        map.setCenter(locs[0], 1);
    }
}

function addMarkerForPlace(name, woeid, latitude, longitude) {
    if (!markers[woeid]) {
        var myMarkerLatLng = new CM.LatLng(latitude, longitude);
        var myMarker = new CM.Marker(myMarkerLatLng, {
            title: name
        });
        
        markers[woeid] = myMarker;
            
        map.addOverlay(myMarker);     
                
        return myMarker;
    }
    else {
        return markers[woeid];
    }
}

function removeMarkerForWoeId(woeid) {
    if (markers[woeid]) {
        var myMarker = markers[woeid];
        map.removeOverlay(myMarker);
        markers[woeid] = null;
        delete markers[woeid];
    }
}