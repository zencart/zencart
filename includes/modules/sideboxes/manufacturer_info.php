<?php
/**
 * manufacturer_info sidebox - displays extra info about the selected product's manufacturer details (if defined in Admin->Catalog->Manufacturers)
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: manufacturer_info.php 2718 2005-12-28 06:42:39Z drbyte $
 */

  if (isset($_GET['products_id'])) {
    $manufacturer_info_sidebox_query = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image,
                                  mi.manufacturers_url
                           from " . TABLE_MANUFACTURERS . " m
                           left join " . TABLE_MANUFACTURERS_INFO . " mi
                           on (m.manufacturers_id = mi.manufacturers_id
                           and mi.languages_id = '" . (int)$_SESSION['languages_id'] . "'), " . TABLE_PRODUCTS . " p
                           where p.products_id = '" . (int)$_GET['products_id'] . "'
                           and p.manufacturers_id = m.manufacturers_id";

    $manufacturer_info_sidebox = $db->Execute($manufacturer_info_sidebox_query);

    if ($manufacturer_info_sidebox->RecordCount() > 0) {

      require($template->get_template_dir('tpl_manufacturer_info.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_manufacturer_info.php');
      $title =  BOX_HEADING_MANUFACTURER_INFO;
      $title_link = false;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>