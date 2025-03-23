<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 03 New in v2.0.0-beta1 $
 */
// Display the modal and the original image. Contained in the wrapping image-grid div.
?>
<!-- Modal -->
<div id="<?= $modal_id ?>" class="imgmodal">
    <div id="<?= $modal_content_id ?>" class="imgmodal-content">
        <span onclick="closeModal('<?= $modal_id ?>')">
        <?= zen_image($image['products_image_large'], $image['products_name'], '', '', 'class="centered-image"') ?>
        <div class="imgmodal-close"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="center"><?= $image['products_name'] ?></div>
            <?php /*
        <div class="imgLink center"><?= TEXT_CLOSE_WINDOW_IMAGE ?></div>
            */ ?>
        </span>
    </div>
</div>
<div class="back">
    <a id="<?= $modal_link_id ?>" <?= $modal_link_attributes ?>><?= $modal_link_img ?>
        <?php /*
    <div class="imgLink center"><?= TEXT_CLICK_TO_ENLARGE ?></div>
        */ ?>
    </a>
</div>
