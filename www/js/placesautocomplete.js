/* global google */

// This example displays an address form, using the autocomplete feature
// of the Google Places API to help users fill in the information.

var autocomplete;

function initializePlacesAutocomplete(selector) {
    var options = {
        types: ['geocode']
    };

    // Create the autocomplete object, restricting the search
    // to geographical location types.
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('placeautocomplete'), options);
}

// [START region_geolocation]
// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var geolocation = new google.maps.LatLng(
                    position.coords.latitude, position.coords.longitude);
            var circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
        });
    }
}
// [END region_geolocation]

// Fix for selecting place on ENTER click instead of submitting form.
$('#placeautocomplete').keydown(function (e) {
        if (e.which == 13 && $('.pac-container:visible').length)
            return false;
    });

