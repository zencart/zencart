<?php

/**
 * also_purchased_products.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (isset($_GET['products_id']) && SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS > 0 && MIN_DISPLAY_ALSO_PURCHASED > 0) {
    $also_purchased_products = $db->ExecuteRandomMulti(sprintf(SQL_ALSO_PURCHASED, (int)$_GET['products_id'], (int)$_GET['products_id']), (int)MAX_DISPLAY_ALSO_PURCHASED);

    $num_products_ordered = $also_purchased_products->RecordCount();

    $row = 0;
    $col = 0;
    $list_box_contents = [];
    $title = '';

    // show only when 1 or more and equal to or greater than minimum set in admin
    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED && $num_products_ordered > 0) {
        if ($num_products_ordered < SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS) {
            $col_width = floor(100 / $num_products_ordered);
        } else {
            $col_width = floor(100 / SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS);
        }

        while (!$also_purchased_products->EOF) {
            $product_info = new Product((int)$also_purchased_products->fields['products_id']);
            $data = array_merge($also_purchased_products->fields, $product_info->getDataForLanguage());

            $list_box_contents[$row][$col] = [
                'params' => 'class="centerBoxContentsAlsoPurch"' . ' ' . 'style="width:' . $col_width . '%;"',
                'text' => ((empty($data['products_image']) && (int)PRODUCTS_IMAGE_NO_IMAGE_STATUS === 0) ? ''
                        : '<a href="' . zen_href_link(zen_get_info_page($data['products_id']), 'products_id=' . $data['products_id']) . '">'
                        . zen_image(DIR_WS_IMAGES . $data['products_image'], $data['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                        . '</a><br>')
                    . '<a href="' . zen_href_link(zen_get_info_page($data['products_id']), 'products_id=' . $data['products_id']) . '">' . $data['products_name'] . '</a>',
            ];

            $col++;
            if ($col > (SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS - 1)) {
                $col = 0;
                $row++;
            }
            $also_purchased_products->MoveNextRandom();
        }
    }
    if ($also_purchased_products->RecordCount() > 0 && $also_purchased_products->RecordCount() >= MIN_DISPLAY_ALSO_PURCHASED) {
        $title = '<h2 class="centerBoxHeading">' . TEXT_ALSO_PURCHASED_PRODUCTS . '</h2>';
        $zc_show_also_purchased = true;
    }
}
