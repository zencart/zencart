<?php
/**
 * A collection of site-specific overrides for the storefront operation.
 *
 * There are some features in the base Zen Cart processing that can be overridden for a specific
 * site, as identified in this module.
 *
 * For use on YOUR site, make a copy of this file (which has all entries commented-out) to /includes/extra_datafiles/site_specific_overrides.php
 * and make your edits there.  Otherwise, your overrides might get "lost" on a future Zen Cart upgrade.  The 'base' Zen Cart definitions
 * of these variables are set by /includes/init_includes/init_common_elements.php.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Apr 30 Modified in v1.5.8 $
 */
// -----
// Identify whether the link to the 'brands' page (added in Zen Cart 1.5.8) is included in the "Information" sidebox.
//
// true ...... Always show in the sidebox.
// false ..... Never show in the sidebox.
// Not set ... (i.e. leave the variable commented-out), the link shows if there are manufacturers with active products (default).
//
//$flag_show_brand_sidebox_link = false;

// -----
// Identify whether the zcDate class' (added in Zen Cart 1.5.8) debug-output is initially enabled.
//
// true ...... The zcDate debug is enabled.
// false ..... The zcDate debug is disabled (the default).
//
//$zen_date_debug = false;
