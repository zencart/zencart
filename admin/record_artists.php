<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: record_artists.php 19330 2011-08-07 06:32:56Z drbyte $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['mID'])) $artists_id = zen_db_prepare_input($_GET['mID']);
        $artists_name = zen_db_prepare_input($_POST['artists_name']);

        $sql_data_array = array('artists_name' => $artists_name);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_RECORD_ARTISTS, $sql_data_array);
          $artists_id = zen_db_insert_id();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          zen_db_perform(TABLE_RECORD_ARTISTS, $sql_data_array, 'update', "artists_id = '" . (int)$artists_id . "'");
        }

      if ($_POST['artists_image_manual'] != '') {
        // add image manually
        $artists_image_name = zen_db_input($_POST['img_dir'] . $_POST['artists_image_manual']);
        $db->Execute("update " . TABLE_RECORD_ARTISTS . "
                      set artists_image = '" .  $artists_image_name . "'
                      where artists_id = '" . (int)$artists_id . "'");
      } else {
        $artists_image = new upload('artists_image');
        $artists_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ( $artists_image->parse() &&  $artists_image->save()) {
          // remove image from database if none
          if ($artists_image->filename != 'none') {
            $db->Execute("update " . TABLE_RECORD_ARTISTS . "
                          set artists_image = '" .  zen_db_input($_POST['img_dir'] . $artists_image->filename) . "'
                          where artists_id = '" . (int)$artists_id . "'");
          } else {
            $db->Execute("update " . TABLE_RECORD_ARTISTS . "
                          set artists_image = ''
                          where artists_id = '" . (int)$artists_id . "'");
          }
        }
      }

        $languages = zen_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $artists_url_array = $_POST['artists_url'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('artists_url' => zen_db_prepare_input($artists_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('artists_id' => $artists_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            zen_db_perform(TABLE_RECORD_ARTISTS_INFO, $sql_data_array);
          } elseif ($action == 'save') {
            zen_db_perform(TABLE_RECORD_ARTISTS_INFO, $sql_data_array, 'update', "artists_id = '" . (int)$artists_id . "' and languages_id = '" . (int)$language_id . "'");
          }
        }

        zen_redirect(zen_href_link(FILENAME_RECORD_ARTISTS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $artists_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page']));
        }
        $artists_id = zen_db_prepare_input($_POST['mID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {

          $manufacturer = $db->Execute("select artists_image
                                        from " . TABLE_RECORD_ARTISTS . "
                                        where artists_id = '" . (int)$artists_id . "'");
          $image_location = DIR_FS_CATALOG_IMAGES . $manufacturer->fields['artists_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        $db->Execute("delete from " . TABLE_RECORD_ARTISTS . "
                      where artists_id = '" . (int)$artists_id . "'");
        $db->Execute("delete from " . TABLE_RECORD_ARTISTS_INFO . "
                      where artists_id = '" . (int)$artists_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $products = $db->Execute("select products_id
                                    from " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                    where artists_id = '" . (int)$artists_id . "'");

          while (!$products->EOF) {
            zen_remove_product($products->fields['products_id']);
            $products->MoveNext();
          }
        } else {
          $db->Execute("update " . TABLE_PRODUCT_MUSIC_EXTRA . "
                        set artists_id = ''
                        where artists_id = '" . (int)$artists_id . "'");
        }

        zen_redirect(zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page']));
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
<body onload="init()">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_RECORD_ARTISTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $artists_query_raw = "select * from " . TABLE_RECORD_ARTISTS . " order by artists_name";
  $artists_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $artists_query_raw, $artists_query_numrows);
  $artists = $db->Execute($artists_query_raw);

  while (!$artists->EOF) {

    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $artists->fields['artists_id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $artists_products = $db->Execute("select count(*) as products_count
                                             from " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                             where artists_id = '" . (int)$artists->fields['artists_id'] . "'");

      $aInfo_array = array_merge($artists->fields, $artists_products->fields);
      $aInfo = new objectInfo($aInfo_array);
    }

    if (isset($aInfo) && is_object($aInfo) && ($artists->fields['artists_id'] == $aInfo->artists_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artists->fields['artists_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artists->fields['artists_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $artists->fields['artists_name']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artists->fields['artists_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artists->fields['artists_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($aInfo) && is_object($aInfo) && ($artists->fields['artists_id'] == $aInfo->artists_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, zen_get_all_get_params(array('mID')) . 'mID=' . $artists->fields['artists_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $artists->MoveNext();
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $artists_split->display_count($artists_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ARTISTS); ?></td>
                    <td class="smallText" align="right"><?php echo $artists_split->display_links($artists_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_RECORD_ARTIST . '</b>');

      $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_RECORD_ARTIST_NAME . '<br>' . zen_draw_input_field('artists_name', '', zen_set_field_length(TABLE_RECORD_ARTISTS, 'artists_name')));
      $contents[] = array('text' => '<br>' . TEXT_RECORD_ARTIST_IMAGE . '<br>' . zen_draw_file_field('artists_image'));
      $dir = @dir(DIR_FS_CATALOG_IMAGES);
      $dir_info[] = array('id' => '', 'text' => "Main Directory");
      while ($file = $dir->read()) {
        if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
          $dir_info[] = array('id' => $file . '/', 'text' => $file);
        }
      }
      $dir->close();

      $default_directory = 'artists/';

      $contents[] = array('text' => '<BR />' . TEXT_ARTISTS_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
      $contents[] = array('text' => '<br />' . TEXT_ARTISTS_IMAGE_MANUAL . '&nbsp;' . zen_draw_input_field('artists_image_manual'));

      $manufacturer_inputs_string = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $manufacturer_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('artists_url[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_RECORD_ARTISTS_INFO, 'artists_url') );
      }

      $contents[] = array('text' => '<br>' . TEXT_RECORD_ARTIST_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_RECORD_ARTIST . '</b>');

      $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_RECORD_ARTIST_NAME . '<br>' . zen_draw_input_field('artists_name', htmlspecialchars($aInfo->artists_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_RECORD_ARTISTS, 'artists_name')));
      $contents[] = array('text' => '<br />' . TEXT_RECORD_ARTIST_IMAGE . '<br>' . zen_draw_file_field('artists_image') . '<br />' . $aInfo->artists_image);
      $dir = @dir(DIR_FS_CATALOG_IMAGES);
      $dir_info[] = array('id' => '', 'text' => "Main Directory");
      while ($file = $dir->read()) {
        if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
          $dir_info[] = array('id' => $file . '/', 'text' => $file);
        }
      }
      $dir->close();
      $default_directory = substr( $aInfo->artists_image, 0,strpos( $aInfo->artists_image, '/')+1);
      $contents[] = array('text' => '<BR />' . TEXT_ARTISTS_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
      $contents[] = array('text' => '<br />' . TEXT_ARTISTS_IMAGE_MANUAL . '&nbsp;' . zen_draw_input_field('artists_image_manual'));
      $contents[] = array('text' => '<br />' . zen_info_image($aInfo->artists_image, $aInfo->artists_name));
      $manufacturer_inputs_string = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $manufacturer_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('artists_url[' . $languages[$i]['id'] . ']', zen_get_artists_url($aInfo->artists_id, $languages[$i]['id']), zen_set_field_length(TABLE_RECORD_ARTISTS_INFO, 'artists_url'));
      }

      $contents[] = array('text' => '<br>' . TEXT_RECORD_ARTIST_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_RECORD_ARTIST . '</b>');

      $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $aInfo->artists_id));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $aInfo->artists_name . '</b>');
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

      if ($aInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<b>' . $aInfo->artists_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($aInfo->date_added));
        if (zen_not_null($aInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($aInfo->last_modified));
        $contents[] = array('text' => '<br>' . zen_info_image($aInfo->artists_image, $aInfo->artists_name));
        $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_count);
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
