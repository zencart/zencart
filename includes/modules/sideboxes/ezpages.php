<?php
/**
 * ezpages sidebox - used to display links to EZ-Pages content
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ezpages.php 6021 2007-03-17 16:34:19Z ajeh $
 */

  $zco_notifier->notify('NOTIFY_START_EZPAGES_SIDEBOX');

  // test if sidebox should display
  if (EZPAGES_STATUS_SIDEBOX == '1' or (EZPAGES_STATUS_SIDEBOX== '2' and (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])))) {
    if (isset($var_linksList)) {
      unset($var_linksList);
    }
    $page_query = $db->Execute("select * from " . TABLE_EZPAGES . " where status_sidebox = 1 and sidebox_sort_order > 0 order by sidebox_sort_order, pages_title");
    if ($page_query->RecordCount()>0) {
      $title =  BOX_HEADING_EZPAGES;
      $box_id =  'ezpages';
      $rows = 0;
      while (!$page_query->EOF) {
        $rows++;
        $page_query_list_sidebox[$rows]['id'] = $page_query->fields['pages_id'];
        $page_query_list_sidebox[$rows]['name'] = $page_query->fields['pages_title'];
        $page_query_list_sidebox[$rows]['altURL']  = "";
        switch (true) {
          // external link new window or same window
          case ($page_query->fields['alt_url_external'] != ''):
          $page_query_list_sidebox[$rows]['altURL']  = $page_query->fields['alt_url_external'];
          break;
          // internal link new window
          case ($page_query->fields['alt_url'] != '' and $page_query->fields['page_open_new_window'] == '1'):
          $page_query_list_sidebox[$rows]['altURL']  = (substr($page_query->fields['alt_url'],0,4) == 'http') ?
          $page_query->fields['alt_url'] :
          ($page_query->fields['alt_url']=='' ? '' : zen_href_link($page_query->fields['alt_url'], '', ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
          // internal link same window
          case ($page_query->fields['alt_url'] != '' and $page_query->fields['page_open_new_window'] == '0'):
          $page_query_list_sidebox[$rows]['altURL']  = (substr($page_query->fields['alt_url'],0,4) == 'http') ?
          $page_query->fields['alt_url'] :
          ($page_query->fields['alt_url']=='' ? '' : zen_href_link($page_query->fields['alt_url'], '', ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
        }

        // if altURL is specified, use it; otherwise, use EZPage ID to create link
        $page_query_list_sidebox[$rows]['link'] = ($page_query_list_sidebox[$rows]['altURL'] =='') ?
        zen_href_link(FILENAME_EZPAGES, 'id=' . $page_query->fields['pages_id'] . ($page_query->fields['toc_chapter'] > 0 ? '&chapter=' . $page_query->fields['toc_chapter'] : ''), ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL')) :
        $page_query_list_sidebox[$rows]['altURL'];
        $page_query_list_sidebox[$rows]['link'] .= ($page_query->fields['page_open_new_window'] == '1' ? '" target="_blank' : '');
        $page_query->MoveNext();
      }

      $title_link = false;

      $var_linksList = $page_query_list_sidebox;

      require($template->get_template_dir('tpl_ezpages.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_ezpages.php');

      $zco_notifier->notify('NOTIFY_END_EZPAGES_SIDEBOX');
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  } // test for display

  $zco_notifier->notify('NOTIFY_END_EZPAGES_SIDEBOX');
?>