<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2024 Oct 15 Modified in v2.1.0 $
 */
$content = "";

$content .= '<div id="' . str_replace('_' , '-' , $box_id . 'Content') . '" class="sideBoxContent"><ul class="list-links">' . "\n";
for ($i=0, $j=sizeof($box_categories_array); $i<$j; $i++) {
    switch(true) {
        // to make a specific category stand out define a new class in the stylesheet example: A.category-holiday
        // uncomment the select below and set the cPath=3 to the cPath= your_categories_id
        // many variations of this can be done
        //      case ($box_categories_array[$i]['path'] == 'cPath=3'):
        //        $new_style = 'category-holiday';
        //        break;
        case ($box_categories_array[$i]['top'] === 'true'):
            $new_style = 'category-top';
        break;

        case ($box_categories_array[$i]['has_sub_cat']):
            $new_style = 'category-subs';
        break;

        default:
            $new_style = 'category-products';
    }
    if (zen_get_product_types_to_category($box_categories_array[$i]['path']) === 3 || ($box_categories_array[$i]['top'] != 'true' && SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS != 1)) {
        // skip if this is for the document box (==3)
    } else {
        $content .= '<li><a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, $box_categories_array[$i]['path']) . '">';

        if ($box_categories_array[$i]['current']) {
            if ($box_categories_array[$i]['has_sub_cat']) {
                $content .= '<span class="category-subs-parent">' . $box_categories_array[$i]['name'] . '</span>';
            } else {
                $content .= '<span class="category-subs-selected">' . $box_categories_array[$i]['name'] . '</span>';
            }
        } else {
            $content .= $box_categories_array[$i]['name'];
        }

        if ($box_categories_array[$i]['has_sub_cat']) {
            $content .= CATEGORIES_SEPARATOR;
        }


        if (SHOW_COUNTS == 'true') {
            if ((CATEGORIES_COUNT_ZERO == '1' && $box_categories_array[$i]['count'] === 0) || $box_categories_array[$i]['count'] >= 1) {
                $content .= '<span class="forward cat-count">' . CATEGORIES_COUNT_PREFIX . $box_categories_array[$i]['count'] . CATEGORIES_COUNT_SUFFIX . '</span>';
            }
        }
        $content .= '</a>';
        $content .= '</li>' . "\n";
    }
}

if (SHOW_CATEGORIES_BOX_SPECIALS === 'true' || SHOW_CATEGORIES_BOX_PRODUCTS_NEW === 'true' || SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS === 'true' || SHOW_CATEGORIES_BOX_PRODUCTS_ALL === 'true' || SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES === 'true') {
    // display a separator between categories and links
    if (SHOW_CATEGORIES_SEPARATOR_LINK === '1') {
        $content .= '' . "\n";
    }
    if (SHOW_CATEGORIES_BOX_SPECIALS === 'true') {
        $show_this = $db->Execute("SELECT products_id FROM " . TABLE_SPECIALS . " WHERE status= 1 limit 1");
        if ($show_this->EOF) {
            $content .= '<li><a class="category-links" href="' . zen_href_link(FILENAME_SPECIALS) . '">' . CATEGORIES_BOX_HEADING_SPECIALS . '</a></li>' . "\n";
        }
    }
    if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW === 'true') {
        // display limits
        $display_limit = zen_get_new_date_range();

        $show_this = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS . " p WHERE products_status = 1 " . $display_limit . " limit 1");
        if (!$show_this->EOF) {
            $content .= '<li><a class="category-links" href="' . zen_href_link(FILENAME_PRODUCTS_NEW) . '">' . CATEGORIES_BOX_HEADING_WHATS_NEW . '</a></li>' . "\n";
        }
    }
    if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS === 'true') {
        $show_this = $db->Execute("SELECT products_id FROM " . TABLE_FEATURED . " WHERE status= 1 limit 1");
        if (!$show_this->EOF) {
            $content .= '<li><a class="category-links" href="' . zen_href_link(FILENAME_FEATURED_PRODUCTS) . '">' . CATEGORIES_BOX_HEADING_FEATURED_PRODUCTS . '</a></li>' . "\n";
        }
    }
    if (SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES === 'true') {
        $show_this = $db->Execute("SELECT categories_id FROM " . TABLE_FEATURED_CATEGORIES . " WHERE status= 1 limit 1");
        if (!$show_this->EOF) {
            $content .= '<li><a class="category-links" href="' . zen_href_link(FILENAME_FEATURED_CATEGORIES) . '">' . CATEGORIES_BOX_HEADING_FEATURED_CATEGORIES . '</a></li>' . "\n";
        }
    }
    if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL === 'true') {
        $content .= '<li><a class="category-links" href="' . zen_href_link(FILENAME_PRODUCTS_ALL) . '">' . CATEGORIES_BOX_HEADING_PRODUCTS_ALL . '</a></li>' . "\n";
    }
}
$content .= '</ul></div>';

