<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 27 Modified in v2.0.0-alpha1 $
 */
/*
 * Set time zone
*/
// put your timezone here. Refer to http://www.php.net/manual/en/timezones.php
$TZ = '';  // eg: 'Europe/Oslo'



/**
* MAKE NO CHANGES BELOW THIS LINE
*
* The following will take the timezone you specified above and apply it in your store.
* If you didn't specify one, it will try to use the setting from your server's PHP configuration
*/
if (empty($TZ)) {
    $TZ = date_default_timezone_get();
} else {
    putenv('TZ=' . $TZ);
    @date_default_timezone_set($TZ);
}

// Now incorporate TZ change into log filenames
zen_set_error_logging_filename();
