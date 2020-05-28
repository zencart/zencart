<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 13 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
      $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
      $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

      $db->Execute("INSERT INTO " . TABLE_TAX_CLASS . " (tax_class_title, tax_class_description, date_added)
                    VALUES ('" . zen_db_input($tax_class_title) . "',
                            '" . zen_db_input($tax_class_description) . "',
                            now())");

      zen_redirect(zen_href_link(FILENAME_TAX_CLASSES));
      break;
    case 'save':
      $tax_class_id = zen_db_prepare_input($_GET['tID']);
      $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
      $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

      $db->Execute("UPDATE " . TABLE_TAX_CLASS . "
                    SET tax_class_id = " . (int)$tax_class_id . ",
                        tax_class_title = '" . zen_db_input($tax_class_title) . "',
                        tax_class_description = '" . zen_db_input($tax_class_description) . "',
                        last_modified = now()
                    WHERE tax_class_id = " . (int)$tax_class_id);

      zen_redirect(zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . (int)$tax_class_id));
      break;
    case 'deleteconfirm':
      $tax_class_id = (int)$_POST['tID'];

      $sql = "SELECT tax_class_id
              FROM " . TABLE_TAX_RATES . "
              WHERE tax_class_id = " . (int)$tax_class_id;
      $result = $db->Execute($sql);
      if ($result->RecordCount() > 0) {
        $_GET['action'] = '';
        $messageStack->add_session(ERROR_TAX_RATE_EXISTS_FOR_CLASS, 'error');
      }
      $sql = "SELECT COUNT(*) AS count
              FROM " . TABLE_PRODUCTS . "
              WHERE products_tax_class_id = " . (int)$tax_class_id;
      $result = $db->Execute($sql);
      if ($result->fields['count'] > 0) {
        $_GET['action'] = '';
        $messageStack->add_session(sprintf(ERROR_TAX_RATE_EXISTS_FOR_PRODUCTS, $result->fields['count']), 'error');
      }
      if ($_GET['action'] == 'deleteconfirm') {
        $db->Execute("DELETE FROM " . TABLE_TAX_CLASS . "
                      WHERE tax_class_id = " . (int)$tax_class_id);
      }
      zen_redirect(zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']));
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
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASSES; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $classes_query_raw = "SELECT tax_class_id, tax_class_title, tax_class_description, last_modified, date_added
                                      FROM " . TABLE_TAX_CLASS . "
                                      ORDER BY tax_class_title";
                $classes_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $classes_query_raw, $classes_query_numrows);
                $classes = $db->Execute($classes_query_raw);
                foreach ($classes as $class) {
                  if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $class['tax_class_id']))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
                    $tcInfo = new objectInfo($class);
                  }

                  if (isset($tcInfo) && is_object($tcInfo) && ($class['tax_class_id'] == $tcInfo->tax_class_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo'              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $class['tax_class_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $class['tax_class_id']; ?></td>
              <td class="dataTableContent"><?php echo $class['tax_class_title']; ?></td>
              <td class="dataTableContent" align="right"><?php
                  if (isset($tcInfo) && is_object($tcInfo) && ($class['tax_class_id'] == $tcInfo->tax_class_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $class['tax_class_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</h4>');

                $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_TITLE, 'tax_class_title', 'class="control-label"') . zen_draw_input_field('tax_class_title', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_DESCRIPTION, 'tax_class_description', 'class="control-label"') . zen_draw_input_field('tax_class_description', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description') . ' class="form-control"'));
                $contents[] = array('text-align' => 'center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</h4>');

                $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_TITLE, 'tax_class_title', 'class="control-label"') . zen_draw_input_field('tax_class_title', htmlspecialchars($tcInfo->tax_class_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title') . ' class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_DESCRIPTION, 'tax_class_description', 'class="control-label"') . zen_draw_input_field('tax_class_description', htmlspecialchars($tcInfo->tax_class_description, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description') . ' class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</h4>');

                $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('tID', $tcInfo->tax_class_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $tcInfo->tax_class_title . '</b>');
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($tcInfo) && is_object($tcInfo)) {
                  $heading[] = array('text' => '<h4>' . $tcInfo->tax_class_title . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($tcInfo->date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($tcInfo->last_modified));
                  $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . $tcInfo->tax_class_description);
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
            <td><?php echo $classes_split->display_count($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></td>
            <td class="text-right"><?php echo $classes_split->display_links($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td colspan="2" class="text-right"><?php echo '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_NEW_TAX_CLASS . '</a>'; ?></td>
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
