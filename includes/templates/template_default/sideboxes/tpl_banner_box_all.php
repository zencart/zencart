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

  // if no active banner in the specified banner group then the box will not show

  $content = '';
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent centeredContent">';

  $banners_counter = 0;
  foreach($banners_all as $banner) {
    $content .= '<section class="info-promowrapper b-all">';
    $content .= zen_display_banner('static', $banner['banners_id']);
    $content .= '</section>';

    // add spacing between banners
    $banners_counter++;
    if ($banners_counter < sizeof($banners_all)) {
      $content .= '<br class="banner-padding>';
    }
  }

  $content .= '</div>';
