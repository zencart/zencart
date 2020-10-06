<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 29 Modified in v1.5.7 $
 */
//define('MAX_DISPLAY_RESTRICT_ENTRIES', 10);
require('includes/application_top.php');

// -----
// This tool, 'normally' invoked from the coupon_admin, manages any category- and/or product-
// restriction(s) for the current coupon.
//
// The 'coupon_restrict' table is pretty simple, identifying categories and/or products
// that are either allowed- or denied-for the associated coupon_id.  Here are some valid
// examples (assuming a valid coupon_id and restrict_id), in the format (products_id, categories_id, coupon_restrict):
//
// 1) A single product that is not valid for the associated coupon (pid, 0, 'Y').
// 2) A single product that *is* valid for the associated coupon (pid, 0, 'N').
// 3) A category whose products are all not valid for the associated coupon (0, cid, 'Y').
//    a) Products within the category can be individually marked valid, as above in (2).
// 4) A category whose products are all valid for the associated coupon (0, cid, 'N').
//    a) Products within the category can be individually marked invalid, as above in (1).
// 5) Special case:  If the associated category_id is -1, that identifies that **all** categories
//    are valid or invalid for the associated coupon.  Product-specific exceptions -- see (1) and (2) above --
//    are then applied.
//
$restrict_array = array(
    array(
        'id' => 'Deny', 
        'text' => TEXT_PULLDOWN_DENY
    ),
    array(
        'id' => 'Allow', 
        'text' => TEXT_PULLDOWN_ALLOW
    )
);

if (isset($_GET['cPath_prod']) && isset($_GET['manufacturers_id']) && ((int)$_GET['cPath_prod']) > 0 && ((int)$_GET['manufacturers_id']) > 0) {
    $messageStack->add_session(ERROR_RESET_CATEGORY_MANUFACTURER, 'caution');
    zen_redirect(zen_href_link(FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('cPath_prod', 'manufacturers_id'))));
}

// -----
// Sanitize some of the multi-used form variables.
//
$cid = (isset($_POST['cid'])) ? (int)$_POST['cid'] : ((isset($_GET['cid'])) ? (int)$_GET['cid'] : 0);
$cPath = (isset($_GET['cPath'])) ? (int)$_GET['cPath'] : 0;
$cPath_prod = (isset($_GET['cPath_prod'])) ? (int)$_GET['cPath_prod'] : 0;

// -----
// If the coupon being restricted doesn't exist, log a PHP notice (since there's
// an admin 'fussing' with the $_GET variables) and redirect back to the coupon_admin.
//
$check = $db->Execute(
    "SELECT coupon_name
       FROM " . TABLE_COUPONS_DESCRIPTION . "
      WHERE coupon_id = $cid
        AND language_id = " . (int)$_SESSION['languages_id'] . "
      LIMIT 1"
);
if ($check->EOF) {
    trigger_error("Undefined coupon_id ($cid) requested by admin_id ({$_SESSION['admin_id']}).", E_USER_NOTICE);
    zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN));
}
$coupon_name = htmlspecialchars($check->fields['coupon_name'], ENT_COMPAT, CHARSET);
unset($check);

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
switch ($action) {
    case 'switch_status':
        $rid = (isset($_POST['rid'])) ? (int)$_POST['rid'] : 0;
        if ($rid > 0) {
            // -----
            // Retrieve the current restriction setting; if the restriction doesn't exist,
            // then there's nothing else to do.
            //
            $status = $db->Execute(
                "SELECT coupon_restrict
                   FROM " . TABLE_COUPON_RESTRICT . "
                  WHERE restrict_id = $rid
                  LIMIT 1"
            );
            if ($status->EOF) {
                break;
            }

            // -----
            // "Toggle" the status for the specified restriction.
            //
            $new_status = ($status->fields['coupon_restrict'] == 'N') ? 'Y' : 'N';
            $db->Execute(
                "UPDATE " . TABLE_COUPON_RESTRICT . "
                   SET coupon_restrict = '" . $new_status . "'
                 WHERE restrict_id = $rid
                 LIMIT 1"
            );
        }
        break;
        
    case 'add_category':
        if (!isset($_POST['cPath'])) {
            break;
        }
        if ($cPath == 0) {
            $cPath = -1;
        }
        $test_query = $db->Execute(
            "SELECT * 
               FROM " . TABLE_COUPON_RESTRICT . "
              WHERE coupon_id = $cid
                AND category_id = $cPath"
        );

        // -----
        // Message the admin if the category is already restricted, in some form, for the coupon.
        //
        if (!$test_query->EOF) {
            $messageStack->add_session(ERROR_DISCOUNT_COUPON_DEFINED_CATEGORY . " $cPath", 'caution');
        // -----
        // Otherwise, toggle the category's restriction status (Allow vs. Deny).
        //
        } else {
            $status = 'N';
            if (isset($_POST['restrict_status']) && $_POST['restrict_status'] == 'Deny') {
                $status = 'Y';
            }
            $db->Execute(
                "INSERT INTO " . TABLE_COUPON_RESTRICT . "
                    (coupon_id, category_id, coupon_restrict)
                 VALUES 
                    ($cid, $cPath, '$status')"
            );
        }
        break;
        
    case 'add_product':
        if (empty($_POST['pid']) || empty($_POST['restrict_status']) || (empty($_POST['prod_cat']) && empty($_POST['prod_man']))) {
            break;
        }
        $pid = (int)$_POST['pid'];
        $test_query = $db->Execute(
            "SELECT * 
               FROM " . TABLE_COUPON_RESTRICT . "
              WHERE coupon_id = $cid
                AND product_id = $pid"
        );
        if (!$test_query->EOF) {
            $messageStack->add_session(ERROR_DISCOUNT_COUPON_DEFINED_PRODUCT . ' ' . $pid, 'caution');
        } else {
            $status = ($_POST['restrict_status'] == 'Deny') ? 'Y' : 'N';
            
            $prod_cat = (!empty($_POST['prod_cat'])) ? (int)$_POST['prod_cat'] : 0;
            $prod_man = (!empty($_POST['prod_man'])) ? (int)$_POST['prod_man'] : 0;
            
            // -----
            // Normal insert of product one-by-one allow/deny to coupon
            //
            if ($pid > 0) {
                $db->Execute(
                    "INSERT INTO " . TABLE_COUPON_RESTRICT . "
                        (coupon_id, product_id, coupon_restrict)
                     VALUES ($cid, $pid, '" . $status . "')"
                );
            // -----
            // Otherwise, adding or dropping all products in a given category or manufacturer.  Note that processing
            // at the top of this script has restricted either a category _or_ a manufacturer!
            //
            } elseif ($pid == -1 || $pid == -2) {
                // adding new records
                if ($prod_cat > 0 && $pid == -1) {
                    // to insert new products from a given categories_id for a coupon_code that are not already in the table
                    // products in the table from the categories_id are skipped
                    $new_products_query = 
                        "SELECT products_id 
                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " 
                          WHERE categories_id = $prod_cat
                            AND products_id NOT IN (
                                    SELECT product_id 
                                      FROM " . TABLE_COUPON_RESTRICT . " 
                                     WHERE coupon_id = $cid
                                )";
                    $new_products = $db->Execute($new_products_query);
                }

                if ($prod_cat > 0 && $pid == -2) {
                    // to delete existing products from a given categories_id for a coupon_code that are already in the table
                    // products in the table from the catategories_id are skipped
                    $new_products_query = 
                        "SELECT products_id 
                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " 
                          WHERE categories_id = $prod_cat
                            AND products_id IN (
                                    SELECT product_id 
                                      FROM " . TABLE_COUPON_RESTRICT . " 
                                     WHERE coupon_restrict = '" . $status . "' 
                                       AND coupon_id = $cid
                                )";
                    $new_products = $db->Execute($new_products_query);
                }

                if ($prod_man > 0 && $pid == -1) {
                    // to insert new products from a given manufacturers_id for a coupon_code that are not already in the table
                    // products in the table from the manufacturers_id are skipped
                    $new_products_query = 
                        "SELECT products_id 
                           FROM " . TABLE_PRODUCTS . " 
                          WHERE manufacturers_id = $prod_man
                            AND products_id NOT IN (
                                    SELECT product_id 
                                      FROM " . TABLE_COUPON_RESTRICT . " 
                                     WHERE coupon_id = $cid
                                )";
                    $new_products = $db->Execute($new_products_query);
                }

                if ($prod_man > 0 && $pid == -2) {
                    // to delete existing products from a given manufacturers_id for a coupon_code that are already in the table
                    // products in the table from the manufacturers_id are skipped
                    $new_products_query = 
                        "SELECT products_id 
                           FROM " . TABLE_PRODUCTS . " 
                          WHERE manufacturers_id = $prod_man
                            AND products_id IN (
                                    SELECT product_id 
                                      FROM " . TABLE_COUPON_RESTRICT . " 
                                     WHERE coupon_restrict = '" . $status . "' 
                                       AND coupon_id = $cid
                                )";
                    $new_products = $db->Execute($new_products_query);
                }

                // nothing to be done
                if ($new_products->EOF) {
                    $messageStack->add_session(ERROR_DISCOUNT_COUPON_DEFINED_CATEGORY . ' ' . $cPath, 'caution');
                }
                while (!$new_products->EOF) {
                    // product passed and needs to be added/deleted
                    // add all products from select category for each product not already defined in coupons_restrict
                    if ($pid == -1) {
                        $db->Execute(
                            "INSERT INTO " . TABLE_COUPON_RESTRICT . "
                                (coupon_id, product_id, coupon_restrict)
                             VALUES 
                                ($cid, {$new_products->fields['products_id']}, '" . $status . "')"
                        );
                    } else {
                        // removed as defined in coupons_restrict for either DENY or ALLOW
                        $db->Execute(
                            "DELETE FROM " . TABLE_COUPON_RESTRICT . "
                              WHERE coupon_id = $cid
                                AND product_id = {$new_products->fields['products_id']}
                                AND coupon_restrict = '" . $status . "'"
                        );
                    }
                    $new_products->MoveNext();
                }
            }
        }
        break;
        
    case 'remove':
        $rid = (isset($_POST['rid'])) ? (int)$_POST['rid'] : 0;
        $db->Execute(
            "DELETE FROM " . TABLE_COUPON_RESTRICT . " 
              WHERE restrict_id = $rid
              LIMIT 1"
        );
        break;
        
    default:
        break;
}

if (!empty($action)) {
    zen_redirect(zen_href_link(FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action'))));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" media="print" href="includes/css/stylesheet_print.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src ="includes/general.js"></script>
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
<body onload="init();">
<!-- header //-->
<?php 
require DIR_WS_INCLUDES . 'header.php'; 
?>
<!-- header_eof //-->
<!-- body //-->
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <h2><?php echo sprintf(SUB_HEADING_COUPON_NAME, $coupon_name, $cid); ?></h2>
<?php
$allowed_icon = '<i class="fa fa-lg fa-check text-success" title="' . TEXT_ALLOWED . '"></i>';
$denied_icon = '<i class="fa fa-lg fa-ban text-danger" title="' . TEXT_DENIED . '"></i>';
$remove_image = zen_image(DIR_WS_IMAGES . 'icons/delete.gif', IMAGE_REMOVE);
$toggle_button = '&nbsp;&nbsp;<button type="button" class="cr-toggle" title="' . TEXT_STATUS_TOGGLE_TITLE . '">' . TEXT_STATUS_TOGGLE . '</button>';

$cpage = (isset($_GET['cpage'])) ? (int)$_GET['cpage'] : 1;

$cr_query_raw = "SELECT * FROM " . TABLE_COUPON_RESTRICT . " WHERE coupon_id = $cid AND category_id != 0";
$cr_split = new splitPageResults($cpage, MAX_DISPLAY_RESTRICT_ENTRIES, $cr_query_raw, $cr_query_numrows);
$cr_list = $db->Execute($cr_query_raw);
?>
    <div class="row">
        <h4><?php echo HEADING_TITLE_CATEGORY; ?></h4>
        <table class="table table-hover">
<?php
if ($cr_list->EOF) {
?>
            <tr class="dataTableHeadingRow">
                <td colspan="4" class="dataTableHeadingContent text-center"><?php echo TEXT_NO_CATEGORY_RESTRICTIONS; ?></td>
            </tr>
<?php
} else {
?>
            <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_CATEGORY_ID; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_CATEGORY_NAME; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_RESTRICT; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_RESTRICT_REMOVE; ?></td>
            </tr>
<?php
    while (!$cr_list->EOF) {
        if ($cr_list->fields['category_id'] == -1) {
            $category_name = TEXT_ALL_CATEGORIES;
        } else {
            $category_name = zen_get_category_name($cr_list->fields['category_id'], $_SESSION['languages_id']);
        }
?>
            <tr class="dataTableRow" data-rid="<?php echo $cr_list->fields['restrict_id']; ?>">
                <td class="dataTableContent text-center"><?php echo $cr_list->fields['category_id']; ?></td>
                <td class="dataTableContent text-center"><?php echo $category_name; ?></td>
                <td class="dataTableContent text-center"><?php echo (($cr_list->fields['coupon_restrict'] == 'N') ? $allowed_icon : $denied_icon) . $toggle_button; ?></td>
                <td class="dataTableContent text-center cr-remove"><?php echo $remove_image; ?></td>
            </tr>
<?php
        $cr_list->MoveNext();
    }
?>
            <tr class="smallText">
                <td colspan="2"><?php echo $cr_split->display_count($cr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, $cpage, TEXT_DISPLAY_NUMBER_OF_CATEGORIES); ?></td>
                <td colspan="2" class="text-right"><?php echo $cr_split->display_links($cr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, MAX_DISPLAY_PAGE_LINKS, $cpage, zen_get_all_get_params(array('cpage','action', 'x', 'y')), 'cpage'); ?></td>
            </tr>
<?php
}
?>
            <tr class="smallText text-center">
                <td class="font-weight-bold"><?php echo TABLE_HEADING_CATEGORY_NAME; ?></td>
                <td>
                    <?php echo 
                    zen_draw_form('cat_cpath', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action')), 'get', 'id="cat-path-form"') .
                    zen_draw_pull_down_menu('cPath', zen_get_category_tree(), $cPath, 'id="cat-path"') .
                    zen_draw_hidden_field('cid', $cid) .
                    '</form>'; ?>
                </td>
                <td><?php echo zen_draw_pull_down_menu('restrict_status', $restrict_array, 'Deny', 'id="cat-status"'); ?></td>
                <td><button type="button" id="cat-add-submit"><?php echo TEXT_SUBMIT_CATEGORY_ADD; ?></button></td>
            </tr>
        </table>
    </div>
<?php
$ppage = (isset($_GET['ppage'])) ? (int)$_GET['ppage'] : 1;

$pr_query_raw = "SELECT * FROM " . TABLE_COUPON_RESTRICT . " WHERE coupon_id = $cid AND product_id != '0'";
$pr_split = new splitPageResults($ppage, MAX_DISPLAY_RESTRICT_ENTRIES, $pr_query_raw, $pr_query_numrows);
$pr_list = $db->Execute($pr_query_raw);
?>
    <div class="row">
        <h4><?php echo HEADING_TITLE_PRODUCT; ?></h4>
        <table class="table table-hover">
<?php
if ($pr_list->EOF) {
?>
            <tr class="dataTableHeadingRow">
                <td colspan="6" class="dataTableHeadingContent text-center"><?php echo TEXT_NO_PRODUCT_RESTRICTIONS; ?></td>
            </tr>
<?php
} else {
?>
            <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCT_ID; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent text-left"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent text-left"><?php echo TABLE_HEADING_PRODUCT_NAME; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_RESTRICT; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_RESTRICT_REMOVE; ?></td>
            </tr>
<?php
    $products_status_disabled = zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF);
    $products_status_enabled = zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON);
    while (!$pr_list->EOF) {
        $products_id = $pr_list->fields['product_id'];
        $products_name = zen_get_products_name($products_id, $_SESSION['languages_id']);
        $products_model = htmlspecialchars(zen_get_products_model($products_id), ENT_COMPAT, CHARSET);
        $products_status = htmlspecialchars(zen_get_products_status($products_id), ENT_COMPAT, CHARSET);;
?>
            <tr class="dataTableRow" data-rid="<?php echo $pr_list->fields['restrict_id']; ?>">
                <td class="dataTableContent text-center"><?php echo $products_id; ?></td>
                <td class="dataTableContent text-center"><?php echo (empty($products_status)) ? $products_status_disabled : $products_status_enabled; ?></td>
                <td class="dataTableContent text-left"><?php echo $products_model; ?></td>
                <td class="dataTableContent text-left"><?php echo $products_name; ?></td>
                <td class="dataTableContent text-center"><?php echo (($pr_list->fields['coupon_restrict'] == 'N') ? $allowed_icon : $denied_icon) . $toggle_button; ?></td>
                <td class="dataTableContent text-center cr-remove"><?php echo $remove_image; ?></td>
            </tr>
<?php
        $pr_list->MoveNext();
    }
?>
            <tr class="smallText">
                <td colspan="3"><?php echo $pr_split->display_count($pr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, $ppage, TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td colspan="3" class="text-right"><?php echo $pr_split->display_links($pr_query_numrows, MAX_DISPLAY_RESTRICT_ENTRIES, MAX_DISPLAY_PAGE_LINKS, $ppage, zen_get_all_get_params(array('ppage','action', 'x', 'y')), 'ppage'); ?></td>
            </tr>
<?php
}

$cPath_prod = (isset($_GET['cPath_prod'])) ? (int)$_GET['cPath_prod'] : 0;
$current_manufacturers_id = (isset($_GET['manufacturers_id'])) ? (int)$_GET['manufacturers_id'] : 0;

$manufacturers_array = array(
    array(
        'id' => '0', 
        'text' => TEXT_NONE
    )
);

$manufacturers = $db->Execute(
    "SELECT distinct m.manufacturers_id, m.manufacturers_name
       FROM " . TABLE_MANUFACTURERS . " m
            LEFT JOIN " . TABLE_PRODUCTS . " p 
                ON m.manufacturers_id = p.manufacturers_id
      WHERE p.manufacturers_id = m.manufacturers_id
        AND p.products_status = 1
        AND p.products_quantity > 0
      ORDER BY m.manufacturers_name"
);

while (!$manufacturers->EOF) {
    $manufacturers_array[] = array(
        'id' => $manufacturers->fields['manufacturers_id'],
        'text' => $manufacturers->fields['manufacturers_name'] . ' [ #' . $manufacturers->fields['manufacturers_id'] . ' ]'
    );
    $manufacturers->MoveNext();
}
unset($manufacturers);

if ($current_manufacturers_id > 0) {
    $products = $db->Execute(
        "SELECT p.products_id, pd.products_name 
           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m
          WHERE p.products_id = pd.products_id 
            AND pd.language_id = " . $_SESSION['languages_id'] . "
            AND p.manufacturers_id = m.manufacturers_id 
            AND m.manufacturers_id = $current_manufacturers_id
          ORDER BY pd.products_name, p.products_id"
    );
} else {
    $products = $db->Execute(
        "SELECT p.products_id, pd.products_name 
           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
          WHERE p.products_id = pd.products_id 
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
            AND p.products_id = p2c.products_id 
            AND p2c.categories_id = $cPath_prod
          ORDER BY pd.products_name, p.products_id"
    );
}

$products_array = array();
if (!$products->EOF) {
    if ($cPath_prod > 0) {
        $products_array[] = array(
            'id' => '-1',
            'text' => TEXT_ALL_PRODUCTS_ADD
        );
        $products_array[] = array(
            'id' => '-2',
            'text' => TEXT_ALL_PRODUCTS_REMOVE
        );
    } elseif ($current_manufacturers_id > 0) {
        $products_array[] = array(
            'id' => '-1',
            'text' => TEXT_ALL_MANUFACTURERS_ADD
        );
        $products_array[] = array(
            'id' => '-2',
            'text' => TEXT_ALL_MANUFACTURERS_REMOVE
        );
    }
}

while (!$products->EOF) {
    $products_array[] = array(
        'id' => $products->fields['products_id'],
        'text' => $products->fields['products_name']
    );
    $products->MoveNext();
}
unset($products);
?>
            <tr class="smallText">
                <td><?php echo TABLE_HEADING_CATEGORY_NAME . HEADER_MANUFACTURER_NAME; ?></td>
                <td colspan="2">
                    <?php echo 
                    zen_draw_form('prod-sel', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action')), 'get', 'id="prod-cat-man"') .
                    zen_draw_pull_down_menu('cPath_prod', zen_get_category_tree(), $cPath_prod, 'id="prod-path"') .
                    '<br /><br />' .
                    zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $current_manufacturers_id, 'id="prod-man"') .
                    zen_draw_hidden_field('cid', $cid) .
                    '</form>'; ?>
                </td>
<?php
if (empty($products_array)) {
?>
                <td colspan="3">&nbsp;</td>
<?php
} else {
?>
                <td><?php echo zen_draw_pull_down_menu('pid', $products_array, 0, 'id="prod-pid"'); ?></td>
                <td class="text-center"><?php echo zen_draw_pull_down_menu('restrict_status', $restrict_array, 'Deny', 'id="prod-status"'); ?></td>
                <td class="text-center"><button type="button" id="prod-add-submit"><?php echo TEXT_SUBMIT_PRODUCT_UPDATE; ?></button></td>
<?php
}
?>
            </tr>
            <tr class="smallText">
                <td colspan="6"><?php echo TEXT_INFO_ADD_DENY_ALL; ?></td>
            </tr>
        </table>
    </div>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php 
require DIR_WS_INCLUDES . 'footer.php'; 
?>
<!-- footer_eof //-->
<?php
// -----
// A collection of "helper" forms, used by the page's jQuery (see below).
//
echo 
    zen_draw_form('new-cat', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action', 'page')) . '&action=add_category', 'post') .
    zen_draw_hidden_field('cPath', $cPath) .
    zen_draw_hidden_field('restrict_status', '', 'id="new-cat-restrict"') .
    '</form>';
    
echo 
    zen_draw_form('new-prod', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action', 'page')) . '&action=add_product', 'post') .
    zen_draw_hidden_field('pid', '0', 'id="new-prod-id"') .
    zen_draw_hidden_field('restrict_status', '', 'id="new-prod-restrict"') .
    zen_draw_hidden_field('prod_cat', '0', 'id="new-prod-cat"') .
    zen_draw_hidden_field('prod_man', '0', 'id="new-prod-man"') .
    '</form>';
    
echo
    zen_draw_form('toggle', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action', 'page')) . '&action=switch_status', 'post') .
    zen_draw_hidden_field('rid', '0', 'id="switch-rid"') .
    zen_draw_hidden_field('cid', $cid) .
    '</form>';
    
echo
    zen_draw_form('remove', FILENAME_COUPON_RESTRICT, zen_get_all_get_params(array('action', 'page')) . '&action=remove', 'post') .
    zen_draw_hidden_field('rid', '0', 'id="remove-rid"') .
    zen_draw_hidden_field('cid', $cid) .
    '</form>';
?>
<script>
$(document).ready(function() {
    $('#cat-path').on('change', function(){
        $('#cat-path-form').submit();
    });
    
    $('#cat-add-submit').on('click', function(){
        $('#new-cat-restrict').val($('#cat-status :selected').val());
        $('form[name="new-cat"]').submit();
    });
    
    $('.cr-remove').hover(function(){
        $(this).css('cursor', 'pointer');
    });
    
    $('.cr-remove').on('click', function(){
        $('#remove-rid').val($(this).closest('tr').data('rid'));
        $('form[name="remove"]').submit();
    });
    
    $('.cr-toggle').on('click', function(){
        $('#switch-rid').val($(this).closest('tr').data('rid'));
        $('form[name="toggle"]').submit();
    });
    
    $('#prod-path, #prod-man').on('change', function(){
        $('#prod-cat-man').submit();
    });
    
    $('#prod-add-submit').on('click', function(){
        $('#new-prod-id').val($('#prod-pid :selected').val());
        $('#new-prod-restrict').val($('#prod-status :selected').val());
        $('#new-prod-cat').val($('#prod-path :selected').val());
        $('#new-prod-man').val($('#prod-man :selected').val());
        $('form[name="new-prod"]').submit();
    });
});
</script>
</body>
</html>
<?php 
require DIR_WS_INCLUDES . 'application_bottom.php';
