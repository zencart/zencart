<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_languages.php  Modified in v1.6.0 $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';

  $lng_cnt = 0;
  foreach($language_list as $key=>$value) {
    $content .= '<a href="' . zen_href_link($current_page_base, zen_get_all_get_params(array('language', 'currency')) . 'language=' . $value['code'], $request_type) . '">' . zen_image(DIR_WS_LANGUAGES .  $value['directory'] . '/images/' . $value['image'], $value['name']) . '</a>&nbsp;&nbsp;';
    $lng_cnt ++;
    if ($lng_cnt >= MAX_LANGUAGE_FLAGS_COLUMNS) {
      $lng_cnt = 0;
      $content .= '<br />';
    }
  }
$content .= '</div>';
