<?php
/**
 * Featured Categories
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 31 Modified in v2.0.0-beta1 $
 * based on Featured Products
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_FEATURED_CATEGORIES');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// load extra language strings used by product_listing module
$languageLoader->setCurrentPage('index');
$languageLoader->loadLanguageForView();

$breadcrumb->add(NAVBAR_TITLE);

    $listing_sql = "SELECT p.categories_id, p.categories_image, pd.categories_name
            FROM " . TABLE_CATEGORIES . " p
            LEFT JOIN " . TABLE_FEATURED_CATEGORIES . " f ON p.categories_id = f.categories_id
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " pd ON p.categories_id = pd.categories_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
            WHERE p.categories_status = 1
            AND f.status = 1";

$listing = $db->Execute($listing_sql);

foreach ($listing as $record) {
    if ($record['categories_image'] === '' || !file_exists(DIR_WS_IMAGES . $record['categories_image'])) {
        $record['categories_image'] = PRODUCTS_IMAGE_NO_IMAGE;
    }
}

// -----
// Define the maximum columns to display.
// These are "soft" configuration setting that can be overridden on a site-specific basis.
//
if (!defined('FC_MAX_COLUMNS')) {
    define('FC_MAX_COLUMNS', '6');
}
// display sort order dropdown

// set the product filters according to selected product type
$typefilter = $_GET['typefilter'] ?? 'default';
//require(zen_get_index_filters_directory($typefilter . '_filter.php'));


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_FEATURED_CATEGORIES', null);
