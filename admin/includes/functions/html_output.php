<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 22 Modified in v1.5.7 $
 */

////
// The HTML href link wrapper function
function zen_href_link($page = '', $parameters = '', $connection = 'SSL', $add_session_id = true) {
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
        $parameters
    );
    $page = str_replace('.php', '', $page);

    $link = HTTP_SERVER . DIR_WS_ADMIN;

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

  function zen_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL') {
    global $zco_notifier;
    $link = null;
    $zco_notifier->notify('NOTIFY_SEFU_INTERCEPT_ADMCATHREF', array(), $link, $page, $parameters, $connection);
    if($link !== null) return $link;

    if ($connection == 'NONSSL') {
      $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL_CATALOG == 'true') {
        $link = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
      }
    } else {
      trigger_error("zen_catalog_href_link($page, $parameters, $connection), Unable to determine connection method on a link! Known methods: NONSSL SSL", E_USER_ERROR);
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL<br><br>Function used:<br><br>zen_catalog_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</b>');
    }
    if ($parameters == '') {
      $link .= 'index.php?main_page='. $page;
    } else {
      $link .= 'index.php?main_page='. $page . "&" . zen_output_string($parameters);
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
      $link = preg_replace('/(&{2,}|(&amp;)+)/', '&', $link);

      // Convert any remaining '&' into '&amp;' (valid URL for href)
      $link = str_replace('&', '&amp;', $link);

    return $link;
  }

function zen_catalog_base_link($connection = '')
{
    global $zco_notifier, $request_type;

    if (empty($connection)) {
        $connection = $request_type;
    }

    $link = null;
    $zco_notifier->notify('NOTIFY_SEFU_INTERCEPT_ADMCATHOME', array(), $link, $connection);
    if ($link !== null) return $link;

    switch ($connection) {
        case 'NONSSL':
            $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
            break;

        case 'SSL':
        default:
            if (ENABLE_SSL_CATALOG == 'true') {
                $link = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG;
            } else {
                $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
            }
    }

    return $link;
}

////
// The HTML image wrapper function
  function zen_image($src, $alt = '', $width = '', $height = '', $params = '') {
      $image = '<img src="' . $src . '" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title="' . $alt . '"';
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
function zen_image_submit($image, $alt = '', $parameters = '')
{
    $image_submit = '<input type="image" src="' . zen_output_string(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image) . '" alt="' . zen_output_string($alt) . '"';
    if (zen_not_null($alt)) {
        $image_submit .= ' title="' . zen_output_string($alt) . '"';
    }
    if (zen_not_null($parameters)) {
        $image_submit .= ' ' . $parameters;
    }
    $image_submit .= '>';
    return $image_submit;
}

////
// Draw a 1 pixel black line
  function zen_black_line() {
    return zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '1', 'style="width:100%;"');
  }

////
// Output a separator either through whitespace, or with an image
  function zen_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    if (substr(rtrim($width), -1) != "%") $width = $width . 'px';
    return zen_image(DIR_WS_IMAGES . $image, '', '', $height, 'style="width:' . $width . ';"');
  }

////
// Output a function button in the selected language
  function zen_image_button($image, $alt = '', $params = '') {

    return zen_image(DIR_WS_LANGUAGES . $_SESSION['language'] . '/images/buttons/' . $image, $alt, '', '', $params);
  }

////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function zen_js_zone_list($country, $form, $field, $showTextField = true) {
    global $db;
    $countries = $db->Execute("SELECT DISTINCT zone_country_id
                               FROM " . TABLE_ZONES . "
                               ORDER BY zone_country_id");

    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      }

      $states = $db->Execute("SELECT zone_name, zone_id
                              FROM " . TABLE_ZONES . "
                              WHERE zone_country_id = '" . $countries->fields['zone_country_id'] . "'
                              ORDER BY zone_name");


      $num_state = 1;
      while (!$states->EOF) {
        if ($num_state == 1) $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states->fields['zone_name'] . '", "' . $states->fields['zone_id'] . '");' . "\n";
        $num_state++;
        $states->MoveNext();
      }
      $num_country++;
      $countries->MoveNext();
    }
      $output_string .= '  }';
      if ($showTextField) {
          $output_string .= ' else {' . "\n" .
              '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
              '  }' . "\n";
      }
    return $output_string;
  }

////
// Output a form
  function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
    $form = '<form name="' . zen_output_string($name) . '" action="';
    if (zen_not_null($parameters)) {
      $form .= zen_href_link($action, $parameters, 'NONSSL');
    } else {
      $form .= zen_href_link($action, '', 'NONSSL');
    }
    $form .= '" method="' . zen_output_string($method) . '"';
    if (zen_not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';
    if (strtolower($method) == 'post') $form .= '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '" />';
    if (strtolower($method) == 'get') {
      $form .= '<input type="hidden" name="cmd" value="' . (isset($_GET['cmd']) ? $_GET['cmd'] : 'home') . '">';
    }
    return $form;
  }

////
// Output a form input field
  function zen_draw_input_field($name, $value = '~*~*#', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $type = zen_output_string($type);
    if ($type === 'price') $type = 'number" step="0.01';

    $field = '<input type="' . $type . '" name="' . zen_output_string($name) . '"';

    if ( $value == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif ($value != '~*~*#' && zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    if ($required && !empty(TEXT_FIELD_REQUIRED)) {
      $field .= '&nbsp;<span class="alert">' . TEXT_FIELD_REQUIRED . '</span>';
    }
    return $field;
  }

////
// Output a form password field
  function zen_draw_password_field($name, $value = '', $required = false, $parameters = '',$autocomplete = false) {
    $parameters .= ' maxlength="40"';
    if($autocomplete == false){
      $parameters .= ' autocomplete="off"';
    }
    $field = zen_draw_input_field($name, $value, $parameters, $required, 'password', false);

    return $field;
  }

////
// Output a form file field
  function zen_draw_file_field($name, $required = false, $parameters = '') {
    $field = zen_draw_input_field($name, '', ' size="50" ' . $parameters, $required, 'file');

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
  function zen_draw_hidden_field($name, $value = '~*~*#', $parameters = '') {
    $field = '<input type="hidden" name="' . zen_output_string($name) . '"';

    if (zen_not_null($value) && $value != '~*~*#') {
      $field .= ' value="' . zen_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

/**
 * Output a form pull down menu
 * @param string $name name
 * @param array $values values
 * @param string $default default value
 * @param string $parameters parameters
 * @param boolean $required required
 * @return string
 */
  function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
  //    $field = '<select name="' . zen_output_string($name) . '"';
    $field = '<select rel="dropdown" name="' . zen_output_string($name) . '"';

    if (zen_not_null($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= '>' . "\n";

    if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $default = stripslashes($GLOBALS[$name]);
    }

    foreach ($values as $value) {
      $field .= '<option value="' . zen_output_string($value['id']) . '"';
      if ($default == $value['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . zen_output_string($value['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n";
    }
    $field .= '</select>' . "\n";

    if ($required == true) {
      $field .= TEXT_FIELD_REQUIRED;
    }

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
