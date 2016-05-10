<?php
/**
 * html_output.php
 * HTML-generating functions used throughout the core
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: html_output.php 19355 2011-08-21 21:12:09Z drbyte  Modified in v1.6.0 $
 */

  /**
   * Returns a link formatted for use in a href attribute. This should be used
   * when adding a link to an web resource / page located within the folder
   * where Zen Cart is installed. Failure to use this function may result in
   * broken website links and cause issues with some Zen Cart plugins.
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
   *   <i>zen_href_link('specs/keyboard.pdf', '', 'NONSSL', true, true, true);</i>
   * Static HTTPS (SSL) Link to a PHP script (with parameters):
   *   <i>zen_href_link('find-location.php', 'ip=127.0.0.1', 'SSL', true, true, true);</i>
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
   * @param boolean $add_session_id true to add the session id to the link when
   *   needed (such as when session cookies are disabled, before the session
   *   cookie exists, or switching connection methods), false otherwise.
   *   Default is true.
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
  function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
    global $request_type, $session_started, $http_domain, $https_domain, $zco_notifier;

    // Notify any observers listening for href_link calls
    $link = $connection;
    $zco_notifier->notify(
      'NOTIFY_HANDLE_HREF_LINK',
      array(
        'page' => $page,
        'parameters' => $parameters,
        'connection' => $connection,
        'add_session_id' => $add_session_id,
        'search_engine_safe' => $search_engine_safe,
        'static' => $static,
        'use_dir_ws_catalog' => $use_dir_ws_catalog
      ),
      $page,
      $parameters,
      $connection,
      $static
    );

    // Do not allow observer to downgrade from SSL to NONSSL
    if ($connection == 'NONSSL' && $link == 'SSL') {
      $connection = $link;
    }

    // Add the protocol, server name, and installed folder
    switch ($connection) {
      case 'SSL':
        if (ENABLE_SSL == 'true') {
          $link = HTTPS_SERVER;
          if($use_dir_ws_catalog) $link .= DIR_WS_HTTPS_CATALOG;
          break;
        }
      case 'NONSSL':
        $link = HTTP_SERVER;
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
        $link = HTTP_SERVER;
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

    // Keep track of the separator
    $separator = '?';
    if (!$static) {
      $separator = '&';
      if (zen_not_null($parameters)) {
        $link .= 'index.php?main_page='. $page . $separator . zen_output_string($parameters);
      }
      else {
        $link .= 'index.php?main_page=' . $page;
      }
    }
    else {
      if (zen_not_null($parameters)) {
        $link .= $page . $separator . zen_output_string($parameters);
        $separator = '&';
      }
      else {
        $link .= $page;
        if(FALSE !== strpos($link, '?')) $separator = '&';
      }
    }

    // Replace duplicates of '&' and instances of '&amp;'  with a single '&'
    $link = preg_replace('/(&amp;|&){2,}|&amp;/', '&', $link);

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      $link = str_replace(array('?', '&', '='), '/', $link);
      $separator = '?';
    }

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (defined('SID') && constant('SID') != '') {
        $link .= $separator . zen_output_string(constant('SID'));
      }
      else if ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if ($http_domain != $https_domain) {
          $link .= $separator . zen_output_string(zen_session_name() . '=' . zen_session_id());
        }
      }
    }

    // Convert any remaining '&' into '&amp;' (valid URL for href)
    $link = str_replace('&', '&amp;', $link);
    return $link;
  }

/*
 * The HTML image wrapper function for non-proportional images
 * used when "proportional images" is turned off or if calling from a template directory
 */
  function zen_image_OLD($src, $alt = '', $width = '', $height = '', $parameters = '') {
    global $template_dir;

//auto replace with defined missing image
    if ($src == DIR_WS_IMAGES and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
      $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }

    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

    // if not in current template switch to template_default
    if (!file_exists($src)) {
      $shared_src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'shared', $src);
      $default_src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
      if (file_exists($shared_src)) {
        $src = $shared_src;
      } else {
        $src = $default_src;
      }
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . zen_output_string($src) . '" alt="' . zen_output_string($alt) . '"';

    if (zen_not_null($alt)) {
      $image .= ' title=" ' . zen_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && zen_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = $image_size[0] * $ratio;
        } elseif (zen_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (zen_not_null($width) && zen_not_null($height)) {
      $image .= ' width="' . zen_output_string($width) . '" height="' . zen_output_string($height) . '"';
    }

    if (zen_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }


/*
 * The HTML image wrapper function
 */
  function zen_image($src, $alt = '', $width = '', $height = '', $parameters = '', $rules = '') {
    global $template_dir, $zco_notifier;

    // microdata
    if (!strstr($parameters, 'itemprop=') && !stristr($rules, 'nomicrodata')) $parameters = trim($parameters . ' itemprop="image"');

    // soft clean the alt tag
    $alt = zen_clean_html($alt);

    // use old method on template images
    if (strstr($src, 'includes/templates') or strstr($src, 'includes/languages') or PROPORTIONAL_IMAGES_STATUS == '0') {
      return zen_image_OLD($src, $alt, $width, $height, $parameters);
    }

//auto replace with defined missing image
    if ($src == DIR_WS_IMAGES and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
      $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }

    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

    // if not in current template switch to template_default
    if (!file_exists($src)) {
      $shared_src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'shared', $src);
      $default_src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
      if (file_exists($shared_src)) {
        $src = $shared_src;
      } else {
        $src = $default_src;
      }
    }

    // hook for handle_image() function such as Image Handler etc
    if (function_exists('handle_image')) {
      $newimg = handle_image($src, $alt, $width, $height, $parameters);
      list($src, $alt, $width, $height, $parameters) = $newimg;
      $zco_notifier->notify('NOTIFY_HANDLE_IMAGE', array($newimg));
    }

    // Convert width/height to int for proper validation.
    // intval() used to support compatibility with plugins like image-handler
    $width = empty($width) ? $width : intval($width);
    $height = empty($height) ? $height : intval($height);

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . zen_output_string($src) . '" alt="' . zen_output_string($alt) . '"';

    if (zen_not_null($alt)) {
      $image .= ' title=" ' . zen_output_string($alt) . ' "';
    }

    if ( ((CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height))) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && zen_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = $image_size[0] * $ratio;
        } elseif (zen_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }


    if (zen_not_null($width) && zen_not_null($height) and file_exists($src)) {
//      $image .= ' width="' . zen_output_string($width) . '" height="' . zen_output_string($height) . '"';
// proportional images
      $image_size = @getimagesize($src);
      // fix division by zero error
      $ratio = ($image_size[0] != 0 ? $width / $image_size[0] : 1);
      if ($image_size[1]*$ratio > $height) {
        $ratio = $height / $image_size[1];
        $width = $image_size[0] * $ratio;
      } else {
        $height = $image_size[1] * $ratio;
      }
// only use proportional image when image is larger than proportional size
      if ($image_size[0] < $width and $image_size[1] < $height) {
        $image .= ' width="' . $image_size[0] . '" height="' . intval($image_size[1]) . '"';
      } else {
        $image .= ' width="' . round($width) . '" height="' . round($height) . '"';
      }
    } else {
       // override on missing image to allow for proportional and required/not required
      if (IMAGE_REQUIRED == 'false') {
        return false;
      } else if (substr($src, 0, 4) != 'http') {
        $image .= ' width="' . intval(SMALL_IMAGE_WIDTH) . '" height="' . intval(SMALL_IMAGE_HEIGHT) . '"';
      }
    }

    // inject rollover class if one is defined. NOTE: This could end up with 2 "class" elements if $parameters contains "class" already.
    if (defined('IMAGE_ROLLOVER_CLASS') && IMAGE_ROLLOVER_CLASS != '') {
      $parameters .= (zen_not_null($parameters) ? ' ' : '') . 'class="rollover"';
    }
    // add $parameters to the tag output
    if (zen_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }

/*
 * The HTML form submit button wrapper function
 * Outputs a "submit" button in the selected language
 */
  function zen_image_submit($image, $alt = '', $parameters = '', $sec_class = '') {
    global $template, $current_page_base, $zco_notifier;
    if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes' && strlen($alt)<30) return zenCssButton($image, $alt, 'submit', $sec_class, $parameters);
    $zco_notifier->notify('PAGE_OUTPUT_IMAGE_SUBMIT');

    $image_submit = '<input type="image" src="' . zen_output_string($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $_SESSION['language'] . '/') . $image) . '" alt="' . zen_output_string($alt) . '"';

    if (zen_not_null($alt)) $image_submit .= ' title=" ' . zen_output_string($alt) . ' "';

    if (zen_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
  }

/*
 * Output a function button in the selected language
 */
  function zen_image_button($image, $alt = '', $parameters = '', $sec_class = '') {
    global $template, $current_page_base, $zco_notifier;

    // inject rollover class if one is defined. NOTE: This could end up with 2 "class" elements if $parameters contains "class" already.
    if (defined('IMAGE_ROLLOVER_CLASS') && IMAGE_ROLLOVER_CLASS != '') {
      $parameters .= (zen_not_null($parameters) ? ' ' : '') . 'class="rollover"';
    }

    $zco_notifier->notify('PAGE_OUTPUT_IMAGE_BUTTON');
    if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes') return zenCssButton($image, $alt, 'button', $sec_class, $parameters);
    return zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $_SESSION['language'] . '/') . $image, $alt, '', '', $parameters);
  }


/**
 * generate CSS buttons in the current language
 * concept from contributions by Seb Rouleau and paulm, subsequently adapted to Zen Cart
 * note: any hard-coded buttons will not be able to use this function
**/
  function zenCssButton($image = '', $text = '', $type = 'button', $sec_class = '', $parameters = '') {
    global $zco_notifier, $template, $current_page_base, $language;
    $button_name = basename($image, '.gif');
    $style = '';
    $css_button = '';

    $zco_notifier->notify('ZEN_CSS_BUTTON_BEGIN', $current_page_base, $image, $text, $type, $sec_class, $parameters, $button_name);

    // if no secondary class is set use the image name for the sec_class
    if (empty($sec_class))  $sec_class = basename($image, '.gif');
    if (!empty($sec_class)) $sec_class = ' ' . $sec_class;
    $buttonClass = (($type == 'submit') ? 'submit_button button ' : 'normal_button button ') . 'btn' . $sec_class;

    if (!empty($parameters)) $parameters = ' ' . $parameters;

    if (defined('CSS_BUTTON_MIN_WIDTH')) {
      // automatic width setting depending on the number of characters
      $min_width = (int)CSS_BUTTON_MIN_WIDTH; // this is the minimum button width, change the value as you like, by defining CSS_BUTTON_MIN_WIDTH in extra_configures
      $character_width = (defined('CSS_BUTTON_CHAR_WIDTH')) ? (int)CSS_BUTTON_CHAR_WIDTH : 6.5; // change this value depending on font size, by adding a define in extra_configures

      // added html_entity_decode function to prevent html special chars to be counted as multiple characters (like &amp;)
      $width = strlen(html_entity_decode($text)) * $character_width;
      $width = (int)$width;
      if ($width < $min_width) $width = $min_width;
      $style = ' style="min-width: ' . $width . 'px;"';
    }

    if (CSS_BUTTON_POPUPS_IS_ARRAY == 'true' || CSS_BUTTON_POPUPS_IS_ARRAY === true) {
      global $css_button_text, $css_button_opts;
      $popuptext = (!empty($css_button_text[$button_name])) ? $css_button_text[$button_name] : ($button_name . CSSBUTTONS_CATALOG_POPUPS_SHOW_BUTTON_NAMES_TEXT);
      $tooltip = ' title="' . $popuptext . '"';
    } else {
      $tooltip = '';
    }

    switch($type) {
      case 'submit':
      // form input button
      if ($parameters != '') {
        // If the input parameters include a "name" attribute, need to emulate an <input type="image" /> return value by adding a _x to the name parameter (creds to paulm)
        if (preg_match('/name="([a-zA-Z0-9\-_]+)"/', $parameters, $matches)) {
          $parameters = str_replace('name="' . $matches[1], 'name="' . $matches[1] . '_x', $parameters);
        }
        // If the input parameters include a "value" attribute, remove it since that attribute will be set to the input text string.
        if (preg_match('/(value="[a-zA-Z0=9\-_]+")/', $parameters, $matches)) {
          $parameters = str_replace($matches[1], '', $parameters);
        }
      }
      $css_button = '<input class="' . $buttonClass . '" ' . ' type="submit" value="' . $text . '"' . $tooltip . $parameters . $style . '>';
      break;

      case 'button':
      // link button
      default:
      $css_button = '<span class="' . $buttonClass . '" ' . $tooltip . $parameters . $style . '>&nbsp;' . $text . '&nbsp;</span>';
    }

    $zco_notifier->notify('ZEN_CSS_BUTTON_END', $current_page_base, $image, $text, $type, $sec_class, $parameters, $css_button);
    return $css_button;
  }


/*
 *  Output a separator either through whitespace, or with an image
 */
  function zen_draw_separator($image = 'true', $width = '100%', $height = '1') {

    // set default to use from template - zen_image will translate if not found in current template
    if ($image == 'true') {
      $image = DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_BLACK_SEPARATOR;
    } else {
      if (!strstr($image, DIR_WS_TEMPLATE_IMAGES)) {
        $image = DIR_WS_TEMPLATE_IMAGES . $image;
      }
    }
    return zen_image($image, '', $width, $height);
  }

/*
 *  Output a form
 */
  function zen_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . zen_output_string($name) . '" action="' . zen_output_string($action) . '" method="' . zen_output_string($method) . '"';

    if (zen_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';
    if (strtolower($method) == 'post') $form .= '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '" />';
    return $form;
  }

/*
 *  Output a form input field
 */
  function zen_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . zen_output_string($type) . '" name="' . zen_sanitize_string(zen_output_string($name)) . '"';
    if ( (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

/*
 *  Output a form password field
 */
  function zen_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return zen_draw_input_field($name, $value, $parameters, 'password', false);
  }

/*
 *  Output a selection field - alias function for zen_draw_checkbox_field() and zen_draw_radio_field()
 */
  function zen_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

    if (zen_not_null($value)) $selection .= ' value="' . zen_output_string($value) . '"';

    if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
      $selection .= ' checked="checked"';
    }

    if (zen_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

/*
 *  Output a form checkbox field
 */
  function zen_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return zen_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

/*
 * Output a form radio field
 */
  function zen_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return zen_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

/*
 *  Output a form textarea field
 */
  function zen_draw_textarea_field($name, $width, $height, $text = '~*~*#', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . zen_output_string($name) . '" cols="' . zen_output_string($width) . '" rows="' . zen_output_string($height) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($text == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= stripslashes($GLOBALS[$name]);
    } elseif ($text != '~*~*#' && zen_not_null($text)) {
      $field .= $text;
    }

    $field .= '</textarea>';

    return $field;
  }

/*
 *  Output a form hidden field
 */
  function zen_draw_hidden_field($name, $value = '~*~*#', $parameters = '') {
    $field = '<input type="hidden" name="' . zen_sanitize_string(zen_output_string($name)) . '"';

    if (zen_not_null($value) && $value != '~*~*#') {
      $field .= ' value="' . zen_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

/*
 * Output a form file-field
 */
  function zen_draw_file_field($name, $required = false) {
    $field = zen_draw_input_field($name, '', ' size="50" ', 'file');

    return $field;
  }


/*
 *  Hide form elements while including session id info
 *  IMPORTANT: This should be used in every FORM that has an OnSubmit() function tied to it, to prevent unexpected logouts
 */
  function zen_hide_session_id() {
    global $session_started;

    if ( ($session_started == true) && defined('SID') && zen_not_null(SID) ) {
      return zen_draw_hidden_field(zen_session_name(), zen_session_id());
    }
  }

/*
 *  Output a form pull down menu
 *  Pulls values from a passed array, with the indicated option pre-selected
 */
  function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select';

    if (!strstr($parameters, 'id=')) $field .= ' id="select-'.zen_output_string($name).'"';

    $field .= ' name="' . zen_output_string($name) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>' . "\n";

    if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) ) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '  <option value="' . zen_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . zen_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
    }
    $field .= '</select>' . "\n";

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

/*
 * Creates a pull-down list of countries
 */
  function zen_get_country_list($name, $selected = '', $parameters = '') {
    $countriesAtTopOfList = array();
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = zen_get_countries();

    // Set some default entries at top of list:
    if (STORE_COUNTRY != SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY) $countriesAtTopOfList[] = SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY;
    $countriesAtTopOfList[] = STORE_COUNTRY;
    // IF YOU WANT TO ADD MORE DEFAULTS TO THE TOP OF THIS LIST, SIMPLY ENTER THEIR NUMBERS HERE.
    // Duplicate more lines as needed
    // Example: Canada is 108, so use 108 as shown:
    //$countriesAtTopOfList[] = 108;

    //process array of top-of-list entries:
    foreach ($countriesAtTopOfList as $key=>$val) {
      $countries_array[] = array('id' => $val, 'text' => zen_get_country_name($val));
    }
    // now add anything not in the defaults list:
    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $alreadyInList = FALSE;
      foreach($countriesAtTopOfList as $key=>$val) {
        if ($countries[$i]['countries_id'] == $val)
        {
          // If you don't want to exclude entries already at the top of the list, comment out this next line:
          $alreadyInList = TRUE;
          continue;
        }
      }
      if (!$alreadyInList) $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return zen_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }
/*
 * Assesses suitability for additional parameters such as rel=nofollow etc
 */
  function zen_href_params($page = '', $parameters = '') {
    global $current_page_base;
    $addparms = '';
    // if nofollow has already been set, ignore this function
    if (stristr($parameters, 'nofollow')) return $parameters;
    // if list of skippable pages has been set in meta_tags.php lang file (is by default), use that to add rel=nofollow params
    if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($page, explode(",", constant('ROBOTS_PAGES_TO_SKIP')))
        || $current_page_base=='down_for_maintenance') $addparms = 'rel="nofollow"';
    return ($parameters == '' ? $addparms : $parameters . ' ' . $addparms);
  }
////
// output label for input fields
  function zen_draw_label($text, $for, $parameters = ''){
    $label = '<label for="' . $for . '" ' . $parameters . '>' . $text . '</label>';
    return $label;
  }
