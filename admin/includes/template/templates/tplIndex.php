<?php
/**
 * Admin Home Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
//print_r($tplVars['widgets']);
?>
<section class="content-header">
    <h1><a href="#" class="widget-add">+ Add Widget</a></h1>
</section>
<div class="grid-stack grid-stack-3">
    <?php foreach ($tplVars['widgets'] as $widgetKey => $widget) { ?>
      <?php $tplVars['widget']['content'] = $widget['content']; ?>
        <div class="grid-stack-item" data-gs-id="<?php echo $widget['widgetBaseId']; ?>"
             data-gs-x="<?php echo $widget['widgetInfo']['widget_column']; ?>"
             data-gs-y="<?php echo $widget['widgetInfo']['widget_row']; ?>"
             data-gs-width="<?php echo $widget['widgetInfo']['widget_width']; ?>" data-gs-height="<?php echo $widget['widgetInfo']['widget_height']; ?>">
            <div id="<?php echo $widget['widgetBaseId']; ?>" class="flipable grid-stack-item-content">
                <div class="flip-front box box-solid <?php echo $widget['widgetInfo']['widget_theme']; ?> sortable">
                    <div class="box-header ui-sortable-handle" style="cursor: move;">
                        <i class="fa <?php echo $widget['widgetInfo']['widget_icon']; ?>"></i>

                        <h3 class="box-title"><?php echo $widget['widgetTitle']; ?></h3>

                        <div class="pull-right box-tools">
                            <button class="btn btn-success btn-sm" data-widget="settings" type="button">
                                <i class="fa fa-wrench"></i>
                            </button>
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


<div id="add-widget" class="modal fade " tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Widget</h4>
            </div>
            <div class="modal-body add-widget-container">
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        var options = {
            width: 3,
            cellHeight: 200,
            verticalMargin: 10,
            disableResize: true
        };
        $('.grid-stack').gridstack(options);
    });

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

    $('.widget-add').click(function (e) {
        zcJS.ajax({
            url: "zcAjaxHandler.php?act=dashboardWidget&method=getInstallableWidgets",
            data: {}
        }).done(function( response ) {
            $('.add-widget-container').html(response.html);
            $('#add-widget').modal('show');
        });

    });


</script>
