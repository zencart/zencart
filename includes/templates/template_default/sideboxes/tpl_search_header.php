<?php
/**
 * Side Box Template: Searchbox for column header
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
$content = '';
$content .= zen_draw_form('quick_find_header', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get');
$content .= zen_draw_hidden_field('main_page', FILENAME_ADVANCED_SEARCH_RESULT);
$content .= zen_draw_hidden_field('search_in_description', '1') . zen_hide_session_id();

$content .= zen_draw_input_field('keyword', '', 'size="6" maxlength="30" style="width: 100px" placeholder="' . HEADER_SEARCH_DEFAULT_TEXT . '" aria-label="' . HEADER_SEARCH_DEFAULT_TEXT . '" ');

$content .= '&nbsp;';

if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes') {
    $content .= zen_image_submit(BUTTON_IMAGE_SEARCH, HEADER_SEARCH_BUTTON);
} else {
    $content .= '<input type="submit" value="' . HEADER_SEARCH_BUTTON . '" style="width: 60px" />';
}

$content .= '</form>';
