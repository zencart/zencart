<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require('includes/application_top.php');
$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (!empty($action)) {
  switch ($action) {
    case 'insert':
      $name = zen_db_prepare_input($_POST['name']);
      $code = zen_db_prepare_input($_POST['code']);
      $image = zen_db_prepare_input($_POST['image']);
      $directory = zen_db_prepare_input($_POST['directory']);
      $sort_order = zen_db_prepare_input((int)$_POST['sort_order']);
      $check = $db->Execute("SELECT *
                             FROM " . TABLE_LANGUAGES . "
                             WHERE code = '" . zen_db_input($code) . "'");
      if ($check->RecordCount() > 0) {
        $messageStack->add(ERROR_DUPLICATE_LANGUAGE_CODE, 'error');
      } else {

        $db->Execute("INSERT INTO " . TABLE_LANGUAGES . " (name, code, image, directory, sort_order)
                      VALUES ('" . zen_db_input($name) . "', '" . zen_db_input($code) . "',
                              '" . zen_db_input($image) . "', '" . zen_db_input($directory) . "',
                              '" . zen_db_input($sort_order) . "')");
        $insert_id = $db->Insert_ID();

        zen_record_admin_activity('Language [' . $code . '] added', 'info');

        // set default, if selected
        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = '" . zen_db_input($code) . "'
                        WHERE configuration_key = 'DEFAULT_LANGUAGE'");
        }

// create additional categories_description records
        $categories = $db->Execute("SELECT categories_id, categories_name, categories_description
                                    FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                    WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($categories as $category) {
          $db->Execute("INSERT INTO " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name, categories_description)
                        VALUES ('" . (int)$category['categories_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($category['categories_name']) . "',
                                '" . zen_db_input($category['categories_description']) . "')");
        }

// create additional products_description records
        $products = $db->Execute("SELECT products_id, products_name, products_description, products_url
                                  FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                  WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($products as $product) {
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url)
                        VALUES ('" . (int)$product['products_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($product['products_name']) . "',
                                '" . zen_db_input($product['products_description']) . "',
                                '" . zen_db_input($product['products_url']) . "')");
        }

// create additional meta_tags_products_description records
        $meta_tags_products = $db->Execute("SELECT products_id, metatags_title, metatags_keywords, metatags_description
                                            FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                                            WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($meta_tags_products as $meta_tags_product) {
          $db->Execute("INSERT INTO " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " (products_id, language_id, metatags_title, metatags_keywords, metatags_description)
                        VALUES ('" . (int)$meta_tags_product['products_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($meta_tags_product['metatags_title']) . "',
                                '" . zen_db_input($meta_tags_product['metatags_keywords']) . "',
                                '" . zen_db_input($meta_tags_product['metatags_description']) . "')");
        }

// create additional meta_tags_categories_description records
        $meta_tags_categories = $db->Execute("SELECT categories_id, metatags_title, metatags_keywords, metatags_description
                                              FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                                              WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($meta_tags_categories as $meta_tags_category) {
          $db->Execute("INSERT INTO " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " (categories_id, language_id, metatags_title, metatags_keywords, metatags_description)
                        VALUES ('" . (int)$meta_tags_category['categories_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($meta_tags_category['metatags_title']) . "',
                                '" . zen_db_input($meta_tags_category['metatags_keywords']) . "',
                                '" . zen_db_input($meta_tags_category['metatags_description']) . "')");
        }

// create additional products_options records
        $products_options = $db->Execute("SELECT products_options_id, products_options_name, products_options_sort_order, products_options_type,
                                                 products_options_length, products_options_comment, products_options_size, products_options_images_per_row,
                                                 products_options_images_style
                                          FROM " . TABLE_PRODUCTS_OPTIONS . "
                                          WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($products_options as $products_option) {
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name, products_options_sort_order, products_options_type, products_options_length, products_options_comment, products_options_size, products_options_images_per_row, products_options_images_style)
                        VALUES ('" . (int)$products_option['products_options_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($products_option['products_options_name']) . "',
                                '" . zen_db_input($products_option['products_options_sort_order']) . "',
                                '" . zen_db_input($products_option['products_options_type']) . "',
                                '" . zen_db_input($products_option['products_options_length']) . "',
                                '" . zen_db_input($products_option['products_options_comment']) . "',
                                '" . zen_db_input($products_option['products_options_size']) . "',
                                '" . zen_db_input($products_option['products_options_images_per_row']) . "',
                                '" . zen_db_input($products_option['products_options_images_style']) . "')");
        }

// create additional products_options_values records
        $products_options_values = $db->Execute("SELECT products_options_values_id, products_options_values_name, products_options_values_sort_order
                                                 FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                 WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($products_options_values as $products_options_value) {
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                        VALUES ('" . (int)$products_options_value['products_options_values_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($products_options_value['products_options_values_name']) . "',
                                '" . zen_db_input($products_options_value['products_options_values_sort_order']) . "')");
        }

// create additional manufacturers_info records
        $manufacturers = $db->Execute("SELECT manufacturers_id, manufacturers_url
                                       FROM " . TABLE_MANUFACTURERS_INFO . "
                                       WHERE languages_id = " . (int)$_SESSION['languages_id']);

        foreach ($manufacturers as $manufacturer) {
          $db->Execute("INSERT INTO " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url)
                        VALUES ('" . $manufacturer['manufacturers_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($manufacturer['manufacturers_url']) . "')");
        }

// create additional orders_status records
        $orders_status = $db->Execute("SELECT orders_status_id, orders_status_name, sort_order
                                       FROM " . TABLE_ORDERS_STATUS . "
                                       WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($orders_status as $status) {
          $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name, sort_order)
                        VALUES (" . $status['orders_status_id'] . ",
                                " . (int)$insert_id . ",
                                '" . zen_db_input($status['orders_status_name']) . "',
                                " . $status['sort_order'] . ")");
        }

        // create additional coupons_description records
        $coupons = $db->Execute("SELECT coupon_id, coupon_name, coupon_description
                                 FROM " . TABLE_COUPONS_DESCRIPTION . "
                                 WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($coupons as $coupon) {
          $db->Execute("INSERT INTO " . TABLE_COUPONS_DESCRIPTION . " (coupon_id, language_id, coupon_name, coupon_description)
                        VALUES ('" . (int)$coupon['coupon_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($coupon['coupon_name']) . "',
                                '" . zen_db_input($coupon['coupon_description']) . "')");
        }

        // create additional ez-page_description records
        $ezpages = $db->Execute("SELECT pages_id, pages_title, pages_html_text
                                 FROM " . TABLE_EZPAGES_CONTENT . "
                                 WHERE languages_id = " . (int)$_SESSION['languages_id']);

        foreach ($ezpages as $ezpage) {
          $db->Execute("INSERT INTO " . TABLE_EZPAGES_CONTENT . " (pages_id, languages_id, pages_title, pages_html_text)
                        VALUES ('" . (int)$ezpage['pages_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($ezpage['pages_title']) . "',
                                '" . zen_db_input($ezpage['pages_html_text']) . "')");
        }

// Following code updates submenus in admin using different methods depending on which table in database needs to be translated and which part of ZC is concerned (core or plugins).
        $lang_data = $db->Execute("SELECT code, directory FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)$insert_id);
        if ($lang_data->fields['code'] !== 'en' && !empty($lang_data->fields['code'])) {
            $lang_suffix = '_' . $lang_data->fields['code'];
            $lang_name = $lang_data->fields['directory'];
            // create additional product_type's type_name records
            $db->Execute("ALTER TABLE " . TABLE_PRODUCT_TYPES . " ADD COLUMN type_name" . $lang_suffix . " VARCHAR(255) AFTER type_name");
            $db->Execute("UPDATE " . TABLE_PRODUCT_TYPES . " SET type_name" . $lang_suffix . " = type_name");

            // create additional product_type_layout's configuration_title and configuration_description records
            $db->Execute("ALTER TABLE " . TABLE_PRODUCT_TYPE_LAYOUT . " ADD COLUMN configuration_title" . $lang_suffix . " TEXT AFTER configuration_title, ADD COLUMN configuration_description" . $lang_suffix . " TEXT AFTER configuration_description");
            $db->Execute("UPDATE " . TABLE_PRODUCT_TYPE_LAYOUT . " SET configuration_title" . $lang_suffix . " = configuration_title, configuration_description" . $lang_suffix . " = configuration_description");

            // create additional plugin_control's description records
            $db->Execute("ALTER TABLE " . TABLE_PLUGIN_CONTROL . " ADD COLUMN description" . $lang_suffix . " VARCHAR(255) AFTER description");
            $db->Execute("UPDATE " . TABLE_PLUGIN_CONTROL . " SET description" . $lang_suffix . " = description");

            // update database with translation for admin submenus if available
            $admin_submenus = [];
            $lang_dir_name = './' . DIR_WS_LANGUAGES . $lang_name . '/admin_submenus';
            $config_lines = file('./' . DIR_WS_LANGUAGES . $lang_name . '/lang.configuration.php', FILE_SKIP_EMPTY_LINES);
            if (is_dir($lang_dir_name)) {
                $dir_content = array_diff(scandir($lang_dir_name), array('..', '.'));
                foreach($dir_content as $key => $filename) {
                    if (strpos($filename, 'admin_menus') === 0) { // checking for files starting by 'admin_menus' to include them as they should contain '$admin_submenus' array data.
                        include($lang_dir_name . '/' . $filename);
                    } elseif (strpos($filename, 'lan_configuration') === 0) { // checking for files starting by 'lan_configuration' which contain language constants to add them to file 'lang.configuration.php'.
                        if (empty($last_line)) {
                            $last_line = $config_lines[array_key_last($config_lines)];
                            array_pop($config_lines); // removes 'return $define;' to add new language constants array.
                        }
                        $config_to_add = file($lang_dir_name . '/' . $filename, FILE_SKIP_EMPTY_LINES);
                        if (empty($config_lines_to_add)) {
                            $config_lines_to_add[] = '// ' . $lang_data->fields['code'] . '_start ' . PHP_EOL; // add a starting tag for newly installed language to identify this language's constants so it can easily be removed later when necessary.
                        }
                        $config_lines_to_add = array_merge($config_lines_to_add, $config_to_add);
                    }
                }
                if (!empty($config_lines_to_add)) {
                    $config_lines_to_add[] = '// ' . $lang_data->fields['code'] . '_end ' . PHP_EOL; // add a ending tag for newly installed language to identify this language's constants so it can easily be removed later when necessary.
                    $config_lines = array_merge($config_lines, $config_lines_to_add);
                    $config_lines[] = $last_line; // puts back 'return $define;' after new language constants array.
                    file_put_contents('./' . DIR_WS_LANGUAGES . $lang_name . '/lang.configuration.php', $config_lines);
                }
            }
            
            $result = $db->Execute('SELECT unique_key, version FROM plugin_control WHERE status = 1'); // check for installed encapsulated plugins
            foreach ($result as $plugin) {
                $plugin_lang_dir_name = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/admin/includes/languages/' . $lang_name . '/admin_submenus';
                if (is_dir($plugin_lang_dir_name)) {
                    $plugin_dir_content = array_diff(scandir($plugin_lang_dir_name), array('..', '.'));
                    foreach($plugin_dir_content as $key => $filename) {
                        $complete_filename = $plugin_lang_dir_name . '/' . $filename;
                        if (file_exists($complete_filename) && strpos($filename, 'admin_menus') === 0) { // checking for files starting by 'admin_menus' in encapsulated plugin folder to include them as they should contain '$admin_submenus' table data.
                            include($complete_filename);
                        } elseif ($filename === 'lan_configuration.php') { // checking for file 'lan_configuration,php' in encapsulated plugin folder, which contains language constants to add them to file 'lang.configuration.php'.
                            if (empty($last_plugin_line)) {
                                $last_plugin_line = $config_lines[array_key_last($config_lines)];
                                array_pop($config_lines); // removes 'return $define;' to add new language constants array.
                            }
                            $conf_to_add = file($complete_filename, FILE_SKIP_EMPTY_LINES);
                            if (empty($conf_lines_to_add)) {
                                $conf_lines_to_add[] = '// ' . $lang_data->fields['code'] . '_start ' . PHP_EOL; // add a starting tag for newly installed language to identify this language's constants so it can easily be removed later when necessary.
                            }
                            $conf_lines_to_add = array_merge($conf_lines_to_add, $conf_to_add);
                        }
                    }
                    if (!empty($conf_lines_to_add)) {
                        $conf_lines_to_add[] = '// ' . $lang_data->fields['code'] . '_end ' . PHP_EOL; // add a ending tag for newly installed language to identify this language's constants so it can easily be removed later when necessary.
                        $config_lines = array_merge($config_lines, $conf_lines_to_add);
                        $config_lines[] = $last_plugin_line; // puts back 'return $define;' after new language constants array.
                        file_put_contents('./' . DIR_WS_LANGUAGES . $lang_name . '/lang.configuration.php', $config_lines);
                    }
                }

            }

            if (!empty($admin_submenus)) {
                $conn = $db->link;
                foreach($admin_submenus as $table_name => $menus) { // Extract translation data for each table that needs translation from array '$admin_submenus'.
                    switch ($table_name) {
                        case 'product_type_layout': // update product_type_layout table columns configuration_title and configuration_description for new language
                            $query_configuration = $conn->prepare("UPDATE " . $table_name . " SET configuration_title" . $lang_suffix . " = ?, configuration_description" . $lang_suffix . " = ? WHERE configuration_key = ?");
                            foreach($menus as $configuration_key => $translation) {
                                $query_configuration->bind_param("sss", $translation['title'], $translation['description'], $configuration_key);
                                $query_configuration->Execute();
                            }
                            $query_configuration->close();
                            break;
                        case 'product_types'; // Updates product_types table column type_name for new language
                            $query_types = $conn->prepare("UPDATE " . $table_name . " SET type_name" . $lang_suffix . " = ? WHERE type_id = ?");
                            foreach($menus as $type_id => $type_name) {
                                $query_types->bind_param("si", $type_name, $type_id);
                                $query_types->Execute();
                            }
                            $query_types->close();
                            break;
                        case 'orders_status'; // Changes orders_status table column orders_status_name to new language
                            $query_orders_status = $conn->prepare("UPDATE " . $table_name . " SET orders_status_name = ? WHERE orders_status_id = ? AND language_id = " . (int)$insert_id);
                            foreach($menus as $orders_status_id => $orders_status_name) {
                                $query_orders_status->bind_param("si", $orders_status_name, $orders_status_id);
                                $query_orders_status->Execute();
                            }
                            $query_orders_status->close();
                            break;
                        case 'products_options_stock_names'; // Changes products_options_stock_names table column pos_name to new language
                            $query_pos_name = $conn->prepare("UPDATE " . $table_name . " SET pos_name = ? WHERE pos_name_id = ? AND language_id = " . (int)$insert_id);
                            foreach($menus as $pos_name_id => $pos_name) {
                                $query_pos_name->bind_param("si", $pos_name, $pos_name_id);
                                $query_pos_name->Execute();
                            }
                            $query_pos_name->close();
                            break;
                        case 'plugin_control'; // Changes plugin_control table column description to new language
                            $query_plugin = $conn->prepare("UPDATE " . $table_name . " SET description" . $lang_suffix . " = ? WHERE unique_key = ?");
                            foreach($menus as $unique_key => $description) {
                                $query_plugin->bind_param("ss", $description, $unique_key);
                                $query_plugin->Execute();
                            }
                            $query_plugin->close();
                            break;
                    }
                }
            }
            
        }

        $zco_notifier->notify('NOTIFY_ADMIN_LANGUAGE_INSERT', (int)$insert_id);

        zen_redirect(zen_href_link(FILENAME_LANGUAGES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'lID=' . $insert_id));
      }

      break;
    case 'save':
      //prepare/sanitize inputs
      $lID = zen_db_prepare_input($_GET['lID']);
      $name = zen_db_prepare_input($_POST['name']);
      $code = zen_db_prepare_input($_POST['code']);
      $image = zen_db_prepare_input($_POST['image']);
      $directory = zen_db_prepare_input($_POST['directory']);
      $sort_order = zen_db_prepare_input($_POST['sort_order']);

      // check if the spelling of the name for the default language has just been changed (thus meaning we need to change the spelling of DEFAULT_LANGUAGE to match it)
// get "code" for the language we just updated
      $result = $db->Execute("SELECT code
                              FROM " . TABLE_LANGUAGES . "
                              WHERE languages_id = " . (int)$lID);
// compare "code" vs DEFAULT_LANGUAGE
      $changing_default_lang = (DEFAULT_LANGUAGE == $result->fields['code']) ? true : false;
// compare whether "code" matches $code (which was just submitted in the edit form
      $default_needs_an_update = (DEFAULT_LANGUAGE == $code) ? false : true;
// if we just edited the default language id's name, then we need to update the database with the new name for default
      $default_lang_change_flag = ($default_needs_an_update && $changing_default_lang) ? true : false;

      // save new language settings
      $db->Execute("UPDATE " . TABLE_LANGUAGES . "
                    SET name = '" . zen_db_input($name) . "',
                        code = '" . zen_db_input($code) . "',
                        image = '" . zen_db_input($image) . "',
                        directory = '" . zen_db_input($directory) . "',
                        sort_order = '" . zen_db_input($sort_order) . "'
                    WHERE languages_id = " . (int)$lID);

      // update default language setting
      if ((isset($_POST['default']) && $_POST['default'] == 'on') || $default_lang_change_flag == true) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = '" . zen_db_input(substr($code, 0, 2)) . "'
                      WHERE configuration_key = 'DEFAULT_LANGUAGE'");
      }
      zen_record_admin_activity('Language entry updated for language code ' . $code, 'info');
      zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']));
      break;
    case 'deleteconfirm':
      $lID = zen_db_prepare_input($_POST['lID']);
      $langue = $db->Execute("SELECT code, directory FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . $lID);
      $lcode = $langue->fields['code'];
      $ldir = $langue->fields['directory'];
      $lng = $db->Execute("SELECT languages_id
                           FROM " . TABLE_LANGUAGES . "
                           WHERE code = '" . zen_db_input(DEFAULT_LANGUAGE) . "'");

      if ($lng->fields['languages_id'] == $lID) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = ''
                      WHERE configuration_key = 'DEFAULT_LANGUAGE'");
      }
      zen_record_admin_activity('Language with ID ' . $lID . ' deleted.', 'info');
      $db->Execute("DELETE FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_COUNT_PRODUCT_VIEWS . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_MANUFACTURERS_INFO . " WHERE languages_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " WHERE language_id = " . (int)$lID);
      $db->Execute("DELETE FROM " . TABLE_EZPAGES_CONTENT . " WHERE languages_id = " . (int)$lID);
      if ($lcode !== 'en') { // removes additional language related column when language is deleted.
          $db->Execute("ALTER TABLE " . TABLE_PRODUCT_TYPES . " DROP COLUMN type_name_" . $lcode);
          $db->Execute("ALTER TABLE " . TABLE_PRODUCT_TYPE_LAYOUT . " DROP COLUMN configuration_title_" . $lcode . ", DROP COLUMN configuration_description_" . $lcode);
          $db->Execute("ALTER TABLE " . TABLE_PLUGIN_CONTROL . " DROP COLUMN description_" . $lcode);
          
          $config_lines = file('./' . DIR_WS_LANGUAGES . $ldir . '/lang.configuration.php', FILE_SKIP_EMPTY_LINES); // When a language is removed, file 'lang.configuration.php' is cleaned up from this language constants.
          do {
              $index_start = 0;
              $index_length = 0;
              foreach ($config_lines as $index => $config_line) {
                  if (strpos($config_line, $lcode . '_start') !== false) { // starting tag for removed language
                      $index_start = (int)$index;
                  } elseif (strpos($config_line, $lcode . '_end') !== false && $index_start !== 0) { // ending tag for removed language
                      $index_length = (int)$index + 1 - $index_start;
                      array_splice($config_lines, $index_start, $index_length);
                      break;
                  }
              }
          } while ($index_start !== 0);
          file_put_contents('./' . DIR_WS_LANGUAGES . $ldir . '/lang.configuration.php', $config_lines);
      }

      // if we just deleted our currently-selected language, need to switch to default lang:
      $getlang = '';
      if ((int)$_SESSION['languages_id'] === (int)$_POST['lID']) {
          $getlang = '&language=' . DEFAULT_LANGUAGE;
      }

      $zco_notifier->notify('NOTIFY_ADMIN_LANGUAGE_DELETE', (int)$lID);

      zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . $getlang));
      break;
    case 'delete':
      $lID = zen_db_prepare_input($_GET['lID']);
      $lng = $db->Execute("SELECT code
                           FROM " . TABLE_LANGUAGES . "
                           WHERE languages_id = " . (int)$lID);
      $remove_language = true;
      if ($lng->fields['code'] == DEFAULT_LANGUAGE) {
        $remove_language = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
      }
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover" role="listbox">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $languages_query_raw = "select languages_id, name, code, image, directory, sort_order
                                        from " . TABLE_LANGUAGES . "
                                        order by sort_order";
                $languages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
                $languages = $db->Execute($languages_query_raw);
                foreach ($languages as $language) {
                  if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ($_GET['lID'] == $language['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
                    $lInfo = new objectInfo($language);
                  }
                  if (isset($lInfo) && is_object($lInfo) && ($language['languages_id'] == $lInfo->languages_id)) {
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'" role="option" aria-selected="true">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $language['languages_id']) . '\'" role="option" aria-selected="false">' . "\n";
                  }
                  if (DEFAULT_LANGUAGE == $language['code']) {
                    echo '                <td class="dataTableContent"><strong>' . $language['name'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
                  } else {
                    echo '                <td class="dataTableContent">' . $language['name'] . '</td>' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $language['code']; ?></td>
              <td class="dataTableContent text-right"><?php
                  if (isset($lInfo) && is_object($lInfo) && ($language['languages_id'] == $lInfo->languages_id)) {
                    echo zen_icon('caret-right', '', '2x', true);
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $language['languages_id']) . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '">' . zen_icon('circle-info', '', '2x', true, true) . '</a>';
                  }
                  ?>&nbsp;</td>
              </tr>
              <?php
            }
            ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</h4>');
                $contents = array('form' => zen_draw_form('languages', FILENAME_LANGUAGES, 'action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_NAME, 'name', 'class="control-label"') . zen_draw_input_field('name', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_CODE, 'code', 'class="control-label"') . zen_draw_input_field('code', '', 'maxlength="2" size="4" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_IMAGE, 'image', 'class="control-label"') . zen_draw_input_field('image', 'icon.gif', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_DIRECTORY, 'directory', 'class="control-label"') . zen_draw_input_field('directory', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_SORT_ORDER, 'sort_order', 'class="control-label"') . zen_draw_input_field('sort_order', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</h4>');
                $contents = array('form' => zen_draw_form('languages', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_NAME, 'name', 'class="control-label"') . zen_draw_input_field('name', htmlspecialchars($lInfo->name, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_CODE, 'code', 'class="control-label"') . zen_draw_input_field('code', $lInfo->code, 'maxlength="2" size="4" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_IMAGE, 'image', 'class="control-label"') . zen_draw_input_field('image', $lInfo->image, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_DIRECTORY, 'directory', 'class="control-label"') . zen_draw_input_field('directory', $lInfo->directory, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LANGUAGE_SORT_ORDER, 'sort_order', 'class="control-label"') . zen_draw_input_field('sort_order', $lInfo->sort_order, 'class="form-control"'));
                if (DEFAULT_LANGUAGE != $lInfo->code) {
                  $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                }
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</h4>');
                $contents = array('form' => zen_draw_form('delete', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('lID', $lInfo->languages_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $lInfo->name . '</b>');
                $contents[] = array('align' => 'text-center', 'text' => (($remove_language) ? '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button>' : '') . ' <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (is_object($lInfo)) {
                  $heading[] = array('text' => '<h4>' . $lInfo->name . '</h4>');
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
                  $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
                  $contents[] = array('text' => '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, $lInfo->name));
                  $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . DIR_WS_CATALOG_LANGUAGES . '<b>' . $lInfo->directory . '</b>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
                }
                break;
            }

            if (!empty($heading) && !empty($contents)) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
            <td class="text-right"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_LANGUAGE; ?></a></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
