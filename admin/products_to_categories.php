<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 June 16 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$_GET['products_filter'] = $products_filter = ((isset($_GET['products_filter']) && $_GET['products_filter'] > 0) ? (int)$_GET['products_filter'] : (isset($_POST['products_filter'])  ? (int)$_POST['products_filter'] : 0));
$_GET['current_category_id'] = $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);

// verify at least one product exists
$chk_products = $db->Execute("SELECT *
                              FROM " . TABLE_PRODUCTS . "
                              LIMIT 1");
if ($chk_products->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
}

// verify product has a master_categories_id
if ($products_filter > 0) {
$chk_products = $db->Execute("SELECT master_categories_id
                              FROM " . TABLE_PRODUCTS . "
                              WHERE products_id = " . $products_filter);
if (!$chk_products->EOF && $chk_products->fields['master_categories_id'] <= 0) {
  $messageStack->add(ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID, 'caution');
//    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
    }
}

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$languages = zen_get_languages();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if ($action == 'new_cat') {//this action from products_previous_next_display.php when a new category is selected
  $new_product_query = $db->Execute("SELECT ptc.*
                                     FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                     LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON ptc.products_id = pd.products_id
                                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                     WHERE ptc.categories_id = " . $current_category_id . "
                                     ORDER BY pd.products_name");
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
//    $messageStack->add_session('SUCCESSFUL! SWITCHED CATEGORIES', 'success');
  zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
}

// set categories and products if not set
if ($products_filter == '' && $current_category_id != '') {
  $new_product_query = $db->Execute("SELECT ptc.*
                                     FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                     LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON ptc.products_id = pd.products_id
                                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                     WHERE ptc.categories_id = " . (int)$current_category_id . "
                                     ORDER BY pd.products_name");
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
  if ($products_filter != '') {
    $messageStack->add_session(WARNING_PRODUCTS_LINK_TO_CATEGORY_REMOVED, 'caution');
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }
} else {
  if ($products_filter == '' && $current_category_id == '') {
    $reset_categories_id = zen_get_category_tree('', '', '0', '', '', true);
    $current_category_id = $reset_categories_id[0]['id'];
    $new_product_query = $db->Execute("SELECT ptc.*
                                       FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                       LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON ptc.products_id = pd.products_id
                                         AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                       WHERE ptc.categories_id = " . (int)$current_category_id . "
                                       ORDER BY pd.products_name");
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
    $_GET['products_filter'] = $products_filter;
  }
}

require(DIR_WS_MODULES . FILENAME_PREV_NEXT);

/**
 * validate the user-entered categories from the Global Tools
 */
function zen_validate_categories($ref_category_id, $target_category_id = '', $reset_mc = false)
{
    global $db, $messageStack;

    $categories_valid = true;

    if ($ref_category_id == '' || zen_get_categories_status($ref_category_id) == '') {//REF does not exist
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_REF_NOT_EXIST, $ref_category_id), 'warning');
    }
    if (!$reset_mc && ($target_category_id == '' || zen_get_categories_status($target_category_id) == '')) {//TARGET does not exist
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_TARGET_NOT_EXIST, $target_category_id), 'warning');
    }
    if (!$reset_mc && ($categories_valid && $ref_category_id == $target_category_id)) {//category IDs are the same
        $categories_valid = false;
        $messageStack->add_session(sprintf(WARNING_CATEGORY_IDS_DUPLICATED, $ref_category_id), 'warning');
    }

    if ($categories_valid) {
        $check_category_from = $db->Execute("SELECT products_id
                                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                           WHERE categories_id = " . $ref_category_id . "
                                           LIMIT 1");

        // check if REF has any products
        if ($check_category_from->RecordCount() < 1) {//there are no products in the FROM category: invalid
            $categories_valid = false;
            $messageStack->add_session(sprintf(WARNING_CATEGORY_NO_PRODUCTS, $ref_category_id), 'warning');
        }
        // check that TARGET has no subcategories
        if (!$reset_mc && zen_childs_in_category_count($target_category_id) > 0) {//subcategories exist in the TO category: invalid
            $categories_valid = false;
            $messageStack->add_session(sprintf(WARNING_CATEGORY_SUBCATEGORIES, $target_category_id), 'warning');
        }
    }
    return $categories_valid;
}
////////////////////////////

if (zen_not_null($action)) {
  switch ($action) {

    //choose a product to display
    case 'set_products_filter':
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
      break;

    //copy products in FROM category as linked products in TO category
    case 'copy_categories_products_to_another_category_linked':
        $copy_from_linked = (int)$_POST['copy_categories_id_from_linked'];
        $copy_to_linked = (int)$_POST['copy_categories_id_to_linked'];

        if (!zen_validate_categories($copy_from_linked, $copy_to_linked)) {
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
        }

      ///////////////////////////////////////////////////////////////
      // if either category was invalid nothing processes below
      ///////////////////////////////////////////////////////////////
      // get products to be linked from
      $products_to_categories_from_linked = $db->Execute("SELECT products_id
                                                          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                                          WHERE categories_id = " . $copy_from_linked);
      $add_links_array = array();
      foreach ($products_to_categories_from_linked as $item) {
        $add_links_array[] = array('products_id' => $item['products_id']);
      }

      // get products already in category to be linked to
      $products_to_categories_to_linked = $db->Execute("SELECT products_id
                                                        FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                                        WHERE categories_id = " . $copy_to_linked);
      $remove_links_array = array();
      foreach ($products_to_categories_to_linked as $item) {
        $remove_links_array[] = array('products_id' => $item['products_id']);
      }

// cannot count added/removed due to the nature of the how these are done
//        $cnt_added = 0;
      // check for elements in $remove_links_array that are already in $add_links_array
      $make_links_result = array();
      for ($i = 0, $n = sizeof($add_links_array); $i < $n; $i++) {
        $good = 'true';
        for ($j = 0, $nn = sizeof($remove_links_array); $j < $nn; $j++) {
          if ($add_links_array[$i]['products_id'] == $remove_links_array[$j]['products_id']) {
            $good = 'false';
            break;
          }
        }
        // build array of new (unlinked) products to copy
        if ($good == 'true') {
          $make_links_result[] = array('products_id' => $add_links_array[$i]['products_id']);
        }
      }
      if (count($make_links_result) == 0) {//nothing new to copy
        $messageStack->add_session(sprintf(WARNING_COPY_FROM_IN_TO_LINKED, $copy_from_linked, $copy_to_linked), 'caution');

      } else {//do the copy
      for ($i = 0, $n = sizeof($make_links_result); $i < $n; $i++) {
//          $cnt_added++;
        $new_product = $make_links_result[$i]['products_id'];
        $sql = "INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                VALUES ('" . $new_product . "', '" . $copy_to_linked . "')";

        $db->Execute($sql);
      }
          $messageStack->add_session(sprintf(SUCCESS_COPY_LINKED, $i, $copy_from_linked, $copy_to_linked), 'success');
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    //remove products from TO categery that are linked from FROM category
    case 'remove_categories_products_to_another_category_linked':

      $remove_from_linked = (int)$_POST['remove_categories_id_from_linked'];
      $remove_to_linked = (int)$_POST['remove_categories_id_to_linked'];

      if (!zen_validate_categories($remove_from_linked, $remove_to_linked)) {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }

      ///////////////////////////////////////////////////////////////
      // if either category was invalid nothing processes below
      ///////////////////////////////////////////////////////////////
      // get products to be removed as added linked from
      $products_to_categories_from_linked = $db->Execute("SELECT ptoc.products_id, p.master_categories_id
                                                          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc
                                                          LEFT JOIN " . TABLE_PRODUCTS . " p ON ptoc.products_id = p.products_id
                                                          WHERE ptoc.categories_id = " . $remove_from_linked);

      $add_links_array = array();
      $master_categories_id_stop= array();
      foreach ($products_to_categories_from_linked as $item) {
        if ($item['master_categories_id'] == $remove_to_linked) {//check if a linked product in the target category has the same master category: do not unlink
          //die('THIS IS THE MASTER CATEGORIES ID!! ' . $remove_to_linked . '<br>');
          //break;
          $master_categories_id_stop[] = array('products_id' => $item['products_id'],
            'master_categories_id' => $item['master_categories_id']);
        }
        $add_links_array[] = array(
          'products_id' => $item['products_id'],
          'master_categories_id' => $item['master_categories_id']);
      }

      $stop_warning = '';
      if (sizeof($master_categories_id_stop) > 0) {//a product set to be unlinked is in its master category. Create message and abort unlinking.
        for ($i = 0, $n = sizeof($master_categories_id_stop); $i < $n; $i++) {
          $stop_warning .= TEXT_PRODUCTS_ID . $master_categories_id_stop[$i]['products_id'] . ': ' . zen_get_products_name($master_categories_id_stop[$i]['products_id']) . '<br>';
        }

        $stop_warning_message = WARNING_MASTER_CATEGORIES_ID_CONFLICT . ' ' . TEXT_MASTER_CATEGORIES_ID_CONFLICT_FROM . $remove_from_linked . TEXT_MASTER_CATEGORIES_ID_CONFLICT_TO . $remove_to_linked . '<br />' . TEXT_INFO_MASTER_CATEGORIES_ID_PURPOSE . WARNING_MASTER_CATEGORIES_ID_CONFLICT_FIX . '<br /><br />' . TEXT_INFO_MASTER_CATEGORIES_ID_CONFLICT . $remove_to_linked . '<br />' . $stop_warning . '<br />';
        $messageStack->add_session($stop_warning_message, 'warning');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $master_categories_id_stop[0]['products_id'] . '&current_category_id=' . $current_category_id));
//          die('THIS IS THE MASTER CATEGORIES ID!! ' . $remove_to_linked . ' - stop: ' . sizeof($master_categories_id_stop) . '<br>');
      }

      // get products already in category to be removed as linked to
      $products_to_categories_to_linked = $db->Execute("SELECT products_id
                                                        FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                                        WHERE categories_id = " . $remove_to_linked);
      $remove_links_array = array();
      foreach ($products_to_categories_to_linked as $item) {
        $remove_links_array[] = array('products_id' => $item['products_id']);
      }

//        $cnt_removed = 0;
      // remove elements in $remove_links_array that are in $add_links_array
        $make_links_result = array();
      for ($i = 0, $n = sizeof($add_links_array); $i < $n; $i++) {
        $good = 'false';
        for ($j = 0, $nn = sizeof($remove_links_array); $j < $nn; $j++) {
          if ($add_links_array[$i]['products_id'] == $remove_links_array[$j]['products_id']) {
            $good = 'true';
            break;
          }
        }
        // build final of good products
        if ($good == 'true') {
          $make_links_result[] = array('products_id' => $add_links_array[$i]['products_id']);
        }
      }
      // check if there are any products to remove
      if (count($make_links_result) == 0) {//no products coincide
        $messageStack->add_session(sprintf(WARNING_REMOVE_FROM_IN_TO_LINKED, $remove_to_linked, $remove_from_linked), 'warning');
      } else {

      for ($i = 0, $n = sizeof($make_links_result); $i < $n; $i++) {
//          $cnt_removed++;
        $remove_product = $make_links_result[$i]['products_id'];
        $sql = "DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = " . $remove_product . "
                AND categories_id = " . $remove_to_linked;
        $db->Execute($sql);
      }
          $messageStack->add_session(sprintf(SUCCESS_REMOVE_LINKED, $i, $remove_to_linked), 'success');
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;


    // reset the master_categories_id for all products in the selected category
    case 'reset_categories_products_to_another_category_master':

      $reset_from_master = (int)$_POST['reset_categories_id_from_master'];

      if (!zen_validate_categories($reset_from_master, '', true)) {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }

      ///////////////////////////////////////////////////////////////
      // if either category was invalid nothing processes below
      ///////////////////////////////////////////////////////////////

      $reset_master_categories_id = $db->Execute("SELECT p.products_id, p.master_categories_id, ptoc.categories_id
                                                  FROM " . TABLE_PRODUCTS . " p
                                                  LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc ON ptoc.products_id = p.products_id
                                                    AND ptoc.categories_id = " . $reset_from_master . "
                                                  WHERE ptoc.categories_id = " . $reset_from_master);

      foreach ($reset_master_categories_id as $item) {
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET master_categories_id = " . (int)$reset_from_master . "
                      WHERE products_id = " . (int)$item['products_id']);
        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($item['products_id']);
      }

      $messageStack->add_session(sprintf(SUCCESS_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER, $reset_from_master), 'success');
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    // change the selected product master category id
    case 'set_master_categories_id':
      $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                    SET master_categories_id = " . (int)$_GET['master_category'] . "
                    WHERE products_id = " . (int)$products_filter);
      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($products_filter);

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    //update the product-to-multiple-categories links
    case 'update_product':
      $zv_check_master_categories_id = ('' !== $_POST['current_master_categories_id']);
      $new_categories_sort_array[] = $_POST['current_master_categories_id'];
      $current_master_categories_id = $_POST['current_master_categories_id'];
      if (!isset($_POST['categories_add'])) $_POST['categories_add'] = array();

      // set the linked products master_categories_id product(s)
      for ($i = 0, $n = sizeof($_POST['categories_add']); $i < $n; $i++) {
        // Populate the list of remaining categories in the selection list.
        $new_categories_sort_array[] = (int)$_POST['categories_add'][$i];
      }

      // remove existing products_to_categories for current product
      $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $products_filter . "'");

      $reset_master_categories_id = '';
      $old_master_categories_id = $current_master_categories_id;
      // add products to categories in order of master_categories_id first then others
      $verify_current_category_id = false;
      for ($i = 0, $n = sizeof($new_categories_sort_array); $i < $n; $i++) {
        // is current master_categories_id in the list?
        if ($new_categories_sort_array[$i] <= 0) {
          die('I WOULD NOT ADD ' . $new_categories_sort_array[$i] . '<br>');
        } else {
          if ($current_category_id == $new_categories_sort_array[$i]) {
            $verify_current_category_id = true;
          }
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                        VALUES (" . $products_filter . ", " . (int)$new_categories_sort_array[$i] . ")");
          if ($reset_master_categories_id == '') {
            $reset_master_categories_id = $new_categories_sort_array[$i];
          }
          if ($old_master_categories_id == $new_categories_sort_array[$i]) {
            $reset_master_categories_id = $new_categories_sort_array[$i];
          }
        }
      }

      // reset master_categories_id in products table
      if ($zv_check_master_categories_id == true) {
        // make sure master_categories_id is set to current master_categories_id
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET master_categories_id = " . (int)$current_master_categories_id . "
                      WHERE products_id = " . $products_filter);
      } else {
        // reset master_categories_id to current_category_id because it was unselected
        if ($reset_master_categories_id == '') {
          $reset_master_categories_id = $current_category_id;
          // Ensure that product is reachable by product/category relationship.
          $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                        VALUES (" . $products_filter . ", " . (int)$reset_master_categories_id . ")");
        }
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET master_categories_id = " . (int)$reset_master_categories_id . "
                      WHERE products_id = " . $products_filter);
      }

      // recalculate price based on new master_categories_id
      zen_update_products_price_sorter($products_filter);

      if ($zv_check_master_categories_id == true) {
        $messageStack->add_session(SUCCESS_MASTER_CATEGORIES_ID, 'success');
      } else {
        $messageStack->add_session(WARNING_MASTER_CATEGORIES_ID, 'warning');
      }

      // if product was removed from current categories_id stay in same category
      if (!$verify_current_category_id) {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'current_category_id=' . $current_category_id));
      } else {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }
      break;
  }
}

//if ($products_filter != '') {
$product_to_copy = $db->Execute("SELECT p.products_id, pd.products_name, p.products_price_sorter, p.products_model, p.master_categories_id, p.products_image
                                 FROM " . TABLE_PRODUCTS . " p,
                                      " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                 WHERE p.products_id = '" . $products_filter . "'
                                 AND p.products_id = pd.products_id
                                 AND pd.language_id = " . (int)$_SESSION['languages_id']);
//}
//  $categories_query = "select distinct cd.categories_id from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc on cd.categories_id = ptoc.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

$categories_query = "SELECT DISTINCT ptoc.categories_id, cd.*
                     FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc
                     LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = ptoc.categories_id
                       AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                     ORDER BY cd.categories_name";
$categories_list = $db->Execute($categories_query);

// current products to categories
//if ($products_filter != '') {
$products_list = $db->Execute("SELECT products_id, categories_id
                               FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                               WHERE products_id = '" . $products_filter . "'");
//}
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
  <!-- <body onload="init()"> -->
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
        <!-- body_text //-->
        <h1><?php echo HEADING_TITLE; ?></h1>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
        <?php require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY); ?>
        <?php if ($products_filter > 0) {//a product is selected ?>
        <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '20'); ?></div>

    <div class="row"><!--Product Block-->
    <div id="leftBlock" class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
    <div id="productSelect">
        <?php echo zen_draw_form('set_products_filter_id', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_products_filter', 'post', 'class="form-horizontal"') ?>
        <?php echo zen_draw_hidden_field('products_filter', $products_filter); ?>
        <?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
        <?php
              $excluded_products = array();
//              $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");
//              while (!$not_for_cart->EOF) {
//                $excluded_products[] = $not_for_cart->fields['products_id'];
//                $not_for_cart->MoveNext();
//              }
              ?>
        <?php echo zen_draw_label(TEXT_PRODUCT_TO_VIEW, 'products_filter'); ?>
        <?php echo zen_draw_products_pull_down('products_filter', 'size="10" class="form-control" id="products_filter"', $excluded_products, true, $products_filter, true, true); ?>
        <button type="submit" class="btn btn-info"><?php echo IMAGE_DISPLAY; ?></button>
        </form>
        <?php echo zen_draw_separator('pixel_trans.gif', '100%', '2'); ?>
        <div><!--pricing and linked category count-->
            <?php
            echo TEXT_PRODUCTS_ID . $product_to_copy->fields['products_id'] . ': ' . $product_to_copy->fields['products_name'] . ' (' . $product_to_copy->fields['products_model'] . ') ';
        // FIX HERE
        echo zen_get_products_display_price($products_filter);
        $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($products_filter);
        echo ($display_priced_by_attributes ? ' <span class="alert">' . TEXT_PRICED_BY_ATTRIBUTES . '</span>' : ' ');
        echo zen_get_products_quantity_min_units_display($products_filter, $include_break = true);
        echo '<br>' . TEXT_INFO_LINKED_TO_COUNT . (isset($products_list) && $products_list != '' ? $products_list->RecordCount() : '');
        ?>
        </div><!--end of product pricing-->
    </div><!--end of product select-->
    <div><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
    <div id="masterCategorySelect">
    <?php if ($product_to_copy->EOF) { //product not linked to ANY category: missing a master category ID/ID invalid ?>
        <span class="alert"><?php echo TEXT_PRODUCTS_ID . $products_filter . ' - ' . TEXT_PRODUCTS_ID_INVALID; ?></span><br>
    <?php } else { ?>
    <div class="form-group">
        <?php
        echo zen_draw_form('restrict_product', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'get', 'class="form-horizontal"', true);
        echo zen_draw_hidden_field('action', 'set_master_categories_id');
        echo zen_draw_hidden_field('products_filter', $products_filter);
        echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']);
        echo zen_hide_session_id(); ?>
        <div style="display:inline-block;float:left;margin-right:10px;"><?php echo zen_draw_label(zen_image(DIR_WS_IMAGES . ($product_to_copy->fields['master_categories_id'] > 0 ? 'icon_green_on.gif' : 'icon_red_on.gif'), IMAGE_ICON_LINKED) . '&nbsp;' . TEXT_MASTER_CATEGORIES_ID, 'master_category');
        echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($products_filter), $product_to_copy->fields['master_categories_id'],'onchange="this.form.submit();" class="form-control" id="master_category"');
        if ($product_to_copy->fields['master_categories_id'] <= 0) { ?>
            <span class="alert"><?php echo WARNING_MASTER_CATEGORIES_ID; ?></span>
            <?php } ?></div>
        <div><?php echo TEXT_INFO_MASTER_CATEGORY_CHANGE; ?></div>
        </form>
    </div>
    </div><!--end of masterCategorySelect-->
    </div><!-- end leftBlock-->
    <div id="infoBox" class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <?php
        $heading = array();
        $contents = array();

        switch ($action) {
            case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_PRODUCTS_TO_CATEGORIES . '</h4>');
                $contents = array('form' => zen_draw_form('products_downloads_edit', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'post', 'class="form-horizontal"'));
                if ($products_filter > 0) {
                    $contents[] = array(
                        'text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                    );
                }
                $contents[] = array('text' => '<strong>' . TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name'] . '</strong>');
                $contents[] = array('text' => '<strong>' . TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model'] . '</strong>');
                $contents[] = array('text' => TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS);
                $contents[] = array(
                    'text' => zen_draw_label(TEXT_PRODUCTS_ID, 'products_filter', 'class="control-label"') . zen_draw_input_field('products_filter', $products_filter, 'class="form-control"')
                );
//      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . '</form>');
                $contents[] = array(
                    'align' => 'center',
                    'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES,
                            'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                );
                break;
            default:
                // only show if a Product is selected
                if ($products_filter > 0) {
                    $heading[] = array('text' => '<h4>ID#' . $product_to_copy->fields['products_id'] . ' - ' . $product_to_copy->fields['products_name'] . '</h4>');
                    $contents[] = array(
                        'text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                    );
                    $contents[] = array('text' => TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name']);
                    $contents[] = array('text' => TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model']);
                    $contents[] = array('text' => TEXT_PRODUCTS_PRICE . zen_get_products_display_price($products_filter));
                    switch (true) {
                        case ($product_to_copy->fields['master_categories_id'] == 0 && $products_filter > 0):
                            $contents[] = array('text' => '<span class="alert">' . WARNING_MASTER_CATEGORIES_ID . '</span>');
                            break;
                        default:
                            $contents[] = array(
                                'align' => 'center',
                                'text' =>
                                    '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER,
                                        'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_ATTRIBUTES . '</a>&nbsp;&nbsp;' .
                                    '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER,
                                        'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a><br /><br />' .
                                    '<a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING,
                                        'cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter) . '" class="btn btn-info" role="button">' . BUTTON_CATEGORY_LISTING . '</a>&nbsp;&nbsp;' .
                                    '<a href="' . zen_href_link(FILENAME_PRODUCT,
                                        'action=new_product' . '&cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_PRODUCT . '</a>'
                            );
                            $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
                            $contents[] = array(
                                'align' => 'center',
                                'text' => zen_draw_form('new_products_to_categories', FILENAME_PRODUCTS_TO_CATEGORIES,
                                        'action=edit&current_category_id=' . $current_category_id) . zen_draw_hidden_field('products_filter',
                                        $products_filter) . '<button type="submit" class="btn btn-primary">' . BUTTON_NEW_PRODUCTS_TO_CATEGORIES . '</button></form>'
                            );
                            break;
                    }
                }
                break;
        }

        if ((zen_not_null($heading)) && (zen_not_null($contents))) {
            $box = new box;
            echo $box->infoBox($heading, $contents);
        }
        ?>
    </div><!--end of infoBox-->
    </div><!--end of row -->

        <?php if ($products_filter >0 && $product_to_copy->fields['master_categories_id'] > 0) { //a product is selected AND it has a master category ?>
        <div class="row">
          <!-- bof: link to categories //-->
          <div>
              <?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?>
              <div class="row"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO; ?></div>
              <?php echo zen_draw_separator('pixel_trans.gif', '100%', '2'); ?>
              <?php echo zen_draw_form('update', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=update_product&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post'); ?>

            <div class="form-group text-center">
                <?php
                if ($product_to_copy->fields['master_categories_id'] < 1) {
                  ?>
                <span class="alert"><?php echo TEXT_SET_MASTER_CATEGORIES_ID; ?></span>
                <?php
              } else {
                ?>
                <button type="submit" class="btn btn-primary"><?php echo BUTTON_UPDATE_CATEGORY_LINKS; ?></button>
              <?php } ?>
            </div>
            <table class="table">
              <thead>
                  <?php
                  $selected_categories_check = '';
                  while (!$products_list->EOF) {
                    $selected_categories_check .= $products_list->fields['categories_id'];
                    $products_list->MoveNext();
                    if (!$products_list->EOF) {
                      $selected_categories_check .= ',';
                    }
                  }
                  $selected_categories = explode(',', $selected_categories_check);
                  ?>
                  <?php
                  $cnt_columns = 0;
                  ?>
                <tr class="dataTableHeadingRow">
                    <?php
                    while ($cnt_columns != MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                      $cnt_columns++;
                      ?>
                    <th class="dataTableHeadingContent text-right"><?php echo TEXT_INFO_ID; ?> </th>
                    <th class="dataTableHeadingContent">&nbsp;&nbsp;<?php echo TEXT_CATEGORIES_NAME; ?></th>
                    <?php
                  }
                  ?>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $cnt_columns = 0;
                  while (!$categories_list->EOF) {
                    $cnt_columns++;
                    if (zen_not_null($selected_categories_check)) {
                      $selected = in_array($categories_list->fields['categories_id'], $selected_categories);
                    } else {
                      $selected = false;
                    }
                    $zc_categories_checkbox = zen_draw_checkbox_field('categories_add[]', $categories_list->fields['categories_id'], $selected);
                    if ($cnt_columns == 1) {
                      ?>
                    <tr class="dataTableHeadingRow">
                        <?php
                      }
                      ?>
                    <td class="dataTableContent text-right"><?php echo $categories_list->fields['categories_id']; ?></td>
                    <?php
                    if ($product_to_copy->fields['master_categories_id'] == $categories_list->fields['categories_id']) {
                      ?>
                      <td class="dataTableContent">&nbsp;<?php echo zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_LINKED); ?>&nbsp;<?php echo $categories_list->fields['categories_name'] . zen_draw_hidden_field('current_master_categories_id', $categories_list->fields['categories_id']); ?></td>
                      <?php
                    } else {
                      ?>
                      <td class="dataTableContent"><?php echo ($selected ? '<strong>' : '') . $zc_categories_checkbox . '&nbsp;' . $categories_list->fields['categories_name'] . ($selected ? '</strong>' : ''); ?></td>
                      <?php
                    }
                    $categories_list->MoveNext();
                    if ($cnt_columns == MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS || $categories_list->EOF) {
                      if ($categories_list->EOF && $cnt_columns != MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                        while ($cnt_columns < MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                          $cnt_columns++;
                          ?>
                          <td class="dataTableContent text-right">&nbsp;</td>
                          <td class="dataTableContent">&nbsp;</td>
                          <?php
                        }
                      }
                      ?>
                    </tr>
                    <?php
                    $cnt_columns = 0;
                  }
                }
                ?>
              </tbody>
            </table>
            <div class="form-group text-center">
                <?php
                if ($product_to_copy->fields['master_categories_id'] < 1) {
                  ?>
                <span class="alert"><?php echo TEXT_SET_MASTER_CATEGORIES_ID; ?></span>
                <?php
              } else {
                ?>
                <button type="submit" class="btn btn-primary"><?php echo BUTTON_UPDATE_CATEGORY_LINKS; ?></button>
              <?php } ?>
            </div>
            <?php echo '</form>'; ?>
          <!-- eof: link to categories //-->
        </div>
          <?php }
    }
        }  ?>
            <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '4'); ?></div>
            <!--Global Tools-->
      <div class="row">
          <h2><?php echo HEADER_CATEGORIES_GLOBAL_CHANGES; ?></h2>
          <div><?php echo TEXT_PRODUCTS_ID_NOT_REQUIRED; ?></div>
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?>
      </div>
      <div class="row dataTableHeadingRow">
        <!-- copy all products from one category to another as linked products -->
        <?php echo zen_draw_form('linked_copy', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=copy_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
          <h3><?php echo TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED_HEADING; ?></h3>
        <div class="form-group-row">
          <div class="col-sm-12"><?php echo TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'copy_categories_id_from_linked', 'class="control-label"') . zen_draw_input_field('copy_categories_id_from_linked', '', 'class="form-control" id="copy_categories_id_from_linked"'); ?>
          </div>
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'copy_categories_id_to_linked', 'class="control-label"') . zen_draw_input_field('copy_categories_id_to_linked', '', 'class="form-control" id="copy_categories_id_to_linked"'); ?>
          </div>
          <div class="col-sm-4"><button type="submit" class="btn btn-primary"><?php echo BUTTON_COPY_CATEGORY_LINKED; ?></button></div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: copy products //-->
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?>
      </div>
      <div class="row dataTableHeadingRow">
        <!-- remove products from one category that are linked to another category -->
        <?php echo zen_draw_form('linked_remove', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=remove_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
          <h3><?php echo TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED_HEADING; ?></h3>
          <div class="form-group-row"><div class="col-sm-12"><?php echo TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'remove_categories_id_from_linked', 'class="control-label"') . zen_draw_input_field('remove_categories_id_from_linked', '', 'class="form-control" id="remove_categories_id_from_linked"'); ?>
          </div>
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'remove_categories_id_to_linked', 'class="control-label"') . zen_draw_input_field('remove_categories_id_to_linked', '', 'class="form-control" id="remove_categories_id_to_linked"'); ?>
          </div>
          <div class="col-sm-4"><button type="submit" class="btn btn-danger"><?php echo BUTTON_REMOVE_CATEGORY_LINKED; ?></button></div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: remove products //-->
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?>
      </div>
      <div class="row dataTableHeadingRow">
        <!-- reset master_categories_id to request Categories -->
        <?php echo zen_draw_form('master_reset', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=reset_categories_products_to_another_category_master' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
          <h3><?php echo TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER_HEADING; ?></h3>
          <div class="form-group-row"><div class="col-sm-12"><?php echo TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER, 'reset_categories_id_from_master', 'class="control-label"') . zen_draw_input_field('reset_categories_id_from_master', '', 'class="form-control" id="reset_categories_id_from_master"'); ?>
          </div>
          <div class="col-sm-offset-4 col-sm-4">
            <button type="submit" class="btn btn-warning"><?php echo BUTTON_RESET_CATEGORY_MASTER; ?></button>
          </div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: reset master_categories_id //-->
      </div>
    </div>

    <!-- body_text_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
