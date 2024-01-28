<?php
/**
 * reviews header_php.php 
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: simon1066 2023 May 21 Modified in v2.0.0-alpha1 $
 */

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


// if review must be approved or is disabled, do not show review
$review_status = " AND r.status = 1";
$reviews_query_raw = "SELECT r.reviews_id, rd.reviews_text, r.reviews_rating, r.date_added, p.products_id, pd.products_name, p.products_image, r.customers_name 
                      FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                      WHERE p.products_status = '1' 
                      AND p.products_id = r.products_id 
                      AND r.reviews_id = rd.reviews_id 
                      AND p.products_id = pd.products_id 
                      AND pd.language_id = :languageID 
                      AND rd.languages_id = :languageID" . $review_status . " 
                      ORDER BY r.reviews_id DESC";

$reviews_query_raw = $db->bindVars($reviews_query_raw, ':languageID', $_SESSION['languages_id'], 'integer');
$reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);

$breadcrumb->add(NAVBAR_TITLE);
?>
