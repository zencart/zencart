<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Fri Jan 8 00:33:36 2016 -0500 Modified in v1.5.5 $
 */
  require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORY_ICON_DISPLAY));

?>

<div id="categoryIcon" class="categoryIcon <?php echo 'align' . base::camelize($align, true); ?>"><?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . $_GET['cPath'], 'NONSSL') . '">' . $category_icon_display_image . $category_icon_display_name .  '</a>'; ?></div>
