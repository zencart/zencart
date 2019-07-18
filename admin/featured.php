<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 16 Modified in v1.5.6c $
 */
require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
        zen_set_featured_status($_GET['id'], $_POST['flag']);
        zen_redirect(zen_href_link(FILENAME_FEATURED, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'fID=' . $_GET['id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'NONSSL'));
      }
      break;
    case 'insert':
      if ($_POST['products_id'] < 1) {
        $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
      } else {
        $products_id = zen_db_prepare_input($_POST['products_id']);

        $featured_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

        $db->Execute("INSERT INTO " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, featured_date_available)
                      VALUES ('" . (int)$products_id . "',
                              now(),
                              '" . zen_db_input($expires_date) . "',
                              '1',
                              '" . zen_db_input($featured_date_available) . "')");

        $new_featured = $db->Execute("SELECT featured_id
                                      FROM " . TABLE_FEATURED . "
                                      WHERE products_id = " . (int)$products_id);
      } // nothing selected to add
      if ($_GET['go_back'] == 'ON') {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_id . '&current_category_id=' . $_GET['current_category_id']));
      } else {
        zen_redirect(zen_href_link(FILENAME_FEATURED, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'fID=' . $new_featured->fields['featured_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      }
      break;
    case 'update':
      $featured_id = zen_db_prepare_input($_POST['featured_id']);

      $featured_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
      $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

      $db->Execute("UPDATE " . TABLE_FEATURED . "
                    SET featured_last_modified = now(),
                        expires_date = '" . zen_db_input($expires_date) . "',
                        featured_date_available = '" . zen_db_input($featured_date_available) . "'
                    WHERE featured_id = " . (int)$featured_id);

      zen_redirect(zen_href_link(FILENAME_FEATURED, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . 'fID=' . (int)$featured_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      break;
    case 'deleteconfirm':
      // demo active test
      if (zen_admin_demo()) {
        $_GET['action'] = '';
        $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
        zen_redirect(zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      }
      $featured_id = zen_db_prepare_input($_POST['fID']);

      $db->Execute("DELETE FROM " . TABLE_FEATURED . "
                    WHERE featured_id = " . (int)$featured_id);

      zen_redirect(zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
      break;
    case 'pre_add_confirmation':
      // check for blank or existing featured
      $skip_featured = false;
      if (empty($_POST['pre_add_products_id'])) {
        $skip_featured = true;
        $messageStack->add_session(WARNING_FEATURED_PRE_ADD_EMPTY, 'caution');
      }

      if ($skip_featured == false) {
        $sql = "SELECT products_id
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_featured = $db->Execute($sql);
        if ($check_featured->RecordCount() < 1) {
          $skip_featured = true;
          $messageStack->add_session(WARNING_FEATURED_PRE_ADD_BAD_PRODUCTS_ID, 'caution');
        }
      }

      if ($skip_featured == false) {
        $sql = "SELECT featured_id
                FROM " . TABLE_FEATURED . "
                WHERE products_id = " . (int)$_POST['pre_add_products_id'];
        $check_featured = $db->Execute($sql);
        if ($check_featured->RecordCount() > 0) {
          $skip_featured = true;
          $messageStack->add_session(WARNING_FEATURED_PRE_ADD_DUPLICATE, 'caution');
        }
      }

      if ($skip_featured == true) {
        zen_redirect(zen_href_link(FILENAME_FEATURED, (isset($_GET['page']) && $_GET['page'] > 0 ? 'page=' . $_GET['page'] . '&' : '') . ((int)$check_featured->fields['featured_id'] > 0 ? 'fID=' . (int)$check_featured->fields['featured_id'] : '' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))));
      }
      // add empty featured

      $featured_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
      $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

      $products_id = zen_db_prepare_input($_POST['pre_add_products_id']);
      $db->Execute("INSERT INTO " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, featured_date_available)
                    VALUES ('" . (int)$products_id . "',
                            now(),
                            '" . zen_db_input($expires_date) . "',
                            '1', '" . zen_db_input($featured_date_available) . "')");

      $new_featured = $db->Execute("SELECT featured_id
                                    FROM " . TABLE_FEATURED . "
                                    WHERE products_id = " . (int)$products_id);

      $messageStack->add_session(SUCCESS_FEATURED_PRE_ADD, 'success');
      zen_redirect(zen_href_link(FILENAME_FEATURED, 'action=edit' . '&fID=' . $new_featured->fields['featured_id'] . '&manual=1'));
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
          <?php echo zen_draw_form('search', FILENAME_FEATURED, '', 'get'); ?>
          <?php
// show reset search
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            echo '<a href="' . zen_href_link(FILENAME_FEATURED) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
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
          <a href="<?php echo zen_href_link(FILENAME_FEATURED, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_PRODUCT; ?></a>
        </div>
        <?php
      }
      ?>
      <?php
      if (($action == 'new') || ($action == 'edit')) {
        $form_action = 'insert';
        if (($action == 'edit') && isset($_GET['fID'])) {
          $form_action = 'update';

          $product = $db->Execute("SELECT p.products_id, pd.products_name, p.products_price, p.products_priced_by_attribute,
                                          f.expires_date, f.featured_date_available
                                   FROM " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                        " . TABLE_FEATURED . " f
                                   WHERE p.products_id = pd.products_id
                                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                   AND p.products_id = f.products_id
                                   AND f.featured_id = " . (int)$_GET['fID']);

          $fInfo = new objectInfo($product->fields);

          if ($fInfo->products_priced_by_attribute == '1') {
            $fInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
          }
        } else {
          $fInfo = new objectInfo(array());

// create an array of featured products, which will be excluded from the pull down menu of products
// (when creating a new featured product)
          $featured_array = array();
          $featured = $db->Execute("SELECT p.products_id, p.products_model
                                    FROM " . TABLE_PRODUCTS . " p,
                                         " . TABLE_FEATURED . " f
                                    WHERE f.products_id = p.products_id");

          foreach ($featured as $item) {
            $featured_array[] = $item['products_id'];
          }

// Uncomment the following in order to also not include things that cannot go in the cart
//          $not_for_cart = $db->Execute("SELECT p.products_id
//                                        FROM " . TABLE_PRODUCTS . " p
//                                        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
//                                        WHERE pt.allow_add_to_cart = 'N'");
//          foreach ($not_for_cart as $item) {
//            $featured_array[] = $item['products_id'];
//          }

        }
        ?>
        <script>
          var StartDate = new ctlSpiffyCalendarBox("StartDate", "new_featured", "start", "btnDate1", "<?php echo (($fInfo->featured_date_available == '0001-01-01') ? '' : zen_date_short($fInfo->featured_date_available)); ?>", scBTNMODE_CUSTOMBLUE);
          var EndDate = new ctlSpiffyCalendarBox("EndDate", "new_featured", "end", "btnDate2", "<?php echo (($fInfo->expires_date == '0001-01-01') ? '' : zen_date_short($fInfo->expires_date)); ?>", scBTNMODE_CUSTOMBLUE);
        </script>
        <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '5'); ?></div>
        <div class="row">
            <?php echo zen_draw_form('new_featured', FILENAME_FEATURED, zen_get_all_get_params(array('action', 'info', 'fID')) . 'action=' . $form_action . '&go_back=' . $_GET['go_back'], 'post', 'onsubmit="return check_dates(start,StartDate.required, end, EndDate.required);" class="form-horizontal"'); ?>
            <?php
            if ($form_action == 'update') {
              echo zen_draw_hidden_field('featured_id', $_GET['fID']);
            }
            ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_FEATURED_PRODUCT, 'products_id', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php
                echo (isset($fInfo->products_name)) ? $fInfo->products_name . ' (' . $currencies->format($fInfo->products_price) . ')' : zen_draw_products_pull_down('products_id', 'size="15" class="form-control"', $featured_array, true, $_GET['add_products_id'], true);
                echo zen_draw_hidden_field('products_price', (isset($fInfo->products_price) ? $fInfo->products_price : ''));
                ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_FEATURED_AVAILABLE_DATE, 'start', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <script>StartDate.writeControl(); StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_FEATURED_EXPIRES_DATE, 'end', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <script>EndDate.writeControl(); EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
            </div>
          </div>
          <table class="table">
            <tr>
              <td class="text-right">
                <button type="submit" class="btn btn-primary"><?php echo (($form_action == 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button>
                <?php echo ((int)$_GET['manual'] == 0 ? '&nbsp;<a href="' . ($_GET['go_back'] == 'ON' ? zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['add_products_id'] . '&current_category_id=' . $_GET['current_category_id']) : zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . ((isset($_GET['fID']) && $_GET['fID'] != '') ? '&fID=' . $_GET['fID'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>' : ''); ?>
              </td>
            </tr>
          </table>
          <?php echo '<form>'; ?>
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
                    $featured_query_raw = "select p.products_id, pd.products_name, p.products_model, p.products_price, p.products_priced_by_attribute,
                                                  f.featured_id, f.featured_date_added, f.featured_last_modified, f.expires_date, f.date_status_change, f.status, f.featured_date_available
                                           from " . TABLE_PRODUCTS . " p,
                                                " . TABLE_FEATURED . " f,
                                                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                           where p.products_id = pd.products_id
                                           and pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                           and p.products_id = f.products_id" . $search . $order_by;

// Split Page
// reset page when page is unknown
                    if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['fID'])) {
                      $old_page = $_GET['page'];
                      $check_page = $db->Execute($featured_query_raw);
                      if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) {
                        $check_count = 1;
                        foreach ($check_page as $item) {
                          if ($item['featured_id'] == $_GET['fID']) {
                            break;
                          }
                          $check_count++;
                        }
                        $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) != 0 ? .5 : 0)), 0);
                        $page = $_GET['page'];
                        if ($old_page != $_GET['page']) {
// do nothing
                        }
                      } else {
                        $_GET['page'] = 1;
                      }
                    }

// create split page control
                    $featured_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $featured_query_raw, $featured_query_numrows);
                    $featured = $db->Execute($featured_query_raw);
                    foreach ($featured as $featured_line) {
                      if ((!isset($_GET['fID']) || (isset($_GET['fID']) && ($_GET['fID'] == $featured_line['featured_id']))) && !isset($fInfo)) {
                        $products = $db->Execute("SELECT products_image
                                                  FROM " . TABLE_PRODUCTS . "
                                                  WHERE products_id = " . (int)$featured_line['products_id']);

                        $fInfo_array = array_merge($featured_line, $products->fields);
                        $fInfo = new objectInfo($fInfo_array);
                      }

                      if (isset($fInfo) && is_object($fInfo) && ($featured_line['featured_id'] == $fInfo->featured_id)) {
                        echo '                  <tr id="defaultSelected" class="dataTableRowSelected"  onclick="document.location.href=\'' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $fInfo->featured_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'" role="button">' . "\n";
                      } else {
                        echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $featured_line['featured_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '\'" role="button">' . "\n";
                      }
                      ?>
                  <td  class="dataTableContent text-right"><?php echo $featured_line['products_id']; ?></td>
                  <td  class="dataTableContent"><?php echo $featured_line['products_name']; ?></td>
                  <td  class="dataTableContent"><?php echo $featured_line['products_model']; ?></td>
                  <td  class="dataTableContent text-center"><?php echo (($featured_line['featured_date_available'] != '0001-01-01' && $featured_line['featured_date_available'] != '') ? zen_date_short($featured_line['featured_date_available']) : TEXT_NONE); ?></td>
                  <td  class="dataTableContent text-center"><?php echo (($featured_line['expires_date'] != '0001-01-01' && $featured_line['expires_date'] != '') ? zen_date_short($featured_line['expires_date']) : TEXT_NONE); ?></td>
                  <td  class="dataTableContent text-center">
                      <?php
                      if ($featured_line['status'] == '1') {
                        echo zen_draw_form('setflag_products', FILENAME_FEATURED, 'action=setflag&id=' . $featured_line['featured_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                        ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
                      <input type="hidden" name="flag" value="0" />
                      <?php echo '</form>'; ?>
                      <?php
                    } else {
                      echo zen_draw_form('setflag_products', FILENAME_FEATURED, 'action=setflag&id=' . $featured_line['featured_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                      ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
                      <input type="hidden" name="flag" value="1" />
                      <?php echo '</form>'; ?>
                      <?php
                    }
                    ?>
                  </td>
                  <td class="dataTableContent text-right">
                      <?php echo '<a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $featured_line['featured_id'] . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                      <?php echo '<a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $featured_line['featured_id'] . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                      <?php
                      if (isset($fInfo) && is_object($fInfo) && ($featured_line['featured_id'] == $fInfo->featured_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_FEATURED, zen_get_all_get_params(array('fID')) . 'fID=' . $featured_line['featured_id'] . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_FEATURED . '</h4>');

                    $contents = array('form' => zen_draw_form('featured', FILENAME_FEATURED, 'page=' . $_GET['page'] . '&action=deleteconfirm' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . zen_draw_hidden_field('fID', $fInfo->featured_id));
                    $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                    $contents[] = array('text' => '<br><b>' . $fInfo->products_name . '</b>');
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $fInfo->featured_id . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'pre_add':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_PRE_ADD_FEATURED . '</h4>');
                    $contents = array('form' => zen_draw_form('featured', FILENAME_FEATURED, 'action=pre_add_confirmation' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')));
                    $contents[] = array('text' => TEXT_INFO_PRE_ADD_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRE_ADD_PRODUCTS_ID, 'pre_add_products_id', 'class="control-label"') . zen_draw_input_field('pre_add_products_id', '', zen_set_field_length(TABLE_FEATURED, 'products_id') . 'class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . ($fInfo->featured_id > 0 ? '&fID=' . $fInfo->featured_id : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
                    break;
                  default:
                    if (is_object($fInfo)) {
                      $heading[] = array('text' => '<h4>' . $fInfo->products_name . '</h4>');

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $fInfo->featured_id . '&action=edit' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_FEATURED, 'page=' . $_GET['page'] . '&fID=' . $fInfo->featured_id . '&action=delete' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_filter=' . $fInfo->products_id) . '" class="btn btn-primary" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a>');
                      $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($fInfo->featured_date_added));
                      $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($fInfo->featured_last_modified));
                      $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_info_image($fInfo->products_image, $fInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));

                      $contents[] = array('text' => '<br>' . TEXT_INFO_AVAILABLE_DATE . ' <b>' . (($fInfo->featured_date_available != '0001-01-01' and $fInfo->featured_date_available != '') ? zen_date_short($fInfo->featured_date_available) : TEXT_NONE) . '</b>');
                      $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . (($fInfo->expires_date != '0001-01-01' and $fInfo->expires_date != '') ? zen_date_short($fInfo->expires_date) : TEXT_NONE) . '</b>');
                      $contents[] = array('text' => TEXT_INFO_STATUS_CHANGE . ' ' . zen_date_short($fInfo->date_status_change));
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, '&action=new_product' . '&cPath=' . zen_get_product_path($fInfo->products_id, 'override') . '&pID=' . $fInfo->products_id . '&product_type=' . zen_get_products_type($fInfo->products_id)) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_PRODUCT . '</a>');

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_FEATURED, 'action=pre_add' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_SELECT . '</a><br>' . TEXT_INFO_MANUAL);
                    } else {
                      $heading[] = array('text' => '<h4>' . TEXT_NONE . '</h4>');
                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_FEATURED, 'action=pre_add' . ((isset($_GET['page']) && $_GET['page'] > 0) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_SELECT . '</a><br>' . TEXT_INFO_MANUAL);
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
                <td><?php echo $featured_split->display_count($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_FEATURED); ?></td>
                <td class="text-right"><?php echo $featured_split->display_links($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'fID'))); ?></td>
              </tr>
              <?php
              if (empty($action)) {
                ?>
                <tr>
                  <td colspan="2" align="right">
                    <a href="<?php echo zen_href_link(FILENAME_FEATURED, ((isset($_GET['page']) && $_GET['page'] > 0) ? 'page=' . $_GET['page'] . '&' : '') . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_PRODUCT; ?></a>
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
