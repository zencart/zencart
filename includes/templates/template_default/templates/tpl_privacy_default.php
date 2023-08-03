<?php
/**
 * Page Template
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
?>
<div class="centerColumn" id="privacy">
<h1 id="privacyDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if (DEFINE_PRIVACY_STATUS >= 1 and DEFINE_PRIVACY_STATUS <= 2) { ?>
<div id="privacyDefaultMainContent" class="content">
<?php
/**
 * require the html_define for the privacy page
 */
  require($define_page);
?>
</div>
<?php } ?>

<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>