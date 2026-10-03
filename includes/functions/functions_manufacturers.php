<?php
/**
 * functions_manufacturers.php
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */

/**
 * Cache key for the manufacturers sidebox list.
 *
 * The list is the same for every visitor: manufacturers_name lives on the
 * manufacturers table rather than a per-language table, so the only thing that
 * varies the result is whether manufacturers without an enabled product are
 * filtered out.
 *
 * @param bool|null $in_stock_only null reads the store's current setting
 * @since ZC v2.3.0
 */
function zen_manufacturers_box_cache_key($in_stock_only = null): string
{
    if ($in_stock_only === null) {
        $in_stock_only = ((int)zen_config('PRODUCTS_MANUFACTURERS_STATUS') === 1);
    }

    return 'manufacturersbox_' . ($in_stock_only ? 'instock' : 'all');
}

/**
 * Discard the cached manufacturers sidebox list, in both of its variants.
 *
 * Both are cleared because the setting that selects between them can itself
 * have changed in the request that calls this.
 *
 * @see includes/modules/sideboxes/manufacturers.php
 * @since ZC v2.3.0
 */
function zen_clear_manufacturers_box_cache(): void
{
    zen_file_cache_clear(zen_manufacturers_box_cache_key(true));
    zen_file_cache_clear(zen_manufacturers_box_cache_key(false));
}
