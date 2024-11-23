<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Paul Williams 2024 Oct 13 Modified in v2.1.0 $
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

    if (empty($page) || ($page == FILENAME_DEFAULT && empty($parameters))) {
        // If the request was for the homepage, do nothing
        $separator = '?';
    }
    else if (!empty($parameters)) {
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
    if ($src === DIR_WS_CATALOG_IMAGES) {
      return '';
    }
    $image = '<img src="' . $src . '" alt="' . zen_output_string($alt) . '"';
    // soft clean the alt tag
    $alt = zen_clean_html($alt);
    if ($alt) {
      $image .= ' title="' . zen_output_string($alt) . '"';
    }

    $styles = '';

    if ($width !== '' && !str_contains($params, 'width:')) {
        $width = trim($width);
        $styles .= 'width:' . $width . (!str_ends_with($width, '%') ? 'px' : '') . '; ';
    }

    if ($height !== '' && !str_contains($params, 'height:')) {
        $height = trim($height);
        $styles .= 'height:' . $height . (!str_ends_with($height, '%') ? 'px' : '') . '; ';
    }

    if (str_contains($params, 'style=')) {
        $params = str_replace('style="', 'style="' . $styles, $params);
    } else {
        $params .= ' style="' . $styles . '"';
    }


    if ($params) {
      $image .= ' ' . trim($params);
    }
    $image .= '>';

    return $image;
  }

/**
 * @deprecated since v1.5.8. Use <button> markup instead
 */
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

/**
 * Provide a mapping from simple icon names to the FontAwesome classes that achieve them.
 * Borrow colour styles: Red: txt-status-on, Yellow: txt-linked, Green: txt-status-on
 */
$iconMap = [
  'caret-right' => 'fa-caret-right txt-navy',
  'circle-info' => 'fa-circle-info txt-black',
  'edit' => 'fa-pencil text-success',
  'popup' => 'fa-up-right-from-square txt-black',
  'enabled' => 'fa-square txt-status-on',
  'linked' => 'fa-square txt-linked',
  'disabled' => 'fa-square txt-status-off',
  'new-window' => 'fa-square txt-orange',
  'new-window-off' => [
    'fa-square fa-stack-2x opacity-25 txt-orange',
    'fa-xmark fa-stack-1x txt-red'
  ],
  'line-chart' => 'fa-line-chart txt-black',
  'calendar-days' => 'fa-regular fa-calendar-days',
  'status-green' => [
    'fa-solid fa-circle fa-stack-1x txt-status-on',
    'fa-regular fa-circle fa-stack-1x txt-black'
  ],
  'status-yellow' => [
    'fa-solid fa-circle fa-stack-1x txt-linked',
    'fa-regular fa-circle fa-stack-1x txt-black'
  ],
  'status-red' => [
    'fa-solid fa-circle fa-stack-1x txt-status-off',
    'fa-regular fa-circle fa-stack-1x txt-black'
  ],
  'status-red-light' => [
    'fa-solid fa-circle fa-stack-1x txt-status-off txt-light',
    'fa-regular fa-circle fa-stack-1x txt-black'
  ],
  'pencil' => 'fa-pencil',
  'trash' => 'fa-trash-alt',
  'preview' => 'fa-magnifying-glass',
  'move' => 'fa-arrow-right-to-bracket',
  'metatags' => 'fa-asterisk',
  'image' => 'fa-image',
  'tick' => 'fa-check txt-status-on',
  'cross' => 'fa-xmark txt-status-off',
  'star' => 'fa-star txt-gold',
  'star-shadow' => 'fa-star txt-gold star-shadow',
  'locked' => 'fa-lock',
  'unlocked' => 'fa-lock-open',
  'loading' => 'fa-gear fa-spin'
];

/**
 * Return a FontAwesome icon according to $icon, with optional tooltip and size specifier.
 *
 * @param string $icon Nickname for the icon to return.
 * @param string $tooltip Optional tooltip to show on hover.  Uses Bootstrap `data-toggle`.
 * @param string $size One of `2x`, `lg` or blank.  Most icons are 2x but some need to be less intrusive.
 * @param bool   $fixedWidth If true, include fa-fw to maintain icon width in a column of different icons.
 * @param bool   $hidden If true, aria-hidden=true is included to hide the element from assistive technologies.
 * Only use when the icon is in a focussable parent e.g. an anchor, so the parent takes focus and the icon
 * itself is not declared by screen readers and the like. Note that in these cases, the tooltip text should
 * go on the parent anchor and not on this icon element using $tooltip.
 * @return string
 */
function zen_icon(string $icon, ?string $tooltip = null, string $size = '', bool $fixedWidth = false, bool $hidden = false): string
{
  global $iconMap;
  if (!array_key_exists($icon, $iconMap)) {
    return '';
  }
  $tooltip = empty($tooltip) ? '' : (' data-toggle="tooltip" title="' . str_replace('"', '\"', $tooltip) . '"');
  $fw = empty($fixedWidth) ? '' : ' fa-fw';
  $classes = $iconMap[$icon];
  if (is_array($classes)) {
    return "<div class=\"icon-{$icon} fa-stack\"{$tooltip}{$fw}>" .
      join(
        '',
        array_map(
          function ($cls) {
            return '<i class="fa-solid ' . $cls . '"></i>';
          }, $classes
        )
      ) .
      '</div>';
  }
  // If the classes looked up have an override, use it (add nothing), otherwise default to fa-solid
  $iconSet = str_contains($classes, 'fa-regular') ? '' : 'fa-solid';
  $sizeClass = $size === '2x' ? ' fa-2x' : ($size === 'lg' ? ' fa-lg' : '');
  $ariaHidden = $hidden ? ' aria-hidden="true"' : '';
  return "<i class=\"$iconSet$sizeClass align-middle $classes$fw\"$tooltip$ariaHidden></i>";
}

////
// Draw a 1 pixel black line
  function zen_black_line() {
    return zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '1', 'style="width:100%;"');
  }

////
// Output a separator either through whitespace, or with an image
  function zen_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
	if (!empty($width)) {
		if (substr(rtrim($width), -1) !== '%') {
            $width = $width . 'px';
        }
		$param = 'style="width:' . $width . ';"';
	} else {
		$param = NULL;
	}
    return zen_image(DIR_WS_IMAGES . $image, '', '', $height, $param);
  }
/**
 * @deprecated since v1.5.8. Use <button> markup instead
 */
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
function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false')
{
    $form = '<form name="' . zen_output_string($name) . '" action="';
    if (!empty($parameters)) {
        $form .= zen_href_link($action, $parameters, 'NONSSL');
    } else {
        $form .= zen_href_link($action, '', 'NONSSL');
    }
    $form .= '" method="' . zen_output_string($method) . '"';
    if (!empty($params)) {
        $form .= ' ' . $params;
    }
    $form .= '>';
    if (strtolower($method) === 'post') {
        $form .= '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '">';
    }
    if (strtolower($method) === 'get') {
        $form .= '<input type="hidden" name="cmd" value="' . str_replace('.php', '', $action) . '">';
    }
    return $form;
}

  /**
 *
 * Output a form input field
 * @param string $name field name
 * @param string $value field value
 * @param string $parameters extra parameters like classes or id
 * @param boolean $required field is required
 * @param string $type filed type
 * @param boolean $reinsert_value
 * @return string
 */
function zen_draw_input_field($name, $value = '~*~*#', $parameters = '', $required = false, $type = 'text', $reinsert_value = true)
{
  $type = zen_output_string($type);
  if ($type === 'price') {
    $type = 'number" step="0.01';
  }
  $field = ($required ? '<div class="input-group">' . PHP_EOL : '');
  $field .= '<input type="' . $type . '" name="' . zen_output_string($name) . '"';

  if ($value == '~*~*#' && (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) && ($reinsert_value == true)) {
    $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
  } elseif ($value != '~*~*#' && zen_not_null($value)) {
    $field .= ' value="' . zen_output_string($value) . '"';
  }

  if (!empty($parameters)) {
    $field .= ' ' . $parameters;
  }

  if ($required && strpos($parameters, 'required') === false) {
    $field .= ' required';
  }

  $field .= '>' . PHP_EOL;
  if ($required) {
    $field .= '<span class="input-group-addon alert-danger">' . '*' . '</span>' . PHP_EOL;
    $field .= '</div>' . PHP_EOL;
  }
  return $field;
}

////
// Output a form password field
function zen_draw_password_field(string $name, string $value = '', bool $required = false, string $parameters = '', bool $autocomplete = false)
{
    $parameters .= ' maxlength="40"';

    if ($autocomplete === false && !str_contains($parameters, 'autocomplete=')) {
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

    if (
        ($checked == true)
        || (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ($GLOBALS[$name] == 'on'))
        || (isset($value) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && (stripslashes($GLOBALS[$name]) == $value))
        || (zen_not_null($value) && zen_not_null($compare) && ($value == $compare))
    ) {
      $selection .= ' checked="checked"';
    }

    if (!empty($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

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
  function zen_draw_textarea_field($name, $wrap, $cols, $height, $text = '~*~*#', $parameters = '', $reinsert_value = true) {
    $cols = (int)$cols;
    $wrap = in_array($wrap, ['soft', 'hard', 'off'], true) ? $wrap : 'soft';
    $field = '<textarea name="' . zen_output_string($name) . '" wrap="' . $wrap . '"' . ($cols > 0 ? ' cols="' . $cols . '"' : '') . ' rows="' . zen_output_string($height) . '"';

    if (!empty($parameters)) $field .= ' ' . $parameters;

    if (!str_contains($parameters, 'id="')) {
        $field .= ' id="' . zen_output_string(str_replace(['[', ']'], '-', $name)) . '"';
    }

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

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

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
function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false)
{
  $field = ($required ? '<div class="input-group">' . PHP_EOL : '');
  $field .= '<select name="' . zen_output_string($name) . '"';

  if (!empty($parameters)) {
    $field .= ' ' . $parameters;
  }

  if ($required && strpos($parameters, 'required') === false) {
    $field .= ' required';
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
  if ($required) {
    $field .= '<span class="input-group-addon alert-danger">' . '*' . '</span>' . PHP_EOL;
    $field .= '</div>' . PHP_EOL;
  }

  return $field;
}

////
// Hide form elements
  function zen_hide_session_id() {
    global $session_started;

    if ( ($session_started == true) && defined('SID') && !empty(SID) ) {
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


/**
 * Output a day/month/year dropdown selector
 * @param string $fieldname_prefix
 * @param string $default_date
 * @return string
 */
function zen_draw_date_selector($fieldname_prefix, $default_date='') {
    $month_array = array();
    $month_array[1] =_JANUARY;
    $month_array[2] =_FEBRUARY;
    $month_array[3] =_MARCH;
    $month_array[4] =_APRIL;
    $month_array[5] =_MAY;
    $month_array[6] =_JUNE;
    $month_array[7] =_JULY;
    $month_array[8] =_AUGUST;
    $month_array[9] =_SEPTEMBER;
    $month_array[10] =_OCTOBER;
    $month_array[11] =_NOVEMBER;
    $month_array[12] =_DECEMBER;
    $usedate = getdate($default_date);
    $day = $usedate['mday'];
    $month = $usedate['mon'];
    $year = $usedate['year'];
    $date_selector = '<select name="'. $fieldname_prefix .'_day">';
    for ($i=1;$i<32;$i++){
        $date_selector .= '<option value="' . $i . '"';
        if ($i==$day) $date_selector .= ' selected';
        $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $fieldname_prefix .'_month">';
    for ($i=1;$i<13;$i++){
        $date_selector .= '<option value="' . $i . '"';
        if ($i==$month) $date_selector .= ' selected';
        $date_selector .= '>' . $month_array[$i] . '</option>';
    }
    $date_selector .= '</select>';
    $date_selector .= '<select name="'. $fieldname_prefix .'_year">';
    for ($i = date('Y') - 5, $j = date('Y') + 11; $i < $j; $i++) {
        $date_selector .= '<option value="' . $i . '"';
        if ($i==$year) $date_selector .= ' selected';
        $date_selector .= '>' . $i . '</option>';
    }
    $date_selector .= '</select>';
    return $date_selector;
}

    /**
     * intended to add a universal search for use with any pulldown that extends the
     * abstract pulldown class.  returns a html string.
     * @param string $filename
     * @param string $action
     * @param bool $includeForm
     * @param array $extrafieldsArray
     * @return string
     */

    function addSearchKeywordForm(string $filename, string $action = '', bool $includeForm = true, array $extrafieldsArray = [])
    {
        $keywords_products = (isset($_POST['keywords']) && zen_not_null($_POST['keywords'])) ? zen_db_input(zen_db_prepare_input($_POST['keywords'])) : '';
        $form = '';
        $endForm = '';
        $fullAction = '';
        if (!empty($action)) {
            $fullAction = 'action=' . $action;
        }
        if ($includeForm) {
            $form = zen_draw_form('keywords', $filename, $fullAction, 'post', 'class="form-horizontal"');
            $endForm = '</form>';
        }
        $html = '
        <div class="row">
            <div class="col-sm-offset-2 col-sm-4">' . $form . '
                <div class="form-group">' .
            zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'keywords', 'class="control-label col-sm-3"') . '
                         <div class="col-sm-9">' .
            zen_draw_input_field('keywords', ($_POST['keywords'] ?? ''), 'class="form-control" id="keywords"') . '
                         </div>
                </div>' . zen_hide_session_id();
        if (!empty($keywords_products)) {
            $html .= '<div class="form-group">
                      <div class="col-sm-3">
                          <p class="control-label">' . TEXT_INFO_SEARCH_DETAIL_FILTER . '</p>
                      </div>
                      <div class="col-sm-9 text-right">' .
                zen_output_string_protected($keywords_products) . ' <a href="' . zen_href_link($filename, $fullAction) . '" class="btn btn-default" role="button">' . IMAGE_RESET . '</a>
                      </div>
                  </div>';
        }
        foreach ($extrafieldsArray as $key => $value) {
            $html .= zen_draw_hidden_field($key, $value) . '<br>';
        }
        $html .= '<br>' . $endForm . '
            </div>
        </div>';
        return $html;
    }
