<?php
/**
 * banner_box sidebox - used to display "square" banners in sideboxes
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: banner_box.php 3133 2006-03-07 23:39:02Z ajeh $
 */

// test if box should display
  $show_banner_box = true;
  if (SHOW_BANNERS_GROUP_SET7 == '') {
    $show_banner_box = false;
  }

  if ($show_banner_box == true) {
    $banner_box[] = TEXT_BANNER_BOX;
    $banner_box_group= SHOW_BANNERS_GROUP_SET7;

    require($template->get_template_dir('tpl_banner_box.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_banner_box.php');

// if no active banner in the specified banner group then the box will not show
// uses banners in the defined group $banner_box_group
    if ($banner->RecordCount() > 0) {

      $title =  BOX_HEADING_BANNER_BOX;
      $title_link = false;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>