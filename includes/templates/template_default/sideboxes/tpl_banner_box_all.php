<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_banner_box_all.php 2982 2006-02-07 07:56:41Z birdbrain $
 */
  $content = '';
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';
// select banners_group to be used
  $new_banner_search = zen_build_banners_group(SHOW_BANNERS_GROUP_SET_ALL);

  // secure pages
  switch ($request_type) {
    case ('SSL'):
      $my_banner_filter=" and banners_on_ssl= 1 ";
      break;
    case ('NONSSL'):
      $my_banner_filter='';
      break;
  }

  $sql = "select banners_id from " . TABLE_BANNERS . " where status = 1 " . $new_banner_search . $my_banner_filter . " order by banners_sort_order";
  $banners_all = $db->Execute($sql);


// if no active banner in the specified banner group then the box will not show
  $banner_cnt = 0;
  while (!$banners_all->EOF) {
    $banner_cnt++;
    $content .= zen_display_banner('static', $banners_all->fields['banners_id']);
// add spacing between banners
    if ($banner_cnt < $banners_all->RecordCount()) {
      $content .= '<br /><br />';
    }
    $banners_all->MoveNext();
  }
  $content .= '</div>';
