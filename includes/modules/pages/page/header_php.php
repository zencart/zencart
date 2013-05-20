<?php
/**
 * ez_pages ("page") header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 4881 2006-11-04 17:51:31Z ajeh $
 */
/*
* This "page" page is the display component of the ez-pages module
* It is called "page" instead of "ez-pages" due to the way the URL would display in the browser
* Aesthetically speaking, "page" is more professional in appearance than "ez-page" in the URL
*
* The EZ-Pages concept was adapted from the InfoPages contribution for Zen Cart v1.2.x, with thanks to Sunrom et al.
*/

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_EZPAGE');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$ezpage_id = (int)$_GET['id'];
if ($ezpage_id == 0) zen_redirect(zen_href_link(FILENAME_DEFAULT));

$chapter_id = (int)$_GET['chapter'];
$chapter_link = (int)$_GET['chapter'];

$sql = "select * from " . TABLE_EZPAGES . " where pages_id = " . (int)$ezpage_id;
// comment the following line to allow access to pages which don't have a status switch set to Yes:
$sql .= " AND (status_toc > 0 or status_header > 0 or status_sidebox > 0 or status_footer > 0)";

// Check to see if page exists and is accessible, retrieving relevant details for display if found
$var_pageDetails = $db->Execute($sql);
// redirect to home page if page not found (or deactivated/deleted):
if ($var_pageDetails->EOF) {
  $messageStack->add_session('header', ERROR_PAGE_NOT_FOUND, 'caution');
  header('HTTP/1.1 404 Not Found');
  header('Status: 404 Not Found');
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

//check db for prev/next based on sort orders
$pos = (isset($_GET['pos'])) ? $_GET['pos'] : 'v';  // v for vertical, h for horizontal  (v assumed if not specified)
$vert_links = array();
$toc_links = array();
//  $pages_order_query = "SELECT pages_id FROM " . TABLE_EZPAGES . " WHERE status = 1 and vertical_sort_order <> 0 ORDER BY vertical_sort_order, horizontal_sort_order, pages_title";
//  $pages_order_query = "SELECT * FROM " . TABLE_EZPAGES . " WHERE ((status_sidebox = 1 and sidebox_sort_order <> 0) or (status_footer = 1 and footer_sort_order <> 0) or (status_header = 1 and header_sort_order <> 0)) and alt_url_external = '' ORDER BY header_sort_order, sidebox_sort_order, footer_sort_order, pages_title";
$pages_order_query = "SELECT *
                      FROM " . TABLE_EZPAGES . "
                      WHERE ((status_toc = 1 and toc_sort_order <> 0) and toc_chapter= :chapterID )
                      AND alt_url_external = '' and alt_url = ''
                      ORDER BY toc_sort_order, pages_title";

$pages_order_query = $db->bindVars($pages_order_query, ':chapterID', $chapter_id, 'integer');
$pages_ordering = $db->execute($pages_order_query);

while (!$pages_ordering->EOF) {
  $vert_links[] = $pages_ordering->fields['pages_id'];
  $toc_links[] = array('pages_id' => $pages_ordering->fields['pages_id'], 'pages_title' => $pages_ordering->fields['pages_title']);
  $pages_ordering->MoveNext();
}

/**
$horiz_links = array();
//  $pages_order_query = "SELECT pages_id FROM " . TABLE_EZPAGES . " WHERE status = 1 and horizontal_sort_order <> 0 ORDER BY horizontal_sort_order, pages_title";
$pages_order_query = "SELECT * FROM " . TABLE_EZPAGES . " WHERE status_footer = 1 and footer_sort_order <> 0 and alt_url_external = '' ORDER BY footer_sort_order, pages_title";
$pages_ordering = $db->execute($pages_order_query);
while (!$pages_ordering->EOF) {
$horiz_links[] = $pages_ordering->fields['pages_id'];
$pages_ordering->MoveNext();
}
*/
// now let's determine prev/next
reset ($vert_links);
$counter = 0;
$previous_v = -1;
$last_v = 0;
$previous_vssl = '0';
$next_item_v = 0;
$next_vssl = 0;
while (list($key, $value) = each ($vert_links)) {
  if ($value == $ezpage_id) {
    $position_v = $counter;
    $previous_vssl = '0';
    if ($key == 0) {
      $previous_v = -1; // it was the first to be found
    } else {
      $previous_v = $vert_links[$key - 1];
    }
    $next_vssl = '0';
    if ($vert_links[$key + 1]) {
      $next_item_v = $vert_links[$key + 1];
    } else {
      $next_item_v = $vert_links[0];
    }
  }
  $last_v = $value;
  $counter++;
}
if ($previous_v == -1) $previous_v = $last_v;

/**
//prev/next for horiz now
reset ($horiz_links);
$counter = 0;
while (list($key, $value) = each ($horiz_links)) {
if ($value == $ezpage_id) {
$position_h = $counter;
$previous_hssl = '0';
if ($key == 0) {
$previous_h = -1; // it was the first to be found
} else {
$previous_h = $horiz_links[$key - 1];
}
$next_hssl = '0';
if ($horiz_links[$key + 1]) {
$next_item_h = $horiz_links[$key + 1];
} else {
$next_item_h = $horiz_links[0];
}
}
$last_h = $value;
$counter++;
}
if ($previous_h == -1) $previous_h = $last_h;
*/

if (!isset($pos) || empty($pos) || $pos=='v') {
  $prev_link = zen_href_link(FILENAME_EZPAGES, 'id=' . $previous_v . '&pos=v' . '&chapter=' . $chapter_link, ($previous_vssl =='0' ? 'NONSSL' : 'SSL'));
  $next_link = zen_href_link(FILENAME_EZPAGES, 'id=' . $next_item_v . '&pos=v' . '&chapter=' . $chapter_link, ($next_vssl =='0' ? 'NONSSL' : 'SSL'));
} else {
  $prev_link = zen_href_link(FILENAME_EZPAGES, 'id=' . $previous_h . '&pos=h' . '&chapter=' . $chapter_link, ($previous_hssl =='0' ? 'NONSSL' : 'SSL'));
  $next_link = zen_href_link(FILENAME_EZPAGES, 'id=' . $next_item_h . '&pos=h' . '&chapter=' . $chapter_link, ($next_hssl =='0' ? 'NONSSL' : 'SSL'));
}



$previous_button = zen_image_button(BUTTON_IMAGE_PREVIOUS, BUTTON_PREVIOUS_ALT);
$next_item_button = zen_image_button(BUTTON_IMAGE_NEXT, BUTTON_NEXT_ALT);
$home_button = zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT);


// set Page Title for heading, navigation, etc
define('NAVBAR_TITLE', $var_pageDetails->fields['pages_title']);
define('HEADING_TITLE', $var_pageDetails->fields['pages_title']);
$breadcrumb->add($var_pageDetails->fields['pages_title']);


// Pull settings from admin switches to determine what, if any, header/column/footer "disable" options need to be set
// Note that these are defined normally under Admin->Configuration->EZ-Pages-Settings
if (!defined('EZPAGES_DISABLE_HEADER_DISPLAY_LIST')) define('EZPAGES_DISABLE_HEADER_DISPLAY_LIST','');
if (!defined('EZPAGES_DISABLE_FOOTER_DISPLAY_LIST')) define('EZPAGES_DISABLE_FOOTER_DISPLAY_LIST','');
if (!defined('EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST')) define('EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST','');
if (!defined('EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST')) define('EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST','');
if ($ezpage_id > 0 ) {
  if (in_array($ezpage_id, explode(",",EZPAGES_DISABLE_HEADER_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_HEADER_DISPLAY_LIST,'*')) $flag_disable_header = true;
  if (in_array($ezpage_id, explode(",",EZPAGES_DISABLE_FOOTER_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_FOOTER_DISPLAY_LIST,'*')) $flag_disable_footer = true;
  if (in_array($ezpage_id, explode(",",EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST,'*')) $flag_disable_left = true;
  if (in_array($ezpage_id, explode(",",EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST,'*')) $flag_disable_right = true;
}
// end flag settings for sections to disable




// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_EZPAGE');
