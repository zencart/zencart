<?php
/**
 * index main_template_vars.php
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

// This should be first line of the script:
$zco_notifier->notify ( 'NOTIFY_HEADER_START_INDEX_MAIN_TEMPLATE_VARS' );
// die($category_depth);
// die($_GET['music_genre_id']);

// release manufacturers_id when nothing is there so a blank filter is not setup.
// this will result in the home page, if used
if (isset ( $_GET['manufacturers_id'] ) && $_GET['manufacturers_id'] <= 0)
{
  unset ( $_GET['manufacturers_id'] );
  unset ( $manufacturers_id );
}

// release music_genre_id when nothing is there so a blank filter is not setup.
// this will result in the home page, if used
if (isset ( $_GET['music_genre_id'] ) && $_GET['music_genre_id'] <= 0)
{
  unset ( $_GET['music_genre_id'] );
  unset ( $music_genre_id );
}

// release record_company_id when nothing is there so a blank filter is not setup.
// this will result in the home page, if used
if (isset ( $_GET['record_company_id'] ) && $_GET['record_company_id'] <= 0)
{
  unset ( $_GET['record_company_id'] );
  unset ( $record_company_id );
}

// only release typefilter if both record_company_id and music_genre_id are blank
// this will result in the home page, if used
if ((isset ( $_GET['record_company_id'] ) && $_GET['record_company_id'] <= 0) and (isset ( $_GET['music_genre_id'] ) && $_GET['music_genre_id'] <= 0))
{
  unset ( $_GET['typefilter'] );
  unset ( $typefilter );
}

// release filter for category or manufacturer when nothing is there
if (isset ( $_GET['filter_id'] ) && $_GET['filter_id'] <= 0)
{
  unset ( $_GET['filter_id'] );
  unset ( $filter_id );
}

// release alpha filter when nothing is there
if (isset ( $_GET['alpha_filter_id'] ) && $_GET['alpha_filter_id'] <= 0)
{
  unset ( $_GET['alpha_filter_id'] );
  unset ( $alpha_filter_id );
}
// hook to notifier so that additional product-type-specific vars can be released too
$zco_notifier->notify ( 'NOTIFY_HEADER_INDEX_MAIN_TEMPLATE_VARS_RELEASE_PRODUCT_TYPE_VARS' );

if ($category_depth == 'nested')
{
  $sql = "SELECT cd.categories_name, c.categories_image
          FROM   " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
          WHERE      c.categories_id = :categoriesID
          AND        cd.categories_id = :categoriesID
          AND        cd.language_id = :languagesID
          AND        c.categories_status= '1'";

  $sql = $db->bindVars ( $sql, ':categoriesID', $current_category_id, 'integer' );
  $sql = $db->bindVars ( $sql, ':languagesID', $_SESSION['languages_id'], 'integer' );
  $category = $db->Execute ( $sql );

  if (isset ( $cPath ) && strpos ( $cPath, '_' ))
  {
    // check to see if there are deeper categories within the current category
    $category_links = array_reverse ( $cPath_array );
    for($i = 0, $n = sizeof ( $category_links ); $i < $n; $i ++)
    {
      $sql = "SELECT count(*) AS total
              FROM   " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
              WHERE      c.parent_id = :parentID
              AND        c.categories_id = cd.categories_id
              AND        cd.language_id = :languagesID
              AND        c.categories_status= '1'";

      $sql = $db->bindVars ( $sql, ':parentID', $category_links[$i], 'integer' );
      $sql = $db->bindVars ( $sql, ':languagesID', $_SESSION['languages_id'], 'integer' );
      $categories = $db->Execute ( $sql );

      if ($categories->fields['total'] < 1)
      {
        // do nothing, go through the loop
      } else
      {
        $categories_query = "SELECT c.categories_id, cd.categories_name, c.categories_image, c.parent_id
                             FROM   " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                             WHERE      c.parent_id = :parentID
                             AND        c.categories_id = cd.categories_id
                             AND        cd.language_id = :languagesID
                             AND        c.categories_status= '1'
                             ORDER BY   sort_order, cd.categories_name";

        $categories_query = $db->bindVars ( $categories_query, ':parentID', $category_links[$i], 'integer' );
        $categories_query = $db->bindVars ( $categories_query, ':languagesID', $_SESSION['languages_id'], 'integer' );
        break; // we've found the deepest category the customer is in
      }
    }
  } else
  {
    $categories_query = "SELECT c.categories_id, cd.categories_name, c.categories_image, c.parent_id
                         FROM   " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                         WHERE      c.parent_id = :parentID
                         AND        c.categories_id = cd.categories_id
                         AND        cd.language_id = :languagesID
                         AND        c.categories_status= '1'
                         ORDER BY   sort_order, cd.categories_name";

    $categories_query = $db->bindVars ( $categories_query, ':parentID', $current_category_id, 'integer' );
    $categories_query = $db->bindVars ( $categories_query, ':languagesID', $_SESSION['languages_id'], 'integer' );
  }
  $categories = $db->Execute ( $categories_query );
  $number_of_categories = $categories->RecordCount ();
  $new_products_category_id = $current_category_id;

  // ///////////////////////////////////////////////////////////////////////////////////////////////////
  $tpl_page_body = 'tpl_index_categories.php';
  // ///////////////////////////////////////////////////////////////////////////////////////////////////

  // } elseif ($category_depth == 'products' || isset($_GET['manufacturers_id']) || isset($_GET['music_genre_id'])) {
} elseif ($category_depth == 'products' || zen_check_url_get_terms ())
{
  if (SHOW_PRODUCT_INFO_ALL_PRODUCTS == '1')
  {
    // set a category filter
    $new_products_category_id = $cPath;
  } else
  {
    // do not set the category
  }
  if ($categoryError)
  {
    $cPath = '';
    $new_products_category_id = '0';
  }

  // //////////////////////////////////////////////////////////////////////////////////////////////////////////
  $tpl_page_body = 'tpl_index_product_list.php';
  // //////////////////////////////////////////////////////////////////////////////////////////////////////////
} else
{
  // //////////////////////////////////////////////////////////////////////////////////////////////////////////
  $tpl_page_body = 'tpl_index_default.php';
  // //////////////////////////////////////////////////////////////////////////////////////////////////////////
}

$listingBoxManager = new ZenCart\QueryBuilderDefinitions\Manager('INDEX_DEFAULT', $db, $zcRequest);
$listingBoxes = $listingBoxManager->getListingBoxes ();
$tplVars['listingBoxes'] = $listingBoxes;

$current_categories_description = "";
$current_categories_name = "";
// categories_description
$sql = "SELECT categories_description, categories_name
        FROM " . TABLE_CATEGORIES_DESCRIPTION . "
        WHERE categories_id= :categoriesID
        AND language_id = :languagesID";

$sql = $db->bindVars ( $sql, ':categoriesID', $current_category_id, 'integer' );
$sql = $db->bindVars ( $sql, ':languagesID', $_SESSION['languages_id'], 'integer' );
$categories_description_lookup = $db->Execute ( $sql );
if ($categories_description_lookup->RecordCount () > 0)
{
  $current_categories_description = $categories_description_lookup->fields['categories_description'];
  $current_categories_name = $categories_description_lookup->fields['categories_name'];
}

if ($current_categories_name == '' && isset($_GET['manufacturers_id'])) {
  $result = $db->Execute( "SELECT * FROM " . TABLE_MANUFACTURERS . "
                           WHERE manufacturers_id = " . (int)$_GET['manufacturers_id'] . " LIMIT 1");
  if (!$result->EOF) $current_categories_name = $result->fields['manufacturers_name'];
}
if ($current_categories_name == '' && isset($_GET['record_company_id'])) {
  $result = $db->Execute( "SELECT * FROM " . TABLE_RECORD_COMPANY . "
                           WHERE record_company_id = " . (int)$_GET['record_company_id'] . " LIMIT 1");
  if (!$result->EOF) $current_categories_name = $result->fields['record_company_name'];
}
if ($current_categories_name == '' && isset($_GET['music_genre_id'])) {
  $result = $db->Execute( "SELECT * FROM " . TABLE_MUSIC_GENRE . "
                           WHERE music_genre_id = " . (int)$_GET['music_genre_id'] . " LIMIT 1");
  if (!$result->EOF) $current_categories_name = $result->fields['music_genre_name'];
}
$zco_notifier->notify('NOTIFY_HEADER_INDEX_MAIN_TEMPLATE_VARS_PAGE_BODY', NULL, $tpl_page_body, $current_categories_name);

require ($template->get_template_dir ( $tpl_page_body, DIR_WS_TEMPLATE, $current_page_base, 'templates' ) . '/' . $tpl_page_body);

// This should be last line of the script:
$zco_notifier->notify ( 'NOTIFY_HEADER_END_INDEX_MAIN_TEMPLATE_VARS', NULL, $current_categories_description );
