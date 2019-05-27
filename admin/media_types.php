<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 May 08 Modified in v1.5.6b $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['mID'])) {
        $type_id = zen_db_prepare_input($_GET['mID']);
      }
      if (isset($_POST['type_ext'])) {
        $type_ext = zen_db_prepare_input($_POST['type_ext']);
      }
      if (isset($_POST['type_name'])) {
        $type_name = zen_db_prepare_input($_POST['type_name']);
      }
      
      $sql_data_array = array('type_ext' => $type_ext);

      if ($action == 'insert') {
        $insert_data_array = array('type_name' => $type_name);

        $sql_data_array = array_merge($sql_data_array, $insert_data_array);

        zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array);
        $type_id = zen_db_insert_id();
      } elseif ($action == 'save') {
        $insert_data_array = array('type_name' => $type_name);

        $sql_data_array = array_merge($sql_data_array, $insert_data_array);

        zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array, 'update', "type_id = " . (int)$type_id);
      }

      zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $type_id));
      break;
    case 'deleteconfirm':
      $type_id = zen_db_prepare_input($_POST['mID']);

      $db->Execute("delete from " . TABLE_MEDIA_TYPES . "
                      where type_id = " . (int)$type_id);


      zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page']));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
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
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover table-striped">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE_EXT; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $media_type_query_raw = "SELECT *
                                         FROM " . TABLE_MEDIA_TYPES . "
                                         ORDER BY type_name";
                $media_type_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $media_type_query_raw, $media_type_query_numrows);
                $media_types = $db->Execute($media_type_query_raw);
                foreach ($media_types as $media_type) {
                  if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $media_type['type_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
                    $mInfo = new objectInfo($media_type);
                  }

                  if (isset($mInfo) && is_object($mInfo) && ($media_type['type_id'] == $mInfo->type_id)) {
                    ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type['type_id'] . '&action=edit'); ?>'">
                    <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type->fields['type_id'] . '&action=edit'); ?>'">
                    <?php } ?>
                  <td class="dataTableContent"><?php echo $media_type['type_name']; ?></td>
                  <td class="dataTableContent"><?php echo $media_type['type_ext']; ?></td>
                  <td class="dataTableContent text-right">
                      <?php
                      if (isset($mInfo) && is_object($mInfo) && ($media_type['type_id'] == $mInfo->type_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                      echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, zen_get_all_get_params(array('mID')) . 'mID=' . $media_type->fields['type_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                      }
                      ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_MEDIA_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_MEDIA_TYPE_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_MEDIA_TYPE_EXT, 'type_ext', 'class="control-label"') . '<br>' . zen_draw_input_field('type_ext', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext') . ' class="form-control"'));
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_MEDIA_TYPE . '</h4>');
                $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_MEDIA_TYPE_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', $mInfo->type_name, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_MEDIA_TYPE_EXT, 'type_ext', 'class="control-label"') . zen_draw_input_field('type_ext', $mInfo->type_ext, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext') . ' class="form-control"'));
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_MEDIA_TYPES . '</h4>');

                $contents = array('form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $mInfo->type_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $mInfo->type_name . '</b>');

                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($mInfo) && is_object($mInfo)) {
                  $heading[] = array('text' => '<h4>' . $mInfo->type_name . '</h4>');

                  $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_EXTENSION . ' ' . $mInfo->type_ext);
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
      <table class="table">
        <tr>
          <td><?php echo $media_type_split->display_count($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MEDIA_TYPES); ?></td>
          <td class="text-right"><?php echo $media_type_split->display_links($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
        </tr>
        <?php if (empty($action)) { ?>
          <tr>
            <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
          </tr>
        <?php } ?>
      </table>
      <!-- body_text_eof //-->

      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
