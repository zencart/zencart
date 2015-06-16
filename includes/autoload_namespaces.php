<?php
/**
 * Default autoloader namespace configuration
 *
 * @package initSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

// ensure library directory constants are set to rational defaults
if (!defined('DIR_CATALOG_LIBRARY')) {
  define('DIR_CATALOG_LIBRARY', DIR_FS_CATALOG . DIR_WS_INCLUDES . 'library/');
}

/**
 * An array of namespace => basedir configurations
 */
return array(
    '\ZenCart\Admin\Services' => DIR_CATALOG_LIBRARY . 'zencart/admin/Services/src',
    '\ZenCart\Admin\Lead' => DIR_CATALOG_LIBRARY . 'zencart/admin/Lead/src',
    '\ZenCart\Admin\Controllers' => DIR_CATALOG_LIBRARY . 'zencart/admin/Controllers/src',
    '\ZenCart\Admin\AjaxDispatch' => DIR_CATALOG_LIBRARY . 'zencart/admin/AjaxDispatch/src',
    '\ZenCart\Admin\DashboardWidget' => DIR_CATALOG_LIBRARY . 'zencart/admin/DashboardWidget/src',
    '\Aura\Web' => DIR_CATALOG_LIBRARY . 'aura/web/src',
    'ZenCart\Platform' => DIR_CATALOG_LIBRARY. 'zencart/platform/src/',
);
