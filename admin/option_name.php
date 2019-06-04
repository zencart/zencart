<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 May 25 Modified in v1.5.6b $
 */
require('includes/application_top.php');

// verify option names and values
$chk_option_names = $db->Execute("SELECT DISTINCT language_id
                                  FROM " . TABLE_PRODUCTS_OPTIONS . "
                                  WHERE language_id = '" . (int)$_SESSION['languages_id'] . "'");
if ($chk_option_names->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
  zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER));
}

//  if (!$lng_id) $_GET['lng_id'] = $_SESSION['languages_id'];
//  if (!$_GET['lng_id']) $_GET['lng_id'] = $_SESSION['languages_id'];

$languages_array = array();
$languages = zen_get_languages();
$_GET['lng_exists'] = false;
if (!isset($_GET['lng_id'])) {
  $_GET['lng_id'] = 0;
}
for ($i = 0, $j=sizeof($languages);$i<$j; $i++) {
  if ($languages[$i]['id'] == $_GET['lng_id']) {
    $_GET['lng_exists'] = true;
  }

  $languages_array[] = array(
    'id' => $languages[$i]['id'],
    'text' => $languages[$i]['name']
  );
}
if (!$_GET['lng_exists'] == true) {
  $_GET['lng_id'] = (int)$_SESSION['languages_id'];
}
if (!isset($_GET['action'])) {
  $_GET['action'] = '';
}

if ($_GET['action'] == "update_sort_order") {
  foreach ($_POST['products_options_sort_order'] as $id => $new_sort_order) {
    $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS . "
                  SET products_options_sort_order = " . (int)$_POST['products_options_sort_order'][$id] . "
                  WHERE products_options_id = " . (int)$id . "
                  AND language_id = " . (int)$_GET['lng_id']);
  }
  $messageStack->add_session(SUCCESS_OPTION_SORT_ORDER, 'success');
  $_GET['action'] = '';
  zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_NAME, 'lng_id=' . (int)$_GET['lng_id']));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
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
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="table-responsive">
        <!-- body_text //-->
        <table class="table table-condensed table-striped">
          <thead>
            <tr class="dataTableHeadingRow">
              <th colspan="<?php echo ($_GET['lng_id'] == $_SESSION['languages_id'] ? '5' : '8'); ?>" class="dataTableHeadingContent text-center"><?php echo TEXT_EDIT_ALL; ?></th>
            </tr>
            <tr class="dataTableHeadingRow">
              <th colspan="3" class="dataTableHeadingContent text-center"><?php echo ($_GET['lng_id'] != $_SESSION['languages_id'] ? 'Current Language' : '&nbsp;'); ?></th>
              <th colspan="<?php echo ($_GET['lng_id'] == $_SESSION['languages_id'] ? '2' : '5'); ?>" class="dataTableHeadingContent" class="text-center">
                  <?php echo zen_draw_form('lng', FILENAME_PRODUCTS_OPTIONS_NAME, '', 'get'); ?>
                  <?php echo zen_hide_session_id(); ?>
                <?php echo zen_draw_label(TEXT_SELECTED_LANGUAGE . zen_get_language_icon($_GET['lng_id']), 'lng_id', 'class="control-label"'); ?>&nbsp;&nbsp;&nbsp;
                <?php echo zen_draw_pull_down_menu('lng_id', $languages_array, $_GET['lng_id'], 'onChange="this.form.submit();" class="form-control"'); ?>
                <?php echo '</form>'; ?>
              </th>
            </tr>
            <?php echo zen_draw_form('update', FILENAME_PRODUCTS_OPTIONS_NAME, 'action=update_sort_order&lng_id=' . $_GET['lng_id']); ?>
            <tr class="dataTableHeadingRow">
                <?php
                if ($_GET['lng_id'] != $_SESSION['languages_id']) {
                  ?>
                <th class="dataTableHeadingContent">&nbsp;</th>
                <th class="dataTableHeadingContent"><?php echo TEXT_CURRENT_NAME; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TEXT_SORT_ORDER; ?></th>
              <?php } ?>
              <th class="dataTableHeadingContent">&nbsp;</th>
              <th class="dataTableHeadingContent"><?php echo TEXT_OPTION_ID; ?></th>
              <th class="dataTableHeadingContent"><?php echo TEXT_OPTION_TYPE; ?></th>
              <th class="dataTableHeadingContent"><?php echo TEXT_OPTION_NAME; ?></th>
              <th class="dataTableHeadingContent"><?php echo TEXT_SORT_ORDER; ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
                <?php
                $options_types = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_OPTIONS_TYPES);
                $options_types_names = array();
                foreach ($options_types as $options_type) {
                    $options_types_names[$options_type['products_options_types_id']] = ' (' . strtoupper($options_type['products_options_types_name']) . ')';
                }

                $rows = $db->Execute("SELECT *
                                      FROM " . TABLE_PRODUCTS_OPTIONS . "
                                      WHERE language_id = '" . (int)$_GET['lng_id'] . "'
                                      ORDER BY products_options_sort_order, products_options_id");
                foreach ($rows as $row) {
                  $option_type = $row['products_options_type'];
                  $the_attributes_type = (isset($options_types_names[$option_type])) ? $options_types_names[$option_type] : " (UNKNOWN: $option_type)";

                  if ($_GET['lng_id'] != $_SESSION['languages_id']) {
                    ?>
                  <td class="dataTableContent text-center"><?php echo zen_get_language_icon($_SESSION['languages_id']); ?></td>
                  <td class="dataTableContent"><?php echo zen_get_option_name_language($row['products_options_id'], $_SESSION['languages_id']); ?></td>
                  <td class="dataTableContent text-right"><?php echo zen_get_option_name_language_sort_order($row['products_options_id'], $_SESSION['languages_id']); ?></td>
                <?php } ?>
                <td class="dataTableContent text-center"><?php echo zen_get_language_icon($_GET['lng_id']); ?></td>
                <td class="dataTableContent"><?php echo $row['products_options_id']; ?></td>
                <td class="dataTableContent"><?php echo $the_attributes_type; ?></td>
                <td class="dataTableContent"><?php echo $row['products_options_name']; ?></td>
                <td class="dataTableContent">
                    <?php echo zen_draw_input_field('products_options_sort_order[' . $row['products_options_id'] . ']', $row['products_options_sort_order'], 'size="4" class="form-control"'); ?>
                </td>
              </tr>
              <?php
            }
            ?>
            <tr class="dataTableHeadingRow">
              <td <?php echo ($_GET['lng_id'] == $_SESSION['languages_id'] ? '' : 'colspan="4"'); ?> class="dataTableHeadingContent">&nbsp;</td>
              <td colspan="4" class="dataTableHeadingContent text-center align-middle">
                <button type="submit" class="btn btn-primary"><?php echo TEXT_UPDATE_SUBMIT; ?></button>
              </td>
            </tr>
            <?php echo '</form>'; ?>
          </tbody>
        </table>
        <!-- body_text_eof //-->
        <!-- body_eof //-->
      </div>
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
