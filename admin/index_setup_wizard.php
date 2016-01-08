<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.5.5 $
 */

if (isset($_GET['action']) && $_GET['action'] == 'update') {

    if (isset($_POST['store_name']) && $_POST['store_name'] != '') {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME'";
        $sql = $db->bindVars($sql, ':configValue:', $_POST['store_name'], 'string');
        $db->execute($sql);
        $store_name = zen_output_string_protected($_POST['store_name']);
    }
    if (isset($_POST['store_owner']) && $_POST['store_owner'] != '') {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_OWNER'";
        $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner'], 'string');
        $db->execute($sql);
        $store_owner = zen_output_string_protected($_POST['store_owner']);
    }
    if (isset($_POST['store_owner_email']) && $_POST['store_owner_email'] != '') {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO',
                                                'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO',
                                                'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
        $sql = $db->bindVars($sql, ':configValue:', $_POST['store_owner_email'], 'string');
        $db->execute($sql);
        $store_owner_email = zen_output_string_protected($_POST['store_owner_email']);
    }
    if (isset($_POST['zone_country_id']) && $_POST['zone_country_id'] != '') {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
        $sql =$db->bindVars($sql, ':configValue:', $_POST['zone_country_id'], 'integer');
        $db->execute($sql);
        $store_country = (int)($_POST['zone_country_id']);
    }
    $store_zone = '';
    if (isset($_POST['zone_id']) && $_POST['zone_id'] != '') {
        $store_zone = (int)($_POST['zone_id']);
    }
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_ZONE'";
    $sql = $db->bindVars($sql, ':configValue:', $store_zone, 'integer');
    $db->execute($sql);

    if (isset($_POST['store_address']) && $_POST['store_address'] != '') {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME_ADDRESS'";
        $sql = $db->bindVars($sql, ':configValue:', $_POST['store_address'], 'string');
        $db->execute($sql);
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

$country_string = zen_draw_pull_down_menu('zone_country_id', zen_get_countries(), $store_country, 'id="zone_country_id"" onChange="update_zone(this.form);"');
$zone_string = zen_draw_pull_down_menu('zone_id', zen_get_country_zones($store_country), $store_zone,
    'id="zone_id"');

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <meta name="robots" content="noindex, nofollow" />
    <script language="JavaScript" src="includes/menu.js" type="text/JavaScript"></script>
    <link href="includes/stylesheet.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS" />
    <link rel="stylesheet" type="text/css" href="includes/admin_access.css" />
    <script type="text/javascript">
        <!--
        function init()
        {
            cssjsmenu('navbar');
            if (document.getElementById)
            {
                var kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
        }
        function update_zone(theForm) {
            // if there is no zone_id field to update, or if it is hidden from display, then exit performing no updates
            if (!theForm || !theForm.elements["zone_id"]) return;
            if (theForm.zone_id.type == "hidden") return;

            // set initial values
            var SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;
            var SelectedZone = theForm.elements["zone_id"].value;

            // reset the array of pulldown options so it can be repopulated
            var NumState = theForm.zone_id.options.length;
            while(NumState > 0) {
                NumState = NumState - 1;
                theForm.zone_id.options[NumState] = null;
            }
            // build dynamic list of countries/zones for pulldown
            <?php echo zen_js_zone_list('SelectedCountry', 'theForm', 'zone_id', false); ?>

            // if we had a value before reset, set it again
            if (SelectedZone != "") theForm.elements["zone_id"].value = SelectedZone;

        }

        // -->
    </script>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<div id="pageWrapper">
    <h1><?php echo HEADING_TITLE_WIZARD; ?></h1>
    <p><?php echo TEXT_STORE_DETAILS; ?></p>
    <?php echo zen_draw_form('setup_wizard', FILENAME_DEFAULT, 'action=update', 'post', 'id="setupWizardForm"'); ?>
    <div>
        <label for="store_name"><?php echo TEXT_STORE_NAME ?></label>
        <?php echo zen_draw_input_field('store_name', $store_name, ' id="store_name" autofocus="autofocus"');?>
    </div>
    <br>
    <div>
        <label for="store_owner"><?php echo TEXT_STORE_OWNER ?></label>
        <?php echo zen_draw_input_field('store_owner', $store_owner, ' id="store_owner"');?>
    </div>
    <br>
    <div>
        <label for="store_owner_email"><?php echo TEXT_STORE_OWNER_EMAIL ?></label>
        <?php echo zen_draw_input_field('store_owner_email', $store_owner_email, ' id="store_owner_email"');?>
    </div>
    <br>
    <div>
        <label for="zone_country_id"><?php echo TEXT_STORE_COUNTRY ?></label>
        <?php echo $country_string; ?>
    </div>
    <br>
    <div>
        <label for="zone_id"><?php echo TEXT_STORE_ZONE ?></label>
        <?php echo $zone_string; ?>
    </div>
    <br>
    <div>
        <label for="store_address"><?php echo TEXT_STORE_ADDRESS ?></label>
        <textarea rows="5" cols="50"  id="store_address" name="store_address"><?php echo $store_address; ?></textarea>
    </div>
    <br>
    <div>
        <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE, 'id="button"') ?>
    </div>
    </form>
</div>
