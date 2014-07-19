<?php
/**
 * banner_box_all sidebox - used to display "square" banners in sideboxes
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: banner_box_all.php 3133 2006-03-07 23:39:02Z ajeh $
 */

// INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Group - Side Box banner_box_all', 'SHOW_BANNERS_GROUP_SET_ALL', 'BannersAll', 'The Banner Display Group may only be from one (1) Banner Group for the Banner All sidebox<br /><br />Default Group is BannersAll<br /><br />What Banner Group do you want to use in the Side Box - banner_box_all?<br />Leave blank for none', '19', '72', '', '', now());
// ALTER TABLE `banners` ADD `banners_sort_order` INT( 11 ) DEFAULT '0' NOT NULL;

// test if box should display
  $show_banner_box_all = true;
  if (SHOW_BANNERS_GROUP_SET_ALL == '') {
    $show_banner_box_all = false;
  }

  if ($show_banner_box_all == true) {
    $banner_box[] = TEXT_BANNER_BOX_ALL;
    $banner_box_group= SHOW_BANNERS_GROUP_SET_ALL;

    require($template->get_template_dir('tpl_banner_box_all.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_banner_box_all.php');

// if no active banner in the specified banner group then the box will not show
// uses banners in the defined group $banner_box_group
    if ($banners_all->RecordCount() > 0) {

      $title =  BOX_HEADING_BANNER_BOX_ALL;
      $title_link = false;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>