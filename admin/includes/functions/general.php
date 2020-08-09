<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 17 Modified in v1.5.7 $
 */


/**
 * @TODO - deprecate in favor of an HR or bordered/borderless DIV
 *
 * @param $image
 * @param $alt
 * @param string $width
 * @param string $height
 * @param string $params
 * @return false|string
 */
function zen_info_image($image, $alt, $width = '', $height = '', $params = '')
{
    if (zen_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image))) {
        $image = zen_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height, $params);
    } else {
        $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
}


function zen_tax_classes_pull_down($parameters, $selected = '')
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $classes = $db->Execute("SELECT tax_class_id, tax_class_title
                             FROM " . TABLE_TAX_CLASS . "
                             ORDER BY tax_class_title");

    while (!$classes->EOF) {
        $select_string .= '<option value="' . $classes->fields['tax_class_id'] . '"';
        if ($selected == $classes->fields['tax_class_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $classes->fields['tax_class_title'] . '</option>';
        $classes->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
}


function zen_geo_zones_pull_down($parameters, $selected = '')
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute("SELECT geo_zone_id, geo_zone_name
                                 FROM " . TABLE_GEO_ZONES . "
                                 ORDER BY geo_zone_name");

    while (!$zones->EOF) {
        $select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
        if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
        $zones->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
}


function zen_get_geo_zone_name($geo_zone_id)
{
    global $db;
    $zones = $db->Execute("SELECT geo_zone_name
                           FROM " . TABLE_GEO_ZONES . "
                           WHERE geo_zone_id = " . (int)$geo_zone_id);

    if ($zones->RecordCount() < 1) {
        $geo_zone_name = $geo_zone_id;
    } else {
        $geo_zone_name = $zones->fields['geo_zone_name'];
    }

    return $geo_zone_name;
}

// @TODO proxy into language class instead of new query
function zen_get_languages()
{
    global $db;
    $languages = $db->Execute("SELECT languages_id, name, code, image, directory
                               FROM " . TABLE_LANGUAGES . " ORDER BY sort_order");

    while (!$languages->EOF) {
        $languages_array[] = array(
            'id' => $languages->fields['languages_id'],
            'name' => $languages->fields['name'],
            'code' => $languages->fields['code'],
            'image' => $languages->fields['image'],
            'directory' => $languages->fields['directory']
        );
        $languages->MoveNext();
    }

    return $languages_array;
}

  function zen_get_orders_status_name($orders_status_id, $language_id = '') {
    global $db;

    if (!$language_id) $language_id = $_SESSION['languages_id'];
    $orders_status = $db->Execute("SELECT orders_status_name
                                   FROM " . TABLE_ORDERS_STATUS . "
                                   WHERE orders_status_id = " . (int)$orders_status_id . "
                                   AND language_id = " . (int)$language_id);
    if ($orders_status->EOF) return '';
    return $orders_status->fields['orders_status_name'];
  }


  function zen_get_orders_status() {
    global $db;

    $orders_status_array = array();
    $orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                                   FROM " . TABLE_ORDERS_STATUS . "
                                   WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                   ORDER BY orders_status_id");

    while (!$orders_status->EOF) {
      $orders_status_array[] = array('id' => $orders_status->fields['orders_status_id'],
                                     'text' => $orders_status->fields['orders_status_name']);
      $orders_status->MoveNext();
    }

    return $orders_status_array;
  }


  function zen_get_products_name($product_id, $language_id = 0) {
    global $db;

    if ($language_id == 0) $language_id = $_SESSION['languages_id'];
    $product = $db->Execute("SELECT products_name
                             FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                             WHERE products_id = " . (int)$product_id . "
                             AND language_id = " . (int)$language_id);
    if ($product->EOF) return '';
    return $product->fields['products_name'];
  }


  function zen_get_products_description($product_id, $language_id) {
    global $db;
    $product = $db->Execute("SELECT products_description
                             FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                             WHERE products_id = " . (int)$product_id . "
                             AND language_id = " . (int)$language_id);
    if ($product->EOF) return '';
    return $product->fields['products_description'];
  }


  function zen_get_products_url($product_id, $language_id) {
    global $db;
    $product = $db->Execute("SELECT products_url
                             FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                             WHERE products_id = " . (int)$product_id . "
                             AND language_id = " . (int)$language_id);
    if ($product->EOF) return '';
    return $product->fields['products_url'];
  }


////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
  function zen_get_manufacturer_url($manufacturer_id, $language_id) {
    global $db;
    $manufacturer = $db->Execute("SELECT manufacturers_url
                                  FROM " . TABLE_MANUFACTURERS_INFO . "
                                  WHERE manufacturers_id = " . (int)$manufacturer_id . "
                                  AND languages_id = " . (int)$language_id);
    if ($manufacturer->EOF) return '';
    return $manufacturer->fields['manufacturers_url'];
  }


////
// Count how many products exist in a category
// TABLES: products, products_to_categories, categories
  function zen_products_in_category_count($categories_id, $include_deactivated = false, $include_child = true, $limit = false) {
    global $db;
    $products_count = 0;

    if ($limit) {
      $limit_count = ' limit 1';
    } else {
      $limit_count = '';
    }

    if ($include_deactivated) {

      $products = $db->Execute("SELECT COUNT(*) AS total
                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                WHERE p.products_id = p2c.products_id
                                AND p2c.categories_id = " . (int)$categories_id . $limit_count);
    } else {
      $products = $db->Execute("SELECT COUNT(*) AS total
                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                WHERE p.products_id = p2c.products_id
                                AND p.products_status = 1
                                AND p2c.categories_id = " . (int)$categories_id . $limit_count);

    }

    $products_count += $products->fields['total'];

    if ($include_child) {
      $childs = $db->Execute("SELECT categories_id FROM " . TABLE_CATEGORIES . "
                              WHERE parent_id = " . (int)$categories_id);
      if ($childs->RecordCount() > 0 ) {
        while (!$childs->EOF) {
          $products_count += zen_products_in_category_count($childs->fields['categories_id'], $include_deactivated);
          $childs->MoveNext();
        }
      }
    }
    return $products_count;
  }


////
// Count how many subcategories exist in a category
// TABLES: categories
  function zen_childs_in_category_count($categories_id) {
    global $db;
    $categories_count = 0;

    $categories = $db->Execute("SELECT categories_id
                                FROM " . TABLE_CATEGORIES . "
                                WHERE parent_id = " . (int)$categories_id);

    while (!$categories->EOF) {
      $categories_count++;
      $categories_count += zen_childs_in_category_count($categories->fields['categories_id']);
      $categories->MoveNext();
    }

    return $categories_count;
  }



//// @TODO - is there a coupon function already to do this query?
function zen_cfg_select_coupon_id($coupon_id, $key = '')
{
    global $db;
    $coupon_array = array();
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $coupons = $db->execute("SELECT cd.coupon_name, c.coupon_id FROM " . TABLE_COUPONS . " c, " . TABLE_COUPONS_DESCRIPTION . " cd WHERE cd.coupon_id = c.coupon_id AND cd.language_id = " . (int)$_SESSION['languages_id']);
    $coupon_array[] = array(
        'id' => '0',
        'text' => 'None'
    );

    while (!$coupons->EOF) {
        $coupon_array[] = array(
            'id' => $coupons->fields['coupon_id'],
            'text' => $coupons->fields['coupon_name']
        );
        $coupons->MoveNext();
    }
    return zen_draw_pull_down_menu($name, $coupon_array, $coupon_id, 'class="form-control"');
}


////
// Alias function for Store configuration values in the Administration Tool
  function zen_cfg_pull_down_country_list($country_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_countries(), $country_id, 'class="form-control"');
  }


////
  function zen_cfg_pull_down_country_list_none($country_id, $key = '') {
    $country_array = zen_get_countries('None');
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, $country_array, $country_id, 'class="form-control"');
  }


////
function zen_cfg_pull_down_zone_list($zone_id, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_pull_down_menu($name, zen_get_country_zones(STORE_COUNTRY), $zone_id, 'class="form-control"');
}


//// @TODO - is there a tax class query function already?
function zen_cfg_pull_down_tax_classes($tax_class_id, $key = '')
{
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class = $db->Execute("SELECT tax_class_id, tax_class_title
                               FROM " . TABLE_TAX_CLASS . "
                               ORDER BY tax_class_title");

    while (!$tax_class->EOF) {
        $tax_class_array[] = array(
            'id' => $tax_class->fields['tax_class_id'],
            'text' => $tax_class->fields['tax_class_title']
        );
        $tax_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $tax_class_array, $tax_class_id, 'class="form-control"');
}


////
// Function to read in text area in admin
function zen_cfg_textarea($text, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 60, 5, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE), 'class="form-control"');
}


////
// Function to read in text area in admin
function zen_cfg_textarea_small($text, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    return zen_draw_textarea_field($name, false, 35, 1, htmlspecialchars($text, ENT_COMPAT, CHARSET, FALSE), 'class="noEditor form-control" autofocus');
}

// @TODO - is there a zone lookup query already?
function zen_cfg_get_zone_name($zone_id)
{
    global $db;
    $zone = $db->Execute("SELECT zone_name
                          FROM " . TABLE_ZONES . "
                          WHERE zone_id = " . (int)$zone_id);

    if ($zone->RecordCount() < 1) {
        return $zone_id;
    } else {
        return $zone->fields['zone_name'];
    }
}

function zen_cfg_pull_down_htmleditors($html_editor, $index = null)
{
    global $editors_list;
    $name = $index ? 'configuration[' . $index . ']' : 'configuration_value';

    $editors_pulldown = array();
    foreach ($editors_list as $key => $value) {
        $editors_pulldown[] = array('id' => $key, 'text' => $value['desc']);
    }
    return zen_draw_pull_down_menu($name, $editors_pulldown, $html_editor, 'class="form-control"');
}

function zen_cfg_pull_down_exchange_rate_sources($source, $key = '')
{
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $pulldown = array();
    $pulldown[] = array('id' => TEXT_NONE, 'text' => TEXT_NONE);
    $funcs = get_defined_functions();
    $funcs = $funcs['user'];
    sort($funcs);
    foreach ($funcs as $func) {
        if (preg_match('/quote_(.*)_currency/', $func, $regs)) {
            $pulldown[] = array('id' => $regs[1], 'text' => $regs[1]);
        }
    }
    return zen_draw_pull_down_menu($name, $pulldown, $source);
}

function zen_cfg_password_input($value, $key = '')
{
    if (function_exists('dbenc_is_encrypted_value_key') && dbenc_is_encrypted_value_key($key)) {
        $value = dbenc_decrypt($value);
    }
    return zen_draw_password_field('configuration[' . $key . ']', $value, 'class="form-control"');
}

function zen_cfg_password_display($value)
{
    $length = strlen($value);
    return str_repeat('*', ($length > 16 ? 16 : $length));
}
/**
 * Sets the status of a product
 * @global object $db
 * @param int $products_id
 * @param int $status
 */
function zen_set_product_status($products_id, $status)
{
  global $db;
  $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                SET products_status = " . (int)$status . ",
                    products_last_modified = now()
                WHERE products_id = " . (int)$products_id);
  return;
}


////
// Alias function for Store configuration values in the Administration Tool
function zen_cfg_select_option($select_array, $key_value, $key = '')
{
    $string = '';

    for ($i = 0, $n = count($select_array); $i < $n; $i++) {
        $name = (zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value';

        $string .= '<div class="radio"><label>' . zen_draw_radio_field($name, $select_array[$i], ($key_value == $select_array[$i] ? true : false), '', 'id="' . strtolower($select_array[$i] . '-' . $name) . '" class="inputSelect"') . $select_array[$i] . '</label></div>';
    }

    return $string;
}


function zen_cfg_select_drop_down($select_array, $key_value, $key = '')
{
    $string = '';

    $name = (zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_pull_down_menu($name, $select_array, (int)$key_value, 'class="form-control"');
}

////
// Alias function for module configuration keys
function zen_mod_select_option($select_array, $key_name, $key_value)
{
    $string = '';
    foreach ($select_array as $key => $value) {
        if (is_int($key)) $key = $value;
        $string .= '<div class="radio"><label>' . zen_draw_radio_field('configuration[' . $key_name . ']', $key, ($key_value == $key ? true : false)) . $value . '</label></div>';
    }

    return $string;
}

////
// Collect server information
function zen_get_system_information($privacy = false)
{
    global $db;

    // determine database size stats
    $indsize = 0;
    $datsize = 0;
    $result = $db->Execute("SHOW TABLE STATUS" . (DB_PREFIX == '' ? '' : " LIKE '" . str_replace('_', '\_', DB_PREFIX) . "%'"));
    while (!$result->EOF) {
        $datsize += $result->fields['Data_length'];
        $indsize += $result->fields['Index_length'];
        $result->MoveNext();
    }

    $strictmysql = false;
    $mysql_mode = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'sql\_mode'");
    if (!$result->EOF) {
        $mysql_mode = $result->fields['Value'];
        if (strstr($result->fields['Value'], 'strict_')) $strictmysql = true;
    }
    $mysql_slow_query_log_status = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log'");
    if (!$result->EOF) {
        $mysql_slow_query_log_status = $result->fields['Value'];
    }
    $mysql_slow_query_log_file = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log\_file'");
    if (!$result->EOF) {
        $mysql_slow_query_log_file = $result->fields['Value'];
    }
    $result = $db->Execute("select now() as datetime");
    $mysql_date = $result->fields['datetime'];

    $errnum = 0;
    $system = $host = $kernel = $output = '';
    list($system, $host, $kernel) = array('', $_SERVER['SERVER_NAME'], php_uname());
    $uptime = (DISPLAY_SERVER_UPTIME == 'true') ? 'Unsupported' : 'Disabled/Unavailable';

    // check to see if "exec()" is disabled in PHP -- if not, get additional info via command line
    $exec_disabled = false;
    $php_disabled_functions = @ini_get("disable_functions");
    if ($php_disabled_functions != '') {
        if (in_array('exec', preg_split('/,/', str_replace(' ', '', $php_disabled_functions)))) {
            $exec_disabled = true;
        }
    }
    if (!$exec_disabled) {
        @exec('uname -a 2>&1', $output, $errnum);
        if ($errnum == 0 && count($output)) list($system, $host, $kernel) = preg_split('/[\s,]+/', $output[0], 5);
        $output = '';
        if (DISPLAY_SERVER_UPTIME == 'true') {
            @exec('uptime 2>&1', $output, $errnum);
            if ($errnum == 0) {
                $uptime = $output[0];
            }
        }
    }

    $timezone = date_default_timezone_get();

    $systemInfo = [
        'date' => zen_datetime_short(date('Y-m-d H:i:s')),
        'timezone' => $timezone,
        'system' => $system,
        'kernel' => $kernel,
        'host' => $host,
        'ip' => gethostbyname($host),
        'uptime' => $uptime,
        'http_server' => $_SERVER['SERVER_SOFTWARE'],
        'php' => PHP_VERSION,
        'zend' => (function_exists('zend_version') ? zend_version() : ''),
        'db_server' => DB_SERVER,
        'db_ip' => gethostbyname(DB_SERVER),
        'db_version' => 'MySQL ' . $db->get_server_info(),
        'db_date' => zen_datetime_short($mysql_date),
        'php_memlimit' => @ini_get('memory_limit'),
        'php_file_uploads' => strtolower(@ini_get('file_uploads')),
        'php_uploadmaxsize' => @ini_get('upload_max_filesize'),
        'php_postmaxsize' => @ini_get('post_max_size'),
        'database_size' => $datsize,
        'index_size' => $indsize,
        'mysql_strict_mode' => $strictmysql,
        'mysql_mode' => $mysql_mode,
        'mysql_slow_query_log_status' => $mysql_slow_query_log_status,
        'mysql_slow_query_log_file' => $mysql_slow_query_log_file,
    ];

    if ($privacy) {
        unset ($systemInfo['mysql_slow_query_log_file']);
    }

    return $systemInfo;
}


  function zen_remove_category($category_id) {
    if ((int)$category_id == TOPMOST_CATEGORY_PARENT_ID) return;
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);

    // delete from salemaker - sale_categories_selected
    $chk_sale_categories_selected = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_selected = " . (int)$category_id . "
    OR sale_categories_selected LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_selected LIKE '%," . (int)$category_id . "'
    OR sale_categories_selected LIKE '" . (int)$category_id . ",%'");

    // delete from salemaker - sale_categories_all
    $chk_sale_categories_all = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_all = " . (int)$category_id . "
    OR sale_categories_all LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_all LIKE '%," . (int)$category_id . "'
    OR sale_categories_all LIKE '" . (int)$category_id . ",%'");

//echo 'WORKING ON: ' . (int)$category_id . ' chk_sale_categories_selected: ' . $chk_sale_categories_selected->RecordCount() . ' chk_sale_categories_all: ' . $chk_sale_categories_all->RecordCount() . '<br>';
while (!$chk_sale_categories_selected->EOF) {
  $skip_cats = false; // used when deleting
  $skip_sale_id = 0;
//echo '<br>FIRST LOOP: sale_id ' . $chk_sale_categories_selected->fields['sale_id'] . ' sale_categories_selected: ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
  // 9 or ,9 or 9,
  // delete record if sale_categories_selected = 9 and  sale_categories_all = ,9,
  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id and $chk_sale_categories_selected->fields['sale_categories_all'] == ',' . (int)$category_id . ',') { // delete record
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_selected->fields['sale_id'] . '<br><br>';
    $skip_cats = true;
    $skip_sale_id = $chk_sale_categories_selected->fields['sale_id'];
    $salemakerdelete = "DELETE from " . TABLE_SALEMAKER_SALES . " WHERE sale_id="  . (int)$skip_sale_id;
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . (int)$category_id . ',/', $chk_sale_categories_selected->fields['sale_categories_selected'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], strlen((int)$category_id . ','));
//echo 'B: new_sale_categories_selected: ' . $new_sale_categories_selected . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',')) ) { // middle or end
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_selected->fields['sale_categories_selected'], $start_cat);
//echo 'C: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
    $skip_cat_last = true;
  }


// not needed in loop 1 if middle does end
  // if on the end - remove ,9 skip if middle cleaned it
  if (!$skip_cats && !$skip_cat_last && (strripos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id)) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id) + strlen(',' . (int)$category_id);
//echo 'D: I need to remove - ,' . (int)$category_id . ' - from the end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
//echo 'D: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
  }

  if (!$skip_cats) {
    $salemakerupdate =
    "UPDATE " . TABLE_SALEMAKER_SALES . "
    SET sale_categories_selected='" . $new_sale_categories_selected . "'
    WHERE sale_id = " . (int)$chk_sale_categories_selected->fields['sale_id'];
//echo 'Update new_sale_categories_selected: ' . $salemakerupdate . '<br>';
    $db->Execute($salemakerupdate);
  } else {
//echo 'Record was deleted sale_id ' . $skip_sale_id . '<br>' . $salemakerdelete;
    $db->Execute($salemakerdelete);
  }

  $chk_sale_categories_selected->MoveNext();
}

while (!$chk_sale_categories_all->EOF) {
//echo '<br><br>SECOND LOOP: sale_id ' . $chk_sale_categories_all->fields['sale_id'] . ' sale_categories_all: ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br><br>';
  // remove ,9 if on front as ,9, - remove ,9 if in the middle as ,9, - remove ,9 if on the end as ,9,
  // beware of ,79, or ,98, or ,99, when cleaning 9
  // if ($chk_sale_categories_all->fields['sale_categories_all'] == ',9') { // front
  // if (something for the middle) { // middle
  // if (right($chk_sale_categories_all->fields['sale_categories_all']) == ',9,') { // end

  $skip_cats = false;
  if ($skip_sale_id == $chk_sale_categories_all->fields['sale_id']) { // was deleted
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_all->fields['sale_id'] . ' but already done' . '<br><br>';
    $skip_cats = true;
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_all->fields['sale_categories_all'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . ',' . (int)$category_id . ',/', $chk_sale_categories_all->fields['sale_categories_all'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], strlen(',' . (int)$category_id));
//echo 'B: new_sale_categories_all: ' . $new_sale_categories_all . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // middle
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_all->fields['sale_categories_all'], $start_cat);
//echo 'C: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }

/*
// not needed in loop 2
  // if on the end - remove ,9,
  if (!$skip_cats && (strripos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id) + strlen(',' . (int)$category_id . ',');
    echo 'D: I need to remove from the end - ,' . (int)$category_id . ', - from the end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
    echo 'D: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }
*/
      $salemakerupdate = "UPDATE " . TABLE_SALEMAKER_SALES . " SET sale_categories_all='" . $new_sale_categories_all . "' WHERE sale_id = " . (int)$chk_sale_categories_all->fields['sale_id'];

//echo 'Update sale_categories_all: ' . $salemakerupdate . '<br>';

      $db->Execute($salemakerupdate);

      $chk_sale_categories_all->MoveNext();
}

//die('DONE TESTING');

    $category_image = $db->Execute("select categories_image
                                    from " . TABLE_CATEGORIES . "
                                    where categories_id = " . (int)$category_id);

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_CATEGORIES . "
                                     where categories_image = '" .
                                           zen_db_input($category_image->fields['categories_image']) . "'");
    if ($duplicate_image->fields['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image']);
      }
    }

    $db->Execute("delete from " . TABLE_CATEGORIES . "
                  where categories_id = " . (int)$category_id);

    $db->Execute("delete from " . TABLE_CATEGORIES_DESCRIPTION . "
                  where categories_id = " . (int)$category_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  where categories_id = " . (int)$category_id);

    $db->Execute("delete from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                  where categories_id = " . (int)$category_id);

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where category_id = " . (int)$category_id);

    zen_record_admin_activity('Deleted category ' . (int)$category_id . ' from database via admin console.', 'warning');
  }

  function zen_remove_product($product_id, $ptc = 'true') {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);

    $product_image = $db->Execute("select products_image
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = " . (int)$product_id);

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_PRODUCTS . "
                                     where products_image = '" . zen_db_input($product_image->fields['products_image']) . "'");

    if ($duplicate_image->fields['total'] < 2 and $product_image->fields['products_image'] != '' && PRODUCTS_IMAGE_NO_IMAGE != substr($product_image->fields['products_image'], strrpos($product_image->fields['products_image'], '/')+1)) {
      $products_image = $product_image->fields['products_image'];
      $products_image_extension = substr($products_image, strrpos($products_image, '.'));
      $products_image_base = preg_replace('/' . $products_image_extension . '/', '', $products_image);

      $filename_medium = 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
      $filename_large = 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

      if (file_exists(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image']);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
      }
    }

    $db->Execute("delete from " . TABLE_SPECIALS . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS . "
                  where products_id = " . (int)$product_id);

//    if ($ptc == 'true') {
      $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                    where products_id = " . (int)$product_id);
//    }

    $db->Execute("delete from " . TABLE_PRODUCTS_DESCRIPTION . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                  where products_id = " . (int)$product_id);

    zen_products_attributes_download_delete($product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                  where products_id = " . (int)$product_id);


    $product_reviews = $db->Execute("select reviews_id
                                     from " . TABLE_REVIEWS . "
                                     where products_id = " . (int)$product_id);

    while (!$product_reviews->EOF) {
      $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . "
                    where reviews_id = " . (int)$product_reviews->fields['reviews_id']);
      $product_reviews->MoveNext();
    }
    $db->Execute("delete from " . TABLE_REVIEWS . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_FEATURED . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where product_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                  where products_id = " . (int)$product_id);

    $db->Execute("delete from " . TABLE_COUNT_PRODUCT_VIEWS . "
                  where product_id = " . (int)$product_id);

    zen_record_admin_activity('Deleted product ' . (int)$product_id . ' from database via admin console.', 'warning');
  }

  function zen_products_attributes_download_delete($product_id) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_PRODUCTS_ATTRIBUTES_DOWNLOAD_DELETE', array(), $product_id);

  // remove downloads if they exist
    $remove_downloads= $db->Execute("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id= " . (int)$product_id);
    while (!$remove_downloads->EOF) {
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id= " . (int)$remove_downloads->fields['products_attributes_id']);
      $remove_downloads->MoveNext();
    }
  }

  function zen_remove_order($order_id, $restock = false) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_ORDER', array(), $order_id, $restock);
    if ($restock == 'on') {
      $order = $db->Execute("select products_id, products_quantity
                             from " . TABLE_ORDERS_PRODUCTS . "
                             where orders_id = " . (int)$order_id);

      while (!$order->EOF) {
        $db->Execute("update " . TABLE_PRODUCTS . "
                      set products_quantity = products_quantity + " . $order->fields['products_quantity'] . ", products_ordered = products_ordered - " . $order->fields['products_quantity'] . " where products_id = " . (int)$order->fields['products_id']);
        $order->MoveNext();
      }
    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = " . (int)$order_id);
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . "
                  where orders_id = " . (int)$order_id);

    $db->Execute("delete from " . TABLE_COUPON_GV_QUEUE . "
                  where order_id = " . (int)$order_id . " and release_flag = 'N'");

    zen_record_admin_activity('Deleted order ' . (int)$order_id . ' from database via admin console.', 'warning');
  }


function zen_call_function($function, $parameter, $object = '')
{
    if ($object == '') {
        return call_user_func($function, $parameter);
    }

    return call_user_func(array($object, $function), $parameter);
}

//@todo - is there a function already for this query?
function zen_get_zone_class_title($zone_class_id)
{
    global $db;
    if ($zone_class_id == '0') {
        return TEXT_NONE;
    }

    $classes = $db->Execute("select geo_zone_name
                               from " . TABLE_GEO_ZONES . "
                               where geo_zone_id = " . (int)$zone_class_id);
    if ($classes->EOF) return '';
    return $classes->fields['geo_zone_name'];
}

//// @todo - is there a function already for this query? See the one above
function zen_cfg_pull_down_zone_classes($zone_class_id, $key = '')
{
    global $db;
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = [['id' => '0', 'text' => TEXT_NONE]];
    $zone_class = $db->Execute("select geo_zone_id, geo_zone_name
                                from " . TABLE_GEO_ZONES . "
                                order by geo_zone_name");

    while (!$zone_class->EOF) {
        $zone_class_array[] = [
            'id' => $zone_class->fields['geo_zone_id'],
            'text' => $zone_class->fields['geo_zone_name']
        ];
        $zone_class->MoveNext();
    }

    return zen_draw_pull_down_menu($name, $zone_class_array, $zone_class_id, 'class="form-control"');
}


////
function zen_cfg_pull_down_order_statuses($order_status_id, $key = '')
{
    $name = ($key) ? 'configuration[' . $key . ']' : 'configuration_value';
    return zen_draw_order_status_dropdown($name, $order_status_id, array('id' => 0, 'text' => TEXT_DEFAULT), 'class="form-control"');
}

/**
 * Return a pull-down menu of the available order-status values,
 * optionally prefixed by a "please choose" selection.
 */
function zen_draw_order_status_dropdown($field_name, $default_value, $first_selection = '', $parms = '')
{
    global $db;
    $statuses = $db->Execute(
        "SELECT orders_status_id AS `id`, orders_status_name AS `text`
            FROM " . TABLE_ORDERS_STATUS . "
            WHERE language_id = " . (int)$_SESSION['languages_id'] . "
            ORDER BY sort_order ASC, orders_status_id ASC"
    );
    $statuses_array = [];
    if (is_array($first_selection)) {
        $statuses_array[] = $first_selection;
    }
    foreach ($statuses as $status) {
        $statuses_array[] = [
            'id' => $status['id'],
            'text' => "{$status['text']} [{$status['id']}]"
        ];
    }
    return zen_draw_pull_down_menu($field_name, $statuses_array, $default_value, $parms);
}

  function zen_get_order_status_name($order_status_id, $language_id = '') {
    global $db;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $_SESSION['languages_id'];

    $status = $db->Execute("select orders_status_name
                            from " . TABLE_ORDERS_STATUS . "
                            where orders_status_id = " . (int)$order_status_id . "
                            and language_id = " . (int)$language_id);
    if ($status->EOF) return 'ERROR: INVALID STATUS ID: ' . (int)$order_status_id;
    return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
  }

/**
 * Return a random value
 */
  function zen_rand($min = null, $max = null) {
    static $seeded;

    if (!$seeded) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

/**
 * lookup attributes model
 */
  function zen_get_products_model($products_id) {
    global $db;
    $check = $db->Execute("select products_model
                    from " . TABLE_PRODUCTS . "
                    where products_id=" . (int)$products_id);
    if ($check->EOF) return '';
    return $check->fields['products_model'];
  }

/**
 * Check if product_id is valid
 */
  function zen_products_id_valid($products_id) {
    global $db;
    $products_valid_query = "select count(*) as count
                         from " . TABLE_PRODUCTS . "
                         where products_id = " . (int)$products_id;

    $products_valid = $db->Execute($products_valid_query);

    if ($products_valid->fields['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }


/**
 * return the size and maxlength settings in the form size="blah" maxlength="blah" based on maximum size being 50
 * uses $tbl = table name, $fld = field name
 * example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
 * @param string $tbl
 * @param string $fld
 * @param int $max
 * @param bool $override
 * @return string
 */
function zen_set_field_length($tbl, $fld, $max = 50, $override = false)
{
    $field_length = zen_field_length($tbl, $fld);
    switch (true) {
        case (($override == false and $field_length > $max)):
            $length = 'size="' . ($max + 1) . '" maxlength="' . $field_length . '"';
            break;
        default:
            $length = 'size="' . ($field_length + 1) . '" maxlength="' . $field_length . '"';
            break;
    }
    return $length;
}

/**
 * @TODO - move to language class
 * Lookup Languages Icon by id or code
 * @param $lookup
 * @return bool|string
 */
function zen_get_language_icon($lookup)
{
    global $db;
    $languages_icon = $db->Execute("SELECT directory, image FROM " . TABLE_LANGUAGES . "
        WHERE
        languages_id = " . (int)$lookup . "
        OR
        code = '" . zen_db_input($lookup) . "'
        LIMIT 1");
    if ($languages_icon->EOF) {
        return '';
    }
    return zen_image(DIR_WS_CATALOG_LANGUAGES . $languages_icon->fields['directory'] . '/images/' . $languages_icon->fields['image'], $languages_icon->fields['directory']);
}


/**
 * @param $lookup
 * @return mixed|string
 * @todo move to lang class
 * lookup language directory name by id or code
 */
function zen_get_language_name($lookup)
{
    global $db;
    $check_language = $db->Execute("SELECT directory FROM " . TABLE_LANGUAGES . "
        WHERE
        languages_id = " . (int)$lookup . "
        OR
        code = '" . zen_db_input($lookup) . "'
        LIMIT 1");

    if ($check_language->EOF) {
        return '';
    }
    return $check_language->fields['directory'];
}

/**
 * configuration key value lookup
 */
  function zen_get_configuration_key_value($lookup) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . zen_db_input($lookup) . "'");
    $lookup_value = '<span class="lookupAttention">' . $lookup . '</span>';
    if ( $configuration_query->RecordCount() > 0 ) {
      $lookup_value = $configuration_query->fields['configuration_value'];
    }
    return $lookup_value;
  }

function zen_get_configuration_group_value($lookup)
{
    global $db;
    $configuration_query = $db->Execute("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id =" . (int)$lookup);
    if ($configuration_query->RecordCount() == 0) {
        return (int)$lookup;
    }
    return $configuration_query->fields['configuration_group_title'];
}


/**
 * check to see if free shipping rules allow the specified shipping module to be enabled or to disable it in lieu of being free
 */
  function zen_get_shipping_enabled($shipping_module) {
    global $PHP_SELF, $cart, $order;

    // for admin always true if installed
    if (strstr($PHP_SELF, FILENAME_MODULES)) {
      return true;
    }

    $check_cart_free = $_SESSION['cart']->in_cart_check('product_is_always_free_shipping','1');
    $check_cart_cnt = $_SESSION['cart']->count_contents();
    $check_cart_weight = $_SESSION['cart']->show_weight();

    switch(true) {
      // for admin always true if installed
      case (strstr($PHP_SELF, FILENAME_MODULES)):
        return true;
        break;
      // Free Shipping when 0 weight - enable freeshipper - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module == 'freeshipper')):
        return true;
        break;
      // Free Shipping when 0 weight - disable everyone - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module != 'freeshipper')):
        return false;
        break;
      // Always free shipping only true - enable freeshipper
      case (($check_cart_free == $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return true;
        break;
      // Always free shipping only true - disable everyone
      case (($check_cart_free == $check_cart_cnt) and $shipping_module != 'freeshipper'):
        return false;
        break;
      // Always free shipping only is false - disable freeshipper
      case (($check_cart_free != $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return false;
        break;
      default:
        return true;
        break;
    }
  }

/**
 * get the type_handler value for the specified product_type
 * @param int $product_type
 */
  function zen_get_handler_from_type($product_type) {
    global $db;

    $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$product_type;
    $handler = $db->Execute($sql);
    if ($handler->EOF) return 'ERROR: Invalid type_handler. Your product_type settings are wrong, incomplete, or damaged.';
    return $handler->fields['type_handler'];
  }

/*
////
// Sets the status of a featured product
  function zen_set_featured_status($featured_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_FEATURED . "
                           set status = '1', expires_date = NULL, date_status_change = NULL
                           where featured_id = " . (int)$featured_id);

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_FEATURED . "
                           set status = '0', date_status_change = now()
                           where featured_id = " . (int)$featured_id);

    } else {
      return -1;
    }
  }
*/


/**
 * @TODO move to a class
 * @todo DRY
 * Sets the status of a product review
 */
function zen_set_reviews_status($review_id, $status)
{
    global $db;
    if ($status == '1') {
        return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 1
                           where reviews_id = " . (int)$review_id);

    } elseif ($status == '0') {
        return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 0
                           where reviews_id = " . (int)$review_id);

    } else {
        return -1;
    }
}

/**
 * Product Types -- configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
 */
  function zen_get_configuration_key_value_layout($lookup, $type=1) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($lookup) . "' and product_type_id=". (int)$type);
    if ($configuration_query->EOF) return '';
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<span class="lookupAttention">' . $lookup . '</span>';
    }
    return $lookup_value;
  }


/**
 * Get the status of a product
 */
  function zen_get_products_status($product_id) {
    global $db;
    $sql = "select products_status from " . TABLE_PRODUCTS . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_status = $db->Execute($sql);
    if ($check_status->EOF) return '';
    return $check_status->fields['products_status'];
  }

/**
 * check if linked
 */
  function zen_get_product_is_linked($product_id, $show_count = 'false') {
    global $db;

    $sql = "select * from " . TABLE_PRODUCTS_TO_CATEGORIES . (zen_not_null($product_id) ? " where products_id=" . (int)$product_id : "");
    $check_linked = $db->Execute($sql);
    if ($check_linked->RecordCount() > 1) {
      if ($show_count == 'true') {
        return $check_linked->RecordCount();
      } else {
        return 'true';
      }
    } else {
      return 'false';
    }
  }


/**
 * TABLES: categories_name from products_id
 */
  function zen_get_categories_name_from_product($product_id) {
    global $db;

//    $check_products_category= $db->Execute("SELECT products_id, categories_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = " . (int)$product_id . " LIMIT 1");
    $check_products_category = $db->Execute("SELECT products_id, master_categories_id
                                             FROM " . TABLE_PRODUCTS . "
                                             WHERE products_id = " . (int)$product_id
                                           );
    if ($check_products_category->EOF) return '';
    $the_categories_name= $db->Execute("SELECT categories_name
                                        FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                        WHERE categories_id= " . (int)$check_products_category->fields['master_categories_id'] . "
                                        AND language_id= " . (int)$_SESSION['languages_id']
                                      );
    if ($the_categories_name->EOF) return '';
    return $the_categories_name->fields['categories_name'];
  }

  function zen_count_products_in_cats($category_id) {
    global $db;
    $cat_products_query = "select count(if (p.products_status=1,1,NULL)) as pr_on, count(*) as total
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = " . (int)$category_id;

    $pr_count = $db->Execute($cat_products_query);
//    echo $pr_count->RecordCount();
    $c_array['this_count'] += $pr_count->fields['total'];
    $c_array['this_count_on'] += $pr_count->fields['pr_on'];

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = " . (int)$category_id;

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
          $m_array = zen_count_products_in_cats($cat_child_categories->fields['categories_id']);
          $c_array['this_count'] += $m_array['this_count'];
          $c_array['this_count_on'] += $m_array['this_count_on'];

//          $this_count_on += $pr_count->fields['pr_on'];
        $cat_child_categories->MoveNext();
      }
    }
    return $c_array;
 }

/**
 * Return the number of products in a category
 * TABLES: products, products_to_categories, categories
 * syntax for count: zen_get_products_to_categories($categories->fields['categories_id'], true)
 * syntax for linked products: zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active')
 */
  function zen_get_products_to_categories($category_id, $include_inactive = false, $counts_what = 'products') {
    global $db;

    $products_count = $cat_products_count = 0;
    $products_linked = '';
    if ($include_inactive == true) {
      switch ($counts_what) {
        case ('products'):
        $cat_products_query = "select count(*) as total
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = " . (int)$category_id;
        break;
        case ('products_active'):
        $cat_products_query = "select p.products_id
                           from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = " . (int)$category_id;
        break;
      }

    } else {
      switch ($counts_what) {
        case ('products'):
          $cat_products_query = "select count(*) as total
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             where p.products_id = p2c.products_id
                             and p.products_status = 1
                             and p2c.categories_id = " . (int)$category_id;
        break;
        case ('products_active'):
          $cat_products_query = "select p.products_id
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                             where p.products_id = p2c.products_id
                             and p.products_status = 1
                             and p2c.categories_id = " . (int)$category_id;
        break;
      }
    }
    $cat_products = $db->Execute($cat_products_query);
    switch ($counts_what) {
      case ('products'):
        if (!$cat_products->EOF) $cat_products_count += $cat_products->fields['total'];
        break;
      case ('products_active'):
        while (!$cat_products->EOF) {
          if (zen_get_product_is_linked($cat_products->fields['products_id']) == 'true') {
            return $products_linked = 'true';
          }
          $cat_products->MoveNext();
        }
        break;
    }

    $cat_child_categories_query = "select categories_id
                               from " . TABLE_CATEGORIES . "
                               where parent_id = " . (int)$category_id;

    $cat_child_categories = $db->Execute($cat_child_categories_query);

    if ($cat_child_categories->RecordCount() > 0) {
      while (!$cat_child_categories->EOF) {
      switch ($counts_what) {
        case ('products'):
          $cat_products_count += zen_get_products_to_categories($cat_child_categories->fields['categories_id'], $include_inactive);
          break;
        case ('products_active'):
          if (zen_get_products_to_categories($cat_child_categories->fields['categories_id'], true, 'products_active') == 'true') {
            return $products_linked = 'true';
          }
          break;
        }
        $cat_child_categories->MoveNext();
      }
    }

    switch ($counts_what) {
      case ('products'):
        return $cat_products_count;
        break;
      case ('products_active'):
        return $products_linked;
        break;
    }
  }

/**
 * master category selection
 * @param int $product_id
 * @param bool $fullpath
 * @return array
 */
function zen_get_master_categories_pulldown($product_id, $fullpath = false)
{
    global $db;
    $master_category_array = [];
    $master_categories_query = $db->Execute("SELECT ptc.products_id, cd.categories_name, cd.categories_id
                                             FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                             LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = ptc.categories_id
                                             WHERE ptc.products_id = " . (int)$product_id . "
                                             AND cd.language_id = " . (int)$_SESSION['languages_id']);
    $master_category_array[] = [
        'id' => '0',
        'text' => TEXT_INFO_SET_MASTER_CATEGORIES_ID,
    ];
    foreach ($master_categories_query as $item) {
        $master_category_array[] = [
            'id' => $item['categories_id'],
            'text' => ($fullpath ? zen_output_generated_category_path($item['categories_id']) : $item['categories_name']) . ' (' . TEXT_INFO_ID . $item['categories_id'] . ')',
        ];
    }
    return $master_category_array;
}

/**
 * get products_type for specified $product_id
 */
  function zen_get_products_type($product_id) {
    global $db;

    $check_products_type = $db->Execute("select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$product_id);
    if ($check_products_type->EOF) return '';
    return $check_products_type->fields['products_type'];
  }


/**
 * Alias function for Store configuration values in the Administration Tool
 * adapted from USPS-related contributions by Brad Waite and Fritz Clapp
 */
function zen_cfg_select_multioption($select_array, $key_value, $key = '')
{
    $string = '';
    for ($i = 0, $n = count($select_array); $i < $n; $i++) {
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
        $key_values = explode(", ", $key_value);
        $string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field($name, $select_array[$i], (in_array($select_array[$i], $key_values) ? true : false), 'id="' . strtolower($select_array[$i] . '-' . $name) . '"') . $select_array[$i] . '</label></div>' . "\n";
    }
    $string .= zen_draw_hidden_field($name, '--none--');
    return $string;
}

/**
 * Function for configuration values that are read-only, e.g. a plugin's version number
 */
function zen_cfg_read_only($text, $key = '')
{
    $name = (!empty($key)) ? 'configuration[' . $key . ']' : 'configuration_value';
    $text = htmlspecialchars_decode($text);

    return $text . zen_draw_hidden_field($name, $text);
}

/**
 * get products image
 */
  function zen_get_products_image($product_id) {
    global $db;
    $product_image = $db->Execute("select products_image
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = " . (int)$product_id);
    if ($product_image->EOF) return '';
    return $product_image->fields['products_image'];
  }



/**
 * build configuration_key based on product type and return its value
 * example: To get the settings for metatags_products_name_status for a product use:
 * zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status')
 * the product is looked up for the products_type which then builds the configuration_key example:
 * SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS
 * the value of the configuration_key is then returned
 * NOTE: keys are looked up first in the product_type_layout table and if not found looked up in the configuration table.
 */
    function zen_get_show_product_switch($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $zv_key = zen_get_show_product_switch_name($lookup, $field, $prefix, $suffix, $field_prefix, $field_suffix);
      $sql = "select configuration_key, configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . zen_db_input($zv_key) . "'";
      $zv_key_value = $db->Execute($sql);
//echo 'I CAN SEE - look ' . $lookup . ' - field ' . $field . ' - key ' . $zv_key . ' value ' . $zv_key_value->fields['configuration_value'] .'<br>';

      if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
      }
      $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . zen_db_input($zv_key) . "'";
      $zv_key_value = $db->Execute($sql);
      if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
      }
      return '';
    }

/**
 * return switch name
 */
    function zen_get_show_product_switch_name($lookup, $field, $prefix= 'SHOW_', $suffix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;
      $type_lookup = 0;
      $type_handler = '';
      $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id=" . (int)$lookup;
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_lookup = $result->fields['products_type'];

      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = " . (int)$type_lookup;
      $result = $db->Execute($sql);
      if (!$result->EOF) $type_handler = $result->fields['type_handler'];
      $zv_key = strtoupper($prefix . $type_handler . $suffix . $field_prefix . $field . $field_suffix);

      return $zv_key;
    }


/**
 * check if products has quantity-discounts defined
 */
  function zen_has_product_discounts($look_up) {
    global $db;

    $check_discount_query = "select products_id from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id=" . (int)$look_up;
    $check_discount = $db->Execute($check_discount_query);

    if ($check_discount->RecordCount() > 0) {
      return 'true';
    } else {
      return 'false';
    }
  }

/**
 * copy quantity-discounts from one product to another
 */
  function zen_copy_discounts_to_product($copy_from, $copy_to) {
    global $db;

    $check_discount_type_query = "select products_discount_type, products_discount_type_from, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id=" . (int)$copy_from;
    $check_discount_type = $db->Execute($check_discount_type_query);
    if ($check_discount_type->EOF) return FALSE;

    $db->Execute("update " . TABLE_PRODUCTS . " set products_discount_type='" . $check_discount_type->fields['products_discount_type'] . "', products_discount_type_from='" . $check_discount_type->fields['products_discount_type_from'] . "', products_mixed_discount_quantity='" . $check_discount_type->fields['products_mixed_discount_quantity'] . "' where products_id=" . (int)$copy_to);

    $check_discount_query = "select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id=" . (int)$copy_from . " order by discount_id";
    $check_discount = $db->Execute($check_discount_query);
    $cnt_discount=1;
    while (!$check_discount->EOF) {
      $db->Execute("insert into " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  (discount_id, products_id, discount_qty, discount_price )
                  values (" . (int)$cnt_discount . ", " . (int)$copy_to . ", '" . $check_discount->fields['discount_qty'] . "', '" . $check_discount->fields['discount_price'] . "')");
      $cnt_discount++;
      $check_discount->MoveNext();
    }
  }


/**
 * Lookup and return product's master_categories_id
 * TABLES: categories
 */
  function zen_get_parent_category_id($product_id) {
    global $db;

    $categories_lookup = $db->Execute("select master_categories_id
                                from " . TABLE_PRODUCTS . "
                                where products_id = " . (int)$product_id);
    if ($categories_lookup->EOF) return '';
    return $categories_lookup->fields['master_categories_id'];
  }

/**
 * replacement for fmod to manage values < 1
 */
  function fmod_round($x, $y) {
    if ($y == 0) {
      return 0;
    }
    $x = strval($x);
    $y = strval($y);
    $zc_round = ($x*1000)/($y*1000);
    $zc_round_ceil = (int)($zc_round);
    $multiplier = $zc_round_ceil * $y;
    $results = abs(round($x - $multiplier, 6));
     return $results;
  }

/**
 * return any field from products or products_description table
 * Example: zen_products_lookup('3', 'products_date_added');
 */
  function zen_products_lookup($product_id, $what_field = 'products_name', $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_lookup = $db->Execute("SELECT " . zen_db_input($what_field) . " AS lookup_field
                              FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              WHERE  p.products_id = " . (int)$product_id . "
                              AND pd.products_id = p.products_id
                              AND pd.language_id = " . (int)$language);
    if ($product_lookup->EOF) return '';
    return $product_lookup->fields['lookup_field'];
  }



// @TODO can this be merged with another pulldown, not specific to coupon admin?
function zen_geo_zones_pull_down_coupon($parameters, $selected = '')
{
    global $db;
    $select_string = '<select ' . $parameters . '>';
    $zones = $db->Execute("select geo_zone_id, geo_zone_name
                                 from " . TABLE_GEO_ZONES . "
                                 order by geo_zone_name");

    if ($selected == 0) {
        $select_string .= '<option value=0 SELECTED>' . TEXT_NONE . '</option>';
    } else {
        $select_string .= '<option value=0>' . TEXT_NONE . '</option>';
    }

    while (!$zones->EOF) {
        $select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
        if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
        $select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
        $zones->MoveNext();
    }
    $select_string .= '</select>';

    return $select_string;
}

/**
 * get customer comments
 */
  function zen_get_orders_comments($orders_id) {
    global $db;
    $orders_comments_query = "SELECT osh.comments from " .
                              TABLE_ORDERS_STATUS_HISTORY . " osh
                              where osh.orders_id = " . (int)$orders_id . "
                              order by osh.orders_status_history_id
                              limit 1";
    $orders_comments = $db->Execute($orders_comments_query);
    if ($orders_comments->EOF) return '';
    return $orders_comments->fields['comments'];
  }

/**
 * manufacturers name
 */
  function zen_get_products_manufacturers_name($product_id) {
    global $db;

    $product_query = "select m.manufacturers_name
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = " . (int)$product_id . "
                      and p.manufacturers_id = m.manufacturers_id";
    $product = $db->Execute($product_query);

    return ($product->RecordCount() > 0) ? $product->fields['manufacturers_name'] : "";
  }



/**
 * Perform an array multisort, based on 1 or 2 columns being passed
 * (defaults to sorting by first column ascendingly then second column ascendingly unless otherwise specified)
 *
 * @param $data        multidimensional array to be sorted
 * @param $columnName1 string representing the named column to sort by as first criteria
 * @param $order1      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @param $columnName2 string representing named column as second criteria
 * @param $order2      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @return array   Original array sorted as specified
 */
function zen_sort_array($data, $columnName1 = '', $order1 = SORT_ASC, $columnName2 = '', $order2 = SORT_ASC)
{
  // simple validations
  $keys = array_keys($data);
  if ($columnName1 == '') {
    $columnName1 = $keys[0];
  }
  if (!in_array($order1, array(SORT_ASC, SORT_DESC))) $order1=SORT_ASC;
  if ($columnName2 == '') {
    $columnName2 = $keys[1];
  }
  if (!in_array($order2, array(SORT_ASC, SORT_DESC))) $order2=SORT_ASC;

  // prepare sub-arrays for aiding in sorting
  foreach($data as $key=>$val)
  {
    $sort1[] = $val[$columnName1];
    $sort2[] = $val[$columnName2];
  }
  // do actual sort based on specified fields.
  array_multisort($sort1, $order1, $sort2, $order2, $data);
  return $data;
}

/**
 * Convert value to a float -- mainly used for sanitizing and returning non-empty strings or nulls
 * @param int|float|string $input
 * @return float|int
 */
    function convertToFloat($input = 0) {
        if ($input === null) return 0;
        $val = preg_replace('/[^0-9,\.\-]/', '', $input);
        // do a non-strict compare here:
        if ($val == 0) return 0;
        return (float)$val;
    }

function zen_set_ezpage_status($pages_id, $status, $status_field)
{
    global $db;
    if ($status == '1') {
        zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 0', 'info');
        return $db->Execute("update " . TABLE_EZPAGES . " set " . zen_db_input($status_field) . " = '0'  where pages_id = " . (int)$pages_id);
    } elseif ($status == '0') {
        zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 1', 'info');
        return $db->Execute("update " . TABLE_EZPAGES . " set " . zen_db_input($status_field) . " = '1'  where pages_id = " . (int)$pages_id);
    } else {
        return -1;
    }
}

