<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
 */
  $version_check_index=true;
  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'class.zcWidgetManager.php');
  require(DIR_WS_CLASSES . 'class.zcDashboardWidgetBase.php');
  //$widgetManager = new zcWidgetManager();
  $widgetProfileList = zcWidgetManager::getInstallableWidgetsList($_SESSION['admin_id']);
  $widgetInfoList = zcWidgetManager::getWidgetInfoForUser($_SESSION['admin_id']);
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
$extraCss[] = array('location' => DIR_WS_INCLUDES . 'template/css/index.css');
require('includes/admin_html_head_index.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<?php
/**
 * Save setup-wizard stuff
 */
if (isset($_POST['action']) && $_POST['action'] == 'setup-wizard')
{
  if (isset($_POST['store_name']) && $_POST['store_name'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key = 'STORE_NAME'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_name'], 'string');
    $db->execute($sql);
  }
  if (isset($_POST['store_owner']) && $_POST['store_owner'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key = 'STORE_OWNER'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner'], 'string');
    $db->execute($sql);
  }
  if (isset($_POST['store_owner_email']) && $_POST['store_owner_email'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO', 'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner_email'], 'string');
    $db->execute($sql);
  }
  if (isset($_POST['store_country']) && $_POST['store_country'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_country'], 'integer');
    $db->execute($sql);
  }
  if (isset($_POST['store_zone']) && $_POST['store_zone'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key = 'STORE_ZONE'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_zone'], 'integer');
    $db->execute($sql);
  }
  if (isset($_POST['store_address']) && $_POST['store_address'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue: WHERE configuration_key = 'STORE_NAME_ADDRESS'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_address'], 'string');
    $db->execute($sql);
  }
  $hasDoneStartWizard = TRUE;
}
if ($hasDoneStartWizard == FALSE) {

?>
<style>
/** @TODO - replace this with a Zurb Foundation class */
.form-horizontal input, select
{
  font-size: .8em;
}
.invalid {
  color: #FF2222
}
</style>
<div class="content-fluid">
  <div class="row">
    <div class="small-12 columns small-centered">
  <h1><?php echo TEXT_HEADING_SETUP_WIZARD; ?></h1>
<?php
  if (!zen_is_superuser())
  {
?>
  <div class="alert alert-error"><?php echo TEXT_WARNING_SUPERUSER_REQUIRED; ?></div>
  </div>
<?php
  } else {
  $storeName = (isset($_POST['store_name'])) ? $_POST['store_name'] : ((STORE_NAME != '') ? STORE_NAME : '');
  $storeOwner = isset($_POST['store_owner']) ? $_POST['store_owner'] : ((STORE_OWNER != '') ? STORE_OWNER : '');
  $storeOwnerEmail = isset($_POST['store_owner_email']) ? $_POST['store_owner_email'] : ((STORE_OWNER_EMAIL_ADDRESS != '') ? STORE_OWNER_EMAIL_ADDRESS : '');
  $storeCountry = isset($_POST['store_country']) ? $_POST['store_country'] : ((STORE_COUNTRY != '') ? STORE_COUNTRY : '');
  $storeZone = isset($_POST['store_zone']) ? $_POST['store_zone'] : ((STORE_ZONE != -1) ? STORE_ZONE : -1);
  $country_string = zen_draw_pull_down_menu('store_country', zen_get_countries(), $storeCountry, 'id="store_country" tabindex="4"');
  $zone_string = zen_draw_pull_down_menu('store_zone', zen_get_country_zones($storeCountry), $storeZone, 'id="store_zone" tabindex="5"');

//  $sql = "select zone_id, zone_name from " . TABLE_ZONES . "";  // order by zone_country_id, zone_name
//  $zone = $db->Execute($sql);
//  $zone_string = '';
//  $zone_string .= '<option value="-1"' . (($storeZone == -1) ? ' SELECTED ' : '') .  '>' . '-- Please Select --' . '</option>';
//  $zone_string .= '<option value="0"' . (($storeZone == 0) ? ' SELECTED ' : '') .  '>' . '-None-' . '</option>';
//  while (!$zone->EOF) {
//    $zone_string .= '<option value="' . $zone->fields['zone_id'] . '"' . (($storeZone == $zone->fields['zone_id']) ? ' SELECTED ' : '') . '>' . $zone->fields['zone_name'] . '</option>';
//    $zone->MoveNext();
//  }
  ?>
   <form id="setup-wizard" class="form-horizontal" method="post">
   <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
   <input type="hidden" name="action" value="setup-wizard">
   <fieldset>
   <legend><?php echo TEXT_FORM_LEGEND_REQUIRED_SETUP; ?></legend>
  <div class="row">
  <div class="small-3 columns">
  <label class="inline" for="store_name"><?php echo TEXT_FORM_LABEL_STORE_NAME; ?></label>
  </div>
  <div class="small-9 columns">
  <input type="text" id="store_name" name="store_name" value="<?php echo zen_output_string_protected($storeName); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STORENAME; ?>">
  </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_owner"><?php echo TEXT_FORM_LABEL_STORE_OWNER; ?></label>
   </div>
  <div class="small-9 columns">
    <input type="text" id="store_owner" name="store_owner" value="<?php echo zen_output_string_protected($storeOwner); ?>" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNER; ?>">
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_owner_email"><?php echo TEXT_FORM_LABEL_STORE_OWNER_EMAIL; ?></label>
   </div>
  <div class="small-9 columns">
   <input type="text" id="store_owner_email" name="store_owner_email" value="<?php echo zen_output_string_protected($storeOwnerEmail); ?>" tabindex="3" placeholder="webmaster@example.com">
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_country"><?php echo TEXT_FORM_LABEL_STORE_COUNTRY; ?></label>
   </div>
  <div class="small-9 columns">
   <?php echo $country_string; ?>
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_zone"><?php echo TEXT_FORM_LABEL_STORE_ZONE; ?></label>
   </div>
  <div class="small-9 columns" id="store_zone_container">
   <?php echo $zone_string; ?>
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_address"><?php echo TEXT_FORM_LABEL_STORE_ADDRESS; ?></label>
   </div>
  <div class="small-9 columns">
   <textarea rows="" cols=""  id="store_address" name="store_address" tabindex="6" placeholder="<?php echo TEXT_EXAMPLE_STOREADDRESS; ?>"></textarea>
   </div>
   </div>

   <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>" tabindex="10">
   </fieldset>
   </form>
<script>
   $().ready(function() {
       $.validator.messages.required = '<?php echo TEXT_FORM_ERROR_REQUIRED; ?>';
       $("#setup-wizard").validate({
         errorElement: 'span',
         errorClass: 'help-inline invalid',
         submitHandler: function(form) {
          form.submit();
         },
         rules: {
           store_name: "required",
           store_owner: "required",
           store_owner_email: "required email",
           store_zone: {
              required: true,
              min: 0
            }
        },
         messages: {
            store_zone: '<?php echo TEXT_FORM_ERROR_CHOOSE_ZONE; ?>'
         }
       });
     });
   $(function()
       {
     $('#store_country').change(function(e) {
      zcJS.ajax({
        url: "zcAjaxHandler.php?act=installWizard&method=getZones",
        data: {id: $(this).val()}
      }).done(function( response ) {
        $('#store_zone_container').html(response.html);
      });

     });
       });
     </script>
<?php
  }
?>
  </div>
</div>
<?php
} else {

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
  <?php foreach ($widgetList as $widgetObject) { ?>
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
    if ($(this).html() == '<i class="icon-down-dir"></i>')
    {
      $(this).html('<i class="icon-up-dir"></i>');
    } else
    {
      $(this).html('<i class="icon-down-dir"></i>');
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
</div>
<?php } ?>

<footer class="small-12 columns small-centered">
<!-- The following copyright announcement is in compliance
to section 2c of the GNU General Public License, and
thus can not be removed, or can only be modified
appropriately.

Please leave this comment intact together with the
following copyright announcement. //-->

  <div class="copyrightrow"><a href="http://www.zen-cart.com" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" border="0" /></a><br /><br />E-Commerce Engine Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></div><div class="warrantyrow"><br /><br />Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="http://www.zen-cart.com/license/2_0.txt" target="_blank">GNU General Public License</a></div>

</footer>

<!-- Initialize the Foundation plugins -->
<script>
  $(document).foundation();
</script>

</body>
</html>
<?php require('includes/application_bottom.php'); ?>
