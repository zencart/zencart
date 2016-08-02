<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=about_us.<br />
 * Displays About Us page.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_about_us_default.php  v1.6.0 $
 */
?>
<div class="centerColumn" id="about_us">
<h1 id="aboutUsHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="aboutUsMainContent" class="content">
<?php
if ($action == 'require') {
  require($define_page);
} else {
  $zco_notifier->notify('NOTIFY_ABOUTUS_TEMPLATE_OUTPUT');
}
?>
</div>

<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</div>
