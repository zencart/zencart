<?php
/**
 * jscript_main.php for the index page.
 *
 * Hides the disp_order dropdown if no products were found for the
 * alpha-filter.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2026 Feb 19 Modified in v2.2.0-alpha $
 */
if ($index_listing_has_products === true) {
    return;
}
?>
<script id="hide-disp-order">
    jQuery(document).ready(function(){
        jQuery('form[name="sorter_form"], #listingDisplayOrderSorter').hide();
    });
</script>
