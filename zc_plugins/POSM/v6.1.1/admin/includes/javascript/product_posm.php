<?php
// -----
// Part of the "Product Options Stock" plugin
//
// Last Updated:  POSM v6.1.1
//
if (($_GET['action'] ?? '') !== 'new_product' || !(function_exists('is_pos_product') && isset($_GET['pID']) && is_pos_product($_GET['pID']))) {
    return;
}

// -----
// Transform the product's quantity elements to a link to the Products' Options' Stock Manager page.
//
$posm_quantity_html =
    '<div class="form-group">' .
        zen_draw_label(POSM_TEXT_PRODUCTS_QTY_CLICK, 'products_price', 'class="col-sm-3 control-label"') .
        '<div class="col-sm-9 col-md-6">' .
            '<a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, 'pID=' . $_GET['pID']) . '" class="btn btn-primary" role="button">' .
                POSM_BUTTON_MANAGE_STOCK .
            '</a>' .
            zen_draw_hidden_field('products_quantity', zen_get_products_stock($_GET['pID'])) .
        '</div>' .
    '</div>';
?>
<script>
$(function(){
    $('input[name="products_quantity"]').closest('div.form-group').replaceWith('<?= $posm_quantity_html ?>');
});
</script>
