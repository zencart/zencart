<?php
/**
 * product_listing_alpha_sorter module
 *
 * @package modules
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product_listing_alpha_sorter.php 4330 2006-08-31 17:10:26Z ajeh $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// build alpha sorter dropdown
  if (PRODUCT_LIST_ALPHA_SORTER == 'true') {
    if ((int)$_GET['alpha_filter_id'] == 0) {
      $prefix = TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES;
    } else {
      $prefix = TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES_RESET;
    }
    $prefix .= ':;';
    $alpha_sort_list = explode(';', $prefix . trim(PRODUCT_LIST_ALPHA_SORTER_LIST));
    for ($j=0, $n=sizeof($alpha_sort_list); $j<$n; $j++) {
      $letters_list[] = array('id' => $j, 'text' => substr($alpha_sort_list[$j], 0, strpos($alpha_sort_list[$j], ':')));
    }

    if (TEXT_PRODUCTS_LISTING_ALPHA_SORTER != '') {
      echo '<label class="inputLabel">' . TEXT_PRODUCTS_LISTING_ALPHA_SORTER . '</label>';
    }
    echo zen_draw_pull_down_menu('alpha_filter_id', $letters_list, (isset($_GET['alpha_filter_id']) ? (int)$_GET['alpha_filter_id'] : 0), 'onchange="this.form.submit()"');
  }
