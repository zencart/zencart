<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 28 Modified in v2.1.0-beta1 $
 */
$content = '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">' . "\n";
$content .= '<div class="wrapper">' . "\n" . '<ul class="list-links">' . "\n";
foreach ($bestsellers_list as $next_bestseller) {
    $content .=
        '<li>' .
            '<a href="' . zen_href_link(zen_get_info_page($next_bestseller['id']), 'products_id=' . $next_bestseller['id']) . '">' .
                zen_trunc_string($next_bestseller['name'], BEST_SELLERS_TRUNCATE, BEST_SELLERS_TRUNCATE_MORE) .
            '</a>' .
        '</li>' . "\n";
}
$content .= '</ul>' . "\n";
$content .= '</div>' . "\n";
$content .= '</div>';
