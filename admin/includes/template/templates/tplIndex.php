<?php
/**
 * Admin Home Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<div id="colone">
<div class="container">
  <div class="row">
    <div class="small-12 columns widget-tools">
      <a href="#" class="widget-add">+ Add Widget</a>
    </div>
  </div>
  <div class="row" id="main-sortables">
<?php require(DIR_WS_INCLUDES . 'template/partials/tplDashboardMainSortables.php'); ?>
  </div>
</div>
<div id="deleteWidget" class="reveal-modal small" data-reveal>
<a class="close-reveal-modal">x</a>
<p><?php echo TEXT_CONFIRM_REMOVE; ?></p>
<a class="radius button remove" href="#"><?php echo TEXT_REMOVE; ?></a>
<a class="radius secondary button dismiss" href="#"><?php echo TEXT_CANCEL; ?></a>
</div>

<div id="add-widget" class="reveal-modal small" data-reveal>
<div class="add-widget-container"></div>
<a class="close-reveal-modal">x</a>
<a class="radius secondary button dismiss" href="#"><?php echo TEXT_CANCEL; ?></a>
</div>

<script>
var intervalTimers = new Array();
$(function() {
  <?php foreach ($tplVars['widgetList'] as $widgetObject) { ?>
    <?php $widget = $widgetObject->getWidgetInfo(); ?>
    <?php if ($widget['widget_refresh'] != 0) { ?>
      createWidgetIntervalTimer('<?php echo $widget['widget_key']; ?>', '<?php echo $widget['widget_refresh']; ?>');
    <?php } ?>
  <?php } ?>

  createSortables();


  $('.widget-add').click(function (e) {
      zcJS.ajax({
        url: "zcAjaxHandler.php?act=dashboardWidget&method=getInstallableWidgets",
        data: {}
      }).done(function( response ) {
        $('.add-widget-container').html(response.html);
        $('#add-widget').foundation('reveal', 'open');
      });


      });

    $('.dismiss').click(function() {
      $('a.close-reveal-modal').trigger('click');
    });
  });


function createSortables() {
  $(".sortable-column").sortable(
  {
    connectWith : ".sortable-column",
    handle: '.widget-handle',
    placeholder: "ui-sortable-placeholder",
    forcePlaceholderSize: true,
    update : function(event, ui)
    {
      if (this === ui.item.parent()[0]) {
        var itemStr = getItems('.sortable-column');
        zcJS.ajax({
          url: "zcAjaxHandler.php?act=dashboardWidget&method=updateWidgetPositions",
          data: {'items': itemStr}
        }).done(function( msg ) {
        });
      }
    }
  });

    $('.widget-minimize').unbind('click').click(function (e) {
    var id = $(this).parents('.widget-container').attr('id');
    if ($(this).html() == '<i class="fa fa-toggle-down"></i>')
    {
      $(this).html('<i class="fa fa-toggle-up"></i>');
    } else
    {
      $(this).html('<i class="fa fa-toggle-down"></i>');
    }
    $('#'+id).find('.widget-body').toggle();
  });

  $('.widget-edit').unbind('click').click(function (e) {
    if ($('.widget-edit-form').length == 0)
    {
      var id = $(this).parents('.widget-container').attr('id');
      doWidgetEdit(id);
    }
  });


  $('.widget-close').unbind('click').click(function (e) {
    var id = $(this).parents('.widget-container').attr('id');
    $('#deleteWidget').foundation('reveal', 'open');
    $('.dismiss').click(function() {
      $('#deleteWidget').foundation('reveal', 'close');
    });
    $('.remove').unbind('click').click(function() {
      $('#deleteWidget').foundation('reveal', 'close');
      $('#'+id).hide();
      var itemStr = id;
      zcJS.ajax({
        url: "zcAjaxHandler.php?act=dashboardWidget&method=removeWidget",
        data: {'item': itemStr}
      }).done(function( response ) {
      });
    })
  });
}

function getItems(container)
{
  var columns = [];
  $(container).each(function(){
    columns.push($(this).sortable('toArray').join(','));
  });
  return columns.join('|');
}
function doWidgetEdit(id)
{
  //$(".columns").sortable('disable');
  zcJS.ajax({
    url: "zcAjaxHandler.php?act=dashboardWidget&method=getWidgetEdit",
    data: {'id': id}
  }).done(function( response ) {
    if (response.html)
    {
      $('#'+ id).find('.widget-body').html(response.html);
      createSortables();
    }
  });
}
function createWidgetIntervalTimer(key, interval)
{
  var realInterval = interval * 1000;
  if (intervalTimers[key]) {
    timer = intervalTimers[key];
    timer.Stop();
    delete timer;
    if (interval != 0)
    {
      var timer = new zcJS.timer({interval: realInterval, intervalEvent: doIntervalProcess, key: key});
      intervalTimers[key] = timer;
      timer.Start();
    }
  } else {
    if (interval != 0)
    {
      var timer = new zcJS.timer({interval: realInterval, intervalEvent: doIntervalProcess, key: key});
      intervalTimers[key] = timer;
      timer.Start();
    }
  }
}

function doIntervalProcess(timer)
{
  zcJS.ajax({
    url: "zcAjaxHandler.php?act=dashboardWidget&method=timerUpdate",
    data: {'id': timer.settings.key}
  }).done(function( response ) {
    $('#'+ timer.settings.key).find('.widget-body').html(response.html);
  });
}
</script>
</div>
