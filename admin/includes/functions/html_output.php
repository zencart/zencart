<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: html_output.php 19356 2011-08-22 05:22:42Z drbyte $
 */

  /**
   * Returns a admin link formatted for use in a href attribute. This should
   * be used when adding a link to an web resource / page located within the
   * Zen Cart admin folder. Failure to use this function may result
   * in broken website links and cause issues with some Zen Cart plugins.
   *
   * This function should not be directly called from a language file. Why?
   * Observers are typically not be loaded until after the language files.
   * So if this function is used in a language file, any observers may not
   * receive notification a admin link is being generated.
   *
   * <b>Example Usage:</b>
   * Link to a category:
   *   <i>zen_href_link(FILENAME_DEFAULT, 'cPath=1_8');</i>
   * Link to a category (using an array for the parameters):
   *   <i>zen_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'));</i>
   * HTTPS (SSL) Link to an EZ Page:
   *   <i>zen_href_link(FILENAME_EZPAGES, 'id=4', 'SSL');</i>
   * Static link to an PDF:
   *   <i>zen_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true);</i>
   *
   * @deprecated 1.6.0 use zen_admin_href_link instead.
   *
   * @param string $page The Zen Cart page name or the path to a file / web
   *   resource (relative to the folder containing Zen Cart).
   *
   * @param string|array $parameters The urlencoded query string (or an array
   *   of key => value pairs to urlencode) to append to the link. When an array
   *   is passed the key / value pairs will be encoded using RFC 3986. Default
   *   is to not add a query string to the link.
   *
   * @param string $connection This parameter is no longer used and will be
   *   ignored. The value of HTTP_SERVER defined in the admin configure.php is
   *   always used when generating the href_link for admin pages and resources.
   *
   * @param boolean $add_session_id true to add the session id to the link when
   *   needed (such as when session cookies are disabled, before the session
   *   cookie exists, or switching connection methods), false otherwise.
   *   Default is true.
   *
   * @return string
   */
  function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true) {
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
   *   <i>zen_href_link(FILENAME_DEFAULT, 'cPath=1_8');</i>
   * Link to a category (using an array for the parameters):
   *   <i>zen_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'));</i>
   * HTTPS (SSL) Link to an EZ Page:
   *   <i>zen_href_link(FILENAME_EZPAGES, 'id=4', 'SSL');</i>
   * Static link to an PDF:
   *   <i>zen_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true);</i>
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

    $link = HTTP_SERVER . DIR_WS_ADMIN;

    // Handle parameters passed as an array (using RFC 3986)
    if(is_array($parameters)) {
      if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
        $parameters = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
      }
      else {
        $compile = array();
        foreach($parameters as $key => $value) {
          // Prior to PHP 5.3, tildes might be encoded per RFC 1738
          // This should not impact functionality for 99% of users.
          $compile[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $parameters = implode('&', $compile);
        unset($compile);
      }
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
   * links. In all other ways the function is identical to zen_href_link on
   * the catalog (store) side of Zen Cart.</i>
   *
   * <b>Example Usage:</b>
   * Link to a category:
   *   <i>zen_href_link(FILENAME_DEFAULT, 'cPath=1_8');</i>
   * Link to a category (using an array for the parameters):
   *   <i>zen_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'));</i>
   * HTTPS (SSL) Link to an EZ Page:
   *   <i>zen_href_link(FILENAME_EZPAGES, 'id=4', 'SSL');</i>
   * Static link to an PDF:
   *   <i>zen_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true);</i>
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
      if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
        $parameters = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
      }
      else {
        $compile = array();
        foreach($parameters as $key => $value) {
          // Prior to PHP 5.3, tildes might be encoded per RFC 1738
          // This should not impact functionality for 99% of users.
          $compile[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $parameters = implode('&', $compile);
        unset($compile);
      }
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
  function zen_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $image = '<img src="' . $src . '" border="0" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title=" ' . $alt . ' "';
    }
    if ($width) {
      $image .= ' width="' . $width . '"';
    }
    if ($height) {
      $image .= ' height="' . $height . '"';
    }
    if ($params) {
      $image .= ' ' . $params;
    }
    $image .= '>';

    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function zen_image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . zen_output_string(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image) . '" border="0" alt="' . zen_output_string($alt) . '"';

    if (zen_not_null($alt)) $image_submit .= ' title=" ' . zen_output_string($alt) . ' "';

    if (zen_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

////
// Draw a 1 pixel black line
  function zen_black_line() {
    return zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }

////
// Output a separator either through whitespace, or with an image
  function zen_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return zen_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a function button in the selected language
  function zen_image_button($image, $alt = '', $params = '') {
    global $language;

    return zen_image(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image, $alt, '', '', $params);
  }

////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function zen_js_zone_list($country, $form, $field) {
    global $db;
    $countries = $db->Execute("select distinct zone_country_id
                               from " . TABLE_ZONES . "
                               order by zone_country_id");

    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      }

      $states = $db->Execute("select zone_name, zone_id
                              from " . TABLE_ZONES . "
                              where zone_country_id = '" . $countries->fields['zone_country_id'] . "'
                              order by zone_name");


      $num_state = 1;
      while (!$states->EOF) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states->fields['zone_name'] . '", "' . $states->fields['zone_id'] . '");' . "\n";
        $num_state++;
        $states->MoveNext();
      }
      $num_country++;
      $countries->MoveNext();
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '  }' . "\n";

    return $output_string;
  }

////
// Output a form
  function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
    $form = '<form name="' . zen_output_string($name) . '" action="';
    if (zen_not_null($parameters)) {
      if ($usessl) {
        $form .= zen_href_link($action, $parameters, 'NONSSL');
      } else {
        $form .= zen_href_link($action, $parameters, 'NONSSL');
      }
    } else {
      if ($usessl) {
        $form .= zen_href_link($action, '', 'NONSSL');
      } else {
        $form .= zen_href_link($action, '', 'NONSSL');
      }
    }
    $form .= '" method="' . zen_output_string($method) . '"';
    if (zen_not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';
    if (strtolower($method) == 'post') $form .= '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '" />';
    if (strtolower($method) == 'get') $form .= '<input type="hidden" name="cmd" value="' . $action . '" />';
    return $form;
  }

////
// Output a form input field
  function zen_draw_input_field($name, $value = '~*~*#', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

    if ( $value == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif ($value != '~*~*#' && zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Output a form password field
  function zen_draw_password_field($name, $value = '', $required = false) {
    $field = zen_draw_input_field($name, $value, 'maxlength="40"', $required, 'password', false);

    return $field;
  }

////
// Output a form filefield
  function zen_draw_file_field($name, $required = false) {
    $field = zen_draw_input_field($name, '', ' size="50" ', $required, 'file');

    return $field;
  }

////
// Output a selection field - alias function for zen_draw_checkbox_field() and zen_draw_radio_field()
  function zen_draw_selection_field($name, $type, $value = '', $checked = false, $compare = '', $parameters = '') {
    $selection = '<input type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

    if (zen_not_null($value)) $selection .= ' value="' . zen_output_string($value) . '"';

    if ( ($checked == true) || (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ($GLOBALS[$name] == 'on')) || (isset($value) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && (stripslashes($GLOBALS[$name]) == $value)) || (zen_not_null($value) && zen_not_null($compare) && ($value == $compare)) ) {
      $selection .= ' checked="checked"';
    }

    if (zen_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

////
// Output a form checkbox field
  function zen_draw_checkbox_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return zen_draw_selection_field($name, 'checkbox', $value, $checked, $compare, $parameters);
  }

////
// Output a form radio field
  function zen_draw_radio_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return zen_draw_selection_field($name, 'radio', $value, $checked, $compare, $parameters);
  }

////
// Output a form textarea field
  function zen_draw_textarea_field($name, $wrap, $width, $height, $text = '~*~*#', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . zen_output_string($name) . '" wrap="' . zen_output_string($wrap) . '" cols="' . zen_output_string($width) . '" rows="' . zen_output_string($height) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($text == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= stripslashes($GLOBALS[$name]);
      $field = str_replace('&gt;', '>', $field);
    } elseif ($text != '~*~*#' && zen_not_null($text)) {
      $field = str_replace('&gt;', '>', $field);
      $field .= $text;
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function zen_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . zen_output_string($name) . '"';

    if (zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Output a form pull down menu
  function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
//    $field = '<select name="' . zen_output_string($name) . '"';
    $field = '<select rel="dropdown" name="' . zen_output_string($name) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>' . "\n";

    if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) ) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . zen_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . zen_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
    }
    $field .= '</select>' . "\n";

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
////
// Hide form elements
  function zen_hide_session_id() {
    global $session_started;

    if ( ($session_started == true) && defined('SID') && zen_not_null(SID) ) {
      return zen_draw_hidden_field(zen_session_name(), zen_session_id());
    }
  }
?>