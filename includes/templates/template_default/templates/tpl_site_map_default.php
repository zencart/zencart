<?php
/**
 * Page Template
 *
 * Loaded by index.php?main_page=site_map
 * Displays site-map and some hard-coded navigation components
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Mar 03 Modified in v2.0.0-rc1 $
 */
?>
<div class="centerColumn" id="siteMap">

<h1 id="siteMapHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if (DEFINE_SITE_MAP_STATUS >= '1' and DEFINE_SITE_MAP_STATUS <= '2') { ?>
<div id="siteMapMainContent" class="content">
<?php
/**
 * require the html_define for the site_map page
 */
  require($define_page);
?>
</div>
<?php } ?>

<?php require($template->get_template_dir('tpl_modules_show_all_pages.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_show_all_pages.php'); ?>
<br class="clearBoth">
<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
