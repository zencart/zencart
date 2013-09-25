<?php
/**
 * determine whether to output the noindex,nofollow metatag
 *
 * @package initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_robots_noindex_rules.php 18697 2011-05-04 14:35:20Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!isset($robotsNoIndex)) $robotsNoIndex = false;

// The $isRobotsMaintenanceMode variable is used only to output helper text in the template for assistance in troubleshooting whether the noindex tag is being output due to an Admin setting.
$isRobotsMaintenanceMode = (defined('ROBOTS_NOINDEX_MAINTENANCE_MODE') && ROBOTS_NOINDEX_MAINTENANCE_MODE == 'Maintenance') ? TRUE : FALSE;

switch (TRUE) {
  case ($isRobotsMaintenanceMode):
    @header("HTTP/1.1 503 Service Unavailable"); // tell search engines to that the site is temporarily unavailable, so they try again later
  case ($robotsNoIndex === TRUE):
  case ($current_page_base == 'down_for_maintenance'):
  case (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",str_replace(' ', '', ROBOTS_PAGES_TO_SKIP)))):
    $robotsNoIndex = TRUE;
}

// CUSTOM RULES CAN GO BELOW HERE:
// Simply set $robotsNoIndex = TRUE based on whatever custom condition you require it.
// ie: to set noindex on a certain single product #5 only, the following could work:
/**
 *  if (isset($_GET['products_id']) && $_GET['products_id'] == 5) $robotsNoIndex = TRUE;
 */
// ie: to set noindex on category #1 the following would work:
/**
 *  if (isset($_GET['cPath']) && $_GET['cPath'] == '1') $robotsNoIndex = TRUE;
 */
