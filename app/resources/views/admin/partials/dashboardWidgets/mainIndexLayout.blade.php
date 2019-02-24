<div class="grid-stack grid-stack-3">
    @foreach ($tplVars['widgets'] as $widgetKey => $widget)
  <?php $tplVars['widget']['content'] = $widget['content']; ?>
    <div class="grid-stack-item" data-gs-id="{{ $widget['widgetBaseId'] }}" id="gs-{{$widget['widgetBaseId']}}"
         data-gs-x="{{$widget['widgetInfo']['widget_column']}}"
         data-gs-y="{{$widget['widgetInfo']['widget_row']}}"
         data-gs-width="{{$widget['widgetInfo']['widget_width']}}"
         data-gs-height="{{$widget['widgetInfo']['widget_height']}}"
         >
        <div id="{{$widget['widgetBaseId']}}" class="grid-stack-item-content">
            <div class="box box-solid {{$widget['widgetInfo']['widget_theme']}} sortable">
                <div class="box-header ui-sortable-handle" style="cursor: move;">
                    <i class="fa {{$widget['widgetInfo']['widget_icon'] }}"></i>
                    <h3 class="box-title">{{$widget['widgetTitle']}}</h3>
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
                    @includeFirst(['partials/dashboardWidgets/' . $widget['templateFile'], 'partials/dashboardWidgets/defaultContent'])
                </div>
            </div>
        </div>
    </div>
@endforeach
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
