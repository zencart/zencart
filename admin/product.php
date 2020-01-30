<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jan 04 Modified in v1.5.6a $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
$product_type = (isset($_POST['product_type']) ? $_POST['product_type'] : (isset($_GET['pID']) ? zen_get_products_type($_GET['pID']) : 1));
$type_handler = $zc_products->get_admin_handler($product_type);
$zco_notifier->notify('NOTIFY_BEGIN_ADMIN_PRODUCTS', $action);

if (zen_not_null($action)) {
  switch ($action) {

    case 'insert_product_meta_tags':
    case 'update_product_meta_tags':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php');
      } else {
        require(DIR_WS_MODULES . 'update_product_meta_tags.php');
      }
      break;
    case 'insert_product':
    case 'update_product':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php');
      } else {
        require(DIR_WS_MODULES . 'update_product.php');
      }
      break;
    case 'new_product_preview':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php');
      } else {
        require(DIR_WS_MODULES . 'new_product_preview.php');
      }
      break;
  }
}

// check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
  if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  }
} else {
  $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}
$tax_class_array = array(array(
    'id' => '0',
    'text' => TEXT_NONE));
$tax_class = $db->Execute("SELECT tax_class_id, tax_class_title
                           FROM " . TABLE_TAX_CLASS . "
                           ORDER BY tax_class_title");
foreach ($tax_class as $item) {
  $tax_class_array[] = array(
    'id' => $item['tax_class_id'],
    'text' => $item['tax_class_title']);
}

$languages = zen_get_languages();
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <script src="includes/general.js"></script>
    <script>
      let tax_rates = [];
<?php
for ($i = 0, $n = sizeof($tax_class_array); $i < $n; $i++) {
  if ($tax_class_array[$i]['id'] > 0) {
    echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
  }
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
        let netValue = $('input[name="products_price_gross"]').val();

        if (taxRate > 0) {
          netValue = netValue / ((taxRate / 100) + 1);
        }

        $('input[name="products_price"]').val(doRound(netValue, 4));
      }
    </script>
    <?php
    if ($action != 'new_product_meta_tags' && $editor_handler != '') {
      include ($editor_handler);
    }
    ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <!-- body_text //-->
    <?php
    if ($action == 'new_product_meta_tags') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info_metatags.php');
    } elseif ($action == 'new_product') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info.php');
    } elseif ($action == 'new_product_preview_meta_tags') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info_meta_tags.php');
    } elseif ($action == 'new_product_preview') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info.php');
    }
    ?>
    <!-- body_text_eof //-->
    <!-- body_eof //-->
    <!-- script for datepicker -->
    <script>
      $(function () {
        $('input[name="products_date_available"]').datepicker();
      })
    </script>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
