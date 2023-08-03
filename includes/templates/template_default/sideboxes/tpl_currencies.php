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
$content .= zen_draw_form('currencies_form', zen_href_link(basename(preg_replace('/.php/','', $PHP_SELF)), '', $request_type, false), 'get');
$content .= zen_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency']) . $hidden_get_variables . zen_hide_session_id();
$content .= zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_GO_ALT);
$content .= '</form>';
$content .= '</div>';
