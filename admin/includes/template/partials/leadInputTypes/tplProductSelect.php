<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 30/05/16
 * Time: 21:50
 */
?>
<div class="form-group">
    <?php require('includes/template/partials/' . $tplVars['leadDefinition']['inputLabelTemplate']); ?>
    <div>
    <?php echo zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0'), '', 'id="current_category_id"') ?>
    <span id="products_from_categories"></span>
    </div>
</div>


<script>
    $('#current_category_id').on('change', function () {

    });

</script>
