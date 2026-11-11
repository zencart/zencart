<?php
// -----
// Part of the "Product Options Stock Manager" by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2022 Vinos de Frutas Tropicales
//
if (isset($_GET['action']) && isset($_GET['products_filter'])) {
    if (is_pos_product($_GET['products_filter'])) {
        $caution_message = '';
        switch ($_GET['action']) {
            case 'delete_option_name_values_confirm':
                $caution_message = POSM_JS_CAUTION_OPTION_REMOVAL;
                break;
            case 'delete_product_attribute':
                if (isset($_GET['attribute_id'])) {
                    $check = $db->Execute(
                        "SELECT COUNT(*) AS count
                           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa
                                INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                    ON pa.products_attributes_id = " . (int)$_GET['attribute_id'] . "
                          WHERE posa.products_id = pa.products_id
                            AND posa.options_id = pa.options_id
                            AND posa.options_values_id = pa.options_values_id"
                    );
                    if ($check->fields['count'] !== null) {
                        $caution_message = sprintf(POSM_JS_CAUTION_ATTRIBUTE_REMOVAL, $check->fields['count']);
                    }
                }
            default:
                break;
        }
        if ($caution_message !== '') {
?>
<script>
window.addEventListener('DOMContentLoaded', function(){
    alert('<?php echo $caution_message; ?>');
});
</script>
<?php
        }
    }
}
