<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Oct 07 New in v2.2.0 $
 */
if (($_GET['action'] ?? '') !== 'delete_product') {
    return;
}
?>
<script id="cpl-php">
$(function() {
    if ($('input[name="product_categories[]"]:checked').length === 0) {
        $('form[name="delete_products"]').find('button[type="submit"]').hide();
    }
    $('input[name="product_categories[]"]').on('change', function() {
        if ($('input[name="product_categories[]"]:checked').length === 0) {
            $('form[name="delete_products"]').find('button[type="submit"]').hide();
        } else {
            $('form[name="delete_products"]').find('button[type="submit"]').show();
        }
    });
});
</script>
