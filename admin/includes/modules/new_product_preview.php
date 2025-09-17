<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
// upload image, if submitted
if (!isset($_GET['read']) || $_GET['read'] !== 'only') {

    $img_dir = $_POST['img_dir'] ?? '';
    $allowed_ext = ['jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'];

    // Primary product image
    $products_image = new upload('products_image');
    $products_image->set_extensions($allowed_ext);
    $products_image->set_destination(DIR_FS_CATALOG_IMAGES . $img_dir);
    if ($products_image->parse() && $products_image->save($_POST['overwrite'] ?? false)) {
        $products_image_name = $img_dir . $products_image->filename;
    } else {
        $products_image_name = $_POST['products_previous_image'] ?? '';
    }

    // Process additional images if any
    $additional_images_names = [];
    if (!empty($_FILES['additional_images']['name'][0])) {
        for ($i = 0, $j = count($_FILES['additional_images']['name']); $i < $j; $i++) {
            if ($_FILES['additional_images']['error'][$i] === UPLOAD_ERR_OK && !empty($_FILES['additional_images']['name'][$i])) {
                $field_name = 'additional_image_' . $i;
                $_FILES[$field_name] = [
                    'name' => $_FILES['additional_images']['name'][$i],
                    'type' => $_FILES['additional_images']['type'][$i],
                    'tmp_name' => $_FILES['additional_images']['tmp_name'][$i],
                    'error' => $_FILES['additional_images']['error'][$i],
                    'size' => $_FILES['additional_images']['size'][$i],
                ];
                $upload = new upload($field_name);
                $upload->set_extensions($allowed_ext);
                $upload->set_destination(DIR_FS_CATALOG_IMAGES . $img_dir);
                if ($upload->parse() && $upload->save($_POST['overwrite'] ?? false)) {
                    $additional_images_names[] = $img_dir . $upload->filename;
                }
                unset($_FILES[$field_name]);
            }
        }
    }
}

// hook to allow interception of product-image uploading by admin-side observer class
$zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_IMAGE_UPLOADED', $products_image, $products_image_name);
