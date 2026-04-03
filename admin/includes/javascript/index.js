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
            layout.main.push($(this).data('id'));
        });
        $('#zone-sidebar li').each(function () {
            layout.sidebar.push($(this).data('id'));
        });
        $('#zone-bottom li').each(function () {
            layout.bottom.push($(this).data('id'));
        });

        // AJAX save
        $.post('ajax_dashboard.php', {layout: layout}, function (response) {
            console.log("Layout Saved");
        });
    }
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();
    })
});
