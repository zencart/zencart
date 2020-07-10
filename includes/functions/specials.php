<?php
/**
 * product-specials functions
 *
 * @package functions
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: specials.php 18695 2011-05-04 05:24:19Z drbyte $
 */

////
// Set the status of a product on special
  function zen_set_specials_status($specials_id, $status) {
    global $db;
    $sql = "update " . TABLE_SPECIALS . "
            set status = '" . (int)$status . "', date_status_change = now()
            where specials_id = '" . (int)$specials_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on special
  function zen_expire_specials() {
    global $db;

    $date_range = time();
    $zc_specials_date = date('Ymd', $date_range);

    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '1'
                       and ((" . $zc_specials_date . " >= expires_date and expires_date != '0001-01-01')
                       or (" . $zc_specials_date . " < specials_date_available and specials_date_available != '0001-01-01'))";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '0');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }
  }

////
// Auto start products on special
  function zen_start_specials() {
    global $db;

    $date_range = time();
    $zc_specials_date = date('Ymd', $date_range);

// turn on special if active
    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '0'
                       and (((specials_date_available <= " . $zc_specials_date . " and specials_date_available != '0001-01-01') and (expires_date > " . $zc_specials_date . "))
                       or ((specials_date_available <= " . $zc_specials_date . " and specials_date_available != '0001-01-01') and (expires_date = '0001-01-01'))
                       or (specials_date_available = '0001-01-01' and expires_date > " . $zc_specials_date . "))
                       ";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '1');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }

// turn off special if not active yet
    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '1'
                       and (" . $zc_specials_date . " < specials_date_available and specials_date_available != '0001-01-01')
                       ";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '0');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }
  }
?>