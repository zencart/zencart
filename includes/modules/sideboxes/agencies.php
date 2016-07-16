<?php
/**
 * agencies sidebox - displays list of agencies for customer to filter products on
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: agencies.php  Modified in v1.6.0 $
 */

  $agency_query = "select agency_id, agency_name
                          from " . TABLE_AGENCY . "
                          order by agency_name";

  $agency = $db->Execute($agency_query);

  if ($agency->RecordCount()>0) {
    $number_of_rows = $agency->RecordCount()+1;

// Display a list
    $agency_array = array();
    if (!isset($_GET['agency_id']) || $_GET['agency_id'] == '' ) {
      $agency_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $agency_array[] = array('id' => '', 'text' => PULL_DOWN_AGENCIES);
    }

    foreach($agency as $result) {
      $elipsis = (strlen($result['agency_name']) > (int)MAX_DISPLAY_AGENCY_NAME_LEN) ? '..' : '';
      $agency_name = substr($result['agency_name'], 0, (int)MAX_DISPLAY_AGENCY_NAME_LEN) . $elipsis;

      $agency_array[] = array('id' => $result['agency_id'],
                                      'text' => $agency_name);
    }

    require($template->get_template_dir('tpl_agency_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_agency_select.php');

    $title = '<label>' . BOX_HEADING_AGENCY . '</label>';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
