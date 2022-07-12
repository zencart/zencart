<?php
/**
 * languages sidebox - allows customer to select from available languages installed on your site
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

// test if box should display
  $show_languages= false;

  // don't display on checkout page:
  if (substr($current_page, 0, 8) != 'checkout') {
    $show_languages= true;
  }

  if ($show_languages == true) {
    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      $lng = new language;
    }

    require($template->get_template_dir('tpl_languages.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_languages.php');
    $title =  BOX_HEADING_LANGUAGES;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
