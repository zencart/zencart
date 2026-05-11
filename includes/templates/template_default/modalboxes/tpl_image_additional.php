<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 May 15 Modified in v2.2.0 $
 */
// Display the modal and the original image. Contained in the wrapping image-grid div.
?>

<!-- Modal for additional image -->
<div id="<?= $modal_id ?>" class="imgmodal">
    <div id="<?= $modal_content_id ?>" class="imgmodal-content">
        <div onclick="closeModal('<?= $modal_id ?>')">
            <!-- Large image inside modal -->
            <?= zen_image(
                $image['products_image_large'],
                $image['products_name'],
                '',
                '',
                'class="centered-image"'
            ); ?>
            <div class="imgmodal-close"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="center"><?= htmlspecialchars($image['products_name'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
</div>

<!-- Thumbnail for additional image -->
<div class="back">
    <a id="<?= $modal_link_id ?>"
       href="<?= htmlspecialchars($image['products_image_large'], ENT_QUOTES, 'UTF-8'); ?>"
       onclick="openModal('<?= $modal_id ?>'); return false;"
       title="<?= htmlspecialchars(TEXT_CLICK_TO_ENLARGE . ' ' . $image['products_name'], ENT_QUOTES, 'UTF-8'); ?>">

        <?= $modal_link_img; ?>

    </a>
</div>
