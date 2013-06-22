<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: coupon_restrict.php 18695 2011-05-04 05:24:19Z drbyte $
 */
  //define('MAX_DISPLAY_RESTRICT_ENTRIES', 10);
  require('includes/application_top.php');
  $restrict_array = array();
  $restrict_array[] = array('id'=>'Deny', text=>'Deny');
  $restrict_array[] = array('id'=>'Allow', text=>'Allow');

/*
  if ($_POST['cPath_prod'] > 0 and $_POST['manufacturers_id'] > 0) {
    $current_category_id = 0;
    $current_manufacturers_id = 0;
    $_POST['cPath_prod'] = 0;
    $_POST['manufacturers_id'] = 0;
  }
*/

  if (isset($_GET['cid'])) $_GET['cid'] = (int)$_GET['cid'];
  if (isset($_GET['info'])) $_GET['info'] = (int)$_GET['info'];
  if (isset($_POST['cPath'])) $_POST['cPath'] = (int)$_POST['cPath'];
  if (isset($_POST['cPath_prod'])) $_POST['cPath_prod'] = (int)$_POST['cPath_prod'];
  if (isset($_GET['build_cat'])) $_GET['build_cat'] = (int)$_GET['build_cat'];
  if (isset($_GET['build_man'])) $_GET['build_man'] = (int)$_GET['build_man'];

  $the_path = $_POST['cPath'];
  if (isset($_GET['action']) && $_GET['action']=='switch_status') {
    if (isset($_POST['switchStatusProto']))
    {
      $status = $db->Execute("select coupon_restrict
                              from " . TABLE_COUPON_RESTRICT . "
                              where restrict_id = '" . $_GET['info'] . "'");

      $new_status = 'N';
      if ($status->fields['coupon_restrict'] == 'N') $new_status = 'Y';
      $db->Execute("update " . TABLE_COUPON_RESTRICT . "
                    set coupon_restrict = '" . $new_status . "'
                    where restrict_id = '" . $_GET['info'] . "'");
    }
  }
  if ($_GET['action']=='add_category' && isset($_POST['cPath'])) {
  	if ($_POST['cPath'] == 0) $_POST['cPath'] = -1;
    $test_query=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . "
                              where coupon_id = '" . $_GET['cid'] . "'
                              and category_id = '" . $_POST['cPath'] . "'");

    if ($test_query->RecordCount() < 1) {
      $status = 'N';
      if ($_POST['restrict_status']=='Deny') $status = 'Y';
      $db->Execute("insert into " . TABLE_COUPON_RESTRICT . "
                  (coupon_id, category_id, coupon_restrict)
                  values ('" . $_GET['cid'] . "', '" . $_POST['cPath'] . "', '" . $status . "')");
    } else {
      // message that nothing is done
      $messageStack->add(ERROR_DISCOUNT_COUPON_DEFINED_CATEGORY . ' ' . $_POST['cPath'], 'caution');
    }
  }


// from products dropdown selection
  if ($_GET['action']=='add_product' && $_POST['products_drop']) {
    $test_query=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $_GET['cid'] . "' and product_id = '" . (int)$_POST['products_drop'] . "'");
    if ($test_query->RecordCount() < 1) {
      $status = 'N';
      if ($_POST['restrict_status']=='Deny') $status = 'Y';

// ==================================
// bof: ALL ADD/DELETE of Products in one Category
        if ($_POST['products_drop'] < 0) {
        // adding new records
          if ($_GET['build_cat'] > 0 && $_POST['products_drop'] == -1) {
          // to insert new products from a given categories_id for a coupon_code that are not already in the table
          // products in the table from the catategories_id are skipped
            $new_products_query = "select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . $_GET['build_cat'] . "' and products_id not in (select product_id from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $_GET['cid'] . "')";
            $new_products = $db->Execute($new_products_query);
          }

          if ($_GET['build_cat'] > 0 && $_POST['products_drop'] == -2) {
          // to delete existing products from a given categories_id for a coupon_code that are already in the table
          // products in the table from the catategories_id are skipped
            $new_products_query = "select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . $_GET['build_cat'] . "' and products_id in (select product_id from " . TABLE_COUPON_RESTRICT . " where coupon_restrict = '" . $status . "' and coupon_id = '" . $_GET['cid'] . "')";
            $new_products = $db->Execute($new_products_query);
          }

          if ($_GET['build_man'] > 0 && $_POST['products_drop'] == -1) {
          // to insert new products from a given manufacturers_id for a coupon_code that are not already in the table
          // products in the table from the manufacturers_id are skipped
            $new_products_query = "select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . $_GET['build_man'] . "' and products_id not in (select product_id from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $_GET['cid'] . "')";
            $new_products = $db->Execute($new_products_query);
          }

          if ($_GET['build_man'] > 0 && $_POST['products_drop'] == -2) {
          // to delete existing products from a given manufacturers_id for a coupon_code that are already in the table
          // products in the table from the manufacturers_id are skipped
            $new_products_query = "select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . $_GET['build_man'] . "' and products_id in (select product_id from " . TABLE_COUPON_RESTRICT . " where coupon_restrict = '" . $status . "' and coupon_id = '" . $_GET['cid'] . "')";
            $new_products = $db->Execute($new_products_query);
          }

          // nothing to be done
          if ($new_products->RecordCount() == 0) {
            $messageStack->add(ERROR_DISCOUNT_COUPON_DEFINED_CATEGORY . ' ' . $_POST['cPath'], 'caution');
          }
          while(!$new_products->EOF) {
            // product passed and needs to be added/deleted
            // add all products from select category for each product not already defined in coupons_restrict
            if ($_POST['products_drop'] == -1) {
              $db->Execute("insert into " . TABLE_COUPON_RESTRICT . "
                          (coupon_id, product_id, coupon_restrict)
                          values ('" . $_GET['cid'] . "', '" . $new_products->fields['products_id'] . "', '" . $status . "')");
            } else {
            // removed as defined in coupons_restrict for either DENY or ALLOW
              $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                            WHERE coupon_id = '" . $_GET['cid'] . "'
                            and product_id = '" . $new_products->fields['products_id'] . "'
                            and coupon_restrict = '" . $status . "'");
            }

            $new_products->MoveNext();
          }
// eof: ALL ADD/DELETE of Products in one Category
// ==================================

      } else {
// normal insert of product one by one allow/deny to coupon
      $db->Execute("insert into " . TABLE_COUPON_RESTRICT . "
                  (coupon_id, product_id, coupon_restrict)
                  values ('" . $_GET['cid'] . "', '" . (int)$_POST['products_drop'] . "', '" . $status . "')");
      } // not all deny allow
    } else {
      $messageStack->add(ERROR_DISCOUNT_COUPON_DEFINED_PRODUCT . ' ' . (int)$_POST['products_drop'], 'caution');
    }
  }
  if ($_GET['action']=='remove') {
    if (isset($_GET['info']) && isset($_POST['actionRemoveProto'])) {
      $db->Execute("delete from " . TABLE_COUPON_RESTRICT . " where restrict_id = '" . $_GET['info'] . "'");
    }
  }
require('includes/admin_html_head.php');
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="pageHeading"><?php echo HEADING_TITLE_CATEGORY; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo HEADER_COUPON_ID; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_COUPON_NAME; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_CATEGORY_ID; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_CATEGORY_NAME; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_ALLOW; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_DENY; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_REMOVE; ?></td>
                  </tr>
<?php
    $cr_query_raw = "select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $_GET['cid'] . "' and category_id != '0'";
    $cr_split = new splitPageResults($_GET['cpage'], MAX_DISPLAY_RESTRICT_ENTRIES, $cr_query_raw, $cr_query_numrows);
    $cr_list = $db->Execute($cr_query_raw);
    while (!$cr_list->EOF) {
      $rows++;
      if (strlen($rows) < 2) {
        $rows = '0' . $rows;
      }
      if (((!$_GET['cid']) || (@$_GET['cid'] == $cr_list->fields['restrict_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cr_list->fields);
      }
        echo '          <tr class="dataTableRow">' . "\n";
     if ($cr_list->fields['category_id'] != -1) {
     $coupon = $db->Execute("select coupon_name from " . TABLE_COUPONS_DESCRIPTION . "
                             where coupon_id = '" . $_GET['cid'] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
     $category_name = zen_get_category_name($cr_list->fields['category_id'], $_SESSION['languages_id']);
     } else {
     	$category_name = TEXT_ALL_CATEGORIES;
     }
?>
                <td class="dataTableContent"><?php echo $_GET['cid']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $coupon->fields['coupon_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cr_list->fields['category_id']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $category_name; ?></td>
<?php
		if ($cr_list->fields['coupon_restrict']=='N') {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $cr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ALLOW) . '</a></td>';
		} else {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $cr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_DENY) . '</a></td>';
		}
		if ($cr_list->fields['coupon_restrict']=='Y') {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $cr_list->fields['restrict_id'], 'NONSSL') . '"  onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ALLOW) . '</a></td>';
		} else {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $cr_list->fields['restrict_id'], 'NONSSL') . '"  onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_DENY) . '</a></td>';
		}
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=remove&info=' . $cr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickActionRemove(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icons/delete.gif', IMAGE_REMOVE) . '</a></td>';
?>
              </tr>
<?php
    $cr_list->MoveNext();
    }
?>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $cr_split->display_count($cr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, $_GET['cpage'], TEXT_DISPLAY_NUMBER_OF_CATEGORIES); ?></td>
                    <td class="smallText" align="right"><?php echo $cr_split->display_links($cr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, MAX_DISPLAY_PAGE_LINKS, $_GET['cpage'],zen_get_all_get_params(array('cpage','action', 'x', 'y')),'cpage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr><form name="restrict_category" method="post" action="<?php echo zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=add_category&info=' . $cInfo->restrict_id, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo HEADER_CATEGORY_NAME; ?></td>
                    <td class="smallText" align="left"></td>
                    <td class="smallText" align="left"><?php echo zen_draw_pull_down_menu('cPath', zen_get_category_tree(), $current_category_id); ?></td>
                    <td class="smallText" align="left"><?php echo zen_draw_pull_down_menu('restrict_status', $restrict_array, $current_category_id); ?></td>
                    <td class="smallText" align="left"><input type="submit" name="add" value="Add"></td>
                    <td class="smallText" align="left">&nbsp;</td>
                    <td class="smallText" align="left">&nbsp;</td>
                  </tr>
                </table></td>
              </tr></form>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="pageHeading"><?php echo HEADING_TITLE_PRODUCT; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo HEADER_COUPON_ID; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_COUPON_NAME; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_PRODUCT_ID; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo HEADER_PRODUCT_STATUS; ?></td>
              <td class="dataTableHeadingContent" align="center"><?php echo HEADER_PRODUCT_MODEL; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_PRODUCT_NAME; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_ALLOW; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_DENY; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo HEADER_RESTRICT_REMOVE; ?></td>
                  </tr>
<?php
    $pr_query_raw = "select * from " . TABLE_COUPON_RESTRICT . " where coupon_id = '" . $_GET['cid'] . "' and product_id != '0'";
    $pr_split = new splitPageResults($_GET['ppage'], MAX_DISPLAY_RESTRICT_ENTRIES, $pr_query_raw, $pr_query_numrows);
    $pr_list = $db->Execute($pr_query_raw);
    while (!$pr_list->EOF) {
      $rows++;
      if (strlen($rows) < 2) {
        $rows = '0' . $rows;
      }
      if (((!$_GET['cid']) || (@$_GET['cid'] == $cr_list->fields['restrict_id'])) && (!$pInfo)) {
        $pInfo = new objectInfo($pr_list);
      }
        echo '          <tr class="dataTableRow">' . "\n";

     $coupon = $db->Execute("select coupon_name from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $_GET['cid'] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
     $product_name = zen_get_products_name($pr_list->fields['product_id'], $_SESSION['languages_id']);
?>
                <td class="dataTableContent"><?php echo $_GET['cid']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $coupon->fields['coupon_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $pr_list->fields['product_id']; ?></td>
          <td class="dataTableContent" align="center"><?php echo (zen_products_lookup($pr_list->fields['product_id'], 'products_status') == 0 ? zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) : zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON)); ?></td>
          <td class="dataTableContent" align="left"><?php echo (zen_products_lookup($pr_list->fields['product_id'], 'products_model')); ?></td>
                <td class="dataTableContent" align="left"><?php echo '<strong>' . $product_name . '</strong><br />' . TEXT_CATEGORY . zen_get_categories_name_from_product($pr_list->fields['product_id']) . '<br />' . TEXT_MANUFACTURER . zen_get_products_manufacturers_name($pr_list->fields['product_id']); ?></td>
<?php
		if ($pr_list->fields['coupon_restrict']=='N') {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $pr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ALLOW) . '</a></td>';
		} else {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $pr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_DENY) . '</a></td>';
		}
		if ($pr_list->fields['coupon_restrict']=='Y') {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $pr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_DENY) . '</a></td>';
		} else {
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=switch_status&info=' . $pr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickSwitchStatus(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ALLOW) . '</a></td>';
		}
		  echo '<td class="dataTableContent" align="center"><a href="' . zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=remove&info=' . $pr_list->fields['restrict_id'], 'NONSSL') . '" onClick="divertClickActionRemove(this.href);return false;" >' . zen_image(DIR_WS_IMAGES . 'icons/delete.gif', IMAGE_REMOVE) . '</a></td>';
?>
              </tr>
<?php
    $pr_list->MoveNext();
    }
?>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $pr_split->display_count($pr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, $_GET['ppage'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                    <td class="smallText" align="right"><?php echo $pr_split->display_links($pr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, MAX_DISPLAY_PAGE_LINKS, $_GET['ppage'],zen_get_all_get_params(array('ppage','action', 'x', 'y')),'ppage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr><form name="restrict_category" method="post" action="<?php echo zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=add_category&info=' . $cInfo->restrict_id, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
<?php
      if (isset($_POST['cPath_prod'])) $current_category_id = $_POST['cPath_prod'];
      $products = $db->Execute("select p.products_id, pd.products_name from " .
      TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
      where p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
      and p.products_id = p2c.products_id and p2c.categories_id = '" . $_POST['cPath_prod'] . "'
      order by pd.products_name");
      $products_array = array();

// manufacturers products
      $current_manufacturers_id = (isset($_POST['manufacturers_id'])) ? (int)$_POST['manufacturers_id'] : 0;
      if ($current_manufacturers_id > 0) {
      $products = $db->Execute("select p.products_id, pd.products_name from " .
      TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m
      where p.products_id = pd.products_id and pd.language_id = '" . $_SESSION['languages_id'] . "'
      and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$current_manufacturers_id . "'
      order by pd.products_name");
      $products_array = array();
      }

// echo 'I see  ' . $current_category_id . ' vs' . $current_manufacturers_id;
      if (!$products->EOF && $current_category_id > 0) {
        $products_array[] = array('id' => '-1',
                                   'text' => TEXT_ALL_PRODUCTS_ADD);
        $products_array[] = array('id' => '-2',
                                   'text' => TEXT_ALL_PRODUCTS_REMOVE);
      }

      if (!$products->EOF && $current_manufacturers_id > 0) {
        $products_array[] = array('id' => '-1',
                                   'text' => TEXT_ALL_MANUFACTURERS_ADD);
        $products_array[] = array('id' => '-2',
                                   'text' => TEXT_ALL_MANUFACTURERS_REMOVE);
      }

      while (!$products->EOF) {
        $products_array[] = array('id'=>$products->fields['products_id'],
                                   'text'=>$products->fields['products_name']);
        $products->MoveNext();
      }
?>
                    <td class="smallText" valign="top"><?php echo HEADER_CATEGORY_NAME . HEADER_MANUFACTURER_NAME; ?></td>
                    <td class="smallText" align="left"></td><form name="restrict_product" method="post" action="<?php echo zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'info=' . $cInfo->restrict_id, 'NONSSL'); ?>">
                    <?php echo zen_hide_session_id(); ?>
                    <td class="smallText" align="left">
<?php
// add manufacturers picker for manufacturers with products
      $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));

      $manufacturers = $db->Execute("select distinct m.manufacturers_id, m.manufacturers_name
                              from " . TABLE_MANUFACTURERS . " m
                              left join " . TABLE_PRODUCTS . " p on m.manufacturers_id = p.manufacturers_id
                              where p.manufacturers_id = m.manufacturers_id
                              and (p.products_status = 1
                              and p.products_quantity > 0)
                              order by m.manufacturers_name");

      while (!$manufacturers->EOF) {
        $manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'],
                                       'text' => $manufacturers->fields['manufacturers_name'] . ' [ #' . $manufacturers->fields['manufacturers_id'] . ' ]');
        $manufacturers->MoveNext();
      }

      echo zen_draw_pull_down_menu('cPath_prod', zen_get_category_tree(), $current_category_id, 'onChange="this.form.submit();"') . '<br>';
      echo zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $current_manufacturers_id, 'onChange="this.form.submit();"');
?>
                    </td></form>
<?php if (sizeof($products_array) > 0) { ?>
                    <form name="restrict_category" method="post" action="<?php echo zen_href_link('coupon_restrict.php', zen_get_all_get_params(array('info', 'action', 'x', 'y')) . 'action=add_product&info=' . $cInfo->restrict_id . '&build_cat=' . $current_category_id . '&build_man=' . $current_manufacturers_id, 'NONSSL'); ?>"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
                    <td class="smallText" valign="top"><?php echo HEADER_PRODUCT_NAME; ?></td>
                    <td class="smallText" align="left"><?php echo zen_draw_pull_down_menu('products_drop', $products_array, $current_category_id); ?></td>
                    <td class="smallText" align="left"><?php echo zen_draw_pull_down_menu('restrict_status', $restrict_array); ?></td>
                    <td class="smallText" align="left"><input type="submit" name="add" value="Update"></td>
                    <td class="smallText" align="left">&nbsp;</td>
                    <td class="smallText" align="left">&nbsp;</td>
<?php } else { ?>
                    <td class="smallText" align="left" colspan="6">&nbsp;</td>
<?php } ?>
                  </tr>
                  <tr>
                    <td class="smallText" align="left" colspan = "9"><?php echo TEXT_INFO_ADD_DENY_ALL; ?></td>
                  </tr>
                </table></td>
              </tr></form>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . (!empty($cInfo->coupon_id) ? $cInfo->coupon_id : $_GET['cid']) . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '')) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
<form name="actionRemove" id="actionRemove" action="#" method="post">
<input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>" />
<input type="hidden" name="actionRemoveProto" value="" />
</form>
<form name="switchStatus" id="switchStatus" action="#" method="post">
<input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>" />
<input type="hidden" name="switchStatusProto" value="" />
</form>
<script type="text/javascript">
function divertClickActionRemove(href)
{
	document.getElementById('actionRemove').action = href;
	document.getElementById('actionRemove').submit();
	return false;
}
function divertClickSwitchStatus(href)
{
	document.getElementById('switchStatus').action = href;
	document.getElementById('switchStatus').submit();
	return false;
}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
