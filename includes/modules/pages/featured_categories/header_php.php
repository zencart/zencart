<?php
/**
 * Featured Categories
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Jeff Rutt 2024 Aug 19 New in v2.1.0-alpha2 $
 * based on Featured Products
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_FEATURED_CATEGORIES');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// load extra language strings used by category_listing module
$languageLoader->setCurrentPage('index');
$languageLoader->loadLanguageForView();

$breadcrumb->add(NAVBAR_TITLE);

$listing_sql = "SELECT c.categories_id, c.categories_image, cd.categories_name
                FROM " . TABLE_CATEGORIES . " c
                LEFT JOIN " . TABLE_FEATURED_CATEGORIES . " fc ON c.categories_id = fc.categories_id
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id
                AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                WHERE c.categories_status = 1
                AND fc.status = 1
                ORDER BY cd.categories_name";

$listing = $db->Execute($listing_sql);

foreach ($listing as $record) {
    if ($record['categories_image'] === '' || !file_exists(DIR_WS_IMAGES . $record['categories_image'])) {
        $record['categories_image'] = PRODUCT_IMAGE_NO_IMAGE;
    }
}

// display sort order dropdown

// set the category filters according to selected category type
$typefilter = $_GET['typefilter'] ?? 'default';
//require(zen_get_index_filters_directory($typefilter . '_filter.php'));


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_FEATURED_CATEGORIES', null);

