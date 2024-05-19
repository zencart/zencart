<?php

/**
 * listing_display_order module to display sorter dropdown
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 Modified in v2.0.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (empty($_GET['main_page'])) {
    $_GET['main_page'] = 'index';
}
if (empty($disp_order_default)) {
    if (PRODUCT_LISTING_DEFAULT_SORT_ORDER === '') {
        // blank means products_sort_order
        $disp_order_default = 8; // see 'case 8' below
    } elseif (strlen(PRODUCT_LISTING_DEFAULT_SORT_ORDER) > 1) {
        // if it is set to the legacy multi-column selector, ie "2a", ignore it and treat it as though blank
        $disp_order_default = 8;
    } else {
        $disp_order_default = (int)PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }
}
if (!isset($_GET['disp_order'])) {
    $_GET['disp_order'] = $disp_order_default;
    $disp_order = $disp_order_default;
} else {
    $disp_order = (int)$_GET['disp_order'];
}

switch ((int)$_GET['disp_order']) {
    case 1:
        $order_by = " ORDER BY pd.products_name";
        break;
    case 2:
        $order_by = " ORDER BY pd.products_name DESC";
        break;
    case 3:
        $order_by = " ORDER BY p.products_price_sorter, pd.products_name";
        break;
    case 4:
        $order_by = " ORDER BY p.products_price_sorter DESC, pd.products_name";
        break;
    case 5:
        $order_by = " ORDER BY p.products_model";
        break;
    case 6:
        $order_by = " ORDER BY p.products_date_added DESC, pd.products_name";
        break;
    case 7:
        $order_by = " ORDER BY p.products_date_added, pd.products_name";
        break;
    case 8:
        $order_by = $default_sort_order ??  " ORDER BY p.products_sort_order, pd.products_name ";
        break;
    case 0:
        // reset
        $_GET['disp_order'] = $disp_order_default;
        $disp_order = $disp_order_default;
        // no break here.
    default:
        $order_by = " ORDER BY p.products_sort_order, pd.products_name";
        break;
}
