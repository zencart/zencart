<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Thu Oct 24 21:13:46 2013 +0100 Modified in v1.5.2 $
 */

  require('includes/application_top.php');

  // verify products exist
  $chk_products = $db->Execute("select * from " . TABLE_PRODUCTS . " limit 1");
  if ($chk_products->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
    zen_redirect(zen_href_link(FILENAME_CATEGORIES));
  }

  // verify product has a master_categories_id
  $chk_products = $db->Execute("select master_categories_id from " . TABLE_PRODUCTS . " where products_id='" . (int)$_GET['products_filter'] . "'");
  if ($chk_products->fields['master_categories_id'] <= 0) {
    $messageStack->add(ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID, 'caution');
//    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $languages = zen_get_languages();

function array_minus_array($a, $b) {
       $c=array_diff($a,$b);
       $c=array_intersect($c, $a);
       return $c;
}

  $_GET['products_filter'] = $products_filter = ((isset($_GET['products_filter']) and $_GET['products_filter'] > 0) ? (int)$_GET['products_filter'] : (int)$_POST['products_filter']);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : (int)$current_category_id);

  if ($action == 'new_cat') {
    $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : $current_category_id);
    $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
//    $messageStack->add_session('SUCCESSFUL! SWITCHED CATEGORIES', 'success');
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }

// set categories and products if not set
  if ($products_filter == '' and $current_category_id != '') {
    $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
    if ($products_filter != '') {
      $messageStack->add_session(WARNING_PRODUCTS_LINK_TO_CATEGORY_REMOVED, 'caution');
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
    }
  } else {
    if ($products_filter == '' and $current_category_id == '') {
      $reset_categories_id = zen_get_category_tree('', '', '0', '', '', true);
      $current_category_id = $reset_categories_id[0]['id'];
      $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
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

        $check_category_from = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $copy_from_linked . "' limit 1");
        $check_category_to = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $copy_to_linked . "' limit 1");

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
        $products_to_categories_from_linked = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $copy_from_linked . "'");
        while (!$products_to_categories_from_linked->EOF) {
          $add_links_array[] = array('products_id' => $products_to_categories_from_linked->fields['products_id']);
          $products_to_categories_from_linked->MoveNext();
        }

        // get products already in category to be linked to
        $products_to_categories_to_linked = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $copy_to_linked . "'");
        while (!$products_to_categories_to_linked->EOF) {
          $remove_links_array[] = array('products_id' => $products_to_categories_to_linked->fields['products_id']);
          $products_to_categories_to_linked->MoveNext();
        }

// cannot count added/removed due to the nature of the how these are done
//        $cnt_added = 0;
        // remove elements in $remove_links_array that are in $add_links_array
        for ($i=0, $n=sizeof($add_links_array); $i<$n; $i++) {
          $good = 'true';
          for ($j=0, $nn=sizeof($remove_links_array); $j<$nn; $j++) {
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

        for ($i=0, $n=sizeof($make_links_result); $i<$n; $i++) {
//          $cnt_added++;
          $new_product = $make_links_result[$i]['products_id'];
          $sql = "insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  (products_id, categories_id)
                  values ($new_product, $copy_to_linked)";

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

        $check_category_from = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $remove_from_linked . "' limit 1");
        $check_category_to = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $remove_to_linked . "' limit 1");


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
        $products_to_categories_from_linked = $db->Execute("select ptoc.products_id, p.master_categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc left join " . TABLE_PRODUCTS . " p on ptoc.products_id=p.products_id where ptoc.categories_id='" . $remove_from_linked . "'");

        while (!$products_to_categories_from_linked->EOF) {
          if ($products_to_categories_from_linked->fields['master_categories_id'] == $remove_to_linked) {
            //die('THIS IS THE MASTER CATEGORIES ID!! ' . $remove_to_linked . '<br>');
            //break;
            $master_categories_id_stop[] = array('products_id' => $products_to_categories_from_linked->fields['products_id'],
                                     'master_categories_id' => $products_to_categories_from_linked->fields['master_categories_id']);
          }
          $add_links_array[] = array('products_id' => $products_to_categories_from_linked->fields['products_id'],
                                     'master_categories_id' => $products_to_categories_from_linked->fields['master_categories_id']);
          $products_to_categories_from_linked->MoveNext();
        }

        $stop_warning = '';
        if (sizeof($master_categories_id_stop) > 0) {
          for ($i=0, $n=sizeof($master_categories_id_stop); $i<$n; $i++) {
            $stop_warning .= TEXT_PRODUCTS_ID . $master_categories_id_stop[$i]['products_id'] . ': ' . zen_get_products_name($master_categories_id_stop[$i]['products_id']) . '<br>';
          }

          $stop_warning_message = WARNING_MASTER_CATEGORIES_ID_CONFLICT . ' ' . TEXT_MASTER_CATEGORIES_ID_CONFLICT_FROM . $remove_from_linked . TEXT_MASTER_CATEGORIES_ID_CONFLICT_TO . $remove_to_linked . '<br />' . TEXT_INFO_MASTER_CATEGORIES_ID_PURPOSE . WARNING_MASTER_CATEGORIES_ID_CONFLICT_FIX . '<br /><br />' . TEXT_INFO_MASTER_CATEGORIES_ID_CONFLICT . $remove_to_linked . '<br />' . $stop_warning . '<br />';
          $messageStack->add_session($stop_warning_message, 'warning');
          zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $master_categories_id_stop[0]['products_id'] . '&current_category_id=' . $current_category_id));
//          die('THIS IS THE MASTER CATEGORIES ID!! ' . $remove_to_linked . ' - stop: ' . sizeof($master_categories_id_stop) . '<br>');
        }

        // get products already in category to be removed as linked to
        $products_to_categories_to_linked = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $remove_to_linked . "'");
        while (!$products_to_categories_to_linked->EOF) {
          $remove_links_array[] = array('products_id' => $products_to_categories_to_linked->fields['products_id']);
          $products_to_categories_to_linked->MoveNext();
        }

//        $cnt_removed = 0;
        // remove elements in $remove_links_array that are in $add_links_array
        for ($i=0, $n=sizeof($add_links_array); $i<$n; $i++) {
          $good = 'true';
          for ($j=0, $nn=sizeof($remove_links_array); $j<$nn; $j++) {
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

        for ($i=0, $n=sizeof($make_links_result); $i<$n; $i++) {
//          $cnt_removed++;
          $remove_product = $make_links_result[$i]['products_id'];
          $sql = "delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $remove_product . "' and categories_id='" . $remove_to_linked . "'";
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

        $zv_invalid_reset_master= 'false';
        $zv_complete_message_master = '';
        $reset_from_master = (int)$_POST['reset_categories_id_from_master'];

        $check_category_from = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $reset_from_master . "' limit 1");

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

        $reset_master_categories_id = $db->Execute("select p.products_id, p.master_categories_id, ptoc.categories_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc on ptoc.products_id= p.products_id and ptoc.categories_id='" . $reset_from_master . "' where ptoc.categories_id='" . $reset_from_master . "'");

        while (!$reset_master_categories_id->EOF) {
          $db->Execute("update " . TABLE_PRODUCTS . " set master_categories_id='" . (int)$reset_from_master . "' where products_id='" . $reset_master_categories_id->fields['products_id'] . "'");
          // reset products_price_sorter for searches etc.
          zen_update_products_price_sorter($reset_master_categories_id->fields['products_id']);
          $reset_master_categories_id->MoveNext();
        }

        $messageStack->add_session($zv_complete_message_master, 'success');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
        break;

      case 'set_master_categories_id':
        $db->Execute("update " . TABLE_PRODUCTS . " set master_categories_id='" . (int)$_GET['master_category'] . "' where products_id='" . $products_filter . "'");
        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_filter);

        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . (int)$_GET['products_filter'] . '&current_category_id=' . $current_category_id));
        break;

      case 'update_product':
        $zv_check_master_categories_id = 'true';
        $new_categories_sort_array[] = $_POST['current_master_categories_id'];
        $current_master_categories_id = $_POST['current_master_categories_id'];

        // set the linked products master_categories_id product(s)
        for ($i=0, $n=sizeof($_POST['categories_add']); $i<$n; $i++) {
          // is current master_categories_id in the list?
          if ($zv_check_master_categories_id == 'true' and $_POST['categories_add'][$i] == $current_master_categories_id->fields['master_categories_id']) {
            $zv_check_master_categories_id = 'true';
            // array is set above to master category
          } else {
            $new_categories_sort_array[] = (int)$_POST['categories_add'][$i];
          }
        }

        // remove existing products_to_categories for current product
        $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $products_filter . "'");

        $reset_master_categories_id = '';
        $old_master_categories_id = $current_master_categories_id;
        // add products to categories in order of master_categories_id first then others
        $verify_current_category_id = false;
        for ($i=0, $n=sizeof($new_categories_sort_array); $i<$n; $i++) {
          // is current master_categories_id in the list?
          if ($new_categories_sort_array[$i] <= 0) {
            die('I WOULD NOT ADD ' . $new_categories_sort_array[$i] . '<br>');
          } else {
            if ($current_category_id == $new_categories_sort_array[$i]) {
              $verify_current_category_id = true;
            }
            $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                    (products_id, categories_id)
                    values (" . $products_filter . ", " . (int)$new_categories_sort_array[$i] . ")");
            if ($reset_master_categories_id == '') {
              $reset_master_categories_id = $new_categories_sort_array[$i];
            }
            if ($old_master_categories_id == $new_categories_sort_array[$i]) {
              $reset_master_categories_id = $new_categories_sort_array[$i];
            }
          }
        }

        // reset master_categories_id in products table
        if ($zv_check_master_categories_id == 'true') {
          // make sure master_categories_id is set to current master_categories_id
          $db->Execute("update " . TABLE_PRODUCTS . " set master_categories_id='" . (int)$current_master_categories_id . "' where products_id='" . $products_filter . "'");
        } else {
          // reset master_categories_id to current_category_id because it was unselected
          $db->Execute("update " . TABLE_PRODUCTS . " set master_categories_id='" . (int)$reset_master_categories_id . "' where products_id='" . $products_filter . "'");
        }

        // recalculate price based on new master_categories_id
        zen_update_products_price_sorter($products_filter);

        if ($zv_check_master_categories_id == 'true') {
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

  $product_to_copy = $db->Execute("select p.products_id, pd.products_name, p.products_price_sorter, p.products_model, p.master_categories_id, p.products_image
                                  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd " . "
                         where p.products_id = '" . $products_filter . "'
                         and p.products_id = pd.products_id
                         and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

//  $catagories_query = "select distinct cd.categories_id from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc on cd.categories_id = ptoc.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  $catagories_query = "select distinct ptoc.categories_id, cd.* from " . TABLE_PRODUCTS_TO_CATEGORIES. " ptoc left join " . TABLE_CATEGORIES_DESCRIPTION  . " cd on cd.categories_id = ptoc.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by cd.categories_name";
  $categories_list = $db->Execute($catagories_query);

// current products to categories
  $products_list = $db->Execute("select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . $products_filter . "'");

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
  <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
//--></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<!-- <body onload="init()"> -->
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
///////////////////////////////////////////////////////////
// BOF: NEW CODE TO KEEP
?>

<?php
  if ($action != 'edit_update') {
    require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY);
?>

      <tr><form name="set_products_filter_id" <?php echo 'action="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_products_filter') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?><?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">

<?php
if ($_GET['products_filter'] != '') {
?>
          <tr>
            <td class="main" width="200" align="left" valign="top">&nbsp;</td>
            <td colspan="2" class="main"><?php echo TEXT_PRODUCT_TO_VIEW; ?></td>
          </tr>
          <tr>
            <td class="main" width="200" align="center" valign="top">

<?php
// FIX HERE
  $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
  echo ($display_priced_by_attributes ? '<span class="alert">' . TEXT_PRICED_BY_ATTRIBUTES . '</span>' . '<br />' : '');
  echo zen_get_products_display_price($_GET['products_filter']) . '<br /><br />';
  echo zen_get_products_quantity_min_units_display($_GET['products_filter'], $include_break = true);
  $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");
  while (!$not_for_cart->EOF) {
    $not_for_cart_array[] = $not_for_cart->fields['products_id'];
    $not_for_cart->MoveNext();
   }
?>
            </td>
            <td class="attributes-even" align="center"><?php echo zen_draw_products_pull_down('products_filter', 'size="10"', $not_for_cart->fields, true, $_GET['products_filter'], true, true); ?></td>
            <td class="main" align="center" valign="top">
              <?php
                echo zen_image_submit('button_display.gif', IMAGE_DISPLAY);
              ?>
            </td>
          </tr>
<?php
} else {
  $not_for_cart = '';
} // $_GET['products_filter'] != ''
?>

<?php
// show when product is linked
// not used in multiple products link manager
?>

        </table></td>
      </form></tr>
<?php } // $action != 'edit_update' ?>
<?php
// EOF: NEW CODE TO KEEP
///////////////////////////////////////////////////////////
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php if ($product_to_copy->EOF) { ?>
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE . '<br />' . '<span class="alert">' . TEXT_PRODUCTS_ID . $products_filter . TEXT_PRODUCTS_ID_INVALID . '</span>'; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_ID_NOT_REQUIRED; ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
<?php } else { ?>
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE . '<br />' . TEXT_PRODUCTS_ID . $product_to_copy->fields['products_id'] . ' ' . $product_to_copy->fields['products_name']; ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO; ?></td>
          </tr>
          <tr><?php echo zen_draw_form('restrict_product', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'get', '', true) . zen_draw_hidden_field('action', 'set_master_categories_id') . zen_draw_hidden_field('products_filter', $products_filter) . zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
            <td class="main">
              <?php
                echo '&nbsp;&nbsp;&nbsp;' . ADMIN_ROW_ICON_LINKED . '&nbsp;&nbsp;';
                echo '<strong>' . TEXT_MASTER_CATEGORIES_ID . '</strong> ' . zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($products_filter), $product_to_copy->fields['master_categories_id'], 'onChange="this.form.submit();"');
                echo zen_hide_session_id();
                if ($product_to_copy->fields['master_categories_id'] <= 0) {
                  echo '&nbsp;&nbsp;' . '<span class="alert">' . WARNING_MASTER_CATEGORIES_ID . '</span>';
                }
                echo TEXT_INFO_LINKED_TO_COUNT . $products_list->RecordCount();
              ?>
            </td>
          </form></tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
        </table></td>
      </tr>

<!-- bof: link to categories //-->
      <tr>
        <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
          </tr>
          <tr class="dataTableHeadingRow">
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" class="pageHeading" align="center"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_AVAILABLE; ?></td>
          </tr>
<?php
    while(!$products_list->EOF) {
      $selected_categories_check .= $products_list->fields['categories_id'];
      $products_list->MoveNext();
      if (!$products_list->EOF) {
        $selected_categories_check .= ',';
      }
    }
    $selected_categories = explode(',', $selected_categories_check);
    echo zen_draw_form('update', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=update_product&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post');
?>
          <tr class="dataTableHeadingRow">
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" height="50" align="center" valign="middle" class="dataTableHeadingContent">
              <?php
                if ($product_to_copy->fields['master_categories_id'] < 1) {
                  echo '<span class="alert">' . TEXT_SET_MASTER_CATEGORIES_ID . '</span>';
                } else {
              ?>
                  <input type="submit" value="<?php echo BUTTON_UPDATE_CATEGORY_LINKS; ?>">
              <?php } ?>
            </td>
          </tr>
<?php
    $cnt_columns = 0;
    echo '<tr class="dataTableHeadingRow">';
    while ($cnt_columns != MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
      $cnt_columns++;
      echo '<td class="dataTableHeadingContent" align="right">' . TEXT_INFO_ID . '</td>' . '<td class="dataTableHeadingContent" align="left">' . '&nbsp;&nbsp;Categories Name' . '</td>';
    }
        echo '</tr>';
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
        echo '<tr class="dataTableHeadingRow">';
      }
      echo '  <td class="dataTableContent" align="right">' . $categories_list->fields['categories_id'] . '</td>' . "\n";
      if ($product_to_copy->fields['master_categories_id'] == $categories_list->fields['categories_id']) {
//        echo '  <td class="dataTableContent" align="left">' . ($selected ? '<strong>' : '') . $zc_categories_checkbox . '&nbsp;' . $categories_list->fields['categories_name'] . ($selected ? '</strong>' : '') . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '</td>' . "\n";
        echo '  <td class="dataTableContent" align="left">' . '&nbsp;' . ADMIN_ROW_ICON_LINKED . '&nbsp;' . $categories_list->fields['categories_name'] . zen_draw_hidden_field('current_master_categories_id', $categories_list->fields['categories_id']) . '</td>' . "\n";
      } else {
        echo '  <td class="dataTableContent" align="left">' . ($selected ? '<strong>' : '') . $zc_categories_checkbox . '&nbsp;' . $categories_list->fields['categories_name'] . ($selected ? '</strong>' : '') . '</td>' . "\n";
      }
      $categories_list->MoveNext();
      if ($cnt_columns == MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS or $categories_list->EOF) {
        if ($categories_list->EOF and $cnt_columns != MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
          while ($cnt_columns < MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
            $cnt_columns++;
            echo '  <td class="dataTableContent" align="right">' . '&nbsp;' . '</td>' . "\n";
            echo '  <td class="dataTableContent" align="left">' . '&nbsp;' . '</td>' . "\n";
          }
        }
        echo '</tr>' . "\n";
        $cnt_columns = 0;
      }
    }
?>
          <tr class="dataTableHeadingRow">
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" height="50" align="center" valign="middle" class="dataTableHeadingContent">
              <?php
                if ($product_to_copy->fields['master_categories_id'] < 1) {
                  echo '<span class="alert">' . TEXT_SET_MASTER_CATEGORIES_ID . '</span>';
                } else {
              ?>
                  <input type="submit" value="<?php echo BUTTON_UPDATE_CATEGORY_LINKS; ?>">
              <?php } ?>
            </td>

          </tr>
          </form>
          <tr>
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" valign="top"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
          </tr>
          <tr>
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" ><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="<?php echo MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS*2; ?>" class="main"><?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER; ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '20'); ?></td>
          </tr>
<?php } ?>
            </table>
<!-- eof: link to categories //-->
            <table border="5" class="dataTableHeadingRow">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">
            <table border="0" class="dataTableHeadingRow" width="100%">
              <tr class="dataTableHeadingRow">
                <td class="pageHeading" align="center"><?php echo HEADER_CATEGORIES_GLOBAL_CHANGES; ?></td>
              </tr>
            </table>
<!-- copy products from one category to another as linked or new products -->
            <table border="0" class="dataTableHeadingRow" width="100%">
              <tr>
                <td colspan="3" valign="middle" height="10"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
              </tr>
              <form name="linked_copy" method="post" action="<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'action=copy_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'NONSSL'); ?>">
              <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
              <tr class="dataTableHeadingRow">
                <td colspan="3" class="dataTableContent"><?php echo TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableContent">
                  <?php
                    $categories_id_from_linked = TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . '&nbsp;<input type="text" name="copy_categories_id_from_linked" size="4">&nbsp;';
                    echo $categories_id_from_linked;
                  ?>
                </td>
                <td class="dataTableContent">
                  <?php
                    $categories_id_to_linked = TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . '&nbsp;<input type="text" name="copy_categories_id_to_linked" size="4">&nbsp;';
                    echo $categories_id_to_linked;
                  ?>
                </td>
                <td align="right" class="dataTableHeadingContent" valign="top">&nbsp;<input type="submit" value="<?php echo BUTTON_COPY_CATEGORY_LINKED; ?>">&nbsp;</td>
              </tr>
              </form>
            </table>
<!-- eof: copy products //-->
<!-- remove products from one category that are linked to another category -->
            <table border="0" class="dataTableHeadingRow" width="100%">
              <tr>
                <td colspan="3" valign="middle" height="10"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
              </tr>
              <form name="linked_remove" method="post" action="<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'action=remove_categories_products_to_another_category_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'NONSSL'); ?>">
              <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
              <tr class="dataTableHeadingRow">
                <td colspan="3" class="dataTableContent"><?php echo TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableContent">
                  <?php
                    $categories_id_from_linked = TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED . '&nbsp;<input type="text" name="remove_categories_id_from_linked" size="4">&nbsp;';
                    echo $categories_id_from_linked;
                  ?>
                </td>
                <td class="dataTableHeadingContent">
                  <?php
                    $categories_id_to_linked = TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED . '&nbsp;<input type="text" name="remove_categories_id_to_linked" size="4">&nbsp;';
                    echo $categories_id_to_linked;
                  ?>
                </td>
                <td align="right" class="dataTableHeadingContent" valign="top">&nbsp;<input type="submit" value="<?php echo BUTTON_REMOVE_CATEGORY_LINKED; ?>">&nbsp;</td>
              </tr>
              </form>
              <tr>
                <td colspan="3"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?></td>
              </tr>
            </table>
<!-- eof: remove products //-->
<!-- reset master_categories_id to request Categories -->
            <table border="0" class="dataTableHeadingRow" width="100%">
              <tr>
                <td colspan="3" valign="middle" height="10"><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
              </tr>
              <form name="master_reset" method="post" action="<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'action=reset_categories_products_to_another_category_master' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'NONSSL'); ?>">
              <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
              <tr class="dataTableHeadingRow">
                <td colspan="3" class="dataTableContent"><?php echo TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableContent">
                  <?php
                    $categories_id_from_linked = TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER . '&nbsp;<input type="text" name="reset_categories_id_from_master" size="4">&nbsp;';
                    echo $categories_id_from_linked;
                  ?>
                </td>
                <td align="right" class="dataTableHeadingContent" valign="top">&nbsp;<input type="submit" value="<?php echo BUTTON_RESET_CATEGORY_MASTER; ?>">&nbsp;</td>
              </tr>
              </form>
              <tr>
                <td colspan="3"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '3'); ?></td>
              </tr>
            </table>
<!-- eof: reset master_categories_id //-->
                </td>
              </tr>
            </table>

            </td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_PRODUCTS_TO_CATEGORIES . '</b>');
      $contents = array('form' => zen_draw_form('products_downloads_edit', FILENAME_PRODUCTS_TO_CATEGORIES, ''));
      if ($products_filter > 0) {
        $contents[] = array('text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
      }
      $contents[] = array('text' => '<b>' . TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name'] . '<br />' . TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model'] . '</b>');
      $contents[] = array('text' => '<br />' . TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS . '<br />' . TEXT_PRODUCTS_ID . zen_draw_input_field('products_filter', $products_filter));
//      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . '</form>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' . '</form>');
      break;
    default:
      // only show if a Product is selected
      if ($products_filter > 0) {
        $heading[] = array('text' => '<b>' . $product_to_copy->fields['products_id'] . ' ' . $product_to_copy->fields['products_name'] . '</b>');
        if ($products_filter > 0) {
          $contents[] = array('text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        }
        $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name']);
        $contents[] = array('text' => TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model']);
        $contents[] = array('text' => TEXT_PRODUCTS_PRICE . zen_get_products_display_price($products_filter));
        switch (true) {
          case ($product_to_copy->fields['master_categories_id'] == 0 and $products_filter > 0):
            $contents[] = array('text' => '<br /><span class="alert">' . WARNING_MASTER_CATEGORIES_ID . '</span><br />&nbsp;');
            break;
          default:
            $contents[] = array('text' => '<form action="' . FILENAME_PRODUCTS_TO_CATEGORIES . '.php' . '?action=edit&current_category_id=' . $current_category_id . '" method="post"><input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '" /><input type="hidden" name="products_filter" value="' . $products_filter . '" />');
            $contents[] = array('align' => 'center', 'text' => '<input type="submit" value="' . BUTTON_NEW_PRODUCTS_TO_CATEGORIES . '"></form>');
            $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3') . '<br />&nbsp;');
            $contents[] = array('align' => 'center', 'text' =>
              '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES) . '</a>&nbsp;&nbsp;' .
              '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_products_price_manager.gif', IMAGE_PRODUCTS_PRICE_MANAGER) . '</a><br /><br />' .
              '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '">' . zen_image_button('button_details.gif', IMAGE_DETAILS) . '</a>&nbsp;&nbsp;' .
              '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'action=new_product' . '&cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '">' . zen_image_button('button_edit_product.gif', IMAGE_EDIT_PRODUCT) . '</a>' . '<br />&nbsp;'
              );
            break;
        }
      }
    break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </td></table>
      </tr>

    </table></td>
<!-- downloads by product_name_eof //-->
  </tr>
</table>
<!-- body_text_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>