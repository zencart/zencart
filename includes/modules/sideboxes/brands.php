<?php
/**
 * brands sidebox - displays a list of manufacturers so customer can choose to filter on those products only
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Sep 24 Modified in v2.0.0-alpha1 $
 */

// test if brands sidebox should show
if ($current_page_base === FILENAME_BRANDS) {
    return;
}
if ($current_page_base === FILENAME_DEFAULT && !empty($_GET['manufacturers_id'])) {
    return;
}

/**
 * The list is the same for every visitor -- manufacturers_name is not
 * per-language -- so the only thing that varies it is whether manufacturers
 * with no enabled product are filtered out.
 */
$sidebox_cache_ttl = (int)(defined('SIDEBOX_CACHE_SECONDS') ? SIDEBOX_CACHE_SECONDS : 3600);
$sidebox_cache = new \Zencart\Cache\FileCache();
$brands_cache_key = 'brandsbox_' . ((int)PRODUCTS_MANUFACTURERS_STATUS === 1 ? 'instock' : 'all');

$brands_array = $sidebox_cache->read($brands_cache_key, $sidebox_cache_ttl);

if ($brands_array === null) {
    if ((int)PRODUCTS_MANUFACTURERS_STATUS === 1) {
        /**
         * retrieve with featured manufacturers first. EXISTS stops at the first
         * enabled product per manufacturer; the SELECT DISTINCT with a LEFT JOIN
         * that this replaced built a temporary table and filesorted it.
         */
        $sql =
            "SELECT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured, (m.featured=1) AS weighted
               FROM " . TABLE_MANUFACTURERS . " m
              WHERE EXISTS (SELECT 1
                              FROM " . TABLE_PRODUCTS . " p
                             WHERE p.manufacturers_id = m.manufacturers_id
                               AND p.products_status = 1)
              ORDER BY weighted DESC, manufacturers_name";
    } else {
        // retrieve with featured manufacturers first
        $sql =
            "SELECT m.manufacturers_name, m.manufacturers_image, m.manufacturers_id, m.featured, (m.featured=1) AS weighted
               FROM " . TABLE_MANUFACTURERS . " m
               ORDER BY weighted DESC, manufacturers_name";
    }

    $brands_array = [];
    foreach ($db->Execute($sql) as $result) {
        $brands_array[] = [
            'id' => $result['manufacturers_id'],
            'text' => zen_output_string($result['manufacturers_name'], false, true),
            'image' => $result['manufacturers_image'],
            'featured' => $result['featured'],
        ];
    }

    if ($sidebox_cache_ttl > 0) {
        $sidebox_cache->write($brands_cache_key, $brands_array);
    }
}

if ($brands_array === []) {
    return;
}

require $template->get_template_dir('tpl_brands.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_brands.php';
$title = BOX_HEADING_BRANDS;
$title_link = FILENAME_BRANDS;
require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
