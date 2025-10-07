<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
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
