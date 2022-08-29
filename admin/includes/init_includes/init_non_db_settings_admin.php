<?php
/**
 * Initializes non-database constants that were previously set in language modules,
 * overridable via site-specific /init_includes processing.  See
 * /admin/includes/init_includes/dist-init_site_specific_non_db_settings_admin.php.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 23 New in v1.5.8-alpha2 $
 */

// -----
// Load the constant values defined for both storefront and admin use.
//
require DIR_FS_CATALOG . DIR_WS_INCLUDES . 'init_includes/init_non_db_settings.php';
