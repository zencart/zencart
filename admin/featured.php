<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 24 Modified in v2.1.0-alpha2 $
 * structurally identical to specials.php, modifications should be replicated
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$action = $_GET['action'] ?? '';
$currentPage = (int)($_GET['page'] ?? 0);

if (!empty($action)) {
  // -----
  // Set an indicator for init_special_funcs.php to perform auto-enable/expiration.
  //
  $_SESSION['expirationsNeedUpdate'] = true;

  switch ($action) {
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] === '1' || $_POST['flag'] === '0')) {
        zen_set_featured_status((int)$_POST['id'], (int)$_POST['flag']);
        zen_redirect(zen_href_link(FILENAME_FEATURED, zen_get_all_get_params(['action', 'fID']) . 'fID=' . $_POST['id'], 'NONSSL'));
      }
      break;
    case 'insert':
      if (empty($_POST['products_id'])) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
      } else {
        $products_id = (int)$_POST['products_id'];

        $error = false;
        $featured_date_available_raw = zen_db_prepare_input($_POST['featured_date_available']);
        if (empty($featured_date_available_raw)) {
            $featured_date_available = '';
        } else {
            if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($featured_date_available_raw)) {
                $local_fmt = zen_datepicker_format_fordate();
                $dt = DateTime::createFromFormat($local_fmt, $featured_date_available_raw);
                $featured_date_available_raw = 'null';
                if (!empty($dt)) {
                    $featured_date_available_raw = $dt->format('Y-m-d');
                }
            }
            if (zcDate::validateDate($featured_date_available_raw) === true) {
                $featured_date_available = $featured_date_available_raw;
            } else {
                $error = true;
                $messageStack->add(ERROR_INVALID_AVAILABLE_DATE, 'error');
            }
        }

        $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
        if (empty($expires_date_raw)) {
            $expires_date = '';
        } else {
            if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($expires_date_raw)) {
                $local_fmt = zen_datepicker_format_fordate();
                $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
                $expires_date_raw = 'null';
                if (!empty($dt)) {
                    $expires_date_raw = $dt->format('Y-m-d');
                }
            }
            if (zcDate::validateDate($expires_date_raw) === true) {
                $expires_date = $expires_date_raw;
            } else {
                $error = true;
                $messageStack->add(ERROR_INVALID_EXPIRES_DATE, 'error');
            }
        }

        if ($error === true) {
            $action = 'new';
            break;
        }

        $db->Execute("INSERT INTO " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, featured_date_available)
                      VALUES (" . (int)$products_id . ", now(), '" . zen_db_input(!empty($expires_date) ? $expires_date : '0001-01-01') . "', 1, '" . zen_db_input(!empty($featured_date_available) ? $featured_date_available : '0001-01-01') . "')");

        $new_featured = $db->Execute("SELECT featured_id
                                      FROM " . TABLE_FEATURED . "
                                      WHERE products_id = " . (int)$products_id);
      } // nothing selected
      if (isset($_GET['go_back']) && $_GET['go_back'] === 'ON') {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_id . '&current_category_id=' . $_GET['current_category_id']));
      } else {
        zen_redirect(zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] . '&' : '') . (isset($new_featured) ? 'fID=' . $new_featured->fields['featured_id'] : '')));
      }
      break;
    case 'update':
        $featured_id = (int)$_POST['featured_id'];
        $error = false;

        $featured_date_available_raw = zen_db_prepare_input($_POST['featured_date_available']);
        if (empty($featured_date_available_raw)) {
            $featured_date_available = '';
        } else {
            if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($featured_date_available_raw)) {
                $local_fmt = zen_datepicker_format_fordate();
                $dt = DateTime::createFromFormat($local_fmt, $featured_date_available_raw);
                $featured_date_available_raw = 'null';
                if (!empty($dt)) {
                    $featured_date_available_raw = $dt->format('Y-m-d');
                }
            }
            if (zcDate::validateDate($featured_date_available_raw) === true) {
                $featured_date_available = $featured_date_available_raw;
            } else {
                $error = true;
                $messageStack->add(ERROR_INVALID_AVAILABLE_DATE, 'error');
            }
        }

        $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
        if (empty($expires_date_raw)) {
            $expires_date = '';
        } else {
            if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($expires_date_raw)) {
                $local_fmt = zen_datepicker_format_fordate();
                $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
                $expires_date_raw = 'null';
                if (!empty($dt)) {
                    $expires_date_raw = $dt->format('Y-m-d');
                }
            }
            if (zcDate::validateDate($expires_date_raw) === true) {
                $expires_date = $expires_date_raw;
            } else {
                $error = true;
                $messageStack->add(ERROR_INVALID_EXPIRES_DATE, 'error');
            }
        }

        if ($error === true) {
            $action = 'edit';
            break;
        }

        $db->Execute("UPDATE " . TABLE_FEATURED . "
                    SET featured_last_modified = now(),
                        expires_date = '" . zen_db_input(!empty($expires_date) ? $expires_date : '0001-01-01') . "',
                        featured_date_available = '" . zen_db_input(!empty($featured_date_available) ? $featured_date_available : '0001-01-01') . "'
                    WHERE featured_id = " . (int)$featured_id);

        zen_redirect(zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'fID=' . (int)$featured_id));
        break;
    case 'deleteconfirm':
      $featured_id = (int)$_POST['fID'];

      $db->Execute("DELETE FROM " . TABLE_FEATURED . "
                    WHERE featured_id = " . (int)$featured_id);

      zen_redirect(zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] : '')));
      break;
    case 'pre_add_confirmation':
      $skip_featured = false;
      // check for no PID entered
      if (empty($_POST['pre_add_products_id'])) {
        $skip_featured = true;
        $messageStack->add_session(WARNING_FEATURED_PRE_ADD_PID_EMPTY, 'caution');
      } else {
        $sql = "SELECT products_id, products_model
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_featured = $db->Execute($sql);
        if ($check_featured->RecordCount() < 1) {// check for valid PID
          $skip_featured = true;
          $messageStack->add_session(sprintf(WARNING_FEATURED_PRE_ADD_PID_NO_EXIST, (int)$_POST['pre_add_products_id']), 'caution');
        }
      }
      // check if Featured already exists
      if ($skip_featured === false) {
        $sql = "SELECT featured_id
                FROM " . TABLE_FEATURED . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_featured = $db->Execute($sql);
        if ($check_featured->RecordCount() > 0) {
          $skip_featured = true;
          $messageStack->add_session(sprintf(WARNING_FEATURED_PRE_ADD_PID_DUPLICATE, (int)$_POST['pre_add_products_id']), 'caution');
        }
      }
      if ($skip_featured === true) {
        zen_redirect(zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (!empty($check_featured->fields['featured_id']) ? 'fID=' . (int)$check_featured->fields['featured_id'] : '' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))));
      } else { // product id is valid
        zen_redirect(zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=new' . '&preID=' . (int)$_POST['pre_add_products_id']));
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
        if (($action === 'edit') && isset($_GET['fID'])) {//update existing Featured
          $form_action = 'update';

          $product = $db->Execute("SELECT p.products_id, p.products_model, pd.products_name, p.products_price, p.products_priced_by_attribute,
                                          DATE_FORMAT(f.expires_date, '" .  zen_datepicker_format_forsql() . "') AS expires_date,
                                          DATE_FORMAT(f.featured_date_available, '" .  zen_datepicker_format_forsql() . "') AS featured_date_available
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                        " . TABLE_FEATURED . " f
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = f.products_id
                                   AND f.featured_id = " . (int)$_GET['fID']);

          $fInfo = new objectInfo($product->fields);
          
          if ($fInfo->featured_date_available === '0001-01-01' || $fInfo->featured_date_available === '01-01-0001') {
              $fInfo->featured_date_available = '';
          }

          if ($fInfo->expires_date === '0001-01-01' || $fInfo->expires_date === '01-01-0001') {
              $fInfo->expires_date = '';
          }

          if ($fInfo->products_priced_by_attribute === '1') {
            $fInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } elseif (($action === 'new') && isset($_GET['preID'])) { //update existing Featured
          $form_action = 'insert';

          $product = $db->Execute("SELECT p.products_id, p.products_model, pd.products_name, p.products_price, p.products_priced_by_attribute
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = " . (int)$_GET['preID']);

          $fInfo = new objectInfo($product->fields);

          if ($fInfo->products_priced_by_attribute === '1') {
            $fInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } elseif (empty($_GET['preID'])) { // insert by product select dropdown
          $fInfo = new objectInfo([]);

// create an array of featured products, which will be excluded from the pull down menu of products
// (when creating a new featured product)
          $featured_array = [];
          $featureds = $db->Execute("SELECT p.products_id, p.products_model
                                     FROM " . TABLE_PRODUCTS . " p,
                                          " . TABLE_FEATURED . " f
                                     WHERE f.products_id = p.products_id");

          foreach ($featureds as $featured) {
            $featured_array[] = $featured['products_id'];
          }

// Uncomment the following to not include things that cannot go in the cart
//          $not_for_cart = $db->Execute("SELECT p.products_id
//                                        FROM " . TABLE_PRODUCTS . " p
//                                        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
//                                        WHERE pt.allow_add_to_cart = 'N'");
//          foreach ($not_for_cart as $item) {
//            $featured_array[] = $item['products_id'];
//          }
        }

          if ($action === 'new' && !isset($_GET['preID'])) {
              $form = addSearchKeywordForm(FILENAME_FEATURED, $action);
              echo $form;
          }
          ?>
        <div class="row">
          <?php echo zen_draw_form('new_featured', FILENAME_FEATURED, zen_get_all_get_params(['action', 'info', 'fID']) . 'action=' . $form_action . (!empty($_GET['go_back']) ? '&go_back=' . $_GET['go_back'] : ''), 'post', 'class="form-horizontal"'); ?>
          <?php
          if ($form_action === 'update') {
            echo zen_draw_hidden_field('featured_id', $_GET['fID']);
          }
          if (!empty($_GET['preID'])) { // new Special: insert by product ID
            echo zen_draw_hidden_field('products_id', $_GET['preID']);
          }
          ?>
          <?php if (isset($fInfo->products_name)) { // Featured is already defined/this is an update ?>
            <div class="form-group">
              <p class="col-sm-3 control-label"><?php echo TEXT_FEATURED_PRODUCT; ?></p>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo 'ID#' . $fInfo->products_id . ': ' . $fInfo->products_model . ' - "' . zen_clean_html($fInfo->products_name) . '" (' . $currencies->format($fInfo->products_price) . ')'; ?></span>
              </div>
            </div>
            <?php
          } elseif (!empty($_GET['preID'])) { // new Featured: insert by product ID
            $preID = (int)$_GET['preID'];
            ?>
            <div class="form-group">
              <p class="col-sm-3 control-label"><?php echo TEXT_FEATURED_PRODUCT; ?></p>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo 'ID#' . $preID . ': ' . zen_get_products_model($preID) . ' - "' . zen_clean_html(zen_get_products_name($preID)) . '" (' . $currencies->format(zen_get_products_base_price($preID)) . ')'; ?></span>
              </div>
            </div>
          <?php } else { ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_FEATURED_PRODUCT, 'products_id', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_pulldown_products('products_id', 'required size="15" class="form-control" id="products_id"', $featured_array, true, (!empty($_GET['add_products_id']) ? $_GET['add_products_id'] : ''), true); ?>
              </div>
            </div>
          <?php } ?>
          <?php echo zen_draw_hidden_field('update_products_id', $fInfo->products_id); ?>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_FEATURED_AVAILABLE_DATE, 'featured_date_available', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_featured_date_available">
                <span class="input-group-addon datepicker_icon">
                  <?php echo zen_icon('calendar-days', size: 'lg') ?>
                </span>
                <?php echo zen_draw_input_field('featured_date_available', $fInfo->featured_date_available, 'class="form-control" id="featured_date_available"'); ?>
              </div>
              <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>) <span class="date-check-error"><?php echo ERROR_INVALID_ACTIVE_DATE; ?></span></span>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_FEATURED_EXPIRES_DATE, 'expires_date', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_expires_date">
                <span class="input-group-addon datepicker_icon">
                  <?php echo zen_icon('calendar-days', size: 'lg') ?>
                </span>
                <?php echo zen_draw_input_field('expires_date', $fInfo->expires_date, 'class="form-control" id="expires_date"'); ?>
              </div>
              <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>) <span class="date-check-error"><?php echo ERROR_INVALID_EXPIRES_DATE; ?></span></span>
            </div>
          </div>
          <?php
          if (isset($_GET['go_back']) && $_GET['go_back'] === 'ON') { // 'go_back' set in Products Price Manager
            $cancel_link = zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['add_products_id'] . '&current_category_id=' . $_GET['current_category_id']);
          } else {
            $cancel_link = zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (!empty($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (!empty($_GET['fID']) ? 'fID=' . $_GET['fID'] : ''));
          }
          ?>
          <?php require DIR_WS_INCLUDES . 'javascript/dateChecker.php'; ?>
          <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-primary"><?php echo(($form_action === 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button> <a class="btn btn-default" role="button" href="<?php echo $cancel_link; ?>"><?php echo IMAGE_CANCEL; ?></a>
          </div>
          <?php echo '</form>'; ?>
        </div>
      <?php } else { ?>
        <div class="row">
          <div class="col-sm-8">
            <a href="<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo TEXT_ADD_FEATURED_SELECT; ?></a>
            <a href="<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=pre_add'); ?>" class="btn btn-primary" role="button" title="<?php echo TEXT_INFO_PRE_ADD_INTRO; ?>"><?php echo TEXT_ADD_FEATURED_PID; ?></a>
          </div>
          <div class="col-sm-4">
          <?php require DIR_WS_MODULES . 'search_box.php'; ?>
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
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STOCK; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTIVE_FROM; ?></th>
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
                $featured_query_raw = "SELECT p.products_id, p.products_quantity, pd.products_name, p.products_model, p.products_price, p.products_priced_by_attribute,
                                              f.featured_id, f.featured_date_added, f.featured_last_modified, f.expires_date, f.date_status_change, f.status, f.featured_date_available
                                       FROM " . TABLE_PRODUCTS . " p,
                                            " . TABLE_FEATURED . " f,
                                            " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                       WHERE p.products_id = pd.products_id
                                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                       AND p.products_id = f.products_id
                                       " . $search . "
                                       " . $order_by;

                // Split Page
                // reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['fID'])) {
                    $check_page = $db->Execute($featured_query_raw);
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) {
                        $check_count = 0;
                        foreach ($check_page as $item) {
                            if ((int)$item['featured_id'] === (int)$_GET['fID']) {
                                break;
                            }
                            $check_count++;
                        }
                        $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) !== 0 ? .5 : 0)));
                        $page = $_GET['page'];
                    } else {
                        $_GET['page'] = 1;
                    }
                }

                // create split page control
                $featured_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $featured_query_raw, $featured_query_numrows);
                $featureds = $db->Execute($featured_query_raw);
                foreach ($featureds as $featured) {
                  if ((!isset($_GET['fID']) || (isset($_GET['fID']) && ((int)$_GET['fID'] === (int)$featured['featured_id']))) && !isset($fInfo)) {
                    $products = $db->Execute("SELECT products_image
                                              FROM " . TABLE_PRODUCTS . "
                                              WHERE products_id = " . (int)$featured['products_id']);

                    $fInfo_array = array_merge($featured, $products->fields);
                    $fInfo = new objectInfo($fInfo_array);
                  }

                  if (isset($fInfo) && is_object($fInfo) && ((int)$featured['featured_id'] === (int)$fInfo->featured_id)) {
                    ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'fID=' . $fInfo->featured_id . '&action=edit'); ?>'">
                    <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'fID=' . $featured['featured_id']); ?>'">
                      <?php
                    }

                    if ($featured['products_priced_by_attribute'] === '1') {
                      $featured_current_price = zen_get_products_base_price($featured['products_id']);
                    } else {
                      $specials_current_price = $featured['products_price'];
                    }
                    $sale_price = zen_get_products_special_price($featured['products_id'], false);
                    ?>
                    <td class="dataTableContent text-right"><?php echo $featured['products_id']; ?></td>
                    <td class="dataTableContent"><?php echo $featured['products_model']; ?></td>
                    <td class="dataTableContent"><?php echo zen_clean_html($featured['products_name']); ?></td>
                    <td class="dataTableContent text-right">
                      <span<?php echo ($featured['products_quantity'] <= 0 ? ' class="txt-red font-weight-bold"' : ''); ?>><?php echo $featured['products_quantity']; ?></span>
                    </td>
                    <td class="dataTableContent text-right"><?php echo zen_get_products_display_price($featured['products_id']); ?></td>
                    <td class="dataTableContent text-center"><?php echo(($featured['featured_date_available'] !== '0001-01-01' && $featured['featured_date_available'] !== '') ? zen_date_short($featured['featured_date_available']) : TEXT_NONE); ?></td>
                    <td class="dataTableContent text-center"><?php echo(($featured['expires_date'] !== '0001-01-01' && $featured['expires_date'] !== '') ? zen_date_short($featured['expires_date']) : TEXT_NONE); ?></td>
                    <td class="dataTableContent text-center">
                      <?php if (($featured['featured_date_available'] !== '0001-01-01' && $featured['featured_date_available'] !== '') || ($featured['expires_date'] !== '0001-01-01' && $featured['expires_date'] !== '')) { ?>
                        <button type="submit" class="btn btn-status" style="cursor: initial;">
                          <?php if ($featured['status'] === '1') { ?>
                            <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo TEXT_FEATURED_ACTIVE; ?>: <?php echo TEXT_FEATURED_STATUS_BY_DATE; ?>"></i>
                          <?php } else { ?>
                            <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo TEXT_FEATURED_INACTIVE; ?>: <?php echo TEXT_FEATURED_STATUS_BY_DATE; ?>"></i>
                          <?php } ?>
                        </button>
                      <?php } else { ?>
                        <?php echo zen_draw_form('setflag_products_' . $featured['products_id'], FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=setflag'); ?>
                        <?php if ($featured['status'] === '1') { ?>
                          <button type="submit" class="btn btn-status">
                            <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo TEXT_FEATURED_ACTIVE; ?>"></i>
                          </button>
                          <?php echo zen_draw_hidden_field('flag', '0'); ?>
                        <?php } else { ?>
                          <button type="submit" class="btn btn-status">
                            <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo TEXT_FEATURED_INACTIVE; ?>"></i>
                          </button>
                          <?php echo zen_draw_hidden_field('flag', '1'); ?>
                        <?php } ?>
                        <?php echo zen_draw_hidden_field('id', $featured['featured_id']); ?>
                        <?php echo '</form>'; ?>
                      <?php } ?>
                    </td>
                    <td class="dataTableContent text-right actions">
                      <div class="btn-group">
                      <a href="<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=edit' . '&fID=' . $featured['featured_id']); ?>" class="btn btn-sm btn-default btn-edit" role="button">
                        <?php echo zen_icon('pencil', ICON_EDIT) ?>
                      </a>
                      <a href="<?php echo zen_href_link(FILENAME_FEATURED, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . 'action=delete' . '&fID=' . $featured['featured_id']); ?>" class="btn btn-sm btn-default btn-delete" role="button">
                        <?php echo zen_icon('trash', ICON_DELETE) ?>
                      </a>
                      </div>
                      <?php if (isset($fInfo) && is_object($fInfo) && ($featured['featured_id'] === $fInfo->featured_id)) {
                        echo zen_icon('caret-right', '', '2x', true);
                      } else { ?>
                        <a href="<?php echo zen_href_link(FILENAME_FEATURED, zen_get_all_get_params(['fID']) . 'fID=' . $featured['featured_id']); ?>" role="button">
                          <?php echo zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, true) ?>
                        </a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <div class="row">
              <div class="col-sm-6"><?php echo $featured_split->display_count($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_FEATURED); ?></div>
              <div class="col-sm-6 text-right"><?php echo $featured_split->display_links($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(['page', 'fID'])); ?></div>
            </div>
          </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
                <?php
                $heading = [];
                $contents = [];

                switch ($action) {
                    case 'delete':
                        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_FEATURED . '</h4>'];
                        $contents = ['form' => zen_draw_form('featured', FILENAME_FEATURED, 'action=deleteconfirm' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . zen_draw_hidden_field('fID', $fInfo->featured_id)];
                        $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
                        $contents[] = ['text' => '<b>' . $fInfo->products_model . ' - "' . zen_clean_html($fInfo->products_name) . '"</b>'];
                        $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_FEATURED, 'fID=' . $fInfo->featured_id . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;

                    case 'pre_add':
                        $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_PRE_ADD_FEATURED . '</h4>'];
                        $contents = ['form' => zen_draw_form('featured', FILENAME_FEATURED, 'action=pre_add_confirmation' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal"')];
                        $contents[] = ['text' => TEXT_INFO_PRE_ADD_INTRO];
                        $result = $db->Execute("SELECT MAX(products_id) AS lastproductid FROM " . TABLE_PRODUCTS);
                        $max_product_id = $result->fields['lastproductid'];
                        $contents[] = ['text' => zen_draw_label(TEXT_PRE_ADD_PRODUCTS_ID, 'pre_add_products_id', 'class="control-label"') . zen_draw_input_field('pre_add_products_id', '', zen_set_field_length(TABLE_FEATURED, 'products_id') . ' class="form-control" id="pre_add_products_id" required max="' . $max_product_id . '"', '', 'number')];
                        $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_CONFIRM . '</button> <a href="' . zen_href_link(FILENAME_FEATURED, (!empty($fInfo->featured_id) ? '&fID=' . $fInfo->featured_id : '') . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;

                    default:
                        if (isset($fInfo) && is_object($fInfo)) {
                            $heading[] = ['text' => '<h4>ID#' . $fInfo->products_id . ': ' . $fInfo->products_model . ' - "' . zen_clean_html($fInfo->products_name) . '"</h4>'];
                            if ($fInfo->products_priced_by_attribute === '1') {
                                $featured_current_price = zen_get_products_base_price($fInfo->products_id);
                            } else {
                                $featured_current_price = $fInfo->products_price;
                            }
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '
                                    <a href="' . zen_href_link(FILENAME_FEATURED, '&fID=' . $fInfo->featured_id . '&action=edit' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>
                                    <a href="' . zen_href_link(FILENAME_FEATURED, '&fID=' . $fInfo->featured_id . '&action=delete' . ($currentPage != 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-warning" role="button">' . TEXT_INFO_HEADING_DELETE_FEATURED . '</a>'
                            ];
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_filter=' . $fInfo->products_id) . '" class="btn btn-primary" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a>'
                            ];
                            $contents[] = ['text' => TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($featured_current_price)];
                            $contents[] = ['text' => TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($fInfo->featured_new_products_price)];
                            $contents[] = ['text' => '<b>' . TEXT_INFO_DISPLAY_PRICE . '<br>' . zen_get_products_display_price($fInfo->products_id) . '</b>'];
                            $contents[] = ['text' => TEXT_FEATURED_AVAILABLE_DATE . ' ' . (($fInfo->featured_date_available !== '0001-01-01' && $fInfo->featured_date_available !== '') ? zen_date_short($fInfo->featured_date_available) : TEXT_NONE)];
                            $contents[] = ['text' => TEXT_FEATURED_EXPIRES_DATE . ' ' . (($fInfo->expires_date !== '0001-01-01' && $fInfo->expires_date !== '') ? zen_date_short($fInfo->expires_date) : TEXT_NONE)];
                            if ($fInfo->date_status_change !== null && $fInfo->date_status_change !== '0001-01-01 00:00:00') {
                                $contents[] = ['text' => TEXT_INFO_STATUS_CHANGED . ' ' . zen_date_short($fInfo->date_status_change)];
                            }
                            $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($fInfo->featured_last_modified)];
                            $contents[] = ['text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($fInfo->featured_date_added)];
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => zen_info_image($fInfo->products_image, htmlspecialchars($fInfo->products_name, ENT_COMPAT, CHARSET, true), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                            ];
                            $contents[] = [
                                'align' => 'text-center',
                                'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_product_path($fInfo->products_id) . '&pID=' . $fInfo->products_id . '&product_type=' . zen_get_products_type($fInfo->products_id)) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_PRODUCT . '</a>'
                            ];
                        }
                        break;
                }
                if (!empty($heading) && !empty($contents)) {
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
        $('input[name="featured_date_available"]').datepicker({
            minDate: 0
        });
        $('input[name="expires_date"]').datepicker({
            minDate: 1
        });
      })
    </script>
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
