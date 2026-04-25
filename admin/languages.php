<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2026 Mar 13 Modified in v2.2.1 $
 */
require 'includes/application_top.php';

$action = $_GET['action'] ?? '';
if (in_array($action, ['save', 'edit', 'delete'])) {
    $id_check = $db->Execute(
        "SELECT *
           FROM " . TABLE_LANGUAGES . "
          WHERE languages_id = " . (int)($_GET['lID'] ?? 0)
    );
    if ($id_check->EOF) {
        zen_redirect(zen_href_link(FILENAME_LANGUAGES));
    }
}

switch ($action) {
    case 'insert':
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = (int)$_POST['sort_order'];

        $check = $db->Execute(
            "SELECT *
               FROM " . TABLE_LANGUAGES . "
              WHERE code = '" . zen_db_input($code) . "'"
        );
        if (!$check->EOF) {
            $messageStack->add(ERROR_DUPLICATE_LANGUAGE_CODE, 'error');
            $action = 'new';
            break;
        }

        $db->Execute(
            "INSERT INTO " . TABLE_LANGUAGES . "
                (name, code, image, directory, sort_order)
             VALUES
                ('" . zen_db_input($name) . "',
                 '" . zen_db_input($code) . "',
                 '" . zen_db_input($image) . "',
                 '" . zen_db_input($directory) . "',
                 $sort_order
                )"
        );
        $insert_id = $db->insert_ID();

        zen_record_admin_activity('Language [' . $code . '] added', 'info');

        // create additional categories_description records
        $categories = $db->Execute(
            "SELECT categories_id, categories_name, categories_description
               FROM " . TABLE_CATEGORIES_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($categories as $category) {
            $db->Execute(
                "INSERT INTO " . TABLE_CATEGORIES_DESCRIPTION . "
                    (categories_id, language_id, categories_name, categories_description)
                 VALUES
                    (" . (int)$category['categories_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($category['categories_name']) . "',
                     '" . zen_db_input($category['categories_description']) . "')"
            );
        }

        // create additional products_description records
        $products = $db->Execute(
            "SELECT products_id, products_name, products_description, products_url
               FROM " . TABLE_PRODUCTS_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($products as $product) {
            $db->Execute(
                "INSERT INTO " . TABLE_PRODUCTS_DESCRIPTION . "
                    (products_id, language_id, products_name, products_description, products_url)
                 VALUES
                    (" . (int)$product['products_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($product['products_name']) . "',
                     '" . zen_db_input($product['products_description']) . "',
                     '" . zen_db_input($product['products_url']) . "')"
            );
        }

        // create additional meta_tags_products_description records
        $meta_tags_products = $db->Execute(
            "SELECT products_id, metatags_title, metatags_keywords, metatags_description
               FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($meta_tags_products as $meta_tags_product) {
            $db->Execute(
                "INSERT INTO " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                    (products_id, language_id, metatags_title, metatags_keywords, metatags_description)
                 VALUES
                    (" . (int)$meta_tags_product['products_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($meta_tags_product['metatags_title']) . "',
                     '" . zen_db_input($meta_tags_product['metatags_keywords']) . "',
                     '" . zen_db_input($meta_tags_product['metatags_description']) . "')"
            );
        }

        // create additional meta_tags_categories_description records
        $meta_tags_categories = $db->Execute(
            "SELECT categories_id, metatags_title, metatags_keywords, metatags_description
               FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($meta_tags_categories as $meta_tags_category) {
            $db->Execute(
                "INSERT INTO " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                    (categories_id, language_id, metatags_title, metatags_keywords, metatags_description)
                 VALUES
                    (" . (int)$meta_tags_category['categories_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($meta_tags_category['metatags_title']) . "',
                     '" . zen_db_input($meta_tags_category['metatags_keywords']) . "',
                     '" . zen_db_input($meta_tags_category['metatags_description']) . "')"
            );
        }

        // create additional products_options records
        $products_options = $db->Execute(
            "SELECT products_options_id, products_options_name, products_options_sort_order, products_options_type,
                    products_options_length, products_options_comment, products_options_comment_position, products_options_size,
                    products_options_images_per_row, products_options_images_style, products_options_rows
               FROM " . TABLE_PRODUCTS_OPTIONS . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($products_options as $products_option) {
            $db->Execute(
                "INSERT INTO " . TABLE_PRODUCTS_OPTIONS . "
                    (products_options_id, language_id, products_options_name, products_options_sort_order, products_options_type, products_options_length,
                    products_options_comment, products_options_comment_position, products_options_size,
                    products_options_images_per_row, products_options_images_style, products_options_rows)
                 VALUES
                    (" . (int)$products_option['products_options_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($products_option['products_options_name']) . "',
                     " . (int)$products_option['products_options_sort_order'] . ",
                     " . (int)$products_option['products_options_type'] . ",
                     " . (int)$products_option['products_options_length'] . ",
                     '" . zen_db_input($products_option['products_options_comment']) . "',
                     " . (int)$products_option['products_options_comment_position'] . ",
                     " . (int)$products_option['products_options_size'] . ",
                     " . (int)$products_option['products_options_images_per_row'] . ",
                     " . (int)$products_option['products_options_images_style'] . ",
                     " . (int)$products_option['products_options_rows'] . ")"
            );
        }

        // create additional products_options_values records
        $products_options_values = $db->Execute(
            "SELECT products_options_values_id, products_options_values_name, products_options_values_sort_order
               FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($products_options_values as $products_options_value) {
            $db->Execute(
                "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                    (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                 VALUES
                    (" . (int)$products_options_value['products_options_values_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($products_options_value['products_options_values_name']) . "',
                     " . (int)$products_options_value['products_options_values_sort_order'] . ")"
            );
        }

        // create additional manufacturers_info records
        $manufacturers = $db->Execute(
            "SELECT manufacturers_id, manufacturers_url
               FROM " . TABLE_MANUFACTURERS_INFO . "
              WHERE languages_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($manufacturers as $manufacturer) {
            $db->Execute(
                "INSERT INTO " . TABLE_MANUFACTURERS_INFO . "
                    (manufacturers_id, languages_id, manufacturers_url)
                 VALUES
                    (" . $manufacturer['manufacturers_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($manufacturer['manufacturers_url']) . "')"
            );
        }

        // create additional orders_status records
        $orders_status = $db->Execute(
            "SELECT orders_status_id, orders_status_name, sort_order
               FROM " . TABLE_ORDERS_STATUS . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($orders_status as $status) {
            $db->Execute(
                "INSERT INTO " . TABLE_ORDERS_STATUS . "
                    (orders_status_id, language_id, orders_status_name, sort_order)
                 VALUES
                    (" . $status['orders_status_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($status['orders_status_name']) . "',
                     " . $status['sort_order'] . ")"
            );
        }

        // create additional tax_rates_description records
        $tax_rates_description = $db->Execute(
            "SELECT tax_rates_id, tax_description
               FROM " . TABLE_TAX_RATES_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($tax_rates_description as $rates_description) {
            $db->Execute(
                "INSERT INTO " . TABLE_TAX_RATES_DESCRIPTION . "
                    (tax_rates_id, language_id, tax_description)
                 VALUES
                    (" . $rates_description['tax_rates_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($rates_description['tax_description']) . "')"
            );
        }

        // create additional coupons_description records
        $coupons = $db->Execute(
            "SELECT coupon_id, coupon_name, coupon_description
               FROM " . TABLE_COUPONS_DESCRIPTION . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($coupons as $coupon) {
            $db->Execute(
                "INSERT INTO " . TABLE_COUPONS_DESCRIPTION . "
                    (coupon_id, language_id, coupon_name, coupon_description)
                 VALUES
                    (" . (int)$coupon['coupon_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($coupon['coupon_name']) . "',
                     '" . zen_db_input($coupon['coupon_description']) . "')"
            );
        }

        // create additional ez-page_description records
        $ezpages = $db->Execute(
            "SELECT pages_id, pages_title, pages_html_text
               FROM " . TABLE_EZPAGES_CONTENT . "
              WHERE languages_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($ezpages as $ezpage) {
            $db->Execute(
                "INSERT INTO " . TABLE_EZPAGES_CONTENT . "
                    (pages_id, languages_id, pages_title, pages_html_text)
                 VALUES
                    (" . (int)$ezpage['pages_id'] . ",
                     " . (int)$insert_id . ",
                     '" . zen_db_input($ezpage['pages_title']) . "',
                     '" . zen_db_input($ezpage['pages_html_text']) . "')"
            );
        }

        $zco_notifier->notify('NOTIFY_ADMIN_LANGUAGE_INSERT', (int)$insert_id);

        // set default, if selected
        if (($_POST['default'] ?? '') === 'on') {
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = '" . zen_db_input($code) . "'
                  WHERE configuration_key = 'DEFAULT_LANGUAGE'
                  LIMIT 1"
            );
        }
        zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'lID=' . $insert_id));
        break;

    case 'save':
        //prepare/sanitize inputs
        $lID = (int)$_GET['lID'];
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = (int)$_POST['sort_order'];

        // check if the spelling of the name for the default language has just been changed (thus 
        // meaning we need to change the spelling of DEFAULT_LANGUAGE to match it)
        // get "code" for the language we just updated
        $result = $db->Execute(
            "SELECT code
               FROM " . TABLE_LANGUAGES . "
              WHERE languages_id = " . (int)$lID
        );
        // compare "code" vs DEFAULT_LANGUAGE
        $changing_default_lang = (DEFAULT_LANGUAGE === ($result->fields['code'] ?? ''));
        // compare whether "code" matches $code (which was just submitted in the edit form
        $default_needs_an_update = (DEFAULT_LANGUAGE !== $code);
        // if we just edited the default language id's name, then we need to update the database with the new name for default
        $default_lang_change_flag = ($default_needs_an_update && $changing_default_lang);

        // save new language settings
        $db->Execute(
            "UPDATE " . TABLE_LANGUAGES . "
                SET name = '" . zen_db_input($name) . "',
                    code = '" . zen_db_input($code) . "',
                    image = '" . zen_db_input($image) . "',
                    directory = '" . zen_db_input($directory) . "',
                    sort_order = " . (int)$sort_order . "
                WHERE languages_id = " . (int)$lID
        );

        // update default language setting
        if (($_POST['default'] ?? '') === 'on' || $default_lang_change_flag === true) {
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = '" . zen_db_input($code) . "'
                  WHERE configuration_key = 'DEFAULT_LANGUAGE'"
            );
        }
        zen_record_admin_activity('Language entry updated for language code ' . $code, 'info');
        zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'lID=' . (int)$_GET['lID']));
        break;

    case 'deleteconfirm':
        $lID = (int)($_POST['lID'] ?? 0);
        $result = $db->Execute("SELECT code FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)$lID);
        if ($result->EOF) {
            zen_redirect(zen_href_link(FILENAME_LANGUAGES));
        }

        if ($result->fields['code'] === DEFAULT_LANGUAGE) {
            $messageStack->add_session(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
            zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'lID=' . (int)$lID));
        }
        zen_record_admin_activity('Language with ID ' . $lID . ' deleted.', 'info');

        $db->Execute("DELETE FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_COUNT_PRODUCT_VIEWS . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_MANUFACTURERS_INFO . " WHERE languages_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_TAX_RATES_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " WHERE language_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_EZPAGES_CONTENT . " WHERE languages_id = " . (int)$lID);
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT . " WHERE template_language = " . (int)$lID);

        // if we just deleted our currently-selected language, need to switch to default lang:
        $getlang = '';
        if ((int)$_SESSION['languages_id'] === (int)$lID) {
            $getlang = '&language=' . DEFAULT_LANGUAGE;
        }

        $zco_notifier->notify('NOTIFY_ADMIN_LANGUAGE_DELETE', (int)$lID);

        zen_redirect(zen_href_link(FILENAME_LANGUAGES, $getlang));
        break;

    case 'delete':
        $lID = (int)$_GET['lID'];
        $result = $db->Execute("SELECT code FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)$lID);
        if (($result->fields['code'] ?? '') === DEFAULT_LANGUAGE) {
            $messageStack->add_session(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
            zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'lID=' . $lID));
        }
        break;

    default:
        break;
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?= HEADING_TITLE ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover" role="listbox">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_LANGUAGE_NAME ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_LANGUAGE_CODE ?></th>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
              </tr>
            </thead>
            <tbody>
<?php
$languages_query_raw =
    "SELECT languages_id, name, code, image, directory, sort_order
       FROM " . TABLE_LANGUAGES . "
      ORDER BY sort_order";
$languages = $db->Execute($languages_query_raw);
foreach ($languages as $language) {
    $languages_id = (int)$language['languages_id'];

    if ((!isset($_GET['lID']) || (int)$_GET['lID'] === $languages_id) && !isset($lInfo) && $action !== 'new') {
        $lInfo = new objectInfo($language);
    }
    if (isset($lInfo) && is_object($lInfo) && $languages_id === (int)$lInfo->languages_id) {
?>
              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?= zen_href_link(FILENAME_LANGUAGES, 'lID=' . $lInfo->languages_id . '&action=edit') ?>'" role="option" aria-selected="true">
<?php
    } else {
?>
             <tr class="dataTableRow" onclick="document.location.href='<?= zen_href_link(FILENAME_LANGUAGES, 'lID=' . $languages_id) ?>'" role="option" aria-selected="false">
<?php
    }
    if (DEFAULT_LANGUAGE === $language['code']) {
?>
              <td class="dataTableContent"><strong><?= $language['name'] . ' (' . TEXT_DEFAULT . ')' ?></strong></td>
<?php
    } else {
?>
              <td class="dataTableContent"><?= zen_output_string_protected($language['name']) ?></td>
<?php
    }
?>
              <td class="dataTableContent"><?= $language['code'] ?></td>
              <td class="dataTableContent text-right">
<?php
    if (isset($lInfo) && is_object($lInfo) && $languages_id === (int)$lInfo->languages_id) {
        echo zen_icon('caret-right', '', '2x', true);
    } else {
        echo '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'lID=' . $languages_id) . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '">' . zen_icon('circle-info', '', '2x', true, true) . '</a>';
    }
?>
                &nbsp;</td>
              </tr>
<?php
}
?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
<?php
$heading = [];
$contents = [];
switch ($action) {
    case 'new':
        $info_heading = TEXT_INFO_HEADING_NEW_LANGUAGE;
        $form_params = 'action=insert';
        $info_intro = TEXT_INFO_INSERT_INTRO;
        $submit_button = IMAGE_INSERT;
        $cancel_params = isset($_GET['lID']) ? 'lID=' . $_GET['lID'] : '';
            //- Fall through to common form display ...
    case 'edit':
        $info_heading ??= TEXT_INFO_HEADING_EDIT_LANGUAGE;
        $form_params ??= 'lID=' . $lInfo->languages_id . '&action=save';
        $info_intro ??= TEXT_INFO_EDIT_INTRO;
        $submit_button ??= IMAGE_UPDATE;
        $cancel_params ??= 'lID=' . $lInfo->languages_id;

        // -----
        // Note: This form is rendered under 3 possible conditions:
        //
        // 1. Editing an existing language definition, using $lInfo object for the data.
        // 2. Redisplaying form data (as checked above for an 'insert' action) when an error occurs.
        // 3. Creating a new language definition.
        //
        $heading[] = ['text' => '<span class="infoBoxHeading h4">' . $info_heading . '</span>'];
        $contents = ['form' => zen_draw_form('languages', FILENAME_LANGUAGES, $form_params, 'post', 'class="form-horizontal"')];
        $contents[] = ['text' => $info_intro];
        $contents[] = ['text' =>
            '<br>' .
            zen_draw_label(TEXT_INFO_LANGUAGE_NAME, 'name', 'class="control-label"') .
            zen_draw_input_field('name', htmlspecialchars($lInfo?->name ?? $name ?? '', ENT_COMPAT, CHARSET, true), zen_set_field_length(TABLE_LANGUAGES, 'name') . ' id="name" class="form-control"', true)
        ];
        $contents[] = ['text' =>
            '<br>' .
            zen_draw_label(TEXT_INFO_LANGUAGE_CODE, 'code', 'class="control-label"') .
            zen_draw_input_field('code', $lInfo?->code ?? $code ?? '', zen_set_field_length(TABLE_LANGUAGES, 'code') . ' id="code" class="form-control"', true)
        ];
        $contents[] = ['text' =>
            '<br>' .
            zen_draw_label(TEXT_INFO_LANGUAGE_IMAGE, 'image', 'class="control-label"') .
            zen_draw_input_field('image', $lInfo?->image ?? $image ?? '', zen_set_field_length(TABLE_LANGUAGES, 'image') . ' id="image" class="form-control"')
        ];
        $contents[] = ['text' =>
            '<br>' .
            zen_draw_label(TEXT_INFO_LANGUAGE_DIRECTORY, 'directory', 'class="control-label"') .
            zen_draw_input_field('directory', $lInfo?->directory ?? $directory ?? '', zen_set_field_length(TABLE_LANGUAGES, 'directory') . ' id="directory" class="form-control"', true)
        ];
        $contents[] = ['text' =>
            '<br>' .
            zen_draw_label(TEXT_INFO_LANGUAGE_SORT_ORDER, 'sort_order', 'class="control-label"') .
            zen_draw_input_field('sort_order', $lInfo?->sort_order ?? $sort_order ?? '0', 'id="sort_order" class="form-control"', false, 'number')
        ];
        if (DEFAULT_LANGUAGE !== ($lInfo?->code ?? '')) {
            $contents[] = ['text' => '<div class="checkbox-inline"><label>' . zen_draw_checkbox_field('default') . TEXT_SET_DEFAULT . '</label></div>'];
        }
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<br>' .
                '<button type="submit" class="btn btn-primary">' . $submit_button . '</button>' .
                '<a href="' . zen_href_link(FILENAME_LANGUAGES, $cancel_params) . '" class="btn btn-default ms-2" role="button">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    case 'delete':
        $heading[] = ['text' => '<span class="infoBoxHeading h4">' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</span>'];
        $contents = ['form' => zen_draw_form('delete', FILENAME_LANGUAGES, 'action=deleteconfirm') . zen_draw_hidden_field('lID', $lInfo->languages_id)];
        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
        $contents[] = ['text' => '<br><b>' . zen_output_string_protected($lInfo->name) . '</b>'];
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button>' .
                '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'lID=' . (int)$_GET['lID']) . '" class="btn btn-default ms-2" role="button">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    default:
        if (!is_object($lInfo)) {
            break;
        }

        $heading[] = ['text' => '<h4>' . zen_output_string_protected($lInfo->name) . '</h4>'];

        $delete_button = '';
        if ($lInfo->code !== DEFAULT_LANGUAGE) {
            $delete_button =
                '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'lID=' . $lInfo->languages_id . '&action=delete') . '" class="btn btn-warning" role="button">' .
                    IMAGE_DELETE .
                '</a>';
        }
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'lID=' . $lInfo->languages_id . '&action=edit') . '" class="btn btn-primary" role="button">' .
                    IMAGE_EDIT .
                '</a> ' .
                $delete_button
        ];
        $contents[] = ['text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . ' ' . zen_output_string_protected($lInfo->name)];
        $contents[] = ['text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code];
        $contents[] = ['text' => '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, $lInfo->name)];
        $contents[] = ['text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . DIR_WS_CATALOG_LANGUAGES . '<b>' . zen_output_string_protected($lInfo->directory) . '</b>'];
        $contents[] = ['text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . (int)$lInfo->sort_order];
        break;
}

if (!empty($heading) && !empty($contents)) {
    $box = new box();
    echo $box->infoBox($heading, $contents);
}
?>
        </div>
      </div>
<?php
if ($action === '' && isset($lInfo)) {
?>
      <div class="row text-center mt-2">
        <a href="<?= zen_href_link(FILENAME_LANGUAGES, 'lID=' . $lInfo->languages_id . '&action=new') ?>" class="btn btn-primary" role="button">
          <?= IMAGE_NEW_LANGUAGE ?>
        </a>
      </div>
<?php
}
?>
       <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
