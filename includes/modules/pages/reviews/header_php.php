<?php
/**
 * reviews header_php.php 
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: modified in v1.6.0 $
 */

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


// if review must be approved or is disabled, do not show review
$review_status = " AND r.status = 1";
$reviews_query_raw = "SELECT r.reviews_id, left(rd.reviews_text, 100) AS reviews_text, r.reviews_rating, r.date_added, p.products_id, pd.products_name, p.products_image, r.customers_name 
                      FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                      WHERE p.products_status = '1' 
                      AND p.products_id = r.products_id 
                      AND r.reviews_id = rd.reviews_id 
                      AND p.products_id = pd.products_id 
                      AND pd.language_id = :languageID 
                      AND rd.languages_id = :languageID" . $review_status . " 
                      ORDER BY r.reviews_id DESC";

$reviews_query_raw = $db->bindVars($reviews_query_raw, ':languageID', $_SESSION['languages_id'], 'integer');
$reviews_query_raw = $db->bindVars($reviews_query_raw, ':languageID', $_SESSION['languages_id'], 'integer');

$reviews_query_count = "SELECT COUNT(*) as total
                      FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                      WHERE p.products_status = '1'
                      AND p.products_id = r.products_id
                      AND r.reviews_id = rd.reviews_id
                      AND p.products_id = pd.products_id
                      AND pd.language_id = :languageID
                      AND rd.languages_id = :languageID" . $review_status;

$reviews_query_count = $db->bindVars($reviews_query_count, ':languageID', $_SESSION['languages_id'], 'integer');
$reviews_query_count = $db->bindVars($reviews_query_count, ':languageID', $_SESSION['languages_id'], 'integer');

$class = NAMESPACE_PAGINATOR . '\\Paginator';
$paginator = new $class($zcRequest);
$paginator->setAdapterParams(array('itemsPerPage'=>MAX_DISPLAY_NEW_REVIEWS));
$paginator->setScrollerParams(array('navLinkText'=>TEXT_DISPLAY_NUMBER_OF_REVIEWS));
$adapterDate = array('dbConn'=>$db, 'mainSql'=>$reviews_query_raw, 'countSql'=>$reviews_query_count);
$paginator->doPagination($adapterDate);
$result = $paginator->getScroller()->getResults();
$tplVars['listingBox']['paginator'] = $result;

$breadcrumb->add(NAVBAR_TITLE);

