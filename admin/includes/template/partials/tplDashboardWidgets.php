<div class="grid-stack grid-stack-3">
    <?php foreach ($tplVars['widgets'] as $widgetKey => $widget) { ?>
  <?php $tplVars['widget']['content'] = $widget['content']; ?>
    <div class="grid-stack-item" data-gs-id="<?php echo $widget['widgetBaseId']; ?>" id="gs-<?php echo $widget['widgetBaseId']; ?>"
         data-gs-x="<?php echo $widget['widgetInfo']['widget_column']; ?>"
         data-gs-y="<?php echo $widget['widgetInfo']['widget_row']; ?>"
         data-gs-width="<?php echo $widget['widgetInfo']['widget_width']; ?>"
         data-gs-height="<?php echo $widget['widgetInfo']['widget_height']; ?>"
         >
        <div id="<?php echo $widget['widgetBaseId']; ?>" class="grid-stack-item-content">
            <div class="box box-solid <?php echo $widget['widgetInfo']['widget_theme']; ?> sortable">
                <div class="box-header ui-sortable-handle" style="cursor: move;">
                    <i class="fa <?php echo $widget['widgetInfo']['widget_icon']; ?>"></i>
                    <h3 class="box-title"><?php echo $widget['widgetTitle']; ?></h3>
                    <div class="pull-right box-tools">
                        <?php if ($widget['widgetInfo']['has_settings']) { ?>
                        <button class="btn btn-success btn-sm settings-toggle" type="button">
                            <i class="fa fa-wrench"></i>
                        </button>
                        <?php } ?>
                        <button class="btn btn-success btn-sm" data-widget="collapse" type="button">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button class="btn btn-success btn-sm" data-widget="remove" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-body">
                    <?php
                    if (file_exists($widget['templateFile'])) {
                        require($widget['templateFile']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>


<script type="text/javascript">

    $(function () {
        initGridStack();
    });

    function initGridStack()
    {
        var options = {
            width: 3,
            cellHeight: 200,
            verticalMargin: 10,
            disableResize: true
        };
        $('.grid-stack').gridstack(options);

    }

    $('.grid-stack').on('change', function(event, items) {
        var res = _.map($('.grid-stack .grid-stack-item:visible'), function (el) {
            el = $(el);
            var node = el.data('_gridstack_node');
            return {
                id: el.attr('data-gs-id'),
                x: node.x,
                y: node.y,
                width: node.width,
                height: node.height
            };
        });
        zcJS.ajax({
            url: "zcAjaxHandler.php?act=dashboardWidget&method=updateWidgetPositions",
            data: {'items': JSON.stringify(res)}
        }).done(function( msg ) {
        });
    });

    $('.widget-add').on('click', function (e) {
        zcJS.ajax({
            url: "zcAjaxHandler.php?act=dashboardWidget&method=getInstallableWidgets",
            data: {}
        }).done(function( response ) {
            $('.add-widget-container').html(response.html);
            $('#add-widget').modal('show');
        });

    });

    $('.settings-toggle').on('click', function(e) {
        var widgetId = $(this).parents(".box").first().parent().attr('id')
        $('#widget-settings').modal('show');
        zcJS.ajax({
            url: "zcAjaxHandler.php?act=dashboardWidget&method=getWidgetSettingsFields",
            data: {widget: widgetId}
        }).done(function( response ) {
            $('.widget-settings-container').html(response.html);
            $('#widget-settings').modal('show');
        });
    });

</script>
