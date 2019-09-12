<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jan 29 Modified in v1.5.6b $
 */
require('includes/application_top.php');

// troubleshooting/debug of option name/value IDs:
$show_name_numbers = true;
$show_value_numbers = true;
// verify option names, values, products
$chk_option_names = $db->Execute("SELECT products_options_id
                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                  WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                  LIMIT 1");
if ($chk_option_names->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
  zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
}
$chk_option_values = $db->Execute("SELECT *
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND products_options_values_id != " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . "
                                   LIMIT 1");
if ($chk_option_values->RecordCount() < 1) {
  foreach ($chk_option_names as $chk_option_name) {
    if (!zen_option_name_base_expects_no_values($chk_option_name['products_options_id'])) {
      $messageStack->add_session(ERROR_DEFINE_OPTION_VALUES, 'caution');
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER));
    }
  }
}
$chk_products = $db->Execute("SELECT *
                              FROM " . TABLE_PRODUCTS . "
                              LIMIT 1");
if ($chk_products->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
}
// check for damaged database, caused by users indiscriminately deleting table data
$ary = array();
$chk_option_values = $db->Execute("SELECT DISTINCT language_id
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
foreach ($chk_option_values as $option_value) {
  $ary[] = $option_value['language_id'];
}
$languages = zen_get_languages();
for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
  if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary)) {
    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name)
                  VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
  }
}

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$_GET['products_filter'] = $products_filter = (isset($_GET['products_filter']) ? (int)$_GET['products_filter'] : (isset($products_filter) ? (int)$products_filter : 0));
$_GET['attributes_id'] = (isset($_GET['attributes_id']) ? (int)$_GET['attributes_id'] : 0);

$_GET['current_category_id'] = $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);
if (isset($_POST['products_filter'])) {
  $_POST['products_filter'] = (int)$_POST['products_filter'];
}
if (isset($_POST['current_category_id'])) {
  $_POST['current_category_id'] = (int)$_POST['current_category_id'];
}
if (isset($_POST['products_options_id_all'])) {
  $_POST['products_options_id_all'] = (int)$_POST['products_options_id_all'];
}
if (isset($_POST['current_category_id'])) {
  $_POST['current_category_id'] = (int)$_POST['current_category_id'];
}
if (isset($_POST['categories_update_id'])) {
  $_POST['categories_update_id'] = (int)$_POST['categories_update_id'];
}

if ($action == 'new_cat') {
  $sql = "SELECT products_id
          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
          WHERE categories_id = " . (int)$current_category_id . "
          ORDER BY products_id";
  $new_product_query = $db->Execute($sql);
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
  zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
}

// set categories and products if not set
if ($products_filter == '' && $current_category_id != '') {
  $sql = "SELECT *
          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
          WHERE categories_id = " . (int)$current_category_id . "
          ORDER BY products_id";
  $new_product_query = $db->Execute($sql);
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
  if ($products_filter != '') {
    zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }
} else {
  if ($products_filter == '' && $current_category_id == '') {
    $reset_categories_id = zen_get_category_tree('', '', '0', '', '', true);
    $current_category_id = $reset_categories_id[0]['id'];
    $sql = "SELECT *
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE categories_id = " . (int)$current_category_id . "
            ORDER BY products_id";
    $new_product_query = $db->Execute($sql);
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
    $_GET['products_filter'] = $products_filter;
  }
}

require(DIR_WS_MODULES . FILENAME_PREV_NEXT);

if (zen_not_null($action)) {
  $_SESSION['page_info'] = '';
  if (isset($_GET['option_page'])) {
    $_SESSION['page_info'] .= 'option_page=' . $_GET['option_page'] . '&';
  }
  if (isset($_GET['value_page'])) {
    $_SESSION['page_info'] .= 'value_page=' . $_GET['value_page'] . '&';
  }
  if (isset($_GET['attribute_page'])) {
    $_SESSION['page_info'] .= 'attribute_page=' . $_GET['attribute_page'] . '&';
  }
  if (isset($_GET['products_filter'])) {
    $_SESSION['page_info'] .= 'products_filter=' . $_GET['products_filter'] . '&';
  }
  if (isset($_GET['current_category_id'])) {
    $_SESSION['page_info'] .= 'current_category_id=' . $_GET['current_category_id'] . '&';
  }

  if (zen_not_null($_SESSION['page_info'])) {
    $_SESSION['page_info'] = substr($_SESSION['page_info'], 0, -1);
  }

  switch ($action) {
/////////////////////////////////////////
//// BOF OF FLAGS
    case 'set_flag_attributes_display_only':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_display_only = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_display_only = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }
      }
      break;

    case 'set_flag_product_attribute_is_free':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET product_attribute_is_free = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET product_attribute_is_free = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;

    case 'set_flag_attributes_default':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_default = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_default = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;

    case 'set_flag_attributes_discounted':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_discounted = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_discounted = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }
        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_GET['products_filter']);
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;

    case 'set_flag_attributes_price_base_included':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_price_base_included = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_price_base_included = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_GET['products_filter']);

        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;

    case 'set_flag_attributes_required':
      if (isset($_POST['divertClickProto'])) {
        $action = '';
        if ($_GET['flag'] == '0') {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_required = 1
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        } else {
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_required = 0
                        WHERE products_id = " . (int)$_GET['products_filter'] . "
                        AND products_attributes_id = " . (int)$_GET['attributes_id']);
        }
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;

//// EOF OF FLAGS
/////////////////////////////////////////

    case 'set_products_filter':
      $_GET['products_filter'] = (int)$_POST['products_filter'];
      $_GET['current_category_id'] = (int)$_POST['current_category_id'];
      $action = '';
      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']));
      break;
// update by product
    case ('update_attribute_sort'):
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'y') {
        if (!zen_has_product_attributes($products_filter, 'false')) {
          $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_NONE . $products_filter . ' ' . zen_get_products_name($products_filter, $_SESSION['languages_id']), 'error');
        } else {
          zen_update_attributes_products_option_values_sort_order($products_filter);
          $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT . $products_filter . ' ' . zen_get_products_name($products_filter, $_SESSION['languages_id']), 'success');
        }
        $action = '';
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_GET['current_category_id']));
      }
      break;
    case 'add_product_attributes':
      $current_image_name = '';
      for ($i = 0; $i < sizeof($_POST['values_id']); $i++) {
        if (isset($_POST['values_id'][$i])) {
          $_POST['values_id'][$i] = (int)$_POST['values_id'][$i];
        }
        if (isset($_POST['options_id'])) {
          $_POST['options_id'] = (int)$_POST['options_id'];
        }
        if (isset($_POST['products_id'])) {
          $_POST['products_id'] = (int)$_POST['products_id'];
        }
// check for duplicate and block them
        $check_duplicate = $db->Execute("SELECT products_id, options_id, options_values_id
                                         FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                         WHERE products_id = " . (int)$_POST['products_id'] . "
                                         AND options_id = " . (int)$_POST['options_id'] . "
                                         AND options_values_id = " . (int)$_POST['values_id'][$i]);
        if ($check_duplicate->RecordCount() > 0) {
          // do not add duplicates -- give a warning
          $messageStack->add_session(ATTRIBUTE_WARNING_DUPLICATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id'][$i]), 'error');
        } else {
// For TEXT and FILE option types, ignore option value entered by administrator and use PRODUCTS_OPTIONS_VALUES_TEXT instead.
          $products_options_array = $db->Execute("SELECT products_options_type
                                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                  WHERE products_options_id = " . (int)$_POST['options_id']);
          $values_id = zen_db_prepare_input((($products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT) or ( $products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $_POST['values_id'][$i]);

          $products_id = zen_db_prepare_input($_POST['products_id']);
          $options_id = zen_db_prepare_input($_POST['options_id']);
//            $values_id = zen_db_prepare_input($_POST['values_id'][$i]);
          $value_price = zen_db_prepare_input($_POST['value_price']);
          $price_prefix = zen_db_prepare_input($_POST['price_prefix']);

          $products_options_sort_order = zen_db_prepare_input($_POST['products_options_sort_order']);

// modified options sort order to use default if not otherwise set
          if (zen_not_null($_POST['products_options_sort_order'])) {
            $products_options_sort_order = zen_db_prepare_input($_POST['products_options_sort_order']);
          } else {
            $sort_order_query = $db->Execute("SELECT products_options_values_sort_order
                                              FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                              WHERE products_options_values_id = " . (int)$_POST['values_id'][$i]);
            $products_options_sort_order = $sort_order_query->fields['products_options_values_sort_order'];
          } // end if (zen_not_null($_POST['products_options_sort_order'])
// end modification for sort order

          $product_attribute_is_free = zen_db_prepare_input($_POST['product_attribute_is_free']);
          $products_attributes_weight = zen_db_prepare_input($_POST['products_attributes_weight']);
          $products_attributes_weight_prefix = zen_db_prepare_input($_POST['products_attributes_weight_prefix']);
          $attributes_display_only = zen_db_prepare_input($_POST['attributes_display_only']);
          $attributes_default = zen_db_prepare_input($_POST['attributes_default']);
          $attributes_discounted = zen_db_prepare_input($_POST['attributes_discounted']);
          $attributes_price_base_included = zen_db_prepare_input($_POST['attributes_price_base_included']);

          $attributes_price_onetime = zen_db_prepare_input($_POST['attributes_price_onetime']);
          $attributes_price_factor = zen_db_prepare_input($_POST['attributes_price_factor']);
          $attributes_price_factor_offset = zen_db_prepare_input($_POST['attributes_price_factor_offset']);
          $attributes_price_factor_onetime = zen_db_prepare_input($_POST['attributes_price_factor_onetime']);
          $attributes_price_factor_onetime_offset = zen_db_prepare_input($_POST['attributes_price_factor_onetime_offset']);
          $attributes_qty_prices = zen_db_prepare_input($_POST['attributes_qty_prices']);
          $attributes_qty_prices_onetime = zen_db_prepare_input($_POST['attributes_qty_prices_onetime']);

          $attributes_price_words = zen_db_prepare_input($_POST['attributes_price_words']);
          $attributes_price_words_free = zen_db_prepare_input($_POST['attributes_price_words_free']);
          $attributes_price_letters = zen_db_prepare_input($_POST['attributes_price_letters']);
          $attributes_price_letters_free = zen_db_prepare_input($_POST['attributes_price_letters_free']);
          $attributes_required = zen_db_prepare_input($_POST['attributes_required']);

// add - update as record exists
// attributes images
// when set to none remove from database
// only processes image once for multiple selection of options_values_id
          if ($i == 0) {
            if (isset($_POST['attributes_image']) && zen_not_null($_POST['attributes_image']) && ($_POST['attributes_image'] != 'none')) {
              $attributes_image = zen_db_prepare_input($_POST['attributes_image']);
            } else {
              $attributes_image = '';
            }

            $attributes_image = new upload('attributes_image');
            $attributes_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
            $attributes_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
            if ($attributes_image->parse() && $attributes_image->save($_POST['overwrite'])) {
              $attributes_image_name = $_POST['img_dir'] . $attributes_image->filename;
            } else {
              $attributes_image_name = (isset($_POST['attributes_previous_image']) ? $_POST['attributes_previous_image'] : '');
            }
            $current_image_name = $attributes_image_name;
          } else {
            $attributes_image_name = $current_image_name;
          }
          $attributes_image_name = zen_limit_image_filename($attributes_image_name, TABLE_PRODUCTS_ATTRIBUTES, 'attributes_image');

          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only, attributes_default, attributes_discounted, attributes_image, attributes_price_base_included, attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime, attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime, attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free, attributes_required)
                        VALUES ('" . (int)$products_id . "',
                                '" . (int)$options_id . "',
                                '" . (int)$values_id . "',
                                '" . (float)zen_db_input($value_price) . "',
                                '" . zen_db_input($price_prefix) . "',
                                '" . (int)zen_db_input($products_options_sort_order) . "',
                                '" . (int)zen_db_input($product_attribute_is_free) . "',
                                '" . (float)zen_db_input($products_attributes_weight) . "',
                                '" . zen_db_input($products_attributes_weight_prefix) . "',
                                '" . (int)zen_db_input($attributes_display_only) . "',
                                '" . (int)zen_db_input($attributes_default) . "',
                                '" . (int)zen_db_input($attributes_discounted) . "',
                                '" . zen_db_input($attributes_image_name) . "',
                                '" . (int)zen_db_input($attributes_price_base_included) . "',
                                '" . (float)zen_db_input($attributes_price_onetime) . "',
                                '" . (float)zen_db_input($attributes_price_factor) . "',
                                '" . (float)zen_db_input($attributes_price_factor_offset) . "',
                                '" . (float)zen_db_input($attributes_price_factor_onetime) . "',
                                '" . (float)zen_db_input($attributes_price_factor_onetime_offset) . "',
                                '" . zen_db_input($attributes_qty_prices) . "',
                                '" . zen_db_input($attributes_qty_prices_onetime) . "',
                                '" . (float)zen_db_input($attributes_price_words) . "',
                                '" . (int)zen_db_input($attributes_price_words_free) . "',
                                '" . (float)zen_db_input($attributes_price_letters) . "',
                                '" . (int)zen_db_input($attributes_price_letters_free) . "',
                                '" . (int)zen_db_input($attributes_required) . "')");

          $products_attributes_id = $db->Insert_ID();

          if (DOWNLOAD_ENABLED == 'true') {

            $products_attributes_filename = zen_limit_image_filename($_POST['products_attributes_filename'], TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename');
            $products_attributes_filename = zen_db_prepare_input($products_attributes_filename);
            $products_attributes_maxdays = (int)zen_db_prepare_input($_POST['products_attributes_maxdays']);
            $products_attributes_maxcount = (int)zen_db_prepare_input($_POST['products_attributes_maxcount']);

//die( 'I am adding ' . strlen($_POST['products_attributes_filename']) . ' vs ' . strlen(trim($_POST['products_attributes_filename'])) . ' vs ' . strlen(zen_db_prepare_input($_POST['products_attributes_filename'])) . ' vs ' . strlen(zen_db_input($products_attributes_filename)) );
            if (zen_not_null($products_attributes_filename)) {
              $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " (products_attributes_id, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount)
                            VALUES (" . (int)$products_attributes_id . ",
                                   '" . zen_db_input($products_attributes_filename) . "',
                                   '" . zen_db_input($products_attributes_maxdays) . "',
                                   '" . zen_db_input($products_attributes_maxcount) . "')");
            }
          }

          $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_ADD_PRODUCT_ATTRIBUTES', $products_attributes_id);
        }
      }

      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($_POST['products_id']);

      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_POST['products_id'] . '&current_category_id=' . $_POST['current_category_id']));
      break;
    case 'update_product_attribute':
      $check_duplicate = $db->Execute("SELECT products_id, options_id, options_values_id, products_attributes_id
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                       WHERE products_id = " . (int)$_POST['products_id'] . "
                                       AND options_id = " . (int)$_POST['options_id'] . "
                                       AND options_values_id = " . (int)$_POST['values_id'] . "
                                       AND products_attributes_id != " . (int)$_POST['attribute_id']);

      if ($check_duplicate->RecordCount() > 0) {
        // do not add duplicates give a warning
        $messageStack->add_session(ATTRIBUTE_WARNING_DUPLICATE_UPDATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
      } else {
        // Validate options_id and options_value_id
        if (!zen_validate_options_to_options_value($_POST['options_id'], $_POST['values_id'])) {
          // do not add invalid match
          $messageStack->add_session(ATTRIBUTE_WARNING_INVALID_MATCH_UPDATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
        } else {
          // add the new attribute
// iii 030811 added:  Enforce rule that TEXT and FILE Options use value PRODUCTS_OPTIONS_VALUES_TEXT_ID
          $products_options_query = $db->Execute("SELECT products_options_type
                                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                  WHERE products_options_id = " . (int)$_POST['options_id']);
          switch ($products_options_query->fields['products_options_type']) {
            case PRODUCTS_OPTIONS_TYPE_TEXT:
            case PRODUCTS_OPTIONS_TYPE_FILE:
              $values_id = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
              break;
            default:
              $values_id = zen_db_prepare_input($_POST['values_id']);
          }
// iii 030811 added END

          $products_id = zen_db_prepare_input($_POST['products_id']);
          $options_id = zen_db_prepare_input($_POST['options_id']);
          $value_price = zen_db_prepare_input($_POST['value_price']);
          $price_prefix = zen_db_prepare_input($_POST['price_prefix']);

          $products_options_sort_order = zen_db_prepare_input($_POST['products_options_sort_order']);
          $product_attribute_is_free = zen_db_prepare_input($_POST['product_attribute_is_free']);
          $products_attributes_weight = zen_db_prepare_input($_POST['products_attributes_weight']);
          $products_attributes_weight_prefix = zen_db_prepare_input($_POST['products_attributes_weight_prefix']);
          $attributes_display_only = zen_db_prepare_input($_POST['attributes_display_only']);
          $attributes_default = zen_db_prepare_input($_POST['attributes_default']);
          $attributes_discounted = zen_db_prepare_input($_POST['attributes_discounted']);
          $attributes_price_base_included = zen_db_prepare_input($_POST['attributes_price_base_included']);

          $attributes_price_onetime = zen_db_prepare_input($_POST['attributes_price_onetime']);
          $attributes_price_factor = zen_db_prepare_input($_POST['attributes_price_factor']);
          $attributes_price_factor_offset = zen_db_prepare_input($_POST['attributes_price_factor_offset']);
          $attributes_price_factor_onetime = zen_db_prepare_input($_POST['attributes_price_factor_onetime']);
          $attributes_price_factor_onetime_offset = zen_db_prepare_input($_POST['attributes_price_factor_onetime_offset']);
          $attributes_qty_prices = zen_db_prepare_input($_POST['attributes_qty_prices']);
          $attributes_qty_prices_onetime = zen_db_prepare_input($_POST['attributes_qty_prices_onetime']);

          $attributes_price_words = zen_db_prepare_input($_POST['attributes_price_words']);
          $attributes_price_words_free = zen_db_prepare_input($_POST['attributes_price_words_free']);
          $attributes_price_letters = zen_db_prepare_input($_POST['attributes_price_letters']);
          $attributes_price_letters_free = zen_db_prepare_input($_POST['attributes_price_letters_free']);
          $attributes_required = zen_db_prepare_input($_POST['attributes_required']);

          $attribute_id = zen_db_prepare_input($_POST['attribute_id']);

// edit
// attributes images
// when set to none remove from database
          if (isset($_POST['attributes_image']) && zen_not_null($_POST['attributes_image']) && ($_POST['attributes_image'] != 'none')) {
            $attributes_image = zen_db_prepare_input($_POST['attributes_image']);
            $attributes_image_none = false;
          } else {
            $attributes_image = '';
            $attributes_image_none = true;
          }

          $attributes_image = new upload('attributes_image');
          $attributes_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
          $attributes_image->set_destination(DIR_FS_CATALOG_IMAGES . (isset($_POST['img_dir']) ? $_POST['img_dir']: ''));
          if ($attributes_image->parse() && $attributes_image->save($_POST['overwrite'])) {
            $attributes_image_name = ($attributes_image->filename != 'none' ? ($_POST['img_dir'] . $attributes_image->filename) : '');
          } else {
            $attributes_image_name = ((isset($_POST['attributes_previous_image']) && !(isset($_POST['attributes_image']) && $_POST['attributes_image'] == 'none')) ? $_POST['attributes_previous_image'] : '');
          }

          if (isset($_POST['image_delete']) && $_POST['image_delete'] == 1) {
            $attributes_image_name = '';
          }

          $attributes_image_name = zen_limit_image_filename($attributes_image_name, TABLE_PRODUCTS_ATTRIBUTES, 'attributes_image');

// turned off until working
          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET attributes_image = '" . zen_db_input($attributes_image_name) . "'
                        WHERE products_attributes_id = " . (int)$attribute_id);

          $db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                        SET products_id = " . (int)$products_id . ",
                            options_id = " . (int)$options_id . ",
                            options_values_id = " . (int)$values_id . ",
                            options_values_price = '" . zen_db_input($value_price) . "',
                            price_prefix = '" . zen_db_input($price_prefix) . "',
                            products_options_sort_order = '" . zen_db_input($products_options_sort_order) . "',
                            product_attribute_is_free = '" . zen_db_input($product_attribute_is_free) . "',
                            products_attributes_weight = '" . zen_db_input($products_attributes_weight) . "',
                            products_attributes_weight_prefix = '" . zen_db_input($products_attributes_weight_prefix) . "',
                            attributes_display_only = '" . zen_db_input($attributes_display_only) . "',
                            attributes_default = '" . zen_db_input($attributes_default) . "',
                            attributes_discounted = '" . zen_db_input($attributes_discounted) . "',
                            attributes_price_base_included = '" . zen_db_input($attributes_price_base_included) . "',
                            attributes_price_onetime = '" . zen_db_input($attributes_price_onetime) . "',
                            attributes_price_factor = '" . zen_db_input($attributes_price_factor) . "',
                            attributes_price_factor_offset = '" . zen_db_input($attributes_price_factor_offset) . "',
                            attributes_price_factor_onetime = '" . zen_db_input($attributes_price_factor_onetime) . "',
                            attributes_price_factor_onetime_offset = '" . zen_db_input($attributes_price_factor_onetime_offset) . "',
                            attributes_qty_prices = '" . zen_db_input($attributes_qty_prices) . "',
                            attributes_qty_prices_onetime = '" . zen_db_input($attributes_qty_prices_onetime) . "',
                            attributes_price_words = '" . zen_db_input($attributes_price_words) . "',
                            attributes_price_words_free = '" . zen_db_input($attributes_price_words_free) . "',
                            attributes_price_letters = '" . zen_db_input($attributes_price_letters) . "',
                            attributes_price_letters_free = '" . zen_db_input($attributes_price_letters_free) . "',
                            attributes_required = '" . zen_db_input($attributes_required) . "'
                        WHERE products_attributes_id = " . (int)$attribute_id);

          if (DOWNLOAD_ENABLED == 'true') {
            $products_attributes_filename = zen_limit_image_filename($_POST['products_attributes_filename'], TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename');
            $products_attributes_filename = zen_db_prepare_input($products_attributes_filename);
            $products_attributes_maxdays = zen_db_prepare_input($_POST['products_attributes_maxdays']);
            $products_attributes_maxcount = zen_db_prepare_input($_POST['products_attributes_maxcount']);

            if (zen_not_null($products_attributes_filename)) {
              $db->Execute("REPLACE INTO " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                            SET products_attributes_id = " . (int)$attribute_id . ",
                                products_attributes_filename = '" . zen_db_input($products_attributes_filename) . "',
                                products_attributes_maxdays = '" . zen_db_input($products_attributes_maxdays) . "',
                                products_attributes_maxcount = '" . zen_db_input($products_attributes_maxcount) . "'");
            }
          }
          $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_UPDATE_PRODUCT_ATTRIBUTE', $attribute_id);
        }
      }

      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($_POST['products_id']);

      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $_POST['current_category_id']));
      break;
    case 'delete_attribute':
      // demo active test
      if (zen_admin_demo()) {
        $_GET['action'] = '';
        $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $_POST['current_category_id']));
      }
      if (isset($_POST['delete_attribute_id'])) {
        $attribute_id = zen_db_prepare_input($_POST['delete_attribute_id']);

        $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ATTRIBUTE', array('attribute_id' => $attribute_id), $attribute_id);

        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                      WHERE products_attributes_id = " . (int)$attribute_id);

// added for DOWNLOAD_ENABLED. Always try to remove attributes, even if downloads are no longer enabled
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                      WHERE products_attributes_id = " . (int)$attribute_id);

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_filter);

        zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $_POST['current_category_id']));
      }
      break;
// delete all attributes
    case 'delete_all_attributes':
      $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ALL', array('pID' => $_POST['products_filter']));

      $action = '';
      $products_filter = (int)$_POST['products_filter'];
      zen_delete_products_attributes($_POST['products_filter']);
      $messageStack->add_session(SUCCESS_ATTRIBUTES_DELETED . ' ID#' . $products_filter, 'success');

      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($products_filter);

      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;

    case 'delete_option_name_values':
      $zco_notifier->notify('NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_OPTION_NAME_VALUES', array('pID' => $_POST['products_filter'], 'options_id' => $_POST['products_options_id_all']));

      $delete_attributes_options_id = $db->Execute("SELECT products_attributes_id
                                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                    WHERE products_id = " . (int)$_POST['products_filter'] . "
                                                    AND options_id = " . (int)$_POST['products_options_id_all']);
      foreach ($delete_attributes_options_id as $attributes_options_id) {
// remove any attached downloads
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                      WHERE products_attributes_id = " . (int)$attributes_options_id['products_attributes_id']);
// remove all option values
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                      WHERE products_id = " . (int)$_POST['products_filter'] . "
                      AND options_id = " . (int)$_POST['products_options_id_all']);
      }

      $action = '';
      $products_filter = $_POST['products_filter'];
      $messageStack->add_session(SUCCESS_ATTRIBUTES_DELETED_OPTION_NAME_VALUES . ' ID#' . zen_options_name($_POST['products_options_id_all']), 'success');

      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($products_filter);

      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;


// attributes copy to product
    case 'update_attributes_copy_to_product':
      $copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
      $copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
      $copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
      zen_copy_products_attributes($_POST['products_filter'], $_POST['products_update_id']);
      $_GET['action'] = '';
      $products_filter = $_POST['products_update_id'];
      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;

// attributes copy to category
    case 'update_attributes_copy_to_category':
      $copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
      $copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
      $copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
      if ($_POST['categories_update_id'] == '') {
        $messageStack->add_session(WARNING_PRODUCT_COPY_TO_CATEGORY_NONE . ' ID#' . $_POST['products_filter'], 'warning');
      } else {
        $copy_to_category = $db->Execute("SELECT products_id
                                          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                          WHERE categories_id = " . (int)$_POST['categories_update_id']);
        foreach ($copy_to_category as $item) {
          zen_copy_products_attributes($_POST['products_filter'], $item['products_id']);
        }
      }
      $_GET['action'] = '';
      $products_filter = $_POST['products_filter'];
      zen_redirect(zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;
  }
}

//iii 031103 added to get results from database option type query
$products_options_types_list = array();
$products_options_type_array = $db->Execute("SELECT products_options_types_id, products_options_types_name
                                             FROM " . TABLE_PRODUCTS_OPTIONS_TYPES . "
                                             ORDER BY products_options_types_id");
foreach ($products_options_type_array as $products_options_type) {
  $products_options_types_list[$products_options_type['products_options_types_id']] = $products_options_type['products_options_types_name'];
}

//CLR 030312 add function to draw pulldown list of option types
// Draw a pulldown for Option Types
//iii 031103 modified to use results of database option type query from above
function draw_optiontype_pulldown($name, $default = '') {
  global $products_options_types_list;
  $values = array();
  foreach ($products_options_types_list as $id => $text) {
    $values[] = array(
      'id' => $id,
      'text' => $text);
  }
  return zen_draw_pull_down_menu($name, $values, $default);
}

//CLR 030312 add function to translate type_id to name
// Translate option_type_values to english string
//iii 031103 modified to use results of database option type query from above
function translate_type_to_name($opt_type) {
  global $products_options_types_list;
  return $products_options_types_list[$opt_type];
}

function zen_js_option_values_list($selectedName, $fieldName) {
  global $db, $show_value_numbers;
  $attributes_sql = "SELECT povpo.products_options_id, povpo.products_options_values_id,
                            po.products_options_name, po.products_options_sort_order,
                            pov.products_options_values_name, pov.products_options_values_sort_order
                     FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povpo,
                          " . TABLE_PRODUCTS_OPTIONS . " po,
                          " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                     WHERE povpo.products_options_id = po.products_options_id
                     AND povpo.products_options_values_id = pov.products_options_values_id
                     AND pov.language_id = po.language_id
                     AND po.language_id = " . (int)$_SESSION['languages_id'] . "
                     ORDER BY po.products_options_id, po.products_options_name, pov.products_options_values_name";

//           "
//           ORDER BY po.products_options_name, pov.products_options_values_sort_order";

  $attributes = $db->Execute($attributes_sql);

  $counter = 1;
  $val_count = 0;
  $value_string = '  // Build conditional Option Values Lists' . "\n";
  $last_option_processed = null;
  foreach ($attributes as $attribute) {
    $products_options_values_name = str_replace('-', '\-', $attribute['products_options_values_name']);
    $products_options_values_name = str_replace('(', '\(', $products_options_values_name);
    $products_options_values_name = str_replace(')', '\)', $products_options_values_name);
    $products_options_values_name = str_replace('"', '\"', $products_options_values_name);
    $products_options_values_name = str_replace('&quot;', '\"', $products_options_values_name);
    $products_options_values_name = str_replace('&frac12;', '1/2', $products_options_values_name);

    if ($counter == 1) {
      $value_string .= '  if (' . $selectedName . ' == "' . $attribute['products_options_id'] . '") {' . "\n";
    } elseif ($last_option_processed != $attribute['products_options_id']) {
      $value_string .= '  } else if (' . $selectedName . ' == "' . $attribute['products_options_id'] . '") {' . "\n";
      $val_count = 0;
    }

    $value_string .= '    ' . $fieldName . '.options[' . $val_count . '] = new Option("' . $products_options_values_name . ($attribute['products_options_values_id'] == 0 ? '/UPLOAD FILE' : '') . ($show_value_numbers ? ' [ #' . $attribute['products_options_values_id'] . ' ] ' : '') . '", "' . $attribute['products_options_values_id'] . '");' . "\n";

    $last_option_processed = $attribute['products_options_id'];
    $val_count++;
    $counter++;
  }
  if ($counter > 1) {
    $value_string .= '  }' . "\n";
  }
  return $value_string;
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <style>
      .menuItemButton {
          background: none;
          color: #333;
          border: none;
          padding: 3px 20px;
          font: inherit;
          display: block;
          font-weight: 400;
          line-height: 1.42857143;
          white-space: nowrap;
          font-size: 11px;
      }
      .menuItemButton:hover{
          color: #262626;
          text-decoration: none;
          background-color: #f5f5f5;
      }
      .row-eq-height {
          display: -webkit-box;
          display: -webkit-flex;
          display: -ms-flexbox;
          display: flex;
      }
    </style>
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function go_option() {
          if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
              location = "<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'option_page=' . (isset($_GET['option_page']) && $_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by=" + document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
          }
      }
      function popupWindow(url) {
          window.open(url, 'popupWindow', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=600,height=460,screenX=150,screenY=150,top=150,left=150')
      }
    </script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
  </head>
  <!-- <body onload="init()"> -->
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <div class="row">
        <h1 class="col-sm-4"><?php echo HEADING_TITLE_ATRIB; ?></h1>
        <div class="col-sm-4">
          <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="menu1" data-toggle="dropdown">
                <?php echo BUTTON_ADDITITONAL_ACTIONS; ?>
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
              <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER) ?>" target="_blank"><?php echo IMAGE_OPTION_NAMES; ?></a></li>
              <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER) ?>" target="_blank"><?php echo IMAGE_OPTION_VALUES; ?></a></li>
              <?php if ($products_filter != '' && $action != 'attribute_features_copy_to_product' && $action != 'attribute_features_copy_to_category' && $action != 'delete_all_attributes_confirm') { ?>
                <li role="presentation" class="divider"></li>
                <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_product_path($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)); ?>"><?php echo IMAGE_EDIT_PRODUCT; ?></a></li>
                <?php if ($zc_products->get_allow_add_to_cart($products_filter) == "Y") { ?>
                  <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>"><?php echo IMAGE_PRODUCTS_PRICE_MANAGER; ?></a></li>
                <?php } ?>
                <?php
                if (zen_has_product_attributes($products_filter, 'false')) {
                  ?>
                  <li role="presentation">
                      <?php echo zen_draw_form('update_sort', FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attribute_sort' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>
                    <button role="menuitem" type="submit" class="menuItemButton"><?php echo TEXT_UPDATE_DEFAULTE_SORT_ORDER; ?></button>
                    <?php echo zen_draw_hidden_field('confirm', 'y'); ?>
                    <?php echo '</form>'; ?>
                  </li>
                  <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '&action=delete_all_attributes_confirm' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>"><?php echo TEXT_DELETE_ALL_OPTIONS_FROM_PRODUCT; ?></a></li>
                  <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attribute_features_copy_to_product' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>"><?php echo TEXT_COPY_ALL_OPTIONS_TO_PRODUCT; ?></a></li>
                  <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attribute_features_copy_to_category' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>"><?php echo TEXT_COPY_ALL_OPTIONS_TO_CATEGORY; ?></a></li>
                <?php } ?>
                <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>"><?php echo IMAGE_PRODUCTS_TO_CATEGORIES; ?></a></li>
              <?php } ?>
            </ul>
          </div>
        </div>
        <div class="col-sm-4 text-right">
            <?php
            echo zen_draw_form('search', FILENAME_CATEGORY_PRODUCT_LISTING, '', 'get');
// show reset search
            if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
              echo '<a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
            }
            echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'search') . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
            if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
              $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
              echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
            }
            echo '</form>';
            ?>
        </div>
      </div>
      <?php
// remove all attributes from the product
      if ($action == 'delete_all_attributes_confirm') {
        ?>
        <div class="row">
            <?php echo zen_draw_form('delete_all', FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_all_attributes'); ?>
            <?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
          <div class="col-xs-6 col-sm-4 text-danger"><strong><?php echo TEXT_DELETE_ALL_ATTRIBUTES . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></strong></div>
          <div class="col-xs-6 col-sm-8">
            <button type="submit" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo IMAGE_DELETE; ?></button>
            <?php echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role=""button>' . IMAGE_CANCEL . '</a>'; ?>
          </div>
          <?php echo '</form>'; ?>
        </div>
        <?php
      }
      ?>
      <?php
// remove option name and all values from the product
      if ($action == 'delete_option_name_values_confirm') {
        ?>
        <div class="row">
            <?php echo zen_draw_form('delete_all', FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_option_name_values'); ?>
            <?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
            <?php echo zen_draw_hidden_field('products_options_id_all', $_GET['products_options_id_all']); ?>
          <div class="row alert text-danger"><strong><?php echo TEXT_DELETE_ATTRIBUTES_OPTION_NAME_VALUES; ?></strong></div>
          <div class="row">
            <div class="col-sm-4"><?php echo TEXT_INFO_PRODUCT_NAME . zen_get_products_name($products_filter) . '<br />' . TEXT_INFO_PRODUCTS_OPTION_ID . $_GET['products_options_id_all'] . '&nbsp;' . TEXT_INFO_PRODUCTS_OPTION_NAME . '&nbsp;' . zen_options_name($_GET['products_options_id_all']); ?></div>
            <div class="col-sm-8">
              <button type="submit" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo IMAGE_DELETE; ?></button>
              <?php echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?>
            </div>
          </div>
          <?php echo '</form>'; ?>
        </div>
        <?php
      }
      ?>

      <?php
      if ($action == 'attribute_features_copy_to_product') {
        $_GET['products_update_id'] = '';
        // excluded current product from the pull down menu of products
        $products_exclude_array = array();
        $products_exclude_array[] = $products_filter;
        ?>
        <div class="row">
            <?php echo zen_draw_form('product_copy_to_product', FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attributes_copy_to_product', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('products_id', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('products_update_id', $_GET['products_update_id']); ?>
            <?php echo zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']); ?>
          <div class="form-group">
            <div class="col-sm-6 text-center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></div>
            <div class="col-sm-6">
                <?php echo TEXT_COPY_ATTRIBUTES_CONDITIONS; ?>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . TEXT_COPY_ATTRIBUTES_DELETE; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . TEXT_COPY_ATTRIBUTES_UPDATE; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . TEXT_COPY_ATTRIBUTES_IGNORE; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-6 text-center">
              <span class="text-danger"><strong><?php echo TEXT_INFO_ATTRIBUTES_FEATURE_COPY_TO; ?></strong></span><br />
              <?php echo zen_draw_products_pull_down('products_update_id', 'size="15" class="form-control"', $products_exclude_array, true, '', true); ?></div>
            <div class="col-sm-6 text-center">
              <button type="submit" class="btn btn-primary"><i class="fa fa-copy" aria-hidden="true"></i> <?php echo IMAGE_COPY; ?></button>
              <?php echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
          </div>
          <?php echo '</form>'; ?>
        </div>
        <?php
      }
      ?>
      <?php
      if ($action == 'attribute_features_copy_to_category') {
        ?>
        <div class="row">
            <?php echo zen_draw_form('product_copy_to_category', FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attributes_copy_to_category', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('products_id', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('products_update_id', $_GET['products_update_id']); ?>
            <?php echo zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']); ?>
            <?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
          <div class="form-group">
            <div class="col-sm-6 text-center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></div>
            <div class="col-sm-6">
              <?php echo TEXT_COPY_ATTRIBUTES_CONDITIONS; ?><br />
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . TEXT_COPY_ATTRIBUTES_DELETE; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . TEXT_COPY_ATTRIBUTES_UPDATE; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . TEXT_COPY_ATTRIBUTES_IGNORE; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-6 text-center">
              <span class="text-danger"><strong><?php echo TEXT_INFO_ATTRIBUTES_FEATURE_CATEGORIES_COPY_TO; ?></strong></span><br />
              <?php echo zen_draw_products_pull_down_categories('categories_update_id', 'size="5" class="form-control"', '', true, true); ?></div>
            <div class="col-sm-6 text-center">
              <button type="submit" class="btn btn-primary"><i class="fa fa-copy" aria-hidden="true"></i> <?php echo IMAGE_COPY; ?></button>
              <?php echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
          </div>
          <?php echo '</form>'; ?>
        </div>

        <?php
      }
      ?>
      <div class="row text-center"><?php echo zen_draw_separator('pixel_black.gif', '90%', '2'); ?></div>
      <?php
      if ($action == '') {
        ?>
        <div class="row">
          <div class="table-responsive">
            <table class="table">
                <?php require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY); ?>
            </table>
          </div>
        </div>

        <div class="row">
            <?php echo zen_draw_form('set_products_filter_id', FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_products_filter', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('products_filter', $products_filter); ?>
            <?php echo zen_draw_hidden_field('current_category_id', $current_category_id); ?>
            <?php
            if ($_GET['products_filter'] != '') {
              ?>
            <div class="form-group">
              <div class="col-xs-offset-2 col-offset-sm-1 col-xs-7 col-sm-7"><?php echo TEXT_PRODUCT_TO_VIEW; ?></div>
            </div>
            <div class="form-group">
              <div class="col-xs-2 col-sm-1 col-md-1 col-lg-1 text-center">
                  <?php
                  $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
                  echo ($display_priced_by_attributes ? '<span class="text-warning"><strong>' . TEXT_PRICED_BY_ATTRIBUTES . '</strong></span>' . '<br />' : '');
                  echo zen_get_products_display_price($_GET['products_filter']) . '<br /><br />';
                  echo zen_get_products_quantity_min_units_display($_GET['products_filter'], $include_break = true);
                  ?>
              </div>
              <div class="col-xs-8 col-sm-8 col-md-6 col-lg-4 text-center"><?php echo zen_draw_products_pull_down('products_filter', 'class="form-control"', '', true, $_GET['products_filter'], true, true); ?></div>
              <div class="col-xs-2 col-sm-3 col-md-5 col-lg-7">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_DISPLAY; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
          </div>

          <?php
        } // product dropdown
        ?>
        <?php
      } // $action == ''
      ?>
      <?php
// start of attributes display
      if ($_GET['products_filter'] == '') {
        ?>
        <div class="row">
          <h2 class="text-center"><?php echo HEADING_TITLE_ATRIB_SELECT; ?></h2>
        </div>
        <?php
      } else {
////
// attribute listings and add

        if ($action == 'update_attribute') {
          $form_action = 'update_product_attribute';
        } elseif ($action == 'delete_product_attribute') {
          $form_action = 'delete_attribute';
        } else {
          $form_action = 'add_product_attributes';
        }

        if (!isset($_GET['attribute_page'])) {
          $_GET['attribute_page'] = 1;
        }
        $prev_attribute_page = $_GET['attribute_page'] - 1;
        $next_attribute_page = $_GET['attribute_page'] + 1;
        ?>

        <?php
        if ($action == '') {
          ?>
          <div class="row">
            <div class="table-responsive">
              <table class="table-bordered">
                <tr>
                  <td class="text-right"><?php echo LEGEND_BOX; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_DISPLAY_ONLY; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_IS_FREE; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_DEFAULT; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTE_IS_DISCOUNTED; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_REQUIRED; ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_IMAGES ?></td>
                  <td class="text-center"><?php echo LEGEND_ATTRIBUTES_DOWNLOAD ?></td>
                </tr>
                <tr>
                  <td class="text-right"><?php echo LEGEND_KEYS; ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_yellow_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_pink_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_pink_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_purple_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_purple_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_red_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif'); ?></td>
                  <td class="text-center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif'); ?></td>
                </tr>
              </table>
            </div>
          </div>
        <?php } ?>
        <?php
// fix here border width
        ?>
        <div class="row">
          <div class="table-responsive">
              <?php echo zen_draw_form('attributes', FILENAME_ATTRIBUTES_CONTROLLER, 'action=' . $form_action . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter, 'post', 'enctype="multipart/form-data" class="form-horizontal"'); ?>

            <?php
            $per_page = (defined('MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER') && (int)MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER > 3) ? (int)MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER : 40;
            $attributes = "SELECT pa.*
                           FROM (" . TABLE_PRODUCTS_ATTRIBUTES . " pa
                           LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON pa.options_id = po.products_options_id
                             AND po.language_id = " . (int)$_SESSION['languages_id'] . ")
                           WHERE pa.products_id = " . (int)$products_filter . "
                           ORDER BY LPAD(po.products_options_sort_order,11,'0'),
                                    LPAD(pa.options_id,11,'0'),
                                    LPAD(pa.products_options_sort_order,11,'0')";
            $attribute_query = $db->Execute($attributes);

            $attribute_page_start = ($per_page * $_GET['attribute_page']) - $per_page;
            $num_rows = $attribute_query->RecordCount();

            if ($num_rows <= $per_page) {
              $num_pages = 1;
            } else if (($num_rows % $per_page) == 0) {
              $num_pages = ($num_rows / $per_page);
            } else {
              $num_pages = ($num_rows / $per_page) + 1;
            }
            $num_pages = (int)$num_pages;

// fix limit error on some versions
            if ($attribute_page_start < 0) {
              $attribute_page_start = 0;
            }

            $attributes = $attributes . " LIMIT $attribute_page_start, $per_page";
            ?>
            <?php if ($num_pages > 1) { ?>
              <div class="row">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-sm">
                      <?php
                      // First
                      if ($_GET['attribute_page'] != '1') {
                        echo '<li><a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=1' . '&products_filter=' . $products_filter) . '" aria-label="First"  title="' . PREVNEXT_TITLE_FIRST_PAGE . '"><i class="fa fa-angle-double-left" aria-hidden="true""></i></a></li>';
                      } else {
                        echo '<li class="disabled"><a href="#" aria-label="First"><i class="fa fa-angle-double-left" aria-hidden="true""></i></a></li>';
                      }
                      // Previous
                      if ($prev_attribute_page) {
                        echo '<li><a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $prev_attribute_page . '&products_filter=' . $products_filter) . '" aria-label="Previous" title="' . PREVNEXT_TITLE_PREVIOUS_PAGE . '"><i class="fa fa-angle-left" aria-hidden="true""></i></a></li>';
                      } else {
                        echo '<li class="disabled"><a href="#" aria-label="Previous"><i class="fa fa-angle-left" aria-hidden="true""></i></a></li>';
                      }

                      for ($i = 1; $i <= $num_pages; $i++) {
                        if ($i != $_GET['attribute_page']) {
                          echo '<li><a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $i . '&products_filter=' . $products_filter) . '">' . $i . '</a></li>';
                        } else {
                          echo '<li class="active"><a href="#">' . $i . '</a></li>';
                        }
                      }

                      // Next and Last
                      if ($_GET['attribute_page'] != $num_pages) {
                        echo '<li><a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $next_attribute_page . '&products_filter=' . $products_filter) . '" aria-label="Next" title="' . PREVNEXT_TITLE_NEXT_PAGE . '"><i class="fa fa-angle-right" aria-hidden="true""></i></a></li>';
                        echo '<li><a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $num_pages . '&products_filter=' . $products_filter) . '" aria-label="Last" title="' . PREVNEXT_TITLE_LAST_PAGE . '"><i class="fa fa-angle-double-right" aria-hidden="true""></i></a></li>';
                      } else {
                        echo '<li class="disabled"><a href="#" aria-label="Next"><i class="fa fa-angle-right" aria-hidden="true""></i></a></li>';
                        echo '<li class="disabled"><a href="#" aria-label="Last"><i class="fa fa-angle-double-right" aria-hidden="true""></i></a></li>';
                      }
                      ?>
                  </ul>
                </nav>
              </div>
              <?php
            }
            ?>
            <table class="table table-striped table-condensed">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent">&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPT_PRICE_PREFIX; ?>&nbsp;<?php echo TABLE_HEADING_OPT_PRICE; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPT_WEIGHT_PREFIX; ?>&nbsp;<?php echo TABLE_HEADING_OPT_WEIGHT; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPT_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo LEGEND_BOX; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRICE_TOTAL; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>

              <?php
              $next_id = 1;
              $attributes_values = $db->Execute($attributes);

              if ($attributes_values->RecordCount() == 0) {
                ?>
                <tr>
                  <td colspan="10" class="pageHeading text-center">
                      <?php echo ($products_filter == '' ? TEXT_NO_PRODUCTS_SELECTED : TEXT_NO_ATTRIBUTES_DEFINED . $products_filter . ' ' . zen_get_products_model($products_filter) . ' - ' . zen_get_products_name($products_filter)); ?>
                  </td>
                </tr>

                <?php
              } else {
                ?>
                <tr>
                  <td colspan="10" class="pageHeading text-center">
                      <?php echo TEXT_INFO_ID . $products_filter . ' ' . zen_get_products_model($products_filter) . ' - ' . zen_get_products_name($products_filter); ?>
                  </td>
                </tr>
              <?php } ?>
              <?php
              $current_options_name = '';
              // get products tax id
              $product_check = $db->Execute("SELECT products_tax_class_id
                                             FROM " . TABLE_PRODUCTS . "
                                             WHERE products_id = " . (int)$products_filter . "
                                             LIMIT 1");
              $rows = 0;
//  echo '$products_filter: ' . $products_filter . ' tax id: ' . $product_check->fields['products_tax_class_id'] . '<br>';
              foreach ($attributes_values as $attributes_value) {
                $current_attributes_products_id = $attributes_value['products_id'];
                $current_attributes_options_id = $attributes_value['options_id'];

                $products_name_only = zen_get_products_name($attributes_value['products_id']);
                $options_name = zen_options_name($attributes_value['options_id']);
                $values_name = zen_values_name($attributes_value['options_values_id']);
                $rows++;

// delete all option name values
                if ($current_options_name != $options_name) {
                  $current_options_name = $options_name;
                  ?>
                  <tr>
                    <td>
                        <?php
                        if ($action == '') {
                          ?>
                        <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_option_name_values_confirm&products_options_id_all=' . $current_attributes_options_id . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-danger" role="button"><i class="fa fa-trash" aria-hidden="true"></i></a>
                        <?php
                      }
                      ?>
                    </td>
                    <td class="pageHeading" colspan="9"><?php echo $current_options_name; ?></td>
                  </tr>
                <?php } // option name delete   ?>
                <?php
                if (($action == 'update_attribute') && ($_GET['attribute_id'] == $attributes_value['products_attributes_id'])) {
                  ?>
                  <tr>
                    <td colspan="10"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td colspan="10">
                      <div class="row">
                        <div class="col-xs-6 col-sm-6"><h3><?php echo PRODUCTS_ATTRIBUTES_EDITING; ?></h3></div>
                        <div class="col-xs-6 col-sm-6 text-right">
                          <?php echo TEXT_SAVE_CHANGES; ?>&nbsp;
                          <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                          <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        </div>
                      </div>
                      <div class="row row-eq-height">
                        <div class="col-xs-1 col-sm-1">
                          <strong><?php echo $attributes_value['products_attributes_id']; ?></strong>
                          <?php echo zen_draw_hidden_field('attribute_id', $attributes_value['products_attributes_id']); ?>
                          <?php echo zen_draw_hidden_field('products_id', $products_filter); ?>
                          <?php echo zen_draw_hidden_field('current_category_id', $current_category_id); ?>
                          <?php echo zen_draw_hidden_field('options_id', $attributes_value['options_id']); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                          <strong><?php echo zen_get_option_name_language($attributes_value['options_id'], $_SESSION['languages_id']); ?>:</strong>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-5"><?php echo zen_draw_label(TABLE_HEADING_OPT_VALUE, 'values_id', 'class="control-label"'); ?>
                            <?php
// FIX HERE 2 - editing
                            $values_values = $db->Execute("SELECT pov.products_options_values_id, pov.products_options_values_name
                                                           FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                                           LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo ON pov.products_options_values_id = povtpo.products_options_values_id
                                                           WHERE pov.language_id = " . (int)$_SESSION['languages_id'] . "
                                                           AND povtpo.products_options_id = " . (int)$attributes_value['options_id'] . "
                                                           ORDER BY pov.products_options_values_name");
                            $optionValuesArray = array();
                            foreach ($values_values as $value) {

                              if ($show_value_numbers == false) {
                                $show_option_name = '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($value['products_options_values_id'])) . ' ]';
                              } else {
                                $show_option_name = ' [ #' . $value['products_options_values_id'] . ' ] ' . '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($value['products_options_values_id'])) . ' ]';
                              }
                              $optionValuesArray[] = array(
                                'id' => $value['products_options_values_id'],
                                'text' => $value['products_options_values_name'] . $show_option_name
                              );
                            }
                            ?>
                            <?php echo zen_draw_pull_down_menu('values_id', $optionValuesArray, $attributes_value['options_values_id'], 'class="form-control"'); ?>
                        </div>
                      </div>
                      <hr style="border: inherit; margin: 10px 0;">
                      <!-- bof: Edit Prices -->
                      <h4><?php echo TEXT_PRICES_AND_WEIGHTS; ?></h4>
                      <div class="row">
                          <?php
// set radio values attributes_display_only
                          switch ($attributes_value['attributes_display_only']) {
                            case '0':
                              $on_attributes_display_only = false;
                              $off_attributes_display_only = true;
                              break;
                            case '1':
                              $on_attributes_display_only = true;
                              $off_attributes_display_only = false;
                              break;
                            default:
                              $on_attributes_display_only = false;
                              $off_attributes_display_only = true;
                          }
// set radio values attributes_default
                          switch ($attributes_value['product_attribute_is_free']) {
                            case '0':
                              $on_product_attribute_is_free = false;
                              $off_product_attribute_is_free = true;
                              break;
                            case '1':
                              $on_product_attribute_is_free = true;
                              $off_product_attribute_is_free = false;
                              break;
                            default:
                              $on_product_attribute_is_free = false;
                              $off_product_attribute_is_free = true;
                          }
// set radio values attributes_default
                          switch ($attributes_value['attributes_default']) {
                            case '0':
                              $on_attributes_default = false;
                              $off_attributes_default = true;
                              break;
                            case '1':
                              $on_attributes_default = true;
                              $off_attributes_default = false;
                              break;
                            default:
                              $on_attributes_default = false;
                              $off_attributes_default = true;
                          }
// set radio values attributes_discounted
                          switch ($attributes_value['attributes_discounted']) {
                            case '0':
                              $on_attributes_discounted = false;
                              $off_attributes_discounted = true;
                              break;
                            case '1':
                              $on_attributes_discounted = true;
                              $off_attributes_discounted = false;
                              break;
                            default:
                              $on_attributes_discounted = false;
                              $off_attributes_discounted = true;
                          }
// set radio values attributes_price_base_included
                          switch ($attributes_value['attributes_price_base_included']) {
                            case '0':
                              $on_attributes_price_base_included = false;
                              $off_attributes_price_base_included = true;
                              break;
                            case '1':
                              $on_attributes_price_base_included = true;
                              $off_attributes_price_base_included = false;
                              break;
                            default:
                              $on_attributes_price_base_included = false;
                              $off_attributes_price_base_included = true;
                          }
// set radio values attributes_required
                          switch ($attributes_value['attributes_required']) {
                            case '0':
                              $on_attributes_required = false;
                              $off_attributes_required = true;
                              break;
                            case '1':
                              $on_attributes_required = true;
                              $off_attributes_required = false;
                              break;
                            default:
                              $on_attributes_required = false;
                              $off_attributes_required = true;
                          }
// set image overwrite
                          $on_overwrite = true;
                          $off_overwrite = false;
// set image delete
                          $on_image_delete = false;
                          $off_image_delete = true;
                          ?>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_OPT_PRICE_PREFIX, 'price_prefix', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('price_prefix', $attributes_value['price_prefix'], 'size="2" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_OPT_PRICE, 'value_price', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('value_price', $attributes_value['options_values_price'], 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_OPT_WEIGHT_PREFIX, 'products_attributes_weight_prefix', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_attributes_weight_prefix', $attributes_value['products_attributes_weight_prefix'], 'size="2" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_OPT_WEIGHT, 'products_attributes_weight', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_attributes_weight', $attributes_value['products_attributes_weight'], 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_OPT_SORT_ORDER, 'products_options_sort_order', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_options_sort_order', $attributes_value['products_options_sort_order'], 'size="4" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_ONETIME, 'attributes_price_onetime', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_onetime', $attributes_value['attributes_price_onetime'], 'size="6" class="form-control"'); ?>
                        </div>
                      </div>
                      <hr style="border: inherit; margin: 10px 0;">
                      <?php if (ATTRIBUTES_ENABLED_PRICE_FACTOR == 'true') { ?>
                        <div class="row">
                          <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR, 'attributes_price_factor', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_factor', $attributes_value['attributes_price_factor'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET, 'attributes_price_factor_offset', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_factor_offset', $attributes_value['attributes_price_factor_offset'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_ONETIME, 'attributes_price_factor_onetime', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_factor_onetime', $attributes_value['attributes_price_factor_onetime'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET_ONETIME, 'attributes_price_factor_onetime_offset', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_factor_onetime_offset', $attributes_value['attributes_price_factor_onetime_offset'], 'size="6" class="form-control"'); ?>
                          </div>
                        </div>
                        <hr style="border: inherit; margin: 10px 0;">
                        <?php
                      } else {
                        echo zen_draw_hidden_field('attributes_price_factor', $attributes_value['attributes_price_factor']);
                        echo zen_draw_hidden_field('attributes_price_factor_offset', $attributes_value['attributes_price_factor_offset']);
                        echo zen_draw_hidden_field('attributes_price_factor_onetime', $attributes_value['attributes_price_factor_onetime']);
                        echo zen_draw_hidden_field('attributes_price_factor_onetime_offset', $attributes_value['attributes_price_factor_onetime_offset']);
                      } // ATTRIBUTES_ENABLED_PRICE_FACTOR
                      ?>
                      <?php if (ATTRIBUTES_ENABLED_QTY_PRICES == 'true') { ?>
                        <div class="row">
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_QTY_PRICES, '', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_qty_prices', $attributes_value['attributes_qty_prices'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_QTY_PRICES_ONETIME, '', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_qty_prices_onetime', $attributes_value['attributes_qty_prices_onetime'], 'size="6" class="form-control"'); ?>
                          </div>
                        </div>
                        <hr style="border: inherit; margin: 10px 0;">
                        <?php
                      } else {
                        echo zen_draw_hidden_field('attributes_qty_prices', $attributes_value['attributes_qty_prices']);
                        echo zen_draw_hidden_field('attributes_qty_prices_onetime', $attributes_value['attributes_qty_prices_onetime']);
                      } // ATTRIBUTES_ENABLED_QTY_PRICES
                      ?>
                      <?php if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true') { ?>
                        <div class="row">
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_WORDS, 'attributes_price_words', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_words', $attributes_value['attributes_price_words'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_WORDS_FREE, 'attributes_price_words_free', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_words_free', $attributes_value['attributes_price_words_free'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS, 'attributes_price_letters', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_letters', $attributes_value['attributes_price_letters'], 'size="6" class="form-control"'); ?>
                          </div>
                          <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS_FREE, 'attributes_price_letters_free', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('attributes_price_letters_free', $attributes_value['attributes_price_letters_free'], 'size="6" class="form-control"'); ?>
                          </div>
                        </div>
                        <hr style="border: inherit; margin: 10px 0;">
                        <?php
                      } else {
                        echo zen_draw_hidden_field('attributes_price_words', $attributes_value['attributes_price_words']);
                        echo zen_draw_hidden_field('attributes_price_words_free', $attributes_value['attributes_price_words_free']);
                        echo zen_draw_hidden_field('attributes_price_letters', $attributes_value['attributes_price_letters']);
                        echo zen_draw_hidden_field('attributes_price_letters_free', $attributes_value['attributes_price_letters_free']);
                      } // ATTRIBUTES_ENABLED_TEXT_PRICES
                      ?>
                      <!-- eof: Edit Prices -->
                      <h4><?php echo TEXT_ATTRIBUTES_FLAGS; ?></h4>
                      <div class="row row-eq-height">
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ff0;">
                          <strong><?php echo TEXT_ATTRIBUTES_DISPLAY_ONLY; ?></strong><br/>
                          <?php echo zen_draw_radio_field('attributes_display_only', '0', $off_attributes_display_only) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_display_only', '1', $on_attributes_display_only) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #2c54f5;">
                          <strong><?php echo TEXT_ATTRIBUTES_IS_FREE; ?></strong><br>
                          <?php echo zen_draw_radio_field('product_attribute_is_free', '0', $off_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('product_attribute_is_free', '1', $on_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ffa346;">
                          <strong><?php echo TEXT_ATTRIBUTES_DEFAULT; ?></strong><br>
                          <?php echo zen_draw_radio_field('attributes_default', '0', $off_attributes_default) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_default', '1', $on_attributes_default) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #f0f;">
                          <strong><?php echo TEXT_ATTRIBUTE_IS_DISCOUNTED; ?></strong><br>
                          <?php echo zen_draw_radio_field('attributes_discounted', '0', $off_attributes_discounted) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_discounted', '1', $on_attributes_discounted) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #d200f0;">
                          <strong><?php echo TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED; ?></strong><br>
                          <?php echo zen_draw_radio_field('attributes_price_base_included', '0', $off_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_price_base_included', '1', $on_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ff0606;">
                          <strong><?php echo TEXT_ATTRIBUTES_REQUIRED; ?></strong><br>
                          <?php echo zen_draw_radio_field('attributes_required', '0', $off_attributes_required) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_required', '1', $on_attributes_required) . '&nbsp;' . TABLE_HEADING_YES; ?>
                        </div>
                      </div>
                      <?php if (ATTRIBUTES_ENABLED_IMAGES == 'true') { ?>
                        <?php
// edit
// attributes images
                        $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                        if ($attributes_value['attributes_image'] != '') {
                          $default_directory = substr($attributes_value['attributes_image'], 0, strpos($attributes_value['attributes_image'], '/') + 1);
                        } else {
                          $default_directory = 'attributes/';
                        }
                        ?>
                        <h4><?php echo TEXT_ATTRIBUTES_IMAGE; ?></h4>
                        <div class="row">
                          <div class="col-sm-2">
                              <?php echo ($attributes_value['attributes_image'] != '' ? zen_image(DIR_WS_CATALOG_IMAGES . $attributes_value['attributes_image']) . '<br>' . $attributes_value['attributes_image'] : ''); ?>
                          </div>
                          <div class="col-sm-6 col-lg-4">
                              <?php echo zen_draw_file_field('attributes_image', '', 'class="form-control"'); ?>
                              <?php echo zen_draw_hidden_field('attributes_previous_image', $attributes_value['attributes_image']); ?>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-sm-3"><?php echo zen_draw_label(TEXT_ATTRIBUTES_IMAGE_DIR, 'img_dir') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'); ?></div>
                          <div class="col-sm-3 col-md-3 col-lg-2"><?php echo zen_draw_label(TEXT_IMAGES_OVERWRITE, 'overwrite'); ?>
                            <div class="radio">
                              <label><?php echo zen_draw_radio_field('overwrite', '0', $off_overwrite) . TABLE_HEADING_NO; ?></label>
                            </div>
                            <div class="radio">
                              <label><?php echo zen_draw_radio_field('overwrite', '1', $on_overwrite) . TABLE_HEADING_YES; ?></label>
                            </div>
                          </div>
                          <div class="col-sm-3"><?php echo zen_draw_label(TEXT_IMAGES_DELETE, 'image_delete'); ?>
                            <div class="radio">
                              <label><?php echo zen_draw_radio_field('image_delete', '0', $off_image_delete) . TABLE_HEADING_NO; ?></label>
                            </div>
                            <div class="radio">
                              <label><?php echo zen_draw_radio_field('image_delete', '1', $on_image_delete) . '&nbsp;' . TABLE_HEADING_YES; ?></label>
                            </div>
                          </div>
                        </div>
                        <hr style="border: inherit; margin: 10px 0;">
                        <?php
                      } else {
                        echo zen_draw_hidden_field('attributes_previous_image', $attributes_value['attributes_image']);
                        echo zen_draw_hidden_field('attributes_image', $attributes_value['attributes_image']);
                      } // ATTRIBUTES_ENABLED_IMAGES
                      ?>
                      <?php
                      if (DOWNLOAD_ENABLED == 'true') {
                        $download_query_raw = "SELECT products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                                               WHERE products_attributes_id = " . (int)$attributes_value['products_attributes_id'];
                        $download = $db->Execute($download_query_raw);
                        $products_attributes_filename = '';
                        $products_attributes_maxdays = 0;
                        $products_attributes_maxcount = 0;
                        if ($download->RecordCount() > 0) {
                          $products_attributes_filename = $download->fields['products_attributes_filename'];
                          $products_attributes_maxdays = $download->fields['products_attributes_maxdays'];
                          $products_attributes_maxcount = $download->fields['products_attributes_maxcount'];
                        }
                        ?>
                        <h4><?php echo TABLE_HEADING_DOWNLOAD; ?></h4>
                        <div class="row">
                          <div class="col-sm-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_TEXT_FILENAME, 'products_attributes_filename', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('products_attributes_filename', $products_attributes_filename, zen_set_field_length(TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename', 35) . 'class="form-control"'); ?>
                          </div>
                          <div class="col-sm-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_TEXT_MAX_DAYS, 'products_attributes_maxdays', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5" class="form-control"'); ?>
                          </div>
                          <div class="col-sm-3 col-lg-2">
                              <?php echo zen_draw_label(TABLE_TEXT_MAX_COUNT, 'products_attributes_maxcount', 'class="control-label"'); ?>
                              <?php echo zen_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5" class="form-control"'); ?>
                          </div>
                        </div>
                        <?php
                      } else {
                        ?>
                        <div class="row">
                          <div><?php echo TEXT_DOWNLOADS_DISABLED; ?></div>
                        </div>
                        <?php
                      }
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="10"><?php echo zen_black_line(); ?></td>

                    <?php
                  } elseif (($action == 'delete_product_attribute') && ($_GET['attribute_id'] == $attributes_value['products_attributes_id'])) {
                    echo zen_draw_hidden_field('products_filter', $_GET['products_filter']);
                    echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']);
                    echo zen_draw_hidden_field('delete_attribute_id', $_GET['attribute_id']);
                    ?>
                  <tr>
                    <td colspan="10"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td colspan="6" class="pageHeading"><?php echo PRODUCTS_ATTRIBUTES_DELETE; ?></td>
                    <td colspan="3" class="pageHeading text-center"><?php echo PRODUCTS_ATTRIBUTES_DELETE; ?></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td><b><?php echo $attributes_value['products_attributes_id']; ?></b></td>
                    <td><b><?php echo $products_name_only; ?></b></td>
                    <td><b><?php echo $options_name; ?></b></td>
                    <td><b><?php echo $values_name; ?></b></td>
                    <td class="text-right"><b><?php echo $attributes_value['options_values_price']; ?></b></td>
                    <td class="text-center"><b><?php echo $attributes_value['price_prefix']; ?></b></td>
                    <td colspan="3" class="text-center">
                      <button type="submit" class="btn btn-primary"><?php echo IMAGE_CONFIRM; ?></button>
                      <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a></td>
                    <td colspan="3">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="10"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <?php
                } else {
// attributes display listing
// calculate current total attribute price
// $attributes_values

                  $attributes_price_final = zen_get_attributes_price_final($attributes_value['products_attributes_id'], 1, $attributes_values, 'false');
                  $attributes_price_final_value = $attributes_price_final;
                  $attributes_price_final = $currencies->display_price($attributes_price_final, zen_get_tax_rate($product_check->fields['products_tax_class_id']), 1);
                  $attributes_price_final_onetime = zen_get_attributes_price_final_onetime($attributes_value['products_attributes_id'], 1, $attributes_values);
                  $attributes_price_final_onetime = $currencies->display_price($attributes_price_final_onetime, zen_get_tax_rate($product_check->fields['products_tax_class_id']), 1);
                  ?>

                  <tr>
                    <td><?php echo $attributes_value['products_attributes_id']; ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><?php echo ($attributes_value['attributes_image'] != '' ? zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . '&nbsp;' : '&nbsp;&nbsp;') . $values_name; ?></td>
                    <td class="text-right"><?php echo $attributes_value['price_prefix']; ?>&nbsp;<?php echo $attributes_value['options_values_price']; ?></td>
                    <td class="text-right"><?php echo $attributes_value['products_attributes_weight_prefix']; ?>&nbsp;<?php echo $attributes_value['products_attributes_weight']; ?></td>
                    <td class="text-right"><?php echo $attributes_value['products_options_sort_order']; ?></td>
                    <?php
                    if ($action == '') {
                      ?>
                      <td class="text-center">
                          <?php
                          if ($attributes_value['attributes_display_only'] == '0') {
                            echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_display_only' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_display_only']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_off.gif', LEGEND_ATTRIBUTES_DISPLAY_ONLY) . '</a>';
                          } else {
                            echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_display_only' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_display_only']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', LEGEND_ATTRIBUTES_DISPLAY_ONLY) . '</a>';
                          }
                          ?>&nbsp;<?php
                        if ($attributes_value['product_attribute_is_free'] == '0') {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_product_attribute_is_free' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['product_attribute_is_free']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', LEGEND_ATTRIBUTES_IS_FREE) . '</a>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_product_attribute_is_free' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['product_attribute_is_free']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', LEGEND_ATTRIBUTES_IS_FREE) . '</a>';
                        }
                        ?>&nbsp;<?php
                        if ($attributes_value['attributes_default'] == '0') {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_default' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_default']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', LEGEND_ATTRIBUTES_DEFAULT) . '</a>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_default' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_default']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', LEGEND_ATTRIBUTES_DEFAULT) . '</a>';
                        }
                        ?>&nbsp;<?php
                        if ($attributes_value['attributes_discounted'] == '0') {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_discounted' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_discounted']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_pink_off.gif', LEGEND_ATTRIBUTE_IS_DISCOUNTED) . '</a>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_discounted' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_discounted']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_pink_on.gif', LEGEND_ATTRIBUTE_IS_DISCOUNTED) . '</a>';
                        }
                        ?>&nbsp;<?php
                        if ($attributes_value['attributes_price_base_included'] == '0') {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_price_base_included' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_price_base_included']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_purple_off.gif', LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED) . '</a>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_price_base_included' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_price_base_included']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_purple_on.gif', LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED) . '</a>';
                        }
                        ?>&nbsp;<?php
                        if ($attributes_value['attributes_required'] == '0') {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_required' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_required']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_red_off.gif', LEGEND_ATTRIBUTES_REQUIRED) . '</a>';
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_required' . '&attributes_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&flag=' . $attributes_value['attributes_required']) . '" onClick="divertClick(this.href);return false;">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', LEGEND_ATTRIBUTES_REQUIRED) . '</a>';
                        }
                        ?>
                      </td>
                    <?php } else { ?>
                      <td>&nbsp;</td>
                    <?php } ?>
                    <?php
                    $new_attributes_price = '';
                    if ($attributes_value['attributes_discounted']) {
                      $new_attributes_price = zen_get_attributes_price_final($attributes_value['products_attributes_id'], 1, '', 'false');
                      $new_attributes_price2 = zen_get_discount_calc($products_filter, true, $new_attributes_price);
                      if ($new_attributes_price != $attributes_price_final_value) {
                        $new_attributes_price = '|' . $currencies->display_price($new_attributes_price2, zen_get_tax_rate($product_check->fields['products_tax_class_id']), 1);
                      } else {
                        $new_attributes_price = '';
                      }
                    }
                    ?>
                    <td class="text-right"><?php echo $attributes_price_final . $new_attributes_price . ' ' . $attributes_price_final_onetime; ?></td>
                    <?php
                    if ($action != '') {
                      ?>
                      <td width="120">&nbsp;</td>
                      <?php
                    } else {
                      ?>
                      <td class="text-right">
                        <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attribute&attribute_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a>
                        <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_product_attribute&attribute_id=' . $attributes_value['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                      </td>
                      <?php
                    }
                    ?>
                  </tr>
                  <?php
// bof: show filename if it exists
                  if (DOWNLOAD_ENABLED == 'true') {
                    $download_display_query_raw = "SELECT products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount
                                                   FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                                                   WHERE products_attributes_id = " . (int)$attributes_value['products_attributes_id'];
                    $download_display = $db->Execute($download_display_query_raw);
                    if ($download_display->RecordCount() > 0) {
                      $filename_is_missing = '';
                      if ( !zen_orders_products_downloads($download_display->fields['products_attributes_filename']) ) {
                        $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
                      } else {
                        $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
                      }
                      ?>

                      <tr>
                        <td colspan="3">&nbsp;</td>
                        <td colspan="4">
                          <table>
                            <tr>
                              <td class="smallText"><?php echo $filename_is_missing . '&nbsp;' . TABLE_TEXT_FILENAME; ?></td>
                              <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_filename']; ?>&nbsp;</td>
                              <td class="smallText">&nbsp;&nbsp;<?php echo TABLE_TEXT_MAX_DAYS_SHORT; ?></td>
                              <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_maxdays']; ?>&nbsp;</td>
                              <td class="smallText">&nbsp;&nbsp;<?php echo TABLE_TEXT_MAX_COUNT_SHORT; ?></td>
                              <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_maxcount']; ?>&nbsp;</td>
                            </tr>
                          </table>
                        </td>
                        <td colspan="3">&nbsp;</td>
                      </tr>
                      <?php
                    } // show downloads
                  }
// eof: show filename if it exists
                  ?>
                  <?php
                }
                $max_attributes_id_values = $db->Execute("SELECT MAX(products_attributes_id) + 1 AS next_id FROM " . TABLE_PRODUCTS_ATTRIBUTES);
                $next_id = $max_attributes_id_values->fields['next_id'];

//////////////////////////////////////////////////////////////
// BOF: Add dividers between Product Names and between Option Names
                // @todo: Zen4all, find a new way to do action below. This is not working without the while/MoveNext.
                /*
                  if (!$attributes_values->EOF) {
                  if ($current_attributes_products_id != $attributes_value['products_id']) {
                  ?>
                  <tr>
                  <td colspan="10"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
                  </tr>
                  <?php
                  } else {
                  if ($current_attributes_options_id != $attributes_value['options_id']) {
                  ?>
                  <tr>
                  <td colspan="10"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
                  </tr>
                  <?php
                  }
                  }
                  }
                 */
// EOF: Add dividers between Product Names and between Option Names
//////////////////////////////////////////////////////////////
                ?>

                <?php
              }
              if (($action == '')) {
                ?>
                <tr>
                  <td colspan="10"><?php echo zen_black_line(); ?></td>
                </tr>

                <!-- bof_adding -->
                <tr>
                  <td colspan="10">
                    <div class="row">
                      <div class="col-xs-6 col-sm-6"><h3><?php echo PRODUCTS_ATTRIBUTES_ADDING; ?></h3></div>
                      <div class="col-xs-6 col-sm-6 text-right">
                        <?php echo TEXT_ATTRIBUTES_INSERT_INFO; ?>&nbsp;
                        <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                      </div>
                    </div>
                    <div class="row row-eq-height">
                      <div class="col-xs-1 col-sm-1">
                        <strong><?php echo $next_id; ?></strong>
                        <?php echo zen_draw_hidden_field('attribute_id', $next_id); ?>
                        <?php echo zen_draw_hidden_field('products_id', $products_filter); ?>
                        <?php echo zen_draw_hidden_field('current_category_id', $current_category_id); ?>
                      </div>
                      <?php
                      $options_values = $db->Execute("SELECT products_options_id, products_options_name, products_options_type
                                                      FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                      WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                                      ORDER BY products_options_name");

                      $optionsDropDownArray = [];
                      foreach ($options_values as $options_value) {
                        $optionsDropDownArray[] = [
                          'id' => $options_value['products_options_id'],
                          'text' => $options_value['products_options_name'] . '&nbsp;&nbsp;&nbsp;[' . translate_type_to_name($options_value['products_options_type']) . ']' . ($show_name_numbers ? ' &nbsp; [ #' . $options_value['products_options_id'] . ' ] ' : '' )
                        ];
                      }
                      ?>
                      <div class="col-xs-6 col-sm-6 col-md-6 col-lg-5">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_NAME, 'options_id'); ?>
                          <?php echo zen_draw_pull_down_menu('options_id', $optionsDropDownArray, '', 'id="OptionName" size="' . ($action != 'delete_attribute' ? "15" : "1") . '" onchange="update_option(this.form)" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-6 col-sm-6 col-md-6 col-lg-5">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_VALUE, 'values_id', 'class="control-label"'); ?>
                        <select name="values_id[]" id="OptionValue" class="form-control" multiple="multiple" <?php echo 'size="' . ($action != 'delete_attribute' ? "15" : "1") . '"'; ?>>
                          <option selected>&lt;-- Please select an Option Name from the list ... </option>
                        </select>
                      </div>
                    </div>
                    <!-- bof: Edit Prices -->
                    <h4><?php echo TEXT_PRICES_AND_WEIGHTS; ?></h4>
                    <div class="row">
                        <?php
                        $chk_defaults = $db->Execute("SELECT products_type
                                                      FROM " . TABLE_PRODUCTS . "
                                                      WHERE products_id = " . (int)$products_filter);
// set defaults for adding attributes

                        $on_attributes_display_only = (zen_get_show_product_switch($products_filter, 'ATTRIBUTES_DISPLAY_ONLY', 'DEFAULT_', '') == 1 ? true : false);
                        $off_attributes_display_only = ($on_attributes_display_only == 1 ? false : true);
                        $on_product_attribute_is_free = (zen_get_show_product_switch($products_filter, 'ATTRIBUTE_IS_FREE', 'DEFAULT_', '') == 1 ? true : false);
                        $off_product_attribute_is_free = ($on_product_attribute_is_free == 1 ? false : true);
                        $on_attributes_default = (zen_get_show_product_switch($products_filter, 'ATTRIBUTES_DEFAULT', 'DEFAULT_', '') == 1 ? true : false);
                        $off_attributes_default = ($on_attributes_default == 1 ? false : true);
                        $on_attributes_discounted = (zen_get_show_product_switch($products_filter, 'ATTRIBUTES_DISCOUNTED', 'DEFAULT_', '') == 1 ? true : false);
                        $off_attributes_discounted = ($on_attributes_discounted == 1 ? false : true);
                        $on_attributes_price_base_included = (zen_get_show_product_switch($products_filter, 'ATTRIBUTES_PRICE_BASE_INCLUDED', 'DEFAULT_', '') == 1 ? true : false);
                        $off_attributes_price_base_included = ($on_attributes_price_base_included == 1 ? false : true);
                        $on_attributes_required = (zen_get_show_product_switch($products_filter, 'ATTRIBUTES_REQUIRED', 'DEFAULT_', '') == 1 ? true : false);
                        $off_attributes_required = ($on_attributes_required == 1 ? false : true);

                        $default_price_prefix = zen_get_show_product_switch($products_filter, 'PRICE_PREFIX', 'DEFAULT_', '');
                        $default_price_prefix = ($default_price_prefix == 1 ? '+' : ($default_price_prefix == 2 ? '-' : ''));
                        $default_products_attributes_weight_prefix = zen_get_show_product_switch($products_filter, 'PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', 'DEFAULT_', '');
                        $default_products_attributes_weight_prefix = ($default_products_attributes_weight_prefix == 1 ? '+' : ($default_products_attributes_weight_prefix == 2 ? '-' : ''));

// set image overwrite
                        $on_overwrite = true;
                        $off_overwrite = false;
                        ?>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_PRICE_PREFIX, 'price_prefix', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('price_prefix', $default_price_prefix, 'size="2" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_PRICE, 'value_price', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('value_price', '', 'size="6" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_WEIGHT_PREFIX, 'products_attributes_weight_prefix', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('products_attributes_weight_prefix', $default_products_attributes_weight_prefix, 'size="2" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_WEIGHT, 'products_attributes_weight', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('products_attributes_weight', '', 'size="6" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_OPT_SORT_ORDER, 'products_options_sort_order', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('products_options_sort_order', '', 'size="4" class="form-control"'); ?>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                          <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_ONETIME, 'attributes_price_onetime', 'class="control-label"'); ?>
                          <?php echo zen_draw_input_field('attributes_price_onetime', '', 'size="6" class="form-control"'); ?>
                      </div>

                      <?php if (ATTRIBUTES_ENABLED_PRICE_FACTOR == 'true') { ?>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR, 'attributes_price_factor', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_factor', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET, 'attributes_price_factor_offset', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_factor_offset', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_ONETIME, 'attributes_price_factor_onetime', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_factor_onetime', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-1">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET_ONETIME, 'attributes_price_factor_onetime_offset', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_factor_onetime_offset', '', 'size="6" class="form-control"'); ?>
                        </div>

                        <?php
                      } // ATTRIBUTES_ENABLED_PRICE_FACTOR
                      ?>
                    </div>
                    <hr style="border: inherit; margin: 10px 0;">
                    <?php if (ATTRIBUTES_ENABLED_QTY_PRICES == 'true') { ?>
                      <div class="row">
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_QTY_PRICES, 'attributes_qty_prices', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_qty_prices', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_QTY_PRICES_ONETIME, 'attributes_qty_prices_onetime', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_qty_prices_onetime', '', 'size="6" class="form-control"'); ?>
                        </div>
                      </div>
                      <hr style="border: inherit; margin: 10px 0;">
                      <?php
                    } // ATTRIBUTES_ENABLED_QTY_PRICES
                    ?>
                    <?php if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true') { ?>
                      <div class="row">
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_WORDS, 'attributes_price_words', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_words', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_WORDS_FREE, 'attributes_price_words_free', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_words_free', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS, 'attributes_price_letters', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_letters', '', 'size="6" class="form-control"'); ?>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS_FREE, 'attributes_price_letters_free', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('attributes_price_letters_free', '', 'size="6" class="form-control"'); ?>
                        </div>
                      </div>
                      <hr style="border: inherit; margin: 10px 0;">
                      <?php
                    } // ATTRIBUTES_ENABLED_TEXT_PRICES
                    ?>
                    <!-- eof: Edit Prices -->
                    <h4><?php echo TEXT_ATTRIBUTES_FLAGS; ?></h4>
                    <div class="row row-eq-height">
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ff0;">
                        <strong><?php echo TEXT_ATTRIBUTES_DISPLAY_ONLY; ?></strong><br/>
                        <?php echo zen_draw_radio_field('attributes_display_only', '0', $off_attributes_display_only) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_display_only', '1', $on_attributes_display_only) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #2c54f5;">
                        <strong><?php echo TEXT_ATTRIBUTES_IS_FREE; ?></strong><br>
                        <?php echo zen_draw_radio_field('product_attribute_is_free', '0', $off_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('product_attribute_is_free', '1', $on_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ffa346;">
                        <strong><?php echo TEXT_ATTRIBUTES_DEFAULT; ?></strong><br>
                        <?php echo zen_draw_radio_field('attributes_default', '0', $off_attributes_default) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_default', '1', $on_attributes_default) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #f0f;">
                        <strong><?php echo TEXT_ATTRIBUTE_IS_DISCOUNTED; ?></strong><br>
                        <?php echo zen_draw_radio_field('attributes_discounted', '0', $off_attributes_discounted) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_discounted', '1', $on_attributes_discounted) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #d200f0;">
                        <strong><?php echo TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED; ?></strong><br>
                        <?php echo zen_draw_radio_field('attributes_price_base_included', '0', $off_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_price_base_included', '1', $on_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                      <div class="col-sm-2 col-md-2 col-lg-1 text-center" style="background-color: #ff0606;">
                        <strong><?php echo TEXT_ATTRIBUTES_REQUIRED; ?></strong><br>
                        <?php echo zen_draw_radio_field('attributes_required', '0', $off_attributes_required) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_required', '1', $on_attributes_required) . '&nbsp;' . TABLE_HEADING_YES; ?>
                      </div>
                    </div>
                    <?php if (ATTRIBUTES_ENABLED_IMAGES == 'true') { ?>
                      <?php
// add
// attributes images
                      $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                      $default_directory = 'attributes/';
                      ?>
                      <h4><?php echo TEXT_ATTRIBUTES_IMAGE; ?></h4>
                      <div class="row">
                        <div class="col-sm-2">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <?php echo zen_draw_file_field('attributes_image', '', 'class="form-control"'); ?>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-3"><?php echo zen_draw_label(TEXT_ATTRIBUTES_IMAGE_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'); ?></div>
                        <div class="col-sm-3 col-md-3 col-lg-2"><?php echo zen_draw_label(TEXT_IMAGES_OVERWRITE, 'overwrite'); ?>
                          <div class="radio">
                            <label><?php echo zen_draw_radio_field('overwrite', '0', $off_overwrite) . TABLE_HEADING_NO; ?></label>
                          </div>
                          <div class="radio">
                            <label><?php echo zen_draw_radio_field('overwrite', '1', $on_overwrite) . TABLE_HEADING_YES; ?></label>
                          </div>
                        </div>
                      </div>
                      <hr style="border: inherit; margin: 10px 0;">
                      <?php
                    } // ATTRIBUTES_ENABLED_IMAGES
                    ?>
                    <?php
                    if (DOWNLOAD_ENABLED == 'true') {
                      $products_attributes_filename = '';
                      $products_attributes_maxdays = DOWNLOAD_MAX_DAYS;
                      $products_attributes_maxcount = DOWNLOAD_MAX_COUNT;
                      ?>
                      <h4><?php echo TABLE_HEADING_DOWNLOAD; ?></h4>
                      <div class="row">
                        <div class="col-sm-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_TEXT_FILENAME, 'products_attributes_filename', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_attributes_filename', $products_attributes_filename, zen_set_field_length(TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename', 35) . 'class="form-control"'); ?>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_TEXT_MAX_DAYS, 'products_attributes_maxdays', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5" class="form-control"'); ?>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                            <?php echo zen_draw_label(TABLE_TEXT_MAX_COUNT, 'products_attributes_maxcount', 'class="control-label"'); ?>
                            <?php echo zen_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5" class="form-control"'); ?>
                        </div>
                      </div>
                      <?php
                    } else {
                      ?>
                      <div class="row">
                        <div><?php echo TEXT_DOWNLOADS_DISABLED; ?></div>
                      </div>
                      <?php
                    } // end of DOWNLOAD_ENABLED section
                    ?>
                    <hr style="border: inherit; margin: 10px 0;">
                    <div class="row">
                      <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                    </div>
                  </td>
                </tr>
                <?php
              }
              ?>
            </table>
            <?php
          }
          ?>
          <?php echo'</form>'; ?>
        </div>
      </div>
      <!-- eof_adding -->

      <!-- products_attributes_eof //-->
      <!-- body_text_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <form id="divertClickProto" action="#" method="post">
      <input type="hidden" name="divertClickProto" value="" />
      <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>" />
    </form>
    <script type="text/javascript">
      function divertClick(href)
      {
          document.getElementById('divertClickProto').action = href;
          document.getElementById('divertClickProto').submit();
          return false;
      }

    </script>
    <script type="text/javascript">
      function update_option(theForm) {
          // if nothing to do, abort
          if (!theForm || !theForm.elements["options_id"] || !theForm.elements["values_id[]"])
              return;
          if (!theForm.options_id.options[theForm.options_id.selectedIndex])
              return;

          // enable hourglass
          document.body.style.cursor = "wait";

          // set initial values
          var SelectedOption = theForm.options_id.options[theForm.options_id.selectedIndex].value;
          var theField = document.getElementById("OptionValue");

          // reset the array of pulldown options so it can be repopulated
          var Opts = theField.options.length;
          while (Opts > 0) {
              Opts = Opts - 1;
              theField.options[Opts] = null;
          }

<?php echo zen_js_option_values_list('SelectedOption', 'theField'); ?>

          // turn off hourglass
          document.body.style.cursor = "default";
      }
    </script>
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
