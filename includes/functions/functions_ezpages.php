<?php
/**
 * ezpages functions - used to prepare links for EZ-Pages
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */


/**
 * look up page_id and create link for ez_pages
 * to use this link add '\<a href="' . zen_ez_pages_link($pages_id) . '">\</a>';
 */
// to use this link add '<a href="' . zen_ez_pages_link($pages_id) . '"></a>';
  function zen_ez_pages_link($ez_pages_id, $ez_pages_chapter = 0, $ez_pages_is_ssl = false, $ez_pages_open_new_window = false, $ez_pages_return_full_url = false) {
    global $db;
    $ez_link = 'unknown';
    $ez_pages_name = 'Click Here';

    if ($ez_pages_chapter == 0) {
      $page_query = $db->Execute("SELECT * FROM " . TABLE_EZPAGES . " e, " . TABLE_EZPAGES_CONTENT . " ec WHERE e.pages_id = ec.pages_id AND ec.languages_id = " . (int)$_SESSION['languages_id'] . " AND e.pages_id='" . (int)$ez_pages_id . "' limit 1");

      $ez_pages_id = $page_query->fields['pages_id'];
      $ez_pages_name = $page_query->fields['pages_title'];
      $ez_pages_alturl = $page_query->fields['alt_url'];
      $ez_pages_chapter = $page_query->fields['toc_chapter'];
      $ez_pages_linkto = "";
      $ez_pages_external = $page_query->fields['alt_url_external'];
      switch (true) {
        // external link new window or same window
        case ($ez_pages_external != ''):
          $ez_pages_linkto  = $ez_pages_external;
          break;
          // internal link new window
        case ($ez_pages_alturl != '' and $ez_pages_open_new_window == '1'):
          $ez_pages_linkto  = (substr($ez_pages_alturl,0,4) == 'http') ?
                              $ez_pages_alturl :
                              ($ez_pages_alturl=='' ? '' : zen_href_link($ez_pages_alturl, '', ($ez_pages_is_ssl=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
          // internal link same window
        case ($ez_pages_alturl != '' and $ez_pages_open_new_window == '0'):
          $ez_pages_linkto  = (substr($ez_pages_alturl,0,4) == 'http') ?
                              $ez_pages_alturl :
                              ($ez_pages_alturl=='' ? '' : zen_href_link($ez_pages_alturl, '', ($ez_pages_is_ssl=='0' ? 'NONSSL' : 'SSL'), true, true, true));
          break;
      }

      // if altURL is specified, use it; otherwise, use EZPage ID to create link
      $ez_link = ($ez_pages_linkto =='') ?
        zen_href_link(FILENAME_EZPAGES, 'id=' . $ez_pages_id . ((int)$ez_pages_chapter != 0 ? '&chapter=' . $ez_pages_chapter : ''), ($ez_pages_is_ssl=='0' ? 'NONSSL' : 'SSL')) :
        $ez_pages_linkto;
      $ez_link .= ($ez_pages_open_new_window == '1' ? '" rel="noopener" target="_blank' : '');
    }

    if ($ez_pages_return_full_url == false) {
      return $ez_link;
    } else {
      return '<a href="' . $ez_link . '">' . $ez_pages_name . '</a>';
    }
  }
