<?php
/**
 * compatibility functions - these are things that are being retired in future versions
 * It is better to use the "new way" to do these things ... which is generally demonstrated by the code inside the functions herein.
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: compatibility.php $
 */



/**
 * Lookup Languages Icon
 */
  function zen_get_language_icon($lookup) {
    global $lng;
    $data = $lng->get_language_data_by_id($lookup);
    if ($data == false || $data['image'] == '') return '';
    $icon = zen_image(DIR_WS_CATALOG_LANGUAGES . $data['directory'] . '/images/' . $data['image']);
    return $icon;
  }

/**
 * lookup language dir from id
 */
  function zen_get_language_name($lookup) {
    global $lng;
    $data = $lng->get_language_data_by_id($lookup);
    if ($data == false || $data['image'] == '') return '';
    return $data['directory'];
  }


/**
 * Count how many subcategories exist in a category
 * TABLES: categories
 */
  function zen_get_products_master_categories_name($categories_id) {
    return zen_get_categories_parent_name($categories_id);
  }


/**
 * Checks to see if the currency code exists as a currency
 * @deprecated since 1.6.0 -- use $currencies->isset() instead
 * @param string $code
 * @param bool $getFirstDefault
 * @return bool|string
 */
function zen_currency_exists($code, $getFirstDefault = false) {
    global $db;

    $currency_code = "select code
                      from " . TABLE_CURRENCIES . "
                      where code = '" . zen_db_input($code) . "' LIMIT 1";

    $currency_first = "select code
                      from " . TABLE_CURRENCIES . "
                      order by value ASC LIMIT 1";

    $currency = $db->Execute(($getFirstDefault == false) ? $currency_code : $currency_first);

    if ($currency->RecordCount()) {
        return strtoupper($currency->fields['code']);
    } else {
        return false;
    }
}
/**
 * alias to str_replace
 * @deprecated since v1.5.0  - use str_replace() instead
 * @param $from
 * @param $to
 * @param $string
 * @return mixed
 */
function zen_convert_linefeeds($from, $to, $string) {
    return str_replace($from, $to, $string);
}
/**
 * @deprecated  since v1.5.0
 * @param string $given_html
 * @param int $quote_style
 * @return string
 */
function zen_html_entity_decode($given_html, $quote_style = ENT_QUOTES) {
    $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
    $trans_table['&#39;'] = "'";
    return ( strtr( $given_html, $trans_table ) );
}


/**
 * Decode string encoded with htmlspecialchars()
 * CLR 030228 Add function zen_decode_specialchars
 * @deprecated  since 1.3.0
 * @param $string
 * @return mixed
 */
function zen_decode_specialchars($string){
    $string=str_replace('&gt;', '>', $string);
    $string=str_replace('&lt;', '<', $string);
    $string=str_replace('&#039;', "'", $string);
    $string=str_replace('&quot;', "\"", $string);
    $string=str_replace('&amp;', '&', $string);

    return $string;
}
/**
 * @deprecated since v1.5.0 - use zen_output_string_protected() instead
 * @param $string
 * @return string
 */
function zen_db_output($string) {
    return htmlspecialchars($string, ENT_COMPAT, CHARSET, TRUE);
}
