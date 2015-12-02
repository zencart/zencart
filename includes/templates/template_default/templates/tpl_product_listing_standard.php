<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
?>
<div class="centerColumn" id="<?php echo $tplVars['listingBox']['cssElement']; ?>Listing">

<?php  require($template->get_template_dir('/tpl_modules_listing_display_order.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_listing_display_order.php'); ?>

<?php require($template->get_template_dir($tplVars['listingBox']['formatter']['template'], DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/' . $tplVars['listingBox']['formatter']['template']); ?>

<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
