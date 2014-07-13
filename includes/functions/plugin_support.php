<?php
/**
 * plugin_support.php
 *
 * @package functions
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Apr 1 15:45:22 2014 -0400 New in v1.5.3 $
 */
/**
 * Functions to support plugin usage
 */

  /**
   * Check for updated version of a plugin
   * Arguments:
   *   $plugin_file_id = the fileid number for the plugin as hosted on the zen-cart.com plugins library
   *   $version_string_to_compare = the version that I have now on my own server (will be checked against the one on the ZC server)
   * If the "version string" passed to this function evaluates (see strcmp) to a value less-then-or-equal-to the one on the ZC server, FALSE will be returned.
   * If the "version string" on the ZC server is greater than the version string passed to this function, this function will return an array with up-to-date information. The [link] value is the plugin page at zen-cart.com
   * If no plugin_file_id is passed, or if no result is found, then FALSE will be returned.
   *
   * USAGE:
   *   if (IS_ADMIN_FLAG) {
   *     $new_version_details = plugin_version_check_for_updates(999999999, 'some_string');
   *     if ($new_version_details !== FALSE) {
   *       $message = '<span class="alert">' . ' - NOTE: A NEW VERSION OF THIS PLUGIN IS AVAILABLE. <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>' . '</span>';
   *     }
   *   }
   */
  function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '')
  {
    if ($plugin_file_id == 0) return FALSE;
    $new_version_available = FALSE;
    $lookup_index = 0;
    $url = 'http://www.zen-cart.com/downloads.php?do=versioncheck' . '&id='.(int)$plugin_file_id;
    $data = json_decode(file_get_contents($url), true);
    // compare versions
    if (strcmp($data[$lookup_index]['latest_plugin_version'], $version_string_to_compare) > 0) $new_version_available = TRUE;
    // check whether present ZC version is compatible with the latest available plugin version
    if (!in_array('v'. PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR, $data[$lookup_index]['zcversions'])) $new_version_available = FALSE;
    return ($new_version_available) ? $data[$lookup_index] : FALSE;
  }

