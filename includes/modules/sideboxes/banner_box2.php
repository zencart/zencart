<?php
/**
 * banner_box2 sidebox - second box used to display "square" banners in sideboxes
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Sep 09 Modified in v1.5.7 $
 */

// test if box should display
$show_banner_box2 = true;
if (SHOW_BANNERS_GROUP_SET8 == '') {
  $show_banner_box2 = false;
}

if ($show_banner_box2 == true) {
  $banner_box = array();
  $banner_box[] = TEXT_BANNER_BOX2;
  $banner_box_group= SHOW_BANNERS_GROUP_SET8;

  require($template->get_template_dir('tpl_banner_box2.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_banner_box2.php');

// if no active banner in the specified banner group then the box will not show
// uses banners in the defined group $banner_box_group
  if ($content != '') {
    $title =  BOX_HEADING_BANNER_BOX2;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
}
