<?php
/**
 * Customer Authorization 
 *
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 2974 2006-02-05 04:53:19Z birdbrain $
 */


$sql = "SELECT customers_authorization 
        FROM " . TABLE_CUSTOMERS . " 
        WHERE customers_id = :customersID";

$sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
$check_customer = $db->Execute($sql);

$_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];

if ($_SESSION['customers_authorization'] != '1') {
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

if (CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF == 'true') $flag_disable_right = true;
if (CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF == 'true') $flag_disable_left = true;
if (CUSTOMERS_AUTHORIZATION_FOOTER_OFF == 'true') $flag_disable_footer = true;
if (CUSTOMERS_AUTHORIZATION_HEADER_OFF == 'true') $flag_disable_header = true;

?>