<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 09 Modified in v1.5.7 $
 * structurally identical to featured.php, modifications should be replicated
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);

if (zen_not_null($action)) {
  switch ($action) {
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] === '1' || $_POST['flag'] === '0')) {
        zen_set_specials_status((int)$_POST['id'], (int)$_POST['flag']);
        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("SELECT products_id
                                      FROM " . TABLE_SPECIALS . "
                                      WHERE specials_id = " . (int)$_POST['id']);
        zen_update_products_price_sorter($update_price->fields['products_id']);
        zen_redirect(zen_href_link(FILENAME_SPECIALS, zen_get_all_get_params(['action']), 'NONSSL'));
      }
      break;
    case 'insert':
      if (empty($_POST['products_id'])) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
      } else {
        $products_id = (int)$_POST['products_id'];
        $products_price = (float)$_POST['products_price'];

        $tmp_value = zen_db_prepare_input($_POST['specials_price']);
        $specials_price = (!zen_not_null($tmp_value) || $tmp_value === '' || $tmp_value === 0) ? 0 : $tmp_value;

        if (substr($specials_price, -1) === '%') {
          $new_special_insert = $db->Execute("SELECT products_id, products_price, products_priced_by_attribute
                                              FROM " . TABLE_PRODUCTS . "
                                              WHERE products_id = " . (int)$products_id);

// check if priced by attribute
          if ($new_special_insert->fields['products_priced_by_attribute'] === '1') {
            $products_price = zen_get_products_base_price($products_id);
          } else {
            $products_price = $new_special_insert->fields['products_price'];
          }

          $specials_price = ((float)$products_price - (((float)$specials_price / 100) * (float)$products_price));
        }

        $specials_date_available_raw = zen_db_prepare_input($_POST['specials_date_available']);
        if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($specials_date_available_raw)) {
          $local_fmt = zen_datepicker_format_fordate();
          $dt = DateTime::createFromFormat($local_fmt, $specials_date_available_raw);
          $specials_date_available_raw = 'null';
          if (!empty($dt)) {
            $specials_date_available_raw = $dt->format('Y-m-d');
          }
        }
        $specials_date_available = (date('Y-m-d') < $specials_date_available_raw) ? $specials_date_available_raw : '0001-01-01';
        $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
        if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($expires_date_raw)) {
          $local_fmt = zen_datepicker_format_fordate();
          $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
          $expires_date_raw = 'null';
          if (!empty($dt)) {
            $expires_date_raw = $dt->format('Y-m-d');
          }
        }
        $expires_date = (date('Y-m-d') < $expires_date_raw) ? $expires_date_raw : '0001-01-01';

        $db->Execute("INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                      VALUES (" . (int)$products_id . ", " . (float)$specials_price . ", now(), '" . zen_db_input($expires_date) . "', 1, '" . zen_db_input($specials_date_available) . "')");

        $new_special = $db->Execute("SELECT specials_id
                                     FROM " . TABLE_SPECIALS . "
                                     WHERE products_id = " . (int)$products_id);

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter((int)$products_id);
      } // nothing selected
      if (isset($_GET['go_back']) && $_GET['go_back'] === 'ON') {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_id . '&current_category_id=' . $_GET['current_category_id']));
      } else {
        zen_redirect(zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] . '&' : '') . (isset($new_special) ? 'sID=' . $new_special->fields['specials_id'] : '')));
      }
      break;
    case 'update':
      $specials_id = (int)$_POST['specials_id'];

      if ($_POST['products_priced_by_attribute'] === '1') {
        $products_price = zen_get_products_base_price($_POST['update_products_id']);
      } else {
        $products_price = (float)$_POST['products_price'];
      }

      $tmp_value = (float)$_POST['specials_price'];
      $specials_price = (!zen_not_null($tmp_value) || $tmp_value === '' || $tmp_value === 0) ? 0 : $tmp_value;

      if (substr($specials_price, -1) === '%') {
        $specials_price = ((float)$products_price - (((float)$specials_price / 100) * (float)$products_price));
      }

      $specials_date_available_raw = zen_db_prepare_input($_POST['specials_date_available']);
      if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($specials_date_available_raw)) {
        $local_fmt = zen_datepicker_format_fordate();
        $dt = DateTime::createFromFormat($local_fmt, $specials_date_available_raw);
        $specials_date_available_raw = 'null';
        if (!empty($dt)) {
          $specials_date_available_raw = $dt->format('Y-m-d');
        }
      }
      $specials_date_available = (date('Y-m-d') < $specials_date_available_raw) ? $specials_date_available_raw : '0001-01-01';
      $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
      if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($expires_date_raw)) {
        $local_fmt = zen_datepicker_format_fordate();
        $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
        $expires_date_raw = 'null';
        if (!empty($dt)) {
          $expires_date_raw = $dt->format('Y-m-d');
        }
      }
      $expires_date = (date('Y-m-d') < $expires_date_raw) ? $expires_date_raw : '0001-01-01';

      $db->Execute("UPDATE " . TABLE_SPECIALS . "
                    SET specials_new_products_price = '" . zen_db_input($specials_price) . "',
                        specials_last_modified = now(),
                        expires_date = '" . zen_db_input($expires_date) . "',
                        specials_date_available = '" . zen_db_input($specials_date_available) . "'
                    WHERE specials_id = " . (int)$specials_id);

      // reset products_price_sorter for searches etc.
      $update_price = $db->Execute("SELECT products_id
                                    FROM " . TABLE_SPECIALS . "
                                    WHERE specials_id = " . (int)$specials_id);
      zen_update_products_price_sorter($update_price->fields['products_id']);

      zen_redirect(zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'sID=' . (int)$specials_id));
      break;
    case 'deleteconfirm':
      $specials_id = (int)$_POST['sID'];

      // reset products_price_sorter for searches etc.
      $update_price = $db->Execute("SELECT products_id
                                    FROM " . TABLE_SPECIALS . "
                                    WHERE specials_id = " . (int)$specials_id);
      $update_price_id = $update_price->fields['products_id'];

      $db->Execute("DELETE FROM " . TABLE_SPECIALS . "
                    WHERE specials_id = " . (int)$specials_id);

      zen_update_products_price_sorter($update_price_id);

      zen_redirect(zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '')));
      break;
    case 'pre_add_confirmation':
      $skip_special = false;
      // check for no PID entered
      if (empty($_POST['pre_add_products_id'])) {
        $skip_special = true;
        $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_PID_EMPTY, 'caution');
      } else {
        $sql = "SELECT products_id, products_model
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_product = $db->Execute($sql);
        if ($check_product->RecordCount() < 1) {// check for valid PID
          $skip_special = true;
          $messageStack->add_session(sprintf(WARNING_SPECIALS_PRE_ADD_PID_NO_EXIST, (int)$_POST['pre_add_products_id']), 'caution');
        } elseif ((!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL === 'false') && (substr($check_product->fields['products_model'], 0, 4) === 'GIFT')) { // check for PID as a gift voucher
          $skip_special = true;
          $messageStack->add_session(sprintf(WARNING_SPECIALS_PRE_ADD_PID_GIFT, (int)$_POST['pre_add_products_id']), 'caution');
        }
      }
      // check if Special already exists
      if ($skip_special === false) {
        $sql = "SELECT specials_id
                FROM " . TABLE_SPECIALS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_special = $db->Execute($sql);
        if ($check_special->RecordCount() > 0) {
          $skip_special = true;
          $messageStack->add_session(sprintf(WARNING_SPECIALS_PRE_ADD_PID_DUPLICATE, (int)$_POST['pre_add_products_id']), 'caution');
        }
      }
      if ($skip_special === true) {
        zen_redirect(zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (!empty($check_special->fields['specials_id']) ? 'sID=' . (int)$check_special->fields['specials_id'] . '&action=edit' : '' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))));
      } else { // product id is valid
        zen_redirect(zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=new' . '&preID=' . (int)$_POST['pre_add_products_id']));
      }
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
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <?php
      if (($action === 'new') || ($action === 'edit')) {
        $form_action = 'insert';
        if (($action === 'edit') && isset($_GET['sID'])) { //update existing Special
          $form_action = 'update';

          $product = $db->Execute("SELECT p.products_id, p.products_model, pd.products_name, p.products_price, p.products_priced_by_attribute,
                                          s.specials_new_products_price, s.expires_date, s.specials_date_available
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                        " . TABLE_SPECIALS . " s
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = s.products_id
                                   AND s.specials_id = " . (int)$_GET['sID']);

          $sInfo = new objectInfo($product->fields);

          if ($sInfo->products_priced_by_attribute === '1') {
            $sInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } elseif (($action === 'new') && isset($_GET['preID'])) { //update existing Special
          $form_action = 'insert';

          $product = $db->Execute("SELECT p.products_id, p.products_model, pd.products_name, p.products_price, p.products_priced_by_attribute
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = " . (int)$_GET['preID']);

          $sInfo = new objectInfo($product->fields);

          if ($sInfo->products_priced_by_attribute === '1') {
            $sInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } elseif (empty($_GET['preID'])) { // insert by product select dropdown
          $sInfo = new objectInfo([]);

// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
          $specials_array = [];
          $specials = $db->Execute("SELECT p.products_id, p.products_model
                                    FROM " . TABLE_PRODUCTS . " p,
                                         " . TABLE_SPECIALS . " s
                                    WHERE s.products_id = p.products_id");

          foreach ($specials as $special) {
            $specials_array[] = $special['products_id'];
          }

// never include Gift Vouchers for specials when set to false
          if (!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL === 'false') {
            $gift_vouchers = $db->Execute("SELECT distinct p.products_id, p.products_model
                                           FROM " . TABLE_PRODUCTS . " p,
                                                " . TABLE_SPECIALS . " s
                                           WHERE p.products_model RLIKE '" . "GIFT" . "'");

            foreach ($gift_vouchers as $gift_voucher) {
              if (substr($gift_voucher['products_model'], 0, 4) === 'GIFT') {
                $specials_array[] = $gift_voucher['products_id'];
              }
            }
          }
// Uncomment the following to not include things that cannot go in the cart
//          $not_for_cart = $db->Execute("SELECT p.products_id
//                                        FROM " . TABLE_PRODUCTS . " p
//                                        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
//                                        WHERE pt.allow_add_to_cart = 'N'");
//          foreach ($not_for_cart as $item) {
//            $specials_array[] = $item['products_id'];
//          }
        }
        ?>
        <div class="row">
          <?php echo zen_draw_form('new_special', FILENAME_SPECIALS, zen_get_all_get_params(['action', 'info', 'sID']) . 'action=' . $form_action . (!empty($_GET['go_back']) ? '&go_back=' . $_GET['go_back'] : ''), 'post', 'class="form-horizontal"'); ?>
          <?php
          if ($form_action === 'update') {
            echo zen_draw_hidden_field('specials_id', $_GET['sID']);
          }
          if (!empty($_GET['preID'])) { // new Special: insert by product ID
            echo zen_draw_hidden_field('products_id', $_GET['preID']);
          }
          ?>
          <div class="form-group">
            <?php if (isset($sInfo->products_name)) { // Special is already defined/this is an update ?>
              <p class="col-sm-3 control-label"><?php echo TEXT_SPECIALS_PRODUCT; ?></p>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo 'ID#' . $sInfo->products_id . ': ' . $sInfo->products_model . ' - "' . zen_clean_html($sInfo->products_name) . '" (' . $currencies->format($sInfo->products_price) . ')'; ?></span>
              </div>
            </div>
            <?php
          } elseif (!empty($_GET['preID'])) { // new Special: insert by product ID
            $preID = (int)$_GET['preID'];
            ?>
            <div class="form-group">
              <p class="col-sm-3 control-label"><?php echo TEXT_SPECIALS_PRODUCT; ?></p>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo 'ID#' . $preID . ': ' . zen_get_products_model($preID) . ' - "' . zen_clean_html(zen_get_products_name($preID)) . '" (' . $currencies->format(zen_get_products_base_price($preID)) . ')'; ?></span>
              </div>
            </div>
          <?php } else { ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_SPECIALS_PRODUCT, 'products_id', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php
                if (empty($prev_next_order)) {
                  $prev_next_order = ' ORDER BY products_model'; // set sort order of dropdown
                }
                echo zen_draw_products_pull_down('products_id', 'required size="15" class="form-control" id="products_id"', $specials_array, true, (!empty($_GET['add_products_id']) ? $_GET['add_products_id'] : ''), true);
                ?>
              </div>
            </div>
          <?php } ?>
          <?php echo zen_draw_hidden_field('products_priced_by_attribute', $sInfo->products_priced_by_attribute); ?>
          <?php echo zen_draw_hidden_field('products_price', (!empty($sInfo->products_price) ? $sInfo->products_price : '')); ?>
          <?php echo zen_draw_hidden_field('update_products_id', $sInfo->products_id); ?>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_SPECIALS_SPECIAL_PRICE, 'specials_price', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('specials_price', (!empty($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : ''), 'class="form-control" id="specials_price"', true); ?>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_SPECIALS_AVAILABLE_DATE, 'specials_date_available', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_specials_date_available">
                <span class="input-group-addon datepicker_icon">
                  <i class="fa fa-calendar fa-lg"></i>
                </span>
                <?php echo zen_draw_input_field('specials_date_available', (($sInfo->specials_date_available == '0001-01-01') ? '' : $sInfo->specials_date_available), 'class="form-control" id="specials_date_available"'); ?>
              </div>
              <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_SPECIALS_EXPIRES_DATE, 'expires_date', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_expires_date">
                <span class="input-group-addon datepicker_icon">
                  <i class="fa fa-calendar fa-lg"></i>
                </span>
                <?php echo zen_draw_input_field('expires_date', (($sInfo->expires_date == '0001-01-01') ? '' : $sInfo->expires_date), 'class="form-control" id="expires_date"'); ?>
              </div>
              <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
            </div>
          </div>
          <?php
          if (isset($_GET['go_back']) && $_GET['go_back'] === 'ON') { // 'go_back' set in Products Price Manager
            $cancel_link = zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['add_products_id'] . '&current_category_id=' . $_GET['current_category_id']);
          } else {
            $cancel_link = zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (!empty($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (!empty($_GET['sID']) ? 'sID=' . $_GET['sID'] : ''));
          }
          ?>
          <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-primary"><?php echo(($form_action === 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button> <a class="btn btn-default" role="button" href="<?php echo $cancel_link; ?>"><?php echo IMAGE_CANCEL; ?></a>
          </div>
            <?php echo '</form>'; ?>
            <hr/>
            <?php
                   echo TEXT_SPECIALS_PRICE_NOTES_HEAD;
                   echo '<ul>'; 
                   echo TEXT_SPECIALS_PRICE_NOTES_BODY; 
                   echo '<li>' . TEXT_INFO_PRE_ADD_INTRO . '</li>'; 
                   echo '</ul>'; 
            ?>
        </div>
      <?php } else { ?>
        <div class="row">
          <div class="col-sm-6">
            <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo TEXT_ADD_SPECIAL_SELECT; ?></a>
            <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=pre_add'); ?>" class="btn btn-primary" role="button" title="<?php echo TEXT_INFO_PRE_ADD_INTRO; ?>"><?php echo TEXT_ADD_SPECIAL_PID; ?></a>
          </div>
          <div class="col-sm-offset-2 col-sm-4">
            <?php echo zen_draw_form('search', FILENAME_SPECIALS, '', 'get', 'class="form-horizontal"'); ?>
            <?php $keywords = (isset($_GET['search']) && zen_not_null($_GET['search'])) ? zen_db_input(zen_db_prepare_input($_GET['search'])) : ''; ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_SEARCH_SPECIALS, 'search', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <?php echo zen_draw_input_field('search', '', 'class="form-control" id="search"'); ?>
              </div>
            </div>
            <?php
            echo zen_hide_session_id();
            if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
              $keywords = zen_db_prepare_input($_GET['search']);
              ?>
              <div class="form-group">
                <div class="col-sm-3">
                  <p class="control-label"><?php echo TEXT_INFO_SEARCH_DETAIL_FILTER; ?></p>
                </div>
                <div class="col-sm-9 text-right">
                  <?php echo zen_output_string_protected($keywords); ?>&nbsp;<a href="<?php echo zen_href_link(FILENAME_SPECIALS); ?>" class="btn btn-default" role="button"><?php echo IMAGE_RESET; ?></a>
                </div>
              </div>
            <?php } ?>
            <?php echo '</form>'; ?>
          </div>
        </div>
        <div class="row">
            <div><?php echo TEXT_STATUS_WARNING; ?></div>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
                <table class="table table-hover">
                    <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent text-right"><?php echo 'ID'; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STOCK; ?></th>
                        <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_AVAILABLE_DATE; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_EXPIRES_DATE; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
                        <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
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
                            'p.products_id',
                        ];
                        $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords));
                    }

                // order of display
                $order_by = " ORDER BY p.products_model"; //set sort order of table listing
                $specials_query_raw = "SELECT p.products_id, p.products_quantity, pd.products_name, p.products_model, p.products_price, p.products_priced_by_attribute,
                                              s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status, s.specials_date_available
                                       FROM " . TABLE_PRODUCTS . " p,
                                            " . TABLE_SPECIALS . " s,
                                            " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                       WHERE p.products_id = pd.products_id
                                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                       AND p.products_id = s.products_id
                                       " . $search . "
                                       " . $order_by;

                // Split Page
                // create split page control
                $specials_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
                $specials = $db->Execute($specials_query_raw);
                foreach ($specials as $special) {
                  if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ((int)$_GET['sID'] === (int)$special['specials_id']))) && !isset($sInfo)) {
                    $products = $db->Execute("SELECT products_image
                                              FROM " . TABLE_PRODUCTS . "
                                              WHERE products_id = " . (int)$special['products_id']);

                    $sInfo_array = array_merge($special, $products->fields);
                    $sInfo = new objectInfo($sInfo_array);
                  }

                  if (isset($sInfo) && is_object($sInfo) && ((int)$special['specials_id'] === (int)$sInfo->specials_id)) {
                    ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'sID=' . $sInfo->specials_id . '&action=edit'); ?>'">
                    <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'sID=' . $special['specials_id']); ?>'">
                      <?php
                    }

                    if ($special['products_priced_by_attribute'] === '1') {
                      $specials_current_price = zen_get_products_base_price($special['products_id']);
                    } else {
                      $specials_current_price = $special['products_price'];
                    }
                    $sale_price = zen_get_products_special_price($special['products_id'], false);
                    ?>
                    <td class="dataTableContent text-right"><?php echo $special['products_id']; ?></td>
                    <td class="dataTableContent"><?php echo $special['products_model']; ?></td>
                    <td class="dataTableContent"><?php echo zen_clean_html($special['products_name']); ?></td>
                    <td class="dataTableContent text-right">
                      <?php $oos_style = $specials->fields['products_quantity'] <= 0 ? ' style="color:red;font-weight:bold"' : ''; ?>
                      <span<?php echo $oos_style; ?>><?php echo $special['products_quantity']; ?></span></td>
                    <td class="dataTableContent text-right"><?php echo zen_get_products_display_price($special['products_id']); ?></td>
                    <td class="dataTableContent text-center"><?php echo(($special['specials_date_available'] !== '0001-01-01' && $special['specials_date_available'] !== '') ? zen_date_short($special['specials_date_available']) : TEXT_NONE); ?></td>
                    <td class="dataTableContent text-center"><?php echo(($special['expires_date'] !== '0001-01-01' && $special['expires_date'] !== '') ? zen_date_short($special['expires_date']) : TEXT_NONE); ?></td>
                    <td class="dataTableContent text-center">
                      <?php if (($special['specials_date_available'] !== '0001-01-01' && $special['specials_date_available'] !== '') || ($special['expires_date'] !== '0001-01-01' && $special['expires_date'] !== '')) { ?>
                        <button type="submit" class="btn btn-status" style="cursor: initial;">
                          <?php if ($special['status'] === '1') { ?>
                            <i class="fa fa-square fa-lg txt-status-on" title="<?php echo TEXT_SPECIAL_ACTIVE; ?>: <?php echo TEXT_SPECIAL_STATUS_BY_DATE; ?>"></i>
                          <?php } else { ?>
                            <i class="fa fa-square fa-lg txt-status-off" title="<?php echo TEXT_SPECIAL_INACTIVE; ?>: <?php echo TEXT_SPECIAL_STATUS_BY_DATE; ?>"></i>
                          <?php } ?>
                        </button>
                      <?php } else { ?>
                        <?php echo zen_draw_form('setflag_products_' . $special['products_id'], FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=setflag'); ?>
                        <?php if ($special['status'] === '1') { ?>
                          <button type="submit" class="btn btn-status">
                            <i class="fa fa-square fa-lg txt-status-on" title="<?php echo TEXT_SPECIAL_ACTIVE; ?>"></i>
                          </button>
                          <?php echo zen_draw_hidden_field('flag', '0'); ?>
                        <?php } else { ?>
                          <button type="submit" class="btn btn-status">
                            <i class="fa fa-square fa-lg txt-status-off" title="<?php echo TEXT_SPECIAL_INACTIVE; ?>"></i>
                          </button>
                          <?php echo zen_draw_hidden_field('flag', '1'); ?>
                        <?php } ?>
                        <?php echo zen_draw_hidden_field('id', $special['products_id']); ?>
                        <?php echo '</form>'; ?>
                      <?php } ?>
                    </td>
                    <td class="dataTableContent text-right">
                      <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=edit' . '&sID=' . $special['products_id']); ?>" title="<?php echo ICON_EDIT; ?>" role="button">
                        <div class="fa-stack fa-fw">
                          <i class="fa fa-circle fa-stack-2x txt-status-on"></i>
                          <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                        </div>
                      </a>
                      <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=delete' . '&sID=' . $special['products_id']); ?>" title="<?php echo ICON_DELETE; ?>" role="button">
                        <div class="fa-stack fa-fw">
                          <i class="fa fa-circle fa-stack-2x txt-status-off"></i>
                          <i class="fa fa-trash fa-stack-1x fa-inverse"></i>
                        </div>
                      </a>
                      <?php if (isset($sInfo) && is_object($sInfo) && ($special['specials_id'] === $sInfo->specials_id)) { ?>
                        <i class="fa fa-caret-right fa-2x fa-fw" style="color:navy; vertical-align: middle;"></i>
                      <?php } else { ?>
                        <a href="<?php echo zen_href_link(FILENAME_SPECIALS, zen_get_all_get_params(['sID']) . 'sID=' . $special['specials_id']); ?>" title="<?php echo IMAGE_ICON_INFO; ?>" role="button">
                          <i class="fa fa-info-circle fa-2x fa-fw" style="color:#000; vertical-align: middle;"></i>
                        </a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <div class="row">
              <div class="col-sm-6"><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></div>
              <div class="col-sm-6 text-right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(['page', 'sID'])); ?></div>
            </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
                <?php
                $heading = [];
                $contents = [];

                switch ($action) {
                    case 'delete':
                        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</h4>'];
                        $contents = ['form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=deleteconfirm' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . zen_draw_hidden_field('sID', $sInfo->specials_id)];
                        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
                        $contents[] = ['text' => '<b>' . $sInfo->products_model . ' - "' . zen_clean_html($sInfo->products_name) . '"</b>'];
                        $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_SPECIALS, 'sID=' . $sInfo->specials_id . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;

                    case 'pre_add':
                        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_PRE_ADD_SPECIALS . '</h4>'];
                        $contents = ['form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=pre_add_confirmation' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal"')];
                        $contents[] = ['text' => TEXT_INFO_PRE_ADD_INTRO];
                        $result = $db->Execute("SELECT MAX(products_id) AS lastproductid FROM " . TABLE_PRODUCTS);
                        $max_product_id = $result->fields['lastproductid'];
                        $contents[] = ['text' => zen_draw_label(TEXT_PRE_ADD_PRODUCTS_ID, 'pre_add_products_id', 'class="control-label"') . zen_draw_input_field('pre_add_products_id', '', zen_set_field_length(TABLE_SPECIALS, 'products_id') . ' class="form-control" id="pre_add_products_id" required max="' . $max_product_id . '"', '', 'number')];
                        $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_CONFIRM . '</button> <a href="' . zen_href_link(FILENAME_SPECIALS, (!empty($sInfo->specials_id) ? '&sID=' . $sInfo->specials_id : '') . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;

                    default:
                        if (isset($sInfo) && is_object($sInfo)) {
                            $heading[] = ['text' => '<h4>ID#' . $sInfo->products_id . ': ' . $sInfo->products_model . ' - "' . zen_clean_html($sInfo->products_name) . '"</h4>'];
                            if ($sInfo->products_priced_by_attribute === '1') {
                                $specials_current_price = zen_get_products_base_price($sInfo->products_id);
                            } else {
                                $specials_current_price = $sInfo->products_price;
                            }
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '
                                <a href="' . zen_href_link(FILENAME_SPECIALS, 'sID=' . $sInfo->specials_id . '&action=edit' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>
                                <a href="' . zen_href_link(FILENAME_SPECIALS, 'sID=' . $sInfo->specials_id . '&action=delete' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-warning" role="button">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</a>'
                            ];
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_filter=' . $sInfo->products_id) . '" class="btn btn-primary" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a>'
                            ];
                            $contents[] = ['text' => TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($specials_current_price)];
                            $contents[] = ['text' => TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price)];
                            $contents[] = ['text' => '<b>' . TEXT_INFO_DISPLAY_PRICE . '<br>' . zen_get_products_display_price($sInfo->products_id) . '</b>'];
                            $contents[] = ['text' => TEXT_SPECIALS_AVAILABLE_DATE . ' ' . (($sInfo->specials_date_available !== '0001-01-01' && $sInfo->specials_date_available !== '') ? zen_date_short($sInfo->specials_date_available) : TEXT_NONE)];
                            $contents[] = ['text' => TEXT_SPECIALS_EXPIRES_DATE . ' ' . (($sInfo->expires_date !== '0001-01-01' && $sInfo->expires_date !== '') ? zen_date_short($sInfo->expires_date) : TEXT_NONE)];
                            if ($sInfo->date_status_change !== null && $sInfo->date_status_change !== '0001-01-01 00:00:00') {
                                $contents[] = ['text' => TEXT_INFO_STATUS_CHANGED . ' ' . zen_date_short($sInfo->date_status_change)];
                            }
                            $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->specials_last_modified)];
                            $contents[] = ['text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->specials_date_added)];
                            $contents[] = ['align' => 'text-center', 'text' => zen_info_image($sInfo->products_image, htmlspecialchars($sInfo->products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_product_path($sInfo->products_id) . '&pID=' . $sInfo->products_id . '&product_type=' . zen_get_products_type($sInfo->products_id)) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_PRODUCT . '</a>'
                            ];
                        }
                        break;
                }
                if ((zen_not_null($heading)) && (zen_not_null($contents))) {
                    $box = new box();
                    echo $box->infoBox($heading, $contents);
                }
            ?>
          </div>
        </div>
      <?php } ?>
      <!-- body_text_eof //-->
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <!-- script for datepicker -->
    <script>
      $(function () {
        $('input[name="specials_date_available"]').datepicker();
        $('input[name="expires_date"]').datepicker();
      })
    </script>
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
