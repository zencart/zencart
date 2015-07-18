<?php
/**
 * banner_box_all sidebox - used to display "square" banners in sideboxes
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: banner_box_all.php ajeh  Modified in v1.6.0 $
 */

// For building custom banner sideboxes, this SQL may be useful as an example to modify
// INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Group - Side Box banner_box_all', 'SHOW_BANNERS_GROUP_SET_ALL', 'BannersAll', 'The Banner Display Group may only be from one (1) Banner Group for the Banner All sidebox<br /><br />Default Group is BannersAll<br /><br />What Banner Group do you want to use in the Side Box - banner_box_all?<br />Leave blank for none', '19', '72', '', '', now());

// if no active banner in the specified banner group then the box will not show
if (SHOW_BANNERS_GROUP_SET_ALL != '') {
  $banner_box[] = TEXT_BANNER_BOX_ALL;
  $banner_box_group= SHOW_BANNERS_GROUP_SET_ALL;

  // select banners_group to be used
  $new_banner_search = zen_build_banners_group($banner_box_group);

  // test for displaying on secure pages
  switch ($request_type) {
    case ('SSL'):
      $my_banner_filter = " and banners_on_ssl= 1 ";
      break;
    case ('NONSSL'):
      $my_banner_filter = '';
      break;
  }
  $sql = "select banners_id from " . TABLE_BANNERS . " where status = 1 " . $new_banner_search . $my_banner_filter . " order by banners_sort_order";
  $banners_all = $db->Execute($sql);

  require($template->get_template_dir('tpl_banner_box_all.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_banner_box_all.php');

  if ($banners_all !== false) {
    $title =  BOX_HEADING_BANNER_BOX_ALL;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
}
