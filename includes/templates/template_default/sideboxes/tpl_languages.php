<?php

/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Jun 17 Modified in v2.2.0 $
 */
$content = "";
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';

$lng_cnt = 0;
foreach ($lng->get_languages_by_code() as $key => $value) {
    $content .= '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(['language', 'currency']) . 'language=' . $key, $request_type) . '">'
        . zen_image(DIR_WS_LANGUAGES . $value['directory'] . '/images/' . $value['image'], $value['name'])
        . '</a>&nbsp;&nbsp;';
    $lng_cnt++;
    if ($lng_cnt >= MAX_LANGUAGE_FLAGS_COLUMNS) {
        $lng_cnt = 0;
        $content .= '<br>';
    }
}
$content .= '</div>';
