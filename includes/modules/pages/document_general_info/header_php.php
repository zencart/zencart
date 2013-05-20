<?php
/**
 * document_general header_php.php 
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 2978 2006-02-07 00:52:01Z drbyte $
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_DOCUMENT_GENERAL_INFO');

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

  // if specified product_id is disabled or doesn't exist, ensure that metatags and breadcrumbs don't share inappropriate information
  $sql = "select p.*, pd.*
           from   " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
           where  p.products_status = '1'
           and    p.products_id = '" . (int)$_GET['products_id'] . "'
           and    pd.products_id = p.products_id
           and    pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  $res = $db->Execute($sql);
  if ( $res->RecordCount() < 1 ) {
    unset($_GET['products_id']);
    unset($breadcrumb->_trail[sizeof($breadcrumb->_trail)-1]['title']);
    $robotsNoIndex = true;
    header('HTTP/1.1 404 Not Found');
  }

  // ensure navigation snapshot in case must-be-logged-in-for-price is enabled
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
  }

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_DOCUMENT_GENERAL_INFO');
?>