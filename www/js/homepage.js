$(document).ready(function () {
    // School and gym markers
    var school = new google.maps.LatLng(49.6622837, 18.6744073);
    var gym = new google.maps.LatLng(49.6732847, 18.6751366);

    /**
     * Initializes google map on homepage. Sets markers and infoboxes to map.
     * 
     * @returns {undefined}
     */
    function initialize() {
        var mapProp = {
            center: gym,
            zoom: 14,
            scrollwheel: false,
            draggable: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        var gymMarker = new google.maps.Marker({
            position: gym,
        });
        gymMarker.setMap(map);
        var schoolMarker = new google.maps.Marker({
            position: school
        });
        schoolMarker.setMap(map);
        gymMarker.info = new google.maps.InfoWindow({
            content: '<b>Dojo: </b> ' + 'Komenského 713,739 61 Třinec </br>'
                    + '<i>primární tréninkové prostory</i>'
        });
        schoolMarker.info = new google.maps.InfoWindow({
            content: '<b>Tělocvična: </b> ' + 'Slezská 773,739 61 Třinec </br>'
                    + '<i>vedlejší tréninkové prostory</i>'
        });
        google.maps.event.addListener(gymMarker, 'click', function () {
            gymMarker.info.open(map, gymMarker);
        });
        google.maps.event.addListener(schoolMarker, 'click', function () {
            schoolMarker.info.open(map, schoolMarker);
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);
    // Add smooth scrolling to all links in navbar + footer link
    $(".navbar a.local-link, #contactUs, footer a[href='#myPage']").on('click', function (event) {

// Prevent default anchor click behavior
        event.preventDefault();
        // Store hash
        var hash = this.hash;
        // Using jQuery's animate() method to add smooth page scroll
        // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
        $('html, body').animate({
            scrollTop: $(hash).offset().top
        }, 900, function () {

// Add hash (#) to URL when done scrolling (default click behavior)
            window.location.hash = hash;
        });
    });
    $(window).scroll(function () {
        $(".slideanim").each(function () {
            var pos = $(this).offset().top;
            var winTop = $(window).scrollTop();
            if (pos < winTop + 600) {
                $(this).addClass("slide");
            }
        });
    });

    // Closes flash messages on click
    $('.flash .close').on('click', function () {
        $(this).parent('.flash').hide();
    });
});