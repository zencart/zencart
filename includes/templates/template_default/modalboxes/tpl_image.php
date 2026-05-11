<?php
/**
 * Module Template
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 10 Modified in v2.2.0 $
 */
require DIR_WS_MODULES . zen_get_module_directory(FILENAME_MAIN_PRODUCT_IMAGE);

// Ensure we have a valid large image
$main_large_image = !empty($products_image_large) ? $products_image_large : $products_image_medium;
?>

<div id="productMainImage" class="centeredContent back">
    <a href="<?= htmlspecialchars($main_large_image, ENT_QUOTES, 'UTF-8') ?>"
        onclick="openModal('imageModalPrimary'); return false;"
        title="<?= htmlspecialchars(TEXT_CLICK_TO_ENLARGE . ' ' . $products_name, ENT_QUOTES, 'UTF-8') ?>"
        rel="noopener"
        >
        <?= zen_image($products_image_medium, $products_name, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT) ?>
    </a>
</div>
