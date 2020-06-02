<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: John 2020 May 02 Modified in v1.5.7 $
 */
require 'includes/application_top.php';
$languages = zen_get_languages();

// verify option names and values
$chk_option_names = $db->Execute("SELECT COUNT(*) AS count
                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                  WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                  LIMIT 1");
if ($chk_option_names->fields['count'] < 1) {
  $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
  zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
}

// check for damaged database, caused by users indiscriminately deleting table data
$ary = array();
$chk_option_values = $db->Execute("SELECT *
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
foreach ($chk_option_values as $item) {
  $ary[] = $item['language_id'];
}
for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
  if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary)) {
    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name)
                  VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
  }
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);
$filter = (isset($_GET['set_filter']) && $_GET['set_filter'] != '' ? (int)$_GET['set_filter'] : 0);
$max_search_results = (isset($_GET['max_search_results']) && $_GET['max_search_results'] != '' ? (int)$_GET['max_search_results'] : (int)MAX_DISPLAY_SEARCH_RESULTS);

// display or hide copier features
if (!isset($_SESSION['option_names_values_copier'])) {
  $_SESSION['option_names_values_copier'] = OPTION_NAMES_VALUES_GLOBAL_STATUS;
}
if (!isset($_GET['reset_option_names_values_copier'])) {
  $reset_option_names_values_copier = $_SESSION['option_names_values_copier'];
}

if (zen_not_null($action)) {
  switch ($action) {
    case 'set_filter':
      $exclude_array = ['action', 'page'];
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)));
      break;
    case 'set_max_search_result':
      $exclude_array = ['action', 'page'];
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)));
      break;
    case 'set_option_names_values_copier':
      $_SESSION['option_names_values_copier'] = $_GET['reset_option_names_values_copier'];
      $action = '';
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER));
      break;
    case 'add_product_option_values':
      $value_name_array = $_POST['value_name'];
      $value_id = (int)$_POST['value_id'];
      $option_id = (int)$_POST['option_id'];
      $products_options_values_sort_order = (int)$_POST['products_options_values_sort_order'];

      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                      VALUES (" . (int)$value_id . ",
                              " . (int)$languages[$i]['id'] . ",
                              '" . zen_db_input($value_name) . "',
                              " . (int)$products_options_values_sort_order . ")");
      }

      $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id)
                    VALUES (" . (int)$option_id . ", " . (int)$value_id . ")");

// alert if possible duplicate
      $duplicate_option_values = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

        if (!empty($value_name)) {
          $check = $db->Execute("SELECT pov.products_options_values_id, pov.products_options_values_name, pov.language_id
                                 FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                 LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po ON pov.products_options_values_id = pov2po.products_options_values_id
                                 WHERE pov.language_id = " . (int)$languages[$i]['id'] . "
                                 AND pov.products_options_values_name = '" . zen_db_input($value_name) . "'
                                 AND pov2po.products_options_id = " . (int)$option_id);
          if ($check->RecordCount() > 1) {
            foreach ($check as $item) {
              $check_dups .= ' - ' . $item['products_options_values_id'];
            }
            $duplicate_option_values .= ' <b>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</b> : ' . $check_dups;
          }
        }
      }
      if (!empty($duplicate_option_values)) {
        $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_VALUE_WARNING_DUPLICATE . ' ' . $duplicate_option_values, 'caution');
      }

      $exclude_array = ['action'];
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)));
      break;
    case 'update_value':
      $value_name_array = $_POST['value_name'];
      $value_id = (int)$_POST['value_id'];
      $option_id = (int)$_POST['option_id'];
      $products_options_values_sort_order = (int)$_POST['products_options_values_sort_order'];

      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

        $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                      SET products_options_values_name = '" . zen_db_input($value_name) . "',
                          products_options_values_sort_order = " . (int)$products_options_values_sort_order . "
                      WHERE products_options_values_id = " . (int)$value_id . "
                      AND language_id = " . (int)$languages[$i]['id']);
      }

      $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    SET products_options_id = " . (int)$option_id . "
                    WHERE products_options_values_id = " . (int)$value_id);


// alert if possible duplicate
      $duplicate_option_values = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

        if (!empty($value_name)) {
          $check = $db->Execute("SELECT pov.products_options_values_id, pov.products_options_values_name, pov.language_id
                                 FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                 LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po ON pov.products_options_values_id = pov2po.products_options_values_id
                                 WHERE pov.language_id = " . (int)$languages[$i]['id'] . "
                                 AND pov.products_options_values_name = '" . zen_db_input($value_name) . "'
                                 AND pov2po.products_options_id = " . (int)$option_id);

          if ($check->RecordCount() > 1) {
            foreach ($check as $item) {
              $check_dups .= ' - ' . $item['products_options_values_id'];
            }
            $duplicate_option_values .= ' <strong>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</strong> : ' . $check_dups;
          }
        }
      }
      if (!empty($duplicate_option_values)) {
        $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_VALUE_WARNING_DUPLICATE . ' ' . $duplicate_option_values, 'caution');
      }

      $exclude_array = ['action'];
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)));
      break;
    case 'delete_value':
      $value_id = (int)$_GET['value_id'];

      $zco_notifier->notify('OPTIONS_VALUES_MANAGER_DELETE_VALUE', array('value_id' => $value_id));

// remove all attributes from products with value
      $remove_attributes_query = $db->Execute("SELECT products_id, products_attributes_id, options_id, options_values_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                               WHERE options_values_id = " . (int)$value_id);
      if ($remove_attributes_query->RecordCount() > 0) {
        // clean all tables of option value
        foreach ($remove_attributes_query as $remove_attribute) {

          $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                        WHERE products_attributes_id = " . $remove_attribute['products_attributes_id']);
        }
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                      WHERE options_values_id = " . (int)$value_id);
      }

      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                    WHERE products_options_values_id = " . (int)$value_id);

      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    WHERE products_options_values_id = " . (int)$value_id);

      $exclude_array = ['action'];
      zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)));
      break;

////////////////////////////////////////////////////
// copy option values based on existance of another option value
    case 'copy_options_values_one_to_another':

      $options_id_from = (int)$_POST['options_id_from'];
      $options_values_values_id_from = (int)$_POST['options_values_values_id_from'];

      $options_id_to = (int)$_POST['options_id_to'];
      $options_values_values_id_to = (int)$_POST['options_values_values_id_to'];

      // one category of products or all products
      if ($_POST['copy_to_categories_id'] != '') {
        $products_only = $db->Execute("SELECT ptc.products_id
                                       FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                       LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON pa.products_id = ptc.products_id
                                       WHERE ptc.categories_id = " . (int)$_POST['copy_to_categories_id'] . "
                                       AND (pa.options_id = " . (int)$options_id_from . "
                                       AND pa.options_values_id = " . (int)$options_values_values_id_from . ")");
      } else {
        $products_only = $db->Execute("SELECT pa.products_id
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       WHERE pa.options_id = " . (int)$options_id_from . "
                                       AND pa.options_values_id = " . (int)$options_values_values_id_from);
      }

      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute = 0;

      if ($options_values_values_id_from == $options_values_values_id_to) {
        // cannot copy to self
        $messageStack->add(ERROR_OPTION_VALUES_COPIED . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to), 'warning');
      } else {
        if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from) || !zen_validate_options_to_options_value($options_id_to, $options_values_values_id_to)) {
          $messageStack->add(ERROR_OPTION_VALUES_COPIED_MISMATCH . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to), 'warning');
        } else {
          // check for existing combination
          if ($products_only->RecordCount() > 0) {
            // check existing matching products and add new attributes
            foreach ($products_only as $product) {
              $current_products_id = (int)$product['products_id'];
              $sql = "INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id)
                      VALUES(" . (int)$current_products_id . ", " . (int)$options_id_to . ", " . (int)$options_values_values_id_to . ")";
              $check_previous = $db->Execute("SELECT COUNT(*) AS count
                                              FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                              WHERE products_id = " . (int)$current_products_id . "
                                              AND options_id = " . (int)$options_id_to . "
                                              AND options_values_id = " . (int)$options_values_values_id_to . "
                                              LIMIT 1");
              // do not add duplicate attributes
              if ($check_previous->fields['count'] < 1) {
                $db->Execute($sql);
                $new_attribute++;
              }
            }

            // display how many products were updated
            if ($new_attribute < 1) {
              // nothing was added due to duplicates
              $messageStack->add(SUCCESS_OPTION_VALUES_COPIED . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'caution');
            } else {
              // successful addition of new attributes that were not duplicates
              $messageStack->add(SUCCESS_OPTION_VALUES_COPIED . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'success');
            }
          } else {
            // warning nothing to copy
            $messageStack->add(ERROR_OPTION_VALUES_NONE . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to) . $zc_categories, 'warning');
          }
        } // mismatch
      } // same option value
      break;
////////////////////////////////////
// fix here copy_options_values_one_to_another_options_id
////////////////////////////////////////////////////
// copy option values based on existance of another option value
    case 'copy_options_values_one_to_another_options_id':

      $options_id_from = (int)$_POST['options_id_from'];
      $options_values_values_id_from = (int)$_POST['options_values_values_id_from'];
      $copy_from_products_id = (int)$_POST['copy_from_products_id'];

      $options_id_to = (int)$_POST['options_id_to'];
      $options_values_values_id_to = (int)$_POST['options_values_values_id_to'];

      // one category of products or all products
      if ($_POST['copy_to_categories_id'] != '') {
        $products_only = $db->Execute("SELECT DISTINCT ptc.products_id
                                       FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                       LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON pa.products_id = ptc.products_id
                                       WHERE ptc.categories_id = " . (int)$_POST['copy_to_categories_id'] . "
                                       AND (pa.options_id = " . (int)$options_id_to . ")");
      } else {
        $products_only = $db->Execute("SELECT DISTINCT pa.products_id
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       WHERE pa.options_id = " . (int)$options_id_to);
      }

      $products_attributes_defaults = $db->Execute("SELECT pa.*
                                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                    WHERE pa.products_id = " . (int)$copy_from_products_id . "
                                                    AND options_id = " . (int)$options_id_from . "
                                                    AND pa.options_values_id = " . (int)$options_values_values_id_from);

      $options_id = (int)$options_id_from;
      $values_id = (int)$options_values_values_id_from;

      if (!$products_attributes_defaults->EOF) {
        $options_values_price = (float)$products_attributes_defaults->fields['options_values_price'];
        $price_prefix = zen_db_prepare_input($products_attributes_defaults->fields['price_prefix']);

        $products_options_sort_order = (int)$products_attributes_defaults->fields['products_options_sort_order'];
        $product_attribute_is_free = (int)$products_attributes_defaults->fields['product_attribute_is_free'];
        $products_attributes_weight = (float)$products_attributes_defaults->fields['products_attributes_weight'];
        $products_attributes_weight_prefix = zen_db_prepare_input($products_attributes_defaults->fields['products_attributes_weight_prefix']);
        $attributes_display_only = (int)$products_attributes_defaults->fields['attributes_display_only'];
        $attributes_default = (int)$products_attributes_defaults->fields['attributes_default'];
        $attributes_discounted = (int)$products_attributes_defaults->fields['attributes_discounted'];
        $attributes_price_base_included = (int)$products_attributes_defaults->fields['attributes_price_base_included'];

        $attributes_price_onetime = (float)$products_attributes_defaults->fields['attributes_price_onetime'];
        $attributes_price_factor = (float)$products_attributes_defaults->fields['attributes_price_factor'];
        $attributes_price_factor_offset = (float)$products_attributes_defaults->fields['attributes_price_factor_offset'];
        $attributes_price_factor_onetime = (float)$products_attributes_defaults->fields['attributes_price_factor_onetime'];
        $attributes_price_factor_onetime_offset = (float)$products_attributes_defaults->fields['attributes_price_factor_onetime_offset'];
        $attributes_qty_prices = zen_db_prepare_input($products_attributes_defaults->fields['attributes_qty_prices']);
        $attributes_qty_prices_onetime = zen_db_prepare_input($products_attributes_defaults->fields['attributes_qty_prices_onetime']);

        $attributes_price_words = (float)$products_attributes_defaults->fields['attributes_price_words'];
        $attributes_price_words_free = (int)$products_attributes_defaults->fields['attributes_price_words_free'];
        $attributes_price_letters = (float)$products_attributes_defaults->fields['attributes_price_letters'];
        $attributes_price_letters_free = (int)$products_attributes_defaults->fields['attributes_price_letters_free'];
        $attributes_required = (int)$products_attributes_defaults->fields['attributes_required'];
      }

      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute = 0;

      if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from) || ($products_attributes_defaults->EOF && !empty($copy_from_products_id))) {
        if ($products_attributes_defaults->EOF && !empty($copy_from_products_id)) {
          // bad product_id with no match
          $messageStack->add(ERROR_OPTION_VALUES_COPIED_MISMATCH_PRODUCTS_ID . $copy_from_products_id . ': ' . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from), 'warning');
        } else {
          // mismatched Option Name/Value
          $messageStack->add(ERROR_OPTION_VALUES_COPIED_MISMATCH . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from), 'warning');
        }
      } else {
        // check for existing combination
        if ($products_only->RecordCount() > 0) {
          // check existing matching products and add new attributes

          foreach ($products_only as $product) {
            $current_products_id = $product['products_id'];

//              $sql = "insert into " . TABLE_PRODUCTS_ATTRIBUTES . "(products_id, options_id, options_values_id) values('" . $current_products_id . "', '" . $options_id_from . "', '" . $options_values_values_id_from . "')";
            $sql = "INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only, attributes_default, attributes_discounted, attributes_image, attributes_price_base_included, attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime, attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime, attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free, attributes_required)
                    VALUES (" . (int)$current_products_id . ",
                            " . (int)$options_id . ",
                            " . (int)$values_id . ",
                            " . (float)$options_values_price . ",
                            '" . zen_db_input($price_prefix) . "',
                            " . (int)$products_options_sort_order . ",
                            " . (int)$product_attribute_is_free . ",
                            " . (float)$products_attributes_weight . ",
                            '" . zen_db_input($products_attributes_weight_prefix) . "',
                            " . (int)$attributes_display_only . ",
                            " . (int)$attributes_default . ",
                            " . (int)$attributes_discounted . ",
                            '" . zen_db_input($attributes_image_name) . "',
                            " . (int)$attributes_price_base_included . ",
                            " . (float)$attributes_price_onetime . ",
                            " . (float)$attributes_price_factor . ",
                            " . (float)$attributes_price_factor_offset . ",
                            " . (float)$attributes_price_factor_onetime . ",
                            " . (float)$attributes_price_factor_onetime_offset . ",
                            '" . zen_db_input($attributes_qty_prices) . "',
                            '" . zen_db_input($attributes_qty_prices_onetime) . "',
                            " . (float)$attributes_price_words . ",
                            " . (int)$attributes_price_words_free . ",
                            " . (float)$attributes_price_letters . ",
                            " . (int)$attributes_price_letters_free . ",
                            " . (int)$attributes_required . ")";

            $check_previous = $db->Execute("SELECT COUNT(*) AS count
                                            FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                            WHERE products_id = " . (int)$current_products_id . "
                                            AND options_id = " . (int)$options_id_from . "
                                            AND options_values_id = " . (int)$options_values_values_id_from . "
                                            LIMIT 1");
            // do not add duplicate attributes
            if ($check_previous->fields['count'] < 1) {
              // add new attribute
              $db->Execute($sql);
              //echo $sql . '<br>';
              $new_attribute++;
            } else {
              // ignore
              if ($_POST['copy_attributes'] == 'copy_attributes_ignore') {
                //echo 'skipped already exists: ' . $current_products_id . '<br>';
              } else {
                // delete old and add new
                //echo 'delete old and add new: ' . $current_products_id . '<br>';
                $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                              WHERE products_id = " . (int)$current_products_id . "
                              AND options_id = " . (int)$options_id_from . "
                              AND options_values_id = " . (int)$options_values_values_id_from);
                $db->Execute($sql);
                $new_attribute++;
              }
            }
          }

          // display how many products were updated
          if ($new_attribute < 1) {
            // nothing was added
            $messageStack->add(ERROR_OPTION_VALUES_NONE . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'warning');
          } else {
            // successful addition of new attributes that were not duplicates
            $messageStack->add(SUCCESS_OPTION_VALUES_COPIED . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'success');
          }
        } else {
          // warning nothing to copy
          $messageStack->add(ERROR_OPTION_VALUES_NONE . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' for: ' . $zc_categories, 'warning');
        }
      } // mismatch
      break;
////////////////////////////////////

    case ('delete_options_values_of_option_name'):

      $options_id_from = (int)$_POST['options_id_from'];
      $options_values_values_id_from = (int)$_POST['options_values_values_id_from'];

      // one category of products or all products
      if ($_POST['copy_to_categories_id'] != '') {
        $products_only = $db->Execute("SELECT ptc.products_id
                                       FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                       LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON pa.products_id = ptc.products_id
                                       WHERE ptc.categories_id = " . (int)$_POST['copy_to_categories_id'] . "
                                       AND (pa.options_id = " . (int)$options_id_from . "
                                       AND pa.options_values_id = " . (int)$options_values_values_id_from . ")");
      } else {
        $products_only = $db->Execute("SELECT pa.products_id
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       WHERE pa.options_id = " . (int)$options_id_from . "
                                       AND pa.options_values_id = " . (int)$options_values_values_id_from);
      }

      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute = 0;

      if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from)) {
        $messageStack->add(ERROR_OPTION_VALUES_DELETE_MISMATCH . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from), 'warning');
      } else {
        // check for existing combination
        if ($products_only->RecordCount() > 0) {
          // check existing matching products and add new attributes
          foreach ($products_only as $product) {
            $current_products_id = $product['products_id'];

            // check for associated downloads
            $downloads_remove_query = "SELECT products_attributes_id
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                       WHERE products_id = " . (int)$current_products_id . "
                                       AND options_id = " . (int)$options_id_from . "
                                       AND options_values_id = " . (int)$options_values_values_id_from;
            $downloads_remove = $db->Execute($downloads_remove_query);

            $remove_downloads_ids = array();
            foreach ($downloads_remove as $row) {
              $remove_downloads_ids[] = $row['products_attributes_id'];
            }
            $zco_notifier->notify('OPTIONS_VALUES_MANAGER_DELETE_VALUES_OF_OPTIONNAME', array('current_products_id' => $current_products_id, 'remove_ids' => $remove_downloads_ids, 'options_id' => $options_id_from, 'options_values_id' => $options_values_values_id_from));

            $sql = "DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                    WHERE products_id = " . (int)$current_products_id . "
                    AND options_id = " . (int)$options_id_from . "
                    AND options_values_id = " . (int)$options_values_values_id_from;
            $delete_selected = $db->Execute($sql);

            // delete associated downloads
            if (sizeof($remove_downloads_ids)) {
              $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                            WHERE products_attributes_id IN (" . implode(',', $remove_downloads_ids) . ")");
            }
            // count deleted attribute
            $new_attribute++;
          }

          // display how many products were updated
          if ($new_attribute < 1) {
            // nothing was added due to duplicates
            $messageStack->add(ERROR_OPTION_VALUES_NONE . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'caution');
          } else {
            // successful addition of new attributes that were not duplicates
            $messageStack->add(SUCCESS_OPTION_VALUES_DELETE . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . ' for: ' . $zc_categories . ' ' . $new_attribute . ' products', 'success');
          }
        } else {
          // warning nothing to copy
          $messageStack->add(ERROR_OPTION_VALUES_NONE . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to) . $zc_categories, 'warning');
        }
      } // mismatch

      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE_VAL; ?></h1>
      <div class="row">
        <div class="col-sm-4">
          <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER) ?>" class="btn btn-default" role="button"><?php echo IMAGE_EDIT_ATTRIBUTES; ?></a> &nbsp;
          <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER) ?>" class="btn btn-default" role="button"><?php echo IMAGE_OPTION_NAMES; ?></a>
        </div>
        <div class="col-sm-4">
          <?php
// toggle switch for show copier features
          $option_names_values_copier_array = array(
            array('id' => '0', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_OFF),
            array('id' => '1', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_ON),
          );
          echo zen_draw_form('set_option_names_values_copier_form', FILENAME_OPTIONS_VALUES_MANAGER, '', 'get', 'class="form-horizontal"');
          echo zen_draw_pull_down_menu('reset_option_names_values_copier', $option_names_values_copier_array, $reset_option_names_values_copier, 'onChange="this.form.submit();" class="form-control"');
          echo zen_hide_session_id();
          echo zen_draw_hidden_field('action', 'set_option_names_values_copier');
          echo '</form>';
          ?>
        </div>
        <div class="col-sm-4 text-right"><?php echo TEXT_PRODUCT_OPTIONS_INFO; ?></div>
      </div>
      <!-- value //-->
      <?php
      if ($action == 'delete_option_value') { // delete product option value
        $exclude_array = ['action'];
        $values_values = $db->Execute("SELECT products_options_values_id, products_options_values_name
                                       FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                       WHERE products_options_values_id = " . (int)$_GET['value_id'] . "
                                       AND language_id = " . (int)$_SESSION['languages_id']);
        ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <td colspan="4" class="pageHeading"><?php echo $values_values->fields['products_options_values_name']; ?></td>
              </tr>
              <?php
              $products_values = $db->Execute("SELECT p.products_id, pd.products_name, po.products_options_name, pa.options_id
                                               FROM " . TABLE_PRODUCTS . " p,
                                                    " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                                    " . TABLE_PRODUCTS_OPTIONS . " po,
                                                    " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                               WHERE pd.products_id = p.products_id
                                               AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                               AND po.language_id = " . (int)$_SESSION['languages_id'] . "
                                               AND pa.products_id = p.products_id
                                               AND pa.options_values_id = " . (int)$_GET['value_id'] . "
                                               AND po.products_options_id = pa.options_id
                                               ORDER BY pd.products_name");
              if ($products_values->RecordCount() > 0) {
                ?>
                <?php if ($products_values->RecordCount() > 10) { ?>
                  <tr>
                    <td colspan="3"><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                    <td class="text-right">
                      <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array) . 'action=delete_value'); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                      <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                    </td>
                  </tr>
                <?php } ?>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ID; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPTION_SORT_ORDER; ?></th>
                  <th><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($products_values as $products_value) { ?>
                  <tr>
                    <td class="text-right"><?php echo $products_value['products_id']; ?></td>
                    <td><?php echo $products_value['products_name']; ?></td>
                    <td class="text-right"><?php echo $options_value["products_options_sort_order"]; ?></td>
                    <td ><?php echo $products_value['products_options_name']; ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="4"><?php echo zen_black_line(); ?></td>
                </tr>
                <tr>
                  <td colspan="3"><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                  <td class="text-right">
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array) . 'action=delete_value'); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                  </td>
                </tr>
              <?php } else { ?>
                <tr>
                  <td colspan="4"><?php echo TEXT_OK_TO_DELETE; ?></td>
                </tr>
                <tr>
                  <td class="text-right" colspan="4">
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array) . 'action=delete_value'); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%'); ?>
        </div>
        <div class="row">
          <?php
          echo zen_draw_form('set_filter', FILENAME_OPTIONS_VALUES_MANAGER, '', 'get', 'class="form-horizontal"');
          echo zen_post_all_get_params();
          echo zen_draw_label(LABEL_FILTER, 'set_filter', 'class="col-sm-3 control-label"');
          ?>
          <div class="col-sm-6">
            <?php
            $filter_values_array[] = [
              'id' => '',
              'text' => PLEASE_SELECT
            ];
            $filter_values_query = "SELECT products_options_id, products_options_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                    WHERE language_id = " . $_SESSION['languages_id'] . "
                                    ORDER BY products_options_name";
            $filter_values = $db->Execute($filter_values_query);
            foreach ($filter_values as $filter_value) {
              $filter_values_array[] = [
                'id' => $filter_value['products_options_id'],
                'text' => $filter_value['products_options_name']
              ];
            }
            ?>
            <?php echo zen_draw_pull_down_menu('set_filter', $filter_values_array, $filter, 'onchange="this.form.submit();" class="form-control"'); ?>
          </div>
          <div class="col-sm-3">
            <?php
            if ($filter !== 0) {
              $exclude_array = ['set_filter', 'page'];
              ?>
              <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, zen_get_all_get_params($exclude_array)); ?>" class="btn btn-default"><?php echo IMAGE_RESET; ?></a>
            <?php } ?>
          </div>
          <?php
          echo zen_draw_hidden_field('action', 'set_filter');
          echo zen_hide_session_id();
          echo '</form>';
          ?>
        </div>
        <?php
        $values_query_raw = "SELECT pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id, pov.products_options_values_sort_order
                             FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                             LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po ON pov2po.products_options_values_id = pov.products_options_values_id
                             LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON po.products_options_id = pov2po.products_options_id
                               AND po.language_id = " . (int)$_SESSION['languages_id'] . "
                             WHERE pov.language_id = " . (int)$_SESSION['languages_id'] . "
                             AND pov2po.products_options_values_id != " . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "
                             " . (isset($filter) && $filter != '' ? " AND po.products_options_id = " . (int)$filter : "") . "
                             ORDER BY po.products_options_name, LPAD(pov.products_options_values_sort_order,11,'0'), pov.products_options_values_name";
        $values_split = new splitPageResults($currentPage, $max_search_results, $values_query_raw, $values_query_numrows);
        ?>
        <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif') ?>
          <?php
          $max_search_results_array = [
            ['id' => $values_query_numrows,
              'text' => TEXT_SHOW_ALL],
            ['id' => '5',
              'text' => '5'],
            ['id' => '10',
              'text' => '10'],
            ['id' => '15',
              'text' => '15'],
            ['id' => '20',
              'text' => '20'],
            ['id' => '25',
              'text' => '25'],
            ['id' => '50',
              'text' => '50'],
          ];
          ?>
          <div class="col-sm-1">
            <?php
            echo zen_draw_form('set_max_search_result', FILENAME_OPTIONS_VALUES_MANAGER, '', 'get', 'class="form-horizontal"');
            echo zen_post_all_get_params();
            ?>
            <div class="form-group">
              <?php echo zen_draw_pull_down_menu('max_search_results', $max_search_results_array, $max_search_results, 'onchange="this.form.submit();" class="form-control"'); ?>
            </div>
            <?php
            echo zen_draw_hidden_field('action', 'set_max_search_result');
            echo zen_hide_session_id();
            echo '</form>';
            ?>
          </div>
          <div class="col-sm-3">
            <?php echo $values_split->display_count($values_query_numrows, $max_search_results, $currentPage, TEXT_DISPLAY_NUMBER_OF_OPTION_VALUES); ?></div>
          <?php $exclude_array = ['page']; ?>
          <div class="col-sm-8 text-right"><?php echo $values_split->display_links($values_query_numrows, $max_search_results, MAX_DISPLAY_PAGE_LINKS, $currentPage, zen_get_all_get_params($exclude_array)); ?></div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_OPTION_VALUE_SORT_ORDER; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $next_id = 1;
              $values_values = $db->Execute($values_query_raw);
              foreach ($values_values as $values_value) {
                $options_name = zen_options_name($values_value['products_options_id']);
// iii 030813 added: Option Type Feature and File Uploading
// fetch products_options_id for use if the option value is deleted
// with TEXT and FILE Options, there are multiple options for the single TEXT
// value and only the single reference should be deleted
                $option_id = $values_value['products_options_id'];

                $values_name = $values_value['products_options_values_name'];
                $products_options_values_sort_order = $values_value['products_options_values_sort_order'];
                ?>
                <tr>
                  <?php
// edit option values
                  if (($action == 'update_option_value') && ($_GET['value_id'] == $values_value['products_options_values_id'])) {
                    echo zen_draw_form('values', FILENAME_OPTIONS_VALUES_MANAGER, 'action=update_value' . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . ($filter !== 0 ? 'set_filter=' . $filter . '&' : '') . ($max_search_results != 0 ? 'max_search_results=' . $max_search_results : ''), 'post', 'class="form-horizontal"');
                    $inputs = '';
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $value_name = $db->Execute("SELECT products_options_values_name
                                                  FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                  WHERE products_options_values_id = " . (int)$values_value['products_options_values_id'] . "
                                                  AND language_id = " . (int)$languages[$i]['id']);
                      $inputs .= '<div class="form-group">';
                      $inputs .= '<div class="input-group">';
                      $inputs .= '<span class="input-group-addon">';
                      $inputs .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);
                      $inputs .= '</span>';
                      $inputs .= zen_draw_input_field('value_name[' . $languages[$i]['id'] . ']', zen_output_string($value_name->fields['products_options_values_name']), zen_set_field_length(TABLE_PRODUCTS_OPTIONS_VALUES, 'products_options_values_name', 50) . ' class="form-control"');
                      $inputs .= '</div>';
                      $inputs .= '</div>';
                    }
                    $products_options_values_sort_order = $db->Execute("SELECT distinct products_options_values_sort_order
                                                                        FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                                        WHERE products_options_values_id = " . (int)$values_value['products_options_values_id']);
                    ?>
                    <td class="attributeBoxContent text-right">
                      <?php echo $values_value['products_options_values_id']; ?>
                      <?php echo zen_draw_hidden_field('value_id', $values_value['products_options_values_id']); ?>
                    </td>
                    <td class="attributeBoxContent">
                      <?php
                      $options_values = $db->Execute("SELECT products_options_id, products_options_name, products_options_type
                                                      FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                      WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                                      AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "
                                                      AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_FILE . "
                                                      ORDER BY products_options_name");

                      $optionsValueArray = [];
                      foreach ($options_values as $options_value) {
                        $optionsValueArray[] = array(
                          'id' => $options_value['products_options_id'],
                          'text' => $options_value['products_options_name']);
                      }
                      ?>
                      <?php echo zen_draw_pull_down_menu('option_id', $optionsValueArray, $values_value['products_options_id'], 'class="form-control"'); ?>
                    </td>
                    <td class="attributeBoxContent"><?php echo $inputs; ?></td>
                    <td class="attributeBoxContent text-right"><?php echo zen_draw_input_field('products_options_values_sort_order', $products_options_values_sort_order->fields['products_options_values_sort_order'], 'size="4" class="form-control"'); ?></td>
                    <td class="attributeBoxContent text-right">
                      <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                      <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . ($filter !== 0 ? 'set_filter=' . $filter . '&' : '') . ($max_search_results != 0 ? 'max_search_results=' . $max_search_results : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL ?></a>
                    </td>
                    <?php
                    echo '</form>';
                  } else {
                    ?>
                    <td class="text-right"><?php echo $values_value["products_options_values_id"]; ?></td>
                    <td><?php echo $options_name; ?></td>
                    <td><?php echo $values_name; ?></td>
                    <td class="text-right"><?php echo $values_value['products_options_values_sort_order']; ?></td>
                    <?php if ($action == 'update_option_value') { ?>
                      <td>&nbsp;</td>
                    <?php } else { ?>
                      <td class="text-right">
                        <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=update_option_value&value_id=' . $values_value['products_options_values_id'] . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . ($filter !== 0 ? 'set_filter=' . $filter . '&' : '') . ($max_search_results != 0 ? 'max_search_results=' . $max_search_results : '')); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a>
                        <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_option_value&value_id=' . $values_value['products_options_values_id'] . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . ($filter !== 0 ? 'set_filter=' . $filter . '&' : '') . ($max_search_results != 0 ? 'max_search_results=' . $max_search_results : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_DELETE; ?></a>
                      </td>
                    <?php } ?>
                    <?php
                  }
                }
                $max_values_id_values = $db->Execute("SELECT MAX(products_options_values_id) + 1 AS next_id
                                                      FROM " . TABLE_PRODUCTS_OPTIONS_VALUES);

                $next_id = $max_values_id_values->fields['next_id'];
                ?>
              </tr>
              <?php if ($action != 'update_option_value') { ?>
                <tr>
                  <?php echo zen_draw_form('values', FILENAME_OPTIONS_VALUES_MANAGER, 'action=add_product_option_values' . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . ($filter !== 0 ? 'set_filter=' . $filter . '&' : '') . ($max_search_results != 0 ? 'max_search_results=' . $max_search_results : ''), 'post', 'class="form-horizontal"'); ?>
                  <td colspan="4">
                    <?php
                    $options_values = $db->Execute("SELECT products_options_id, products_options_name, products_options_type
                                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                    WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                                    AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "
                                                    AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_FILE . "
                                                    ORDER BY products_options_name");

                    $optionsValueArray = [];
                    foreach ($options_values as $options_value) {
                      $optionsValueArray[] = array(
                        'id' => $options_value['products_options_id'],
                        'text' => $options_value['products_options_name']);
                    }
                    ?>
                    <div class="col-md-4">
                      <div class="form-group">
                        <?php echo zen_draw_pull_down_menu('option_id', $optionsValueArray, '', 'class="form-control"'); ?>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <?php for ($i = 0, $n = sizeof($languages); $i < $n; $i++) { ?>
                        <div class="form-group">
                          <div class="input-group">
                            <span class="input-group-addon"><?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></span>
                            <?php echo zen_draw_input_field('value_name[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_PRODUCTS_OPTIONS_VALUES, 'products_options_values_name', 50) . ' class="form-control"'); ?>
                          </div>
                        </div>
                      <?php } ?>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <?php echo zen_draw_label(TEXT_SORT, 'products_options_values_sort_order', 'class="control-label"'); ?>
                        <?php echo zen_draw_input_field('products_options_values_sort_order', '', 'size="4" class="form-control"'); ?>
                      </div>
                    </div>
                  </td>
                  <td class="text-right">
                    <?php echo zen_draw_hidden_field('value_id', $next_id); ?>
                    <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                  </td>
                  <?php
                  echo '</form>';
                  ?>
                </tr>
                <tr>
                  <td colspan="5"><?php echo zen_black_line(); ?></td>
                </tr>
                <?php
              }
            }
            ?>
          </tbody>
        </table>
      </div>


      <?php if ($_SESSION['option_names_values_copier'] == '0') { ?>
        <div class="row">
          <h2 class="text-center"><?php echo TEXT_INFO_OPTION_NAMES_VALUES_COPIER_STATUS; ?></h2>
        </div>
      <?php } else { ?>

        <?php
        // bof: build dropdowns for delete and add

        /*
          this builds the resulting values for use in the case statements above
          $options_id_from = $_POST['options_id_from'];
          $options_values_values_id_from = $_POST['options_values_values_id_from'];

          $options_id_to = $_POST['options_id_to'];
          $options_values_values_id_to = $_POST['options_values_values_id_to'];
         */

        // build dropdown for option_name from
        $options_values_from = $db->Execute("SELECT *
                                             FROM " . TABLE_PRODUCTS_OPTIONS . "
                                             WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                             AND products_options_name != ''
                                             AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "
                                             AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_FILE . "
                                             ORDER BY products_options_name");
        $option_from_dropdown = [];
        foreach ($options_values_from as $item) {
          $option_from_dropdown[] = array(
            'id' => $options_values_from->fields['products_options_id'],
            'text' => $options_values_from->fields['products_options_name']);
        }

        $option_to_dropdown = $option_from_dropdown;

        // build dropdown for option_values from
        $options_values_values_from = $db->Execute("SELECT *
                                                    FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                    WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                                    AND products_options_values_id != 0
                                                    ORDER BY products_options_values_name");

        $option_values_from_dropdown = [];
        foreach ($options_values_values_from as $item) {
          $show_option_name = '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($item['products_options_values_id'])) . ']';

          $option_values_from_dropdown[] = array(
            'id' => $item['products_options_values_id'],
            'text' => $item['products_options_values_name'] . $show_option_name);
        }

        $option_values_to_dropdown = $option_values_from_dropdown;

        $to_categories_id = zen_draw_label(TEXT_SELECT_OPTION_VALUES_TO_CATEGORIES_ID, 'copy_to_categories_id', 'class="control-label"') . zen_draw_input_field('copy_to_categories_id', '', 'size="4" class="form-control"');

        $options_id_from_products_id = zen_draw_label(TEXT_SELECT_OPTION_FROM_PRODUCTS_ID, 'copy_from_products_id', 'class="control-label"') . zen_draw_input_field('copy_from_products_id', '', 'size="4" class="form-control"');

        // eof: build dropdowns for delete and add
        ?>

        <!--
        bof: copy Option Name and Value From to Option Name and Value to - all products
        example: Copy Color Red to products with Size Small
        -->
        <div class="table-responsive" style="border: 2px solid #999;">
          <table class="table">
            <tr>
              <td colspan="4"><?php echo TEXT_OPTION_VALUE_COPY_ALL; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php echo TEXT_INFO_OPTION_VALUE_COPY_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_VALUES_MANAGER, 'action=copy_options_values_one_to_another', 'post', 'class="form-horizontal"'); ?>
              <td class="dataTableHeadingContent">
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_FROM, 'options_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_id_from', $option_from_dropdown, '', 'class="form-control"'); ?><br />
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_VALUES_FROM, 'options_values_values_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_values_values_id_from', $option_values_from_dropdown, '', 'class="form-control"'); ?>
              </td>
              <td class="dataTableHeadingContent">
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_TO, 'options_id_to', 'class="control-label"') . zen_draw_pull_down_menu('options_id_to', $option_to_dropdown, '', 'class="form-control"'); ?><br />
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_VALUES_TO, 'options_values_values_id_to', 'class="control-label"') . zen_draw_pull_down_menu('options_values_values_id_to', $option_values_to_dropdown, '', 'class="form-control"'); ?>
              </td>
              <td class="dataTableHeadingContent"><?php echo $to_categories_id; ?></td>
              <td class="dataTableHeadingContent text-center">
                <button type="submit" class="btn btn-warning"><?php echo IMAGE_INSERT; ?></button>
              </td>
              <?php echo '</form>'; ?>
            </tr>
          </table>
        </div>
        <!-- eof: copy all option values to another Option Name -->
        <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>

        <!--
        bof: delete all Option Name for an Value
        example: Delete Color Red
        -->
        <div class="table-responsive" style="border: 2px solid #999;">
          <table class="table">
            <tr>
              <td colspan="3"><?php echo TEXT_OPTION_VALUE_DELETE_ALL; ?></td>
            </tr>
            <tr>
              <td colspan="3"><?php echo TEXT_INFO_OPTION_VALUE_DELETE_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_options_values_of_option_name', 'post', 'class="form-horizontal"'); ?>
              <td class="dataTableHeadingContent">
                <?php echo zen_draw_label(TEXT_SELECT_DELETE_OPTION_FROM, 'options_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_id_from', $option_from_dropdown, '', 'class="form-control"'); ?><br />
                <?php echo zen_draw_label(TEXT_SELECT_DELETE_OPTION_VALUES_FROM, 'options_values_values_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_values_values_id_from', $option_values_from_dropdown, '', 'class="form-control"'); ?>
              </td>
              <td class="dataTableHeadingContent"><?php echo $to_categories_id; ?></td>
              <td class="dataTableHeadingContent text-center">
                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
              </td>
              <?php echo '</form>'; ?>
            </tr>
          </table>
        </div>
        <!-- eof: delete all matching option name for option values -->
        <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?>
        </div>

        <!--
        bof: copy Option Name and Value From to Option Name and Value to - all products
        example: Copy Color Red to products with Size Small
        -->
        <div class="table-responsive" style="border: 2px solid #999;">
          <table class="table">
            <tr>
              <td colspan="4"><?php echo TEXT_OPTION_VALUE_COPY_OPTIONS_TO; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php echo TEXT_INFO_OPTION_VALUE_COPY_OPTIONS_TO; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <?php echo zen_draw_form('quick_jump', FILENAME_OPTIONS_VALUES_MANAGER, 'action=copy_options_values_one_to_another_options_id', 'post', 'class="form-horizontal"'); ?>
              <td class="dataTableHeadingContent">
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_FROM_ADD, 'options_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_id_from', $option_from_dropdown, '', 'class="form-control"'); ?><br />
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_VALUES_FROM_ADD, 'options_values_values_id_from', 'class="control-label"') . zen_draw_pull_down_menu('options_values_values_id_from', $option_values_from_dropdown, '', 'class="form-control"'); ?><br />
                <?php echo $options_id_from_products_id; ?>
              </td>
              <td class="dataTableHeadingContent">
                <?php echo zen_draw_label(TEXT_SELECT_OPTION_TO_ADD_TO, 'options_id_to', 'class="control-label"') . zen_draw_pull_down_menu('options_id_to', $option_to_dropdown, '', 'class="form-control"'); ?>
              </td>
              <td class="dataTableHeadingContent">
                <?php echo $to_categories_id; ?><br />
                <?php echo TEXT_COPY_ATTRIBUTES_CONDITIONS; ?><br />
                <div class="radio">
                  <label>
                    <?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . TEXT_COPY_ATTRIBUTES_UPDATE; ?>
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <?php echo zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore', true) . TEXT_COPY_ATTRIBUTES_IGNORE; ?>
                  </label>
                </div>
              </td>
              <td class="dataTableHeadingContent text-center">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
              </td>
              <?php echo '</form>'; ?>
            </tr>
            <!-- eof: copy all option values to another Option Name -->
          <?php } ?>
        </table>
      </div>
      <!-- option value eof //-->
      <!-- body_text_eof //-->
      <!-- footer //-->
      <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
      <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
