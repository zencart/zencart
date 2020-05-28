<?php
/**
 * Header code file for the Advanced Search Results page
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jan 29 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ADVANCED_SEARCH_RESULTS');

if (!defined('KEYWORD_FORMAT_STRING')) define('KEYWORD_FORMAT_STRING','keywords');
if (!defined('ADVANCED_SEARCH_INCLUDE_METATAGS')) define('ADVANCED_SEARCH_INCLUDE_METATAGS', 'true');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
// set the product filters according to selected product type

$typefilter = 'default';
if (isset($_GET['typefilter'])) $typefilter = $_GET['typefilter'];
require(zen_get_index_filters_directory($typefilter . '_filter.php'));

$error = false;
$missing_one_input = false;

$_GET['keyword'] = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// -----
// Give an observer the chance to indicate that there's another element to the search
// that **is** provided, enabling the search to continue.
//
$search_additional_clause = false;
$zco_notifier->notify('NOTIFY_ADVANCED_SEARCH_RESULTS_ADDL_CLAUSE', array(), $search_additional_clause);

if ($search_additional_clause === false && 
(empty($_GET['keyword']) || $_GET['keyword'] == HEADER_SEARCH_DEFAULT_TEXT || $_GET['keyword'] == KEYWORD_FORMAT_STRING) &&
(isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
(isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
(isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
(isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
  $error = true;
  $missing_one_input = true;
  $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
} else {
  $dfrom = '';
  $dto = '';
  $pfrom = '';
  $pto = '';
  $keywords = '';
  $dfrom_array = array();
  $dto_array = array();

  if (isset($_GET['dfrom'])) {
    $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
  }

  if (isset($_GET['dto'])) {
    $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
  }

  if (isset($_GET['pfrom'])) {
    $pfrom = $_GET['pfrom'];
  }

  if (isset($_GET['pto'])) {
    $pto = $_GET['pto'];
  }

  if (isset($_GET['keyword']) && $_GET['keyword'] != HEADER_SEARCH_DEFAULT_TEXT  && $_GET['keyword'] != KEYWORD_FORMAT_STRING) {
    $keywords = $_GET['keyword'];
  }

  $date_check_error = false;
  if (zen_not_null($dfrom)) {
    if (!zen_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
      $error = true;
      $date_check_error = true;

      $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
    }
  }

  if (zen_not_null($dto)) {
    if (!zen_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
      $error = true;
      $date_check_error = true;

      $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
    }
  }

  if (($date_check_error == false) && zen_not_null($dfrom) && zen_not_null($dto)) {
    if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
      $error = true;

      $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
    }
  }

  $price_check_error = false;
  if (zen_not_null($pfrom)) {
    if (!settype($pfrom, 'float')) {
      $error = true;
      $price_check_error = true;

      $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
    }
  }

  if (zen_not_null($pto)) {
    if (!settype($pto, 'float')) {
      $error = true;
      $price_check_error = true;

      $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
    }
  }

  if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
    if ($pfrom > $pto) {
      $error = true;

      $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
    }
  }

  if (zen_not_null($keywords)) {
    if (!zen_parse_search_string(stripslashes($keywords), $search_keywords)) {
      $error = true;

      $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
    }
  }
}

if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
  $error = true;
  // redundant should be able to remove this
  if (!$missing_one_input) {
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  }
}

if ($error == true) {

  zen_redirect(zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params(), 'NONSSL', true, false));
}


$define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                     'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                     'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                     'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                     'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                     'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                     'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE);

asort($define_list);

$column_list = array();
foreach($define_list as $column => $value) {
  if ($value) $column_list[] = $column;
}

$select_column_list = '';

for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
  if (($column_list[$col] == 'PRODUCT_LIST_NAME') || ($column_list[$col] == 'PRODUCT_LIST_PRICE')) {
    continue;
  }

  if (zen_not_null($select_column_list)) {
    $select_column_list .= ', ';
  }

  switch ($column_list[$col]) {
    case 'PRODUCT_LIST_MODEL':
    $select_column_list .= 'p.products_model';
    break;
    case 'PRODUCT_LIST_MANUFACTURER':
    $select_column_list .= 'm.manufacturers_name';
    break;
    case 'PRODUCT_LIST_QUANTITY':
    $select_column_list .= 'p.products_quantity';
    break;
    case 'PRODUCT_LIST_IMAGE':
    $select_column_list .= 'p.products_image';
    break;
    case 'PRODUCT_LIST_WEIGHT':
    $select_column_list .= 'p.products_weight';
    break;
  }
}
/*
// always add quantity regardless of whether or not it is in the listing for add to cart buttons
if (PRODUCT_LIST_QUANTITY < 1) {
  $select_column_list .= ', p.products_quantity ';
}
*/

// always add quantity regardless of whether or not it is in the listing for add to cart buttons
if (PRODUCT_LIST_QUANTITY < 1) {
  if (empty($select_column_list)) {
    $select_column_list .= ' p.products_quantity ';
  } else  {
    $select_column_list .= ', p.products_quantity ';
  }
}

if (zen_not_null($select_column_list)) {
  $select_column_list .= ', ';
}

// Notifier Point
$zco_notifier->notify('NOTIFY_SEARCH_COLUMNLIST_STRING');


//  $select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = 1, s.specials_new_products_price, p.products_price) as final_price ";
$select_str = "SELECT DISTINCT " . $select_column_list .
              " p.products_sort_order, m.manufacturers_id, p.products_id, pd.products_name, 
                p.products_price, p.products_tax_class_id, p.products_price_sorter, 
                p.products_qty_box_status, p.master_categories_id, p.product_is_call ";

if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
  $select_str .= ", SUM(tr.tax_rate) AS tax_rate ";
}

// Notifier Point
$zco_notifier->notify('NOTIFY_SEARCH_SELECT_STRING');


//  $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
$from_str = "FROM (" . TABLE_PRODUCTS . " p
             LEFT JOIN " . TABLE_MANUFACTURERS . " m
             USING(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c )";
             
if (ADVANCED_SEARCH_INCLUDE_METATAGS == 'true') {
    $from_str .= 
        " LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
             ON mtpd.products_id= p2c.products_id
            AND mtpd.language_id = :languagesID";
    $from_str = $db->bindVars($from_str, ':languagesID', $_SESSION['languages_id'], 'integer');
}

if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
  if (empty($_SESSION['customer_country_id'])) {
    $_SESSION['customer_country_id'] = STORE_COUNTRY;
    $_SESSION['customer_zone_id'] = STORE_ZONE;
  }
  $from_str .= " LEFT JOIN " . TABLE_TAX_RATES . " tr
                 ON p.products_tax_class_id = tr.tax_class_id
                 LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz
                 ON tr.tax_zone_id = gz.geo_zone_id
                 AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryID)
                 AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneID)";

  $from_str = $db->bindVars($from_str, ':zoneCountryID', $_SESSION['customer_country_id'], 'integer');
  $from_str = $db->bindVars($from_str, ':zoneID', $_SESSION['customer_zone_id'], 'integer');
}

// Notifier Point
$zco_notifier->notify('NOTIFY_SEARCH_FROM_STRING');

$where_str = " WHERE (p.products_status = 1
               AND p.products_id = pd.products_id
               AND pd.language_id = :languagesID
               AND p.products_id = p2c.products_id
               AND p2c.categories_id = c.categories_id ";

$where_str = $db->bindVars($where_str, ':languagesID', $_SESSION['languages_id'], 'integer');

// reset previous selection
if (!isset($_GET['inc_subcat'])) {
  $_GET['inc_subcat'] = '0';
}
if (!isset($_GET['search_in_description'])) {
  $_GET['search_in_description'] = '0';
}
$_GET['search_in_description'] = (int)$_GET['search_in_description'];

if (isset($_GET['categories_id']) && zen_not_null($_GET['categories_id'])) {
  if ($_GET['inc_subcat'] == '1') {
    $subcategories_array = array();
    zen_get_subcategories($subcategories_array, $_GET['categories_id']);
    $where_str .= " AND p2c.products_id = p.products_id
                    AND p2c.products_id = pd.products_id
                    AND (p2c.categories_id = :categoriesID";

    $where_str = $db->bindVars($where_str, ':categoriesID', $_GET['categories_id'], 'integer');

    if (sizeof($subcategories_array) > 0) {
      $where_str .= " OR p2c.categories_id in (";
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $where_str .= " :categoriesID";
        if ($i+1 < $n) $where_str .= ",";
        $where_str = $db->bindVars($where_str, ':categoriesID', $subcategories_array[$i], 'integer');
      }
      $where_str .= ")";
    }
    $where_str .= ")";
  } else {
    $where_str .= " AND p2c.products_id = p.products_id
                    AND p2c.products_id = pd.products_id
                    AND pd.language_id = :languagesID
                    AND p2c.categories_id = :categoriesID";

    $where_str = $db->bindVars($where_str, ':categoriesID', $_GET['categories_id'], 'integer');
    $where_str = $db->bindVars($where_str, ':languagesID', $_SESSION['languages_id'], 'integer');
  }
}

if (isset($_GET['manufacturers_id']) && zen_not_null($_GET['manufacturers_id'])) {
  $where_str .= " AND m.manufacturers_id = :manufacturersID";
  $where_str = $db->bindVars($where_str, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
}

if (isset($keywords) && zen_not_null($keywords)) {
  if (zen_parse_search_string(stripslashes($_GET['keyword']), $search_keywords)) {
    $where_str .= " AND (";
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      switch ($search_keywords[$i]) {
        case '(':
        case ')':
        case 'and':
        case 'or':
        $where_str .= " " . $search_keywords[$i] . " ";
        break;
        default:
        $where_str .= "(pd.products_name LIKE '%:keywords%'
                                         OR p.products_model
                                         LIKE '%:keywords%'
                                         OR m.manufacturers_name
                                         LIKE '%:keywords%'";

        $where_str = $db->bindVars($where_str, ':keywords', $search_keywords[$i], 'noquotestring');
        
        // conditionally include meta tags in search
        if (ADVANCED_SEARCH_INCLUDE_METATAGS == 'true') {
            $where_str .= " OR (mtpd.metatags_keywords != '' AND mtpd.metatags_keywords LIKE '%:keywords%')";
            $where_str .= " OR (mtpd.metatags_description != '' AND mtpd.metatags_description LIKE '%:keywords%')";
            $where_str = $db->bindVars($where_str, ':keywords', $search_keywords[$i], 'noquotestring');
        }

        if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) {
          $where_str .= " OR pd.products_description
                          LIKE '%:keywords%'";

          $where_str = $db->bindVars($where_str, ':keywords', $search_keywords[$i], 'noquotestring');
        }
        $where_str .= ')';
        break;
      }
    }
    $where_str .= " ))";
  }
}
if (!isset($keywords) || $keywords == "") {
  $where_str .= ')';
}
  if (isset($_GET['alpha_filter_id']) && (int)$_GET['alpha_filter_id'] > 0) {
    $alpha_sort = " and (pd.products_name LIKE '" . chr((int)$_GET['alpha_filter_id']) . "%') ";
    $where_str .= $alpha_sort;
  } else {
    $alpha_sort = '';
    $where_str .= $alpha_sort;
  }

if (isset($_GET['dfrom']) && zen_not_null($_GET['dfrom']) && ($_GET['dfrom'] != DOB_FORMAT_STRING)) {
  $where_str .= " AND p.products_date_added >= :dateAdded";
  $where_str = $db->bindVars($where_str, ':dateAdded', zen_date_raw($dfrom), 'date');
}

if (isset($_GET['dto']) && zen_not_null($_GET['dto']) && ($_GET['dto'] != DOB_FORMAT_STRING)) {
  $where_str .= " and p.products_date_added <= :dateAdded";
  $where_str = $db->bindVars($where_str, ':dateAdded', zen_date_raw($dto), 'date');
}

$rate = $currencies->get_value($_SESSION['currency']);
$pfrom = 0.0;
$pto = 0.0;

if ($rate) {
  if (!empty($_GET['pfrom'])) {
    $pfrom = (float)$_GET['pfrom'] / $rate;
  }
  if (!empty($_GET['pto'])) {
    $pto = (float)$_GET['pto'] / $rate;
  }
}

if (DISPLAY_PRICE_WITH_TAX == 'true') {
  if ($pfrom) {
    $where_str .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :price)";
    $where_str = $db->bindVars($where_str, ':price', $pfrom, 'float');
  }
  if ($pto) {
    $where_str .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :price)";
    $where_str = $db->bindVars($where_str, ':price', $pto, 'float');
  }
} else {
  if ($pfrom) {
    $where_str .= " and (p.products_price_sorter >= :price)";
    $where_str = $db->bindVars($where_str, ':price', $pfrom, 'float');
  }
  if ($pto) {
    $where_str .= " and (p.products_price_sorter <= :price)";
    $where_str = $db->bindVars($where_str, ':price', $pto, 'float');
  }
}


$order_str = '';

// Notifier Point
$zco_notifier->notify('NOTIFY_SEARCH_WHERE_STRING');


if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
  $where_str .= " group by p.products_id, tr.tax_priority";
}

// set the default sort order setting from the Admin when not defined by customer
if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
  $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
}
if ((!isset($_GET['sort'])) || (!preg_match('/[1-8][ad]/', $_GET['sort'])) || (substr($_GET['sort'], 0 , 1) > sizeof($column_list))) {
  for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
    if ($column_list[$col] == 'PRODUCT_LIST_NAME') {
      $_GET['sort'] = $col+1 . 'a';
      $order_str .= ' ORDER BY pd.products_name';
      break;
    } else {
      // sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
      // for reverse, descending order use:
      //       $listing_sql .= " order by p.products_sort_order desc, pd.products_name";
      $order_str .= " order by p.products_sort_order, pd.products_name";
      break;
    }
  }
  // if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
  if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
    $_GET['sort'] = '20a';
  }
} else {
  $sort_col = substr($_GET['sort'], 0 , 1);
  $sort_order = substr($_GET['sort'], -1);
  $order_str = ' order by ';
  switch ($column_list[$sort_col-1]) {
    case 'PRODUCT_LIST_MODEL':
    $order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    break;
    case 'PRODUCT_LIST_NAME':
    $order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
    break;
    case 'PRODUCT_LIST_MANUFACTURER':
    $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    break;
    case 'PRODUCT_LIST_QUANTITY':
    $order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    break;
    case 'PRODUCT_LIST_IMAGE':
    $order_str .= "pd.products_name";
    break;
    case 'PRODUCT_LIST_WEIGHT':
    $order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    break;
    case 'PRODUCT_LIST_PRICE':
    //        $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    $order_str .= "p.products_price_sorter " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
    break;
  }
}
//$_GET['keyword'] = zen_output_string_protected($_GET['keyword']);

$listing_sql = $select_str . $from_str . $where_str . $order_str;
// Notifier Point
$zco_notifier->notify('NOTIFY_SEARCH_ORDERBY_STRING', $listing_sql);

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ADVANCED_SEARCH));
$breadcrumb->add(NAVBAR_TITLE_2);
$breadcrumb->add(zen_output_string_protected($keywords));

$result = new splitPageResults($listing_sql, MAX_DISPLAY_PRODUCTS_LISTING, 'p.products_id', 'page');
if ($result->number_of_rows == 0) {
  $messageStack->add_session('search', TEXT_NO_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params('action')));
}
// if only one product found in search results, go directly to the product page, instead of displaying a link to just one item:
if ($result->number_of_rows == 1 && SKIP_SINGLE_PRODUCT_CATEGORIES == 'True') {
  $result = $db->Execute($listing_sql);
  zen_redirect(zen_href_link(zen_get_info_page($result->fields['products_id']), 'cPath=' . zen_get_product_path($result->fields['products_id']) . '&products_id=' . $result->fields['products_id']));
}
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ADVANCED_SEARCH_RESULTS', $keywords);
//EOF
