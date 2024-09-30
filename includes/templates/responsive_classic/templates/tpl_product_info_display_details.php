<?php
/**
 * Page Template, displays the default (can be overridden) product-details list
 * for a given product.
 *
 * Loaded automatically by tpl_product_info_display.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 17 New in v2.1.0-beta1 $
 */
$display_product_model = ($flag_show_product_info_model === '1' && $products_model !== '');
$display_product_weight = ($flag_show_product_info_weight === '1' && $products_weight != 0);
$display_product_quantity = ($flag_show_product_info_quantity === '1');
$display_product_manufacturer = ($flag_show_product_info_manufacturer === '1' && !empty($manufacturers_name));
if ($display_product_model || $display_product_weight || $display_product_quantity || $display_product_manufacturer) {
?>
<ul id="productDetailsList">
    <?= (($display_product_model === true) ? '<li>' . TEXT_PRODUCT_MODEL . $products_model . '</li>' : '') . "\n" ?>
    <?= (($display_product_weight === true) ? '<li>' . TEXT_PRODUCT_WEIGHT .  $products_weight . TEXT_PRODUCT_WEIGHT_UNIT . '</li>'  : '') . "\n" ?>
    <?= (($display_product_quantity === true) ? '<li>' . $products_quantity . TEXT_PRODUCT_QUANTITY . '</li>'  : '') . "\n" ?>
    <?= (($display_product_manufacturer === true) ? '<li>' . TEXT_PRODUCT_MANUFACTURER . $manufacturers_name . '</li>' : '') . "\n" ?>
</ul>
<?php
}
