<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Wed Jan 17 12:01:19 2018 +0100 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$products_id = zen_db_prepare_input($_POST['products_id']);
$new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

$duplicate_check = $db->Execute("SELECT COUNT(*) AS total
                                 FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                 WHERE products_id = " . (int)$products_id . "
                                 AND categories_id = " . (int)$new_parent_id);

if ($duplicate_check->fields['total'] < 1) {
  $db->Execute("UPDATE " . TABLE_PRODUCTS_TO_CATEGORIES . "
                SET categories_id = " . (int)$new_parent_id . "
                WHERE products_id = " . (int)$products_id . "
                AND categories_id = " . (int)$current_category_id);

  // reset master_categories_id if moved from original master category
  $check_master = $db->Execute("SELECT products_id, master_categories_id
                                FROM " . TABLE_PRODUCTS . "
                                WHERE products_id = " . (int)$products_id);
  if ($check_master->fields['master_categories_id'] == (int)$current_category_id) {
    $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                  SET master_categories_id = " . (int)$new_parent_id . "
                  WHERE products_id = " . (int)$products_id);
  }

  // reset products_price_sorter for searches etc.
  zen_update_products_price_sorter((int)$products_id);
  zen_record_admin_activity('Moved product ' . (int)$products_id . ' from category ' . (int)$current_category_id . ' to category ' . (int)$new_parent_id, 'notice');
} else {
  $messageStack->add_session(ERROR_CANNOT_MOVE_PRODUCT_TO_CATEGORY_SELF, 'error');
}

zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $new_parent_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
