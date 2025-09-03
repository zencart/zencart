<?php
/**
 * Customer Authorization 
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!zen_is_logged_in()) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

$sql =
    "SELECT customers_authorization
       FROM " . TABLE_CUSTOMERS . "
      WHERE customers_id = :customersID";

$sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
$check_customer = $db->Execute($sql, 1);
if ($check_customer->EOF) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));  //- Shouldn't happen, but for completeness ...
}

$_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];

if ($_SESSION['customers_authorization'] !== '1' && $_SESSION['customers_authorization'] !== '3') {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(NAVBAR_TITLE);

$flag_disable_right ??= (CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF === 'true');
$flag_disable_left ??= (CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF === 'true');
$flag_disable_footer ??= (CUSTOMERS_AUTHORIZATION_FOOTER_OFF === 'true');
$flag_disable_header ??= (CUSTOMERS_AUTHORIZATION_HEADER_OFF === 'true');
