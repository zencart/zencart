<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jun 05 Modified in v2.1.0-alpha1 $
 */
require 'includes/application_top.php';

// verify products exist
$chk_products = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " LIMIT 1");
if ($chk_products->RecordCount() < 1) {
  $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
}

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$products_filter = (isset($_GET['products_filter']) ? (int)$_GET['products_filter'] : 0);

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : 0);
$sql = "SELECT ptc.*
        FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
        LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON ptc.products_id = pd.products_id
          AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
        LEFT join " . TABLE_PRODUCTS . " p ON p.products_id = pd.products_id
        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
        WHERE ptc.categories_id = :category_id
        AND pt.allow_add_to_cart = 'Y'
        ORDER by pd.products_name";

if ($action == 'new_cat') {
  $sql = $db->bindVars($sql, ':category_id', $current_category_id, 'integer');
  $new_product_query = $db->Execute($sql);
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
  zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
}

// set categories and products if not set
if ($products_filter == '' && !empty($current_category_id)) {
  $sql = $db->bindVars($sql, ':category_id', $current_category_id, 'integer');
  $new_product_query = $db->Execute($sql);
  $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
  if ($products_filter != '') {
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }
} else {
  if ($products_filter == '' && empty($current_category_id)) {
    $reset_categories_id = zen_get_category_tree('', '', '0', '', '', true);
    $current_category_id = $reset_categories_id[0]['id'];
    $sql = $db->bindVars($sql, ':category_id', $current_category_id, 'integer');
    $new_product_query = $db->Execute($sql);
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';
    $_GET['products_filter'] = $products_filter;
  }
}

require DIR_WS_MODULES . FILENAME_PREV_NEXT;
if (!empty($action)) {
  switch ($action) {
    case 'delete_special_confirm' :
      if (isset($_POST['product_id'])) {
        $delete_special = $db->Execute("DELETE FROM " . TABLE_SPECIALS . " WHERE products_id = " . (int)$_POST['product_id']);

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_filter);

        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }
      break;

    case 'delete_featured_confirm' :
      if (isset($_POST['product_id'])) {
        $delete_featured = $db->Execute("DELETE FROM " . TABLE_FEATURED . " WHERE products_id = " . (int)$_POST['product_id']);

        zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      }
      break;

    case 'add_discount_qty_id' :
      $add_id_query = $db->Execute("SELECT discount_id
                                    FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                                    WHERE products_id = " . (int)$products_filter . "
                                    ORDER BY discount_id DESC LIMIT 1");
      $add_cnt = 1;
      $add_id = ($add_id_query->EOF) ? 0 : (int)$add_id_query->fields['discount_id'];
      while ($add_cnt <= DISCOUNT_QTY_ADD) {
        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " (discount_id, products_id)
                      VALUES (" . ($add_id + $add_cnt) . ", " . (int)$products_filter . ")");
        $add_cnt++;
      }
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;

    case ('update'):

      if (!empty($_POST['master_category'])) {
        $master_categories_id = $_POST['master_category'];
      } else {
        $master_categories_id = $_POST['master_categories_id'];
      }
// Set date to null if empty or in the past
      $products_date_available = zen_prepare_date($_POST['products_date_available'], 'null');
// Set dates empty equivelent '0001-01-01' if empty or in the past
      $specials_date_available = zen_prepare_date($_POST['specials_date_available'] ?? '', '0001-01-01');
      $specials_expires_date = zen_prepare_date($_POST['specials_expires_date'] ?? '', '0001-01-01');

      $featured_date_available = zen_prepare_date($_POST['featured_date_available'] ?? '','0001-01-01');
      $featured_expires_date = zen_prepare_date($_POST['featured_expires_date'] ?? '', '0001-01-01');

      $tmp_value = (isset($_POST['products_price_sorter']) ? zen_db_prepare_input($_POST['products_price_sorter']) : '');
      $products_price_sorter = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;

      $sql = "UPDATE " . TABLE_PRODUCTS . "
              SET products_price = " . (float)$_POST['products_price'] . ",
                  products_tax_class_id = " . (int)$_POST['products_tax_class_id'] . ",
                  products_date_available = " . ($products_date_available != 'null' ? "'" . $products_date_available . "'" : $products_date_available) . ",
                  products_last_modified = now(),
                  products_status = " . (int)$_POST['products_status'] . ",
                  products_quantity_order_min = " . (float)$_POST['products_quantity_order_min'] . ",
                  products_quantity_order_units = " . (float)$_POST['products_quantity_order_units'] . ",
                  products_quantity_order_max = " . (float)$_POST['products_quantity_order_max'] . ",
                  product_is_free = " . (int)$_POST['product_is_free'] . ",
                  product_is_call = " . (int)$_POST['product_is_call'] . ",
                  products_quantity_mixed = " . (int)$_POST['products_quantity_mixed'] . ",
                  products_priced_by_attribute = " . (int)$_POST['products_priced_by_attribute'] . ",
                  products_discount_type = " . (isset($_POST['products_discount_type']) ? (int)$_POST['products_discount_type'] : 0) . ",
                  products_discount_type_from = " . (isset($_POST['products_discount_type_from']) ? (int)$_POST['products_discount_type_from'] : 0) . ",
                  products_price_sorter = " . (int)$products_price_sorter . ",
                  master_categories_id = " . (int)$master_categories_id . ",
                  products_mixed_discount_quantity = " . (isset($_POST['products_mixed_discount_quantity']) ? (int)$_POST['products_mixed_discount_quantity'] : 0) . "
              WHERE products_id = " . (int)$products_filter;

      $db->Execute($sql);

      if ($_POST['specials_id'] != '') {

        $specials_id = zen_db_prepare_input($_POST['specials_id']);

        if ($_POST['products_priced_by_attribute'] == '1') {
          $products_price = zen_get_products_base_price($products_filter);
        } else {
          $products_price = zen_db_prepare_input($_POST['products_price']);
        }

        $specials_price = zen_db_prepare_input($_POST['specials_price']);
        if (substr($specials_price, -1) == '%') {
          $specials_price = ((float)$products_price - (((float)$specials_price / 100) * (float)$products_price));
        }
        $db->Execute("UPDATE " . TABLE_SPECIALS . "
                      SET specials_new_products_price = " . (float)$specials_price . ",
                          specials_date_available = '" . $specials_date_available . "',
                          specials_last_modified = NOW(),
                          expires_date = '" . $specials_expires_date . "',
                          status = " . (int)$_POST['special_status'] . "
                      WHERE products_id = " . (int)$products_filter);
      }

      if ($_POST['featured_id'] != '') {

        $db->Execute("UPDATE " . TABLE_FEATURED . "
                      SET featured_date_available = '" . $featured_date_available . "',
                          expires_date = '" . $featured_expires_date . "',
                          featured_last_modified = NOW(),
                          status = " . (int)$_POST['featured_status'] . "
                      WHERE products_id = " . (int)$products_filter);
      }

      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id = " . (int)$products_filter);
      $i = 1;
      $new_id = 0;
      $discount_cnt = 0;
      if (!empty($_POST['discount_qty'])) {
        for ($i = 1, $n = sizeof($_POST['discount_qty']); $i <= $n; $i++) {
          if ($_POST['discount_qty'][$i] > 0) {
            $new_id++;
            $db->Execute(
                "INSERT INTO " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                    (discount_id, products_id, discount_qty, discount_price, discount_price_w)
                 VALUES (" .
                    (int)$new_id . ', ' .
                    (int)$products_filter . ",
                    '" . zen_db_input($_POST['discount_qty'][$i]) . "',
                    '" . zen_db_input($_POST['discount_price'][$i]) . "',
                    '" . zen_db_input($_POST['discount_price_w'][$i] ?? '0') . "'
                 )"
            );
            $discount_cnt++;
          }
        }
      }

      if ($discount_cnt <= 0) {
        $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                      SET products_discount_type = 0
                      WHERE products_id = " . (int)$products_filter);
      }

      // reset products_price_sorter for searches etc.
      zen_update_products_price_sorter($products_filter);
      $messageStack->add_session(PRODUCT_UPDATE_SUCCESS, 'success');

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
      break;
    case 'set_products_filter':
      $_GET['products_filter'] = $_POST['products_filter'];

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
      break;

    case 'edit':
      // set edit message
      $messageStack->add_session(PRODUCT_WARNING_UPDATE, 'caution');
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit_update' . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id));
      break;
    case 'cancel':
      // clean up blank discount_qty
      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE discount_qty = 0");
      // set edit message
      $messageStack->add_session(PRODUCT_WARNING_UPDATE_CANCEL, 'warning');
      zen_redirect(zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id));
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
    <div class="container">
      <!-- body //-->
      <div class="row">
        <h1 class="col-sm-8"><?php echo HEADING_TITLE; ?></h1>
        <div class="col-sm-4">
          <?php if (!empty($products_filter) && $products_filter !== 0) { ?>
            <div class="dropdown">
              <button class="btn btn-default dropdown-toggle" type="button" id="menu1" data-toggle="dropdown">
                <?php echo BUTTON_ADDITIONAL_ACTIONS; ?>
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_product_path($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)); ?>"><?php echo IMAGE_EDIT_PRODUCT; ?></a></li>
                <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'NONSSL'); ?>"><?php echo IMAGE_EDIT_ATTRIBUTES; ?></a></li>
                <li role="presentation"><a role="menuitem" href="<?php echo zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter); ?>"><?php echo IMAGE_PRODUCTS_TO_CATEGORIES; ?></a></li>
              </ul>
            </div>
          <?php } ?>
        </div>
      </div>
      <!-- body_text //-->
      <div class="row text-center"><?php echo zen_draw_separator('pixel_black.gif', '90%', '2'); ?></div>
      <?php
      if ($action != 'edit_update') {
        ?>
        <div class="row">
          <?php require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY); ?>
        </div>
        <div class="row">
          <?php echo zen_draw_form('set_products_filter', FILENAME_PRODUCTS_PRICE_MANAGER, 'action=set_products_filter', 'post', 'class="form-horizontal"'); ?>
          <?php echo zen_draw_hidden_field('products_filter', isset($_GET['products_filter']) ? $_GET['products_filter'] : ''); ?>
          <?php echo zen_draw_hidden_field('current_category_id', isset($_GET['current_category_id']) ? $_GET['current_category_id'] : ''); ?>
          <?php
          if (!empty($_GET['products_filter'])) {
            ?>
            <div class="form-group">
              <div class="col-xs-offset-2 col-offset-sm-1 col-xs-7 col-sm-7"><?php echo TEXT_PRODUCT_TO_VIEW; ?></div>
            </div>
            <div class="form-group">
              <div class="col-xs-2 col-sm-1 col-md-1 col-lg-1 text-center">
                <?php
                $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
                echo ($display_priced_by_attributes ? '<span class="text-warning"><strong>' . TEXT_PRICED_BY_ATTRIBUTES . '</strong></span>' . '<br>' : '');
                echo zen_get_products_display_price($_GET['products_filter']) . '<br><br>';
                echo zen_get_products_quantity_min_units_display($_GET['products_filter'], $include_break = true);
                $excluded_products = [];
//  $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");
//  foreach ($not_for_cart as $not_for) {
//    $excluded_products[] = $not_for['products_id'];
//  }
                ?>
              </div>
              <div class="col-xs-8 col-sm-8 col-md-6 col-lg-4 text-center"><?php echo zen_draw_pulldown_products('products_filter', 'class="form-control"', '', true, $_GET['products_filter'], true, true); ?></div>
              <div class="col-xs-2 col-sm-3 col-md-5 col-lg-7">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_DISPLAY; ?></button>
              </div>
            </div>
            <?php
          } // $_GET['products_filter'] != ''
          ?>
          <?php echo '</form>'; ?>
        </div>

        <?php
      } // $action != 'edit_update'
      ?>
      <?php
// show when product is linked
      if  (!empty($products_filter) && $products_filter !== 0 && zen_get_product_is_linked($products_filter) === 'true') {
        ?>
        <div class="row text-center">
          <i class="fa-solid fa-square fa-lg txt-linked" aria-hidden="true" title="<?php echo IMAGE_ICON_LINKED; ?>"></i>&nbsp;&nbsp;<?php echo TEXT_LEGEND_LINKED . '&nbsp;' . zen_get_product_is_linked($products_filter, 'true'); ?>
        </div>
      <?php } ?>
      <?php
// start of attributes display
      if ($products_filter == '') {
        ?>
        <div class="row">
          <h3 class="text-center"><?php echo HEADING_TITLE_PRODUCT_SELECT; ?></h3>
        </div>
      <?php } ?>

      <?php
// only show if allowed in cart
      if (zen_get_products_allow_add_to_cart($products_filter) === 'Y') {

// featured information
        $featuredParameters = [
          'products_id' => '',
          'featured_id' => '',
          'expires_date' => '',
          'featured_date_available' => '',
          'status' => 1,
        ];
        $fInfo = new objectInfo($featuredParameters);

        $productsFeatured = $db->Execute("SELECT p.products_id,
                                                 f.featured_id, f.expires_date, f.featured_date_available, f.status
                                          FROM " . TABLE_PRODUCTS . " p
                                          INNER JOIN " . TABLE_FEATURED . " f USING (products_id)
                                          WHERE f.products_id = " . (int)$_GET['products_filter']);

        if ($productsFeatured->RecordCount() > 0) {
          $fInfo->updateObjectInfo($productsFeatured->fields);
        }

// specials information
        $specialParameters = [
          'products_id' => '',
          'specials_id' => '',
          'specials_new_products_price' => '',
          'expires_date' => '',
          'specials_date_available' => '',
          'status' => 1,
        ];
        $sInfo = new objectInfo($specialParameters);

        $productsSpecial = $db->Execute("SELECT p.products_id,
                                                s.specials_id, s.specials_new_products_price, s.expires_date, s.specials_date_available, s.status
                                         FROM " . TABLE_PRODUCTS . " p
                                         INNER JOIN " . TABLE_SPECIALS . " s USING (products_id)
                                         WHERE s.products_id = " . (int)$_GET['products_filter']);

        if ($productsSpecial->RecordCount() > 0) {
          $sInfo->updateObjectInfo($productsSpecial->fields);
        }

// products information
        $productParameters = [
          'products_id' => '',
          'products_model' => '',
          'products_price' => '',
          'products_date_available' => '',
          'products_tax_class_id' => '',
          'products_quantity_order_min' => '',
          'products_quantity_order_units' => '',
          'products_quantity_order_max' => '',
          'product_is_free' => 0,
          'product_is_call' => 0,
          'products_quantity_mixed' => 0,
          'products_priced_by_attribute' => 0,
          'products_status' => 1,
          'products_discount_type' => '',
          'products_discount_type_from' => '',
          'products_price_sorter' => '',
          'products_name' => '',
          'master_categories_id' => '',
          'products_mixed_discount_quantity' => 1,
        ];
        $pInfo = new objectInfo($productParameters);
        $products = $db->Execute("SELECT p.products_id, p.products_model,
                                         p.products_price, p.products_tax_class_id,
                                         DATE_FORMAT(p.products_date_available, '" . zen_datepicker_format_forsql() . "') AS products_date_available,
                                         p.products_quantity_order_min, products_quantity_order_units, p.products_quantity_order_max,
                                         p.product_is_free, p.product_is_call, p.products_quantity_mixed, p.products_priced_by_attribute, p.products_status,
                                         p.products_discount_type, p.products_discount_type_from, p.products_price_sorter,
                                         pd.products_name,
                                         p.master_categories_id, p.products_mixed_discount_quantity
                                  FROM " . TABLE_PRODUCTS . " p
                                  LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = p.products_id
                                    AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                  WHERE p.products_id = " . (int)$_GET['products_filter']);

        if ($products->RecordCount() > 0) {
          $pInfo->updateObjectInfo($products->fields);
        }

// Product is product discount type - None, Percentage, Actual Price, $$ off
        $discount_type_array = [
          ['id' => 0, 'text' => DISCOUNT_TYPE_DROPDOWN_0],
          ['id' => 1, 'text' => DISCOUNT_TYPE_DROPDOWN_1],
          ['id' => 2, 'text' => DISCOUNT_TYPE_DROPDOWN_2],
          ['id' => 3, 'text' => DISCOUNT_TYPE_DROPDOWN_3],
        ];

// Product is product discount type from price or special
        $discount_type_from_array = [
          ['id' => 0, 'text' => DISCOUNT_TYPE_FROM_DROPDOWN_0],
          ['id' => 1, 'text' => DISCOUNT_TYPE_FROM_DROPDOWN_1,],
        ];

// tax class id
        $tax_class_array = [
          ['id' => 0, 'text' => TEXT_NONE],
        ];

        $tax_classes = $db->Execute("SELECT tax_class_id, tax_class_title
                                     FROM " . TABLE_TAX_CLASS . "
                                     ORDER BY tax_class_title");
        foreach ($tax_classes as $tax_class) {
          $tax_class_array[] = [
            'id' => $tax_class['tax_class_id'],
            'text' => $tax_class['tax_class_title']
          ];
        }
        ?>
        <?php if (isset($pInfo->products_id) && $pInfo->products_id != '') { ?>
          <script>
            let tax_rates = [];
    <?php
    foreach ($tax_class_array as $key => $value) {
      if ($key === 0) {
        continue;
      }
      echo '    tax_rates[' . $value['id'] . '] = ' . zen_get_tax_rate_value($value['id']) . ';' . "\n";
    }
    ?>

            function doRound(x, places) {
              return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
            }

            function getTaxRate() {
              const parameterVal = $('select[name="products_tax_class_id"]').val();
              if ((parameterVal > 0) && (tax_rates[parameterVal] > 0)) {
                return tax_rates[parameterVal];
              } else {
                return 0;
              }
            }
            function updateGross() {
              const taxRate = getTaxRate();
              let grossValue = $('input[name="products_price"]').val();
              if (taxRate > 0) {
                grossValue = grossValue * ((taxRate / 100) + 1);
              }

              $('input[name="products_price_gross"]').val(doRound(grossValue, 4));
            }

            function updateNet() {
              const taxRate = getTaxRate();
              let netValue = document.forms["new_prices"].products_price_gross.value;
              if (taxRate > 0) {
                netValue = netValue / ((taxRate / 100) + 1);
              }

              $('input[name="products_price"]').val(doRound(netValue, 4));
            }
    <?php if (isset($sInfo->products_id) && $sInfo->products_id != '') { ?>
              function updateSpecialsGross() {
                const taxRate = getTaxRate();
                let grossSpecialsValue = document.forms["new_prices"].specials_price.value;
                if (/^\d+(\.\d+)?%$/.test(grossSpecialsValue)) {
                  document.forms["new_prices"].specials_price_gross.value = grossSpecialsValue.slice(0, grossSpecialsValue.length - 1) + "%";
                } else {
                  if (taxRate > 0) {
                    grossSpecialsValue = grossSpecialsValue * ((taxRate / 100) + 1);
                  }

                  document.forms["new_prices"].specials_price_gross.value = doRound(grossSpecialsValue, 4);
                }
              }

              function updateSpecialsNet() {
                const taxRate = getTaxRate();
                let netSpecialsValue = document.forms["new_prices"].specials_price_gross.value;
                if (/^\d+(\.\d+)?%$/.test(netSpecialsValue)) {
                  document.forms["new_prices"].specials_price.value = netSpecialsValue.slice(0, netSpecialsValue.length - 1) + "%";
                } else {
                  if (taxRate > 0) {
                    netSpecialsValue = netSpecialsValue / ((taxRate / 100) + 1);
                  }

                  document.forms["new_prices"].specials_price.value = doRound(netSpecialsValue, 4);
                }
              }
    <?php } ?>
          </script>
        <?php } ?>

        <?php
// auto fix bad or missing products master_categories_id
        if (zen_get_product_is_linked($products_filter) == 'false' and $pInfo->master_categories_id != zen_get_products_category_id($products_filter)) {
          $sql = "UPDATE " . TABLE_PRODUCTS . "
                  SET master_categories_id = " . (int)zen_get_products_category_id($products_filter) . "
                  WHERE products_id = " . (int)$products_filter;
          $db->Execute($sql);
          $pInfo->master_categories_id = zen_get_products_category_id($products_filter);
        }
        ?>

        <?php if (isset($pInfo->products_id) && $pInfo->products_id != '') { ?>
          <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
          <h2><?php echo TEXT_PRODUCT_INFO; ?> #<?php echo $pInfo->products_id; ?>&nbsp;&nbsp;<?php echo $pInfo->products_name; ?>&nbsp;&nbsp;&nbsp;<?php echo TEXT_PRODUCTS_MODEL; ?> <?php echo $pInfo->products_model; ?></h2>
          <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
          <?php
          switch ($action) {
            case 'delete_special':
              ?>
              <table class="table">
                <tr class="pageHeading">
                  <td class="alert text-center"><?php echo TEXT_SPECIALS_CONFIRM_DELETE; ?></td>
                </tr>
                <tr>
                  <td class="main">
                    <?php echo zen_draw_form('delete_special', FILENAME_PRODUCTS_PRICE_MANAGER, 'action=delete_special_confirm' . '&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']); ?>
                    <?php echo zen_draw_hidden_field('product_id', $_GET['products_filter']); ?>
                    <button type="submit" class="btn btn-danger"><?php echo IMAGE_REMOVE_SPECIAL; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                  </td>
                </tr>
              </table>
              <?php break; ?>
            <?php case 'delete_featured': ?>
              <table class="table">
                <tr class="pageHeading">
                  <td class="alert text-center"><?php echo TEXT_FEATURED_CONFIRM_DELETE; ?></td>
                </tr>
                <tr>
                  <td class="main">
                    <?php echo zen_draw_form('delete_featured', FILENAME_PRODUCTS_PRICE_MANAGER, 'action=delete_featured_confirm&products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']); ?>
                    <?php echo zen_draw_hidden_field('product_id', $_GET['products_filter']); ?>
                    <button type="submit" class="btn btn-danger"><?php echo IMAGE_REMOVE_FEATURED; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_GET['current_category_id']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                  </td>
                </tr>
              </table>
              <?php break; ?>
            <?php
            case 'edit':
            case 'edit_upgrade':
            default:
              ?>
              <?php echo zen_draw_form('new_prices', FILENAME_PRODUCTS_PRICE_MANAGER, zen_get_all_get_params(['action', 'info', $_GET['products_filter']]) . 'action=update', 'post', 'onsubmit="return check_dates_ppm(featured_start,FeaturedStartDate.required, featured_end, FeaturedEndDate.required, product_start, ProductStartDate.required);" class="form-horizontal"'); ?>
              <?php
              if ($action == 'edit' || $action == 'edit_update') {
                $readonly = '';
                $jsreadonly = '';
              } else {
                $readonly = " readonly";
                $jsreadonly = " disabled";
              }
              echo zen_draw_hidden_field('products_id', $_GET['products_filter']);
              echo zen_draw_hidden_field('specials_id', isset($sInfo->specials_id) ? $sInfo->specials_id : '');
              echo zen_draw_hidden_field('featured_id', $fInfo->featured_id);
//          echo zen_draw_hidden_field('discounts_list', $discounts_qty);
              ?>

              <?php if ($action == '') { ?>
                <div class="form-group">
                  <div class="col-sm-12 pageHeading text-center">
                    <span class="alert"><?php echo TEXT_INFO_PREVIEW_ONLY; ?></span>
                  </div>
                </div>
              <?php } ?>
              <div class="form-group">
                <div class="col-sm-12 main text-center">
                  <?php if ($action == '') { ?>
                    <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT_PRODUCT; ?></a><br><?php echo TEXT_INFO_EDIT_CAUTION; ?>
                  <?php } else { ?>
                    <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE_PRICE_CHANGES; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=cancel' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a><br><?php echo TEXT_UPDATE_COMMIT; ?>
                  <?php } ?>
                </div>
              </div>
              <div class="row">
                <?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?>
              </div>

              <?php if (zen_get_product_is_linked($products_filter) == 'true') { ?>
                <div class="from-group">
                  <?php echo zen_draw_label(TEXT_MASTER_CATEGORIES_ID, 'master_category', 'class="col-sm-3 control-label"'); ?>
                  <div class="col-sm-9 col-md-6">
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa-solid fa-square fa-lg txt-linked" aria-hidden="true" title="<?php echo IMAGE_ICON_LINKED; ?>"></i></span>
                      <?php echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($products_filter), $pInfo->master_categories_id, 'class="form-control" id="master_category"'); ?>
                    </div>
                    <span class="help-block"><?php echo TEXT_INFO_MASTER_CATEGORIES_ID; ?></span>
                  </div>
                </div>
                <div class="col-sm-12 text-center"><?php echo ($action == '' ? '<span class="alert">' . TEXT_INFO_PREVIEW_ONLY . '</span>' : TEXT_INFO_UPDATE_REMINDER); ?></div>
              <?php } ?>

              <?php if (zen_get_product_is_linked($products_filter) == 'false' and $pInfo->master_categories_id != zen_get_products_category_id($products_filter)) { ?>
                <div class="col-sm-12">
                  <span class="alert"><?php echo sprintf(TEXT_INFO_MASTER_CATEGORIES_ID_WARNING, $pInfo->master_categories_id, zen_get_products_category_id($products_filter)); ?></span>
                  <br><strong><?php echo sprintf(TEXT_INFO_MASTER_CATEGORIES_ID_UPDATE_TO_CURRENT, $pInfo->master_categories_id, zen_get_products_category_id($products_filter)); ?></strong>
                </div>
              <?php } ?>
              <?php echo zen_draw_hidden_field('master_categories_id', $pInfo->master_categories_id); ?>
              <div class="col-sm-12">
                <div class="well" style="color: #31708f;background-color: #d9edf7;border-color: #bce8f1;;padding: 10px 10px 0 0;">
                  <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PRODUCTS_TAX_CLASS, 'products_tax_class_id', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'class="form-control" id="products_tax_class_id"'); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="well" style="color: #31708f;background-color: #d9edf7;border-color: #bce8f1;;padding: 10px 10px 0 0;">
                  <div class="col-sm-12"><?php echo TEXT_PRODUCTS_PRICE_INFO; ?></div>
                  <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PRICE_NET, 'products_price', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_input_field('products_price', (isset($pInfo->products_price) ? $pInfo->products_price : ''), 'OnKeyUp="updateGross()" class="form-control" id="products_price"' . $readonly); ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PRICE_GROSS, 'products_price_gross', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_input_field('products_price_gross', (isset($pInfo->products_price) ? $pInfo->products_price : ''), 'OnKeyUp="updateNet()" class="form-control" id="products_price_gross"' . $readonly); ?>
                    </div>
                  </div>
                </div>
              </div>
              <script>
                updateGross();
              </script>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_AVAILABLE_DATE, 'products_date_available', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                  <div class="date input-group" id="datepicker_products_date_available">
                    <span class="input-group-addon datepicker_icon">
                      <?php echo zen_icon('calendar-days', size: 'lg') ?>
                    </span>
                    <?php echo zen_draw_input_field('products_date_available', (($pInfo->products_date_available == '0001-01-01') ? '' : $pInfo->products_date_available), 'class="form-control" id="products_date_available" autocomplete="off"' . $readonly); ?>
                  </div>
                  <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9 col-md-6">
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_status', '1', $pInfo->products_status == '1', '', $jsreadonly) . TEXT_PRODUCT_AVAILABLE; ?></label>
                  </div>
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_status', '0', $pInfo->products_status == '0', '', $jsreadonly) . TEXT_PRODUCT_NOT_AVAILABLE; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MIN_RETAIL, 'products_quantity_order_min', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min == 0 ? 1 : $pInfo->products_quantity_order_min), 'size="6" class="form-control" id="products_quantity_order_min"' . $readonly); ?>
                </div>
              </div>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL, 'products_quantity_order_units', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units == 0 ? 1 : $pInfo->products_quantity_order_units), 'size="6" class="form-control" id="products_quantity_order_units"' . $readonly); ?>
                </div>
              </div>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MAX_RETAIL, 'products_quantity_order_max', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_input_field('products_quantity_order_max', $pInfo->products_quantity_order_max, 'size="6" class="form-control" id="products_quantity_order_max"' . $readonly); ?><span class="help-block"><?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?></span>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-3">
                  <p class="control-label"><?php echo TEXT_PRODUCTS_MIXED; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_quantity_mixed', '1', $pInfo->products_quantity_mixed == 1, '', $jsreadonly) . TEXT_YES; ?></label>
                  </div>
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_quantity_mixed', '0', $pInfo->products_quantity_mixed == 0, '', $jsreadonly) . TEXT_NO; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-3">
                  <p class="control-label"><?php echo TEXT_PRODUCT_IS_FREE; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('product_is_free', '1', ($pInfo->product_is_free == 1), '', $jsreadonly) . TEXT_YES; ?></label>
                  </div>
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('product_is_free', '0', ($pInfo->product_is_free == 0), '', $jsreadonly) . TEXT_NO; ?></label>
                  </div>
                  <?php echo ($pInfo->product_is_free == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</span>' : ''); ?>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-3">
                  <p class="control-label"><?php echo TEXT_PRODUCT_IS_CALL; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('product_is_call', '1', ($pInfo->product_is_call == 1), '', $jsreadonly) . TEXT_YES; ?></label>
                  </div>
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('product_is_call', '0', ($pInfo->product_is_call == 0), '', $jsreadonly) . TEXT_NO; ?></label>
                  </div>
                  <?php echo ($pInfo->product_is_call == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</span>' : ''); ?>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-3">
                  <p class="control-label"><?php echo TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES; ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_priced_by_attribute', '1', $pInfo->products_priced_by_attribute == 1, '', $jsreadonly) . TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE; ?></label>
                  </div>
                  <div class="radio-inline">
                    <label><?php echo zen_draw_radio_field('products_priced_by_attribute', '0', $pInfo->products_priced_by_attribute == 0, '', $jsreadonly) . TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE; ?></label>
                  </div>
                  <?php echo ($pInfo->products_priced_by_attribute == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</span>' : ''); ?>
                </div>
              </div>
              <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
              <?php if (isset($sInfo->products_id) && $sInfo->products_id != '') { ?>
                <div class="col-sm-12">
                  <div class="well" style="color: #31708f;background-color: #ebebff;border-color: #bce8f1;;padding: 10px 10px 0 0;">
                    <div class="col-sm-12"><?php echo TEXT_SPECIALS_PRODUCT_INFO; ?></div>
                    <div class="form-group">
                      <?php echo zen_draw_label(TEXT_SPECIALS_SPECIAL_PRICE_NET, 'specials_price', 'class="col-sm-3 control-label"'); ?>
                      <div class="col-sm-9 col-md-6">
                        <?php echo zen_draw_input_field('specials_price', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : ''), 'OnKeyUp="updateSpecialsGross()" class="form-control" id="specials_price"' . $readonly); ?>
                      </div>
                    </div>
                    <div class="form-group">
                      <?php echo zen_draw_label(TEXT_SPECIALS_SPECIAL_PRICE_GROSS, 'specials_price_gross', 'class="col-sm-3 control-label"'); ?>
                      <div class="col-sm-9 col-md-6">
                        <?php echo zen_draw_input_field('specials_price_gross', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : ''), 'OnKeyUp="updateSpecialsNet()" class="form-control" id="specials_price_gross"' . $readonly); ?>
                      </div>
                    </div>
                  </div>
                </div>
                <script>
                  updateSpecialsGross();
                </script>
                <div class="form-group">
                  <?php echo zen_draw_label(TEXT_AVAILABLE_DATE, 'specials_date_available', 'class="col-sm-3 control-label"'); ?>
                  <div class="col-sm-9 col-md-6">
                    <div class="date input-group" id="datepicker_specials_date_available">
                      <span class="input-group-addon datepicker_icon">
                        <?php echo zen_icon('calendar-days', size: 'lg') ?>
                      </span>
                      <?php echo zen_draw_input_field('specials_date_available', (($sInfo->specials_date_available == '0001-01-01') ? '' : $sInfo->specials_date_available), 'class="form-control" id="specials_date_available" autocomplete="off"' . $readonly); ?>
                    </div>
                    <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
                  </div>
                </div>
                <div class="form-group">
                  <?php echo zen_draw_label(TEXT_SPECIALS_EXPIRES_DATE, 'specials_expires_date', 'class="col-sm-3 control-label"'); ?>
                  <div class="col-sm-9 col-md-6">
                    <div class="date input-group" id="datepicker_specials_expires_date">
                      <span class="input-group-addon datepicker_icon">
                        <?php echo zen_icon('calendar-days', size: 'lg') ?>
                      </span>
                      <?php echo zen_draw_input_field('specials_expires_date', (($sInfo->expires_date == '0001-01-01') ? '' : $sInfo->expires_date), 'class="form-control" id="specials_expires_date" autocomplete="off"' . $readonly); ?>
                    </div>
                    <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-3">
                    <p class="control-label"><?php echo TEXT_SPECIALS_PRODUCTS_STATUS; ?></p>
                  </div>
                  <div class="col-sm-9 col-md-6">
                    <div class="radio-inline">
                      <label><?php echo zen_draw_radio_field('special_status', '1', $sInfo->status == 1, '', $jsreadonly) . TEXT_SPECIALS_PRODUCT_AVAILABLE; ?></label>
                    </div>
                    <div class="radio-inline">
                      <label><?php echo zen_draw_radio_field('special_status', '0', $sInfo->status == 0, '', $jsreadonly) . TEXT_SPECIALS_PRODUCT_NOT_AVAILABLE; ?></label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9 col-md-6 text-center">
                    <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id . '&action=delete_special'); ?>" class="btn btn-warning" role="button"><?php echo IMAGE_REMOVE_SPECIAL; ?></a>
                  </div>
                </div>
                <?php if ($sInfo->status == 0) { ?>
                  <div class="col-sm-12">
                    <span class="errorText"><?php echo TEXT_SPECIAL_DISABLED; ?></span>
                  </div>
                <?php } ?>
                <div class="col-sm-12">
                  <?php echo TEXT_SPECIALS_PRICE_TIP; ?>
                </div>
              <?php } else { ?>
                <div class="form-group">
                    <div class="control-label col-sm-3"><?php echo TEXT_SPECIALS_PRODUCT_INFO; ?></div>
                  <div class="col-sm-9 col-md-6 text-center">
                    <?php
// Specials cannot be added to Gift Vouchers when false
                    // prevent log on null
                    if (empty($pInfo->products_model)) {
                        $pInfo->products_model = '';
                    }
                    if ((substr($pInfo->products_model, 0, 4) != 'GIFT') || (substr($pInfo->products_model, 0, 4) == 'GIFT' && (defined('MODULE_ORDER_TOTAL_GV_SPECIAL') && MODULE_ORDER_TOTAL_GV_SPECIAL == 'true'))) {
                      ?>
                      <a href="<?php echo zen_href_link(FILENAME_SPECIALS, 'add_products_id=' . $_GET['products_filter'] . '&action=new' . (isset($sInfo->specials_id) ? '&sID=' . $sInfo->specials_id : '') . '&go_back=ON' . '&current_category_id=' . $current_category_id); ?>" class="btn btn-info" role="button"><i class="fa-solid fa-plus"></i> <?php echo IMAGE_INSTALL_SPECIAL; ?></a>
                    <?php } else { ?>
                      <?php echo TEXT_SPECIALS_NO_GIFTS; ?>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
              <?php if (isset($fInfo->products_id) && $fInfo->products_id != '') { ?>
                <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
                <div class="col-sm-12"><?php echo TEXT_FEATURED_PRODUCT_INFO; ?></div>
                <div class="form-group">
                  <?php echo zen_draw_label(TEXT_AVAILABLE_DATE, 'featured_date_available', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6">
                    <div class="date input-group" id="datepicker_featured_date_available">
                      <span class="input-group-addon datepicker_icon">
                        <?php echo zen_icon('calendar-days', size: 'lg') ?>
                      </span>
                      <?php echo zen_draw_input_field('featured_date_available', (($fInfo->featured_date_available == '0001-01-01') ? '' : $fInfo->featured_date_available), 'class="form-control" id="featured_date_available" autocomplete="off"' . $readonly); ?>
                    </div>
                    <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
                  </div>
                </div>
                <div class="form-group">
                  <?php echo zen_draw_label(TEXT_FEATURED_EXPIRES_DATE, 'featured_expires_date', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6">
                    <div class="date input-group" id="datepicker_expires_date">
                      <span class="input-group-addon datepicker_icon">
                        <?php echo zen_icon('calendar-days', size: 'lg') ?>
                      </span>
                      <?php echo zen_draw_input_field('featured_expires_date', (($fInfo->expires_date == '0001-01-01') ? '' : $fInfo->expires_date), 'class="form-control" id="featured_expires_date" autocomplete="off"' . $readonly); ?>
                    </div>
                    <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)</span>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-md-3">
                    <p class="control-label"><?php echo TEXT_FEATURED_PRODUCTS_STATUS; ?></p>
                  </div>
                  <div class="col-sm-9 col-md-6">
                    <div class="radio-inline">
                      <label><?php echo zen_draw_radio_field('featured_status', '1', $fInfo->status == 1, '', $jsreadonly) . TEXT_FEATURED_PRODUCT_AVAILABLE; ?></label>
                    </div>
                    <div class="radio-inline">
                      <label><?php echo zen_draw_radio_field('featured_status', '0', $fInfo->status == 0, '', $jsreadonly) . TEXT_FEATURED_PRODUCT_NOT_AVAILABLE; ?></label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9 col-md-6 text-center">
                    <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $current_category_id . '&action=delete_featured'); ?>" class="btn btn-warning" role="button"><?php echo IMAGE_REMOVE_FEATURED; ?></a>
                  </div>
                </div>
                <?php if ($fInfo->status == 0) { ?>
                  <div class="col-sm-12">
                    <span class="errorText"><?php echo TEXT_FEATURED_DISABLED; ?></span>
                  </div>
                <?php } ?>
              <?php } else { ?>
                <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
                <div class="form-group">
                    <div class="control-label col-sm-3"><?php echo TEXT_FEATURED_PRODUCT_INFO; ?></div>
                  <div class="col-sm-9 col-md-6 text-center">
                    <a href="<?php echo zen_href_link(FILENAME_FEATURED, 'add_products_id=' . $_GET['products_filter'] . '&go_back=ON' . '&action=new' . '&current_category_id=' . $current_category_id); ?>" class="btn btn-info" role="button"><i class="fa-solid fa-plus"></i> <?php echo IMAGE_INSTALL_FEATURED; ?></a>
                  </div>
                </div>
              <?php } ?>
              <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
              <?php
              $discounts_qty = $db->Execute("SELECT *
                                             FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                                             WHERE products_id = " . (int)$products_filter . "
                                             ORDER BY discount_qty");
              $discount_cnt = $discounts_qty->RecordCount();

              if ($discounts_qty->RecordCount() > 0) {
                $i = 0;
                $discount_name = [];
                foreach ($discounts_qty as $discount_qty) {
                  $i++;
                  $discount_name[] = [
                    'id' => $i,
                    'discount_qty' => $discount_qty['discount_qty'],
                    'discount_price' => $discount_qty['discount_price'],
                    'discount_price_w' => $discount_qty['discount_price_w'],
                  ];
                }
                ?>

                <div class="well" style="color: #31708f;background-color: #d9edf7;border-color: #bce8f1;;padding: 10px 10px 0 0;">
                  <div class="col-sm-12"><?php echo TEXT_DISCOUNT_TYPE_INFO; ?></div>
                  <div class="form-group">
                    <div class="col-sm-3">
                      <p class="control-label"><?php echo TEXT_PRODUCTS_MIXED_DISCOUNT_QUANTITY; ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                      <div class="radio-inline">
                        <label><?php echo zen_draw_radio_field('products_mixed_discount_quantity', '1', $pInfo->products_mixed_discount_quantity == 1, '', $jsreadonly) . TEXT_YES; ?></label>
                      </div>
                      <div class="radio-inline">
                        <label><?php echo zen_draw_radio_field('products_mixed_discount_quantity', '0', $pInfo->products_mixed_discount_quantity == 0, '', $jsreadonly) . TEXT_NO; ?></label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <?php echo zen_draw_label(TEXT_DISCOUNT_TYPE, 'products_discount_type', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_pull_down_menu('products_discount_type', $discount_type_array, $pInfo->products_discount_type, 'class="form-control" id="products_discount_type"' . $readonly); ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <?php echo zen_draw_label(TEXT_DISCOUNT_TYPE_FROM, 'products_discount_type_from', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_pull_down_menu('products_discount_type_from', $discount_type_from_array, $pInfo->products_discount_type_from, 'class="form-control" id="products_discount_type_from"' . $readonly); ?>
                    </div>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th class="main"><?php echo TEXT_PRODUCTS_DISCOUNT_QTY_TITLE; ?></th>
                        <th class="main"><?php echo TEXT_PRODUCTS_DISCOUNT_QTY; ?></th>
                        <th class="main"><?php echo TEXT_PRODUCTS_DISCOUNT_PRICE; ?></th>
<?php
                        if (WHOLESALE_PRICING_CONFIG !== 'false') {
?>
                        <th class="main">
                            <?php echo TEXT_PRODUCTS_DISCOUNT_PRICE_W; ?>
                            <a href="#" class="pop-help" data-toggle="popover" data-placement="top" title="<?php echo HELPTEXT_WHOLESALE_POPUP_TITLE; ?>" data-content="<?php echo HELPTEXT_WHOLESALE_PRICES; ?>">
                                <i class="fa-solid fa-circle-info"></i>
                            </a>
                        </th>
<?php
                        }
?>
                        <?php
                        if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
                          ?>
                          <th class="main text-right"><?php echo TEXT_PRODUCTS_DISCOUNT_PRICE_EACH_TAX; ?></th>
                          <th class="main text-right"><?php echo TEXT_PRODUCTS_DISCOUNT_PRICE_EXTENDED_TAX; ?></th>
                        <?php } else { ?>
                          <th class="main text-right"><?php echo TEXT_PRODUCTS_DISCOUNT_PRICE_EACH; ?></th>
                          <th class="main text-right"><?php echo TEXT_PRODUCTS_DISCOUNT_PRICE_EXTENDED; ?></th>
                        <?php } ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
                      $display_price = zen_get_products_base_price($_GET['products_filter']);
                      $display_specials_price = zen_get_products_special_price($_GET['products_filter'], false);
//  $display_sale_price = zen_get_products_special_price($_GET['products_filter'], false);

                      for ($i = 0, $n = sizeof($discount_name); $i < $n; $i++) {
                        switch ($pInfo->products_discount_type) {
                          // none
                          case '0':
                            $discounted_price = 0;
                            break;
                          // percentage discount
                          case '1':
                            if ($pInfo->products_discount_type_from == '0') {
                              $discounted_price = $display_price - ($display_price * ($discount_name[$i]['discount_price'] / 100));
                            } else {
                              if (!$display_specials_price) {
                                $discounted_price = $display_price - ($display_price * ($discount_name[$i]['discount_price'] / 100));
                              } else {
                                $discounted_price = $display_specials_price - ($display_specials_price * ($discount_name[$i]['discount_price'] / 100));
                              }
                            }

                            break;
                          // actual price
                          case '2':
                            if ($pInfo->products_discount_type_from == '0') {
                              // @todo: Why is this if statement??
                              $discounted_price = $discount_name[$i]['discount_price'];
                            } else {
                              $discounted_price = $discount_name[$i]['discount_price'];
                            }
                            break;
                          // amount offprice
                          case '3':
                            if ($pInfo->products_discount_type_from == '0') {
                              $discounted_price = $display_price - $discount_name[$i]['discount_price'];
                            } else {
                              if (!$display_specials_price) {
                                $discounted_price = $display_price - $discount_name[$i]['discount_price'];
                              } else {
                                $discounted_price = $display_specials_price - $discount_name[$i]['discount_price'];
                              }
                            }
                            break;
                        }
                        ?>
                        <tr>
                          <td class="main"><?php echo TEXT_PRODUCTS_DISCOUNT . ' ' . $discount_name[$i]['id']; ?></td>
                          <td class="main"><?php echo zen_draw_input_field('discount_qty[' . $discount_name[$i]['id'] . ']', $discount_name[$i]['discount_qty'], 'class="form-control"' . $readonly); ?></td>
                          <td class="main"><?php echo zen_draw_input_field('discount_price[' . $discount_name[$i]['id'] . ']', $discount_name[$i]['discount_price'], 'class="form-control"' . $readonly); ?></td>
<?php
                        if (WHOLESALE_PRICING_CONFIG !== 'false') {
?>
                          <td class="main"><?php echo zen_draw_input_field('discount_price_w[' . $discount_name[$i]['id'] . ']', $discount_name[$i]['discount_price_w'], 'class="form-control"' . $readonly); ?></td>
<?php
                        }
?>
                          <?php
                          if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
                            ?>
                            <td class="main text-right"><?php echo $currencies->display_price($discounted_price, '', 1) . ' ' . $currencies->display_price($discounted_price, zen_get_tax_rate(1), 1); ?></td>
                            <td class="main text-right"><?php echo ' x ' . number_format($discount_name[$i]['discount_qty']) . ' = ' . $currencies->display_price($discounted_price, '', $discount_name[$i]['discount_qty']) . ' ' . $currencies->display_price($discounted_price, zen_get_tax_rate(1), $discount_name[$i]['discount_qty']); ?></td>
                          <?php } else { ?>
                            <td class="main text-right"><?php echo $currencies->display_price($discounted_price, '', 1); ?></td>
                            <td class="main text-right"><?php echo ' x ' . number_format($discount_name[$i]['discount_qty']) . ' = ' . $currencies->display_price($discounted_price, '', $discount_name[$i]['discount_qty']); ?></td>
                          <?php } ?>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>


              <?php if ($action == 'edit_update') { ?>
                <div class="form-group">
                  <div class="control-label col-sm-3"><?php echo TEXT_ADD_ADDITIONAL_DISCOUNT; ?></div>
                  <div class="col-sm-9 col-md-6 text-center">
                    <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&action=add_discount_qty_id'); ?>" class="btn btn-info" role="button"><?php echo IMAGE_ADD_BLANK_DISCOUNTS; ?></a>
                    <span class="help-block"><?php echo TEXT_BLANKS_INFO; ?></span>
                  </div>
                </div>
              <?php } else { ?>
                <?php if (empty($discount_name)) { ?>
                  <div class="col-sm-12"><?php echo TEXT_INFO_NO_DISCOUNTS; ?></div>
                <?php } ?>
              <?php } ?>
              <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
              <?php if ($action == '') { ?>
                <div class="form-group">
                  <div class="col-sm-12">
                    <div class="pageHeading text-center">
                      <span class="alert"><?php echo TEXT_INFO_PREVIEW_ONLY; ?></span>
                    </div>
                  </div>
                </div>
              <?php } ?>
              <div class="form-group">
                <div class="col-sm-12">
                  <div class="main text-center">
                    <?php if ($action == '') { ?>
                      <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT_PRODUCT; ?></a><br><?php echo TEXT_INFO_EDIT_CAUTION; ?>
                    <?php } else { ?>
                      <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE_PRICE_CHANGES; ?></button> <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=cancel' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a><br><?php echo TEXT_UPDATE_COMMIT; ?>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>
              <?php echo '</form>'; ?>
              <?php if ($action == 'edit' || $action == 'edit_update') { ?>
                <!-- script for datepicker -->
                <script>
                  $(function () {
                    $('#products_date_available').datepicker({
                        minDate: 1
                    });
                    $('#specials_date_available').datepicker({
                        minDate: 0
                    });
                    $('#specials_expires_date').datepicker({
                        minDate: 1
                    });
                    $('#featured_date_available').datepicker({
                        minDate: 0
                    });
                    $('#featured_expires_date').datepicker({
                        minDate: 1
                    });
                  });
                </script>
                <?php
              }
              break;
          }
        }
      }
      ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <script>
        $('[data-toggle="popover"]').popover();
        $('a.pop-help').click(function(e) {
            e.preventDefault();
        });
    </script>
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
