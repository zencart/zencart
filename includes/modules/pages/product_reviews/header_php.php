<?php
/**
 * Product Reviews
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Wed Aug 28 23:29:28 2013 -0400 Modified in v1.5.3 $
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_PRODUCT_REVIEWS');

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

  $review_query_raw = "SELECT p.products_id, p.products_price, p.products_tax_class_id, p.products_image, p.products_model, pd.products_name
                       FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                       WHERE p.products_id = :productsID
                       AND p.products_status = 1
                       AND p.products_id = pd.products_id
                       AND pd.language_id = :languagesID";

  $review_query_raw = $db->bindVars($review_query_raw, ':productsID', $_GET['products_id'], 'integer');
  $review_query_raw = $db->bindVars($review_query_raw, ':languagesID', $_SESSION['languages_id'], 'integer');
  $review = $db->Execute($review_query_raw);

  $products_price = zen_get_products_display_price($review->fields['products_id']);
  $products_name = $review->fields['products_name'];

  if ($review->fields['products_model'] != '') {
    $products_model = '<br /><span class="smallText">[' . $review->fields['products_model'] . ']</span>';
  } else {
    $products_model = '';
  }


// set image
//  $products_image = $review->fields['products_image'];
  if ($review->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $review->fields['products_image'];
  }

  $review_status = " and r.status = 1";

  $reviews_query_raw = "SELECT r.reviews_id, left(rd.reviews_text, 100) as reviews_text, r.reviews_rating, r.date_added, r.customers_name
                        FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                        WHERE r.products_id = :productsID
                        AND r.reviews_id = rd.reviews_id
                        AND rd.languages_id = :languagesID " . $review_status . "
                        ORDER BY r.reviews_id desc";

  $reviews_query_raw = $db->bindVars($reviews_query_raw, ':productsID', $_GET['products_id'], 'integer');
  $reviews_query_raw = $db->bindVars($reviews_query_raw, ':languagesID', $_SESSION['languages_id'], 'integer');
  $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
  $reviews = $db->Execute($reviews_split->sql_query);
  $reviewsArray = array();
  while (!$reviews->EOF) {
    $reviewsArray[] = array('id'=>$reviews->fields['reviews_id'],
                            'customersName'=>$reviews->fields['customers_name'],
                            'dateAdded'=>$reviews->fields['date_added'],
                            'reviewsText'=>$reviews->fields['reviews_text'],
                            'reviewsRating'=>$reviews->fields['reviews_rating']);
    $reviews->MoveNext();
  }




  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  $breadcrumb->add(NAVBAR_TITLE);

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_PRODUCT_REVIEWS');
