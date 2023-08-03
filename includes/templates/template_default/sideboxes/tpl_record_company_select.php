<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 05 Modified in v1.5.8-alpha $
 */
$content = '';
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
$content .= zen_draw_form('record_company_form', zen_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get', 'class="sidebox-select-form"');
$content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT) . zen_hide_session_id() . zen_draw_hidden_field('typefilter', 'record_company');
$content .= zen_draw_label(PLEASE_SELECT, 'select-record_company_id', 'class="sr-only"');
$content .= zen_draw_pull_down_menu('record_company_id', $record_company_array, $default_selection, 'size="' . MAX_RECORD_COMPANY_LIST . '"' . $required);
$content .= zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_GO_ALT);
$content .= '</form>';
$content .= '</div>';
