<?php
/**
 * manufacturers sidebox - displays a list of manufacturers so customer can choose to filter on their products only
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Crystal Jones  Tue Mar 3 10:36:43 2015 -0800 Modified in v1.5.5 $
 */

// test if manufacturers sidebox should show
  $show_manufacturers= true;

// for large lists of manufacturers uncomment this section
/*
  if (($_GET['main_page']==FILENAME_DEFAULT and ($_GET['cPath'] == '' or $_GET['cPath'] == 0)) or  ($request_type == 'SSL')) {
    $show_manufacturers= false;
  } else {
    $show_manufacturers= true;
  }
*/

if ($show_manufacturers) {

// only check products if requested - this may slow down the processing of the manufacturers sidebox
  if (PRODUCTS_MANUFACTURERS_STATUS == '1') {
    $manufacturer_sidebox_query = "select distinct m.manufacturers_id, m.manufacturers_name
                            from " . TABLE_MANUFACTURERS . " m
                            left join " . TABLE_PRODUCTS . " p on m.manufacturers_id = p.manufacturers_id
                            where m.manufacturers_id = p.manufacturers_id and p.products_status= 1
                            order by manufacturers_name";
  } else {
    $manufacturer_sidebox_query = "select m.manufacturers_id, m.manufacturers_name
                            from " . TABLE_MANUFACTURERS . " m
                            order by manufacturers_name";
  }

  $manufacturer_sidebox = $db->Execute($manufacturer_sidebox_query);

  if ($manufacturer_sidebox->RecordCount()>0) {
    $number_of_rows = $manufacturer_sidebox->RecordCount()+1;

// Display a list
    $manufacturer_sidebox_array = array();
    if (!isset($_GET['manufacturers_id']) || $_GET['manufacturers_id'] == '' ) {
      $manufacturer_sidebox_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $manufacturer_sidebox_array[] = array('id' => '', 'text' => PULL_DOWN_MANUFACTURERS);
    }

    while (!$manufacturer_sidebox->EOF) {
      $manufacturer_sidebox_name = zen_output_string(((strlen($manufacturer_sidebox->fields['manufacturers_name']) > (int)MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturer_sidebox->fields['manufacturers_name'], 0, (int)MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturer_sidebox->fields['manufacturers_name']), false, true);
      $manufacturer_sidebox_array[] = array('id' => $manufacturer_sidebox->fields['manufacturers_id'],
                                       'text' => $manufacturer_sidebox_name);

      $manufacturer_sidebox->MoveNext();
    }
      require($template->get_template_dir('tpl_manufacturers_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_manufacturers_select.php');

    $title = BOX_HEADING_MANUFACTURERS;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
} // $show_manufacturers
