<?php
/**
 * featured-products functions
 *
 * @package functions
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: featured.php 18695 2011-05-04 05:24:19Z drbyte $
 */

////
// Set the status of a featured product
  function zen_set_featured_status($featured_id, $status) {
    global $db;
    $sql = "update " . TABLE_FEATURED . "
            set status = '" . (int)$status . "', date_status_change = now()
            where featured_id = '" . (int)$featured_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on featured
  function zen_expire_featured() {
    global $db;

    $date_range = time();
    $zc_featured_date = date('Ymd', $date_range);

    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '1'
                       and ((" . $zc_featured_date . " >= expires_date and expires_date != '0001-01-01')
                       or (" . $zc_featured_date . " < featured_date_available and featured_date_available != '0001-01-01'))";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '0');
        $featured->MoveNext();
      }
    }
  }

////
// Auto start products on featured
  function zen_start_featured() {
    global $db;

    $date_range = time();
    $zc_featured_date = date('Ymd', $date_range);

    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '0'
                       and (((featured_date_available <= " . $zc_featured_date . " and featured_date_available != '0001-01-01') and (expires_date > " . $zc_featured_date . "))
                       or ((featured_date_available <= " . $zc_featured_date . " and featured_date_available != '0001-01-01') and (expires_date = '0001-01-01'))
                       or (featured_date_available = '0001-01-01' and expires_date > " . $zc_featured_date . "))
                       ";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '1');
        $featured->MoveNext();
      }
    }

// turn off featured if not active yet
    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '1'
                       and (" . $zc_featured_date . " < featured_date_available and featured_date_available != '0001-01-01')
                       ";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '0');
        $featured->MoveNext();
      }
    }

  }
?>