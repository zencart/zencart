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
      /*
    case 'new_product_preview_meta_tags':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview_meta_tags.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview_meta_tags.php');
      } else {
        require(DIR_WS_MODULES . 'new_product_preview_meta_tags.php');
      }
      break;
      */
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
    <?php
    if ($action != 'new_product_meta_tags' && $editor_handler != '') {
      include ($editor_handler);
    }
    ?>
  </head>
  <body onload="init();">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <!-- body_text //-->
    <?php
    // echo DIR_WS_MODULES . $zc_products->get_handler($product_type);
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
      $(function(){
        $('input[name="products_date_available"]').datepicker();
      })
    </script>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
