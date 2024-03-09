<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 Mar 07 Modified in v2.0.0-rc1 $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">';
      if (!empty($manufacturer_info_sidebox->fields['manufacturers_image']))
  $content .= '<div class="centeredContent">' . zen_image(DIR_WS_IMAGES . $manufacturer_info_sidebox->fields['manufacturers_image'], $manufacturer_info_sidebox->fields['manufacturers_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</div>';
  $content .= '<ul style="margin: 0; padding: 0; list-style-type: none;">' . "\n" ;
      if (!empty($manufacturer_info_sidebox->fields['manufacturers_url']))
  $content .= '<li><a href="' . zen_href_link(FILENAME_REDIRECT, 'action=manufacturer&manufacturers_id=' . $manufacturer_info_sidebox->fields['manufacturers_id']) . '" rel="noopener" target="_blank">' . sprintf(BOX_MANUFACTURER_INFO_HOMEPAGE, $manufacturer_info_sidebox->fields['manufacturers_name']) . '</a></li>' . "\n" ;
  $content .= '<li><a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturer_info_sidebox->fields['manufacturers_id']) . '">' . BOX_MANUFACTURER_INFO_OTHER_PRODUCTS . '</a></li>' . "\n" ;
  $content .= '</ul>' . "\n" ;
  $content .= '</div>';
