<?php
/**
 * document_product header_php.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert 2026-07-22 Modified in v2.3.0
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_DOCUMENT_PRODUCT_INFO');

// strict cPath validation
if (isset($_GET['products_id']) && (int)$_GET['products_id'] > 0 && isset($_GET['cPath'])) {
    $true_cPath = zen_validate_product_cpath((int)$_GET['products_id'], $_GET['cPath']);

    if ($true_cPath !== '' && $_GET['cPath'] !== $true_cPath) {
        zen_execute_cpath_redirect($true_cPath, (int)$_GET['products_id']);
    }
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$product_info = new Product($products_id_current = (!empty($_GET['products_id']) ? (int)$_GET['products_id'] : 0));
if ($product_info->exists() && $current_page !== $product_info->get('info_page')) {
    zen_redirect(zen_href_link($product_info->get('info_page'), zen_get_all_get_params()));
}

zen_product_set_header_response($products_id_current, $product_info);

// ensure navigation snapshot is set in order to "go back" in case must-be-logged-in-for-price is enabled
if (!zen_is_logged_in()) {
    $_SESSION['navigation']->set_snapshot();
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_DOCUMENT_PRODUCT_INFO');
