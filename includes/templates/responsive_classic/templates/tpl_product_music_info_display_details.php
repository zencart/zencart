<?php
/**
 * Page Template, displays the product-details list for a music product.
 *
 * Loaded automatically by index.php?main_page=product_music_info.
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
$display_product_music_artist = ($flag_show_product_music_info_artist === '1' && !empty($products_artist_name));
$display_product_music_genre = ($flag_show_product_music_info_genre === '1' && !empty($products_music_genre_name));
if ($display_product_model || $display_product_weight || $display_product_quantity || $display_product_manufacturer || $display_product_music_artist || $display_product_music_genre) {
?>
<ul id="productDetailsList">
    <?= (($display_product_model === true) ? '<li>' . TEXT_PRODUCT_MODEL . $products_model . '</li>' : '') . "\n" ?>
    <?= (($display_product_weight === true) ? '<li>' . TEXT_PRODUCT_WEIGHT .  $products_weight . TEXT_PRODUCT_WEIGHT_UNIT . '</li>'  : '') . "\n" ?>
    <?= (($display_product_quantity === true) ? '<li>' . $products_quantity . TEXT_PRODUCT_QUANTITY . '</li>'  : '') . "\n" ?>
    <?= (($display_product_manufacturer === true) ? '<li>' . TEXT_PRODUCT_MANUFACTURER . $manufacturers_name . '</li>' : '') . "\n" ?>
    <?= (($display_product_music_artist === true) ? '<li>' . TEXT_PRODUCT_ARTIST . $products_artist_name . '</li>' : '') . "\n" ?>
    <?= (($display_product_music_genre === true) ? '<li>' . TEXT_PRODUCT_MUSIC_GENRE . $products_music_genre_name . '</li>' : '') . "\n" ?>
</ul>
<?php
}
