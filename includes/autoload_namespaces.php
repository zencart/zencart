<?php
/**
 * Default autoloader namespace configuration
 *
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

// ensure library directory constants are set to rational defaults
if (!defined('DIR_ADMIN_LIBRARY')) {
  define('DIR_ADMIN_LIBRARY', DIR_FS_ADMIN . DIR_WS_INCLUDES . 'library/');
}
if (!defined('DIR_CATALOG_LIBRARY')) {
  define('DIR_CATALOG_LIBRARY', DIR_FS_CATALOG . DIR_WS_INCLUDES . 'library/');
}

/**
 * An array of namespace => basedir configurations
 */
return array(
    '\Aura\Web' => DIR_CATALOG_LIBRARY . 'aura/web/src',
    '\Aura\Di' => DIR_CATALOG_LIBRARY . 'aura/di/src',
    'ZenCart\Platform' => DIR_CATALOG_LIBRARY. 'zencart/platform/src/',
    'ZenCart\ListingBox' => DIR_CATALOG_LIBRARY. 'zencart/listingBox/src/',
);
