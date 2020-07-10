<?php
/**
 * ezpages sidebox - used to display links to EZ-Pages content
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */

  $zco_notifier->notify('NOTIFY_START_EZPAGES_SIDEBOX');

  // test if sidebox should display
  if (EZPAGES_STATUS_SIDEBOX == '1' or (EZPAGES_STATUS_SIDEBOX== '2' && zen_is_whitelisted_admin_ip())) {
    if (isset($var_linksList)) {
      unset($var_linksList);
    }

    if (!$sniffer->table_exists(TABLE_EZPAGES_CONTENT)) {
      return; // early exit; db not upgraded
    }
    $pages_query = $db->Execute("SELECT e.*, ec.*
                                FROM " . TABLE_EZPAGES . " e,
                                     " . TABLE_EZPAGES_CONTENT . " ec
                                WHERE e.pages_id = ec.pages_id
                                AND ec.languages_id = " . (int)$_SESSION['languages_id'] . "
                                AND e.status_sidebox = 1
                                AND e.sidebox_sort_order > 0
                                ORDER BY e.sidebox_sort_order, ec.pages_title");
    if ($pages_query->RecordCount()>0) {
      $title =  BOX_HEADING_EZPAGES;
      $box_id =  'ezpages';
      $rows = 0;
      $page_query_list_sidebox = array();
      foreach ($pages_query as $page_query) {
        $rows++;
        $page_query_list_sidebox[$rows]['id'] = $page_query['pages_id'];
        $page_query_list_sidebox[$rows]['name'] = $page_query['pages_title'];
        $page_query_list_sidebox[$rows]['altURL']  = "";
        switch (true) {
          // external link new window or same window
          case ($page_query['alt_url_external'] != ''):
          $page_query_list_sidebox[$rows]['altURL']  = $page_query['alt_url_external'];
          break;
          // internal link new window
          case ($page_query['alt_url'] != '' && $page_query['page_open_new_window'] == '1'):
          $page_query_list_sidebox[$rows]['altURL']  = (substr($page_query['alt_url'],0,4) == 'http') ?
          $page_query['alt_url'] :
          ($page_query['alt_url']=='' ? '' : zen_href_link($page_query['alt_url'], '', ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
          // internal link same window
          case ($page_query['alt_url'] != '' && $page_query['page_open_new_window'] == '0'):
          $page_query_list_sidebox[$rows]['altURL']  = (substr($page_query['alt_url'],0,4) == 'http') ?
          $page_query['alt_url'] :
          ($page_query['alt_url']=='' ? '' : zen_href_link($page_query['alt_url'], '', ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
        }

        // if altURL is specified, use it; otherwise, use EZPage ID to create link
        $page_query_list_sidebox[$rows]['link'] = ($page_query_list_sidebox[$rows]['altURL'] =='') ?
        zen_href_link(FILENAME_EZPAGES, 'id=' . $page_query['pages_id'] . ($page_query['toc_chapter'] > 0 ? '&chapter=' . $page_query['toc_chapter'] : ''), ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL')) :
        $page_query_list_sidebox[$rows]['altURL'];
        $page_query_list_sidebox[$rows]['link'] .= ($page_query['page_open_new_window'] == '1' ? '" rel="noreferrer noopener" target="_blank' : '');
      }

      $title_link = false;

      $var_linksList = $page_query_list_sidebox;

      require($template->get_template_dir('tpl_ezpages.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_ezpages.php');

      $zco_notifier->notify('NOTIFY_END_EZPAGES_SIDEBOX');
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  } // test for display

  $zco_notifier->notify('NOTIFY_END_EZPAGES_SIDEBOX');
