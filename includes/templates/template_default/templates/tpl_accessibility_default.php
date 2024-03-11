<?php
/**
 * Page Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 Mar 01 New in v2.0.0-rc1 $
 */
?>
<div class="centerColumn" id="accessibility">
<h1 id="accessibilityDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="accessibilityDefaultMainContent" class="content">
<?php
/**
 * require the html_define for the accessibility page
 */
  require($define_page);
?>
</div>

<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
