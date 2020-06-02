<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Jan 17 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if (isset($_POST['edit']) && $_POST['edit'] == 'edit') {
  $action = 'new_product_meta_tags';
} else {
  if (isset($_GET['pID'])) {
    $products_id = zen_db_prepare_input($_GET['pID']);
  }

  $sql_data_array = [
    'metatags_title_status' => (int)$_POST['metatags_title_status'],
    'metatags_products_name_status' => (int)$_POST['metatags_products_name_status'],
    'metatags_model_status' => (int)$_POST['metatags_model_status'],
    'metatags_price_status' => (int)$_POST['metatags_price_status'],
    'metatags_title_tagline_status' => (int)$_POST['metatags_title_tagline_status'],
  ];

  if ($action == 'new_product_meta_tags') {
    $sql_data_array['products_id'] = (int)$products_id;
    $sql_data_array['products_date_added'] = 'now()';
    zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
  } elseif ($action == 'update_product_meta_tags') {
    $sql_data_array['products_last_modified'] = 'now()';
    zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
  }

// check if new meta tags or existing
  $check_meta_tags_description = $db->Execute("SELECT products_id
                                               FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                                               WHERE products_id = " . (int)$products_id);
  if ($check_meta_tags_description->RecordCount() <= 0) {
    $action = 'new_product_meta_tags';
  }
  $languages = zen_get_languages();
  for ($i = 0, $n = count($languages); $i < $n; $i++) {
    $language_id = $languages[$i]['id'];

    $sql_data_array = [
      'metatags_title' => zen_db_prepare_input(isset($_POST['metatags_title'][$language_id]) ? $_POST['metatags_title'][$language_id] : ''),
      'metatags_keywords' => zen_db_prepare_input(isset($_POST['metatags_keywords'][$language_id]) ? $_POST['metatags_keywords'][$language_id] : ''),
      'metatags_description' => zen_db_prepare_input(isset($_POST['metatags_description'][$language_id]) ? $_POST['metatags_description'][$language_id] : '')
    ];

    if ($action == 'new_product_meta_tags') {
      $insert_sql_data = [
        'products_id' => (int)$products_id,
        'language_id' => (int)$language_id
      ];

      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

      zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
    } elseif ($action == 'update_product_meta_tags') {
      if ($n == 1 && empty($_POST['metatags_title'][$language_id]) && empty($_POST['metatags_keywords'][$language_id]) && empty($_POST['metatags_description'][$language_id])) {
        $remove_products_metatag = "DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                                    WHERE products_id = " . (int)$products_id . "
                                    AND language_id = " . (int)$language_id;
        $db->Execute($remove_products_metatag);
      } else {
        zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = " . (int)$products_id . " and language_id = " . (int)$language_id);
      }
    }
  }
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
}
