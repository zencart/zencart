<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 May 02 Modified in v1.5.6b $
 */
require('includes/application_top.php');

$_GET['products_filter'] = $products_filter = ((isset($_GET['products_filter']) && $_GET['products_filter'] > 0) ? (int)$_GET['products_filter'] : (int)$_POST['products_filter']);
$_GET['current_category_id'] = $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);

// verify products exist
$chk_products = $db->Execute("SELECT *
                              FROM " . TABLE_PRODUCTS . "
                              LIMIT 1");
if ($chk_products->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
}

// verify product has a master_categories_id
$chk_products = $db->Execute("SELECT master_categories_id
                              FROM " . TABLE_PRODUCTS . "
                              WHERE products_id = " . (int)$_GET['products_filter']);
if ($chk_products->fields['master_categories_id'] <= 0) {
  $messageStack->add(ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID, 'caution');
//    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
}

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$languages = zen_get_languages();

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if ($action == 'new_cat') {
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


if (zen_not_null($action)) {
  switch ($action) {
    case 'set_products_filter':
      $_GET['products_filter'] = $_POST['products_filter'];

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
      break;
    case 'copy_categories_products_to_another_category_linked':
      $zv_invalid_copy_linked = 'false';
      $zv_complete_message_linked = '';
      $copy_from_linked = (int)$_POST['copy_categories_id_from_linked'];
      $copy_to_linked = (int)$_POST['copy_categories_id_to_linked'];

      // do not proceed unless categories are different
      if ($copy_from_linked == $copy_to_linked) {
        $messageStack->add_session(WARNING_DUPLICATE_PRODUCTS_TO_CATEGORY_LINKED, 'warning');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }

      $check_category_from = $db->Execute("SELECT products_id
                                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                           WHERE categories_id = " . $copy_from_linked . "
                                           LIMIT 1");
      $check_category_to = $db->Execute("SELECT products_id
                                         FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                         WHERE categories_id = " . $copy_to_linked . "
                                         LIMIT 1");

      // check if from is valid category
      if ($check_category_from->RecordCount() < 1) {
        $zv_invalid_copy_linked = 'true';
        $zv_complete_message_linked .= WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . $copy_from_linked . '&nbsp;';
      } else {
        $zv_complete_message_linked .= SUCCESS_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . $copy_from_linked . '&nbsp;';
      }

      // check if to is valid category
      if ($check_category_to->RecordCount() < 1) {
        if (zen_childs_in_category_count($copy_to_linked) > 0) {
          $zv_invalid_copy_linked = 'true';
          $zv_complete_message_linked .= WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . $copy_to_linked . '&nbsp;';
        }
        $zv_complete_message_linked .= WARNING_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED_MISSING . $copy_to_linked . '&nbsp;';
      } else {
        $zv_complete_message_linked .= SUCCESS_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . $copy_to_linked . '&nbsp;';
      }

      if ($zv_invalid_copy_linked == 'true') {
        $messageStack->add_session($zv_complete_message_linked, 'warning');
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
      // remove elements in $remove_links_array that are in $add_links_array
      for ($i = 0, $n = sizeof($add_links_array); $i < $n; $i++) {
        $good = 'true';
        for ($j = 0, $nn = sizeof($remove_links_array); $j < $nn; $j++) {
          if ($add_links_array[$i]['products_id'] == $remove_links_array[$j]['products_id']) {
            $good = 'false';
            break;
          }
        }
        // build final of good products
        if ($good == 'true') {
          $make_links_result[] = array('products_id' => $add_links_array[$i]['products_id']);
        }
      }

      for ($i = 0, $n = sizeof($make_links_result); $i < $n; $i++) {
//          $cnt_added++;
        $new_product = $make_links_result[$i]['products_id'];
        $sql = "INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                VALUES ('" . $new_product . "', '" . $copy_to_linked . "')";

        $db->Execute($sql);
      }

      // set message of completion
      if (sizeof($make_links_result) == 0) {
        $zv_complete_message_linked = WARNING_COPY_FROM_IN_TO_LINKED . $zv_complete_message_linked;
        $warning_color = 'caution';
      } else {
        if ($check_category_from->RecordCount() < 1 or $check_category_to->RecordCount() < 1) {
          $zv_complete_message_linked = WARNING_COPY_LINKED . $zv_complete_message_linked;
          $warning_color = 'error';
        } else {
          $zv_complete_message_linked = SUCCESS_COPY_LINKED . $zv_complete_message_linked;
          $warning_color = 'success';
        }
      }

      if (sizeof($make_links_result) == 0) {
        $messageStack->add_session($zv_complete_message_linked, $warning_color);
      } else {
        $messageStack->add_session($zv_complete_message_linked, $warning_color);
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    case 'remove_categories_products_to_another_category_linked':
      $zv_invalid_remove_linked = 'false';
      $zv_complete_message_linked = '';
      $remove_from_linked = (int)$_POST['remove_categories_id_from_linked'];
      $remove_to_linked = (int)$_POST['remove_categories_id_to_linked'];

      // do not proceed unless categories are different
      if ($remove_from_linked == $remove_to_linked) {
        $messageStack->add_session(WARNING_DUPLICATE_PRODUCTS_TO_CATEGORY_LINKED, 'warning');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }

      $check_category_from = $db->Execute("SELECT products_id
                                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                           WHERE categories_id = " . $remove_from_linked . "
                                           LIMIT 1");
      $check_category_to = $db->Execute("SELECT products_id
                                         FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                         WHERE categories_id = " . $remove_to_linked . "
                                         LIMIT 1");


      // check if from is valid category
      if ($check_category_from->RecordCount() < 1) {
        $zv_invalid_remove_linked = 'true';
        $zv_complete_message_linked .= WARNING_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . $remove_from_linked . '&nbsp;';
      } else {
        $zv_complete_message_linked .= SUCCESS_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . $remove_from_linked . '&nbsp;';
      }

      // check if to is valid category
      if ($check_category_to->RecordCount() < 1) {
        $zv_invalid_remove_linked = 'true';
        $zv_complete_message_linked .= WARNING_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . $remove_to_linked . '&nbsp;';
      } else {
        $zv_complete_message_linked .= SUCCESS_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . $remove_to_linked . '&nbsp;';
      }

      if ($zv_invalid_remove_linked == 'true') {
        $messageStack->add_session($zv_complete_message_linked, 'warning');
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
      foreach ($products_to_categories_from_linked as $item) {
        if ($item['master_categories_id'] == $remove_to_linked) {
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
      if (sizeof($master_categories_id_stop) > 0) {
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
      for ($i = 0, $n = sizeof($add_links_array); $i < $n; $i++) {
        $good = 'true';
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

      for ($i = 0, $n = sizeof($make_links_result); $i < $n; $i++) {
//          $cnt_removed++;
        $remove_product = $make_links_result[$i]['products_id'];
        $sql = "DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = " . $remove_product . "
                AND categories_id = " . $remove_to_linked;
        $db->Execute($sql);
      }

      // set message of completion
      if (sizeof($make_links_result) == 0) {
        $zv_complete_message_linked = WARNING_REMOVE_FROM_IN_TO_LINKED . $zv_complete_message_linked;
        $warning_color = 'caution';
      } else {
        if ($check_category_from->RecordCount() < 1 or $check_category_to->RecordCount() < 1) {
          $zv_complete_message_linked = WARNING_REMOVE_LINKED . $zv_complete_message_linked;
          $warning_color = 'warning';
        } else {
          $zv_complete_message_linked = SUCCESS_REMOVE_LINKED . $zv_complete_message_linked;
          $warning_color = 'success';
        }
      }

      if (sizeof($make_links_result) == 0) {
        $messageStack->add_session($zv_complete_message_linked, $warning_color);
      } else {
        $messageStack->add_session($zv_complete_message_linked, $warning_color);
      }

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    case 'reset_categories_products_to_another_category_master':
      // reset the master_categories_id for all products in selected category

      $zv_invalid_reset_master = 'false';
      $zv_complete_message_master = '';
      $reset_from_master = (int)$_POST['reset_categories_id_from_master'];

      $check_category_from = $db->Execute("SELECT products_id
                                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                           WHERE categories_id = " . $reset_from_master . "
                                           LIMIT 1");

      // check if from is valid category
      if ($check_category_from->RecordCount() < 1) {
        $zv_invalid_reset_master = 'true';
        $zv_complete_message_master .= WARNING_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER . $reset_from_master . '&nbsp;';
      } else {
        $zv_complete_message_master .= SUCCESS_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER . $reset_from_master . '&nbsp;';
      }

      if ($zv_invalid_reset_master == 'true') {
        $messageStack->add_session($zv_complete_message_master, 'warning');
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

      $messageStack->add_session($zv_complete_message_master, 'success');
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    case 'set_master_categories_id':
      $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                    SET master_categories_id = " . (int)$_GET['master_category'] . "
                    WHERE products_id = " . (int)$products_filter);
      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($products_filter);

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . (int)$_GET['products_filter'] . '&current_category_id=' . $current_category_id));
      break;

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
                                 WHERE p.products_id = '" . (int)$products_filter . "'
                                 AND p.products_id = pd.products_id
                                 AND pd.language_id = " . (int)$_SESSION['languages_id']);
//}
//  $catagories_query = "select distinct cd.categories_id from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc on cd.categories_id = ptoc.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
$catagories_query = "SELECT DISTINCT ptoc.categories_id, cd.*
                     FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc
                     LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = ptoc.categories_id
                       AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                     ORDER BY cd.categories_name";
$categories_list = $db->Execute($catagories_query);

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
    <!-- body_text //-->
    <div class="container-fluid">
        <?php
        if ($action != 'edit_update') {
          require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY);
          ?>

        <div class="row">
            <?php echo zen_draw_form('set_products_filter_id', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_products_filter', 'post', 'class="form-horizontal"') ?>
            <?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?>
            <?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
            <?php
            if ($_GET['products_filter'] != '') {
              ?>
            <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?></div>
            <div class="row"><?php echo TEXT_PRODUCT_TO_VIEW; ?></div>
            <div class="row">
              <div class="col-sm-4">

                <?php
// FIX HERE
                $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
                echo ($display_priced_by_attributes ? '<span class="alert">' . TEXT_PRICED_BY_ATTRIBUTES . '</span>' . '<br>' : '');
                echo zen_get_products_display_price($_GET['products_filter']) . '<br><br>';
                echo zen_get_products_quantity_min_units_display($_GET['products_filter'], $include_break = true);
                ?>
              </div>
              <?php
              $excluded_products = array();
//              $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");
//              while (!$not_for_cart->EOF) {
//                $excluded_products[] = $not_for_cart->fields['products_id'];
//                $not_for_cart->MoveNext();
//              }
              ?>
              <div class="col-sm-4"><?php echo zen_draw_products_pull_down('products_filter', 'size="10" class="form-control"', $excluded_products, true, $_GET['products_filter'], true, true); ?></div>
              <div class="col-sm-4">
                <button type="submit" class="btn btn-info"><?php echo IMAGE_DISPLAY; ?></button>
              </div>
            </div>
            <?php
          } // $_GET['products_filter'] != ''
          ?>

          <?php
// show when product is linked
// not used in multiple products link manager
          ?>
          <?php echo '</form>'; ?>
        </div>
      <?php } // $action != 'edit_update'  ?>
      <?php if ($product_to_copy->EOF) { ?>
        <h1><?php echo HEADING_TITLE . '<br />' . '<span class="alert">' . TEXT_PRODUCTS_ID . $products_filter . TEXT_PRODUCTS_ID_INVALID . '</span>'; ?></h1>
        <div class="form-group"><?php echo TEXT_PRODUCTS_ID_NOT_REQUIRED; ?></div>
      <?php } else { ?>
        <h1><?php echo HEADING_TITLE . '<br />' . TEXT_PRODUCTS_ID . $product_to_copy->fields['products_id'] . ' ' . $product_to_copy->fields['products_name']; ?></h1>
        <div class="form-group"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO; ?></div>
        <div class="form-group">
            <?php
            echo zen_draw_form('restrict_product', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'get', 'class="form-horizontal"', true);
            echo zen_draw_hidden_field('action', 'set_master_categories_id');
            echo zen_draw_hidden_field('products_filter', $products_filter);
            echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']);
            ?>
          <div class="col-sm-3 control-label">
              <?php
              echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
              echo zen_draw_label(TEXT_MASTER_CATEGORIES_ID, 'master_category');
              ?>
          </div>
          <div class="col-sm-6">
              <?php
              echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($products_filter), $product_to_copy->fields['master_categories_id'], 'onchange="this.form.submit();" class="form-control"');
              echo zen_hide_session_id();
              ?>
          </div>
          <div class="col-sm-3">
              <?php
              if ($product_to_copy->fields['master_categories_id'] <= 0) {
                echo '<span class="alert">' . WARNING_MASTER_CATEGORIES_ID . '</span>';
              }
              echo TEXT_INFO_LINKED_TO_COUNT . (isset($products_list) && $products_list != '' ? $products_list->RecordCount() : '');
              ?>
          </div>
          <?php echo '</form>'; ?>
        </div>

        <div class="row">
          <!-- bof: link to categories //-->
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?>
              <?php echo zen_draw_form('update', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=update_product&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post'); ?>
            <div class="form-group text-center"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_AVAILABLE; ?></div>
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
//        echo '<tr class="dataTableHeadingRow">';

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
//        echo '  <td class="dataTableContent" align="left">' . ($selected ? '<strong>' : '') . $zc_categories_checkbox . '&nbsp;' . $categories_list->fields['categories_name'] . ($selected ? '</strong>' : '') . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '</td>' . "\n";
                      ?>
                      <td class="dataTableContent">&nbsp;<?php echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED); ?>&nbsp;<?php echo $categories_list->fields['categories_name'] . zen_draw_hidden_field('current_master_categories_id', $categories_list->fields['categories_id']); ?></td>
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
            <div class="form-group"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></div>
            <div class="form-group"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER; ?></div>
          <?php } ?>

          <!-- eof: link to categories //-->
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($action) {
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_PRODUCTS_TO_CATEGORIES . '</h4>');
                $contents = array('form' => zen_draw_form('products_downloads_edit', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'post', 'class="form-horizontal"'));
                if ($products_filter > 0) {
                  $contents[] = array('text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
                }
                $contents[] = array('text' => '<strong>' . TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name'] . '</strong>');
                $contents[] = array('text' => '<strong>' . TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model'] . '</strong>');
                $contents[] = array('text' => TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS);
                $contents[] = array('text' => zen_draw_label(TEXT_PRODUCTS_ID, 'products_filter', 'class="control-label"') . zen_draw_input_field('products_filter', $products_filter, 'class="form-control"'));
//      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . '</form>');
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                // only show if a Product is selected
                if ($products_filter > 0) {
                  $heading[] = array('text' => '<h4>' . $product_to_copy->fields['products_id'] . ' ' . $product_to_copy->fields['products_name'] . '</h4>');
                  $contents[] = array('text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
                  $contents[] = array('text' => TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name']);
                  $contents[] = array('text' => TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model']);
                  $contents[] = array('text' => TEXT_PRODUCTS_PRICE . zen_get_products_display_price($products_filter));
                  switch (true) {
                    case ($product_to_copy->fields['master_categories_id'] == 0 && $products_filter > 0):
                      $contents[] = array('text' => '<span class="alert">' . WARNING_MASTER_CATEGORIES_ID . '</span>');
                      break;
                    default:
                      $contents[] = array('align' => 'center', 'text' => zen_draw_form('new_products_to_categories', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=edit&current_category_id=' . $current_category_id) . zen_draw_hidden_field('products_filter', $products_filter) . '<button type="submit" class="btn btn-primary">' . BUTTON_NEW_PRODUCTS_TO_CATEGORIES . '</button></form>');
                      $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
                      $contents[] = array('align' => 'center', 'text' =>
                        '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_ATTRIBUTES . '</a>&nbsp;&nbsp;' .
                        '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a><br /><br />' .
                        '<a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter) . '" class="btn btn-info" role="button">' . IMAGE_DETAILS . '</a>&nbsp;&nbsp;' .
                        '<a href="' . zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_PRODUCT . '</a>'
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
        </div>
      </div>
      <div class="row text-center">
          <?php echo HEADER_CATEGORIES_GLOBAL_CHANGES; ?>
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?>
      </div>
      <div class="row dataTableHeadingRow" style="padding: 5px 0 5px 0">
        <!-- copy products from one category to another as linked or new products -->
        <?php echo zen_draw_form('linked_copy', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=copy_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
        <div class="form-group-row">
          <div class="col-sm-12"><?php echo TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'copy_categories_id_from_linked', 'class="control-label"') . zen_draw_input_field('copy_categories_id_from_linked', '', 'class="form-control"'); ?>
          </div>
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'copy_categories_id_to_linked', 'class="control-label"') . zen_draw_input_field('copy_categories_id_to_linked', '', 'class="form-control"'); ?>
          </div>
          <div class="col-sm-4"><button type="submit" class="btn btn-primary"><?php echo BUTTON_COPY_CATEGORY_LINKED; ?></button></div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: copy products //-->
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?>
      </div>
      <div class="row dataTableHeadingRow" style="padding: 5px 0 5px 0">
        <!-- remove products from one category that are linked to another category -->
        <?php echo zen_draw_form('linked_remove', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=remove_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
        <div class="form-group-row">
          <div class="col-sm-12"><?php echo TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'remove_categories_id_from_linked', 'class="control-label"') . zen_draw_input_field('remove_categories_id_from_linked', '', 'class="form-control"'); ?>
          </div>
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'remove_categories_id_to_linked', 'class="control-label"') . zen_draw_input_field('remove_categories_id_to_linked', '', 'class="form-control"'); ?>
          </div>
          <div class="col-sm-4"><button type="submit" class="btn btn-danger"><?php echo BUTTON_REMOVE_CATEGORY_LINKED; ?></button></div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: remove products //-->
      </div>
      <div class="row">
          <?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?>
      </div>
      <div class="row dataTableHeadingRow" style="padding: 5px 0 5px 0">
        <!-- reset master_categories_id to request Categories -->
        <?php echo zen_draw_form('master_reset', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=reset_categories_products_to_another_category_master' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post', 'class="form-horizontal"'); ?>
        <div class="form-group-row">
          <div class="col-sm-12"><?php echo TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER; ?></div>
        </div>
        <div class="form-group-row">
          <div class="col-sm-4">
              <?php echo zen_draw_label(TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER, 'reset_categories_id_from_master', 'class="control-label"') . zen_draw_input_field('reset_categories_id_from_master', '', 'class="form-control"'); ?>
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
