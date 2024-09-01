<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Jeff Rutt 2024 Aug 07 Modified in v2.1.0-alpha2 $
 */
/** @var \Aura\Autoload\Loader $psr4Autoloader */
$psr4Autoloader->addPrefix('Zencart\QueryBuilder', DIR_FS_CATALOG . DIR_WS_CLASSES);
$psr4Autoloader->addPrefix('Zencart\Traits', DIR_FS_CATALOG . DIR_WS_CLASSES . 'traits');
$psr4Autoloader->addPrefix('Zencart\FileSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\InitSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\PluginManager', DIR_FS_CATALOG . DIR_WS_CLASSES);
$psr4Autoloader->addPrefix('Zencart\LanguageLoader', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\ResourceLoaders', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\PageLoader', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\Events', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\PluginSupport', DIR_FS_CATALOG . DIR_WS_CLASSES . 'PluginSupport');
$psr4Autoloader->addPrefix('Zencart\ViewBuilders', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ViewBuilders');
$psr4Autoloader->addPrefix('Zencart\Exceptions', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Exceptions');
$psr4Autoloader->addPrefix('Zencart\Filters', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Filters');
$psr4Autoloader->addPrefix('Zencart\Request', DIR_FS_CATALOG . DIR_WS_CLASSES);

// -----
// Admin-only classes
//
if (defined('DIR_FS_ADMIN')) {
    $psr4Autoloader->addPrefix('Zencart\Paginator', DIR_FS_ADMIN . DIR_WS_CLASSES);

    $psr4Autoloader->setClassFile('box', DIR_FS_ADMIN . DIR_WS_CLASSES . 'box.php');
    $psr4Autoloader->setClassFile('boxTableBlock', DIR_FS_ADMIN . DIR_WS_CLASSES . 'table_block.php');
    $psr4Autoloader->setClassFile('configurationValidation', DIR_FS_ADMIN . DIR_WS_CLASSES . 'configurationValidation.php');
    $psr4Autoloader->setClassFile('messageStack', DIR_FS_ADMIN . DIR_WS_CLASSES . 'message_stack.php');
    $psr4Autoloader->setClassFile('objectInfo', DIR_FS_ADMIN . DIR_WS_CLASSES . 'object_info.php');
    $psr4Autoloader->setClassFile('products', DIR_FS_CATALOG . DIR_WS_CLASSES . 'products.php');    //- Deprecated v2.1.0
    $psr4Autoloader->setClassFile('VersionServer', DIR_FS_ADMIN . DIR_WS_CLASSES . 'VersionServer.php');
    $psr4Autoloader->setClassFile('WhosOnline', DIR_FS_ADMIN . DIR_WS_CLASSES . 'WhosOnline.php');
// -----
// Storefront-only classes
//
} else {
    $psr4Autoloader->setClassFile('breadcrumb', DIR_FS_CATALOG . DIR_WS_CLASSES . 'breadcrumb.php');
    $psr4Autoloader->setClassFile('currencies', DIR_FS_CATALOG . DIR_WS_CLASSES . 'currencies.php');
    $psr4Autoloader->setClassFile('messageStack', DIR_FS_CATALOG . DIR_WS_CLASSES . 'message_stack.php');
    $psr4Autoloader->setClassFile('navigationHistory', DIR_FS_CATALOG . DIR_WS_CLASSES . 'navigation_history.php');
    $psr4Autoloader->setClassFile('template_func', DIR_FS_CATALOG . DIR_WS_CLASSES . 'template_func.php');
    $psr4Autoloader->setClassFile('Zencart\Search\Search', DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.search.php');
    $psr4Autoloader->setClassFile('Zencart\Search\SearchOptions', DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.search.php');
}

// -----
// Common admin/storefront classes
//
$psr4Autoloader->setClassFile('category_tree', DIR_FS_CATALOG . DIR_WS_CLASSES . 'category_tree.php');
$psr4Autoloader->setClassFile('Coupon', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Coupon.php');
$psr4Autoloader->setClassFile('CouponValidation', DIR_FS_CATALOG . DIR_WS_CLASSES . 'CouponValidation.php');
$psr4Autoloader->setClassFile('Customer', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Customer.php');
$psr4Autoloader->setClassFile('language', DIR_FS_CATALOG . DIR_WS_CLASSES . 'language.php');
$psr4Autoloader->setClassFile('MeasurementUnits', DIR_FS_CATALOG . DIR_WS_CLASSES . 'MeasurementUnits.php');
$psr4Autoloader->setClassFile('notifier', DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');
$psr4Autoloader->setClassFile('Product', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Product.php');
$psr4Autoloader->setClassFile('Settings', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Settings.php');
$psr4Autoloader->setClassFile('shoppingCart', DIR_FS_CATALOG . DIR_WS_CLASSES . 'shopping_cart.php');
$psr4Autoloader->setClassFile('sniffer', DIR_FS_CATALOG . DIR_WS_CLASSES . 'sniffer.php');
$psr4Autoloader->setClassFile('TemplateSettings', DIR_FS_CATALOG . DIR_WS_CLASSES . 'TemplateSettings.php');
$psr4Autoloader->setClassFile('upload', DIR_FS_CATALOG . DIR_WS_CLASSES . 'upload.php');
$psr4Autoloader->setClassFile('zcDate', DIR_FS_CATALOG . DIR_WS_CLASSES . 'zcDate.php');
$psr4Autoloader->setClassFile('zcPassword', DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
$psr4Autoloader->setClassFile('ZenShipping', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ZenShipping.php');
$psr4Autoloader->setClassFile('Zencart\SessionHandler', DIR_FS_CATALOG . DIR_WS_CLASSES . 'SessionHandler.php' );
$psr4Autoloader->setClassFile('Category', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Category.php' );
