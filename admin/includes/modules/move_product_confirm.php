<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista Apr 21 2020 Modified in v1.5.7 $
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

    $messageStack->add_session(sprintf(TEXT_PRODUCT_MOVED, $products_id, zen_get_products_name($products_id), $new_parent_id, zen_get_category_name($new_parent_id, $_SESSION['languages_id'])), 'success');

  // reset master_categories_id if moved from original master category
  $check_master = $db->Execute("SELECT products_id, master_categories_id
                                FROM " . TABLE_PRODUCTS . "
                                WHERE products_id = " . (int)$products_id);
  if ($check_master->fields['master_categories_id'] == (int)$current_category_id) {
    $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                  SET master_categories_id = " . (int)$new_parent_id . "
                  WHERE products_id = " . (int)$products_id);
      $messageStack->add_session(sprintf(TEXT_PRODUCT_MASTER_CATEGORY_RESET, $products_id, zen_get_products_name($products_id), $new_parent_id, zen_get_categories_name_from_product((int)$products_id)), 'caution');
  }

  // reset products_price_sorter for searches etc.
  zen_update_products_price_sorter((int)$products_id);
  zen_record_admin_activity('Moved product ' . (int)$products_id . ' from category ' . (int)$current_category_id . ' to category ' . (int)$new_parent_id, 'notice');
} else {
  $messageStack->add_session(ERROR_CANNOT_MOVE_PRODUCT_TO_CATEGORY_SELF, 'error');
}

if (!isset($action) || $action !== 'multiple_product_copy_return') {
zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $new_parent_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
}

