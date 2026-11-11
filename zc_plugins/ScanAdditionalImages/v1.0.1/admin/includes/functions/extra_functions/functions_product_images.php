<?php
/**
 * Compatibility bridge: the scanner uses zen_get_image_lookup_filename_components() from
 * includes/functions/functions_product_images.php (present since Zen Cart v2.2.0), but that
 * file is only loaded by the storefront on released versions; the admin function loader
 * does not include it. Load it here when the admin has not already done so.
 *
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
if (!function_exists('zen_get_image_lookup_filename_components')) {
    require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_product_images.php';
}
