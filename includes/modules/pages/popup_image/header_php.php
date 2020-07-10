<?php
/**
 * Pop up Image Header
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Nov 09 Modified in v1.5.7 $
 */
/**
 * Header code file for the product-larger-images popup window
 *
 */

// This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_POPUP_IMAGES_ADDITIONAL');

  $_SESSION['navigation']->remove_current_page();

  $products_values_query = "SELECT pd.products_name, p.products_image
                            FROM " . TABLE_PRODUCTS . " p
                            left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on p.products_id = pd.products_id
                            WHERE p.products_status = 1
                            and p.products_id = :productsID
                            and pd.language_id = :languagesID ";

  $products_values_query = $db->bindVars($products_values_query, ':productsID', $_GET['pID'], 'integer');
  $products_values_query = $db->bindVars($products_values_query, ':languagesID', $_SESSION['languages_id'], 'integer');

  $products_values = $db->Execute($products_values_query);

  // Ensure data/variable is available for use downstream.
  if ($products_values->EOF) {
    $products_values->fields['products_image'] = '';
    $products_values->fields['products_name'] = '';
  }

  $products_image = $products_values->fields['products_image'];

  //auto replace with defined missing image
  if ($products_image == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  }

  $products_image_extension = substr($products_image, strrpos($products_image, '.'));
  $products_image_base = preg_replace('|'.$products_image_extension.'$|', '', $products_image);
  $products_image_medium = $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
  $products_image_large = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

  // check for a medium image else use small
  if (!file_exists(DIR_WS_IMAGES . 'medium/' . $products_image_medium)) {
    $products_image_medium = DIR_WS_IMAGES . $products_image;
  } else {
    $products_image_medium = DIR_WS_IMAGES . 'medium/' . $products_image_medium;
  }
  // check for a large image else use medium else use small
  if (!file_exists(DIR_WS_IMAGES . 'large/' . $products_image_large)) {
    $products_image_large = $products_image_medium;
  } else {
    $products_image_large = DIR_WS_IMAGES . 'large/' . $products_image_large;
  }

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_POPUP_IMAGES');
