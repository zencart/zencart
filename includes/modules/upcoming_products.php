<?php
/**
 * upcoming_products module
 *
 * @package modules
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: upcoming_products.php 18923 2011-06-13 03:40:09Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// initialize vars
$categories_products_id_list = '';
$list_of_products = '';
$expected_query = '';

$display_limit = zen_get_upcoming_date_range();
$productsInCategory = array();

$limit_clause = "  order by " . (EXPECTED_PRODUCTS_FIELD == 'date_expected' ? 'date_expected' : 'products_name') . " " . (EXPECTED_PRODUCTS_SORT == 'asc' ? 'asc' : 'desc') . "
                   limit " . (int)MAX_DISPLAY_UPCOMING_PRODUCTS;

if ( (($manufacturers_id > 0 && $_GET['filter_id'] == 0) || $_GET['music_genre_id'] > 0 || $_GET['record_company_id'] > 0) || (!isset($new_products_category_id) || $new_products_category_id == '0') ) {
  $expected_query = "select p.products_id, pd.products_name, products_date_available as date_expected, p.master_categories_id
                     from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                     where p.products_id = pd.products_id
                     and p.products_status = 1
                     and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'" .
                     $display_limit .
                     $limit_clause;
} else {
  if ($manufacturers_id > 0 && $_GET['filter_id'] > 0)
  {
    $categoryId = $_GET['filter_id'];
  } else
  {
    $categoryId = zenGetLeafCategory($cPath);
  }
  
  if (!isset($contentBoxCategoryList))
  {
    $categories = zenGetCategoryArrayWithChildren($categoryId);
    $contentBoxCategoryList = implode(',', $categories);
  }

   $expected_query = "select p.products_id, pd.products_name, products_date_available as date_expected, p.master_categories_id
                       from (" . TABLE_PRODUCTS . " p 
                       left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id 
                       left join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc on p.products_id = ptc.products_id)
                       where ptc.categories_id IN (" . $contentBoxCategoryList . ")
                       and p.products_id = pd.products_id
                       and p.products_status = 1
                       and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
                       $display_limit .
                       $limit_clause;
  }


if ($expected_query != '') $expected = $db->Execute($expected_query);
if ($expected_query != '' && $expected->RecordCount() > 0) {
  while (!$expected->EOF) {
    if (!isset($productsInCategory[$expected->fields['products_id']])) $productsInCategory[$expected->fields['products_id']] = zen_get_generated_category_path_rev($expected->fields['master_categories_id']);
    $expectedItems[] = $expected->fields;
    $expected->MoveNext();
  }
  require($template->get_template_dir('tpl_modules_upcoming_products.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_upcoming_products.php');
}
