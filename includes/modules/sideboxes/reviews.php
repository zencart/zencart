<?php
/**
 * reviews sidebox - displays a random product-review
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: reviews.php 16044 2010-04-23 01:15:45Z drbyte $
 */

// if review must be approved or disabled do not show review
  $review_status = " and r.status = 1 ";

  $random_review_sidebox_select = "select r.reviews_id, r.reviews_rating, p.products_id, p.products_image, pd.products_name,
                    substring(reviews_text, 1, 70) as reviews_text
                    from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                           . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    where p.products_status = '1'
                    and p.products_id = r.products_id
                    and r.reviews_id = rd.reviews_id
                    and rd.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                    and p.products_id = pd.products_id
                    and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'" .
                    $review_status;

  if (isset($_GET['products_id'])) {
    $random_review_sidebox_select .= " and p.products_id = '" . (int)$_GET['products_id'] . "'";
  }

  $random_review_sidebox_product = $db->ExecuteRandomMulti($random_review_sidebox_select, MAX_RANDOM_SELECT_REVIEWS);
  if ($random_review_sidebox_product->RecordCount() > 0) {
    require($template->get_template_dir('tpl_reviews_random.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_reviews_random.php');
  } elseif (isset($_GET['products_id']) and zen_products_id_valid($_GET['products_id'])) {
// display 'write a review' box
    require($template->get_template_dir('tpl_reviews_write.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_reviews_write.php');
  } else {
// display 'no reviews' box
    require($template->get_template_dir('tpl_reviews_none.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_reviews_none.php');
  }
  $title =  BOX_HEADING_REVIEWS;
  $title_link = FILENAME_REVIEWS;
  require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
?>