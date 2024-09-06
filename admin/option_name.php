<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 17 Modified in v2.1.0-alpha2 $
 */
require 'includes/application_top.php';

// verify option names and values
$chk_option_names = $db->Execute(
    "SELECT DISTINCT language_id
       FROM " . TABLE_PRODUCTS_OPTIONS . "
      WHERE language_id = '" . (int)$_SESSION['languages_id'] . "'"
);
if ($chk_option_names->EOF) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
    zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
}

$action = $_GET['action'] ?? '';
$lng_id = (int)($_GET['lng_id'] ?? $_SESSION['languages_id']);
if ($lng_id === 0) {
    $lng_id = (int)$_SESSION['languages_id'];
}
if ($action === 'update_sort_order') {
    if (!isset($_POST['products_options_sort_order']) || !is_array($_POST['products_options_sort_order'])) {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_NAME, 'lng_id=' . $lng_id));
    }
    foreach ($_POST['products_options_sort_order'] as $id => $new_sort_order) {
        $db->Execute(
            "UPDATE " . TABLE_PRODUCTS_OPTIONS . "
                SET products_options_sort_order = " . (int)$_POST['products_options_sort_order'][$id] . "
              WHERE products_options_id = " . (int)$id . "
                AND language_id = $lng_id"
        );
    }
    $messageStack->add_session(SUCCESS_OPTION_SORT_ORDER, 'success');
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_NAME, 'lng_id=' . $lng_id));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="table-responsive">
        <!-- body_text //-->
        <table class="table table-condensed table-striped">
          <thead>
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent text-center" colspan="2"><?= TEXT_EDIT_ALL ?></th>
            </tr>
<?php
$languages_array = [];
$languages = zen_get_languages();
$lng_exists = false;
foreach ($languages as $next_lang) {
    if ((int)$next_lang['id'] === $lng_id) {
        $lng_exists = true;
    }

    $languages_array[] = [
        'id' => $next_lang['id'],
        'text' => $next_lang['name'],
    ];
}
if ($lng_exists === false) {
    $lng_id = (int)$_SESSION['languages_id'];
}
?>
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent text-center col-sm-5">
                <?= ($lng_id !== (int)$_SESSION['languages_id'] ? 'Current Language' : '&nbsp;') ?>
              </th>
              <th class="dataTableHeadingContent text-center">
                <?= zen_draw_form('lng', FILENAME_PRODUCTS_OPTIONS_NAME, '', 'get') ?>
                    <?= zen_hide_session_id() ?>
                    <?= zen_draw_label(TEXT_SELECTED_LANGUAGE . zen_get_language_icon($lng_id), 'lng_id', 'class="control-label"') ?>&nbsp;&nbsp;&nbsp;
                    <?= zen_draw_pull_down_menu('lng_id', $languages_array, $lng_id, 'onchange="this.form.submit();" class="form-control"') ?>
                <?= '</form>' ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="2">
                <?= zen_draw_form('update', FILENAME_PRODUCTS_OPTIONS_NAME, 'action=update_sort_order&lng_id=' . $lng_id) ?>
                <table class="table table-condensed table-striped">
                  <thead>
                    <tr class="dataTableHeadingRow">
<?php
$using_session_language = ($lng_id === (int)$_SESSION['languages_id']);
if ($using_session_language === false) {
?>
                      <th class="dataTableHeadingContent">&nbsp;</th>
                      <th class="dataTableHeadingContent"><?= TEXT_CURRENT_NAME ?></th>
                      <th class="dataTableHeadingContent text-right"><?= TEXT_SORT_ORDER ?></th>
<?php
}
?>
                      <th class="dataTableHeadingContent">&nbsp;</th>
                      <th class="dataTableHeadingContent"><?= TEXT_OPTION_ID ?></th>
                      <th class="dataTableHeadingContent"><?= TEXT_OPTION_TYPE ?></th>
                      <th class="dataTableHeadingContent"><?= TEXT_OPTION_NAME ?></th>
                      <th class="dataTableHeadingContent"><?= TEXT_SORT_ORDER ?></th>
                    </tr>
                  </thead>
                  <tbody>
<?php
$options_types = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_OPTIONS_TYPES);
$options_types_names = [];
foreach ($options_types as $options_type) {
    $options_types_names[$options_type['products_options_types_id']] = ' (' . strtoupper($options_type['products_options_types_name']) . ')';
}

$rows = $db->Execute(
    "SELECT *
       FROM " . TABLE_PRODUCTS_OPTIONS . "
      WHERE language_id = $lng_id
      ORDER BY products_options_sort_order, products_options_id"
);
foreach ($rows as $row) {
    $option_type = $row['products_options_type'];
    $the_attributes_type = $options_types_names[$option_type] ?? " (UNKNOWN: $option_type)";
?>
                    <tr>
<?php
    if ($using_session_language === false) {
?>
                      <td class="dataTableContent text-center"><?= zen_get_language_icon($_SESSION['languages_id']) ?></td>
                      <td class="dataTableContent"><?= zen_get_option_name_language($row['products_options_id'], $_SESSION['languages_id']) ?></td>
                      <td class="dataTableContent text-right"><?= zen_get_option_name_language_sort_order($row['products_options_id'], $_SESSION['languages_id']) ?></td>
<?php
    }
?>
                      <td class="dataTableContent text-center"><?= zen_get_language_icon($lng_id) ?></td>
                      <td class="dataTableContent"><?= $row['products_options_id'] ?></td>
                      <td class="dataTableContent"><?= $the_attributes_type ?></td>
                      <td class="dataTableContent"><?= $row['products_options_name'] ?></td>
                      <td class="dataTableContent">
                        <?= zen_draw_input_field('products_options_sort_order[' . $row['products_options_id'] . ']', $row['products_options_sort_order'], 'size="4" class="form-control"') ?>
                      </td>
                    </tr>
<?php
}
?>
                    <tr class="dataTableHeadingRow">
                      <td <?= ($using_session_language === true ? '' : 'colspan="4"') ?> class="dataTableHeadingContent">&nbsp;</td>
                      <td colspan="4" class="dataTableHeadingContent text-center align-middle">
                        <button type="submit" class="btn btn-primary"><?= TEXT_UPDATE_SUBMIT ?></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <?= '</form>' ?>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- body_text_eof //-->
        <!-- body_eof //-->
      </div>
    </div>
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
