<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: ajeh  Fri Oct 19 13:10:27 2012 -0400 Modified in v1.5.5 $
 */

  require('includes/application_top.php');

  // verify option names and values
  $chk_option_names = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS_OPTIONS . " where language_id='" . (int)$_SESSION['languages_id'] . "' limit 1");
  if ($chk_option_names->fields['count'] < 1) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
    zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
  }

  // check for damaged database, caused by users indiscriminately deleting table data
  $ary = array();
  $chk_option_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id=" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
  while (!$chk_option_values->EOF) {
    $ary[] = $chk_option_values->fields['language_id'];
    $chk_option_values->MoveNext();
  }
  $languages = zen_get_languages();
  for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
    if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary)) {
      $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

// display or hide copier features
  if (!isset($_SESSION['option_names_values_copier'])) {
    $_SESSION['option_names_values_copier'] = OPTION_NAMES_VALUES_GLOBAL_STATUS;
  }
  if (!isset($_GET['reset_option_names_values_copier'])) {
    $reset_option_names_values_copier = $_SESSION['option_names_values_copier'];
  }

  if (zen_not_null($action)) {
    $_SESSION['page_info'] = '';
    if (isset($_GET['option_page'])) $_SESSION['page_info'] .= 'option_page=' . $_GET['option_page'] . '&';
    if (isset($_GET['value_page'])) $_SESSION['page_info'] .= 'value_page=' . $_GET['value_page'] . '&';
    if (isset($_GET['attribute_page'])) $_SESSION['page_info'] .= 'attribute_page=' . $_GET['attribute_page'] . '&';
    if (zen_not_null($_SESSION['page_info'])) {
      $_SESSION['page_info'] = substr($_SESSION['page_info'], 0, -1);
    }

    switch ($action) {
      case 'set_option_names_values_copier':
        $_SESSION['option_names_values_copier'] = $_GET['reset_option_names_values_copier'];
        $action='';
        zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER));
        break;
      case 'add_product_option_values':
        $value_name_array = $_POST['value_name'];
        $value_id = zen_db_prepare_input($_POST['value_id']);
        $option_id = zen_db_prepare_input($_POST['option_id']);
        $products_options_values_sort_order = zen_db_prepare_input($_POST['products_options_values_sort_order']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

          $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                      (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                      values ('" . (int)$value_id . "',
                              '" . (int)$languages[$i]['id'] . "',
                              '" . zen_db_input($value_name) . "',
                              '" . (int)$products_options_values_sort_order . "')");
        }

        $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    (products_options_id, products_options_values_id)
                    values ('" . (int)$option_id . "', '" . (int)$value_id . "')");

// alert if possible duplicate
        $duplicate_option_values= '';
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

          if (!empty($value_name)) {
            $check= $db->Execute("select pov.products_options_values_id, pov.products_options_values_name, pov.language_id
                                from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id
                                where pov.language_id= '" . (int)$languages[$i]['id'] . "'
                                and pov.products_options_values_name='" . zen_db_input($value_name) . "'
                                and pov2po.products_options_id ='" . (int)$option_id .
                                "'");
            if ($check->RecordCount() > 1) {
              while (!$check->EOF) {
                $check_dups .= ' - ' . $check->fields['products_options_values_id'];
                $check->MoveNext();
              }
              $duplicate_option_values .= ' <b>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</b> : ' . $check_dups;
            }
          }
        }
        if (!empty($duplicate_option_values)) {
          $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_VALUE_WARNING_DUPLICATE . ' ' . $duplicate_option_values, 'caution');
        }

        zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, $_SESSION['page_info']));
        break;
      case 'update_value':
        $value_name_array = $_POST['value_name'];
        $value_id = zen_db_prepare_input($_POST['value_id']);
        $option_id = zen_db_prepare_input($_POST['option_id']);
        $products_options_values_sort_order = zen_db_prepare_input($_POST['products_options_values_sort_order']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

          $db->Execute("update " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                        set products_options_values_name = '" . zen_db_input($value_name) . "', products_options_values_sort_order = '" . (int)$products_options_values_sort_order . "'
                        where products_options_values_id = '" . zen_db_input($value_id) . "'
                        and language_id = '" . (int)$languages[$i]['id'] . "'");

        }

        $db->Execute("update " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                      set products_options_id = '" . (int)$option_id . "'
                      where products_options_values_id = '" . (int)$value_id . "'");


// alert if possible duplicate
        $duplicate_option_values= '';
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = zen_db_prepare_input($value_name_array[$languages[$i]['id']]);

          if (!empty($value_name)) {
            $check= $db->Execute("select pov.products_options_values_id, pov.products_options_values_name, pov.language_id
                                from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id
                                where pov.language_id= '" . (int)$languages[$i]['id'] . "'
                                and pov.products_options_values_name='" . zen_db_input($value_name) . "'
                                and pov2po.products_options_id ='" . (int)$option_id .
                                "'");

            if ($check->RecordCount() > 1) {
              while (!$check->EOF) {
                $check_dups .= ' - ' . $check->fields['products_options_values_id'];
                $check->MoveNext();
              }
              $duplicate_option_values .= ' <b>' . strtoupper(zen_get_language_name($languages[$i]['id'])) . '</b> : ' . $check_dups;
            }
          }
        }
        if (!empty($duplicate_option_values)) {
          $messageStack->add_session(ATTRIBUTE_POSSIBLE_OPTIONS_VALUE_WARNING_DUPLICATE . ' ' . $duplicate_option_values, 'caution');
        }

        zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, $_SESSION['page_info']));
        break;
      case 'delete_value':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, $_SESSION['page_info']));
        }
        $value_id = zen_db_prepare_input($_GET['value_id']);

        $zco_notifier->notify('OPTIONS_VALUES_MANAGER_DELETE_VALUE', array('value_id' => $value_id));

// remove all attributes from products with value
        $remove_attributes_query = $db->Execute("select products_id, products_attributes_id, options_id, options_values_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where options_values_id ='" . (int)$value_id . "'");
        if ($remove_attributes_query->RecordCount() > 0) {
          // clean all tables of option value
          while (!$remove_attributes_query->EOF) {

            $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                          where products_attributes_id='" . $remove_attributes_query->fields['products_attributes_id'] . "'");

            $remove_attributes_query->MoveNext();
          }
          $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . "
                        where options_values_id='" . (int)$value_id . "'");
        }

        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                      where products_options_values_id = '" . (int)$value_id . "'");

        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                      where products_options_values_id = '" . (int)$value_id . "'");

        zen_redirect(zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, $_SESSION['page_info']));
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
        $products_only = $db->Execute("select ptc.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES  . " ptc left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa on pa.products_id=ptc.products_id where ptc.categories_id='" . (int)$_POST['copy_to_categories_id'] . "' and (pa.options_id='" . (int)$options_id_from . "' and pa.options_values_id='" . (int)$options_values_values_id_from . "')");
      } else {
        $products_only = $db->Execute("select pa.products_id from " . TABLE_PRODUCTS_ATTRIBUTES  . " pa where pa.options_id='" . (int)$options_id_from . "' and pa.options_values_id='" . (int)$options_values_values_id_from . "'");
      }

/*
// debug code
            while(!$products_only->EOF) {
              echo 'Product ' . $products_only->fields['products_id'] . '<br>';
              $products_only->MoveNext();
            }


die('I SEE match from: ' . $options_id_from . '-' . $options_values_values_id_from . ' add to: ' . $options_id_to . ' -' . $options_values_values_id_to . ' | only for cat ' . $_POST['copy_to_categories_id'] . ' | found matches ' . $products_only->RecordCount());
*/


      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute=0;

      if ($options_values_values_id_from == $options_values_values_id_to) {
        // cannot copy to self
        $messageStack->add(ERROR_OPTION_VALUES_COPIED . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to), 'warning');
      } else {
        if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from) or !zen_validate_options_to_options_value($options_id_to, $options_values_values_id_to)) {
          $messageStack->add(ERROR_OPTION_VALUES_COPIED_MISMATCH . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from) . TEXT_INFO_TO . zen_options_name($options_id_to) . ' ' . zen_values_name($options_values_values_id_to), 'warning');
        } else {
          // check for existing combination
          if ($products_only->RecordCount() > 0) {
            // check existing matching products and add new attributes
            while(!$products_only->EOF) {
              $current_products_id = $products_only->fields['products_id'];
              $sql = "insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id) values('" . $current_products_id . "', '" . $options_id_to . "', '" . $options_values_values_id_to . "')";
              $check_previous = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $current_products_id . "' and options_id='" . $options_id_to . "' and options_values_id='" . $options_values_values_id_to . "' limit 1");
              // do not add duplicate attributes
              if ($check_previous->fields['count'] < 1) {
                $db->Execute($sql);
                $new_attribute++;
              }
              $products_only->MoveNext();
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
        $products_only = $db->Execute("select distinct ptc.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES  . " ptc left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa on pa.products_id=ptc.products_id where ptc.categories_id='" . (int)$_POST['copy_to_categories_id'] . "' and (pa.options_id='" . $options_id_to . "')");
      } else {
        $products_only = $db->Execute("select distinct pa.products_id from " . TABLE_PRODUCTS_ATTRIBUTES  . " pa where pa.options_id='" . $options_id_to . "'");
      }

      $products_attributes_defaults = $db->Execute("select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES  . " pa where pa.products_id = '" . $copy_from_products_id . "' and options_id='" . $options_id_from . "' and pa.options_values_id='" . $options_values_values_id_from . "'");

      $options_id = zen_db_prepare_input($options_id_from);
      $values_id = zen_db_prepare_input($options_values_values_id_from);

      if (!$products_attributes_defaults->EOF) {
        $options_values_price = zen_db_prepare_input($products_attributes_defaults->fields['options_values_price']);
        $price_prefix = zen_db_prepare_input($products_attributes_defaults->fields['price_prefix']);

        $products_options_sort_order = zen_db_prepare_input($products_attributes_defaults->fields['products_options_sort_order']);
        $product_attribute_is_free = zen_db_prepare_input($products_attributes_defaults->fields['product_attribute_is_free']);
        $products_attributes_weight = zen_db_prepare_input($products_attributes_defaults->fields['products_attributes_weight']);
        $products_attributes_weight_prefix = zen_db_prepare_input($products_attributes_defaults->fields['products_attributes_weight_prefix']);
        $attributes_display_only = zen_db_prepare_input($products_attributes_defaults->fields['attributes_display_only']);
        $attributes_default = zen_db_prepare_input($products_attributes_defaults->fields['attributes_default']);
        $attributes_discounted = zen_db_prepare_input($products_attributes_defaults->fields['attributes_discounted']);
        $attributes_price_base_included = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_base_included']);

        $attributes_price_onetime = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_onetime']);
        $attributes_price_factor = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_factor']);
        $attributes_price_factor_offset = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_factor_offset']);
        $attributes_price_factor_onetime = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_factor_onetime']);
        $attributes_price_factor_onetime_offset = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_factor_onetime_offset']);
        $attributes_qty_prices = zen_db_prepare_input($products_attributes_defaults->fields['attributes_qty_prices']);
        $attributes_qty_prices_onetime = zen_db_prepare_input($products_attributes_defaults->fields['attributes_qty_prices_onetime']);

        $attributes_price_words = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_words']);
        $attributes_price_words_free = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_words_free']);
        $attributes_price_letters = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_letters']);
        $attributes_price_letters_free = zen_db_prepare_input($products_attributes_defaults->fields['attributes_price_letters_free']);
        $attributes_required = zen_db_prepare_input($products_attributes_defaults->fields['attributes_required']);
      }

/*
/////
// debug code
            while(!$products_only->EOF) {
              echo 'Product ' . $products_only->fields['products_id'] . '<br>';
              $products_only->MoveNext();
            }


die('I SEE match from products_id:' . $copy_from_products_id . ' options_id_from: ' . $options_id_from . '-' . $options_values_values_id_from . ' add to: ' . $options_id_to . ' | only for cat ' . $_POST['copy_to_categories_id'] . ' | found matches ' . $products_only->RecordCount() . '<br>' .
'from products_id: ' . $products_attributes_defaults->fields['products_id'] . ' option_id: ' . $products_attributes_defaults->fields['options_id'] . ' options_values_id: ' . $products_attributes_defaults->fields['options_values_id']
);
/////
*/

      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute=0;

        if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from) or ($products_attributes_defaults->EOF and $copy_from_products_id != '')) {
          if ($products_attributes_defaults->EOF and $copy_from_products_id != '') {
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

            while(!$products_only->EOF) {
              $current_products_id = $products_only->fields['products_id'];

//              $sql = "insert into " . TABLE_PRODUCTS_ATTRIBUTES . "(products_id, options_id, options_values_id) values('" . $current_products_id . "', '" . $options_id_from . "', '" . $options_values_values_id_from . "')";
                $sql = "insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_attributes_id, products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only, attributes_default, attributes_discounted, attributes_image, attributes_price_base_included, attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime, attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime, attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free, attributes_required)
                          values (0,
                                  '" . (int)$current_products_id . "',
                                  '" . (int)$options_id . "',
                                  '" . (int)$values_id . "',
                                  '" . zen_db_input($options_values_price) . "',
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
                                  '" . (int)zen_db_input($attributes_required) . "')";

              $check_previous = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $current_products_id . "' and options_id='" . $options_id_from . "' and options_values_id='" . $options_values_values_id_from . "' limit 1");
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
                  $db->Execute("DELETE from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $current_products_id . "' and options_id='" . $options_id_from . "' and options_values_id='" . $options_values_values_id_from . "'");
                  $db->Execute($sql);
                  $new_attribute++;
                }
              }
              $products_only->MoveNext();
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
        $products_only = $db->Execute("select ptc.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES  . " ptc left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa on pa.products_id=ptc.products_id where ptc.categories_id='" . (int)$_POST['copy_to_categories_id'] . "' and (pa.options_id='" . $options_id_from . "' and pa.options_values_id='" . $options_values_values_id_from . "')");
      } else {
        $products_only = $db->Execute("select pa.products_id from " . TABLE_PRODUCTS_ATTRIBUTES  . " pa where pa.options_id='" . $options_id_from . "' and pa.options_values_id='" . $options_values_values_id_from . "'");
      }

      if ($_POST['copy_to_categories_id'] == '') {
        $zc_categories = ' All Products ';
      } else {
        $zc_categories = ' Category: ' . (int)$_POST['copy_to_categories_id'];
      }

      $new_attribute=0;

      if (!zen_validate_options_to_options_value($options_id_from, $options_values_values_id_from)) {
        $messageStack->add(ERROR_OPTION_VALUES_DELETE_MISMATCH . TEXT_INFO_FROM . zen_options_name($options_id_from) . ' ' . zen_values_name($options_values_values_id_from), 'warning');
      } else {
        // check for existing combination
        if ($products_only->RecordCount() > 0) {
          // check existing matching products and add new attributes
          while(!$products_only->EOF) {
            $current_products_id = $products_only->fields['products_id'];

            // check for associated downloads
            $downloads_remove_query = "select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $current_products_id . "' and options_id='" . $options_id_from . "' and options_values_id='" . $options_values_values_id_from . "'";
            $downloads_remove = $db->Execute($downloads_remove_query);

            $remove_downloads_ids = array();
            foreach($downloads_remove as $row) {
              $remove_downloads_ids[] = $row['products_attributes_id'];
            }
            $zco_notifier->notify('OPTIONS_VALUES_MANAGER_DELETE_VALUES_OF_OPTIONNAME', array('current_products_id' => $current_products_id, 'remove_ids' => $remove_downloads_ids, 'options_id'=>$options_id_from, 'options_values_id'=>$options_values_values_id_from));

            $sql = "delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $current_products_id . "' and options_id='" . $options_id_from . "' and options_values_id='" . $options_values_values_id_from . "'";
            $delete_selected = $db->Execute($sql);

            // delete associated downloads
            if (sizeof($remove_downloads_ids)) {
              $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                            where products_attributes_id in (" . implode(',', $remove_downloads_ids) . ")");
            }
            // count deleted attribute
            $new_attribute++;
            $products_only->MoveNext();
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
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
//--></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <table border="0" width="75%" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td width="100%">
           <table width="100%" border="0" cellspacing="0" cellpadding="2">
             <tr>
               <td height="40" valign="bottom">
                 <a href="<?php echo  zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES); ?></a> &nbsp;
                 <a href="<?php echo  zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_option_names.gif', IMAGE_OPTION_NAMES); ?></a>
               </td>
              <td class="main" height="40" valign="bottom">
                <?php
// toggle switch for show copier features
                  $option_names_values_copier_array = array(array('id' => '0', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_OFF),
                                        array('id' => '1', 'text' => TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_ON),
                                        );
                  echo zen_draw_form('set_option_names_values_copier_form', FILENAME_OPTIONS_VALUES_MANAGER, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_option_names_values_copier', $option_names_values_copier_array, $reset_option_names_values_copier, 'onChange="this.form.submit();"') .
                  zen_hide_session_id() .
                  zen_draw_hidden_field('action', 'set_option_names_values_copier') .
                  '</form>';
                ?>
              </td>
               <td class="main" align="right" valign="bottom"><?php echo TEXT_PRODUCT_OPTIONS_INFO; ?></td>
             </tr>
          </table>
       </td>
     </tr>
     <tr>
        <td valign="top" width="50%">
           <table width="100%" border="0" cellspacing="0" cellpadding="2">
<!-- value //-->
<?php
  if ($action == 'delete_option_value') { // delete product option value
    $values_values = $db->Execute("select products_options_values_id, products_options_values_name
                                   from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   where products_options_values_id = '" . (int)$_GET['value_id'] . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");

?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo $values_values->fields['products_options_values_name']; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4"><?php echo zen_black_line(); ?></td>
              </tr>
<?php
    $products_values = $db->Execute("select p.products_id, pd.products_name, po.products_options_name, pa.options_id
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, "
                                     . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where pd.products_id = p.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and po.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and pa.products_id = p.products_id
                              and pa.options_values_id='" . (int)$_GET['value_id'] . "'
                              and po.products_options_id = pa.options_id
                              order by pd.products_name");

    if ($products_values->RecordCount() > 0) {
?>
<?php
// extra cancel button
    if ($products_values->RecordCount() > 10) {
?>
                  <tr>
                    <td class="main" colspan="3"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                    <td class="main" align="right"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_value&value_id=' . $_GET['value_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_delete.gif', ' delete '); ?></a>&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
<?php
  } // extra cancel
?>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPTION_SORT_ORDER; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="4"><?php echo zen_black_line(); ?></td>
                  </tr>

<?php
      while (!$products_values->EOF) {
        $rows++;
?>
                  <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $products_values->fields['products_id']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values->fields['products_name']; ?>&nbsp;</td>
                    <td class="smallText" align="right">&nbsp;<?php echo $options_values->fields["products_options_sort_order"]; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values->fields['products_options_name']; ?>&nbsp;</td>
                  </tr>
<?php
        $products_values->MoveNext();
      }
?>
                  <tr>
                    <td colspan="4"><?php echo zen_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="3"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                    <td class="main" align="right"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_value&value_id=' . $_GET['value_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_delete.gif', ' delete '); ?></a>&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
<?php
    } else {
?>
                  <tr>
                    <td class="main" colspan="3"><br /><?php echo TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br /><?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_value&value_id=' . $_GET['value_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_delete.gif', ' delete '); ?></a>&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', ' cancel '); ?></a>&nbsp;</td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
<?php
  } else {
?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_VAL; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="5" class="smallText">
<?php
//    $values = "select pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id, pov.products_options_values_sort_order from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id where pov.language_id = '" . (int)$_SESSION['languages_id'] . "' and pov2po.products_options_values_id !='" . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "' order by LPAD(pov2po.products_options_id,11,'0'), LPAD(pov.products_options_values_sort_order,11,'0'), pov.products_options_values_name";
    $values = "select pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id, pov.products_options_values_sort_order from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id     left join " . TABLE_PRODUCTS_OPTIONS . " po on pov2po.products_options_id = po.products_options_id where pov.language_id = '" . (int)$_SESSION['languages_id'] . "' and po.language_id = '" . (int)$_SESSION['languages_id'] . "' and po.language_id = pov.language_id and pov2po.products_options_values_id !='" . PRODUCTS_OPTIONS_VALUES_TEXT_ID . "' order by  po.products_options_name, LPAD(pov.products_options_values_sort_order,11,'0'), pov.products_options_values_name";
    if (!isset($_GET['value_page'])) {
      $_GET['value_page'] = 1;
    }
    $prev_value_page = $_GET['value_page'] - 1;
    $next_value_page = $_GET['value_page'] + 1;

    $value_query = $db->Execute($values);
    $num_rows = $value_query->RecordCount();

    $per_page = (MAX_ROW_LISTS_OPTIONS == '') ? $num_rows : (int)MAX_ROW_LISTS_OPTIONS;
    $value_page_start = ($per_page * $_GET['value_page']) - $per_page;

    if ($num_rows <= $per_page) {
      $num_pages = 1;
    } else if (($num_rows % $per_page) == 0) {
      $num_pages = ($num_rows / $per_page);
    } else {
      $num_pages = ($num_rows / $per_page) + 1;
    }
    $num_pages = (int) $num_pages;

// fix limit error on some versions
    if ($value_page_start < 0) { $value_page_start = 0; }

    $values = $values . " LIMIT $value_page_start, $per_page";

    // Previous
    if ($prev_value_page)  {
      echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'option_order_by=' . $option_order_by . '&value_page=' . $prev_value_page) . '"> &lt;&lt; </a> | ';
    }

    for ($i = 1; $i <= $num_pages; $i++) {
      if ($i != $_GET['value_page']) {
         echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($option_order_by) ? 'option_order_by=' . $option_order_by . '&' : '') . 'value_page=' . $i) . '">' . $i . '</a> | ';
      } else {
         echo '<b><font color=red>' . $i . '</font></b> | ';
      }
    }

    // Next
    if ($_GET['value_page'] != $num_pages) {
      echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($option_order_by) ? 'option_order_by=' . $option_order_by . '&' : '') . 'value_page=' . $next_value_page) . '"> &gt;&gt;</a> ';
    }
?>
                </td>
              </tr>
              <tr>
                <td colspan="6"><?php echo zen_black_line(); ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPTION_VALUE_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="6"><?php echo zen_black_line(); ?></td>
              </tr>
<?php
    $next_id = 1;
    $rows = 0;
    $values_values = $db->Execute($values);
    while (!$values_values->EOF) {
      $options_name = zen_options_name($values_values->fields['products_options_id']);
// iii 030813 added: Option Type Feature and File Uploading
// fetch products_options_id for use if the option value is deleted
// with TEXT and FILE Options, there are multiple options for the single TEXT
// value and only the single reference should be deleted
      $option_id = $values_values->fields['products_options_id'];

      $values_name = $values_values->fields['products_options_values_name'];
      $products_options_values_sort_order = $values_values->fields['products_options_values_sort_order'];
      $rows++;
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
// FIX HERE
// edit option values
      if (($action == 'update_option_value') && ($_GET['value_id'] == $values_values->fields['products_options_values_id'])) {
        echo '<form name="values" action="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=update_value' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '" method="post">';
        echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']);
        $inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $value_name = $db->Execute("select products_options_values_name
                                      from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                      where products_options_values_id = '" . (int)$values_values->fields['products_options_values_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" ' . zen_set_field_length(TABLE_PRODUCTS_OPTIONS_VALUES, 'products_options_values_name', 50) . ' value="' . zen_output_string($value_name->fields['products_options_values_name']) . '">&nbsp;<br />';
        }
          $products_options_values_sort_order = $db->Execute("select distinct products_options_values_sort_order from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$values_values->fields['products_options_values_id'] . "'");
          $inputs2 .= '&nbsp;<input type="text" name="products_options_values_sort_order" size="4" value="' . $products_options_values_sort_order->fields['products_options_values_sort_order'] . '">&nbsp;';
?>
                <td align="center" class="attributeBoxContent">&nbsp;<?php echo $values_values->fields['products_options_values_id']; ?><input type="hidden" name="value_id" value="<?php echo $values_values->fields['products_options_values_id']; ?>">&nbsp;</td>
                <td align="center" class="attributeBoxContent">&nbsp;<?php echo "\n"; ?><select name="option_id">
<?php
        $options_values = $db->Execute("select products_options_id, products_options_name, products_options_type
                                       from " . TABLE_PRODUCTS_OPTIONS . "
                                       where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "'
                                       order by products_options_name");

        while (!$options_values->EOF) {
          echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '"';
          if ($values_values->fields['products_options_id'] == $options_values->fields['products_options_id']) {
            echo ' selected';
          }
          echo '>' . $options_values->fields['products_options_name'] . '</option>';
          $options_values->MoveNext();
        }
?>
                </select>&nbsp;</td>
                <td height="50" class="attributeBoxContent"><?php echo $inputs; ?></td>
                <td class="attributeBoxContent" align="right"><?php echo $inputs2; ?></td>
                <td align="center" class="attributeBoxContent">&nbsp;<?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>&nbsp;</td>
<?php
        echo '</form>';
      } else {
// iii 030813 added:  option ID to parameter list of delete button's href
// allows delete to specify just that option/value pair when deleting from
// the TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS table
?>
                <td align="center" class="smallText">&nbsp;<?php echo $values_values->fields["products_options_values_id"]; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $values_name; ?>&nbsp;</td>
                <td class="smallText" align="right"><?php echo $values_values->fields['products_options_values_sort_order']; ?></td>
<?php
// hide buttons when editing
  if ($action== 'update_option_value') {
?>
            <td width='120' align="center" class="smallText">&nbsp;</td>
<?php
  } else {
?>
<!--                <td align="center" class="smallText">&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=update_option_value&value_id=' . $values_values->fields['products_options_values_id'] . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] : ''), 'NONSSL') . '">'; ?><?php echo zen_image_button('button_edit.gif', IMAGE_UPDATE); ?></a>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_option_value&value_id=' . $values_values->fields['products_options_values_id'] . '&option_id=' . $option_id, 'NONSSL') , '">'; ?><?php echo zen_image_button('button_delete.gif', IMAGE_DELETE); ?></a>&nbsp;</td> -->
                <td align="center" class="smallText">&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=update_option_value&value_id=' . $values_values->fields['products_options_values_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '">'; ?><?php echo zen_image_button('button_edit.gif', IMAGE_UPDATE); ?></a>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_option_value&value_id=' . $values_values->fields['products_options_values_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) , '">'; ?><?php echo zen_image_button('button_delete.gif', IMAGE_DELETE); ?></a>&nbsp;</td>
<?php
//    $values_values->MoveNext();
  }
?>
<?php
      }
      $max_values_id_values = $db->Execute("select max(products_options_values_id) + 1
                                           as next_id from " . TABLE_PRODUCTS_OPTIONS_VALUES);

      $next_id = $max_values_id_values->fields['next_id'];
// good one
      $values_values->MoveNext();
    }
?>
              </tr>
              <tr>
                <td colspan="5"><?php echo zen_black_line(); ?></td>
              </tr>
<?php
    if ($action != 'update_option_value') {
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      echo '<form name="values" action="' . zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=add_product_option_values' . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') ) . '" method="post">';echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']);
?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<select name="option_id">
<?php
      $options_values = $db->Execute("select products_options_id, products_options_name, products_options_type
                                      from " . TABLE_PRODUCTS_OPTIONS . "
                                      where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "'
                                      order by products_options_name");

      while (!$options_values->EOF) {
        echo '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
        $options_values->MoveNext();
      }

      $inputs = '';
      $inputs2 = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" ' . zen_set_field_length(TABLE_PRODUCTS_OPTIONS_VALUES, 'products_options_values_name', 50) . '>&nbsp;<br />';
      }
        $inputs2 .= TEXT_SORT . '<input type="text" name="products_options_values_sort_order" size="4">&nbsp;';
?>
                </select>&nbsp;</td>
                <td class="smallText"><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
                <td colspan="1" class="smallText"><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs2; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT); ?>&nbsp;</td>
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
            </td>
          </tr>


<?php if ($_SESSION['option_names_values_copier'] == '0') { ?>
  <table align="center" width="90%">
    <tr>
      <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
    <tr>
      <td class="pageHeading" align="center"><?php echo TEXT_INFO_OPTION_NAMES_VALUES_COPIER_STATUS; ?></td>
    </tr>
    <tr>
      <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
  </table>
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
  $options_values_from = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_name !='' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "' and products_options_type !='" . (int)PRODUCTS_OPTIONS_TYPE_FILE . "' order by products_options_name");
  while(!$options_values_from->EOF) {
    $option_from_dropdown .= "\n" . '  <option name="' . $options_values_from->fields['products_options_name'] . '" value="' . $options_values_from->fields['products_options_id'] . '">' . $options_values_from->fields['products_options_name'] . '</option>';
    $options_values_from->MoveNext();
  }

  $option_to_dropdown= $option_from_dropdown;

  $option_from_dropdown = "\n" . '<select name="options_id_from">' . $option_from_dropdown;
  $option_from_dropdown.= "\n" . '</select>';

  $option_to_dropdown = "\n" . '<select name="options_id_to">' . $option_to_dropdown;
  $option_to_dropdown.= "\n" . '</select>';

  // build dropdown for option_values from
  $options_values_values_from = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$_SESSION['languages_id'] . "' and products_options_values_id !='0' order by products_options_values_name");
  while(!$options_values_values_from->EOF) {
    $show_option_name= '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($options_values_values_from->fields['products_options_values_id'])) . ']';
    $option_values_from_dropdown .= "\n" . '  <option name="' . $options_values_values_from->fields['products_options_values_name'] . '" value="' . $options_values_values_from->fields['products_options_values_id'] . '">' . $options_values_values_from->fields['products_options_values_name'] . $show_option_name . '</option>'; echo zen_draw_hidden_field('option_value_from_filter', $_GET['options_id_from']);
    $options_values_values_from->MoveNext();
  }

  $option_values_to_dropdown = $option_values_from_dropdown;

  $option_values_from_dropdown = "\n" . '<select name="options_values_values_id_from">' . $option_values_from_dropdown;
  $option_values_from_dropdown .= "\n" . '</select>';

  $option_values_to_dropdown = "\n" . '<select name="options_values_values_id_to">' . $option_values_to_dropdown;
  $option_values_to_dropdown .= "\n" . '</select>';

  $to_categories_id = TEXT_SELECT_OPTION_VALUES_TO_CATEGORIES_ID . '<br />&nbsp;<input type="text" name="copy_to_categories_id" size="4">&nbsp;';

  $options_id_from_products_id = TEXT_SELECT_OPTION_FROM_PRODUCTS_ID . '&nbsp;<input type="text" name="copy_from_products_id" size="4">&nbsp;';

  // eof: build dropdowns for delete and add
?>

<!--
bof: copy Option Name and Value From to Option Name and Value to - all products
example: Copy Color Red to products with Size Small
-->

            <tr>
              <td colspan="5"><?php echo zen_draw_separator('pixel_black.gif', '100%', '10'); ?></td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_OPTION_VALUE_COPY_ALL; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_INFO_OPTION_VALUE_COPY_ALL; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td colspan="5"><table border="1" cellspacing="0" cellpadding="2" width="100%">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=copy_options_values_one_to_another', 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent">
                  <?php echo
                  TEXT_SELECT_OPTION_FROM . '<br />' . $option_from_dropdown . '&nbsp;<br />' .
                  TEXT_SELECT_OPTION_VALUES_FROM . '<br />' . $option_values_from_dropdown; ?>&nbsp;
                  </td>
                  <td class="dataTableHeadingContent">
                  <?php echo
                  TEXT_SELECT_OPTION_TO . '<br />' . $option_to_dropdown . '&nbsp;<br />' .
                  TEXT_SELECT_OPTION_VALUES_TO . '<br />' . $option_values_to_dropdown;?>&nbsp;
                  </td>
                  <td class="dataTableHeadingContent"><?php echo $to_categories_id; ?>&nbsp;</td>
                  <td align="center" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT); ?>&nbsp;</td>
                  </form>
                </tr>
              </table></td>
            </tr>
<!-- eof: copy all option values to another Option Name -->


<!--
bof: delete all Option Name for an Value
example: Delete Color Red
-->

            <tr>
              <td colspan="5"><?php echo zen_draw_separator('pixel_black.gif', '100%', '10'); ?></td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_OPTION_VALUE_DELETE_ALL; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_INFO_OPTION_VALUE_DELETE_ALL; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td colspan="5"><table border="1" cellspacing="0" cellpadding="2" width="100%">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=delete_options_values_of_option_name', 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent">
                  <?php echo
                  TEXT_SELECT_DELETE_OPTION_FROM . '<br />' . $option_from_dropdown . '&nbsp;<br />' .
                  TEXT_SELECT_DELETE_OPTION_VALUES_FROM . '<br />' . $option_values_from_dropdown; ?>&nbsp;
                  </td>
                  <td class="dataTableHeadingContent"><?php echo $to_categories_id; ?>&nbsp;</td>
                  <td align="center" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_delete.gif', IMAGE_DELETE); ?>&nbsp;</td>
                  </form>
                </tr>
              </table></td>
            </tr>
<!-- eof: delete all matching option name for option values -->


<!--
bof: copy Option Name and Value From to Option Name and Value to - all products
example: Copy Color Red to products with Size Small
-->

            <tr>
              <td colspan="5"><?php echo zen_draw_separator('pixel_black.gif', '100%', '10'); ?></td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_OPTION_VALUE_COPY_OPTIONS_TO; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr>
              <td class="main" colspan="4"><?php echo TEXT_INFO_OPTION_VALUE_COPY_OPTIONS_TO; ?></td>
              <td class="main" colspan="1"> </td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td colspan="5"><table border="1" cellspacing="0" cellpadding="2" width="100%">
                <tr class="dataTableHeadingRow">
                  <form name="quick_jump" method="post" action="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER, 'action=copy_options_values_one_to_another_options_id', 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                  <td class="dataTableHeadingContent" valign="top">
                  <?php echo
                  TEXT_SELECT_OPTION_FROM_ADD . '<br />' . $option_from_dropdown . '&nbsp;<br />' .
                  TEXT_SELECT_OPTION_VALUES_FROM_ADD . '<br />' . $option_values_from_dropdown . '&nbsp;<br /><br />' .
                  $options_id_from_products_id; ?>&nbsp;
                  </td>
                  <td class="dataTableHeadingContent" valign="top">
                  <?php echo
                  TEXT_SELECT_OPTION_TO_ADD_TO . '<br />' . $option_to_dropdown;?>&nbsp;
                  </td>
                  <td class="dataTableHeadingContent" valign="top">
                  <?php
                  echo $to_categories_id . '<br />' .
                  TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore', true) . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE;
                  ?>&nbsp;</td>
                  <td align="center" class="dataTableHeadingContent" valign="top">&nbsp;<?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT); ?>&nbsp;</td>
                  </form>
                </tr>
              </table></td>
            </tr>
<!-- eof: copy all option values to another Option Name -->
<?php } // show copier features ?>

        </table>
</td></tr></table>
<!-- option value eof //-->

<!-- body_text_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
