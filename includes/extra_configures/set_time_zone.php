<?php
/**
 * @package initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
/*
 * Set time zone
*/
if (version_compare(PHP_VERSION, 5.3, '>='))
{
  // put your timezone here. Refer to http://www.php.net/manual/en/timezones.php
  $TZ = '';  // eg: 'Europe/Oslo'



  /**
   * MAKE NO CHANGES BELOW THIS LINE
   *
   * The following will take the timezone you specified above and apply it in your store.
   * If you didn't specify one, it will try to use the setting from your server's PHP configuration
   */
  if ($TZ == '') {
    $TZ = date_default_timezone_get();
  }
  if ($TZ != '') {
    putenv('TZ=' . $TZ);
    @date_default_timezone_set($TZ);
  }
}