<?php
/**
 * Arrange for the derived caches that the storefront categories and
 * manufacturers sideboxes read to be dropped at the end of any admin request
 * that could have changed the catalog.
 *
 * The clear is registered as a shutdown function rather than placed in
 * application_bottom.php, because the admin almost never reaches
 * application_bottom.php after a successful save: the category, product,
 * manufacturer and product-to-category actions all finish with zen_redirect(),
 * which calls exit(). Shutdown functions still run after exit(), so this fires
 * on the redirecting paths, on a plain page render, and on a die() alike.
 *
 * Running at shutdown also keeps the clear after the page's own writes have
 * committed. Clearing during init would leave a window in which a storefront
 * request could rebuild from pre-edit data and store it under a fresh
 * timestamp, serving stale counts for the whole cache lifetime.
 *
 * Every admin path that can change a category, product or manufacturer is
 * either a POST form or a GET carrying action= (setflag, the *_confirm
 * actions), so restricting the clear to those leaves ordinary navigation --
 * listings, dashboards, reports, order views, configuration browsing --
 * untouched without opening a coverage gap. Per-event notifiers cannot do this
 * job on this branch: there is no notifier at all on category or product
 * deletion, the status toggles, move, copy, or in
 * admin/products_to_categories.php.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Only the presence of action= is inspected, never its value, so this is safe
 * ahead of init_sanitize.php.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_GET['action'])) {
    return;
}

register_shutdown_function(static function () {
    if (function_exists('zen_clear_sidebox_caches')) {
        zen_clear_sidebox_caches();
    }
});
