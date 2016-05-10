<?php
/**
 * pop up image additional
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Fri Sep 11 15:51:04 2015 +0100 Modified in v1.5.5 $
 */
// This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_POPUP_IMAGES_ADDITIONAL');

  if (!isset($base_path)) $base_path = DIR_FS_CATALOG;

  if (!defined('TEXT_NO_IMAGE_AVAILABLE')) define('TEXT_NO_IMAGE_AVAILABLE', 'No image available');

  $_SESSION['navigation']->remove_current_page();

  $products_values_query = "SELECT pd.products_name, p.products_image, p.products_status
                            FROM " . TABLE_PRODUCTS . " p
                            left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on p.products_id = pd.products_id
                            WHERE p.products_id = :productsID
                            and pd.language_id = :languagesID ";

  $products_values_query = $db->bindVars($products_values_query, ':productsID', $_GET['pID'], 'integer');
  $products_values_query = $db->bindVars($products_values_query, ':languagesID', $_SESSION['languages_id'], 'integer');

  $products_values = $db->Execute($products_values_query);


  if (!$products_values->EOF) {
    $product_disabled = (bool)($products_values->fields['products_status'] == 0);

    $products_name = $products_values->fields['products_name'];

    $products_image = $products_values->fields['products_image'];

    $products_image_extension = substr($products_image, strrpos($products_image, '.'));
    $products_image_base = preg_replace('|'.$products_image_extension.'$|', '', $products_image);
    $products_image_medium = DIR_WS_IMAGES . 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
    $products_image_large = DIR_WS_IMAGES . 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

    $pos = $_GET['pos'] = (int)$_GET['pos'];

    // set base/fallback
    $products_image_path = DIR_WS_IMAGES . $products_image;
  }
  
  $_GET['products_image_large_additional'] = str_replace(' ', '+', stripslashes($_REQUEST['products_image_large_additional']));

  $basepath = "";
  $realBase = realpath($basepath);
  $userpath = $basepath . $_GET['products_image_large_additional'];
  $realUserPath = realpath($userpath);
  if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
      $_GET['products_image_large_additional'] = '';
  }

  $zco_notifier->notify('NOTIFY_POPUP_IMAGES_ADDITIONAL_INTERCEPT');

  // if product disabled, display none
  if ($product_disabled) {
    $products_image_path = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    $products_name = TEXT_NO_IMAGE_AVAILABLE;
    header('HTTP/1.1 503 Unavailable');
  } elseif (file_exists($base_path . $_GET['img'])) {
  // use supplied image path if product not disabled and valid path supplied
    $products_image_path = htmlentities($_GET['img']);
  } elseif (!file_exists($products_image_path)) {
  // if not found, display none
    $products_image_path = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    $products_name = TEXT_NO_IMAGE_AVAILABLE;
    header('HTTP/1.1 404 Not Found');
  }

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_POPUP_IMAGES_ADDITIONAL');
