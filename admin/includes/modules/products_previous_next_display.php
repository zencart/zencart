<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products_previous_next_display.php 18942 2011-06-13 23:26:41Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// used following load of products_previous_next.php
?>
<!-- bof: products_previous_next_display -->
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="3" class="main" align="left"><strong>
          <?php echo (HEADING_TITLE == '' ? HEADING_TITLE2 : HEADING_TITLE); ?>&nbsp;-&nbsp;<?php echo zen_output_generated_category_path($current_category_id); ?></strong>
          <?php echo '<br />' . TEXT_CATEGORIES_PRODUCTS; ?>
        </td>
      </tr>
      <tr>
        <td colspan="3" class="main" align="left"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . (zen_get_products_status($products_filter) == '0' ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></td>
      </tr>
      <tr>
        <td colspan="3" class="main" align="center"><?php echo ($counter > 0 ? (PREV_NEXT_PRODUCT) . ($position+1 . "/" . $counter) : '&nbsp;'); ?></td>
      </tr>
      <tr>
        <?php if ($counter > 0 ) { ?>
          <td align="center" class="main"><a href="<?php echo zen_href_link($curr_page, "products_filter=" . $previous . '&current_category_id=' . $current_category_id); ?>"><?php echo zen_image_button('button_prev.gif', BUTTON_PREVIOUS_ALT); ?></a>&nbsp;&nbsp;</td>
        <?php } ?>
        <td align="left" class="main"><?php echo zen_draw_form('new_category', $curr_page, '', 'get'); ?>&nbsp;&nbsp;<?php echo zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0', '', '', true), $current_category_id, 'onChange="this.form.submit();"'); ?><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); echo zen_hide_session_id(); echo zen_draw_hidden_field('action', 'new_cat'); ?>&nbsp;&nbsp;</form></td>
        <?php if ($counter > 0 ) { ?>
          <td align="center" class="main">&nbsp;&nbsp;<a href="<?php echo zen_href_link($curr_page, "products_filter=" . $next_item . '&current_category_id=' . $current_category_id); ?>"><?php echo zen_image_button('button_next.gif', BUTTON_NEXT_ALT); ?></a></td>
        <?php } ?>
      </tr>
    </table></td>
  </tr>
<!-- eof: products_previous_next_display -->
