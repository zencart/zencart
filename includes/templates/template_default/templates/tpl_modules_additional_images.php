<?php
/**
 * Loaded by product-type template to display additional product images.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2025 Oct 19 Modified in v2.2.0 $
 */

require DIR_WS_MODULES . zen_get_module_directory('additional_images.php');

if (empty($flag_show_product_info_additional_images) || empty($modal_images)) {
    return;
}

?>
<div id="productAdditionalImages" class="image-grid">
<?php
    /*
     * Plugins for older templates, such as colorbox etc, may need to use the old manual-grid display template
     * In such case, set $use_legacy_additional_images_columnar_template=true so that the modal grid isn't generated
     * You will also need to remove the 'class="image-grid"' markup in the <div> above.
     */
    if (!empty($use_legacy_additional_images_columnar_template)) {
        require $template->get_template_dir('tpl_columnar_display.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_columnar_display.php';
        // reset array of modal images, so that the modal template gets skipped
        $modal_images = [];
    }

    $i = 0; // starts at zero here, and is immediately incremented in the loop because we want the modal IDs to start at '1'.
    foreach ($modal_images as $image) {
        $i++;

        // Generate modal variables for each image
        $modal_id = 'imageModal' . $i;
        $modal_content_id = 'modalContent' . $i;
        $modal_link_id = 'modalLink' . $i;
        $modal_link_js = 'openModal(\'' . $modal_id . '\')';
        $modal_link_attributes = 'href="#" onclick="' . $modal_link_js . '; return false;"';
        $modal_link_img = zen_image($image['base_image'], $image['products_name'], MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT);

        // Call the modal template to render the image and its modal
        require $template->get_template_dir('tpl_image_additional.php', DIR_WS_TEMPLATE, $current_page_base, 'modalboxes') . '/tpl_image_additional.php';
    }
?>
</div>
