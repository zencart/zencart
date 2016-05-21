<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: specials.php ajeh  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (!isset($_GET['reset_specials_sort_order'])) {
    $reset_specials_sort_order = $_SESSION['specials_sort_order'];
  }

  zen_set_time_limit(900);

  if (zen_not_null($action)) {
    switch ($action) {
      case 'set_specials_sort_order':
        $_SESSION['specials_sort_order'] = $_GET['reset_specials_sort_order'];
        $action='';
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) && $_GET['sID'] > 0 ? 'sID=' . $_GET['sID'] : '') . (isset($_GET['search']) && trim($_GET['search']) != ''  ? '&search=' . $_GET['search'] : '')));
        break;
      case 'setflag':
        if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0))
        {
          zen_set_specials_status($_GET['id'], $_POST['flag']);
          // reset products_price_sorter for searches etc.
          $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . (int)$_GET['id'] . "'");
          zen_update_products_price_sorter($update_price->fields['products_id']);
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $_GET['id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        }
        break;
      case 'insert':
        if ($_POST['products_id'] < 1) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
        } else {
        $products_id = zen_db_prepare_input($_POST['products_id']);
        $products_price = zen_db_prepare_input($_POST['products_price']);

        $tmp_value = zen_db_prepare_input($_POST['specials_price']);
        $specials_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

        if (substr($specials_price, -1) == '%') {
          $new_special_insert = $db->Execute("select products_id, products_price, products_priced_by_attribute
                                              from " . TABLE_PRODUCTS . "
                                              where products_id = '" . (int)$products_id . "'");

// check if priced by attribute
          if ($new_special_insert->fields['products_priced_by_attribute'] == '1') {
            $products_price = zen_get_products_base_price($products_id);
          } else {
            $products_price = $new_special_insert->fields['products_price'];
          }

          $specials_price = ($products_price - (($specials_price / 100) * $products_price));
        }

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['end']));

        $db->Execute("insert into " . TABLE_SPECIALS . "
                    (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                    values ('" . (int)$products_id . "',
                            '" . zen_db_input($specials_price) . "',
                            now(),
                            '" . zen_db_input($expires_date) . "', '1', '" . zen_db_input($specials_date_available) . "')");

        $new_special = $db->Execute("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "'");

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter((int)$products_id);

        } // nothing selected
        if ($_GET['go_back'] == 'ON'){
          zen_redirect(zen_admin_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_id . '&current_category_id=' . $_GET['current_category_id']));
        } else {
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $new_special->fields['specials_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        }
        break;
      case 'insert_category':
        $skip_specials = ($_POST['skip_specials'] == 'skip_specials_yes');
        $price_range_from = $_POST['price_range_from'];
        $price_range_to = $_POST['price_range_to'];
        $include_subcategories = ($_POST['include_subcategories'] == 'include_subcategories_yes');
        $include_inactive = ($_POST['include_inactive'] == 'include_inactive_yes');
        if ($_POST['categories_id'] < 1 || empty($_POST['specials_price'])) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED_CATEGORY, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        } else {
          // get all category products_id
          global $categories_products_id_list;
          $categories_products_id_list = '';
          $products_id_list = zen_get_categories_products_list($_POST['categories_id'], $include_inactive, $include_subcategories);
          if (is_array($products_id_list) && sizeof($products_id_list) > 0) {
            $special_added = false;
            // build products list
            foreach($products_id_list as $key => $value) {
              $new_specials_products_id = $value;
              $products_id = zen_db_prepare_input($new_specials_products_id);
              // check if a price range is set
              if ($price_range_from > 0 || $price_range_to > 0) {
                $products_price_normal = zen_get_products_base_price($products_id);
                if ($products_price_normal < $price_range_from || $products_price_normal > $price_range_to) {
                  // skip adding the special as products_price is out of price range
                  continue;
                }
              }

              $chk_special_query = "SELECT products_id from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'";
              $chk_special = $db->Execute($chk_special_query);
              // check if product has a special and skip if skip_specials
              if (!$chk_special->EOF) {
                if ($skip_specials) {
                  continue;
                } else {
                  $db->Execute("DELETE from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'");
                }
              }

              $tmp_value = zen_db_prepare_input($_POST['specials_price']);
              $specials_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

              if (substr($specials_price, -1) == '%') {
                $new_special_insert = $db->Execute("select products_id, products_price, products_priced_by_attribute
                                                    from " . TABLE_PRODUCTS . "
                                                    where products_id = '" . (int)$products_id . "'");

                // check if priced by attribute
                if ($new_special_insert->fields['products_priced_by_attribute'] == '1') {
                  $products_price = zen_get_products_base_price($products_id);
                } else {
                  $products_price = $new_special_insert->fields['products_price'];
                }

                $specials_price = ($products_price - (($specials_price / 100) * $products_price));
              }

              $specials_date_available = ((zen_db_prepare_input($_POST['special_start_date']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['special_start_date']));
              $expires_date = ((zen_db_prepare_input($_POST['special_end_date']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['special_end_date']));

              $db->Execute("insert into " . TABLE_SPECIALS . "
                          (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                          values ('" . (int)$products_id . "',
                                  '" . zen_db_input($specials_price) . "',
                                  now(),
                                  '" . zen_db_input($expires_date) . "', '1', '" . zen_db_input($specials_date_available) . "')");
              $special_added = true;

//@@TODO - remove $new_special not used?
//              $new_special = $db->Execute("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "'");
              // reset products_price_sorter for searches etc.
              zen_update_products_price_sorter((int)$products_id);
            }
            if ($special_added) {
              $messageStack->add_session(SUCCESS_SPECIALS_UPDATED_CATEGORY . $_POST['categories_id'] . ' ' . SUCCESS_SPECIALS_PRICE_SET . $_POST['specials_price'], 'success');
            } else {
              $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_CATEGORY_SUB, $_POST['categories_id']) . ' ' . SUCCESS_SPECIALS_PRICE_SET . $_POST['specials_price'], 'caution');
            }
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
          } else {
            $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_CATEGORY_SUB, $_POST['categories_id']), 'caution');
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
          }
        } // nothing selected
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
        break;
      case 'remove_category':
        $include_subcategories = ($_POST['include_subcategories'] == 'include_subcategories_yes');
        $include_inactive = ($_POST['include_inactive'] == 'include_inactive_yes');

        if ($_POST['categories_id'] < 1) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED_CATEGORY, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        } else {
          // get all category products_id
          global $categories_products_id_list;
          $categories_products_id_list = '';
          $products_id_list = zen_get_categories_products_list($_POST['categories_id'], $include_inactive, $include_subcategories);
          if (is_array($products_id_list) && sizeof($products_id_list) > 0) {
            $special_removed = false;
            // build products list
            foreach($products_id_list as $key => $value) {
              $new_specials_products_id = $value;
              $products_id = zen_db_prepare_input($new_specials_products_id);
              $chk_special_query = "SELECT products_id from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'";
              $chk_special = $db->Execute($chk_special_query);
              // check if product has a special
              if (!$chk_special->EOF) {
                $db->Execute("DELETE from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'");
                $special_removed = true;
                // reset products_price_sorter for searches etc.
                zen_update_products_price_sorter((int)$products_id);
              }
            }
            if ($special_removed) {
              $messageStack->add_session(SUCCESS_SPECIALS_REMOVED_CATEGORY . $_POST['categories_id'], 'success');
            } else {
              $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_CATEGORY_SUB, $_POST['categories_id']), 'caution');
            }
          } else {
            $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_CATEGORY_SUB, $_POST['categories_id']), 'caution');
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
          }
        } // nothing selected
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
        break;

      case 'insert_manufacturer':
        $skip_specials = ($_POST['skip_specials'] == 'skip_specials_yes');
        $price_range_from = $_POST['price_range_from'];
        $price_range_to = $_POST['price_range_to'];

        if ($_POST['manufacturer_id'] < 1 || empty($_POST['specials_price'])) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED_MANUFACTURER, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        } else {
          // build $products_id_list based on manufacturer_id
          $chk_special_query = "SELECT products_id from " . TABLE_PRODUCTS . " WHERE manufacturers_id = '" . (int)$_POST['manufacturer_id'] . "'";
          $chk_special = $db->Execute($chk_special_query);
          $products_id_list = array();
          while (!$chk_special->EOF) {
            $products_id_list[] = $chk_special->fields['products_id'];
            $chk_special->MoveNext();
          }
          if (is_array($products_id_list) && sizeof($products_id_list) > 0) {
            $special_added = false;
            // build products list
            foreach($products_id_list as $key => $value) {
              $new_specials_products_id = $value;
              $products_id = zen_db_prepare_input($new_specials_products_id);
              // check if a price range is set
              if ($price_range_from > 0 || $price_range_to > 0) {
                $products_price_normal = zen_get_products_base_price($products_id);
                if ($products_price_normal < $price_range_from || $products_price_normal > $price_range_to) {
                  // skip adding the special as products_price is out of price range
                  continue;
                }
              }
              $chk_special_query = "SELECT products_id from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'";
              $chk_special = $db->Execute($chk_special_query);
              // check if product has a special and skip if skip_specials
              if (!$chk_special->EOF) {
                if ($skip_specials) {
                  continue;
                } else {
                  $db->Execute("DELETE from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'");
                }
              }

              $tmp_value = zen_db_prepare_input($_POST['specials_price']);
              $specials_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

              if (substr($specials_price, -1) == '%') {
                $new_special_insert = $db->Execute("select products_id, products_price, products_priced_by_attribute
                                                    from " . TABLE_PRODUCTS . "
                                                    where products_id = '" . (int)$products_id . "'");

                // check if priced by attribute
                if ($new_special_insert->fields['products_priced_by_attribute'] == '1') {
                  $products_price = zen_get_products_base_price($products_id);
                } else {
                  $products_price = $new_special_insert->fields['products_price'];
                }

                $specials_price = ($products_price - (($specials_price / 100) * $products_price));
              }

              $specials_date_available = ((zen_db_prepare_input($_POST['special_start_date']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['special_start_date']));
              $expires_date = ((zen_db_prepare_input($_POST['special_end_date']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['special_end_date']));

              $db->Execute("insert into " . TABLE_SPECIALS . "
                          (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                          values ('" . (int)$products_id . "',
                                  '" . zen_db_input($specials_price) . "',
                                  now(),
                                  '" . zen_db_input($expires_date) . "', '1', '" . zen_db_input($specials_date_available) . "')");
              $special_added = true;

//@@TODO - remove $new_special not used?
//              $new_special = $db->Execute("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "'");
              // reset products_price_sorter for searches etc.
              zen_update_products_price_sorter((int)$products_id);
            }
            if ($special_added) {
              $messageStack->add_session(SUCCESS_SPECIALS_UPDATED_MANUFACTURER . $_POST['manufacturer_id'] . ' ' . SUCCESS_SPECIALS_PRICE_SET . $_POST['specials_price'], 'success');
            } else {
              $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_MANUFACTURER_SUB, $_POST['manufacturer_id']), 'caution');
            }
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
          } else {
            $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_MANUFACTURER_SUB, $_POST['manufacturer_id']), 'caution');
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
          }
        } // nothing selected
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
        break;
      case 'remove_manufacturer':
        if ($_POST['manufacturer_id'] < 1) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED_MANUFACTURER, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        } else {
          // build $products_id_list based on manufacturer_id
          $chk_special_query = "SELECT products_id from " . TABLE_PRODUCTS . " WHERE manufacturers_id = '" . (int)$_POST['manufacturer_id'] . "'";
          $chk_special = $db->Execute($chk_special_query);
          $products_id_list = array();
          while (!$chk_special->EOF) {
            $products_id_list[] = $chk_special->fields['products_id'];
            $chk_special->MoveNext();
          }
          if (is_array($products_id_list) && sizeof($products_id_list) > 0) {
            $special_removed = false;
            // build products list
            foreach($products_id_list as $key => $value) {
              $new_specials_products_id = $value;
              $products_id = zen_db_prepare_input($new_specials_products_id);
              $chk_special_query = "SELECT products_id from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'";
              $chk_special = $db->Execute($chk_special_query);
              // check if product has a special
              if (!$chk_special->EOF) {
                $db->Execute("DELETE from " . TABLE_SPECIALS . " WHERE products_id = '" . $products_id . "'");
                $special_removed = true;
                // reset products_price_sorter for searches etc.
                zen_update_products_price_sorter((int)$products_id);
              }
            }
            if ($special_removed) {
              $messageStack->add_session(SUCCESS_SPECIALS_REMOVED_MANUFACTURER . $_POST['manufacturer_id'], 'success');
            } else {
              $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_MANUFACTURER_SUB, $_POST['manufacturer_id']), 'caution');
            }
          } else {
            $messageStack->add_session(sprintf(ERROR_NOTHING_SELECTED_MANUFACTURER_SUB, $_POST['manufacturer_id']), 'caution');
            zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
          }
        } // nothing selected
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS));
        break;
      case 'update':
        $specials_id = zen_db_prepare_input($_POST['specials_id']);

        if ($_POST['products_priced_by_attribute'] == '1') {
          $products_price = zen_get_products_base_price($_POST['update_products_id']);
        } else {
          $products_price = zen_db_prepare_input($_POST['products_price']);
        }

        $tmp_value = zen_db_prepare_input($_POST['specials_price']);
        $specials_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

        if (substr($specials_price, -1) == '%') $specials_price = ($products_price - (($specials_price / 100) * $products_price));

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['end']));

        $db->Execute("update " . TABLE_SPECIALS . "
                      set specials_new_products_price = '" . zen_db_input($specials_price) . "',
                          specials_last_modified = now(),
                          expires_date = '" . zen_db_input($expires_date) . "',
                          specials_date_available = '" . zen_db_input($specials_date_available) . "'
                      where specials_id = '" . (int)$specials_id . "'");

        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");
        zen_update_products_price_sorter($update_price->fields['products_id']);

        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $specials_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        }
        $specials_id = zen_db_prepare_input($_POST['sID']);

        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");
        $update_price_id = $update_price->fields['products_id'];

        $db->Execute("delete from " . TABLE_SPECIALS . "
                      where specials_id = '" . (int)$specials_id . "'");

        zen_update_products_price_sorter($update_price_id);

        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        break;
      case 'pre_add_confirmation':
      // check for blank or existing special
        $skip_special = false;
        if (empty($_POST['pre_add_products_id'])) {
          $skip_special = true;
          $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_EMPTY, 'caution');
        }

        if ($skip_special == false) {
          $sql = "select products_id, products_model from " . TABLE_PRODUCTS . " where products_id='" . (int)$_POST['pre_add_products_id'] . "'";
          $check_special = $db->Execute($sql);
          if ((!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL == 'false') && ($check_special->RecordCount() < 1 || substr($check_special->fields['products_model'], 0, 4) == 'GIFT')) {
            $skip_special = true;
            $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_BAD_PRODUCTS_ID, 'caution');
          }
        }

        if ($skip_special == false) {
          $sql = "select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$_POST['pre_add_products_id'] . "'";
          $check_special = $db->Execute($sql);
          if ($check_special->RecordCount() > 0) {
            $skip_special = true;
            $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_DUPLICATE, 'caution');
          }
        }

        if ($skip_special == true) {
          zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . ($check_special->fields['specials_id'] > 0 ? 'sID=' . $check_special->fields['specials_id'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
        }
      // add empty special

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_format_date_raw($_POST['end']));

        $products_id = zen_db_prepare_input($_POST['pre_add_products_id']);
        $db->Execute("insert into " . TABLE_SPECIALS . "
                    (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                    values ('" . (int)$products_id . "',
                            '" . zen_db_input($specials_price) . "',
                            now(),
                            '" . zen_db_input($expires_date) . "', '1', '" . zen_db_input($specials_date_available) . "')");

        $new_special = $db->Execute("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "'");

        $messageStack->add_session(SUCCESS_SPECIALS_PRE_ADD, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_SPECIALS, 'action=edit' . '&sID=' . $new_special->fields['specials_id'] . '&manual=1'));
        break;
    }
  }
require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
         <tr><?php echo zen_draw_form('search', FILENAME_SPECIALS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
  }
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
            </td>
            <td class="smallText" align="right">
<?php
// add Sort Order here
// $_SESSION['specials_sort_order']
// toggle switch for display sort order
// order of display
        $specials_sort_order_array = array(array('id' => '0', 'text' => TEXT_SORT_NAME_MODEL),
                              array('id' => '1', 'text' => TEXT_SORT_PRODUCTS_ID),
                              array('id' => '2', 'text' => TEXT_SORT_MODEL_NAME),
                              array('id' => '3', 'text' => TEXT_SORT_AVAILABLE_DESC_NAME),
                              array('id' => '4', 'text' => TEXT_SORT_AVAILABLE_ASC_NAME),
                              array('id' => '5', 'text' => TEXT_SORT_EXPIRE_DESC_NAME),
                              array('id' => '6', 'text' => TEXT_SORT_EXPIRE_ASC_NAME),
                              array('id' => '7', 'text' => TEXT_SORT_STATUS_NAME_DESC_NAME),
                              array('id' => '8', 'text' => TEXT_SORT_STATUS_NAME_ASC_NAME)
                              );
        echo TEXT_SORT_SPECIALS_TITLE_INFO . zen_draw_form('set_specials_sort_order_form', FILENAME_SPECIALS, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_specials_sort_order', $specials_sort_order_array, $reset_specials_sort_order, 'onChange="this.form.submit();"') . zen_hide_session_id() .
        ($_GET['sID'] != '' ? zen_draw_hidden_field('sID', $_GET['sID']) : '') .
        zen_draw_hidden_field('action', 'set_specials_sort_order') .
        '</form>';
?>
            </td>
          </form></tr>
          <tr>
            <td colspan="3" class="main"><?php echo TEXT_STATUS_WARNING; ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if (empty($action)) {
?>
                    <td align="center"><?php echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new') . '">' . zen_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?></td>
<?php
  }
?>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
    $form_action = 'insert';
    if ( ($action == 'edit') && isset($_GET['sID']) ) {
      $form_action = 'update';

      $product = $db->Execute("select p.products_id, pd.products_name, p.products_price, p.products_priced_by_attribute,
                                      s.specials_new_products_price, s.expires_date, s.specials_date_available
                               from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                                        TABLE_SPECIALS . " s
                               where p.products_id = pd.products_id
                               and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                               and p.products_id = s.products_id
                               and s.specials_id = '" . (int)$_GET['sID'] . "'");

      $sInfo = new objectInfo($product->fields);

      if ($sInfo->products_priced_by_attribute == '1') {
        $sInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
      }

    } else {
      $sInfo = new objectInfo(array());

// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
      $specials_array = array();
      $specials = $db->Execute("select p.products_id, p.products_model
                                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                                where s.products_id = p.products_id");

      while (!$specials->EOF) {
        $specials_array[] = $specials->fields['products_id'];
        $specials->MoveNext();
      }

// never include Gift Vouchers for specials when set to false
      if (!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL == 'false') {
        $gift_vouchers = $db->Execute("select distinct p.products_id, p.products_model
                                  from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                                  where p.products_model rlike '" . "GIFT" . "'");

        while (!$gift_vouchers->EOF) {
          if(substr($gift_vouchers->fields['products_model'], 0, 4) == 'GIFT') {
            $specials_array[] = $gift_vouchers->fields['products_id'];
          }
          $gift_vouchers->MoveNext();
        }
      }
// do not include things that cannot go in the cart
      $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");

      while (!$not_for_cart->EOF) {
        $specials_array[] = $not_for_cart->fields['products_id'];
        $not_for_cart->MoveNext();
      }
    }
?>
      <tr><?php echo zen_draw_form("new_special", FILENAME_SPECIALS, zen_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action . '&go_back=' . $_GET['go_back']); ?><?php if ($form_action == 'update') echo zen_draw_hidden_field('specials_id', $_GET['sID']); ?>
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_PRODUCT; ?>&nbsp;</td>
            <td class="main"><?php echo (isset($sInfo->products_name)) ? $sInfo->products_name . ' <small>(' . $currencies->format($sInfo->products_price) . ')</small>' : zen_draw_products_pull_down('products_id', 'size="15" style="font-size:12px"', $specials_array, true, $_GET['add_products_id'], true); echo zen_draw_hidden_field('products_price', (isset($sInfo->products_price) ? $sInfo->products_price : '')); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('specials_price', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : '')); echo zen_draw_hidden_field('products_priced_by_attribute', $sInfo->products_priced_by_attribute); echo zen_draw_hidden_field('update_products_id', $sInfo->products_id); ?></td>
          </tr>

          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_AVAILABLE_DATE; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('start', (($sInfo->specials_date_available == '0001-01-01') ? '' : zen_date_short($sInfo->specials_date_available)), 'class="datepicker"');  ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_EXPIRES_DATE; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('end', (($sInfo->expires_date == '0001-01-01') ? '' : zen_date_short($sInfo->expires_date)), 'class="datepicker"'); ?></td>
          </tr>

        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><br><?php echo TEXT_SPECIALS_PRICE_TIP; ?></td>
            <td class="main" align="right" valign="top"><br><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)) . ((int)$_GET['manual'] == 0 ? '&nbsp;&nbsp;&nbsp;<a href="' . ($_GET['go_back'] == 'ON' ? zen_admin_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['add_products_id'] . '&current_category_id=' . $_GET['current_category_id']) : zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . ((isset($_GET['sID']) and $_GET['sID'] != '') ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' : ''); ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="right"><?php echo 'ID#'; ?>&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
                <td colspan="2" class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_AVAILABLE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_EXPIRES_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;&nbsp;</td>
              </tr>
<?php
// create search filter
  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $search = " and (pd.products_name like '%" . $keywords . "%' or pd.products_description like '%" . $keywords . "%' or p.products_model like '%" . $keywords . "%')";
  }

//$_SESSION['specials_sort_order'] = 2;
switch ((int)$_SESSION['specials_sort_order']) {
  case (0):
    $order_by = " order by pd.products_name, p.products_model ";
    break;
  case (1):
    $order_by = " order by p.products_id ";
    break;
  case (2):
    $order_by = " order by p.products_model, pd.products_name ";
    break;
  case (3):
    $order_by = " order by s.specials_date_available DESC, pd.products_name ";
    break;
  case (4):
    $order_by = " order by s.specials_date_available ASC, pd.products_name ";
    break;
  case (5):
    $order_by = " order by s.expires_date DESC, pd.products_name ";
    break;
  case (6):
    $order_by = " order by s.expires_date ASC, pd.products_name ";
    break;
  case (7):
    $order_by = " order by s.status DESC, pd.products_name ";
    break;
  case (8):
    $order_by = " order by s.status ASC, pd.products_name ";
    break;
  default:
    $order_by = " order by pd.products_name, p.products_model ";
    break;
}
  $specials_query_raw = "select p.products_id, pd.products_name, p.products_model, p.products_price, p.products_priced_by_attribute, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status, s.specials_date_available from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = s.products_id" . $search . $order_by;

// Split Page
// reset page when page is unknown
if (($_GET['page'] == '1' or $_GET['page'] == '') and $_GET['sID'] != '') {
  $old_page = $_GET['page'];
  $check_page = $db->Execute($specials_query_raw);
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
    $check_count=1;
    while (!$check_page->EOF) {
      if ($check_page->fields['specials_id'] == $_GET['sID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS) !=0 ? .5 : 0)),0);
    $page = $_GET['page'];
    if ($old_page != $_GET['page']) {
// do nothing
    }
  } else {
    $_GET['page'] = 1;
  }
}

// create split page control
    $specials_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
    $specials = $db->Execute($specials_query_raw);
    while (!$specials->EOF) {
      if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $specials->fields['specials_id']))) && !isset($sInfo)) {
        $products = $db->Execute("select products_image
                                  from " . TABLE_PRODUCTS . "
                                  where products_id = '" . (int)$specials->fields['products_id'] . "'");

        $sInfo_array = array_merge($specials->fields, $products->fields);
        $sInfo = new objectInfo($sInfo_array);
      }

      if (isset($sInfo) && is_object($sInfo) && ($specials->fields['specials_id'] == $sInfo->specials_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'">' . "\n";
      }

      if ($specials->fields['products_priced_by_attribute'] == '1') {
        $specials_current_price = zen_get_products_base_price($specials->fields['products_id']);
      } else {
        $specials_current_price = $specials->fields['products_price'];
      }

      $sale_price = zen_get_products_special_price($specials->fields['products_id'], false);

?>
                <td  class="dataTableContent" align="right"><?php echo $specials->fields['products_id']; ?>&nbsp;</td>
                <td  class="dataTableContent"><?php echo $specials->fields['products_name']; ?></td>
                <td  class="dataTableContent" align="left"><?php echo $specials->fields['products_model']; ?>&nbsp;</td>
                <td colspan="2" class="dataTableContent" align="right"><?php echo zen_get_products_display_price($specials->fields['products_id']); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($specials->fields['specials_date_available'] != '0001-01-01' and $specials->fields['specials_date_available'] !='') ? zen_date_short($specials->fields['specials_date_available']) : TEXT_NONE); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($specials->fields['expires_date'] != '0001-01-01' and $specials->fields['expires_date'] !='') ? zen_date_short($specials->fields['expires_date']) : TEXT_NONE); ?></td>
                <td  class="dataTableContent" align="center">
<?php
      if ($specials->fields['status'] == '1') {
        echo zen_draw_form('setflag_products', FILENAME_SPECIALS, 'action=setflag&id=' . $specials->fields['specials_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
        <input type="hidden" name="flag" value="0" />
        </form>
<?php
      } else {
        echo zen_draw_form('setflag_products', FILENAME_SPECIALS, 'action=setflag&id=' . $specials->fields['specials_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
        <input type="hidden" name="flag" value="1" />
        </form>
<?php
      }
?>
                </td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($sInfo) && is_object($sInfo) && ($specials->fields['specials_id'] == $sInfo->specials_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, zen_get_all_get_params(array('sID')) . 'sID=' . $specials->fields['specials_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
      </tr>
<?php
      $specials->MoveNext();
    }
?>
              <tr>
                <td colspan="8"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
                    <td class="smallText" align="right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params( array( 'page', 'sID' ))); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new') . '">' . zen_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>');

      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&action=deleteconfirm' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . zen_draw_hidden_field('sID', $sInfo->specials_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $sInfo->products_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pre_add':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_PRE_ADD_SPECIALS . '</b>');
      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=pre_add_confirmation' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      $contents[] = array('text' => TEXT_INFO_PRE_ADD_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_PRE_ADD_PRODUCTS_ID . '<br>' . zen_draw_input_field('pre_add_products_id', '', zen_set_field_length(TABLE_SPECIALS, 'products_id')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . (int)$_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pre_add_category':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_PRE_ADD_SPECIALS_CATEGORY . '</b>');
      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=insert_category' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      $contents[] = array('text' => TEXT_INFO_PRE_ADD_INTRO_CATEGORY . '<br />');
      $contents[] = array('text' => '<br />' . TEXT_PRE_ADD_CATEGORY_ID . '<br>' . zen_draw_input_field('categories_id'));

      $contents[] = array('text' => TEXT_INFO_INCLUDE_SUBCATEGORIES . '<br />' . zen_draw_radio_field('include_subcategories', 'include_subcategories_yes', true) . ' ' . TEXT_SKIP_SUBCATEGORIES . '<br />' . zen_draw_radio_field('include_subcategories', 'include_subcategories_no') . ' ' . TEXT_SKIP_SUBCATEGORIES_NO);
      $contents[] = array('text' => TEXT_INFO_INCLUDE_INACTIVE . '<br />' . zen_draw_radio_field('include_inactive', 'include_inactive_yes', true) . ' ' . TEXT_SKIP_INACTIVE . '<br />' . zen_draw_radio_field('include_inactive', 'include_inactive_no') . ' ' . TEXT_SKIP_INACTIVE_NO);
      $contents[] = array('text' => TEXT_INFO_SKIP_SPECIALS . '<br />' . zen_draw_radio_field('skip_specials', 'skip_specials_yes', true) . ' ' . TEXT_SKIP_SPECIALS_TRUE . '<br />' . zen_draw_radio_field('skip_specials', 'skip_specials_no') . ' ' . TEXT_SKIP_SPECIALS_FALSE);

      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_FROM . ' ' . zen_draw_input_field('price_range_from') . ' ' . TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_TO . ' ' . zen_draw_input_field('price_range_to'));

      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_PRICE . '<br>' . zen_draw_input_field('specials_price'));
      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_START_DATE . '<br>' . zen_draw_input_field('special_start_date', '', 'maxlength="10" class="datepicker"') . '&nbsp;' . SPECIALS_DATE_ERROR);
      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_END_DATE . '<br>' . zen_draw_input_field('special_end_date', '', 'maxlength="10" class="datepicker"') . '&nbsp;' . SPECIALS_DATE_ERROR);

      $contents[] = array('text' => TEXT_SPECIALS_PRICE_TIP);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pre_remove_category':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_PRE_REMOVE_SPECIALS_CATEGORY . '</b>');
      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=remove_category' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      $contents[] = array('text' => TEXT_INFO_PRE_REMOVE_INTRO_CATEGORY . '<br />');
      $contents[] = array('text' => '<br />' . TEXT_PRE_REMOVE_CATEGORY_ID . '<br>' . zen_draw_input_field('categories_id'));

      $contents[] = array('text' => TEXT_INFO_INCLUDE_SUBCATEGORIES . '<br />' . zen_draw_radio_field('include_subcategories', 'include_subcategories_yes', true) . ' ' . TEXT_SKIP_SUBCATEGORIES . '<br />' . zen_draw_radio_field('include_subcategories', 'include_subcategories_no') . ' ' . TEXT_SKIP_SUBCATEGORIES_NO);
      $contents[] = array('text' => TEXT_INFO_INCLUDE_INACTIVE . '<br />' . zen_draw_radio_field('include_inactive', 'include_inactive_yes', true) . ' ' . TEXT_SKIP_INACTIVE . '<br />' . zen_draw_radio_field('include_inactive', 'include_inactive_no') . ' ' . TEXT_SKIP_INACTIVE_NO);

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pre_add_manufacturer':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_PRE_ADD_SPECIALS_MANUFACTURER . '</b>');
      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=insert_manufacturer' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      $contents[] = array('text' => TEXT_INFO_PRE_ADD_INTRO_MANUFACTURER . '<br />');
      $contents[] = array('text' => '<br />' . TEXT_PRE_ADD_MANUFACTURER_ID . '<br>' . zen_draw_input_field('manufacturer_id'));
      $contents[] = array('text' => TEXT_INFO_SKIP_SPECIALS . '<br />' . zen_draw_radio_field('skip_specials', 'skip_specials_yes', true) . ' ' . TEXT_SKIP_SPECIALS_TRUE . '<br />' . zen_draw_radio_field('skip_specials', 'skip_specials_no') . ' ' . TEXT_SKIP_SPECIALS_FALSE);

      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_FROM . ' ' . zen_draw_input_field('price_range_from') . ' ' . TEXT_PRE_ADD_SPECIAL_PRICE_RANGE_TO . ' ' . zen_draw_input_field('price_range_to'));

      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_PRICE . '<br>' . zen_draw_input_field('specials_price'));
      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_START_DATE . '<br>' . zen_draw_input_field('special_start_date', '', 'maxlength="10" class="datepicker"') . '&nbsp;' . SPECIALS_DATE_ERROR);
      $contents[] = array('text' => TEXT_PRE_ADD_SPECIAL_END_DATE . '<br>' . zen_draw_input_field('special_end_date', '', 'maxlength="10" class="datepicker"') . '&nbsp;' . SPECIALS_DATE_ERROR);

      $contents[] = array('text' => TEXT_SPECIALS_PRICE_TIP);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'pre_remove_manufacturer':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_PRE_REMOVE_SPECIALS_MANUFACTURER . '</b>');
      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=remove_manufacturer' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      $contents[] = array('text' => TEXT_INFO_PRE_REMOVE_INTRO_MANUFACTURER . '<br />');
      $contents[] = array('text' => '<br />' . TEXT_PRE_REMOVE_MANUFACTURER_ID . '<br>' . zen_draw_input_field('manufacturer_id'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');

      if ($sInfo->products_priced_by_attribute == '1') {
        $specials_current_price = zen_get_products_base_price($sInfo->products_id);
      } else {
        $specials_current_price = $sInfo->products_price;
      }

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_filter=' . $sInfo->products_id) . '">' . zen_image_button('button_products_price_manager.gif', IMAGE_PRODUCTS_PRICE_MANAGER) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->specials_date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->specials_last_modified));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($specials_current_price));
        $contents[] = array('text' => '' . TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price));
        $contents[] = array('text' => '' . TEXT_INFO_DISPLAY_PRICE . ' ' . zen_get_products_display_price($sInfo->products_id));

        $contents[] = array('text' => '<br>' . TEXT_INFO_AVAILABLE_DATE . ' <b>' . (($sInfo->specials_date_available != '0001-01-01' and $sInfo->specials_date_available !='') ? zen_date_short($sInfo->specials_date_available) : TEXT_NONE) . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . (($sInfo->expires_date != '0001-01-01' and $sInfo->expires_date !='') ? zen_date_short($sInfo->expires_date) : TEXT_NONE) . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . zen_date_short($sInfo->date_status_change));
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_CATEGORIES, '&action=new_product' . '&cPath=' . zen_get_product_path($sInfo->products_id, 'override') . '&pID=' . $sInfo->products_id . '&product_type=' . zen_get_products_type($sInfo->products_id)) . '">' . zen_image_button('button_edit_product.gif', IMAGE_EDIT_PRODUCT) . '<br />' . TEXT_PRODUCT_EDIT . '</a>');
      } else {
        $heading[] = array('text' => '<b>' . TEXT_NONE . '</b>');
      }

      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'action=pre_add' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_select.gif', IMAGE_SELECT) . '<br />' . TEXT_INFO_MANUAL . '</a><br /><br />');
      $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3') . '<br /><br />');
      $contents[] = array('align' => 'center', 'text' => TEXT_INFO_CATEGORY . '<br />' . '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'action=pre_add_category' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_select.gif', IMAGE_SELECT) . '<br />' . TEXT_INFO_MANUAL_CATEGORY . '</a><br /><br />');
      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'action=pre_remove_category' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '<br />' . TEXT_INFO_MANUAL_CATEGORY_REMOVE . '</a><br /><br />');
      $contents[] = array('align' => 'center', 'text' => TEXT_INFO_MANUFACTURER . '<br />' . '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'action=pre_add_manufacturer' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_select.gif', IMAGE_SELECT) . '<br />' . TEXT_INFO_MANUAL_MANUFACTURER . '</a><br /><br />');
      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_SPECIALS, 'action=pre_remove_manufacturer' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . ((isset($_GET['sID']) && $_GET['sID'] > 0) ? '&sID=' . $_GET['sID'] : '') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '<br />' . TEXT_INFO_MANUAL_MANUFACTURER_REMOVE . '</a><br /><br />');

      break;
  }
  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
}
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<script>
  $(function() {
    $(".datepicker").datepicker();
  });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
