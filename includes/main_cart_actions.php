<?php
/**
 * Main Shopping Cart actions supported.
 *
 * The main cart actions supported by the shopping_cart class.
 * This can be added to externally using the extra_cart_actions directory.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 23 Modified in v2.0.0-alpha1 $
 */
use Zencart\FileSystem\FileSystem;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * NOTE: the $goto and $parameters variables are set by init_cart_handler.php
 */

/**
 * Gather all PHP files present in the extra_cart_actions subdirectory.
 */
$baseDir = DIR_WS_INCLUDES . 'extra_cart_actions/';
$main_cart_actions = [];
$mca_filesystem = new FileSystem();
$files = $mca_filesystem->listFilesFromDirectoryAlphaSorted(DIR_FS_CATALOG . $baseDir);
foreach ($files as $file) {
    $main_cart_actions[$file] = $baseDir;
}

/**
 * Gather all PHP files present in enabled zc_plugins, noting that any like-named file
 * in one of these directories **overrides** any file present in the 'base' directory!
 */
foreach ($installedPlugins as $plugin) {
    $pluginDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/extra_cart_actions/';
    $files = $mca_filesystem->listFilesFromDirectoryAlphaSorted(DIR_FS_CATALOG . $pluginDir);
    foreach ($files as $file) {
        $main_cart_actions[$file] = $pluginDir;
    }
}

/**
 * Now, load any extra_cart_actions files located ...
 */
foreach ($main_cart_actions as $file => $file_dir) {
    require DIR_FS_CATALOG . $file_dir . $file;
}

switch ($_GET['action']) {
    /**
     * customer wants to update the product quantity in their shopping cart
     * delete checkbox or 0 quantity removes from cart
     */
    case 'update_product' :
        $_SESSION['cart']->actionUpdateProduct($goto, $parameters);
        break;
    /**
     * customer adds a product from the products page
     */
    case 'add_product' :
        $_SESSION['cart']->actionAddProduct($goto, $parameters);
        break;
    case 'buy_now' :
        /**
         * performed by the 'buy now' button in product listings and review page
         */
        $_SESSION['cart']->actionBuyNow($goto, $parameters);
        break;
    case 'multiple_products_add_product' :
        /**
         * performed by the multiple-add-products button
         */
        $_SESSION['cart']->actionMultipleAddProduct($goto, $parameters);
        break;
    case 'notify' :
        $_SESSION['cart']->actionNotify($goto, $parameters);
        break;
    case 'notify_remove' :
        $_SESSION['cart']->actionNotifyRemove($goto, $parameters);
        break;
    case 'cust_order' :
        $_SESSION['cart']->actionCustomerOrder($goto, $parameters);
        break;
    case 'remove_product' :
        $_SESSION['cart']->actionRemoveProduct($goto, $parameters);
        break;
    case 'cart' :
        $_SESSION['cart']->actionCartUserAction($goto, $parameters);
        break;
    case 'empty_cart' :
        $_SESSION['cart']->reset(true);
        break;
}
