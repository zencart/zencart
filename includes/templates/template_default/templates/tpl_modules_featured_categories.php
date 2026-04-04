<?php
/**
 * Module Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Jeff Rutt 2024 Aug 14 New in v2.1.0-alpha2 $
 * based on tpl_modules_featured_products
 */
  $zc_show_featured = false;
  include DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_CATEGORIES_MODULE);
?>

<!-- bof: featured categories  -->
<?php if ($zc_show_featured == true) { ?>
<div class="centerBoxWrapper" id="featuredCategories">
<?php
/**
 * require the list_box_content template to display the category
 */
  require $template->get_template_dir('tpl_columnar_display.php',DIR_WS_TEMPLATE, $current_page_base,'common') . '/tpl_columnar_display.php';
?>
</div>
<?php } ?>
<!-- eof: featured categories  -->

