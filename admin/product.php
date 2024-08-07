<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Jul 02 Modified in v2.1.0-alpha1 $
 */
require 'includes/application_top.php';

$action = $_GET['action'] ?? '';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$product_type = (int)($_POST['product_type'] ?? $_GET['products_type'] ?? 1);

if (isset($_GET['pID'])) {
    $product_lookup = (new Product((int)$_GET['pID']));
    $product_type = $product_lookup->get('products_type');
    $type_handler = $product_lookup->getTypeHandler() . '.php';
}

if ($action !== 'new_product' && $action !== 'new_product_preview' && $action !== 'insert_product') { 
    if ($product_lookup === null || !$product_lookup->exists()) {
        $messageStack->add_session(sprintf(WARNING_PRODUCT_DOES_NOT_EXIST, (int)($_GET['pID'] ?? 0)), 'warning');
        zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
    }
}

$zco_notifier->notify('NOTIFY_BEGIN_ADMIN_PRODUCTS', $action, $action);

if (!empty($action)) {
    switch ($action) {
        case 'insert_product_meta_tags':
        case 'update_product_meta_tags':
            require zen_get_admin_module_from_directory($product_type, 'update_product_meta_tags.php');
            break;

        case 'insert_product':
        case 'update_product':
            require zen_get_admin_module_from_directory($product_type, 'update_product.php');
            break;

        case 'new_product_preview':
            if (!isset($_POST['master_categories_id'])
                || (($_POST['products_model'] ?? '') . implode('', $_POST['products_url'] ?? []) . implode('', $_POST['products_name'] ?? []) . implode('', $_POST['products_description'] ?? [])) === '')
            {
                $messageStack->add(ERROR_NO_DATA_TO_SAVE, 'error');
                $action = 'new_product';
                break;
            }
            require zen_get_admin_module_from_directory($product_type, 'new_product_preview.php');
            break;

        case 'new_product_preview_meta_tags':
            if (!isset($_POST['products_price_sorter'], $_POST['products_model'])) {
                $messageStack->add(ERROR_NO_DATA_TO_SAVE, 'error');
                $action = 'new_product_meta_tags';
            }
            break;

        default:
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

$tax_class_array = [
    ['id' => '0', 'text' => TEXT_NONE],
];
$tax_class = $db->Execute(
    "SELECT tax_class_id, tax_class_title
       FROM " . TABLE_TAX_CLASS . "
      ORDER BY tax_class_title"
);
foreach ($tax_class as $item) {
    $tax_class_array[] = [
        'id' => $item['tax_class_id'],
        'text' => $item['tax_class_title'],
    ];
}

$languages = zen_get_languages();
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
<?php
require DIR_WS_INCLUDES . 'admin_html_head.php';
if ($action !== 'new_product_meta_tags' && $editor_handler !== '') {
    require $editor_handler;
}
?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <!-- body_text //-->
<?php
if ($action === 'new_product_meta_tags') {
    require zen_get_admin_module_from_directory($product_type, 'collect_info_metatags.php');
} elseif ($action === 'new_product') {
    require zen_get_admin_module_from_directory($product_type, 'collect_info.php');
} elseif ($action === 'new_product_preview_meta_tags') {
    require zen_get_admin_module_from_directory($product_type, 'preview_info_meta_tags.php');
} elseif ($action === 'new_product_preview') {
    require zen_get_admin_module_from_directory($product_type, 'preview_info.php');
}
?>
    <!-- body_text_eof //-->
    <!-- body_eof //-->
    <!-- script for datepicker -->
    <script>
      $(function () {
        $('input[name="products_date_available"]').datepicker({
            minDate: 1
        });
      })
    </script>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
