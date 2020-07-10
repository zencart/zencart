<?php
/**
 * ez_pages ("page") header_php.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jun 29 Modified in v1.5.7 $
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

$ezpage_id = (int)$_GET['id'];
if ($ezpage_id == 0) {
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

$chapter_id = isset($_GET['chapter']) ? (int)$_GET['chapter'] : 0;
$chapter_link = isset($_GET['chapter']) ? (int)$_GET['chapter'] : 0;


if (!$sniffer->table_exists(TABLE_EZPAGES_CONTENT)) {
  return; // early exit; db not upgraded
}
$sql = "SELECT e.*, ec.*
        FROM  " . TABLE_EZPAGES . " e,
              " . TABLE_EZPAGES_CONTENT . " ec
        WHERE e.pages_id = ec.pages_id
        AND ec.languages_id = '" . (int)$_SESSION['languages_id'] . "'
        AND e.pages_id = " . (int)$ezpage_id;
// comment the following line to allow access to pages which don't have a status switch set to Yes:
$sql .= " AND (status_toc > 0 or status_header > 0 or status_sidebox > 0 or status_footer > 0 or status_visible > 0)";

// Check to see if page exists and is accessible, retrieving relevant details for display if found
$var_pageDetails = $db->Execute($sql);
// redirect to home page if page not found (or deactivated/deleted):
if ($var_pageDetails->EOF) {
  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  $messageStack->add_session('header', ERROR_PAGE_NOT_FOUND, 'caution');
  header('HTTP/1.1 404 Not Found');
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

//check db for prev/next based on sort orders
$pos = (isset($_GET['pos'])) ? $_GET['pos'] : 'v';  // v for vertical, h for horizontal  (v assumed if not specified)
$vert_links = array();
$toc_links = array();
$pages_order_query = "SELECT e.*,ec.*
                      FROM  " . TABLE_EZPAGES . " e,
                            " . TABLE_EZPAGES_CONTENT . " ec
                      WHERE ((e.status_toc = 1 AND e.toc_sort_order <> 0) AND e.toc_chapter= :chapterID )
                      AND e.alt_url_external = ''
                      AND e.alt_url = ''
                      AND ec.languages_id = '" . (int)$_SESSION['languages_id'] . "' 
                      AND e.pages_id = ec.pages_id
                      ORDER BY e.toc_sort_order, ec.pages_title";

$pages_order_query = $db->bindVars($pages_order_query, ':chapterID', $chapter_id, 'integer');
$pages_ordering = $db->execute($pages_order_query);

foreach ($pages_ordering as $page_order) {
  $vert_links[] = $page_order['pages_id'];
  $toc_links[] = array(
    'pages_id' => $page_order['pages_id'],
    'pages_title' => $page_order['pages_title']);
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
$counter = 0;
$previous_v = -1;
$last_v = 0;
$previous_vssl = '0';
$next_item_v = 0;
$next_vssl = 0;
foreach($vert_links as $key => $value) {
  if ($value == $ezpage_id) {
    $position_v = $counter;
    $previous_vssl = '0';
    if ($key == 0) {
      $previous_v = -1; // it was the first to be found
    } else {
      $previous_v = $vert_links[$key - 1];
    }
    $next_vssl = '0';
    if (!empty($vert_links[$key + 1])) {
      $next_item_v = $vert_links[$key + 1];
    } else {
      $next_item_v = $vert_links[0];
    }
  }
  $last_v = $value;
  $counter++;
}
if ($previous_v == -1) {
  $previous_v = $last_v;
}

/**
//prev/next for horiz now
$counter = 0;
foreach($horiz_links as $key => $value) {
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


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));


// Pull settings from admin switches to determine what, if any, header/column/footer "disable" options need to be set
// Note that these are defined normally under Admin->Configuration->EZ-Pages-Settings
if (!defined('EZPAGES_DISABLE_HEADER_DISPLAY_LIST')) {
  define('EZPAGES_DISABLE_HEADER_DISPLAY_LIST', '');
}
if (!defined('EZPAGES_DISABLE_FOOTER_DISPLAY_LIST')) {
  define('EZPAGES_DISABLE_FOOTER_DISPLAY_LIST', '');
}
if (!defined('EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST')) {
  define('EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST', '');
}
if (!defined('EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST')) {
  define('EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST', '');
}
if ($ezpage_id > 0) {
  if (in_array($ezpage_id, explode(",", EZPAGES_DISABLE_HEADER_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_HEADER_DISPLAY_LIST, '*')) {
    $flag_disable_header = true;
  }
  if (in_array($ezpage_id, explode(",", EZPAGES_DISABLE_FOOTER_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_FOOTER_DISPLAY_LIST, '*')) {
    $flag_disable_footer = true;
  }
  if (in_array($ezpage_id, explode(",", EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST, '*')) {
    $flag_disable_left = true;
  }
  if (in_array($ezpage_id, explode(",", EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST)) || strstr(EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST, '*')) {
    $flag_disable_right = true;
  }
}
// end flag settings for sections to disable
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_EZPAGE');
