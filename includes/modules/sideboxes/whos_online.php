<?php
/**
 * whos_online sidebox - displays how many guests/members are online currently
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: whos_online.php 2718 2005-12-28 06:42:39Z drbyte $
 */

// test if box should display
  $show_whos_online= false;
  $show_whos_online= true;
  $n_guests = 0;
  $n_members = 0;

// Set expiration time, default is 1200 secs (20 mins)
  $xx_mins_ago = (time() - 1200);

  $db->Execute("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

  $whos_online_query = $db->Execute("select customer_id from " . TABLE_WHOS_ONLINE);
  while (!$whos_online_query->EOF) {
    if (!$whos_online_query->fields['customer_id'] == 0) $n_members++;
    if ($whos_online_query->fields['customer_id'] == 0) $n_guests++;

    $user_total = sprintf($whos_online_query->RecordCount());

    $whos_online_query->MoveNext();
  }

  if ($user_total == 1) {
    $there_is_are = BOX_WHOS_ONLINE_THEREIS . '&nbsp;';
  } else {
    $there_is_are = BOX_WHOS_ONLINE_THEREARE . '&nbsp;';
  }

  if ($n_guests == 1) {
    $word_guest = '&nbsp;' . BOX_WHOS_ONLINE_GUEST;
  } else {
    $word_guest = '&nbsp;' . BOX_WHOS_ONLINE_GUESTS;
  }

  if ($n_members == 1) {
    $word_member = '&nbsp;' . BOX_WHOS_ONLINE_MEMBER;
  } else {
    $word_member = '&nbsp;' . BOX_WHOS_ONLINE_MEMBERS;
  }

  if (($n_guests >= 1) && ($n_members >= 1)) { 
    $word_and = '&nbsp;' . BOX_WHOS_ONLINE_AND . '&nbsp;<br />';
  } else {
    $word_and = "";
  }

  $textstring = $there_is_are;
  if ($n_guests >= 1) $textstring .= $n_guests . $word_guest;

  $textstring .= $word_and;
  if ($n_members >= 1) $textstring .= $n_members . $word_member;

  $textstring .= '&nbsp;' . BOX_WHOS_ONLINE_ONLINE;


  $whos_online[] = $textstring;

// only show if either the tutorials are active or additional links are active
  if (sizeof($whos_online) > 0) {
    require($template->get_template_dir('tpl_whos_online.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_whos_online.php');

    $title =  BOX_HEADING_WHOS_ONLINE;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
?>
