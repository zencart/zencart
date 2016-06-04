<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: update_product_meta_tags.php 19772 2011-10-11 15:13:26Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

        if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
          $action = 'new_product_meta_tags';
        } else {
         if (isset($_GET['pID'])) $products_id = zen_db_prepare_input($_GET['pID']);
          $products_date_available = zen_db_prepare_input($_POST['products_date_available']);

          $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

          $sql_data_array = array(
                                  'metatags_title_status' => zen_db_prepare_input($_POST['metatags_title_status']),
                                  'metatags_products_name_status' => zen_db_prepare_input($_POST['metatags_products_name_status']),
                                  'metatags_model_status' => zen_db_prepare_input($_POST['metatags_model_status']),
                                  'metatags_price_status' => zen_db_prepare_input($_POST['metatags_price_status']),
                                  'metatags_title_tagline_status' => zen_db_prepare_input($_POST['metatags_title_tagline_status'])
                                  );

          if ($action == 'new_product_meta_tags') {
            $insert_sql_data = array( 'products_id' => (int)$products_id);
            $insert_sql_data = array( 'products_date_added' =>  'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
          } elseif ($action == 'update_product_meta_tags') {
            $update_sql_data = array( 'products_last_modified' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);
//die('UPDATE PRODUCTS ID:' . (int)$products_id . ' - ' . sizeof($sql_data_array));
            zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
          }

// check if new meta tags or existing
          $check_meta_tags_description = $db->Execute("select products_id from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " where products_id='" . (int)$products_id . "'");
          if ($check_meta_tags_description->RecordCount() <= 0) {
            $action = 'new_product_meta_tags';
          }
          $languages = zen_get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('metatags_title' => zen_db_prepare_input($_POST['metatags_title'][$language_id]),
                                    'metatags_keywords' => zen_db_prepare_input($_POST['metatags_keywords'][$language_id]),
                                    'metatags_description' => zen_db_prepare_input($_POST['metatags_description'][$language_id]));

            if ($action == 'new_product_meta_tags') {
              $insert_sql_data = array('products_id' => (int)$products_id,
                                       'language_id' => (int)$language_id);

              $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

              zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_product_meta_tags') {
              if ($n == 1 && empty($_POST['metatags_title'][$language_id]) && empty($_POST['metatags_keywords'][$language_id]) && empty($_POST['metatags_description'][$language_id])) {
                $remove_products_metatag = "DELETE from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'";
                $db->Execute($remove_products_metatag);
              } else {

                zen_db_perform(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
              }
            }
          }
          zen_redirect(zen_admin_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
        }
