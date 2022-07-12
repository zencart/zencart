<?php
/**
 * brands header_php.php
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Apr 30 New in v1.5.8-alpha $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_BRANDS');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(BREADCRUMB_BRANDS, zen_href_link(FILENAME_BRANDS));

$category_depth = 'brands';
$typefilter = $_GET['typefilter'] = 'brands';

$listing_sql =
    "SELECT manufacturers_name, manufacturers_image, manufacturers_id, featured
       FROM " . TABLE_MANUFACTURERS . " m
      ORDER BY featured DESC, manufacturers_name";

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
    define('BRANDS_IMAGE_WIDTH', '126');
}
if (!defined('BRANDS_IMAGE_HEIGHT')) {
    define('BRANDS_IMAGE_HEIGHT', '126');
}
if (!defined('BRANDS_MAX_COLUMNS')) {
    define('BRANDS_MAX_COLUMNS', '6');
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_BRANDS');
