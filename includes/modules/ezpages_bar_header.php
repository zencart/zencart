<?php
/**
 * ezpages_bar_header - used to display links to EZ-Pages content horizontally as a header element
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_START_EZPAGES_HEADERBAR');

$var_linksList = array();

// test if bar should display:
if (EZPAGES_STATUS_HEADER == '1' || (EZPAGES_STATUS_HEADER == '2' && zen_is_whitelisted_admin_ip())) {

  if (!$sniffer->table_exists(TABLE_EZPAGES_CONTENT)) {
    return; // early exit; db not upgraded
  }
  $pages_query = $db->Execute("SELECT e.pages_id, e.page_open_new_window, e.page_is_ssl, e.alt_url, e.alt_url_external, e.toc_chapter, ec.pages_title
                              FROM  " . TABLE_EZPAGES . " e,
                                    " . TABLE_EZPAGES_CONTENT . " ec
                              WHERE e.pages_id = ec.pages_id
                              AND ec.languages_id = " . (int)$_SESSION['languages_id'] . "
                              AND e.status_header = 1
                              AND e.header_sort_order > 0
                              ORDER BY e.header_sort_order, ec.pages_title");

  if ($pages_query->RecordCount()>0) {
    $rows = 0;
    $page_query_list_header = array();
    foreach ($pages_query as $page_query) {
      $rows++;
      $page_query_list_header[$rows]['id'] = $page_query['pages_id'];
      $page_query_list_header[$rows]['name'] = $page_query['pages_title'];
      $page_query_list_header[$rows]['altURL'] = '';

      // if altURL is specified, check to see if it starts with "http", and if so, create direct URL, otherwise use a zen href link
      switch (true) {
        // external link new window or same window
        case ($page_query['alt_url_external'] != ''):
        $page_query_list_header[$rows]['altURL']  = $page_query['alt_url_external'];
        break;
        // internal link new window
        case ($page_query['alt_url'] != '' && $page_query['page_open_new_window'] == '1'):
        $page_query_list_header[$rows]['altURL']  = (substr($page_query['alt_url'],0,4) == 'http') ?
        $page_query['alt_url'] :
        ($page_query['alt_url']=='' ? '' : zen_href_link($page_query['alt_url'], '', ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
        break;
        // internal link same window
        case ($page_query['alt_url'] != '' && $page_query['page_open_new_window'] == '0'):
        $page_query_list_header[$rows]['altURL']  = (substr($page_query['alt_url'],0,4) == 'http') ?
        $page_query['alt_url'] :
        ($page_query['alt_url']=='' ? '' : zen_href_link($page_query['alt_url'], '', ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
        break;
      }

      // if altURL is specified, use it; otherwise, use EZPage ID to create link
      $page_query_list_header[$rows]['link'] = ($page_query_list_header[$rows]['altURL'] =='') ?
      zen_href_link(FILENAME_EZPAGES, 'id=' . $page_query['pages_id'] . ($page_query['toc_chapter'] > 0 ? '&chapter=' . $page_query['toc_chapter'] : ''), ($page_query['page_is_ssl']=='0' ? 'NONSSL' : 'SSL')) :
      $page_query_list_header[$rows]['altURL'];
      $page_query_list_header[$rows]['link'] .= ($page_query['page_open_new_window'] == '1' ? '" rel="noreferrer noopener" target="_blank' : '');
    }

    $var_linksList = $page_query_list_header;
  }
} // display

$zco_notifier->notify('NOTIFY_END_EZPAGES_HEADERBAR');
