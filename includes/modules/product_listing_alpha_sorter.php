<?php
/**
 * product_listing_alpha_sorter module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// build alpha sorter dropdown
  if (PRODUCT_LIST_ALPHA_SORTER == 'true') {
    $letters_list = array();
    if (empty($_GET['alpha_filter_id'])) {
      $letters_list[] = array('id' => '0', 'text' => TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES);
    } else {
      $letters_list[] = array('id' => '0', 'text' => TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES_RESET);
    }
    for ($i=65; $i<91; $i++) {
      $letters_list[] = array('id' => sprintf('%02d', $i), 'text' => chr($i) );
    }
    for ($i=48; $i<58; $i++) {
      $letters_list[] = array('id' => sprintf('%02d', $i), 'text' => chr($i) );
    }

    $zco_notifier->notify('NOTIFY_PRODUCT_LISTING_ALPHA_SORTER_SELECTLIST', isset($prefix) ? $prefix : '', $letters_list);

    if (TEXT_PRODUCTS_LISTING_ALPHA_SORTER != '') {
        echo '<label class="inputLabel" for="select-alpha_filter_id">' . TEXT_PRODUCTS_LISTING_ALPHA_SORTER . '</label>';
    } else {
        echo '<label class="inputLabel sr-only" for="select-alpha_filter_id">' . TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES . '</label>';
    }
    echo zen_draw_pull_down_menu('alpha_filter_id', $letters_list, (isset($_GET['alpha_filter_id']) ? $_GET['alpha_filter_id'] : ''), 'onchange="this.form.submit()"');
  }
