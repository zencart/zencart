<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: media_types.php 19294 2011-07-28 18:15:46Z drbyte $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['mID'])) $type_id = zen_db_prepare_input($_GET['mID']);
        if (isset($_POST['type_ext'])) $type_ext = zen_db_prepare_input($_POST['type_ext']);
        if (isset($_POST['type_name'])) $type_name = zen_db_prepare_input($_POST['type_name']);
        $sql_data_array = array('type_ext' => $type_ext);

        if ($action == 'insert') {
          $insert_data_array = array('type_name' => $type_name);

          $sql_data_array = array_merge($sql_data_array, $insert_data_array);

          zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array);
          $type_id = zen_db_insert_id();

        } elseif ($action == 'save') {
          $insert_data_array = array('type_name' => $type_name);
          $sql_data_array = array_merge($sql_data_array, $insert_data_array);

          zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array, 'update', "type_id = '" . (int)$type_id . "'");
        }

        zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $type_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page']));
        }
        $type_id = zen_db_prepare_input($_POST['mID']);

        $db->Execute("delete from " . TABLE_MEDIA_TYPES . "
                      where type_id = '" . (int)$type_id . "'");


        zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page']));
        break;
    }
  }
require('includes/admin_html_head.php');
?>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE_EXT; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $media_type_query_raw = "select * from " . TABLE_MEDIA_TYPES . " order by type_name";
  $media_type_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $media_type_query_raw, $media_type_query_numrows);
  $media_type = $db->Execute($media_type_query_raw);
  while (!$media_type->EOF) {
    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $media_type->fields['type_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
      $mInfo = new objectInfo($media_type->fields);
    }

    if (isset($mInfo) && is_object($mInfo) && ($media_type->fields['type_id'] == $mInfo->type_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type->fields['type_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type->fields['type_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $media_type->fields['type_name']; ?></td>
                <td class="dataTableContent"><?php echo $media_type->fields['type_ext']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type->fields['type_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type->fields['type_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($mInfo) && is_object($mInfo) && ($media_type->fields['type_id'] == $mInfo->type_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, zen_get_all_get_params(array('mID')) . 'mID=' . $media_type->fields['type_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $media_type->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $media_type_split->display_count($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MEDIA_TYPES); ?></td>
                    <td class="smallText" align="right"><?php echo $media_type_split->display_links($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="3" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_MEDIA_TYPE . '</b>');

      $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_MEDIA_TYPE_NAME . '<br>' . zen_draw_input_field('type_name', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name')));
      $contents[] = array('text' => '<br>' . TEXT_MEDIA_TYPE_EXT . '<br>' . zen_draw_input_field('type_ext', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext')));

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_MEDIA_TYPE . '</b>');

      $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);

      $contents[] = array('text' => '<br>' . TEXT_MEDIA_TYPE_NAME . '<br>' . zen_draw_input_field('type_name', $mInfo->type_name, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name')));

      $contents[] = array('text' => '<br />' . TEXT_MEDIA_TYPE_EXT . '<br>' . zen_draw_input_field('type_ext', $mInfo->type_ext, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MEDIA_TYPES . '</b>');

      $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $mInfo->type_id));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $mInfo->type_name . '</b>');

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($mInfo) && is_object($mInfo)) {
        $heading[] = array('text' => '<b>' . $mInfo->type_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_EXTENSION . ' ' . $mInfo->type_ext);
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
