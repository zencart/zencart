<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sidebox_delete_product.php 3358 2006-04-03 04:33:32Z ajeh $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</b>');

        $contents = array('form' => zen_draw_form('products', $type_admin_handler, 'action=delete_product_confirm&product_type=' . $product_type . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br /><b>' . $pInfo->products_name . ' ID#' . $pInfo->products_id . '</b>');

        // zen_get_category_name(zen_get_parent_category_id($pInfo->products_id), (int)$_SESSION['languages_id'])

        $product_categories_string = '';
        $product_categories = zen_generate_category_path($pInfo->products_id, 'product');

        if (sizeof($product_categories) > 1) {
          $contents[] = array('text' => '<br /><b><span class="alert">' . TEXT_MASTER_CATEGORIES_ID . '</span>' . '</b>');
        }
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
          $category_path = '';
          for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
            $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $category_path = substr($category_path, 0, -16);
          if (sizeof($product_categories) > 1 && zen_get_parent_category_id($pInfo->products_id) == $product_categories[$i][sizeof($product_categories[$i])-1]['id']) {
            $product_categories_string .= '<strong><span class="alert">' . zen_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '</strong></span><br />';
          } else {
            $product_categories_string .= zen_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br />';
          }
        }
        $product_categories_string = substr($product_categories_string, 0, -4);

        $contents[] = array('text' => '<br />' . $product_categories_string);
        $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
?>