<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_category_icon_display.php 6216 2007-04-17 06:09:57Z ajeh $
 */
  require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORY_ICON_DISPLAY));

?>
<div align="<?php echo $align; ?>" id="categoryIcon" class="categoryIcon">
  <a href="<?php echo zen_href_link(FILENAME_DEFAULT, 'cPath=' . $_GET['cPath'], 'NONSSL'); ?>">
    <?php echo $category_icon_display_image; ?>
    <span itemprop="category" content="<?php echo $category_icon_display_name; ?>"><?php echo $category_icon_display_name; ?></span>
  </a>
</div>