<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2024 Sep 04 Modified in v2.1.0-beta1 $
 */
require 'includes/application_top.php';

$action = $_GET['action'] ?? '';
$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);
$languages = zen_get_languages();
if (!empty($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['mID'])) {
        $manufacturers_id = (int)$_GET['mID'];
      }
      $manufacturers_name = zen_db_prepare_input($_POST['manufacturers_name']);

      $featured = (!empty($_POST['featured']) ? (int)$_POST['featured'] : 0);

      $sql_data_array = ['manufacturers_name' => $manufacturers_name];

      $sql_data_array['featured'] = $featured;

      // -----
      // Give a watching observer the opportunity to add/update additional fields in the
      // manufacturers table.
      //
      $zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_INSERT_UPDATE', ['action' => $action, 'manufacturers_id' => $manufacturers_id ?? 0], $sql_data_array);

      if ($action === 'insert') {
        $insert_sql_data = ['date_added' => 'now()'];

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
        $manufacturers_id = zen_db_insert_id();
      } elseif ($action === 'save') {
        $update_sql_data = ['last_modified' => 'now()'];

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = " . (int)$manufacturers_id);
      }


      if ($_POST['manufacturers_image_manual'] != '') { // add image manually
        if ($_POST['manufacturers_image_manual'] === 'none') {
          $manufacturers_image_name = '';
        } else {
          $manufacturers_image_name = zen_db_input($_POST['img_dir'] . $_POST['manufacturers_image_manual']);
        }
        $db->Execute("UPDATE " . TABLE_MANUFACTURERS . "
                      SET manufacturers_image = '" . $manufacturers_image_name . "'
                      WHERE manufacturers_id = " . (int)$manufacturers_id);
      } else {
        $manufacturers_image = new upload('manufacturers_image');
        $manufacturers_image->set_extensions(['jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg']);
        $manufacturers_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ($manufacturers_image->parse() && $manufacturers_image->save()) {
          if ($manufacturers_image->filename !== 'none') {
            $db_filename = zen_limit_image_filename($manufacturers_image->filename, TABLE_MANUFACTURERS, 'manufacturers_image');
            $db->Execute("UPDATE " . TABLE_MANUFACTURERS . "
                          SET manufacturers_image = '" . zen_db_input($_POST['img_dir'] . $db_filename) . "'
                          WHERE manufacturers_id = " . (int)$manufacturers_id);
          } else {
            // remove image from database if 'none'
            $db->Execute("UPDATE " . TABLE_MANUFACTURERS . "
                          SET manufacturers_image = ''
                          WHERE manufacturers_id = " . (int)$manufacturers_id);
          }
        }
      }

      for ($i = 0, $n = count($languages); $i < $n; $i++) {
        $manufacturers_url_array = $_POST['manufacturers_url'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = ['manufacturers_url' => zen_db_prepare_input($manufacturers_url_array[$language_id])];

        if ($action === 'insert') {
          $insert_sql_data = [
            'manufacturers_id' => $manufacturers_id,
            'languages_id' => $language_id
          ];

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
        } elseif ($action === 'save') {
          zen_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = " . (int)$manufacturers_id . " and languages_id = " . (int)$language_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $manufacturers_id));
      break;
    case 'deleteconfirm':
      $manufacturers_id = zen_db_prepare_input($_POST['mID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] === 'on')) {
        $manufacturer = $db->Execute("SELECT manufacturers_image
                                      FROM " . TABLE_MANUFACTURERS . "
                                      WHERE manufacturers_id = " . (int)$manufacturers_id);

        $image_location = DIR_FS_CATALOG_IMAGES . $manufacturer->fields['manufacturers_image'];

        if (file_exists($image_location)) {
          @unlink($image_location);
        }
      }

      $db->Execute("DELETE FROM " . TABLE_MANUFACTURERS . "
                    WHERE manufacturers_id = " . (int)$manufacturers_id);
      $db->Execute("DELETE FROM " . TABLE_MANUFACTURERS_INFO . "
                    WHERE manufacturers_id = " . (int)$manufacturers_id);

      if (isset($_POST['delete_products']) && ($_POST['delete_products'] === 'on')) {
        $products = $db->Execute("SELECT products_id
                                  FROM " . TABLE_PRODUCTS . "
                                  WHERE manufacturers_id = " . (int)$manufacturers_id);

        foreach ($products as $product) {
          zen_remove_product($product['products_id']);
        }
      } else {
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET manufacturers_id = 0
                      WHERE manufacturers_id = " . (int)$manufacturers_id);
      }

      zen_redirect(zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '')));
      break;

    default:
      // -----
      // Give a watching observer the opportunity to add/update additional fields in the
      // manufacturers table.
      //
      $zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_DEFAULT_ACTION', ['action' => $action]);
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
      .p_label {
        display: inline-block;
        max-width: 100%;
        margin-bottom: 5px;
        font-weight: 700;
      }
    </style>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURER_FEATURED; ?></th>
<?php
// -----
// A watching observer can add extra table column headings via an associative array in the form:
//
// $extra_headings = [
//     [
//       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
//       'text' => $value
//     ],
// ];
//
// Observer note:  Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
// multiple observers might be injecting content!
//
$extra_headings = false;
$zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_EXTRA_COLUMN_HEADING', [], $extra_headings);
if (is_array($extra_headings)) {
  foreach ($extra_headings as $heading_info) {
      $align = (isset($heading_info['align'])) ? (' text-' . $heading_info['align']) : '';
?>
                <th class="dataTableHeadingContent<?php echo $align; ?>"><?php echo $heading_info['text']; ?></th>
<?php
    }
}
?>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $manufacturers_query_raw =
                "SELECT *, (featured=1) AS weighted
                   FROM " . TABLE_MANUFACTURERS . "
                  ORDER BY weighted DESC, manufacturers_name";

              // reset page when page is unknown
              if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['mID'])) {
                $check_page = $db->Execute($manufacturers_query_raw);
                $check_count = 0;
                if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                  foreach ($check_page as $item) {
                    if ($item['manufacturers_id'] == $_GET['mID']) {
                      break;
                    }
                    $check_count++;
                  }
                  $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                } else {
                  $_GET['page'] = 1;
                }
              }

              $manufacturers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $manufacturers_query_raw, $manufacturers_query_numrows);
              $manufacturers = $db->Execute($manufacturers_query_raw);
              foreach ($manufacturers as $manufacturer) {
                if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $manufacturer['manufacturers_id']))) && !isset($mInfo) && (substr($action, 0, 3) !== 'new')) {
                  $manufacturer_products = $db->Execute("SELECT COUNT(*) AS products_count
                                                         FROM " . TABLE_PRODUCTS . "
                                                         WHERE manufacturers_id = " . (int)$manufacturer['manufacturers_id']);

                  $mInfo_array = array_merge($manufacturer, $manufacturer_products->fields);
                  $mInfo = new objectInfo($mInfo_array);
                }

                if (isset($mInfo) && is_object($mInfo) && ($manufacturer['manufacturers_id'] == $mInfo->manufacturers_id)) {
                  ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $manufacturer['manufacturers_id'] . '&action=edit'); ?>'">
                  <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $manufacturer['manufacturers_id'] . '&action=edit'); ?>'" style="cursor:pointer;">
                  <?php } ?>
                  <td class="dataTableContent text-center"><?php echo $manufacturer['manufacturers_id']; ?></td>
                  <td class="dataTableContent"><?php echo $manufacturer['manufacturers_name']; ?></td>
                  <td class="dataTableContent"><?php echo $manufacturer['featured'] ? '<strong>' . TEXT_YES . '</strong>' : TEXT_NO; ?></td>
<?php
// -----
// A watching observer can provide any added manufacturers' fields' values to the listing
// via an associative array in the form:
//
// $extra_data = [
//     [
//       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
//       'text' => $value
//     ],
// ];
//
// Observer note:  Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
// multiple observers might be injecting content!
//
$extra_data = false;
$zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_EXTRA_COLUMN_DATA', $manufacturer, $extra_data);
if (is_array($extra_data)) {
    foreach ($extra_data as $data_info) {
        $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
?>
                  <td class="dataTableContent<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
<?php
    }
}
?>
                  <td class="dataTableContent text-right actions">
                    <div class="btn-group">
                    <a href="<?php echo zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $manufacturer['manufacturers_id'] . '&action=edit'); ?>" class="btn btn-sm btn-default btn-edit" role="button" data-toggle="tooltip" title="<?php echo ICON_EDIT ?>">
                      <?php echo zen_icon('pencil', hidden: true) ?>
                    </a>
                    <a href="<?php echo zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $manufacturer['manufacturers_id'] . '&action=delete'); ?>" class="btn btn-sm btn-default btn-delete" role="button" data-toggle="tooltip" title="<?php echo ICON_DELETE ?>">
                      <?php echo zen_icon('trash', hidden: true) ?>
                    </a>
                    </div>
                    <?php if (isset($mInfo) && is_object($mInfo) && ($manufacturer['manufacturers_id'] == $mInfo->manufacturers_id)) {
                      echo zen_icon('caret-right', '', '2x', true);
                    } else { ?>
                      <a href="<?php echo zen_href_link(FILENAME_MANUFACTURERS, zen_get_all_get_params(['mID']) . 'mID=' . $manufacturer['manufacturers_id']); ?>">
                        <?php echo zen_icon('circle-info', '', '2x', true, true) ?>
                      </a>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
          <?php
          $heading = [];
          $contents = [];

          switch ($action) {
            case 'new':
              $heading[] = ['text' => '<h4>' . TEXT_HEADING_NEW_MANUFACTURER . '</h4>'];

              $contents = ['form' => zen_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'action=insert', 'post', 'enctype="multipart/form-data" class="form-horizontal"')];
              $contents[] = ['text' => TEXT_NEW_INTRO];
              $contents[] = ['text' => zen_draw_label(TEXT_MANUFACTURERS_NAME, 'manufacturers_name', 'class="control-label"') . zen_draw_input_field('manufacturers_name', '', zen_set_field_length(TABLE_MANUFACTURERS, 'manufacturers_name') . ' class="form-control" id="manufacturers_name" required')];
              $contents[] = ['text' => '<label class="checkbox-inline">' . zen_draw_checkbox_field('featured') . TEXT_MANUFACTURER_FEATURED_LABEL . '</label>'];

              // -----
              // Give a watching observer the opportunity to add additional content to the sidebox
              // form to gather any new fields it might support via an array of arrays in the form:
              //
              // $additional_contents = [
              //     [
              //       'align' => $alignment,    // (Optional) One of 'text-center', 'text-right', or 'text-left'.
              //       'text' => $value
              //     ],
              // ];
              //
              // Observer note:  Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
              // multiple observers might be injecting content!
              //
              $additional_contents = false;
              $zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_NEW', '', $additional_contents);
              if (is_array($additional_contents)) {
                  foreach ($additional_contents as $next_addition) {
                      $contents[] = $next_addition;
                  }
              }

              $contents[] = ['text' => zen_draw_label(TEXT_MANUFACTURERS_IMAGE, 'manufacturers_image', 'class="control-label"') . zen_draw_file_field('manufacturers_image', '', 'class="form-control" id="manufacturers_image"')];
              $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
              $default_directory = 'manufacturers/';

              $contents[] = ['text' => zen_draw_label(TEXT_UPLOAD_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control" id="img_dir"')];

              $contents[] = ['text' => zen_draw_label(TEXT_IMAGE_MANUAL, 'manufacturers_image_manual', 'class="control-label"') . zen_draw_input_field('manufacturers_image_manual', '', 'class="form-control" id="manufacturers_image_manual"')];

              $manufacturer_inputs_string = '';
              for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $manufacturer_inputs_string .= '<div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_MANUFACTURERS_INFO, 'manufacturers_url') . ' class="form-control"') . '</div><br>';
              }

              $contents[] = ['text' => '<p class="p_label control-label">' . TEXT_MANUFACTURERS_URL . '</p>' . $manufacturer_inputs_string];
              $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . ($_GET['mID'] ?? '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
              break;
            case 'edit':
              $heading[] = ['text' => '<h4>' . TEXT_HEADING_EDIT_MANUFACTURER . '</h4>'];

              $contents = ['form' => zen_draw_form('manufacturers', FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $mInfo->manufacturers_id . '&action=save', 'post', 'enctype="multipart/form-data" class="form-horizontal"')];
              $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
              $contents[] = ['text' => zen_draw_label(TEXT_MANUFACTURERS_NAME, 'manufacturers_name', 'class="control-label"') . zen_draw_input_field('manufacturers_name', htmlspecialchars($mInfo->manufacturers_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_MANUFACTURERS, 'manufacturers_name') . ' class="form-control" id="manufacturers_name" required')];
              $contents[] = ['text' => '<label class="checkbox-inline">' . zen_draw_checkbox_field('featured', '1', $mInfo->featured) . TEXT_MANUFACTURER_FEATURED_LABEL . '</label>'];

              // -----
              // Give a watching observer the opportunity to add additional content to the sidebox
              // form to manage any new fields it might support via an array of arrays in the form:
              //
              // $additional_contents = [
              //     [
              //       'align' => $alignment,    // (Optional) One of 'text-center', 'text-right', or 'text-left'.
              //       'text' => $value
              //     ],
              // ];
              //
              // Observer note:  Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
              // multiple observers might be injecting content!
              //
              $additional_contents = false;
              $zco_notifier->notify('NOTIFY_ADMIN_MANUFACTURERS_EDIT', $mInfo, $additional_contents);
              if (is_array($additional_contents)) {
                  foreach ($additional_contents as $next_addition) {
                      $contents[] = $next_addition;
                  }
              }

              $contents[] = ['text' => zen_draw_label(TEXT_MANUFACTURERS_IMAGE, 'manufacturers_image', 'class="control-label"') . zen_draw_file_field('manufacturers_image', '', ' class="form-control" id="manufacturers_image"') . '<br>' . $mInfo->manufacturers_image];
              $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
              $default_directory = ($mInfo->manufacturers_image === null) ? '/' : substr($mInfo->manufacturers_image, 0, strpos($mInfo->manufacturers_image, '/') + 1);

              $contents[] = ['text' => zen_draw_label(TEXT_UPLOAD_DIR, 'img_dir', 'class="control-label"') . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control" id="img_dir"')];

              $contents[] = ['text' => zen_draw_label(TEXT_IMAGE_MANUAL, 'manufacturers_image_manual', 'class="control-label"') . zen_draw_input_field('manufacturers_image_manual', '', 'class="form-control" id="manufacturers_image_manual"')];

              $contents[] = ['text' => zen_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
              $manufacturer_inputs_string = '';
              for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $manufacturer_inputs_string .= '<div class="input-group"><span class="input-group-addon">' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '</span>' . zen_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', zen_get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']), zen_set_field_length(TABLE_MANUFACTURERS_INFO, 'manufacturers_url') . ' class="form-control"') . '</div><br>';
              }

              $contents[] = ['text' => '<p class="p_label control-label">' . TEXT_MANUFACTURERS_URL . '</p>' . $manufacturer_inputs_string];
              $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $mInfo->manufacturers_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
              break;
            case 'delete':
              $heading[] = ['text' => '<h4>' . TEXT_HEADING_DELETE_MANUFACTURER . '</h4>'];

              $contents = ['form' => zen_draw_form('manufacturers', FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=deleteconfirm', 'post', 'class="form-horizontal"') . zen_draw_hidden_field('mID', $mInfo->manufacturers_id)];
              $contents[] = ['text' => TEXT_DELETE_INTRO];
              $contents[] = ['text' => '<b>' . $mInfo->manufacturers_name . '</b>'];
              $contents[] = ['text' => '<label class="checkbox-inline">' . zen_draw_checkbox_field('delete_image', '', true) . TEXT_DELETE_IMAGE . '</label>'];

              if ($mInfo->products_count > 0) {
                $contents[] = ['text' => '<label class="checkbox-inline">' . zen_draw_checkbox_field('delete_products') . TEXT_DELETE_PRODUCTS . '</label>'];
                $contents[] = ['text' => sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count)];
              }

              $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $mInfo->manufacturers_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
              break;
            default:
              if (isset($mInfo) && is_object($mInfo)) {
                $heading[] = ['text' => '<h4>' . $mInfo->manufacturers_name . '</h4>'];

                $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $mInfo->manufacturers_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'mID=' . $mInfo->manufacturers_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                if ($mInfo->featured) {
                  $contents[] = ['align' => 'text-center', 'text' => '<strong>' . TEXT_MANUFACTURER_IS_FEATURED . '</strong>'];
                }
                $contents[] = ['text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($mInfo->date_added)];
                if (zen_not_null($mInfo->last_modified)) {
                  $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($mInfo->last_modified)];
                }
                $contents[] = ['text' => zen_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
                $contents[] = ['text' => TEXT_PRODUCTS . ' ' . $mInfo->products_count];
              }
              break;
          }

          if (!empty($heading) && !empty($contents)) {
            $box = new box();
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
        <!-- body_text_eof //-->
      </div>
       <?php
      if (empty($action)) {
        $current_mid = (isset($mInfo)) ? 'mID=' . $mInfo->manufacturers_id . '&' : '';
     ?>
        <div class="col-sm-12 text-right">
          <a href="<?php echo zen_href_link(FILENAME_MANUFACTURERS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . $current_mid . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a>
        </div>
      <?php } ?>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $manufacturers_split->display_count($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?></td>
            <td class="text-right"><?php echo $manufacturers_split->display_links($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
        </table>
      </div>
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
