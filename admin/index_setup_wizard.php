<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2023 Jul 06 Modified in v2.0.0-alpha1 $
 */
if (isset($_GET['action']) && $_GET['action'] == 'update') {

  if (isset($_POST['store_name']) && $_POST['store_name'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = :configValue:
            WHERE configuration_key = 'STORE_NAME'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_name'], 'string');
    $db->Execute($sql);
    $store_name = zen_output_string_protected($_POST['store_name']);
  }
  if (isset($_POST['store_owner']) && $_POST['store_owner'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = :configValue:
            WHERE configuration_key = 'STORE_OWNER'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner'], 'string');
    $db->Execute($sql);
    $store_owner = zen_output_string_protected($_POST['store_owner']);
  }
  if (isset($_POST['store_owner_email']) && $_POST['store_owner_email'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = :configValue:
            WHERE configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO',
                                        'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO',
                                        'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO',
                                        'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO',
                                        'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO',
                                        'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner_email'], 'string');
    $db->Execute($sql);
    $store_owner_email = zen_output_string_protected($_POST['store_owner_email']);
  }
  if (isset($_POST['zone_country_id']) && $_POST['zone_country_id'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = :configValue:
                WHERE configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['zone_country_id'], 'integer');
    $db->Execute($sql);
    $store_country = (int)($_POST['zone_country_id']);
  }
  $store_zone = '';
  if (isset($_POST['zone_id']) && $_POST['zone_id'] != '') {
    $store_zone = (int)($_POST['zone_id']);
  }
  $sql = "UPDATE " . TABLE_CONFIGURATION . "
          SET configuration_value = :configValue:
          WHERE configuration_key = 'STORE_ZONE'";
  $sql = $db->bindVars($sql, ':configValue:', $store_zone, 'integer');
  $db->Execute($sql);

  if (isset($_POST['store_address']) && $_POST['store_address'] != '') {
    $sql = "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = :configValue:
            WHERE configuration_key = 'STORE_NAME_ADDRESS'";
    $sql = $db->bindVars($sql, ':configValue:', $_POST['store_address'], 'string');
    $db->Execute($sql);
    $store_address = zen_output_string_protected($_POST['store_address']);
  }
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
} else {
  $store_country = STORE_COUNTRY;
  $store_zone = STORE_ZONE;
  $store_name = STORE_NAME;
  $store_owner = STORE_OWNER;
  $store_owner_email = STORE_OWNER_EMAIL_ADDRESS;
  $store_address = STORE_NAME_ADDRESS;
}

$country_string = zen_draw_pull_down_menu('zone_country_id', zen_get_countries_for_admin_pulldown(), $store_country, 'id="zone_country_id" class="form-control" onchange="update_zone(this.form);"');
$zone_string = zen_draw_pull_down_menu('zone_id', zen_get_country_zones($store_country), $store_zone, 'id="zone_id" class="form-control"');
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" href="includes/css/admin_access.css">
    <script>
      function update_zone(theForm) {
          // if there is no zone_id field to update, or if it is hidden from display, then exit performing no updates
          if (!theForm || !theForm.elements["zone_id"]) {
              return;
          }
          if (theForm.zone_id.type === "hidden") {
              return;
          }

          // set initial values
          let SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;
          let SelectedZone = theForm.elements["zone_id"].value;

          // reset the array of pulldown options so it can be repopulated
          let NumState = theForm.zone_id.options.length;
          while (NumState > 0) {
              NumState = NumState - 1;
              theForm.zone_id.options[NumState] = null;
          }
          // build dynamic list of countries/zones for pulldown
<?php echo zen_js_zone_list('SelectedCountry', 'theForm', 'zone_id', false); ?>

          // hide the field and its label if no zone
          if (theForm.zone_id.options.length == 0) {
              $("#zone_id, label[for=zone_id], #zonefieldgroup").hide();
          } else {
              $("#zone_id, label[for=zone_id], #zonefieldgroup").show();
          }

          // if we had a value before reset, set it again
          if (SelectedZone != '') {
              theForm.elements["zone_id"].value = SelectedZone;
          }
      }
    </script>
  </head>
  <body id="setupWizardPage">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <div class="container">
      <h1><?php echo HEADING_TITLE_WIZARD; ?></h1>
      <p><?php echo TEXT_STORE_DETAILS; ?></p>
      <div class="row">
          <?php echo zen_draw_form('setupWizardForm', FILENAME_DEFAULT, 'action=update', 'post', 'id="setupWizardForm" class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_STORE_NAME, 'store_name', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('store_name', $store_name, 'id="store_name" class="form-control" autofocus="autofocus"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_STORE_OWNER, 'store_owner', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('store_owner', $store_owner, 'id="store_owner" class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_STORE_OWNER_EMAIL, 'store_owner_email', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('store_owner_email', $store_owner_email, 'id="store_owner_email" class="form-control"', null, 'email'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_STORE_COUNTRY, 'zone_country_id', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo $country_string; ?>
          </div>
        </div>
        <div class="form-group" id="zonefieldgroup">
            <?php echo zen_draw_label(TEXT_STORE_ZONE, 'zone_id', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo $zone_string; ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_STORE_ADDRESS, 'store_address', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_textarea_field('store_address', 'soft', '50', '5', $store_address, 'id="store_address" class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary" id="button"><?php echo IMAGE_UPDATE; ?></button>
        </div>
        <?php echo '</form>'; ?>
      </div>
    </div>
