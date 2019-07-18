<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 16 Modified in v1.5.6c $
 *
 * ALERT: This file requires PHP 5.4 or newer because it uses the short-array syntax.
 * 
 */
// Default refresh interval (0=off).  NOTE: Using automated refresh may put you in breach of PCI Compliance
$defaultRefreshInterval = 0;

// highlight bots
function zen_check_bot($value) {
  return empty($value);
}

function zen_check_quantity($which) {
  global $db;
  $which_query = $db->Execute("SELECT sesskey, value
                               FROM " . TABLE_SESSIONS . "
                               WHERE sesskey= '" . $which . "'");

  $who_query = $db->Execute("SELECT session_id, time_entry, time_last_click, host_address, user_agent
                             FROM " . TABLE_WHOS_ONLINE . "
                             WHERE session_id='" . $which . "'");

  // longer than 2 minutes light color
  $xx_mins_ago_long = (time() - WHOIS_TIMER_INACTIVE);

  $chk_cart_status = base64_decode($which_query->fields['value']);
  switch (true) {
    case ($which_query->RecordCount() == 0):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        return zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif');
      } else {
        return zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
      }
      break;
    case (strstr($chk_cart_status, '"contents";a:0:')):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        return zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif');
      } else {
        return zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
      }
      break;
    case (!strstr($chk_cart_status, '"contents";a:0:')):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        return zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif');
      } else {
        return zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
      }
      break;
  }
}

// time since last click
function zen_check_minutes($the_time_last_click) {
  $the_seconds = (time() - $the_time_last_click);
  $the_time_since = gmdate('H:i:s', $the_seconds);
  return $the_time_since;
}

require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

// same time_entry as time_last_click for 600 seconds = 10 minutes assumed to have left immediately
$xx_mins_ago_dead = (time() - WHOIS_TIMER_DEAD);

// remove after how many seconds? default= 1200 = 20 minutes
$xx_mins_ago = (time() - WHOIS_TIMER_REMOVE);

// remove entries that have expired
$db->Execute("DELETE FROM " . TABLE_WHOS_ONLINE . "
              WHERE time_last_click < '" . $xx_mins_ago . "'
              OR (time_entry=time_last_click
                AND time_last_click < '" . $xx_mins_ago_dead . "')");

if (!isset($_SESSION['wo_exclude_admins'])) {
  $_SESSION['wo_exclude_admins'] = TRUE;
}
if (isset($_GET['na'])) {
  $_SESSION['wo_exclude_admins'] = $_GET['na'] != 0;
}

if (!isset($_SESSION['wo_exclude_spiders'])) {
  $_SESSION['wo_exclude_spiders'] = TRUE;
}
if (isset($_GET['ns'])) {
  $_SESSION['wo_exclude_spiders'] = $_GET['ns'] != 0;
}

if (isset($_GET['t'])) {
  $_SESSION['wo_timeout'] = (int)$_GET['t'];
}
if (!isset($_SESSION['wo_timeout'])) {
  $_SESSION['wo_timeout'] = $defaultRefreshInterval;
}
if (!isset($_SESSION['wo_timeout']) || $_SESSION['wo_timeout'] < 3) {
  $_SESSION['wo_timeout'] = 0;
}

$listing = isset($_GET['q']) ? $_GET['q'] : '';
switch ($listing) {
  case "full_name-desc":
    $order = "full_name DESC, LPAD(ip_address,11,'0')";
    break;
  case "full_name":
    $order = "full_name, LPAD(ip_address,11,'0')";
    break;
  case "ip_address":
    $order = "ip_address, session_id";
    break;
  case "ip_address-desc":
    $order = "ip_address DESC, session_id";
    break;
  case "time_last_click-desc":
    $order = "time_last_click DESC, LPAD(ip_address,11,'0')";
    break;
  case "time_last_click":
    $order = "time_last_click, LPAD(ip_address,11,'0')";
    break;
  case "time_entry-desc":
    $order = "time_entry DESC, LPAD(ip_address,11,'0')";
    break;
  case "time_entry":
    $order = "time_entry, LPAD(ip_address,11,'0')";
    break;
  case "last_page_url-desc":
    $order = "last_page_url DESC, LPAD(ip_address,11,'0')";
    break;
  case "last_page_url":
    $order = "last_page_url, LPAD(ip_address,11,'0')";
    break;
  case "session_id":
    $order = "session_id, ip_address";
    break;
  case "session_id-desc":
    $order = "session_id DESC, ip_address";
    break;
  default:
    $order = "time_entry, LPAD(ip_address,11,'0')";
}
$where = '';
if ($_SESSION['wo_exclude_spiders']) {
  $where = "WHERE session_id != '' ";
}
if ($_SESSION['wo_exclude_admins']) {
  $where .= ($where == '') ? " WHERE " : " AND ";
  $where .= "ip_address != '' AND ip_address NOT IN ('" . implode("','", preg_split('/[\s,]/', EXCLUDE_ADMIN_IP_FOR_MAINTENANCE . ',' . $_SERVER['REMOTE_ADDR'])) . "') ";
}
$sql = "SELECT customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, host_address, user_agent
        FROM " . TABLE_WHOS_ONLINE . "
        :where:
        ORDER BY :orderby:";
$sql = $db->bindVars($sql, ':where:', $where, 'passthru');
$sql = $db->bindVars($sql, ':orderby:', $order, 'passthru');
$whos_online = $db->Execute($sql);

// catch the case where we have an invalid session key, and if so default it to first entry
$found_entry = false;
$candidate_info = '';
foreach ($whos_online as $item) {
  if (!isset($candidate_info)) {
    $candidate_info = $item['session_id']; // get first entry in list
  }
  if (!$found_entry && isset($_GET['info']) && $_GET['info'] == $item['session_id']) {
    $found_entry = true;
    break;
  }
}
if (!$found_entry) {
  $_GET['info'] = $candidate_info;
}

// rewind query
$whos_online->rewind();
$total_sess = $whos_online->RecordCount();

$optURL = FILENAME_WHOS_ONLINE . '.php?' . zen_get_all_get_params(['t', 'na', 'ns']);
$listingURL = FILENAME_WHOS_ONLINE . '.php?' . zen_get_all_get_params(['q', 't', 'na', 'ns']);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
      function refreshTimer(time) {
          if (time.length >= 2) {
              clearTimeout(initTimer);
              if (theTimer != null) {
                  clearTimeout(theTimer);
              }
              var theTimer = setTimeout('window.location="<?php echo $optURL; ?>t=' + time + '&auto=true"', (time * 1000));
          }
      }
<?php if (isset($_SESSION['wo_timeout']) && (int)$_SESSION['wo_timeout'] > 0) { ?>
        var initTimer = setTimeout('location.reload(true)', <?php echo isset($_SESSION['wo_timeout']) ? $_SESSION['wo_timeout'] * 1000 : '60000'; ?>);
<?php } ?>
    </script>
    <style>
      .whos-online td {
          color:#444;
          font-family:Helvetica, Arial, sans-serif;
      }
      .whos-online td.infoBoxHeading {
          color:#fff;
      }
      .last-url-link {
          background:#fff;
          border:1px dashed #aaa;
          margin:5px 0;
          padding:5px;
      }
      .last-url-link a {
          color:green;
      }
      .dataTableRowBot .last-url-link a {color: #333;}
      .dataTableRowSelectedBot .last-url-link a {color: #333;}
      .dataTableRowBot .last-url-link {background: #f0cbfa;}
      .dataTableRowSelectedBot .last-url-link {background: #f0cbfa;}

      #wo-legend {float: left;}
      #wo-filters { float: right; background-color: #599659; color: #fff}
      #wo-filters .optionClick { display: inline-block; color: #fff; border: 1px solid #fff; font-weight: bold; padding: 1px; margin: 2px 1px;}
      #wo-filters .chosen {background-color: #003D00;}
    </style>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>

      <!-- body_text //-->
      <div class="row">
        <div class="col-sm-6" id="wo-legend">
            <?php
            echo
            '<a href="' . zen_href_link(FILENAME_WHOS_ONLINE . '.php', zen_get_all_get_params()) . '" class="menuBoxContentLink">' . '<strong><u>' . WHOS_ONLINE_REFRESH_LIST_TEXT . '</u></strong>' . '</a>' .
            '<br />' . "\n" . WHOS_ONLINE_LEGEND_TEXT . '&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_NO_CART_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_NO_CART_TEXT . '<br />' .
            WHOS_ONLINE_INACTIVE_LAST_CLICK_TEXT . '&nbsp;' . WHOIS_TIMER_INACTIVE . 's' . '&nbsp;||&nbsp;' . WHOS_ONLINE_INACTIVE_ARRIVAL_TEXT . '&nbsp;' .
            WHOIS_TIMER_DEAD . 's&nbsp;' . WHOS_ONLINE_REMOVED_TEXT;
            ?>
        </div>

        <div class="col-sm-6" id="wo-filters">
            <?php echo TEXT_WHOS_ONLINE_TIMER_UPDATING . ($_SESSION['wo_timeout'] > 0 ? sprintf(TEXT_WHOS_ONLINE_TIMER_EVERY, $_SESSION['wo_timeout']) : TEXT_WHOS_ONLINE_TIMER_DISABLED); ?>

          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '0') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=0"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ0; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '5') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=5"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ1; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '15') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=15"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ2; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '30') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=30"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ3; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '60') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=60"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ4; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '300') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=300"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ5; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '600') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=600"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ6; ?></a>&nbsp;
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '840') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=840"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ7; ?></a>&nbsp;<br />

          <?php echo TEXT_WHOS_ONLINE_FILTER_SPIDERS; ?>
          <a class="optionClick<?php echo ($_SESSION['wo_exclude_spiders']) ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>ns=1"><?php echo TEXT_YES; ?></a>&nbsp;
          <a class="optionClick<?php echo (!$_SESSION['wo_exclude_spiders']) ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>ns=0"><?php echo TEXT_NO; ?></a>&nbsp;
          &nbsp;&nbsp;&nbsp;
          <?php echo TEXT_WHOS_ONLINE_FILTER_ADMINS; ?>
          <a class="optionClick<?php echo ($_SESSION['wo_exclude_admins']) ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>na=1"><?php echo TEXT_YES; ?></a>&nbsp;
          <a class="optionClick<?php echo (!$_SESSION['wo_exclude_admins']) ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>na=0"><?php echo TEXT_NO; ?></a>&nbsp;
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12"><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $total_sess); ?></div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr class="dataTableHeadingRowWhois">
                  <th class="dataTableHeadingContentWhois"><?php echo TABLE_HEADING_ONLINE; ?></th>
                  <th class="dataTableHeadingContentWhois text-center"><?php echo TABLE_HEADING_CUSTOMER_ID; ?></th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'full_name-desc' or $listing == 'full_name') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_FULL_NAME . '</span>' : TABLE_HEADING_FULL_NAME); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=full_name"; ?>"><?php echo ($listing == 'full_name' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=full_name-desc"; ?>"><?php echo ($listing == 'full_name-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'ip_address-desc' or $listing == 'ip_address') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_IP_ADDRESS . '</span>' : TABLE_HEADING_IP_ADDRESS); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=ip_address"; ?>"><?php echo ($listing == 'ip_address' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=ip_address-desc"; ?>"><?php echo ($listing == 'ip_address-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'session_id-desc' or $listing == 'session_id') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_SESSION_ID . '</span>' : TABLE_HEADING_SESSION_ID); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=session_id"; ?>"><?php echo ($listing == 'session_id' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=session_id-desc"; ?>"><?php echo ($listing == 'session_id-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'time_entry-desc' or $listing == 'time_entry') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_ENTRY_TIME . '</span>' : TABLE_HEADING_ENTRY_TIME); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=time_entry"; ?>"><?php echo ($listing == 'time_entry' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=time_entry-desc"; ?>"><?php echo ($listing == 'time_entry-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'time_last_click-desc' or $listing == 'time_last_click') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_CLICK . '</span>' : TABLE_HEADING_LAST_CLICK); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=time_last_click"; ?>"><?php echo ($listing == 'time_last_click' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=time_last_click-desc"; ?>"><?php echo ($listing == 'time_last_click-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($listing == 'last_page_url-desc' or $listing == 'last_page_url') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_PAGE_URL . '</span>' : TABLE_HEADING_LAST_PAGE_URL); ?>&nbsp;
                    <br /><a href="<?php echo $listingURL . "q=last_page_url"; ?>"><?php echo ($listing == 'last_page_url' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=last_page_url-desc"; ?>"><?php echo ($listing == 'last_page_url-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $ip_array = [];
                  $d = 0; // duplicates counter
                  foreach ($whos_online as $item) {
                    $time_online = (time() - $item['time_entry']);

                    if (empty($info) && (empty($_GET['info']) || $_GET['info'] == $item['session_id'])) {
                      $info = $item['session_id'];
                      $ip_address = $item['ip_address'];
                      $full_name = $item['full_name'];
                    }

// Check for duplicates
                    if (in_array($item['ip_address'], $ip_array)) {
                      $d++;
                    } else {
                      $ip_array[] = $item['ip_address'];
                    }

// Check for bots
                    $is_a_bot = zen_check_bot($item['session_id']);
                    if ($item['session_id'] == $info) {
                      if ($is_a_bot == true) {
                        echo '              <tr class="dataTableRowSelectedBot">' . "\n";
                      } else {
                        echo '              <tr class="dataTableRowSelectedWhois">' . "\n";
                      }
                    } else {
                      if ($is_a_bot == true) {
                        echo '              <tr class="dataTableRowBot" onmouseover="this.className=\'dataTableRowOverBot\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRowBot\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(['info', 'action']) . 'info=' . $item['session_id'], 'NONSSL') . '\'">' . "\n";
                      } else {
                        echo '              <tr class="dataTableRowWhois" onmouseover="this.className=\'dataTableRowOverWhois\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRowWhois\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(['info', 'action']) . 'info=' . $item['session_id'], 'NONSSL') . '\'">' . "\n";
                      }
                    }
                    ?>
                <td class="dataTableContentWhois"><?php echo zen_check_quantity($item['session_id']) . '&nbsp;' . gmdate('H:i:s', $time_online); ?></td>
                <td class="dataTableContentWhois" align="center">
                    <?php
                    if ($item['customer_id'] != 0) {
                      echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(['cID', 'action']) . 'cID=' . $item['customer_id'] . '&action=edit', 'NONSSL') . '"><u>' . $item['customer_id'] . '</u></a>';
                    } else {
                      echo $item['customer_id'];
                    }
                    ?>
                </td>
                <td class="dataTableContentWhois" nowrap="nowrap">
                    <?php
                    if ($item['customer_id'] != 0) {
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'cID=' . $item['customer_id'], 'NONSSL') . '">' . '<u>' . $item['full_name'] . '</u></a>';
                    } else {
                      echo $item['full_name'];
                    }
                    ?>
                </td>
                <td class="dataTableContentWhois" align="left" valign="top"><a href="http://whois.domaintools.com/<?php echo $item['ip_address']; ?>" target="_blank"><?php echo '<u>' . $item['ip_address'] . '</u>'; ?></a></td>
                <td>&nbsp;</td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $item['time_entry']); ?></td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $item['time_last_click']); ?></td>
                <td class="dataTableContentWhois" colspan="2" valign="top">&nbsp;</td>
                </tr>
                <?php
                // show host name
                if (WHOIS_SHOW_HOST == '1') {
                  if ($item['session_id'] == $info) {
                    if ($is_a_bot == true) {
                      echo '              <tr class="dataTableRowSelectedBot">' . "\n";
                    } else {
                      echo '              <tr class="dataTableRowSelectedWhois">' . "\n";
                    }
                  } else {
                    if ($is_a_bot == true) {
                      echo '              <tr class="dataTableRowBot" onmouseout="this.className=\'dataTableRowBot\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(['info', 'action']) . 'info=' . zen_output_string_protected($item['session_id']), 'NONSSL') . '\'">' . "\n";
                    } else {
                      echo '              <tr class="dataTableRowWhois" onmouseout="this.className=\'dataTableRowWhois\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(['info', 'action']) . 'info=' . zen_output_string_protected($item['session_id']), 'NONSSL') . '\'">' . "\n";
                    }
                  }
                  ?>
                  <td class="dataTableContentWhois" colspan=3 valign="top">&nbsp;&nbsp;<?php echo TIME_PASSED_LAST_CLICKED . '<br />&nbsp;&nbsp;&nbsp;&nbsp;' . zen_check_minutes($item['time_last_click']); ?> ago</td>
                  <td class="dataTableContentWhois" colspan=5 valign="top">
                      <?php
                      echo TEXT_SESSION_ID . zen_output_string_protected($item['session_id']) . '<br />' .
                      TEXT_HOST . zen_output_string_protected($item['host_address']) . '<br />' .
                      TEXT_USER_AGENT . zen_output_string_protected($item['user_agent']) . '<br />';

                      $lastURLlink = '<a href="' . zen_output_string_protected($item['last_page_url']) . '" target="_blank">' . '<u>' . zen_output_string_protected($item['last_page_url']) . '</u>' . '</a>';
                      if (preg_match('/^(.*)' . zen_session_name() . '=[a-f,0-9]+[&]*(.*)/i', $item['last_page_url'], $array)) {
                        $lastURLlink = zen_output_string_protected($array[1] . $array[2]);
                      }
                      echo '<div class="last-url-link">' . $lastURLlink . '</div>';
                      ?>
                  </td>

                  </tr>
                  <?php
                } // show host
                ?>
                <tr>
                  <td colspan="8"></td>
                </tr>

                <?php
              }
              if (!$d) {
                $d = 0;
              }
              $total_dupes = $d;
              $ip_unique = sizeof($ip_array);
              $total_cust = $total_sess - $total_dupes;
              ?>
              </tbody>
              <tfoot>
                <?php
// repeat legend when whois >=
                if ($whos_online->RecordCount() >= WHOIS_REPEAT_LEGEND_BOTTOM) {
                  ?>
                  <tr>
                    <td colspan="8">Legend:
                        <?php
                        echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . " Active cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . " Inactive cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . " Active no cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . " Inactive no cart "
                            . "<br />Inactive is Last Click >= " . WHOIS_TIMER_INACTIVE . "s"
                            . " &nbsp; || Inactive since arrival > " . WHOIS_TIMER_DEAD . "s will be removed";
                        ?>
                    </td>
                  </tr>
                  <?php
                }
                ?>
                <tr>
                  <td colspan="8">
                    <?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $total_sess); ?><br />
                    <?php echo TEXT_DUPLICATE_IPS . $total_dupes; ?><br />
                    <?php echo TEXT_TOTAL_UNIQUE_USERS . $total_cust; ?>.
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
          <?php
          $heading = [];
          $contents = [];
          if (!empty($info)) {
            $heading[] = ['text' => '<h4>' . TABLE_HEADING_SHOPPING_CART . '</h4>'];
            $tag = 0;
            $session_data = '';
            $result = $db->Execute("SELECT value
                                    FROM " . TABLE_SESSIONS . "
                                    WHERE sesskey = '" . $info . "'");
            $session_data = trim($result->fields['value']);

            if (strpos($session_data, 'cart|O') == 0) {
              $session_data = base64_decode($session_data);
            }
            if (strpos($session_data, 'cart|O') == 0) {
              $session_data = '';
            }


            if ($length = strlen($session_data)) {

              $start_field = [];
              $session_data_field = [];

              $session_fields = [
                                  'id' => 'customer_id|s',
                                  'currency' => 'currency|s',
                                  'country' => 'customer_country_id|s',
                                  'zone' => 'customer_zone_id|s',
                                  'cart' => 'cart|O',
                                ];

              foreach ($session_fields as $key => $value) {
                $start_field[$key] = strpos($session_data, $value);

                // If the session type is not found then don't try to initiate it.
                if (false === $start_field[$key]) {
                  continue;
                }

                $session_data_field[$key] = substr($session_data, $start_field[$key], (strpos($session_data, ';', $start_field[$key]) - $start_field[$key] + 1));

                if ('cart' === $key) {
                  $end_cart = (int)strpos($session_data, 'check_valid|s');
                  $session_data_field[$key] = substr($session_data, $start_field[$key], ($end_cart - $start_field[$key]));

//                  $end_cart = (int)strpos($session_data, '|', $start_field[$key] + strlen($value));
//                  $end_cart = (int)strrpos(substr($session_data, 0, $end_cart), ';}');
//                  $session_data_field[$key] = substr($session_data, $start_field[$key], ($end_cart - $start_field[$key] + 2));
                }

                $backup = $_SESSION;
                if (false === session_decode($session_data_field[$key])) {
                    $_SESSION = $backup;
                }
                unset($backup);
              }

              if (isset($_SESSION['cart']) && is_object($_SESSION['cart'])) {
                $contents[] = ['text' => $full_name . ' - ' . $ip_address . '<br />' . $info];
                $products = $_SESSION['cart']->get_products();
                for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
                  $contents[] = ['text' => $products[$i]['quantity'] . ' x ' . '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . zen_get_product_path($products[$i]['id']) . '&pID=' . $products[$i]['id']) . '">' . $products[$i]['name'] . '</a>'];
                }

                if (sizeof($products) > 0) {
                  $contents[] = ['text' => zen_draw_separator('pixel_black.gif', '100%', '1')];
                  $contents[] = ['align' => 'right', 'text' => TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($_SESSION['cart']->show_total(), true, $_SESSION['currency'])];
                } else {
                  $contents[] = ['text' => TEXT_EMPTY_CART];
                }
              }
            }
          }

          if (zen_not_null($heading) && zen_not_null($contents)) {
            $box = new box;
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
      </div>
      <!-- body_text_eof //-->
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
