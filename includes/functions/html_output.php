<?php
/**
 * html_output.php
 * HTML-generating functions used throughout the core
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Oct 04 Modified in v2.1.0 $
 */

/*
 * The HTML href link wrapper function
 */
  function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
    global $request_type, $session_started, $http_domain, $https_domain, $zco_notifier;
    $link = null;
    $zco_notifier->notify('NOTIFY_SEFU_INTERCEPT', array(), $link, $page, $parameters, $connection, $add_session_id, $static, $use_dir_ws_catalog);
    if($link !== null) return $link;

    if (empty($page)) {
        trigger_error("zen_href_link($page, $parameters, $connection), unable to determine the page link.");
        $page = FILENAME_DEFAULT;
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER;
    } elseif ($connection == 'SSL' || $connection == '') {
      if (ENABLE_SSL == 'true') {
        $link = HTTPS_SERVER ;
      } else {
        $link = HTTP_SERVER;
      }
    } else {
      trigger_error("zen_href_link($page, $parameters, $connection), Unable to determine connection method on a link! Known methods: NONSSL SSL");
      $link = HTTP_SERVER;
    }

    if ($use_dir_ws_catalog) {
      if ($connection == 'SSL' && ENABLE_SSL == 'true') {
        $link .= DIR_WS_HTTPS_CATALOG;
      } else {
        $link .= DIR_WS_CATALOG;
      }
    }

    if (!$static) {
      if (!empty($parameters)) {
        $link .= 'index.php?main_page='. $page . "&" . zen_output_string($parameters);
      } else {
        $link .= 'index.php?main_page=' . $page;
      }
    } else {
      if (!empty($parameters)) {
        $link .= $page . "?" . zen_output_string($parameters);
      } else {
        $link .= $page;
      }
    }

    $separator = '&';

    while (substr($link, -1) == '&' || substr($link, -1) == '?') $link = substr($link, 0, -1);
// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ($add_session_id == true && $session_started == true && SESSION_FORCE_COOKIE_USE == 'False') {
      if (defined('SID') && !empty(constant('SID'))) {
        $sid = constant('SID');
      } elseif ( ($request_type == 'NONSSL' && $connection == 'SSL' && ENABLE_SSL == 'true') || ($request_type == 'SSL' && $connection == 'NONSSL') ) {
        if ($http_domain != $https_domain) {
          $sid = zen_session_name() . '=' . zen_session_id();
        }
      }
    }

// clean up the link before processing
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    if (SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $search_engine_safe == true) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('&amp;', '/', $link);
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);

      $separator = '?';
    }

    if (isset($sid)) {
      $link .= $separator . zen_output_string($sid);
    }

// clean up the link after processing
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    $link = preg_replace('/&/', '&amp;', $link);
    $link = preg_replace('~//$~', '/', $link);
    return $link;
  }

/*
 * This function, added to the storefront in zc1.5.6, provides a common method for
 * plugins that span the admin and storefront to create a storefront (a.k.a catalog)
 * link.
 */
function zen_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL')
{
    return zen_href_link($page, $parameters, $connection, false);
}

/*
 * The HTML image wrapper function for non-proportional images
 * used when "proportional images" is turned off or if calling from a template directory
 */
function zen_image_OLD($src, $title = '', $width = '', $height = '', $parameters = '')
{
    global $template_dir;

    //auto replace with defined missing image
    if ($src === DIR_WS_IMAGES && PRODUCTS_IMAGE_NO_IMAGE_STATUS === '1') {
        $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }

    if ((empty($src) || $src === DIR_WS_IMAGES) && IMAGE_REQUIRED === 'false') {
        return false;
    }

    // if not in current template switch to template_default
    $file_exists = is_file($src);
    if ($file_exists === false) {
        $src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
        $file_exists = is_file($src);
    }
    if ($file_exists === false && IMAGE_REQUIRED === 'false') {
        return false;
    }

    $image = '<img src="' . zen_output_string($src) . '"';

    if (empty($title)) {
        $alt_text = (defined('IMAGE_ALT_TEXT_NO_TITLE')) ? IMAGE_ALT_TEXT_NO_TITLE : 'n/a';
    } else {
        $title_text = zen_output_string($title);
        $alt_text = ((defined('IMAGE_ALT_PREFIX')) ? (IMAGE_ALT_PREFIX . ' ') : '') . $title_text;
        $image .= ' title="' . $title_text . '"';
    }
    $image .= ' alt="' . $alt_text . '"';

    $width = (int)$width;
    $height = (int)$height;
    if ($file_exists === true && CONFIG_CALCULATE_IMAGE_SIZE === 'true' && ($width === 0 || $height === 0)) {
        $image_size = getimagesize($src);
        if ($image_size === false) {
            if (IMAGE_REQUIRED === 'false') {
                return false;
            }
        } elseif ($width === 0 && $height === 0) {
            $width = (int)$image_size[0];
            $height = (int)$image_size[1];
        } elseif ($width === 0) {
            $ratio = $height / $image_size[1];
            $width = (int)($image_size[0] * $ratio);
        } else {
            $ratio = $width / $image_size[0];
            $height = (int)($image_size[1] * $ratio);
        }
    }

    if ($width !== 0 && $height !== 0) {
        $image .= ' width="' . $width . '" height="' . $height . '"';
    }

    if (!empty($parameters)) {
        $image .= ' ' . $parameters;
    }

    $image .= '>';

    return $image;
}

/*
 * The HTML image wrapper function
 */
function zen_image($src, $title = '', $width = '', $height = '', $parameters = '')
{
    global $template_dir, $zco_notifier;

    // soft clean the title attribute's value
    $title = zen_clean_html($title);

    // use old method on template images
    if (strpos($src, 'includes/templates') !== false || strpos($src, 'includes/languages') !== false || PROPORTIONAL_IMAGES_STATUS === '0') {
        return zen_image_OLD($src, $title, $width, $height, $parameters);
    }

    //auto replace with defined missing image
    if ($src === DIR_WS_IMAGES && PRODUCTS_IMAGE_NO_IMAGE_STATUS === '1') {
        $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }

    if ((empty($src) || ($src === DIR_WS_IMAGES)) && IMAGE_REQUIRED === 'false') {
        return false;
    }

    // if not in current template switch to template_default
    if (!is_file($src)) {
        $src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
    }

    // hook for handle_image() function such as Image Handler etc
    if (function_exists('handle_image')) {
        $newimg = handle_image($src, $title, $width, $height, $parameters);
        list($src, $title, $width, $height, $parameters) = $newimg;
        $zco_notifier->notify('NOTIFY_HANDLE_IMAGE', [$newimg]);
    }

    //image is defined but is missing
    if (PRODUCTS_IMAGE_NO_IMAGE_STATUS === '1' && !is_file($src)) {
        $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }
    
    $zco_notifier->notify('NOTIFY_OPTIMIZE_IMAGE', $template_dir, $src, $title, $width, $height, $parameters);

    // Determine if the source-file exists.
    $file_exists = is_file($src);
    $image_size = false;
    if ($file_exists === true) {
        $image_size = getimagesize($src);
    }
    if ($image_size === false && IMAGE_REQUIRED === 'false') {
        return false;
    }

    // Convert width/height to int for proper validation.
    $width = empty($width) ? 0 : (int)$width;
    $height = empty($height) ? 0 : (int)$height;

    $image = '<img src="' . zen_output_string($src) . '"';

    if (empty($title)) {
        $alt_text = (defined('IMAGE_ALT_TEXT_NO_TITLE')) ? IMAGE_ALT_TEXT_NO_TITLE : 'n/a';
    } else {
        $title_text = zen_output_string($title);
        $alt_text = ((defined('IMAGE_ALT_PREFIX')) ? (IMAGE_ALT_PREFIX . ' ') : '') . $title_text;
        $image .= ' title="' . $title_text . '"';
    }
    $image .= ' alt="' . $alt_text . '"';

    if ($image_size !== false && CONFIG_CALCULATE_IMAGE_SIZE === 'true' && ($width === 0 || $height === 0)) {
        if ($width === 0 && $height === 0) {
            $width = $image_size[0];
            $height = $image_size[1];
        } elseif ($width === 0) {
            $ratio = $height / $image_size[1];
            $width = (int)($image_size[0] * $ratio);
        } else {
            $ratio = $width / $image_size[0];
            $height = (int)($image_size[1] * $ratio);
        }
    }

    if ($image_size !== false && $width !== 0 && $height !== 0) {
        // fix division by zero error
        $ratio = ($image_size[0] !== 0) ? $width / $image_size[0] : 1;
        if ($image_size[1] * $ratio > $height) {
            $ratio = $height / $image_size[1];
            $width = (int)($image_size[0] * $ratio);
        } else {
            $height = (int)($image_size[1] * $ratio);
        }

        // only use proportional image when image is larger than proportional size
        if ($image_size[0] < $width && $image_size[1] < $height) {
            $image .= ' width="' . $image_size[0] . '" height="' . $image_size[1] . '"';
        } else {
            $image .= ' width="' . (int)round($width) . '" height="' . (int)round($height) . '"';
        }
    } elseif (strpos($src, 'http') !== 0) {
        $image .= ' width="' . (int)SMALL_IMAGE_WIDTH . '" height="' . (int)SMALL_IMAGE_HEIGHT . '"';
    }

    // inject rollover class if one is defined. NOTE: This could end up with 2 "class" elements if $parameters contains "class" already.
    if (defined('IMAGE_ROLLOVER_CLASS') && IMAGE_ROLLOVER_CLASS !== '') {
        $parameters .= (!empty($parameters) ? ' ' : '') . 'class="rollover"';
    }
    // add $parameters to the tag output
    if (!empty($parameters)) {
        $image .= ' ' . $parameters;
    }

    $image .= '>';

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

    if (!empty($alt)) $image_submit .= ' title="' . zen_output_string($alt) . '"';

    if (!empty($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

/*
 * Output a function button in the selected language
 */
  function zen_image_button($image, $alt = '', $parameters = '', $sec_class = '') {
    global $template, $current_page_base, $zco_notifier;

    // inject rollover class if one is defined. NOTE: This could end up with 2 "class" elements if $parameters contains "class" already.
    if (defined('IMAGE_ROLLOVER_CLASS') && IMAGE_ROLLOVER_CLASS != '') {
      $parameters .= (!empty($parameters) ? ' ' : '') . 'class="rollover"';
    }

    $zco_notifier->notify('PAGE_OUTPUT_IMAGE_BUTTON');
    if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes') {
        if (preg_match('/\.(png|gif|jpe?g|webp)/i', $image)) {
            return zenCssButton($image, $alt, 'button', $sec_class, $parameters);
        } else {
            return zen_draw_button($image, $sec_class, '', $parameters, $alt, 'button');
        }
    }
    return zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $_SESSION['language'] . '/') . $image, $alt, '', '', $parameters);
  }


/**
 * Draw a <button> element
 **/
function zen_draw_button($text = '', $added_classes = '', $id = '', $parameters = '', $title = '', $type = 'button')
{
    global $zco_notifier;

    // legacy support
    // remove .gif etc suffix if any
    $text = preg_replace('/\.(png|gif|jpe?g|webp)$/', '', $text);
    $text = str_replace('_', ' ', Illuminate\Support\Str::title($text));

    $classes = '';
    // optionally force something like 'btn' into the current template's buttons by defining a constant for the template:
    if (defined('TEMPLATE_BASE_CSS_BUTTON_CLASSES')) {
        $classes .= constant('TEMPLATE_BASE_CSS_BUTTON_CLASSES');
    }

    $the_button = '';

    $zco_notifier->notify('NOTIFY_ZEN_DRAW_BUTTON', null, $text, $classes, $added_classes, $id, $parameters, $title, $type, $the_button);

    if (empty($the_button)) {
        $the_button = '<button class="' . $classes;
        if (!empty($added_classes)) {
            $the_button .= (empty($classes) ? '' : ' ') . $added_classes;
        }
        $the_button .= '"';

        if (!empty($id)) {
            $the_button .= ' id="' . $id . '"';
        }

        if ($type != 'button') {
            $the_button .= ' type="' . $type . '"';
        }

        if (!empty($parameters)) {
            $the_button .= ' ' . $parameters;
        }

        if (!empty($title)) {
            $the_button .= ' title="' . $title . '"';
        }

        $the_button .= '>';
        $the_button .= $text;
        $the_button .= '</button>';
    }

    return $the_button;
}

/**
 * generate CSS buttons in the current language
 * concept from contributions by Seb Rouleau and paulm, subsequently adapted to Zen Cart
 * note: any hard-coded buttons will not be able to use this function
 **/
  function zenCssButton($image = '', $text = '', $type = 'button', $sec_class = '', $parameters = '') {
   global $css_button_text, $css_button_opts, $template, $current_page_base, $language;

   $button_name = basename($image);
   $button_name = preg_replace('/\.(png|gif|jpe?g|webp)$/', '', $button_name);

    // if no secondary class is set use the image name for the sec_class
    if (empty($sec_class)) $sec_class = $button_name;
    if(!empty($sec_class)) $sec_class = ' ' . $sec_class;
    if(!empty($parameters))$parameters = ' ' . $parameters;
    $mouse_out_class  = 'cssButton ' . (($type == 'submit') ? 'submit_button button ' : 'normal_button button ') . $sec_class;
    $mouse_over_class = 'cssButtonHover ' . (($type == 'button') ? 'normal_button button ' : '') . $sec_class . $sec_class . 'Hover';
    // javascript to set different classes on mouseover and mouseout: enables hover effect on the buttons
    // (pure css hovers on non link elements do work work in every browser)
    $css_button_js =  'onmouseover="this.className=\''. $mouse_over_class . '\'" onmouseout="this.className=\'' . $mouse_out_class . '\'"';

    if (defined('CSS_BUTTON_POPUPS_IS_ARRAY') && CSS_BUTTON_POPUPS_IS_ARRAY == 'true') {
      $popuptext = (!empty($css_button_text[$button_name])) ? $css_button_text[$button_name] : ($button_name . CSSBUTTONS_CATALOG_POPUPS_SHOW_BUTTON_NAMES_TEXT);
      $tooltip = ' title="' . $popuptext . '"';
    } else {
      $tooltip = '';
    }
    $css_button = '';

    if ($type == 'submit'){
      // form input button
      if ($parameters != '') {
        // If the input parameters include a "name" attribute, need to emulate an <input type="image"> return value by adding a _x to the name parameter (creds to paulm)
        if (preg_match('/name="([a-zA-Z0-9\-_]+)"/', $parameters, $matches)) {
          $parameters = str_replace('name="' . $matches[1], 'name="' . $matches[1] . '_x', $parameters);
        }
        // If the input parameters include a "value" attribute, remove it since that attribute will be set to the input text string.
        if (preg_match('/(value="[a-zA-Z0=9\-_]+")/', $parameters, $matches)) {
          $parameters = str_replace($matches[1], '', $parameters);
        }
      }

      // Give an observer the chance to provide alternate formatting for the button (it's set to an empty
      // string above).  If the value is still empty after the notification, create the standard-format
      // of the button.
      $GLOBALS['zco_notifier']->notify(
            'NOTIFY_ZEN_CSS_BUTTON_SUBMIT',
            array(
                'button_name' => $button_name,
                'text' => $text,
                'sec_class' => $sec_class,
                'parameters' => $parameters,
            ),
            $css_button
      );
      if ($css_button == '') {
        $css_button = '<input class="' . $mouse_out_class . '" ' . $css_button_js . ' type="submit" value="' . $text . '"' . $tooltip . $parameters . '>';
      }
    }

    if ($type=='button') {
      // link button
      // -----
      // Give an observer the chance to provide alternate formatting for the button (it's set to an empty string
      // above).  If the value is still empty after the notification, create the standard-format
      // of the button.
      $GLOBALS['zco_notifier']->notify(
            'NOTIFY_ZEN_CSS_BUTTON_BUTTON',
            array(
                'button_name' => $button_name,
                'text' => $text,
                'sec_class' => $sec_class,
                'parameters' => $parameters,
            ),
            $css_button
      );
      if ($css_button == '') {
        $css_button = '<span class="' . $mouse_out_class . '" ' . $css_button_js . $tooltip . $parameters . '>&nbsp;' . $text . '&nbsp;</span>';
      }
    }
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


/**
 * generates javascript for dynamically updating the states/provinces list when the country is changed
 *
 * @param string $country number
 * @param string $form html form name to attach to
 * @param string $field html field to attach to
 * @return string javascript segment
 */
function zen_js_zone_list(string $country, string $form, string $field) {
    global $db;
    $sql = "SELECT DISTINCT zone_country_id
            FROM " . TABLE_ZONES . "
            ORDER BY zone_country_id";
    $countries = $db->Execute($sql);
    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
        if ($num_country == 1) {
            $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
        } else {
            $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
        }

        $sql = "SELECT zone_name, zone_id
                FROM " . TABLE_ZONES . "
                WHERE zone_country_id = " . (int)$countries->fields['zone_country_id'] . "
                ORDER BY zone_name";
        $results = $db->Execute($sql);
        $num_state = 1;
        foreach ($results as $state) {
            if ($num_state == 1) $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
            $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $state['zone_name'] . '", "' . $state['zone_id'] . '");' . "\n";
            $num_state++;
        }
        $num_country++;
        $countries->MoveNext();
        $output_string .= '    hideStateField(' . $form . ');' . "\n" ;
    }
    $output_string .= '  } else {' . "\n" .
        '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
        '    showStateField(' . $form . ');' . "\n" .
        '  }' . "\n";
    return $output_string;
}


/*
 *  Output a form
 */
  function zen_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . zen_output_string($name) . '" action="' . zen_output_string($action) . '" method="' . zen_output_string($method) . '"';

    if (!empty($parameters)) $form .= ' ' . $parameters;

    $form .= '>';
    if (strtolower($method) == 'post') $form .= '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '">';
    return $form;
  }

/*
 *  Output a form input field
 */
  function zen_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true, $required = false) {
    // -----
    // Give an observer the opportunity to **totally** override this function's operation.
    //
    $field = false;
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_INPUT_FIELD_OVERRIDE',
        array(
            'name' => $name,
            'value' => $value,
            'parameters' => $parameters,
            'type' => $type,
            'reinsert_value' => $reinsert_value,
            'required' => $required,
        ),
        $field
    );
    if ($field !== false) {
        return $field;
    }

    $field = '<input type="' . zen_output_string($type) . '" name="' . zen_sanitize_string(zen_output_string($name)) . '"';
    if (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && $reinsert_value == true) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    }

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    // -----
    // Give an observer the opportunity to modify the just-rendered field.
    //
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_INPUT_FIELD',
        array(
            'name' => $name,
            'value' => $value,
            'parameters' => $parameters,
            'type' => $type,
            'reinsert_value' => $reinsert_value,
            'required' => $required,
        ),
        $field
    );

    if ($required == true && !empty(TEXT_FIELD_REQUIRED)) {
      $field .= TEXT_FIELD_REQUIRED;
    }

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
    // -----
    // Give an observer the opportunity to **totally** override this function's operation.
    //
    $selection = false;
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_SELECTION_FIELD_OVERRIDE',
        array(
            'name' => $name,
            'value' => $value,
            'parameters' => $parameters,
            'type' => $type,
            'checked' => $checked
        ),
        $selection
    );
    if ($selection !== false) {
        return $selection;
    }

    $selection = '<input type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

    if (zen_not_null($value)) $selection .= ' value="' . zen_output_string($value) . '"';

    if (
        ($checked == true) ||
        (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) &&
            ($GLOBALS[$name] == 'on' || (isset($value) && stripslashes($GLOBALS[$name]) == $value))
        )
    ) {
      $selection .= ' checked="checked"';
    }

    if (!empty($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

    // -----
    // Give an observer the opportunity to modify the just-rendered field.
    //
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_SELECTION_FIELD',
        array(
            'name' => $name,
            'value' => $value,
            'parameters' => $parameters,
            'type' => $type,
            'checked' => $checked
        ),
        $selection
    );
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
    // -----
    // Give an observer the opportunity to **totally** override this function's operation.
    //
    $field = false;
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_TEXTAREA_FIELD_OVERRIDE',
        array(
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'text' => $text,
            'parameters' => $parameters,
            'reinsert_value' => $reinsert_value,
        ),
        $field
    );
    if ($field !== false) {
        return $field;
    }

    $field = '<textarea name="' . zen_output_string($name) . '" cols="' . zen_output_string($width) . '" rows="' . zen_output_string($height) . '"';

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($text == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= stripslashes($GLOBALS[$name]);
    } elseif ($text != '~*~*#' && zen_not_null($text)) {
      $field .= $text;
    }

    $field .= '</textarea>';

    // -----
    // Give an observer the opportunity to modify the just-rendered field.
    //
    $GLOBALS['zco_notifier']->notify(
        'NOTIFY_ZEN_DRAW_TEXTAREA_FIELD',
        array(
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'text' => $text,
            'parameters' => $parameters,
            'reinsert_value' => $reinsert_value,
        ),
        $field
    );
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

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

/*
 * Output a form file-field
 * @param string $name name
 * @param boolean $required required
 * @return string
 */
  function zen_draw_file_field($name, $required = false) {
    $field = zen_draw_input_field($name, '', ' size="50" ', 'file', false, $required);

    return $field;
  }


/*
 *  Hide form elements while including session id info
 *  IMPORTANT: This should be used in every FORM that has an OnSubmit() function tied to it, to prevent unexpected logouts
 */
  function zen_hide_session_id() {
    global $session_started;

    if ($session_started == true && defined('SID') && !empty(SID) ) {
      return zen_draw_hidden_field(zen_session_name(), zen_session_id());
    }
  }

  /**
 *  Output a form pull down menu
 *  Pulls values from a passed array, with the indicated option pre-selected
 * @param string $name name
 * @param array $values values
 * @param string $default default value
 * @param string $parameters parameters
 * @param boolean $required required
 * @return string
 */
function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false)
{
  // -----
  // Give an observer the opportunity to **totally** override this function's operation.
  //
  $field = false;
  $GLOBALS['zco_notifier']->notify(
      'NOTIFY_ZEN_DRAW_PULL_DOWN_MENU_OVERRIDE',
      array(
        'name' => $name,
        'values' => $values,
        'default' => $default,
        'parameters' => $parameters,
        'required' => $required,
      ),
      $field
  );
  if ($field !== false) {
    return $field;
  }

  $field = '<select ';

  if (strpos($parameters, 'id=') === false) {
    $field .= ' id="select-' . zen_output_string($name) . '"';
  }

  $field .= ' name="' . zen_output_string($name) . '"';

  if (!empty($parameters)) {
    $field .= ' ' . $parameters;
  }

  $field .= '>' . "\n";

  if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
    $default = stripslashes($GLOBALS[$name]);
  }

  foreach ($values as $value) {
    $field .= '  <option value="' . zen_output_string($value['id']) . '"';
    if ($default == $value['id']) {
      $field .= ' selected="selected"';
    }

    $field .= '>' . zen_output_string($value['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
  }
  $field .= '</select>' . "\n";

  if ($required == true) {
     $field .= TEXT_FIELD_REQUIRED;
   }
  // -----
  // Give an observer the chance to make modifications to the just-rendered field.
  //
  $GLOBALS['zco_notifier']->notify(
      'NOTIFY_ZEN_DRAW_PULL_DOWN_MENU',
      array(
        'name' => $name,
        'values' => $values,
        'default' => $default,
        'parameters' => $parameters,
        'required' => $required,
      ),
      $field
  );
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
    if (SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY !== '' && STORE_COUNTRY !== SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY) $countriesAtTopOfList[] = SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY;
    $countriesAtTopOfList[] = STORE_COUNTRY;
    // IF YOU WANT TO ADD MORE DEFAULTS TO THE TOP OF THIS LIST, SIMPLY ENTER THEIR NUMBERS HERE.
    // Duplicate more lines as needed
    // Example: Canada is 38, so use 38 as shown:
    //$countriesAtTopOfList[] = 38;
    // United Kingdom is 222, so would use 222 as shown:
    //$countriesAtTopOfList[] = 222;

    //process array of top-of-list entries:
    foreach ($countriesAtTopOfList as $key=>$val) {
      // -----
      // Account for the possibility that one of the top-of-list countries has been disabled.  If
      // that's the case, issue a PHP notice since the condition really shouldn't happen!
      //
      $country_name = zen_get_country_name($val);
      if ($country_name === '') {
        trigger_error('Country with countries_id = ' . $val . ' is either disabled or does not exist.', E_USER_NOTICE);
      } else {
        $countries_array[] = array('id' => $val, 'text' => $country_name);
      }
    }
    // now add anything not in the defaults list:
    for ($i=0, $n=count($countries); $i<$n; $i++) {
      $alreadyInList = FALSE;
      foreach($countriesAtTopOfList as $key=>$val) {
        if ($countries[$i]['countries_id'] == $val)
        {
          // If you don't want to exclude entries already at the top of the list, comment out this next line:
          $alreadyInList = TRUE;
          break; // found the duplicate, no further need to process this loop
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
/**
 * @param string $text
 * @param string $for
 * @param string $parameters
 * @return string
 */
function zen_draw_label($text, $for, $parameters = '')
{
    $label = '<label for="' . $for . '"' . (!empty($parameters) ? ' ' . $parameters : '') . '>' . $text . '</label>';
    return $label;
}
