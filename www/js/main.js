// Nette JS init
$(function () {
    $.nette.init();
});

// DateTime picker locale
$.datetimepicker.setLocale('cs');

$(document).ready(function () {
    // DateTime picker init
    $(".datepicker").datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        dayOfWeekStart: 1
    });

    $(".datetimepicker").datetimepicker({
        format: 'Y-m-d H:i',
        step: 30,
        dayOfWeekStart: 1
    });

    // Google PlacesAutoComplete init
    initializePlacesAutocomplete('placeautocomplete');
});

// Navigation panels AJAX fix
$("ul.nav-pills.nav-ajax li a").on('click', function () {
    $(this).parents('ul.nav-pills').children('li.active').removeClass('active');
    $(this).parent('li').addClass('active');
});

// AJAX spinner for AJAX loading animation
$(function () {
    $('<div id="ajax-spinner"></div>').appendTo("body").ajaxStop(function () {
        $(this).hide();
    }).hide();
});

// Nette ajaxification
$("a.ajax").on("click", function (event) {
    $("#ajax-spinner").show();
});

// AJAX fix for ajaxification of newly loaded snippets
$(document).ajaxComplete(function () {
    $("a.ajax").on("click", function (event) {
        $("#ajax-spinner").show();
    });

    $("ul.nav-pills.nav-ajax li a").on('click', function () {
        $(this).parents('ul.nav-pills').children('li.active').removeClass('active');
        $(this).parent('li').addClass('active');
    });
});

// Flash message hiding on click
$('.flash').on('click', function () {
    $(this).hide();
});