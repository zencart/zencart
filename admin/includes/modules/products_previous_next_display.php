<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2019 Sep 26 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
// used following load of products_previous_next.php
?>
<!-- bof: products_previous_next_display -->
<!-- heading -->
<div class="row"><strong>
        <?php echo(HEADING_TITLE == '' ? HEADING_TITLE2 : HEADING_TITLE); ?>&nbsp;-&nbsp;<?php echo zen_output_generated_category_path($current_category_id); ?></strong>
    <?php echo '<br />' . TEXT_CATEGORIES_PRODUCTS; ?>
</div>
<!-- heading eof -->
<!-- category/product status -->
<div class="row"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . (zen_get_products_status($products_filter) == '0' ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></div>
<!-- category/product status eof -->
<!-- product count -->
<div class="row"><?php echo($counter > 0 ? (PREV_NEXT_PRODUCT) . ($position + 1 . "/" . $counter) : '&nbsp;'); ?></div>
<!-- product count eof-->
<!-- prev-cat-next navigation -->
<div class="row">
    <?php if ($counter > 0) { ?>
        <div class="col-sm-2 text-center">
            <a href="<?php echo zen_href_link($curr_page, "products_filter=" . $previous . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo BUTTON_PREVIOUS_ALT; ?></a>
        </div>
    <?php } ?>
    <div class="col-sm-4">
        <?php echo zen_draw_form('new_category', $curr_page, '', 'get'); ?>
        <?php echo zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0', '', '', true), $current_category_id, 'onChange="this.form.submit();" class="form-control"'); ?>
        <?php
        if (isset($_GET['products_filter'])) {
            echo zen_draw_hidden_field('products_filter', $_GET['products_filter']);
        }
        echo zen_hide_session_id();
        echo zen_draw_hidden_field('action', 'new_cat');
        ?>
        <?php echo '</form>'; ?>
    </div>
    <?php if ($counter > 0) { ?>
        <div class="col-sm-2 text-center">
            <a href="<?php echo zen_href_link($curr_page, "products_filter=" . $next_item . '&current_category_id=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo BUTTON_NEXT_ALT; ?></a>
        </div>
    <?php } ?>
</div>
<!-- prev-cat-next navigation eof -->
<!-- eof: products_previous_next_display -->
