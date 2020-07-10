<?php
/**
 * Product Reviews info 
 * 
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 2978 2006-02-07 00:52:01Z drbyte $
 */
/**
 * Header code file for product review info page
 *
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_PRODUCT_REVIEWS_INFO');

  if (isset($_GET['reviews_id']) && zen_not_null($_GET['reviews_id']) && isset($_GET['products_id']) && zen_not_null($_GET['products_id'])) {

// check product exists and current
// if product does not exist or is status 0 send to _info page
    $products_reviews_check_query = "SELECT count(*) AS count 
                                     FROM " . TABLE_PRODUCTS . " p
                                     WHERE p.products_id= :productsID
                                     AND p.products_status = 1";

    $products_reviews_check_query = $db->bindVars($products_reviews_check_query, ':productsID', $_GET['products_id'], 'integer');
    $products_reviews_check = $db->Execute($products_reviews_check_query);

    if ($products_reviews_check->fields['count'] < 1) {
      zen_redirect(zen_href_link(zen_get_info_page((int)$_GET['products_id']), 'products_id=' . (int)$_GET['products_id']));
    }

// count reviews for additional link
// if review must be approved or disabled do not show review
    $review_status = " and r.status = '1'";

    $reviews_count_query = "SELECT count(*) as count 
                            FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                            WHERE r.products_id = :productsID
                            AND r.reviews_id = rd.reviews_id
                            AND rd.languages_id = :languagesID " . $review_status;

    $reviews_count_query = $db->bindVars( $reviews_count_query, ':productsID', $_GET['products_id'], 'integer');
    $reviews_count_query = $db->bindVars( $reviews_count_query, ':languagesID', $_SESSION['languages_id'], 'integer');
    $reviews_count = $db->Execute($reviews_count_query);

    $reviews_counter = $reviews_count->fields['count'];

// if review must be approved or disabled do not show review
    $review_status = " and r.status = '1'";

    $review_info_check_query = "SELECT count(*) AS total
                                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                                WHERE r.reviews_id = :reviewsID
                                AND r.products_id = :productsID
                                AND r.reviews_id = rd.reviews_id
                                AND rd.languages_id = :languagesID " . $review_status;

    $review_info_check_query = $db->bindVars($review_info_check_query, ':reviewsID', $_GET['reviews_id'], 'integer');
    $review_info_check_query = $db->bindVars($review_info_check_query, ':productsID', $_GET['products_id'], 'integer');
    $review_info_check_query = $db->bindVars($review_info_check_query, ':languagesID', $_SESSION['languages_id'], 'integer');
    $review_info_check = $db->Execute($review_info_check_query);

    if ($review_info_check->fields['total'] < 1) {
      zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))));
    }
  } else {
    zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))));
  }

  $sql = "UPDATE " . TABLE_REVIEWS . "
          SET reviews_read = reviews_read+1
          WHERE reviews_id = :reviewsID";

  $sql = $db->bindVars($sql, ':reviewsID', $_GET['reviews_id'], 'integer');
  $db->Execute($sql);

  $review_info_query = "SELECT rd.reviews_text, r.reviews_rating, r.reviews_id, r.customers_name,
                               r.date_added, r.reviews_read, p.products_id, p.products_price,
                               p.products_tax_class_id, p.products_image, p.products_model, pd.products_name
                        FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                        WHERE r.reviews_id = :reviewsID
                        AND r.reviews_id = rd.reviews_id
                        AND rd.languages_id = :languagesID
                        AND r.products_id = p.products_id
                        AND p.products_status = '1'
                        AND p.products_id = pd.products_id
                        AND pd.language_id = :languagesID " . $review_status;

  $review_info_query = $db->bindVars($review_info_query, ':reviewsID', $_GET['reviews_id'], 'integer');
  $review_info_query = $db->bindVars($review_info_query, ':languagesID', $_SESSION['languages_id'], 'integer');
  $review_info = $db->Execute($review_info_query);

  $products_price = zen_get_products_display_price($review_info->fields['products_id']);

  $products_name = $review_info->fields['products_name'];

  if ($review_info->fields['products_model'] != '') {
    $products_model = '<br /><span class="smallText">[' . $review_info->fields['products_model'] . ']</span>';
  } else {
    $products_model = '';
  }

// set image
//  $products_image = $review_info->fields['products_image'];
  if ($review_info->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $review_info->fields['products_image'];
  }

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  $breadcrumb->add(NAVBAR_TITLE);

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_PRODUCT_REVIEWS_INFO');
?>