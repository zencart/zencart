<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//
require 'includes/application_top.php';

$action = (string)($_GET['action'] ?? '');
$nID = (int)($_POST['nID'] ?? $_GET['nID'] ?? 0);
if ($action !== '' && $action !== 'new' && $action !== 'insert') {
    if ($nID === 0) {
        $messageStack->add(MESSAGE_ERROR_NO_ID);
        $action = '';
    }
}

switch ($action) {
    case 'insert':
    case 'save':
        // -----
        // Name "rules":
        // - No commas allowed in a name.  The comma is used as a separator when an order contains a mixed (partial in-stock) product.
        // - The [date] symbol, when used, must be used in all language localizations for a given name.
        //
        $pos_names = zen_db_prepare_input($_POST['pos_name']);
        $num_date_inserts = 0;
        $comma_in_name = false;
        $name_too_long = false;

        // -----
        // Determine the maximum allowed length for an out-of-stock 'name'.
        //
        $max_name_length = zen_field_length(TABLE_PRODUCTS_OPTIONS_STOCK_NAMES, 'pos_name');

        foreach ($pos_names as $current_name) {
            if ($posObserver->stringPos($current_name, '[date]') !== false) {
                $num_date_inserts++;
            }
            if ($posObserver->stringPos($current_name, ',') !== false) {
                $comma_in_name = true;
                break;
            }
            if ($posObserver->stringLen($current_name) > $max_name_length) {
                $name_too_long = true;
                $action = ($action == 'save') ? 'edit' : 'new';
                $messageStack->add(sprintf(ERROR_NAME_TOO_LONG, $current_name), 'error');
            }
        }
        if ($comma_in_name) {
            $action = ($action === 'save') ? 'edit' : 'new';
            $messageStack->add(ERROR_COMMA_IN_NAME, 'error');
        } elseif ($num_date_inserts !== 0 && $num_date_inserts !== count($pos_names)) {
            $action = ($action === 'save') ? 'edit' : 'new';
            $messageStack->add(ERROR_DATE_MULTI_LANG, 'error');
        } elseif (!$name_too_long) {
            $languages = zen_get_languages();
            foreach ($languages as $current_language) {
                $language_id = $current_language['id'];
                $sql_data_array = [
                    'pos_name' => $pos_names[$language_id]
                ];

                if ($action === 'insert' || get_pos_oos_name($nID, $language_id) === false) {
                    if ($nID === 0) {
                        $next_id = $db->Execute(
                            "SELECT MAX(pos_name_id) AS pos_name_id
                               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES
                        );
                        $nID = $next_id->fields['pos_name_id'] + 1;
                    }
                    $sql_data_array['pos_name_id'] = $nID;
                    $sql_data_array['language_id'] = $language_id;
                    zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK_NAMES, $sql_data_array);
                } else {
                    zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK_NAMES, $sql_data_array, 'update', "pos_name_id = $nID AND language_id = $language_id");
                }
            }
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, "nID=$nID"));
        }
        break;

    case 'deleteconfirm':
        $db->Execute(
            "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
              WHERE pos_name_id = $nID"
        );
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES));
        break;

    case 'delete':
        $status = $db->Execute(
            "SELECT pos_name_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
              WHERE pos_name_id = $nID
              LIMIT 1"
        );
        if (!$status->EOF) {
            $label_name = get_pos_oos_name($nID, $_SESSION['languages_id']);
            $messageStack->add_session(sprintf(ERROR_USED_IN_OPTIONS_STOCK, $label_name));
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, "nID=$nID"));
        }
        break;

    default:
        break;
}

// -----
// This version of POSM supports Zen Cart versions 1.5.7a through 2.0.0-alpha. A future Zen Cart version will be removing
// the 'legacy' stylesheets and javascript provided in previous versions.  As such, determine
// the Zen Cart base version in use to maintain the downwardly-compatible use of this module.
//
// Note: Once support for versions prior to 1.5.8 is dropped, the unconditional
// use of admin_html_head.php can be used!
//
$posm_zc_version = PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
$admin_html_head_supported = ($posm_zc_version >= '1.5.7');
$body_onload = ($admin_html_head_supported === true) ? '' : ' onload="init();"';
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
<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <p class="text-right"><a class="btn btn-sm btn-info" role="button" title="<?= BUTTON_MANAGE_ALT ?>" href="<?= zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK) ?>"><?= BUTTON_MANAGE ?></a>
    <p><?= TEXT_INSTRUCTIONS ?></p>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
                <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_NAME_ID ?></th>
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_LABEL_NAME ?></th>
                        <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
<?php
$names_list = $db->Execute(
    "SELECT pos_name_id, pos_name
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
      WHERE language_id = " . (int)$_SESSION['languages_id'] . "
      ORDER BY pos_name_id"
);
foreach ($names_list as $name) {
    $pos_name_id = $name['pos_name_id'];
    if ((!isset($_GET['nID']) || $_GET['nID'] == $pos_name_id) && !isset($nInfo) && strpos($action, 'new') !== 0) {
        $nInfo = new objectInfo($names_list->fields);
    }
    if (isset($nInfo) && is_object($nInfo) && $pos_name_id === $nInfo->pos_name_id) {
        $action_link = zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
    } else {
        $action_link = '<a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, "nID=$pos_name_id") . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
    }
?>
                    <tr class="dataTableRow">
                        <td class="dataTableContent"><?= $names_list->fields['pos_name_id'] ?></td>
                        <td class="dataTableContent"><?= $names_list->fields['pos_name'] ?></td>
                        <td class="dataTableContent text-right"><?= $action_link ?>&nbsp;</td>
                    </tr>
<?php
}
if ($action === '') {
?>
                    <tr>
                        <td colspan="3" class="text-right">
                            <a href="<?= zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'action=new') ?>" class="btn btn-primary" role="button"><?= IMAGE_INSERT ?></a>
                        </td>
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
$name_field_size = zen_set_field_length(TABLE_PRODUCTS_OPTIONS_STOCK_NAMES, 'pos_name');
switch ($action) {
    case 'new':
        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_NEW . '</h4>'];

        $contents = ['form' => zen_draw_form('status', FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'action=insert', 'post', 'class="form-horizontal"')];
        $contents[] = ['text' => TEXT_INFO_INSERT_INTRO];

        $inputs_string = '';
        $languages = zen_get_languages();
        foreach ($languages as $current_language) {
            $lang_id = $current_language['id'];
            $lang_dir = $current_language['directory'];
            $lang_img = $current_language['image'];
            $lang_name = $current_language['name'];
            $inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . "$lang_dir/images/$lang_img", $lang_name) . '&nbsp;' . zen_draw_input_field("pos_name[$lang_id]", $_POST['pos_name'][$lang_id] ?? '', 'class="name-input form-control" ' . $name_field_size);
        }

        $contents[] = ['text' => '<br>' . TEXT_INFO_LABEL_NAME . $inputs_string];
        $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
        break;

    case 'edit':
        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_EDIT . '</h4>'];

        $contents = ['form' => zen_draw_form('status', FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'nID=' . $nInfo->pos_name_id  . '&action=save', 'post', 'class="form-horizontal"')];
        $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];

        $inputs_string = '';
        $languages = zen_get_languages();
        foreach ($languages as $current_language) {
            $lang_id = $current_language['id'];
            $lang_dir = $current_language['directory'];
            $lang_img = $current_language['image'];
            $lang_name = $current_language['name'];
            $inputs_string .= 
                '<br>' .
                zen_image(DIR_WS_CATALOG_LANGUAGES . "$lang_dir/images/$lang_img", $lang_name) .
                '&nbsp;' .
                zen_draw_input_field("pos_name[$lang_id]", $_POST['pos_name'][$lang_id] ?? get_pos_oos_name($nInfo->pos_name_id, $lang_id), 'class="name-input form-control" ' . $name_field_size);

        }
        $contents[] = ['text' => '<br>' . TEXT_INFO_LABEL_NAME . $inputs_string];
        $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'nID=' . $nInfo->pos_name_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
        break;

    case 'delete':
        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE . '</h4>'];

        $contents = ['form' => zen_draw_form('status', FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'action=deleteconfirm', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('nID', $nInfo->pos_name_id)];
        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
        $contents[] = ['text' => '<br><b>' . $nInfo->pos_name . '</b>'];
        $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'nID=' . $nInfo->pos_name_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
        break;

    default:
        if (isset($nInfo) && is_object($nInfo)) {
          $heading[] = ['text' => '<h4>' . $nInfo->pos_name . '</h4>'];

          $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'nID=' . $nInfo->pos_name_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES, 'nID=' . $nInfo->pos_name_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];

          $inputs_string = '';
          $languages = zen_get_languages();
          foreach ($languages as $current_language) {
                $inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $current_language['directory'] . '/images/' . $current_language['image'], $current_language['name']) . '&nbsp;' . get_pos_oos_name($nInfo->pos_name_id, $current_language['id']);
          }
          $contents[] = ['text' => $inputs_string];
        }
        break;
}

if (count($heading) !== 0 && count($contents) !== 0) {
    $box = new box();
    echo $box->infoBox($heading, $contents);
}
?>
        </div>
    </div>
</div>
<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
