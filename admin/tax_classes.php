<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tax_classes.php 19330 2011-08-07 06:32:56Z drbyte $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

        $db->Execute("insert into " . TABLE_TAX_CLASS . "
                    (tax_class_title, tax_class_description, date_added)
                    values ('" . zen_db_input($tax_class_title) . "',
                            '" . zen_db_input($tax_class_description) . "',
                            now())");

        zen_redirect(zen_href_link(FILENAME_TAX_CLASSES));
        break;
      case 'save':
        $tax_class_id = zen_db_prepare_input($_GET['tID']);
        $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

        $db->Execute("update " . TABLE_TAX_CLASS . "
                      set tax_class_id = '" . (int)$tax_class_id . "',
                          tax_class_title = '" . zen_db_input($tax_class_title) . "',
                          tax_class_description = '" . zen_db_input($tax_class_description) . "',
                          last_modified = now()
                      where tax_class_id = '" . (int)$tax_class_id . "'");

        zen_redirect(zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tax_class_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']));
        }
        $tax_class_id = zen_db_prepare_input($_POST['tID']);

        $sql = "select tax_class_id from " . TABLE_TAX_RATES . " where tax_class_id='" . $tax_class_id . "'";
        $result = $db->Execute($sql);
        if ($result->RecordCount() > 0) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_TAX_RATE_EXISTS_FOR_CLASS, 'error');
        }
        $sql = "select count(*) as count from " . TABLE_PRODUCTS . " where products_tax_class_id='" . $tax_class_id . "'";
        $result = $db->Execute($sql);
        if ($result->fields['count'] > 0) {
          $_GET['action']= '';
          $messageStack->add_session(sprintf(ERROR_TAX_RATE_EXISTS_FOR_PRODUCTS, $result->fields['count']), 'error');
        }
        if ($_GET['action'] == 'deleteconfirm') {
          $db->Execute("delete from " . TABLE_TAX_CLASS . "
                        where tax_class_id = '" . (int)$tax_class_id . "'");
        }
        zen_redirect(zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASSES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $classes_query_raw = "select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " order by tax_class_title";
  $classes_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $classes_query_raw, $classes_query_numrows);
  $classes = $db->Execute($classes_query_raw);
  while (!$classes->EOF) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $classes->fields['tax_class_id']))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
      $tcInfo = new objectInfo($classes->fields);
    }

    if (isset($tcInfo) && is_object($tcInfo) && ($classes->fields['tax_class_id'] == $tcInfo->tax_class_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo'              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $classes->fields['tax_class_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $classes->fields['tax_class_id']; ?></td>
                <td class="dataTableContent"><?php echo $classes->fields['tax_class_title']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($classes->fields['tax_class_id'] == $tcInfo->tax_class_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $classes->fields['tax_class_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $classes->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $classes_split->display_count($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></td>
                    <td class="smallText" align="right"><?php echo $classes_split->display_links($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_tax_class.gif', IMAGE_NEW_TAX_CLASS) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_draw_input_field('tax_class_title', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title')));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . zen_draw_input_field('tax_class_description', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_draw_input_field('tax_class_title', htmlspecialchars($tcInfo->tax_class_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title')));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . zen_draw_input_field('tax_class_description', htmlspecialchars($tcInfo->tax_class_description, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('tID', $tcInfo->tax_class_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $tcInfo->tax_class_title . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
        $heading[] = array('text' => '<b>' . $tcInfo->tax_class_title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($tcInfo->date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($tcInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . $tcInfo->tax_class_description);
      }
      break;
  }
  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>