<?php
/**
 * functions_sideboxes.php
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */

/**
 * Drop every cached sidebox dataset.
 *
 * One place that knows the whole set, so that the admin's end-of-request
 * invalidation and the Store Manager button do not each carry their own list
 * and drift apart from it.
 *
 * Called when something in the catalog may have changed. It is deliberately
 * coarse: these entries cost one or two queries each to rebuild, so clearing
 * all of them is far cheaper than working out which ones a given admin action
 * could have affected, and it cannot leave one of them stale by omission.
 *
 * @since ZC v2.3.0
 */
function zen_clear_sidebox_caches(): void
{
    $cache = new \Zencart\Cache\FileCache();

    /**
     * Fixed keys. The two per-status ones are listed in both variants because
     * the setting that selects between them can itself have just changed.
     */
    foreach (
        [
            'brandsbox_instock',
            'brandsbox_all',
            'musicgenresbox',
            'recordcompaniesbox',
            'documentcategoriesguard',
        ] as $key
    ) {
        $cache->clear($key);
    }

    /**
     * Split by language, so cleared by prefix rather than by enumerating the
     * installed languages, which would cost a query here.
     */
    $cache->clearPrefix('ezpagesbox');

    /**
     * The category maps and the manufacturers list keep their own clear
     * helpers, because both are also called from code that has nothing to do
     * with sideboxes.
     */
    zen_clear_category_map_cache();
    zen_clear_manufacturers_box_cache();
}
