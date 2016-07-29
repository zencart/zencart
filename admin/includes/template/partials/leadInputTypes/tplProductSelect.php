<?php
/**
 * Admin Lead Template  - productSelect partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>

<div class="form-group">
    <?php require('includes/template/partials/' . $tplVars['leadDefinition']['inputLabelTemplate']); ?>
    <?php echo zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0'), '', 'id="current_category_id" class="input-group col-sm-6"') ?>
    <div class="input-group col-sm-offset-4 col-sm-6" id="products_from_categories"></div>

</div>


<script>
    $(function() {
        var categoryId = $('#current_category_id').val();
        getProductsFromCategoriesByAjax(categoryId)

    });
    $('#current_category_id').on('change', function () {
        var categoryId = $('#current_category_id').val();
        getProductsFromCategoriesByAjax(categoryId)
    });

     function getProductsFromCategoriesByAjax(categoryId)
     {
         zcJS.ajax({
             url: "<?php echo zen_admin_href_link($_GET['cmd'], 'action=productsFromCategory'); ?>",
             data: {id: categoryId}
         }).done(function( response ) {
             if (response.html)
             {
                 $('#products_from_categories').html(response.html);
             }
         });
     }

</script>
