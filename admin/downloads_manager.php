<?php
/*
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: John 2024 Jun 19 Modified in v2.1.0-alpha1 $
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$languages = zen_get_languages();

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);

// Override instructions in:
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
$show_download_date = $show_download_date ?? false;

if (!empty($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      $db_filename = zen_limit_image_filename($_POST['products_attributes_filename'], TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename');
      $sql = "UPDATE " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
              SET products_attributes_filename=:filename:,
                  products_attributes_maxdays=:maxdays:,
                  products_attributes_maxcount=:maxcount:
              WHERE products_attributes_id = " . (int)$_GET['padID'];
      $sql = $db->bindVars($sql, ':filename:', $db_filename, 'string');
      $sql = $db->bindVars($sql, ':maxdays:', $_POST['products_attributes_maxdays'], 'string');
      $sql = $db->bindVars($sql, ':maxcount:', $_POST['products_attributes_maxcount'], 'string');
      $db->Execute($sql);
      zen_record_admin_activity('Downloads-manager details added/updated for ' . $_POST['products_attributes_filename'], 'info');
      zen_redirect(zen_href_link(FILENAME_DOWNLOADS_MANAGER, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . (int)$_GET['padID']));
      break;
  }
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
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <div class="col-sm-offset-8 col-sm-4">
         <?php require DIR_WS_MODULES . 'search_box.php'; ?>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6">
          <div class="fa-stack fa-fw">
            <i class="fa-solid fa-circle fa-stack-1x txt-yellow"></i>
            <i class="fa-regular fa-circle fa-stack-1x txt-black"></i>
          </div>
          <?php echo TEXT_WARNING_PRODUCT_MISCONFIGURED_SHORT; ?>
          <div class="fa-stack fa-fw">
            <i class="fa-solid fa-circle fa-stack-1x txt-red"></i>
            <i class="fa-regular fa-circle fa-stack-1x txt-black"></i>
          </div>
          <?php echo TEXT_INFO_FILENAME_MISSING; ?>&nbsp;&nbsp;
          <div class="fa-stack fa-fw">
            <i class="fa-solid fa-circle fa-stack-1x txt-lime"></i>
            <i class="fa-regular fa-circle fa-stack-1x txt-black"></i>
          </div>
          <?php echo TEXT_INFO_FILENAME_GOOD; ?>
        </div>
      </div>
      <div class="row">
        <!-- body //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <!-- body_text //-->
          <!-- downloads by product_name//-->
          <table class="table table-hover" role="listbox">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ATTRIBUTES_ID; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRODUCTS_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPTION_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPTION_VALUE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_TEXT_FILENAME; ?></th>
<?php if ($show_download_date === true) { ?>
                <th class="dataTableHeadingContent"><?php echo TABLE_TEXT_DATE; ?></th>
<?php } ?>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_TEXT_MAX_DAYS; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_TEXT_MAX_COUNT; ?></th>
                <th class="dataTableHeadingContent">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
// create search filter
                $search = '';
                if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                  $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                    $keyword_search_fields = [
                        'pd.products_name',
                        'p.products_model',
                        'pd.products_description',
                        'pad.products_attributes_filename',
                    ];
                    $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords));
                }

// order of display
              $order_by = " ORDER BY pd.products_name ";

// create split page control
              $sql = "SELECT pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount,
                      pa.products_attributes_id, pa.options_id, pa.options_values_id, pd.products_name,
                      p.products_model, p.products_id, p.products_virtual, p.product_is_always_free_shipping
                      FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                      LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON pad.products_attributes_id = pa.products_attributes_id
                      LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pa.products_id = pd.products_id
                        AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                      LEFT JOIN " . TABLE_PRODUCTS . " p ON p.products_id = pd.products_id
                      WHERE pa.products_attributes_id = pad.products_attributes_id
                      " . $search . "
                      " . $order_by;
              $products_downloads_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, $sql, $products_downloads_query_numrows);
              $products_downloads_query = $db->Execute($sql);

              foreach ($products_downloads_query as $products_downloads) {

                if ((!isset($_GET['padID']) || (isset($_GET['padID']) && ($_GET['padID'] == $products_downloads['products_attributes_id']))) && !isset($padInfo)) {
                  $padInfo_array = $products_downloads;
                  $padInfo = new objectInfo($padInfo_array);
                }

                if (!defined('DIR_FS_DOWNLOAD')) {
                  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
                }

                $product_is_misconfigured = '';
                if ($products_downloads['product_is_always_free_shipping'] == 1 || $products_downloads['products_virtual'] == 1) {
                  $product_is_misconfigured = '<div class="fa-stack fa-fw"><i class="fa-solid fa-circle fa-stack-1x txt-yellow"></i><i class="fa-regular fa-circle fa-stack-1x txt-black"></i></div>';
                }
                $file_check = zen_orders_products_downloads($products_downloads['products_attributes_filename']);
                ?>
                <?php if (isset($padInfo) && is_object($padInfo) && ($products_downloads['products_attributes_id'] == $padInfo->products_attributes_id)) { ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action', 'page')) . ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $padInfo->products_attributes_id . '&action=edit'); ?>'" role="option" aria-selected="true">
                  <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action', 'page')) . ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $products_downloads['products_attributes_id']); ?>'" role="option" aria-selected="false">
                  <?php } ?>

                  <td class="text-right"><?php echo $products_downloads['products_attributes_id']; ?></td>
                  <td class="text-right"><?php echo $product_is_misconfigured . '&nbsp;' . $products_downloads['products_id']; ?></td>
                  <td><?php echo $products_downloads['products_name']; ?></td>
                  <td><?php echo $products_downloads['products_model']; ?></td>
                  <td><?php echo zen_options_name($products_downloads['options_id']); ?></td>
                  <td><?php echo zen_values_name($products_downloads['options_values_id']); ?></td>
                  <td>
                    <div class="fa-stack fa-fw">
                      <i class="fa-solid fa-circle fa-stack-1x<?php echo (!$file_check ? ' txt-red' : ' txt-lime'); ?>"></i>
                      <i class="fa-regular fa-circle fa-stack-1x txt-black"></i>
                    </div>
                    <?php echo $products_downloads['products_attributes_filename']; ?></td>
<?php if ($show_download_date === true) {
         $file_date = '';
         if ($file_check) {
            $handler = zen_get_download_handler($products_downloads['products_attributes_filename']);

            if ($handler === 'local') {
              $file_date = $zcDate->output(DATE_TIME_FORMAT, filemtime(DIR_FS_DOWNLOAD . $products_downloads['products_attributes_filename']));
            }
         }
?>
                  <td><?php echo $file_date;  ?></td>
<?php } ?>
                  <td class="text-right"><?php echo $products_downloads['products_attributes_maxdays']; ?></td>
                  <td class="text-right"><?php echo $products_downloads['products_attributes_maxcount']; ?></td>
                  <td class="text-right">
                    <?php if (isset($padInfo) && is_object($padInfo) && ($products_downloads['products_attributes_id'] == $padInfo->products_attributes_id)) {
                      echo zen_icon('caret-right', '', '2x', true);
                    } else { ?>
                      <a href="<?php echo zen_href_link(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID')) . ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $products_downloads['products_attributes_id']); ?>" data-toggle="tooltip" title="<?php echo IMAGE_ICON_INFO; ?>" role="button">
                        <?php echo zen_icon('circle-info', '', '2x', true, false) ?>
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
            case 'edit':
              $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_PRODUCTS_DOWNLOAD . '</h4>');

              $contents = array('form' => zen_draw_form('products_downloads_edit', FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $padInfo->products_attributes_id . '&action=save', 'post', 'class="form-horizontal"'));
              $contents[] = array('text' => '<b>' . TEXT_PRODUCTS_NAME . $padInfo->products_name . '<br>' . TEXT_PRODUCTS_MODEL . $padInfo->products_model . '</b>');
              $contents[] = array('text' => '<br>' . TEXT_INFO_EDIT_INTRO);
              $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_FILENAME, 'products_attributes_filename', 'class="control-label"') . zen_draw_input_field('products_attributes_filename', $padInfo->products_attributes_filename, 'class="form-control"'));
              $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_MAX_DAYS, 'products_attributes_maxdays', 'class="control-label"') . zen_draw_input_field('products_attributes_maxdays', $padInfo->products_attributes_maxdays, 'class="form-control"'));
              $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_MAX_COUNT, 'products_attributes_maxcount', 'class="control-label"') . zen_draw_input_field('products_attributes_maxcount', $padInfo->products_attributes_maxcount, 'class="form-control"'));
              $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_DOWNLOADS_MANAGER, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $padInfo->products_attributes_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
              break;
            default:
              if (isset($padInfo) && is_object($padInfo)) {
                $heading[] = array('text' => '<h4>' . $padInfo->products_attributes_id . ' ' . $padInfo->products_attributes_filename . '</h4>');

                $contents[] = array('align' => 'center', 'text' =>
                  '<a href="' . zen_href_link(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'padID=' . $padInfo->products_attributes_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>' .
                  '&nbsp;<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $padInfo->products_id . '&current_categories_id=' . $padInfo->master_categories_id) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_ATTRIBUTES . '</a>'
                );
                $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'product_type=' . $padInfo->products_type . '&pID=' . $padInfo->products_id . '&action=new_product') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_PRODUCT . '</a>');
                if ($padInfo->product_is_always_free_shipping == 1 || $padInfo->products_virtual == 1) {
                  $contents[] = array('params' => 'errorText', 'text' => '<br>' . TEXT_WARNING_PRODUCT_MISCONFIGURED);
                }
                $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_NAME . $padInfo->products_name);
                $contents[] = array('text' => TEXT_PRODUCTS_MODEL . $padInfo->products_model);
                $contents[] = array('text' => TEXT_INFO_FILENAME . $padInfo->products_attributes_filename);
                $contents[] = array('text' => TEXT_INFO_MAX_DAYS . $padInfo->products_attributes_maxdays);
                $contents[] = array('text' => TEXT_INFO_MAX_COUNT . $padInfo->products_attributes_maxcount);
              }
              break;
          }

          if (!empty($heading) && !empty($contents)) {
            $box = new box();
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
        <!-- downloads by product_name_eof //-->
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $products_downloads_split->display_count($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_DOWNLOADS_MANAGER); ?></td>
            <!--
            <td class="smallText text-right"><?php echo $products_downloads_split->display_links($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
            -->
            <td class="text-right"><?php echo $products_downloads_split->display_links($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php if (isset($_GET['search']) && zen_not_null($_GET['search'])) { ?>
            <tr>
              <td class="text-right" colspan="2"><?php echo '<a href="' . zen_href_link(FILENAME_DOWNLOADS_MANAGER) . '" class="btn btn-primary" role="button">' . IMAGE_RESET . '</a>'; ?></td>
            </tr>
          <?php } ?>
        </table>
      </div>
    </div>
    <!-- body_text_eof //-->
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
