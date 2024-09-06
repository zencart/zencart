<?php
/**
 * brands header_php.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 24 Modified in v2.1.0-alpha2 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_BRANDS');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(BREADCRUMB_BRANDS, zen_href_link(FILENAME_BRANDS));

$category_depth = 'brands';
$typefilter = $_GET['typefilter'] = 'brands';

if ((int)PRODUCTS_MANUFACTURERS_STATUS === 1) {
    $listing_sql =
        "SELECT DISTINCT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured
           FROM " . TABLE_MANUFACTURERS . " m
                LEFT JOIN " . TABLE_PRODUCTS . " p
                    ON m.manufacturers_id = p.manufacturers_id
          WHERE p.products_status = 1
          ORDER BY m.featured DESC, m.manufacturers_name";
} else {
    $listing_sql =
        "SELECT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured
           FROM " . TABLE_MANUFACTURERS . " m
           ORDER BY m.featured DESC, m.manufacturers_name";
}

$brands = [
    'featured' => [],
    'other' => [],
];
$listing = $db->Execute($listing_sql);
foreach ($listing as $record) {
    if ($record['manufacturers_image'] === '' || !file_exists(DIR_WS_IMAGES . $record['manufacturers_image'])) {
        $record['manufacturers_image'] = PRODUCTS_IMAGE_NO_IMAGE;
    }

    if ($record['featured'] === '0') {
        $brands['other'][] = $record;
    } else {
        $brands['featured'][] = $record;
    }
}

// -----
// Define the height and width to be used for the manufacturer's image as well as the maximum columns to display.
// These are "soft" configuration setting that can be overridden on a site-specific basis.
//
if (!defined('BRANDS_IMAGE_WIDTH')) {
    define('BRANDS_IMAGE_WIDTH', IMAGE_PRODUCT_LISTING_WIDTH);
}
if (!defined('BRANDS_IMAGE_HEIGHT')) {
    define('BRANDS_IMAGE_HEIGHT', IMAGE_PRODUCT_LISTING_HEIGHT);
}
if (!defined('BRANDS_MAX_COLUMNS')) {
    define('BRANDS_MAX_COLUMNS', PRODUCT_LISTING_COLUMNS_PER_ROW);
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_BRANDS');
