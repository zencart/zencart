<?php
/**
 * Page Template
 *
 * Displays page-not-found message and site-map (if configured)
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */
?>
<div class="centerColumn" id="pageNotFound">
<h1 id="pageNotFoundHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if (DEFINE_PAGE_NOT_FOUND_STATUS == '1') { ?>
<div id="pageNotFoundMainContent" class="content">
<?php
/**
 * require the html_define for the page_not_found page
 */
  require($define_page); ?>
</div>
<?php } ?>

<?php require($template->get_template_dir('tpl_modules_show_all_pages.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_show_all_pages.php'); ?>
<br class="clearBoth">
    <div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
