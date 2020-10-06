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
        $artists_id = zen_db_prepare_input($_GET['mID']);
      }
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

        zen_db_perform(TABLE_RECORD_ARTISTS, $sql_data_array, 'update', "artists_id = " . (int)$artists_id);
      }

      if ($_POST['artists_image_manual'] != '') {
        // add image manually
        $artists_image_name = zen_db_input($_POST['img_dir'] . $_POST['artists_image_manual']);
        $db->Execute("UPDATE " . TABLE_RECORD_ARTISTS . "
                      SET artists_image = '" . $artists_image_name . "'
                      WHERE artists_id = " . (int)$artists_id);
      } else {
        $artists_image = new upload('artists_image');
        $artists_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
        $artists_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ($artists_image->parse() && $artists_image->save()) {
          if ($artists_image->filename != 'none') {
            $db_filename = zen_limit_image_filename($artists_image->filename, TABLE_RECORD_ARTISTS, 'artists_image');
            $db->Execute("UPDATE " . TABLE_RECORD_ARTISTS . "
                          SET artists_image = '" . zen_db_input($_POST['img_dir'] . $db_filename) . "'
                          WHERE artists_id = " . (int)$artists_id);
          } else {
            // remove image from database if 'none'
            $db->Execute("UPDATE " . TABLE_RECORD_ARTISTS . "
                          SET artists_image = ''
                          WHERE artists_id = " . (int)$artists_id);
          }
        }
      }

      $languages = zen_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $artists_url_array = $_POST['artists_url'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array('artists_url' => zen_db_prepare_input($artists_url_array[$language_id]));

        if ($action == 'insert') {
          $insert_sql_data = array(
            'artists_id' => $artists_id,
            'languages_id' => $language_id);

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_RECORD_ARTISTS_INFO, $sql_data_array);
        } elseif ($action == 'save') {
          zen_db_perform_language(TABLE_RECORD_ARTISTS_INFO, $sql_data_array, 'artists_id', (int)$artists_id, (int)$language_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_RECORD_ARTISTS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $artists_id));
      break;
    case 'deleteconfirm':
      $artists_id = zen_db_prepare_input($_POST['mID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {

        $manufacturer = $db->Execute("SELECT artists_image
                                      FROM " . TABLE_RECORD_ARTISTS . "
                                      WHERE artists_id = " . (int)$artists_id);
        $image_location = DIR_FS_CATALOG_IMAGES . $manufacturer->fields['artists_image'];

        if (file_exists($image_location))
          @unlink($image_location);
      }

      $db->Execute("DELETE FROM " . TABLE_RECORD_ARTISTS . "
                    WHERE artists_id = " . (int)$artists_id);
      $db->Execute("DELETE FROM " . TABLE_RECORD_ARTISTS_INFO . "
                    WHERE artists_id = " . (int)$artists_id);

      if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
        $products = $db->Execute("SELECT products_id
                                  FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                  WHERE artists_id = " . (int)$artists_id);

        foreach ($products as $product) {
          zen_remove_product($product['products_id']);
        }
      } else {
        $db->Execute("UPDATE " . TABLE_PRODUCT_MUSIC_EXTRA . "
                      SET artists_id = 0 
                      WHERE artists_id = " . (int)$artists_id);
      }

      zen_redirect(zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page']));
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
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_RECORD_ARTISTS; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $artists_query_raw = "SELECT *
                                      FROM " . TABLE_RECORD_ARTISTS . "
                                      ORDER BY artists_name";
                $artists_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $artists_query_raw, $artists_query_numrows);
                $artists = $db->Execute($artists_query_raw);

                foreach ($artists as $artist) {
                  if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $artist['artists_id']))) && (substr($action, 0, 3) != 'new')) {
                    $artists_products = $db->Execute("SELECT COUNT(*) AS products_count
                                                      FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                                      WHERE artists_id = " . (int)$artist['artists_id']);

                    $aInfo_array = array_merge($artist, $artists_products->fields);
                    $aInfo = new objectInfo($aInfo_array);
                  }

                  if (isset($aInfo) && is_object($aInfo) && ($artist['artists_id'] == $aInfo->artists_id)) {
                    ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artist['artists_id'] . '&action=edit'); ?>'">
                    <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $artist['artists_id'] . '&action=edit'); ?>'">
                    <?php } ?>
                  <td class="dataTableContent"><?php echo $artist['artists_name']; ?></td>
                  <td class="dataTableContent text-right">
                      <?php
                      if (isset($aInfo) && is_object($aInfo) && ($artist['artists_id'] == $aInfo->artists_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, zen_get_all_get_params(array('mID')) . 'mID=' . $artist['artists_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_RECORD_ARTIST . '</h4>');
                $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_NAME, 'artists_name', 'class="control-label"') . zen_draw_input_field('artists_name', '', zen_set_field_length(TABLE_RECORD_ARTISTS, 'artists_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_IMAGE, 'artists_image', 'class="control-label"') . zen_draw_file_field('artists_image', '', 'class="form-control"'));

                $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                $default_directory = 'artists/';

                $contents[] = array('text' => zen_draw_label(TEXT_ARTISTS_IMAGE_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_ARTISTS_IMAGE_MANUAL, 'artists_image_manual', 'class="control-label"') . zen_draw_input_field('artists_image_manual', '', 'class="form-control"'));

                $manufacturer_inputs_string = '';
                $languages = zen_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $manufacturer_inputs_string .= '<br><div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('artists_url[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_RECORD_ARTISTS_INFO, 'artists_url') . ' class="form-control"') . '</div>';
                }

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_URL, 'artists_url', 'class="control-label"') . $manufacturer_inputs_string);
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_RECORD_ARTIST . '</h4>');
                $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_NAME, 'artists_name', 'class="control-label"') . zen_draw_input_field('artists_name', htmlspecialchars($aInfo->artists_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_RECORD_ARTISTS, 'artists_name') . ' class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_IMAGE, 'artists_image', 'class="control-label"') . zen_draw_file_field('artists_image', '', 'class="form-control"') . '<br>' . $aInfo->artists_image);

                $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
                $default_directory = substr($aInfo->artists_image, 0, strpos($aInfo->artists_image, '/') + 1);

                $contents[] = array('text' => zen_draw_label(TEXT_ARTISTS_IMAGE_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => zen_draw_label(TEXT_ARTISTS_IMAGE_MANUAL, 'artists_image_manual', 'class="control-label"') . zen_draw_input_field('artists_image_manual', '', 'class="form-control"'));
                $contents[] = array('text' => zen_info_image($aInfo->artists_image, $aInfo->artists_name));
                $manufacturer_inputs_string = '';
                $languages = zen_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $manufacturer_inputs_string .= '<br><div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('artists_url[' . $languages[$i]['id'] . ']', zen_get_artists_url($aInfo->artists_id, $languages[$i]['id']), zen_set_field_length(TABLE_RECORD_ARTISTS_INFO, 'artists_url') . ' class="form-control"') . '</div>';
                }

                $contents[] = array('text' => zen_draw_label(TEXT_RECORD_ARTIST_URL, 'artists_url', 'class="control-label"') . $manufacturer_inputs_string);
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_RECORD_ARTIST . '</h4>');

                $contents = array('form' => zen_draw_form('artists', FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $aInfo->artists_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $aInfo->artists_name . '</b>');
                $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_image', '', true) . TEXT_DELETE_IMAGE . '</label></div>');

                if ($aInfo->products_count > 0) {
                  $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_products') . TEXT_DELETE_PRODUCTS . '</label></div>');
                  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count));
                }

                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($aInfo) && is_object($aInfo)) {
                  $heading[] = array('text' => '<h4>' . $aInfo->artists_name . '</h4>');

                  $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($aInfo->date_added));
                  if (zen_not_null($aInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($aInfo->last_modified));
                  }
                  $contents[] = array('text' => '<br>' . zen_info_image($aInfo->artists_image, $aInfo->artists_name));
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
          <td><?php echo $artists_split->display_count($artists_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ARTISTS); ?></td>
          <td class="text-right"><?php echo $artists_split->display_links($artists_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
        </tr>
        <?php if (empty($action)) { ?>
          <tr>
            <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_RECORD_ARTISTS, 'page=' . $_GET['page'] . '&mID=' . $aInfo->artists_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
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
