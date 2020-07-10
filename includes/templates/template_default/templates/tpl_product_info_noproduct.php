<?php
/**
 * Page Template
 *
 * Displays simple "product not found" message if the selected product's details cannot be located in the database
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Mon Feb 11 23:15:15 2013 -0500 Modified in v1.5.2 $
 */
?>
<div class="centerColumn" id="productInfoNoProduct">

<div id="productInfoNoProductMainContent" class="content"><?php echo TEXT_PRODUCT_NOT_FOUND; ?></div>

<div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></div>

<br class="clearBoth" />

<?php
//// bof: missing
$show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_MISSING);

while (!$show_display_category->EOF) {
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_FEATURED_PRODUCTS') {
/**
 * display the featured product center box
 */
    require($template->get_template_dir('tpl_modules_featured_products.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_featured_products.php');
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_SPECIALS_PRODUCTS') {
/**
 * display the special product center box
 */
    require($template->get_template_dir('tpl_modules_specials_default.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_specials_default.php');
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS') {
/**
 * display the new product center box
 */
    require($template->get_template_dir('tpl_modules_whats_new.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_whats_new.php');
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_UPCOMING') {
/**
 * display the upcoming product center box
 */
    include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
  }
?>
<?php
  $show_display_category->MoveNext();
} //// eof: missing
?>
</div>