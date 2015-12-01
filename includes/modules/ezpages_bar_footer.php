<?php
/**
 * ezpages bar (footer) - used to display links to EZ-Pages content in horizontal format (usually as a footer element)
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ezpages_bar_footer.php 6021 2007-03-17 16:34:19Z ajeh $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_START_EZPAGES_FOOTERBAR');

// test if bar should display:
if (EZPAGES_STATUS_FOOTER == '1' or (EZPAGES_STATUS_FOOTER == '2' and (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])))) {
  //  $page_query = $db->Execute("select * from " . TABLE_EZPAGES . " where status = 1 and languages_id=" . (int)$_SESSION['languages_id'] . " and horizontal_sort_order > 0 order by horizontal_sort_order, pages_title");
  if (isset($var_linksList)) {
    unset($var_linksList);
  }
  $page_query = $db->Execute("select * from " . TABLE_EZPAGES . " where status_footer = 1 and footer_sort_order > 0 order by footer_sort_order, pages_title");
  if ($page_query->RecordCount()>0) {
    $rows = 0;
    while (!$page_query->EOF) {
      $rows++;
      $page_query_list_footer[$rows]['id'] = $page_query->fields['pages_id'];
      $page_query_list_footer[$rows]['name'] = $page_query->fields['pages_title'];
      $page_query_list_footer[$rows]['altURL'] = '';

      // if altURL is specified, check to see if it starts with "http", and if so, create direct URL, otherwise use a zen href link
      switch (true) {
        // external link new window or same window
        case ($page_query->fields['alt_url_external'] != ''):
        $page_query_list_footer[$rows]['altURL']  = $page_query->fields['alt_url_external'];
        break;
        // internal link new window
        case ($page_query->fields['alt_url'] != '' and $page_query->fields['page_open_new_window'] == '1'):
        $page_query_list_footer[$rows]['altURL']  = (substr($page_query->fields['alt_url'],0,4) == 'http') ?
        $page_query->fields['alt_url'] :
        ($page_query->fields['alt_url']=='' ? '' : zen_href_link($page_query->fields['alt_url'], '', ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
        break;
        // internal link same window
        case ($page_query->fields['alt_url'] != '' and $page_query->fields['page_open_new_window'] == '0'):
        $page_query_list_footer[$rows]['altURL']  = (substr($page_query->fields['alt_url'],0,4) == 'http') ?
        $page_query->fields['alt_url'] :
        ($page_query->fields['alt_url']=='' ? '' : zen_href_link($page_query->fields['alt_url'], '', ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL'), true, true, true));
        break;
      }

      // if altURL is specified, use it; otherwise, use EZPage ID to create link
      $page_query_list_footer[$rows]['link'] = ($page_query_list_footer[$rows]['altURL'] =='') ?
      zen_href_link(FILENAME_EZPAGES, 'id=' . $page_query->fields['pages_id'] . ($page_query->fields['toc_chapter'] > 0 ? '&chapter=' . $page_query->fields['toc_chapter'] : ''), ($page_query->fields['page_is_ssl']=='0' ? 'NONSSL' : 'SSL')) :
      $page_query_list_footer[$rows]['altURL'];
      $page_query_list_footer[$rows]['link'] .= ($page_query->fields['page_open_new_window'] == '1' ? '" target="_blank' : '');
      $page_query->MoveNext();
    }

    $var_linksList = $page_query_list_footer;
  }
} // test for display

$zco_notifier->notify('NOTIFY_END_EZPAGES_FOOTERBAR');
?>