<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index.php 19537 2011-09-20 17:14:44Z drbyte $
 */
  $version_check_index=true;
  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'class.zcWidgetManager.php');
  require(DIR_WS_CLASSES . 'class.zcDashboardWidgetBase.php');
  //$widgetManager = new zcWidgetManager();
  $widgetProfileList = zcWidgetManager::getInstallableWidgetsList($_SESSION['admin_id'], $_SESSION['languages_id']);
  $widgetInfoList = zcWidgetManager::getWidgetInfoForUser($_SESSION['admin_id'], $_SESSION['languages_id']);
  //$widgetProfileList = $widgetManager->mergeProfileInfoList($widgetProfileList, $widgetInfoList);
  $widgetList = zcWidgetManager::loadWidgetClasses($widgetInfoList);
  $tplVars = zcWidgetManager::prepareTemplateVariables($widgetList);
  $tplVars['widgetInfoList'] = $widgetInfoList;
  //print_r($tplVars);
  
  $languages = zen_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $_SESSION['language']) {
      $languages_selected = $languages[$i]['code'];
    }
  }
$extraCss[] = array('location'=>DIR_WS_INCLUDES . 'template/css/index.css');
require('includes/admin_html_head_index.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<div class="container">
  <div class="row">
    <div class="twelve columns widget-tools">
      <a href="#" class="widget-add">+ Add Widget</a>
    </div>
  </div>
  <div class="row" id="main-sortables">
<?php require(DIR_WS_INCLUDES . 'template/partials/tplDashboardMainSortables.php'); ?>
  </div>
</div>
<div id="deleteWidget" class="reveal-modal">
<a class="close-reveal-modal">x</a>
<p><?php echo TEXT_CONFIRM_REMOVE; ?></p>
<a class="radius button remove" href="#"><?php echo TEXT_REMOVE; ?></a>
<a class="radius secondary button dismiss" href="#"><?php echo TEXT_CANCEL; ?></a>
</div>

<div id="add-widget" class="reveal-modal">
<div class="add-widget-container"></div>
<a class="close-reveal-modal">x</a>
<a class="radius secondary button dismiss" href="#"><?php echo TEXT_CANCEL; ?></a>
</div>

<script>
var intervalTimers = new Array();
$(function() {
  <?php foreach ($widgetList as $widgetObject) { ?>
    <?php $widget = $widgetObject->getWidgetInfo(); ?>
    <?php if ($widget['widget_refresh'] != 0) { ?>
      createWidgetIntervalTimer('<?php echo $widget['widget_key']; ?>', '<?php echo $widget['widget_refresh']; ?>');
    <?php } ?> 
  <?php } ?>
	
	createSortables();
	$('.widget-add').click(function (e) {
		$('#add-widget').reveal({opened: function() {

	    zcJS.ajax({
	      url: "zcAjaxHandler.php?act=dashboardWidget&method=getInstallableWidgets",
	      data: {}
	    }).done(function( response ) {
		    $('.add-widget-container').html(response.html); 
		  });	
		
		  
			} 
		});
		$('.dismiss').click(function() {
			$(this).trigger('reveal:close');
		});
  });
	
});

function createSortables() {
  $(".columns").sortable(
  {
    connectWith : ".columns",
    handle: '.widget-handle',
    placeholder: "ui-sortable-placeholder",
    forcePlaceholderSize: true,
    update : function(event, ui) 
    {
      if (this === ui.item.parent()[0]) {
        var itemStr = getItems('.columns');
        zcJS.ajax({
    	    url: "zcAjaxHandler.php?act=dashboardWidget&method=updateWidgetPositions",
    	    data: {'items': itemStr}
    	  }).done(function( msg ) {
    	  });	
      }            
    }
  });

	$('.widget-edit').click(function (e) {
		if ($('.widget-edit-form').length == 0)
		{
		  var id = $(this).parents('.widget-container').attr('id');
		  doWidgetEdit(id);
		}
	});
  
	$('.widget-minimize').click(function (e) {
		var id = $(this).parents('.widget-container').attr('id');
		if ($(this).html() == '-')
		{
			$(this).html('&#47;');
		} else 
		{
			$(this).html('&#45;');
		}
		$('#'+id).find('.widget-body').toggle();
	});

	$('.widget-close').click(function (e) {
		var id = $(this).parents('.widget-container').attr('id');
		$('#deleteWidget').reveal();
		$('.dismiss').click(function() {
			$(this).trigger('reveal:close');
		});
		$('.remove').click(function() {
			$('#deleteWidget').trigger('reveal:close');
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
  var timer = new zcJS.timer({interval: realInterval, intervalEvent: doIntervalProcess, key: key});
  intervalTimers[key] = timer;
  timer.Start();
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




<!-- The following copyright announcement is in compliance
to section 2c of the GNU General Public License, and
thus can not be removed, or can only be modified
appropriately.

Please leave this comment intact together with the
following copyright announcement. //-->

<div class="copyrightrow"><a href="http://www.zen-cart.com" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" border="0" /></a><br /><br />E-Commerce Engine Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></div><div class="warrantyrow"><br /><br />Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="http://www.zen-cart.com/license/2_0.txt" target="_blank">GNU General Public License</a><br />
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>