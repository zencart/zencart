/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */
$(function () {
    var sortableConfig = {
        handle: ".panel-heading",      // Only drag by the header
        connectWith: ".sortable-list", // Allow moving between lists
        placeholder: "ui-sortable-placeholder",

        // KEY FIXES FOR USABILITY:
        tolerance: "pointer",       // Detects drop target based on mouse cursor, not box overlap
        forcePlaceholderSize: true, // Placeholder takes exact size of dragged item
        distance: 5,                // Prevents accidental drags on clicks
        cursor: "move",             // Visual cursor feedback

        // restrict where things can go
        receive: function (event, ui) {
            var item = ui.item;
            var receiverId = $(this).attr('id');
            var senderId = ui.sender.attr('id');
            var widgetId = item.data('id');

            // Main Zone is exclusive (nothing enters, nothing leaves)
            var isMainWidget = (senderId === 'zone-main');
            var isTargetMain = (receiverId === 'zone-main');

            if (isMainWidget && !isTargetMain) {
                $(ui.sender).sortable('cancel');
                return; // Main widgets stay in Main
            }
            if (!isMainWidget && isTargetMain) {
                $(ui.sender).sortable('cancel');
                return; // Other widgets cannot enter Main
            }

            // Traffic Widget cannot fit in Sidebar
            if (item.hasClass('locked-bottom') && receiverId === 'zone-sidebar') {
                $(ui.sender).sortable('cancel');
                alert('The Traffic Chart is too wide for the sidebar.');
                return;
            }

            // if dropping INTO Sidebar -> force full width of sidebar (col-md-12)
            if (receiverId === 'zone-sidebar') {
                item.removeClass('col-md-3 col-sm-6 col-md-6').addClass('col-md-12');
            }

            // if dropping INTO Bottom -> restore grid width
            if (receiverId === 'zone-bottom') {
                item.removeClass('col-md-12');

                if (item.hasClass('locked-bottom')) {
                    item.addClass('col-md-6 col-sm-12'); // Traffic is half width
                } else {
                    item.addClass('col-md-3 col-sm-6'); // Standard widgets are quarter width
                }
            }
        },

        stop: function () {
            saveLayout();
        }
    };

    $("#zone-main").sortable(sortableConfig);
    $("#zone-sidebar, #zone-bottom").sortable(sortableConfig);

    function saveLayout() {
        var layout = {
            main: [],
            sidebar: [],
            bottom: []
        };

        $('#zone-main li').each(function () {
            var widgetId = $(this).data('id');
            if (widgetId) layout.main.push(widgetId);
        });
        $('#zone-sidebar li').each(function () {
            var widgetId = $(this).data('id');
            if (widgetId) layout.sidebar.push(widgetId);
        });
        $('#zone-bottom li').each(function () {
            var widgetId = $(this).data('id');
            if (widgetId) layout.bottom.push(widgetId);
        });

        // AJAX save
        zcJS.ajax({
            url: "ajax.php?act=ajaxAdminDashboardWidgetArrange&method=save",
            data: {layout: JSON.stringify(layout)}
        }).done(function(response ) {
            //console.log(response);
            if (response.error === true) {
                if (window.console && typeof(console.log) === 'function') {
                    console.log(response.message);
                }
            }
        });
    }
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover({
            html: true,
            sanitize: true
        });
    })
});
