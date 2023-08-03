var $ = jQuery.noConflict();
$(function ($) {
    $("#repopulateSearch").change(function() {
        if ($('#repopulateSearch').is(':checked')) {
            var searchTerms = new URLSearchParams(window.location.search).get('search');
            $('#search').val(searchTerms + ' ');
            $('#searchTermRepopulate').remove();
        }
    });
    $("#restrictIDs").change(function() {
        if ($('#restrictIDs').is(':checked')) {
            var href = new URL(window.location.href);
            href.searchParams.set('restrictIDs', 'on');
            window.location = href.toString();
        } else {
            var href = new URL(window.location.href);
            href.searchParams.set('restrictIDs', 'off');
            window.location = href.toString();
        }
    });
});
