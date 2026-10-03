<?php
/**
 * manufacturers sidebox - displays a list of manufacturers so customer can choose to filter on their products only
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2025 May 16 Modified in v2.2.0 $
 */
/**
 * Serve the list from the cache when it is fresh, so crawler traffic stops
 * re-running this query on every page. The key is derived in one place, by
 * zen_manufacturers_box_cache_key(), so that the admin can invalidate it.
 *
 * A read-only cache directory just means falling back to the query, as before.
 */
$manufacturer_cache_ttl = (int)(defined('MANUFACTURERS_SIDEBOX_CACHE_SECONDS') ? MANUFACTURERS_SIDEBOX_CACHE_SECONDS : 3600);

$manufacturer_sidebox_rows = zen_file_cache_read(zen_manufacturers_box_cache_key(), $manufacturer_cache_ttl);

if ($manufacturer_sidebox_rows === null) {
    // only check products if requested - this may slow down the processing of the manufacturers sidebox
    if ((int)zen_config('PRODUCTS_MANUFACTURERS_STATUS') === 1) {
        /**
         * EXISTS stops at the first enabled product per manufacturer. The
         * SELECT DISTINCT ... LEFT JOIN form this replaced built a temporary
         * table and filesorted it, which is what made the box expensive on a
         * large catalog.
         */
        $manufacturer_sidebox_query =
            "SELECT m.manufacturers_id, m.manufacturers_name
               FROM " . TABLE_MANUFACTURERS . " m
              WHERE EXISTS (SELECT 1
                              FROM " . TABLE_PRODUCTS . " p
                             WHERE p.manufacturers_id = m.manufacturers_id
                               AND p.products_status = 1)
              ORDER BY m.manufacturers_name";
    } else {
        $manufacturer_sidebox_query =
            "SELECT m.manufacturers_id, m.manufacturers_name
               FROM " . TABLE_MANUFACTURERS . " m
               ORDER BY manufacturers_name";
    }

    $manufacturer_sidebox_rows = [];
    foreach ($db->Execute($manufacturer_sidebox_query) as $sidebox_element) {
        $manufacturer_sidebox_rows[] = [
            'manufacturers_id' => (int)$sidebox_element['manufacturers_id'],
            'manufacturers_name' => $sidebox_element['manufacturers_name'],
        ];
    }

    // a lifetime of zero turns the cache off; keep the query rewrite only
    if ($manufacturer_cache_ttl > 0) {
        zen_file_cache_write(zen_manufacturers_box_cache_key(), $manufacturer_sidebox_rows);
    }
}

if (!empty($manufacturer_sidebox_rows)) {
    // -----
    // Display a list, noting that the empty ('') selection will not be enabled (via jQuery)
    // if this is the initial display without a previous selection.
    //
    $manufacturer_sidebox_array = [];
    $default_selection = (isset($_GET['manufacturers_id'])) ? (int)$_GET['manufacturers_id'] : '';
    if (!isset($_GET['manufacturers_id']) || $_GET['manufacturers_id'] === '' ) {
        $required = ' required';
        $manufacturer_sidebox_array[] = ['id' => '', 'text' => PULL_DOWN_ALL];
    } else {
        $required = '';
        $manufacturer_sidebox_array[] = ['id' => '', 'text' => PULL_DOWN_MANUFACTURERS];
    }

    foreach ($manufacturer_sidebox_rows as $sidebox_element) {
        $manufacturer_sidebox_name = $sidebox_element['manufacturers_name'];
        if (mb_strlen($manufacturer_sidebox_name) > (int)$tplSetting->MAX_DISPLAY_MANUFACTURER_NAME_LEN) {
            $manufacturer_sidebox_name = mb_substr($manufacturer_sidebox_name, 0, (int)$tplSetting->MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..';
        }
        $manufacturer_sidebox_array[] = [
            'id' => $sidebox_element['manufacturers_id'],
            'text' => zen_output_string($manufacturer_sidebox_name, false, true),
        ];
    }
    require $template->get_template_dir('tpl_manufacturers_select.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_manufacturers_select.php';

    $title = BOX_HEADING_MANUFACTURERS;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
