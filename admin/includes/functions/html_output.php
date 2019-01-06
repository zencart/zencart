<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */

  /**
   * @deprecated in version 1.6.0. Use zen_admin_href_link() instead
   */
  function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true) {
    //trigger_error ('The function zen_href_link is deprecated, use zen_admin_href_link instead. Note that the parameters are different too.', E_USER_NOTICE);
    return zen_admin_href_link($page, $parameters, $add_session_id);
  }

  /**
   * Returns a admin link formatted for use in a href attribute. This should
   * be used when adding a link to an web resource / page located within the
   * Zen Cart admin folder. Failure to use this function may result
   * in broken website links and cause issues with some Zen Cart plugins.
   *
   * This function should not be directly called from a language file. Why?
   * Observers are typically not be loaded until after the language files.
   * So if this function is used in a language file, any observers may not
   * receive notification a catalog link is being generated.
   *
   * <b>Example Usage:</b>
   * Link to a category:
   *   <i>zen_admin_href_link(FILENAME_DEFAULT, 'cPath=1_8');</i>
   * Link to a category (using an array for the parameters):
   *   <i>zen_admin_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'));</i>
   * HTTPS (SSL) Link to an EZ Page:
   *   <i>zen_admin_href_link(FILENAME_EZPAGES, 'id=4', 'SSL');</i>
   * Static link to an PDF:
   *   <i>zen_admin_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_admin_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true);</i>
   *
   * @param string $page The Zen Cart page name or the path to a file / web
   *   resource (relative to the folder containing Zen Cart).
   *
   * @param string|array $parameters The urlencoded query string (or an array
   *   of key => value pairs to urlencode) to append to the link. When an array
   *   is passed the key / value pairs will be encoded using RFC 3986. Default
   *   is to not add a query string to the link.
   *
   * @param boolean $add_session_id true to add the session id to the link when
   *   needed (such as when session cookies are disabled, before the session
   *   cookie exists, or switching connection methods), false otherwise.
   *   Default is true.
   *
   * @return string
   */
  function zen_admin_href_link($page = '', $parameters = '', $add_session_id = true) {
    global $zco_notifier, $session_started;

    // Notify any observers listening for href_link calls
    $zco_notifier->notify(
      'NOTIFY_HANDLE_ADMIN_HREF_LINK',
      array(
        'page' => $page,
        'parameters' => $parameters,
        'add_session_id' => false,
      ),
      $page,
      $parameters,
      $static
    );
    $page = str_replace('.php', '', $page);

    $link = ADMIN_HTTP_SERVER . DIR_WS_ADMIN;

    // Handle parameters passed as an array (using RFC 3986)
    if(is_array($parameters)) {
      $parameters = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
    else {
      // Clean up parameters (should not start or end with these characters)
      $parameters = trim($parameters, '&?');
    }

    // Keep track of the separator
    $separator = '&';

    if (!zen_not_null($page) || ($page == FILENAME_DEFAULT && !zen_not_null($parameters))) {
      // If the request was for the homepage, do nothing
      $separator = '?';
    }
    else if (zen_not_null($parameters)) {
      $link .= 'index.php?cmd='. $page . '&' . zen_output_string($parameters);
    }
    else {
      $link .= 'index.php?cmd=' . $page;
    }

    // Replace duplicates of '&' and instances of '&amp;'  with a single '&'
    $link = preg_replace('/(&amp;|&){2,}|&amp;/', '&', $link);

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) ) {
      if (defined('SID') && constant('SID') != '') {
        $link .= $separator . zen_output_string(constant('SID'));
      }
    }
    $link = preg_replace('/(&{2,}|(&amp;)+)/', '&', $link);

    // Convert any remaining '&' into '&amp;' (valid URL for href)
    $link = str_replace('&', '&amp;', $link);
    return $link;
  }

  /**
   * Returns a catalog link formatted for use in a href attribute. This should
   * be used when adding a link to an web resource / page located within the
   * folder where Zen Cart is installed. Failure to use this function may result
   * in broken website links and cause issues with some Zen Cart plugins.
   *
   * This function should not be directly called from a language file. Why?
   * Observers are typically not be loaded until after the language files.
   * So if this function is used in a language file, any observers may not
   * receive notification a catalog link is being generated.
   *
   * <i>This function does not allow the addition of a session id (zenid) to the
   * links. In all other ways the function is identical to zen_admin_href_link on
   * the catalog (store) side of Zen Cart.</i>
   *
   * <b>Example Usage:</b>
   * Link to a category:
   *   <i>zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8');</i>
   * Link to a category (using an array for the parameters):
   *   <i>zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'));</i>
   * HTTPS (SSL) Link to an EZ Page:
   *   <i>zen_catalog_href_link(FILENAME_EZPAGES, 'id=4', 'SSL');</i>
   * Static link to an PDF:
   *   <i>zen_catalog_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_catalog_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true);</i>
   *
   * @param string $page The Zen Cart page name or the path to a file / web
   *   resource (relative to the folder containing Zen Cart).
   *
   * @param string|array $parameters The urlencoded query string (or an array
   *   of key => value pairs to urlencode) to append to the link. When an array
   *   is passed the key / value pairs will be encoded using RFC 3986. Default
   *   is to not add a query string to the link.
   *
   * @param string $connection The connection method to utilize. To use the
   *   http protocol (unencrypted communication) use 'NONSSL'. To use the
   *   https protocol (encrypted communication) use 'SSL'. Defaults to 'NONSSL'.
   *   If the connection method is unknown, 'NONSSL' is used and a warning
   *   message will be added to the Zen Cart debug logs.
   *
   * @param string $search_engine_safe true to replace the query string with
   *   a string delimited by forward slashes, false otherwise. Default is true.
   *   This setting has no effect unless SEARCH_ENGINE_FRIENDLY_URLS is also
   *   configured to 'true'.
   *
   *   <i>Example: When enabled, a query string such as "cPath=1_8&products_id=25"
   *   will be changed to "cPath/1_8/products_id/25".</i>
   *
   * @param string $static true when $page is a file / web resource,
   *   false otherwise. Default is false.
   *
   * @param string $use_dir_ws_catalog true to include the website catalog
   *   directory when creating the link, false otherwise. Default is true.
   *
   * @return string
   */
  function zen_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL', $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
    global $zco_notifier;

    // Notify any observers listening for href_link calls
    $link = $connection;
    $zco_notifier->notify(
      'NOTIFY_HANDLE_HREF_LINK',
      array(
        'page' => $page,
        'parameters' => $parameters,
        'connection' => $connection,
        'add_session_id' => false,
        'search_engine_safe' => $search_engine_safe,
        'static' => $static,
        'use_dir_ws_catalog' => $use_dir_ws_catalog
      ),
      $page,
      $parameters,
      $connection,
      $static
    );

    // Do not allow switching from NONSSL to SSL
    if($connection == 'NONSSL' && $link == 'SSL') {
      $connection = $link;
    }

    // Add the protocol, server name, and installed folder
    switch ($connection) {
      case 'SSL':
        if (ENABLE_SSL == 'true') {
          $link = HTTPS_CATALOG_SERVER;
          if($use_dir_ws_catalog) $link .= DIR_WS_HTTPS_CATALOG;
          break;
        }
      case 'NONSSL':
        $link = HTTP_CATALOG_SERVER;
        if($use_dir_ws_catalog) $link .= DIR_WS_CATALOG;
        break;
      default:
        // Add a warning to the log (uses NONSSL as a default)
        $e = new Exception();
        error_log(sprintf(
          CONNECTION_TYPE_UNKNOWN,
          $connection,
          $e->getTraceAsString()
        ));
        unset($e);
        $link = HTTP_CATALOG_SERVER;
        if($use_dir_ws_catalog) $link .= DIR_WS_CATALOG;
    }

    // Handle parameters passed as an array (using RFC 3986)
    if(is_array($parameters)) {
      $parameters = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
    else {
      // Clean up parameters (should not start or end with these characters)
      $parameters = trim($parameters, '&?');
    }

    // Check if the request was for the homepage
    if (!zen_not_null($page) || ($page == FILENAME_DEFAULT && !zen_not_null($parameters))) {
      $page = '';
      $static = true;
    }

    if (!$static) {
      if (zen_not_null($parameters)) {
        $link .= 'index.php?main_page='. $page . '&' . zen_output_string($parameters);
      }
      else {
        $link .= 'index.php?main_page=' . $page;
      }
    }
    else {
      if (zen_not_null($parameters)) {
        $link .= $page . '?' . zen_output_string($parameters);
      }
      else {
        $link .= $page;
      }
    }

    // Replace duplicates of '&' and instances of '&amp;'  with a single '&'
    $link = preg_replace('/(&amp;|&){2,}|&amp;/', '&', $link);

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      $link = str_replace(array('?', '&', '='), '/', $link);
    }

    // Convert any remaining '&' into '&amp;' (valid URL for href)
    $link = str_replace('&', '&amp;', $link);
    return $link;
  }

////
// The HTML image wrapper function
/**
 * @param string $page
 * @param string $parameters
 * @param string $connection
 * @param bool $add_session_id
 * @return mixed|string
 */
function zen_ajax_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true) {
    $link = zen_admin_href_link($page, $parameters, $add_session_id);
    $link = str_replace('&amp;', '&', $link);
    return $link;
}

require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');
