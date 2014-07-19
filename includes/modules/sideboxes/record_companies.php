<?php
/**
 * record_companies sidebox - displays list of record companies for customer to filter products on
 *
 * @package templateSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: record_companies.php 18922 2011-06-13 03:23:35Z drbyte $
 */

  $record_company_query = "select record_company_id, record_company_name
                          from " . TABLE_RECORD_COMPANY . "
                          order by record_company_name";

  $record_company = $db->Execute($record_company_query);

  if ($record_company->RecordCount()>0) {
    $number_of_rows = $record_company->RecordCount()+1;

// Display a list
    $record_company_array = array();
    if (!isset($_GET['record_company_id']) || $_GET['record_company_id'] == '' ) {
      $record_company_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $record_company_array[] = array('id' => '', 'text' => PULL_DOWN_RECORD_COMPANIES);
    }

    while (!$record_company->EOF) {
      $record_company_name = ((strlen($record_company->fields['record_company_name']) > (int)MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) ? substr($record_company->fields['record_company_name'], 0, (int)MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) . '..' : $record_company->fields['record_company_name']);
      $record_company_array[] = array('id' => $record_company->fields['record_company_id'],
                                       'text' => $record_company_name);

      $record_company->MoveNext();
    }
      require($template->get_template_dir('tpl_record_company_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_record_company_select.php');

    $title = '<label>' . BOX_HEADING_RECORD_COMPANY . '</label>';
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
