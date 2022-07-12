<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 11 New in v1.5.8-alpha $
 */

$content = "";
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent"><ul class="list-links">' . "\n";
foreach ($brands_array as $brand) {
    $row_class = 'brand-name';
    if (!empty($_GET['manufacturers_id']) && $_GET['manufacturers_id'] == $brand['id']) {
        $row_class .= ' current';
    }

    $content .= '<li><a class="' . $row_class . '" href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . (int)$brand['id']) . '">';

    $content .= $brand['text'];

    $content .= '</a>';
    $content .= '</li>' . "\n";
}

$content .= '</ul></div>';

