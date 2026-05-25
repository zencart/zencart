<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;
use Zencart\Templates\TemplateSelect;

require 'includes/application_top.php';

if (isset($_GET['tID'])) {
    $selected_template = (int)$_GET['tID'];
}
$action = $_GET['action'] ?? '';
$template_info = zen_get_catalog_template_directories();

$installedPluginKeys = [];
foreach ((new PluginControlRepository($db))->getAll() as $plugin) {
    if (($plugin['status'] ?? PluginStatus::NOT_INSTALLED) !== PluginStatus::NOT_INSTALLED) {
        $installedPluginKeys[$plugin['unique_key']] = true;
    }
}

$template_info = array_filter(
    $template_info,
    static function (array $template) use ($installedPluginKeys): bool {
        if (empty($template['is_plugin_template'])) {
            return true;
        }

        return isset($installedPluginKeys[$template['plugin_key'] ?? '']);
    }
);

$templateIsSelectable = static function (string $templateKey) use ($template_info): bool {
    return isset($template_info[$templateKey]);
};

if (!empty($action)) {
    switch ($action) {
        case 'insert':
            $templateKey = (string)($_POST['ln'] ?? '');
            if (!$templateIsSelectable($templateKey)) {
                $messageStack->add_session(ERROR_TEMPLATE_SELECTION_NOT_AVAILABLE, 'error');
                zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
            }

            $selected_template = (int)zen_register_new_template($templateKey, (int)$_POST['lang']);
            $action = '';
            break;

        case 'save':
            $templateKey = (string)($_POST['ln'] ?? '');
            if (!$templateIsSelectable($templateKey)) {
                $messageStack->add_session(ERROR_TEMPLATE_SELECTION_NOT_AVAILABLE, 'error');
                zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
            }

            zen_update_template_name_for_id($selected_template, $templateKey);
            $init_file = zen_get_template_init_file_path($templateKey);
            if ($init_file !== null && file_exists($init_file)) {
                require $init_file;
            }
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
            break;

        case 'deleteconfirm':
            zen_deregister_template_id((int)($_POST['tID'] ?? 0));
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
            break;
    }
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
        <div class="row"><?= TEXT_TEMPLATE_SELECT_INFO ?></div>
        <div class="row">
            <!-- body_text //-->
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
                <table class="table table-hover">
                    <thead>
                        <tr class="dataTableHeadingRow">
                            <th class="dataTableHeadingContent"><?= TABLE_HEADING_LANGUAGE ?></th>
                            <th class="dataTableHeadingContent"><?= TABLE_HEADING_NAME ?></th>
                            <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_DIRECTORY ?></th>
                            <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
// -----
// Note: No need for pagination!
//
$templateSelect = new TemplateSelect();
$templates = $templateSelect->getAllActiveTemplates();
foreach ($templates as $template) {
    if (!isset($template_info[$template['template_dir']])) {
        $template_info[$template['template_dir']] = [
           'name' => '<strong class="errorText"> MISSING DIRECTORY: ' . $template['template_dir'] . '</strong>',
           'version' => '',
           'author' => '',
           'description' => '',
           'screenshot' => '',
           'missing' => true,
        ];
    }
    if ((!isset($selected_template) || $selected_template === (int)$template['template_id']) && !isset($tInfo) && $action !== 'new') {
        $tInfo = new objectInfo($template);
    }

    if (isset($tInfo) && is_object($tInfo) && $template['template_id'] === $tInfo->template_id) {
        if ($action === 'edit') {
            $row_parameters = 'id="defaultSelected" class="dataTableRowSelected"';
        } else {
            $href_link = zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id . '&action=edit');
            $row_parameters = 'id="defaultSelected" class="dataTableRowSelected" style="cursor:pointer" onclick="document.location.href=\'' . $href_link . '\'"';
        }
    } else {
        $href_link = zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $template['template_id']);
        $row_parameters = 'class="dataTableRow" style="cursor:pointer" onclick="document.location.href=\'' . $href_link . '\'"';
    }

    if ((int)$template['template_language'] === 0) {
        $template_language = TEXT_INFO_DEFAULT_LANGUAGE;
    } else {
        $template_language = zen_get_language_name($template['template_language']);
    }
?>
                        <tr <?= $row_parameters ?>>
                            <td class="dataTableContent"><?= $template_language ?></td>
                            <td class="dataTableContent"><?= $template_info[$template['template_dir']]['name'] ?></td>
                            <td class="dataTableContent text-center"><?= $template['template_dir'] ?></td>
                            <td class="dataTableContent text-right">
<?php
    if (isset($tInfo) && is_object($tInfo) && $template['template_id'] == $tInfo->template_id) {
        echo zen_icon('caret-right', '', '2x', true);
    } else {
        echo
            '<a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $template['template_id']) . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '" role="button">' .
                zen_icon('circle-info', '', '2x', true, false) .
            '</a>';
    }
?>
                                &nbsp;
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
if (isset($tInfo) && is_object($tInfo)) {
    if ((int)$tInfo->template_language === 0) {
        $template_language = TEXT_INFO_DEFAULT_LANGUAGE;
    } else {
        $template_language = zen_get_language_name($tInfo->template_language);
    }
}
$heading = [];
$contents = [];

switch ($action) {
    case 'new':
        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_NEW_TEMPLATE . '</h4>'];

        $contents = ['form' => zen_draw_form('zones', FILENAME_TEMPLATE_SELECT, 'action=insert', 'post', 'class="form-horizontal"')];
        $contents[] = ['text' => TEXT_INFO_INSERT_INTRO];
        foreach($template_info as $key => $value) {
            if (isset($value['missing'])) {
                continue;
            }
            $template_array[] = [
                'id' => $key,
                'text' => $value['name'] . ' (' . ($value['version'] ?? 'V0') . ')',
            ];
        }
        $lns = zen_get_template_languages_not_registered();
        foreach ($lns as $ln) {
            $language_array[] = [
                'id' => $ln['language_id'],
                'text' => $ln['language_name'],
            ];
        }
        $contents[] = [
            'text' =>
                zen_draw_label(TEXT_INFO_TEMPLATE_NAME, 'ln', 'class="control-label"') .
                zen_draw_pull_down_menu('ln', $template_array, '', 'class="form-control" id="ln"')
        ];
        $contents[] = [
            'text' =>
                zen_draw_label(TEXT_INFO_LANGUAGE_NAME, 'lang', 'class="control-label"') .
                zen_draw_pull_down_menu('lang', $language_array, '', 'class="form-control" id="lang"')
        ];
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> ' .
                '<a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT) . '" class="btn btn-default" role="button">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    case 'edit':
        $heading[] = ['text' => '<h4>' . TABLE_HEADING_LANGUAGE . ': '  . $template_language . '</h4>'];

        $contents = ['form' => zen_draw_form('templateselect', FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id . '&action=save', 'post', 'class="form-horizontal"')];
        $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
        foreach($template_info as $key => $value) {
            if (isset($value['missing'])) {
                continue;
            }
            $template_array[] = ['id' => $key, 'text' => $value['name'] . ' (' . ($value['version'] ?? 'V0') . ')',];
        }
        $contents[] = [
            'text' =>
                zen_draw_label(TEXT_INFO_TEMPLATE_NAME, 'ln', 'class="control-label"') .
                zen_draw_pull_down_menu('ln', $template_array, $templateSelect->getTemplateDirForLanguage($tInfo->template_language), 'class="form-control" id="ln"')
        ];
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> ' .
                '<a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id) . '" class="btn btn-default" role="button">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    case 'delete':
        $heading[] = ['text' => '<h4>' . TABLE_HEADING_LANGUAGE . ': '  . $template_language . '</h4>'];

        $contents = ['form' => zen_draw_form('zones', FILENAME_TEMPLATE_SELECT, 'action=deleteconfirm') . zen_draw_hidden_field('tID', $tInfo->template_id)];
        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
        $contents[] = ['text' => '<b>' . $template_info[$tInfo->template_dir]['name'] . '</b>'];
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> ' .
                '<a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id) . '" class="btn btn-default" role="button">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    default:
        if (!(isset($tInfo) && is_object($tInfo))) {
            break;
        }

        $heading[] = ['text' => '<h4>' . TABLE_HEADING_LANGUAGE . ': '  . $template_language . '</h4>'];

        if ((int)$tInfo->template_language === 0) {
            $contents[] = ['text' => '<h5>' . TEXT_INFO_DEFAULT_TEMPLATE . '</h5>'];
        }
        $contents[] = ['text' => TEXT_INFO_TEMPLATE_NAME . ': <strong>"' . $template_info[$tInfo->template_dir]['name'] . '</strong>"'];
        $contents[] = ['text' => TEXT_INFO_TEMPLATE_AUTHOR . $template_info[$tInfo->template_dir]['author']];
        $contents[] = ['text' => TEXT_INFO_TEMPLATE_VERSION . $template_info[$tInfo->template_dir]['version']];
        $contents[] = ['text' => TEXT_INFO_TEMPLATE_DESCRIPTION . '<br>' . strip_tags($template_info[$tInfo->template_dir]['description'], ['br', 'em', 'strong'])];
        if ($template_info[$tInfo->template_dir]['has_template_settings'] === true) {
            $template_settings_button =
            '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#view-settings">' .
                TEXT_VIEW_TEMPLATE_SETTINGS .
            '</button> ';
            $template_settings = file_get_contents($template_info[$tInfo->template_dir]['template_settings_path']);
            if ($template_settings === false) {
                $template_settings = ERROR_COULD_NOT_READ_FILE;
            }
            $template_settings = nl2br(zen_output_string_protected($template_settings), true);
            $contents[] = [
                'text' =>
                    '<div id="view-settings" class="modal fade" role="dialog">' .
                        '<div class="modal-dialog modal-lg">' .
                            '<div class="modal-content">' .
                                '<div class="modal-header">' .
                                    '<button type="button" class="close" data-dismiss="modal">&times;</button>' .
                                    '<h4 class="modal-title">' .
                                        TEXT_MODAL_HEADING_INTRO . $template_info[$tInfo->template_dir]['name'] .
                                    '</h4>' .
                                '</div>' .
                                '<div class="modal-body">' .
                                    '<samp>' . $template_settings . '</samp>' .
                                '</div>' .
                                '<div class="modal-footer">' .
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">' . TEXT_CLOSE . '</button>' .
                                '</div>' .
                            '</div>' .
                        '</div>' .
                    '</div>'
            ];
        }
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                ($template_settings_button ?? '') .
                '<a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id . '&action=edit') . '" class="btn btn-primary" role="button">' .
                    TEXT_INFO_EDIT_INTRO .
                '</a>' .
                ((int)$tInfo->template_language !== 0 ? ' <a href="' . zen_href_link(FILENAME_TEMPLATE_SELECT, 'tID=' . $tInfo->template_id . '&action=delete') . '" class="btn btn-warning mt-2" role="button">' . IMAGE_DELETE . '</a>' : '')
        ];
        $contents[] = ['text' => '<hr>'];
        $contents[] = ['text' => TEXT_INFO_TEMPLATE_INSTALLED];
        foreach($template_info as $key => $value) {
            $screenshotPath = zen_get_template_screenshot_web_path($key);
            $contents[] = [
                'text' =>
                    ($screenshotPath === null
                        ? ''
                        : '<a href="' . $screenshotPath . '" rel="noreferrer noopener" target = "_blank" class="btn btn-info" role="button">' .
                            IMAGE_PREVIEW .
                        '</a>&nbsp;&nbsp;') .
                    $value['name']
            ];
        }
        break;
}

if (!empty($heading) && !empty($contents)) {
    $box = new box();
    echo $box->infoBox($heading, $contents);
}
?>
            </div>
<?php
if (empty($action)) {
    $template_languages = [];
    foreach ($templates as $template) {
        $template_languages[] = $template['template_language'];
    }
    foreach ($languages as $language) {
        if (!in_array($language['id'], $template_languages)) {
?>
            <div class="row text-right">
                <a href="<?= zen_href_link(FILENAME_TEMPLATE_SELECT, 'action=new') ?>" class="btn btn-primary" role="button">
                    <?= IMAGE_NEW_TEMPLATE ?>
                </a>
            </div>
  <?php
            break;
        }
    }
}
?>
        </div>
        <!-- body_text_eof //-->
    </div>

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
<!-- body_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
