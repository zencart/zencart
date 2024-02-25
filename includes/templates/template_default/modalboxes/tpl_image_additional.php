<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 03 New in v2.0.0-beta1 $
 */
// Display the modal and the original image. Contained in the wrapping image-grid div.
?>
<!-- Modal -->
<div id="<?php echo $modal_id; ?>" class="imgmodal">
    <div id="<?php echo $modal_content_id; ?> . '" class="imgmodal-content">
        <span onclick="closeModal('<?php echo $modal_id; ?>')">
        <?php echo zen_image($image['products_image_large'], $image['products_name'], '', '', 'class="centered-image"'); ?>
        <div class="imgmodal-close"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="center"><?php echo $image['products_name']; ?></div>
<!--        <div class="imgLink center">--><?php //echo TEXT_CLOSE_WINDOW_IMAGE; ?><!--</div>-->
        </span>
    </div>
</div>
<div class="back">
    <a id="<?php echo $modal_link_id; ?>" <?php echo $modal_link_attributes; ?>>
        <?php echo $modal_link_img; ?>
        <br>
<!--        <div class="imgLink center">--><?php //echo TEXT_CLICK_TO_ENLARGE; ?><!--</div>-->
    </a>
</div>
