<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Mar 09 Modified in v2.0.0-rc2 $
 */
$content = '';
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
$content .= zen_draw_form('manufacturers_form', zen_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get', 'class="sidebox-select-form"');
$content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT);
$content .= zen_draw_label(PLEASE_SELECT, 'select-manufacturers_id', 'class="sr-only"');
$content .= zen_draw_pull_down_menu('manufacturers_id', $manufacturer_sidebox_array, $default_selection, 'aria-label="' . BOX_HEADING_MANUFACTURERS  . '" size="' . MAX_MANUFACTURERS_LIST . '"' . $required) . zen_hide_session_id();
$content .= zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_GO_ALT);
$content .= '</form>';
$content .= '</div>';
