<?php
/*
 * Patch GET 20191111
 * 
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jan 20 New in v1.5.7 $
 */
/**
 * Suitable for versions of Zen Cart prior to v1.5.7
 *
 * Non-sanitization/access - $_GET
 *
 * Please Note : This file should be placed in includes/extra_configures and will automatically load.
 *  
 */

if (!isset($_GET)) {
    return;
}
if (!is_array($_GET)) {
    return;
}
foreach ($_GET as $key => $value) {
    if ($key === 'amp;') continue;
    if (strpos($key, 'amp;') !== 0) {
        continue;
    }
    $newtext = substr($key, 4);
    if (isset($_GET[$newtext])) continue;

    $_GET[$newtext] = $_GET['amp;' . $newtext];
    unset($_GET['amp;' . $newtext]);
}

