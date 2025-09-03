<?php
/**
 * Side Box Template: Searchbox
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 25 Modified in v2.1.0-alpha1 $
 */
$content = '';
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
$content .= zen_draw_form('quick_find', zen_href_link(FILENAME_SEARCH_RESULT, '', $request_type, false), 'get');
$content .= zen_draw_hidden_field('main_page', FILENAME_SEARCH_RESULT);
$content .= zen_draw_hidden_field('search_in_description', '1') . zen_hide_session_id();

$content .= zen_draw_input_field('keyword', $_GET['keyword'] ?? '', 'size="18" maxlength="100" style="width: ' . ((int)$column_width - 30) . 'px" placeholder="' . SEARCH_DEFAULT_TEXT . '"  aria-label="' . SEARCH_DEFAULT_TEXT . '"');
$content .= '<br>';

if (strtolower(IMAGE_USE_CSS_BUTTONS) === 'yes' || strtolower(IMAGE_USE_CSS_BUTTONS) === 'found') {
    $content .= zen_image_submit(BUTTON_IMAGE_SEARCH, HEADER_SEARCH_BUTTON);
} else {
    $content .= '<input type="submit" value="' . HEADER_SEARCH_BUTTON . '" style="width: 55px">';
}

$content .= '<br>';
$content .= '<a href="' . zen_href_link(FILENAME_SEARCH) . '">' . BOX_SEARCH_ADVANCED_SEARCH . '</a>';

$content .= "</form>";
$content .= '</div>';
