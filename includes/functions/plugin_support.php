<?php
/**
 * plugin_support.php
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
  * @version $Id: Author: DrByte  Thu Mar 3 14:25:45 2016 -0500 Modified in v1.5.5 $
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
    $url1 = 'https://plugins.zen-cart.com/versioncheck/'.(int)$plugin_file_id;
    $url2 = 'https://www.zen-cart.com/versioncheck/'.(int)$plugin_file_id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 19);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 19);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check [' . (int)$plugin_file_id . '] ' . HTTP_SERVER);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);

    if ($error > 0) {
      trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying http instead.");
      curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url1));
      $response = curl_exec($ch);
      $error = curl_error($ch);
      $errno = curl_errno($ch);
    }
    if ($error > 0) {
      trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying www instead.");
      curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url2));
      $response = curl_exec($ch);
      $error = curl_error($ch);
      $errno = curl_errno($ch);
    }
    curl_close($ch);
    if ($error > 0 || $response == '') {
      trigger_error('CURL error checking plugin versions: ' . $errno . ':' . $error . "\nTrying file_get_contents() instead.");
      $ctx = stream_context_create(array('http' => array('timeout' => 5)));
      $response = file_get_contents($url1, null, $ctx);
      if ($response === false) {
        trigger_error('file_get_contents() error checking plugin versions.' . "\nTrying http instead.");
        $response = file_get_contents(str_replace('tps:', 'tp:', $url1), null, $ctx);
      }
      if ($response === false) {
        trigger_error('file_get_contents() error checking plugin versions.' . "\nAborting.");
        return false;
      }
    }

    $data = json_decode($response, true);
    if (!$data || !is_array($data)) return false;
    // compare versions
    if (strcmp($data[$lookup_index]['latest_plugin_version'], $version_string_to_compare) > 0) $new_version_available = TRUE;
    // check whether present ZC version is compatible with the latest available plugin version
    if (!in_array('v'. PROJECT_VERSION_MAJOR . '.' . substr(PROJECT_VERSION_MINOR, 0, 3), $data[$lookup_index]['zcversions'])) $new_version_available = FALSE;
    return ($new_version_available) ? $data[$lookup_index] : FALSE;
  }
