<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 17 Modified in v1.5.7 $
 */
require('includes/application_top.php');
$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (zen_not_null($action)) {
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
        $orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                                       FROM " . TABLE_ORDERS_STATUS . "
                                       WHERE language_id = " . (int)$_SESSION['languages_id']);

        foreach ($orders_status as $status) {
          $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name)
                        VALUES ('" . (int)$status['orders_status_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($status['orders_status_name']) . "')");
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

      // if we just deleted our currently-selected language, need to switch to default lang:
      $lng = $db->Execute("SELECT languages_id
                           FROM " . TABLE_LANGUAGES . "
                           WHERE code = '" . zen_db_input(DEFAULT_LANGUAGE) . "'");
      if ((int)$_SESSION['languages_id'] == (int)$_POST['lID'])
        $_SESSION['languages_id'] = $lng->fields['languages_id'];

      $zco_notifier->notify('NOTIFY_ADMIN_LANGUAGE_DELETE', (int)$lID);
      
      zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
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
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
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
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
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
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $language['languages_id']) . '\'" role="button">' . "\n";
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
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $language['languages_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
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
