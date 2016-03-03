<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Mon Oct 19 15:20:23 2015 -0400 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// upload image, if submitted
  if (!isset($_GET['read']) || $_GET['read'] !== 'only') {
    $products_image = new upload('products_image');
    $products_image->set_extensions(array('jpg','jpeg','gif','png','webp','flv','webm','ogg'));
    $products_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
    if ($products_image->parse() && $products_image->save($_POST['overwrite'])) {
      $products_image_name = $_POST['img_dir'] . $products_image->filename;
    } else {
      $products_image_name = (isset($_POST['products_previous_image']) ? $_POST['products_previous_image'] : '');
    }
  }

// hook to allow interception of product-image uploading by admin-side observer class
$zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_IMAGE_UPLOADED', $products_image, $products_image_name);
