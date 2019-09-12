<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Feb 14 Modified in v1.5.6b $
 */
require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
        zen_set_specials_status($_GET['id'], $_POST['flag']);
        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("SELECT products_id
                                      FROM " . TABLE_SPECIALS . "
                                      WHERE specials_id = " . (int)$_GET['id']);
        zen_update_products_price_sorter($update_price->fields['products_id']);
        zen_redirect(zen_href_link(FILENAME_SPECIALS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $_GET['id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'NONSSL'));
      }
      break;
    case 'insert':
      if (empty($_POST['products_id'])) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
      } else {
        $products_id = zen_db_prepare_input($_POST['products_id']);
        $products_price = zen_db_prepare_input($_POST['products_price']);

        $tmp_value = zen_db_prepare_input($_POST['specials_price']);
        $specials_price = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;

        if (substr($specials_price, -1) == '%') {
          $new_special_insert = $db->Execute("SELECT products_id, products_price, products_priced_by_attribute
                                              FROM " . TABLE_PRODUCTS . "
                                              WHERE products_id = " . (int)$products_id);

// check if priced by attribute
          if ($new_special_insert->fields['products_priced_by_attribute'] == '1') {
            $products_price = zen_get_products_base_price($products_id);
          } else {
            $products_price = $new_special_insert->fields['products_price'];
          }

          $specials_price = ((float)$products_price - (((float)$specials_price / 100) * (float)$products_price));
        }

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

        $products_id = zen_db_prepare_input($_POST['products_id']);
        $db->Execute("INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                      VALUES ('" . (int)$products_id . "',
                              '" . zen_db_input($specials_price) . "',
                              now(),
                              '" . zen_db_input($expires_date) . "',
                              '1',
                              '" . zen_db_input($specials_date_available) . "')");

        $new_special = $db->Execute("SELECT specials_id
                                     FROM " . TABLE_SPECIALS . "
                                     WHERE products_id = " . (int)$products_id);

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter((int)$products_id);
      } // nothing selected
      if ($_GET['go_back'] == 'ON') {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_id . '&current_category_id=' . $_GET['current_category_id']));
      } else {
        zen_redirect(zen_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $new_special->fields['specials_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      }
      break;
    case 'update':
      $specials_id = zen_db_prepare_input($_POST['specials_id']);

      if ($_POST['products_priced_by_attribute'] == '1') {
        $products_price = zen_get_products_base_price($_POST['update_products_id']);
      } else {
        $products_price = zen_db_prepare_input($_POST['products_price']);
      }

      $tmp_value = zen_db_prepare_input($_POST['specials_price']);
      $specials_price = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;

      if (substr($specials_price, -1) == '%') {
        $specials_price = ($products_price - (($specials_price / 100) * $products_price));
      }

      $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
      $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

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

      zen_redirect(zen_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $specials_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      break;
    case 'deleteconfirm':
      $specials_id = zen_db_prepare_input($_POST['sID']);

      // reset products_price_sorter for searches etc.
      $update_price = $db->Execute("SELECT products_id
                                    FROM " . TABLE_SPECIALS . "
                                    WHERE specials_id = " . (int)$specials_id);
      $update_price_id = $update_price->fields['products_id'];

      $db->Execute("DELETE FROM " . TABLE_SPECIALS . "
                    WHERE specials_id = " . (int)$specials_id);

      zen_update_products_price_sorter($update_price_id);

      zen_redirect(zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      break;
    case 'pre_add_confirmation':
      // check for blank or existing special
      $skip_special = false;
      if (empty($_POST['pre_add_products_id'])) {
        $skip_special = true;
        $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_EMPTY, 'caution');
      }

      if ($skip_special == false) {
        $sql = "SELECT products_id, products_model
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_special = $db->Execute($sql);
        if ((!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL == 'false') && ($check_special->RecordCount() < 1 || substr($check_special->fields['products_model'], 0, 4) == 'GIFT')) {
          $skip_special = true;
          $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_BAD_PRODUCTS_ID, 'caution');
        }
      }

      if ($skip_special == false) {
        $sql = "SELECT specials_id
                FROM " . TABLE_SPECIALS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_special = $db->Execute($sql);
        if ($check_special->RecordCount() > 0) {
          $skip_special = true;
          $messageStack->add_session(WARNING_SPECIALS_PRE_ADD_DUPLICATE, 'caution');
        }
      }

      if ($skip_special == true) {
        zen_redirect(zen_href_link(FILENAME_SPECIALS, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . ($check_special->fields['specials_id'] > 0 ? 'sID=' . $check_special->fields['specials_id'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      }
      // add empty special

      $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
      $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

      $products_id = zen_db_prepare_input($_POST['pre_add_products_id']);
      $db->Execute("INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_date_added, expires_date, status, specials_date_available)
                    VALUES ('" . (int)$products_id . "',
                            now(),
                            '" . zen_db_input($expires_date) . "',
                            '1',
                            '" . zen_db_input($specials_date_available) . "')");

      $new_special = $db->Execute("SELECT specials_id
                                   FROM " . TABLE_SPECIALS . "
                                   WHERE products_id = " . (int)$products_id);

      $messageStack->add_session(SUCCESS_SPECIALS_PRE_ADD, 'success');
      zen_redirect(zen_href_link(FILENAME_SPECIALS, 'action=edit' . '&sID=' . $new_special->fields['specials_id'] . '&manual=1'));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <?php
    if (($action == 'new') || ($action == 'edit')) {
      ?>
      <link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
      <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
      <?php
    }
    ?>
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
    <div id="spiffycalendar" class="text"></div>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <div class="row text-right">
          <?php echo zen_draw_form('search', FILENAME_SPECIALS, '', 'get'); ?>
          <?php
// show reset search
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            echo '<a href="' . zen_href_link(FILENAME_SPECIALS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
          }
          echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
            echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
          }
          ?>
          <?php echo '</form>'; ?>
      </div>
      <div class="row"><?php echo TEXT_STATUS_WARNING; ?></div>
      <?php
      if (empty($action)) {
        ?>
        <div class="row text-center">
          <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_PRODUCT; ?></a>
        </div>
        <?php
      }
      ?>
      <?php
      if (($action == 'new') || ($action == 'edit')) {
        $form_action = 'insert';
        if (($action == 'edit') && isset($_GET['sID'])) {
          $form_action = 'update';

          $product = $db->Execute("SELECT p.products_id, pd.products_name, p.products_price, p.products_priced_by_attribute,
                                          s.specials_new_products_price, s.expires_date, s.specials_date_available
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                        " . TABLE_SPECIALS . " s
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = s.products_id
                                   AND s.specials_id = " . (int)$_GET['sID']);

          $sInfo = new objectInfo($product->fields);

          if ($sInfo->products_priced_by_attribute == '1') {
            $sInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } else {
          $sInfo = new objectInfo(array());

// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
          $specials_array = array();
          $specials = $db->Execute("SELECT p.products_id, p.products_model
                                    FROM " . TABLE_PRODUCTS . " p,
                                         " . TABLE_SPECIALS . " s
                                    WHERE s.products_id = p.products_id");

          foreach ($specials as $special) {
            $specials_array[] = $special['products_id'];
          }

// never include Gift Vouchers for specials when set to false
        if (!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL == 'false') {
          $gift_vouchers = $db->Execute("SELECT distinct p.products_id, p.products_model
                                         FROM " . TABLE_PRODUCTS . " p,
                                              " . TABLE_SPECIALS . " s
                                         WHERE p.products_model RLIKE '" . "GIFT" . "'");

          foreach ($gift_vouchers as $gift_voucher) {
            if (substr($gift_voucher['products_model'], 0, 4) == 'GIFT') {
              $specials_array[] = $gift_voucher['products_id'];
            }
          }
        }
// uncomment the following to not include things that cannot go in the cart
//          $not_for_cart = $db->Execute("SELECT p.products_id
//                                        FROM " . TABLE_PRODUCTS . " p
//                                        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
//                                        WHERE pt.allow_add_to_cart = 'N'");
//          foreach ($not_for_cart as $item) {
//            $specials_array[] = $item['products_id'];
//          }

        }
        ?>
        <script>
          var StartDate = new ctlSpiffyCalendarBox("StartDate", "new_special", "start", "btnDate1", "<?php echo (($sInfo->specials_date_available == '0001-01-01') ? '' : zen_date_short($sInfo->specials_date_available)); ?>", scBTNMODE_CUSTOMBLUE);
          var EndDate = new ctlSpiffyCalendarBox("EndDate", "new_special", "end", "btnDate2", "<?php echo (($sInfo->expires_date == '0001-01-01') ? '' : zen_date_short($sInfo->expires_date)); ?>", scBTNMODE_CUSTOMBLUE);
        </script>
        <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></div>
        <div class="row">
            <?php echo zen_draw_form('new_special', FILENAME_SPECIALS, zen_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action . (!empty($_GET['go_back']) ? '&go_back=' . $_GET['go_back'] : ''), 'post', 'onsubmit="return check_dates(start,StartDate.required, end, EndDate.required);" class="form-horizontal"'); ?>
            <?php
            if ($form_action == 'update') {
              echo zen_draw_hidden_field('specials_id', $_GET['sID']);
            }
            ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_SPECIALS_PRODUCT, 'products_id', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php
                echo (isset($sInfo->products_name)) ? $sInfo->products_name . ' (' . $currencies->format($sInfo->products_price) . ')' : zen_draw_products_pull_down('products_id', 'size="15" class="form-control"', $specials_array, true, (!empty($_GET['add_products_id']) ? $_GET['add_products_id'] : ''), true);
                echo zen_draw_hidden_field('products_price', (isset($sInfo->products_price) ? $sInfo->products_price : ''));
                ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_SPECIALS_SPECIAL_PRICE, 'specials_price', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php
                echo zen_draw_input_field('specials_price', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : ''), 'class="form-control"');
                echo zen_draw_hidden_field('products_priced_by_attribute', $sInfo->products_priced_by_attribute);
                echo zen_draw_hidden_field('update_products_id', $sInfo->products_id);
                ?>
            </div>
          </div>

          <div class="form-group">
              <?php echo zen_draw_label(TEXT_SPECIALS_AVAILABLE_DATE, 'start', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <script>StartDate.writeControl(); StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_SPECIALS_EXPIRES_DATE, 'end', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
              <script>EndDate.writeControl(); EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
            </div>
          </div>
          <table class="table">
            <tr>
              <td><?php echo TEXT_SPECIALS_PRICE_TIP; ?></td>
              <td class="text-right">
                <button type="submit" class="btn btn-primary"><?php echo (($form_action == 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button>
                <?php echo ((empty($_GET['manual']) || (int)$_GET['manual'] == 0) ? '&nbsp;<a href="' . (isset($_GET['go_back']) && $_GET['go_back'] == 'ON' ? zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['add_products_id'] . '&current_category_id=' . $_GET['current_category_id']) : zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . ((isset($_GET['sID']) && $_GET['sID'] != '') ? '&sID=' . $_GET['sID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>' : ''); ?>
              </td>
            </tr>
          </table>
          <?php echo '</form>'; ?>
          <?php
        } else {
          ?>
          <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <table class="table table-hover">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent text-right"><?php echo 'ID#'; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
                    <th colspan="2" class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></th>
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
                      $search = " and (pd.products_name like '%" . $keywords . "%'
                                  or pd.products_description like '%" . $keywords . "%'
                                  or p.products_model like '%" . $keywords . "%')";
                    }

// order of display
                    $order_by = " order by pd.products_name ";
                    $specials_query_raw = "select p.products_id, pd.products_name, p.products_model, p.products_price, p.products_priced_by_attribute,
                                                  s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status, s.specials_date_available
                                           from " . TABLE_PRODUCTS . " p,
                                                " . TABLE_SPECIALS . " s,
                                                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                           where p.products_id = pd.products_id
                                           and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                           and p.products_id = s.products_id" . $search . $order_by;

// Split Page
// reset page when page is unknown
                    if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['sID'])) {
                      $old_page = $_GET['page'];
                      $check_page = $db->Execute($specials_query_raw);
                      if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                        $check_count = 1;
                        foreach ($check_page as $item) {
                          if ($item['specials_id'] == $_GET['sID']) {
                            break;
                          }
                          $check_count++;
                        }
                        $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                        $page = $_GET['page'];
                        if ($old_page != $_GET['page']) {
// do nothing
                        }
                      } else {
                        $_GET['page'] = 1;
                      }
                    }

// create split page control
                    $specials_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
                    $specials = $db->Execute($specials_query_raw);
                    foreach ($specials as $special) {
                      if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $special['specials_id']))) && !isset($sInfo)) {
                        $products = $db->Execute("SELECT products_image
                                                  FROM " . TABLE_PRODUCTS . "
                                                  WHERE products_id = " . (int)$special['products_id']);

                        $sInfo_array = array_merge($special, $products->fields);
                        $sInfo = new objectInfo($sInfo_array);
                      }

                      if (isset($sInfo) && is_object($sInfo) && ($special['specials_id'] == $sInfo->specials_id)) {
                        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'" role="button">' . "\n";
                      } else {
                        echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $special['specials_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'" role="button">' . "\n";
                      }

                      if ($special['products_priced_by_attribute'] == '1') {
                        $specials_current_price = zen_get_products_base_price($special['products_id']);
                      } else {
                        $specials_current_price = $special['products_price'];
                      }

                      $sale_price = zen_get_products_special_price($special['products_id'], false);
                      ?>
                  <td  class="dataTableContent text-right"><?php echo $special['products_id']; ?>&nbsp;</td>
                  <td  class="dataTableContent"><?php echo $special['products_name']; ?></td>
                  <td  class="dataTableContent"><?php echo $special['products_model']; ?>&nbsp;</td>
                  <td colspan="2" class="dataTableContent text-right"><?php echo zen_get_products_display_price($special['products_id']); ?></td>
                  <td  class="dataTableContent text-center"><?php echo (($special['specials_date_available'] != '0001-01-01' && $special['specials_date_available'] != '') ? zen_date_short($special['specials_date_available']) : TEXT_NONE); ?></td>
                  <td  class="dataTableContent text-center"><?php echo (($special['expires_date'] != '0001-01-01' && $special['expires_date'] != '') ? zen_date_short($special['expires_date']) : TEXT_NONE); ?></td>
                  <td  class="dataTableContent text-center">
                      <?php
                      if ($special['status'] == '1') {
                        echo zen_draw_form('setflag_products', FILENAME_SPECIALS, 'action=setflag&id=' . $special['specials_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                        ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
                      <input type="hidden" name="flag" value="0" />
                      <?php echo '</form>'; ?>
                      <?php
                    } else {
                      echo zen_draw_form('setflag_products', FILENAME_SPECIALS, 'action=setflag&id=' . $special['specials_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                      ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
                      <input type="hidden" name="flag" value="1" />
                      <?php echo '</form>'; ?>
                      <?php
                    }
                    ?>
                  </td>
                  <td class="dataTableContent text-right">
                      <?php echo '<a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $special['specials_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                      <?php echo '<a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $special['specials_id'] . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                      <?php
                      if (isset($sInfo) && is_object($sInfo) && ($special['specials_id'] == $sInfo->specials_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_SPECIALS, zen_get_all_get_params(array('sID')) . 'sID=' . $special['specials_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                      }
                      ?>
                  </td>
                  </tr>
                  <?php
                }
                ?>
                </tbody>
              </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
                <?php
                $heading = array();
                $contents = array();

                switch ($action) {
                  case 'delete':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</h4>');

                    $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&action=deleteconfirm' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . zen_draw_hidden_field('sID', $sInfo->specials_id));
                    $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                    $contents[] = array('text' => '<br><b>' . $sInfo->products_name . '</b>');
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'pre_add':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_PRE_ADD_SPECIALS . '</h4>');
                    $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'action=pre_add_confirmation' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal"'));
                    $contents[] = array('text' => TEXT_INFO_PRE_ADD_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRE_ADD_PRODUCTS_ID, 'pre_add_products_id', 'class="control-label"') . zen_draw_input_field('pre_add_products_id', '', zen_set_field_length(TABLE_SPECIALS, 'products_id') . 'class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_CONFIRM . '</button> <a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  default:
                    if (isset($sInfo) && is_object($sInfo)) {
                      $heading[] = array('text' => '<h4>' . $sInfo->products_name . '</h4>');

                      if ($sInfo->products_priced_by_attribute == '1') {
                        $specials_current_price = zen_get_products_base_price($sInfo->products_id);
                      } else {
                        $specials_current_price = $sInfo->products_price;
                      }

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_filter=' . $sInfo->products_id) . '" class="btn btn-primary" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a>');
                      $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->specials_date_added));
                      $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->specials_last_modified));
                      $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
                      $contents[] = array('text' => '<br>' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($specials_current_price));
                      $contents[] = array('text' => TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price));
                      $contents[] = array('text' => TEXT_INFO_DISPLAY_PRICE . ' ' . zen_get_products_display_price($sInfo->products_id));

                      $contents[] = array('text' => '<br>' . TEXT_INFO_AVAILABLE_DATE . ' <b>' . (($sInfo->specials_date_available != '0001-01-01' and $sInfo->specials_date_available != '') ? zen_date_short($sInfo->specials_date_available) : TEXT_NONE) . '</b>');
                      $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . (($sInfo->expires_date != '0001-01-01' and $sInfo->expires_date != '') ? zen_date_short($sInfo->expires_date) : TEXT_NONE) . '</b>');
                      $contents[] = array('text' => TEXT_INFO_STATUS_CHANGE . ' ' . zen_date_short($sInfo->date_status_change));
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, '&action=new_product' . '&cPath=' . zen_get_product_path($sInfo->products_id, 'override') . '&pID=' . $sInfo->products_id . '&product_type=' . zen_get_products_type($sInfo->products_id)) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_PRODUCT . '</a>');

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_SPECIALS, 'action=pre_add' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_SELECT . '</a><br>' . TEXT_INFO_MANUAL);
                    } else {
                      $heading[] = array('text' => '<h4>' . TEXT_NONE . '</h4>');
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_SPECIALS, 'action=pre_add' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_SELECT . '</a><br>' . TEXT_INFO_MANUAL);
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
          <div class="row">
            <table class="table">
              <tr>
                <td><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
                <td class="text-right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'sID'))); ?></td>
              </tr>
              <?php
              if (empty($action)) {
                ?>
                <tr>
                  <td colspan="2" align="right">
                    <a href="<?php echo zen_href_link(FILENAME_SPECIALS, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_PRODUCT; ?></a>
                  </td>
                </tr>
                <?php
              }
              ?>
            </table>
          </div>
          <?php
        }
        ?>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
