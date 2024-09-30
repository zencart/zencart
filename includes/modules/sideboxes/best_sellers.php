<?php
/**
* best_sellers sidebox - displays selected number of (usually top ten) best selling products
*
 * @copyright Copyright 2003-2024 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: lat9 2024 Sep 28 Modified in v2.1.0-beta1 $
*/
if (isset($current_category_id) && ($current_category_id > 0)) {
    $best_sellers_query =
        "SELECT DISTINCT p.products_id, pd.*, p.*
           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                    . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c
          WHERE p.products_status = 1
            AND p.products_ordered > 0
            AND p.products_id = pd.products_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
            AND p.products_id = p2c.products_id
            AND p2c.categories_id = c.categories_id
            AND " . (int)$current_category_id . " IN (c.categories_id, c.parent_id)
          ORDER BY p.products_ordered desc, pd.products_name";

} else {
    $best_sellers_query =
        "SELECT DISTINCT p.products_id, pd.*, p.*
           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
          WHERE p.products_status = 1
            AND p.products_ordered > 0
            AND p.products_id = pd.products_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
          ORDER BY p.products_ordered desc, pd.products_name";
}

$limit = (trim(MAX_DISPLAY_BESTSELLERS) === '') ? '' : (' LIMIT ' . (int)MAX_DISPLAY_BESTSELLERS);
$best_sellers_query .= $limit;
$best_sellers = $db->Execute($best_sellers_query);
if ($best_sellers->RecordCount() >= MIN_DISPLAY_BESTSELLERS) {
    $rows = 0;
    $bestsellers_list = [];
    foreach ($best_sellers as $bestseller) {
        $product_info = (new Product((int)$bestseller['products_id']))->withDefaultLanguage();
        $bestseller = array_merge($bestseller, $product_info->getData());
        $best_products_id = $bestseller['products_id'];
        $rows++;
        $bestsellers_list[$rows] = [
            'id' => $best_products_id,
            'name'  => $bestseller['products_name'],
            'image' => zen_image(DIR_WS_IMAGES . $bestseller['products_image'], $bestseller['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT),
            'href' => zen_href_link(zen_get_info_page($best_products_id), 'cPath=' . zen_get_generated_category_path_rev($bestseller['master_categories_id']) . '&products_id=' . $best_products_id),
            'price' => zen_get_products_display_price((int)$bestseller['products_id']),
            'model'  => $bestseller['products_model'],
            'description'  => $bestseller['products_description'],
        ];
    }

    $title =  BOX_HEADING_BESTSELLERS;
    $box_id =  'bestsellers';
    $title_link = false;
    require $template->get_template_dir('tpl_best_sellers.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_best_sellers.php';

    $title =  BOX_HEADING_BESTSELLERS;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
