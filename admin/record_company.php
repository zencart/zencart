<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jun 04 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['mID'])) {
        $record_company_id = zen_db_prepare_input($_GET['mID']);
      }
      $record_company_name = zen_db_prepare_input($_POST['record_company_name']);

      $sql_data_array = array('record_company_name' => $record_company_name);

      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_RECORD_COMPANY, $sql_data_array);
        $record_company_id = zen_db_insert_id();
      } elseif ($action == 'save') {
        $update_sql_data = array('last_modified' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_RECORD_COMPANY, $sql_data_array, 'update', "record_company_id = " . (int)$record_company_id);
      }

      if ($_POST['record_company_image_manual'] != '') {
        // add image manually
        $artists_image_name = zen_db_input($_POST['img_dir'] . $_POST['record_company_image_manual']);
        $db->Execute("UPDATE " . TABLE_RECORD_COMPANY . "
                      SET record_company_image = '" . $artists_image_name . "'
                      WHERE record_company_id = " . (int)$record_company_id);
      } else {
        $record_company_image = new upload('record_company_image');
        $record_company_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
        $record_company_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ($record_company_image->parse() && $record_company_image->save()) {
          if ($record_company_image->filename != 'none') {
            $db_filename = zen_limit_image_filename($record_company_image->filename, TABLE_RECORD_COMPANY, 'record_company_image');
            $db->Execute("UPDATE " . TABLE_RECORD_COMPANY . "
                          SET record_company_image = '" . zen_db_input($_POST['img_dir'] . $db_filename) . "'
                          WHERE record_company_id = " . (int)$record_company_id);
          } else {
            // remove image from database if 'none'
            $db->Execute("UPDATE " . TABLE_RECORD_COMPANY . "
                          SET record_company_image = ''
                          WHERE record_company_id = " . (int)$record_company_id);
          }
        }
      }

      $languages = zen_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $record_company_url_array = $_POST['record_company_url'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array('record_company_url' => zen_db_prepare_input($record_company_url_array[$language_id]));

        if ($action == 'insert') {
          $insert_sql_data = array(
            'record_company_id' => $record_company_id,
            'languages_id' => $language_id);

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_RECORD_COMPANY_INFO, $sql_data_array);
        } elseif ($action == 'save') {
          zen_db_perform_language(TABLE_RECORD_COMPANY_INFO, $sql_data_array, 'record_company_id', (int)$record_company_id, (int)$language_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_RECORD_COMPANY, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $record_company_id));
      break;
    case 'deleteconfirm':
      $record_company_id = zen_db_prepare_input($_POST['mID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
        $record_company = $db->Execute("SELECT record_company_image
                                        FROM " . TABLE_RECORD_COMPANY . "
                                        WHERE record_company_id = " . (int)$record_company_id);

        $image_location = DIR_FS_CATALOG_IMAGES . $record_company->fields['record_company_image'];

        if (file_exists($image_location)) {
          @unlink($image_location);
        }
      }

      $db->Execute("DELETE FROM " . TABLE_RECORD_COMPANY . "
                    WHERE record_company_id = " . (int)$record_company_id);
      $db->Execute("DELETE FROM " . TABLE_RECORD_COMPANY_INFO . "
                    WHERE record_company_id = " . (int)$record_company_id);

      if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
        $products = $db->Execute("SELECT products_id
                                  FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                  WHERE record_company_id = " . (int)$record_company_id);

        foreach ($products as $product) {
          zen_remove_product($products['products_id']);
        }
      } else {
        $db->Execute("UPDATE " . TABLE_PRODUCT_MUSIC_EXTRA . "
                      SET record_company_id = 0 
                      WHERE record_company_id = " . (int)$record_company_id);
      }

      zen_redirect(zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page']));
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
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_RECORD_COMPANY; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $record_company_query_raw = "SELECT *
                                             FROM " . TABLE_RECORD_COMPANY . "
                                             ORDER BY record_company_name";
                $record_company_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $record_company_query_raw, $record_company_query_numrows);
                $record_companies = $db->Execute($record_company_query_raw);

                foreach ($record_companies as $record_company) {
                  if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $record_company['record_company_id']))) && (substr($action, 0, 3) != 'new')) {
                    $record_company_products = $db->Execute("SELECT COUNT(*) AS products_count
                                                             FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                                             WHERE record_company_id = " . (int)$record_company['record_company_id']);

                    $aInfo_array = array_merge($record_company, $record_company_products->fields);
                    $aInfo = new objectInfo($aInfo_array);
                  }

                  if (isset($aInfo) && is_object($aInfo) && ($record_company['record_company_id'] == $aInfo->record_company_id)) {
                    ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $record_company['record_company_id'] . '&action=edit'); ?>'">
                    <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $record_company['record_company_id'] . '&action=edit'); ?>'">
                    <?php } ?>
                  <td class="dataTableContent"><?php echo $record_company['record_company_name']; ?></td>
                  <td class="dataTableContent text-right">
                      <?php
                      if (isset($aInfo) && is_object($aInfo) && ($record_company['record_company_id'] == $aInfo->record_company_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_RECORD_COMPANY, zen_get_all_get_params(array('mID')) . 'mID=' . $record_company['record_company_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_RECORD_COMPANY . '</h4>');
                $contents = array('form' => zen_draw_form('record_company', FILENAME_RECORD_COMPANY, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_NAME, 'record_company_name', 'class="control-label"') . zen_draw_input_field('record_company_name', '', zen_set_field_length(TABLE_RECORD_COMPANY, 'record_company_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE, 'record_company_image', 'class="control-label"') . zen_draw_file_field('record_company_image', '', 'class="form-control"'));

                $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                $default_directory = 'record_company/';

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE_MANUAL, 'record_company_image_manual', 'class="control-label"') . zen_draw_input_field('record_company_image_manual', '', 'class="form-control"'));

                $record_company_inputs_string = '';
                $languages = zen_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $record_company_inputs_string .= '<br><div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('record_company_url[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_RECORD_COMPANY_INFO, 'record_company_url') . ' class="form-control"') . '</div>';
                }

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_URL, 'record_company_url', 'class="control-label"') . $record_company_inputs_string);
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_RECORD_COMPANY . '</h4>');
                $contents = array('form' => zen_draw_form('record_company', FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_NAME, 'record_company_name', 'class="control-label"') . zen_draw_input_field('record_company_name', htmlspecialchars($aInfo->record_company_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_RECORD_COMPANY, 'record_company_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE, 'record_company_image', 'class="control-label"') . zen_draw_file_field('record_company_image', '', 'class="form-control"') . '<br>' . $aInfo->record_company_image);

                $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                $default_directory = substr($aInfo->record_company_image, 0, strpos($aInfo->record_company_image, '/') + 1);

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_IMAGE_MANUAL, 'record_company_image_manual', 'class="control-label"') . zen_draw_input_field('record_company_image_manual', '', 'class="form-control"'));
                $contents[] = array('text' => zen_info_image($aInfo->record_company_image, $aInfo->record_company_name));
                $record_company_inputs_string = '';
                $languages = zen_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $record_company_inputs_string .= '<br><div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('record_company_url[' . $languages[$i]['id'] . ']', zen_get_record_company_url($aInfo->record_company_id, $languages[$i]['id']), zen_set_field_length(TABLE_RECORD_COMPANY_INFO, 'record_company_url') . ' class="form-control"') . '</div>';
                }

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_COMPANY_URL, 'record_company_url', 'class="control-label"') . $record_company_inputs_string);
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_RECORD_COMPANY . '</h4>');

                $contents = array('form' => zen_draw_form('record_company', FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $aInfo->record_company_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $aInfo->record_company_name . '</b>');
                $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_image', '', true) . TEXT_DELETE_IMAGE . '</label></div>');

                if ($aInfo->products_count > 0) {
                  $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_products') . TEXT_DELETE_PRODUCTS . '</label></div>');
                  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count));
                }

                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($aInfo) && is_object($aInfo)) {
                  $heading[] = array('text' => '<h4>' . $aInfo->record_company_name . '</h4>');

                  $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($aInfo->date_added));
                  if (zen_not_null($aInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($aInfo->last_modified));
                  }
                  $contents[] = array('text' => '<br>' . zen_info_image($aInfo->record_company_image, $aInfo->record_company_name));
                  $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_count);
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
          <td><?php echo $record_company_split->display_count($record_company_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_RECORD_COMPANIES); ?></td>
          <td class="text-right"><?php echo $record_company_split->display_links($record_company_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
        </tr>
        <?php if (empty($action)) { ?>
          <tr>
            <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_RECORD_COMPANY, 'page=' . $_GET['page'] . '&mID=' . $aInfo->record_company_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
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
