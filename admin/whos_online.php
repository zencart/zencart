<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: whos_online.php DrByte  Modified in v1.6.0 $
 */

// Default refresh interval (0=off).  NOTE: Using automated refresh may put you in breach of PCI Compliance
  $defaultRefreshInterval = 0;

// highlight bots
function zen_check_bot($checking) {
  if (empty($checking)) {
    return true;
  } else {
    return false;
  }
}

function zen_check_quantity($which) {
  global $db;
  $which_query = $db->Execute("select sesskey, value
                               from " . TABLE_SESSIONS . "
                               where sesskey= '" . $which . "'");

  $who_query = $db->Execute("select session_id, time_entry, time_last_click, host_address, user_agent
                             from " . TABLE_WHOS_ONLINE . "
                             where session_id='" . $which . "'");

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
    case (strstr($chk_cart_status,'"contents";a:0:')):
    if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
      return zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif');
    } else {
      return zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
    }
    break;
    case (!strstr($chk_cart_status,'"contents";a:0:')):
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
  $the_time_since= gmdate('H:i:s', $the_seconds);
  return $the_time_since;
}

  require('includes/application_top.php');

  $currencies = new currencies();

  // same time_entry as time_last_click for 600 seconds = 10 minutes assumed to have left immediately
  $xx_mins_ago_dead = (time() - WHOIS_TIMER_DEAD);

  // remove after how many seconds? default= 1200 = 20 minutes
  $xx_mins_ago = (time() - WHOIS_TIMER_REMOVE);

// remove entries that have expired
  $db->Execute("delete from " . TABLE_WHOS_ONLINE . "
                where time_last_click < '" . $xx_mins_ago . "'
                or (time_entry=time_last_click
                and time_last_click < '" . $xx_mins_ago_dead . "')");

  if (!isset($_SESSION['wo_exclude_admins'])) {
    $_SESSION['wo_exclude_admins'] = TRUE;
  }
  if (isset($_GET['na'])) {
    $_SESSION['wo_exclude_admins'] = ($_GET['na'] == 0) ? FALSE : TRUE;
  }

  if (!isset($_SESSION['wo_exclude_spiders'])) {
    $_SESSION['wo_exclude_spiders'] = TRUE;
  }
  if (isset($_GET['ns'])) {
    $_SESSION['wo_exclude_spiders'] = ($_GET['ns'] == 0) ? FALSE : TRUE;
  }

  if (isset($_GET['t']) ) {
    $_SESSION['wo_timeout'] = (int)$_GET['t'];
  }
  if (!isset($_SESSION['wo_timeout'])) {
    $_SESSION['wo_timeout'] = $defaultRefreshInterval;
  }
  if (!isset($_SESSION['wo_timeout']) || $_SESSION['wo_timeout'] < 3) {
    $_SESSION['wo_timeout'] = 0;
  }

  $listing = $_GET['q'];
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
    $where = "where session_id != '' ";
  }
  if ($_SESSION['wo_exclude_admins']) {
    $where .= ($where == '') ? " where " : " and ";
    $where .= "ip_address != '' and ip_address not in ('" . implode("','", preg_split('/[\s,]/', EXCLUDE_ADMIN_IP_FOR_MAINTENANCE . ',' . $_SERVER['REMOTE_ADDR'])) . "') ";
  }
  $sql = "select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, host_address, user_agent
          from " . TABLE_WHOS_ONLINE . " :where: order by :orderby:";
  $sql = $db->bindVars($sql, ':where:', $where, 'passthru');
  $sql = $db->bindVars($sql, ':orderby:', $order, 'passthru');
  $whos_online = $db->Execute($sql);

  // catch the case where we have an invalid session key, and if so default it to first entry
  $found_entry = false;
  $candidate_info = '';
  while(!$whos_online->EOF) {
    if (!isset($candidate_info)) $candidate_info = $whos_online->fields['session_id']; // get first entry in list
    if (!$found_entry && isset($_GET['info']) && $_GET['info'] == $whos_online->fields['session_id']) {
      $found_entry = true;
      break;
    }
    $whos_online->MoveNext();
  }
  if (!$found_entry) $_GET['info'] = $candidate_info;

  // rewind query
  $whos_online->rewind();
  $total_sess = $whos_online->RecordCount();

  $optURL = FILENAME_WHOS_ONLINE . '.php?' . zen_get_all_get_params(array('t', 'na', 'ns'));
  $listingURL = FILENAME_WHOS_ONLINE . '.php?' . zen_get_all_get_params(array('q', 't', 'na', 'ns'));
require('includes/admin_html_head.php');
?>
<script type="text/javascript">
  <!--
 function refreshTimer(time)
  {
     if(time.length>=2) {
     clearTimeout(initTimer);
     if(theTimer!=null) {
       clearTimeout(theTimer);
     }
     var theTimer = setTimeout('window.location="<?php echo $optURL; ?>t='+time+'&auto=true"', (time*1000));
   }
  }
<?php if (isset($_SESSION['wo_timeout']) && (int)$_SESSION['wo_timeout'] > 0) { ?>
   var initTimer = setTimeout('location.reload(true)', <?php echo (isset($_SESSION['wo_timeout'])) ? $_SESSION['wo_timeout'] * 1000 : '60000'; ?>);
<?php } ?>

  // -->
</script>
<style>
<!-- /* inline CSS Styles */
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
-->
</style>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
          <tr>
            <td class="smallText" colspan="2"><div id="wo-legend">
              <?php echo
              '<a href="' . zen_href_link(FILENAME_WHOS_ONLINE . '.php', zen_get_all_get_params()) . '" class="menuBoxContentLink">' . '<strong><u>' . WHOS_ONLINE_REFRESH_LIST_TEXT . '</u></strong>' . '</a>' .
              '<br />' . "\n" . WHOS_ONLINE_LEGEND_TEXT . '&nbsp;' .
              zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_TEXT . '&nbsp;&nbsp;' .
              zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_TEXT . '&nbsp;&nbsp;' .
              zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_NO_CART_TEXT . '&nbsp;&nbsp;' .
              zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_NO_CART_TEXT . '<br />' .
              WHOS_ONLINE_INACTIVE_LAST_CLICK_TEXT . '&nbsp;' . WHOIS_TIMER_INACTIVE . 's' .'&nbsp;||&nbsp;' . WHOS_ONLINE_INACTIVE_ARRIVAL_TEXT . '&nbsp;' .
              WHOIS_TIMER_DEAD . 's&nbsp;' . WHOS_ONLINE_REMOVED_TEXT;?>
              </div>

              <div id="wo-filters">
                <?php echo TEXT_WHOS_ONLINE_TIMER_UPDATING . ($_SESSION['wo_timeout'] > 0 ? sprintf(TEXT_WHOS_ONLINE_TIMER_EVERY, $_SESSION['wo_timeout']) : TEXT_WHOS_ONLINE_TIMER_DISABLED); ?>

                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='0') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=0"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ0; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='5') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=5"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ1; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='15') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=15"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ2; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='30') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=30"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ3; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='60') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=60"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ4; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='300') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=300"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ5; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='600') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=600"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ6; ?></a>&nbsp;
                <a class="optionClick<?php echo ($_SESSION['wo_timeout']=='840') ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>t=840"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ7; ?></a>&nbsp;<br />

                <?php echo TEXT_WHOS_ONLINE_FILTER_SPIDERS; ?>
                <a class="optionClick<?php echo ($_SESSION['wo_exclude_spiders'])  ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>ns=1"><?php echo TEXT_YES; ?></a>&nbsp;
                <a class="optionClick<?php echo (!$_SESSION['wo_exclude_spiders']) ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>ns=0"><?php echo TEXT_NO; ?></a>&nbsp;
                &nbsp;&nbsp;&nbsp;
                <?php echo TEXT_WHOS_ONLINE_FILTER_ADMINS; ?>
                <a class="optionClick<?php echo ($_SESSION['wo_exclude_admins'])  ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>na=1"><?php echo TEXT_YES; ?></a>&nbsp;
                <a class="optionClick<?php echo (!$_SESSION['wo_exclude_admins']) ? ' chosen' : ''; ?>" href="<?php echo $optURL;?>na=0"><?php echo TEXT_NO; ?></a>&nbsp;
              </div>
            </td>
          </tr>
          <tr>
            <td class="smallText" colspan="2" valign="top"><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $total_sess);?>
            </td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr class="whos-online">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRowWhois">
                <td class="dataTableHeadingContentWhois"><?php echo TABLE_HEADING_ONLINE; ?></td>
                <td class="dataTableHeadingContentWhois" align="center"><?php echo TABLE_HEADING_CUSTOMER_ID; ?></td>

                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='full_name-desc' or $listing=='full_name') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_FULL_NAME . '</span>' : TABLE_HEADING_FULL_NAME); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=full_name"; ?>"><?php echo ($listing=='full_name' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=full_name-desc"; ?>"><?php echo ($listing=='full_name-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='ip_address-desc' or $listing=='ip_address') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_IP_ADDRESS . '</span>' : TABLE_HEADING_IP_ADDRESS); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=ip_address"; ?>"><?php echo ($listing=='ip_address' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=ip_address-desc"; ?>"><?php echo ($listing=='ip_address-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='session_id-desc' or $listing=='session_id') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_SESSION_ID . '</span>' : TABLE_HEADING_SESSION_ID); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=session_id"; ?>"><?php echo ($listing=='session_id' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=session_id-desc"; ?>"><?php echo ($listing=='session_id-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='time_entry-desc' or $listing=='time_entry') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_ENTRY_TIME . '</span>' : TABLE_HEADING_ENTRY_TIME); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=time_entry"; ?>"><?php echo ($listing=='time_entry' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=time_entry-desc"; ?>"><?php echo ($listing=='time_entry-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='time_last_click-desc' or $listing=='time_last_click') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_CLICK . '</span>' : TABLE_HEADING_LAST_CLICK); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=time_last_click"; ?>"><?php echo ($listing=='time_last_click' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=time_last_click-desc"; ?>"><?php echo ($listing=='time_last_click-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
                <td class="dataTableHeadingContentWhois" align="center">
                  <?php echo (($listing=='last_page_url-desc' or $listing=='last_page_url') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_PAGE_URL . '</span>' : TABLE_HEADING_LAST_PAGE_URL); ?>&nbsp;
                  <br /><a href="<?php echo $listingURL . "q=last_page_url"; ?>"><?php echo ($listing=='last_page_url' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                  &nbsp;<a href="<?php echo $listingURL . "q=last_page_url-desc"; ?>"><?php echo ($listing=='last_page_url-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                </td>
              </tr>
<?php
  $ip_array = array();
  $d=0;
  while (!$whos_online->EOF) {
    $time_online = (time() - $whos_online->fields['time_entry']);

    if ( (!$_GET['info'] || $_GET['info'] == $whos_online->fields['session_id']) && !$info) {
      $info = $whos_online->fields['session_id'];
      $ip_address = $whos_online->fields['ip_address'];
      $full_name = $whos_online->fields['full_name'];
    }

// Check for duplicates
    if (in_array($whos_online->fields['ip_address'], $ip_array)) {
      $d++;
    } else {
      $ip_array[] = $whos_online->fields['ip_address'];
    }

// Check for bots
    $is_a_bot=zen_check_bot($whos_online->fields['session_id']);
  if ($whos_online->fields['session_id'] == $info) {
      if ($is_a_bot==true) {
        echo '              <tr class="dataTableRowSelectedBot">' . "\n";
      } else {
        echo '              <tr class="dataTableRowSelectedWhois">' . "\n";
      }
  } else {
    if ($is_a_bot==true) {
        echo '              <tr class="dataTableRowBot" onmouseover="this.className=\'dataTableRowOverBot\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRowBot\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(array('info', 'action')) . 'info=' . $whos_online->fields['session_id'], 'NONSSL') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRowWhois" onmouseover="this.className=\'dataTableRowOverWhois\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRowWhois\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(array('info', 'action')) . 'info=' . $whos_online->fields['session_id'], 'NONSSL') . '\'">' . "\n";
      }
  }
?>
                <td class="dataTableContentWhois"><?php echo zen_check_quantity($whos_online->fields['session_id']) . '&nbsp;' . gmdate('H:i:s', $time_online); ?></td>
                <td class="dataTableContentWhois" align="center">
                  <?php
                    if ($whos_online->fields['customer_id'] != 0) {
                      echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $whos_online->fields['customer_id'] . '&action=edit', 'NONSSL') . '"><u>' . $whos_online->fields['customer_id'] . '</u></a>';
                    } else {
                      echo $whos_online->fields['customer_id'];
                    }
                  ?>
                </td>
                <td class="dataTableContentWhois" nowrap="nowrap">
                  <?php
                    if ($whos_online->fields['customer_id'] != 0) {
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'cID=' . $whos_online->fields['customer_id'], 'NONSSL') . '">' . '<u>' . $whos_online->fields['full_name'] . '</u></a>';
                    } else {
                      echo $whos_online->fields['full_name'];
                    }
                  ?>
                </td>
                <td class="dataTableContentWhois" align="left" valign="top"><a href="http://whois.domaintools.com/<?php echo $whos_online->fields['ip_address']; ?>" target="_blank"><?php echo '<u>' . $whos_online->fields['ip_address'] . '</u>'; ?></a></td>
                <td>&nbsp;</td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $whos_online->fields['time_entry']); ?></td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $whos_online->fields['time_last_click']); ?></td>
                <td class="dataTableContentWhois" colspan="2" valign="top">&nbsp;</td>
              </tr>
<?php
  // show host name
  if (WHOIS_SHOW_HOST=='1') {
    if ($whos_online->fields['session_id'] == $info) {
    if ($is_a_bot==true) {
        echo '              <tr class="dataTableRowSelectedBot">' . "\n";
      } else {
        echo '              <tr class="dataTableRowSelectedWhois">' . "\n";
      }
    } else {
      if ($is_a_bot==true) {
        echo '              <tr class="dataTableRowBot" onmouseout="this.className=\'dataTableRowBot\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(array('info', 'action')) . 'info=' . zen_output_string_protected($whos_online->fields['session_id']), 'NONSSL') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRowWhois" onmouseout="this.className=\'dataTableRowWhois\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(array('info', 'action')) . 'info=' . zen_output_string_protected($whos_online->fields['session_id']), 'NONSSL') . '\'">' . "\n";
      }
  }
?>
                <td class="dataTableContentWhois" colspan=3 valign="top">&nbsp;&nbsp;<?php echo TIME_PASSED_LAST_CLICKED . '<br />&nbsp;&nbsp;&nbsp;&nbsp;' . zen_check_minutes($whos_online->fields['time_last_click']); ?> ago</td>
                <td class="dataTableContentWhois" colspan=5 valign="top">
                  <?php
                    echo TEXT_SESSION_ID . zen_output_string_protected($whos_online->fields['session_id']) . '<br />' .
                    TEXT_HOST . zen_output_string_protected($whos_online->fields['host_address']) . '<br />' .
                    TEXT_USER_AGENT . zen_output_string_protected($whos_online->fields['user_agent']) . '<br />';

                    $lastURLlink = '<a href="' . zen_output_string_protected($whos_online->fields['last_page_url']) . '" target="_blank">' . '<u>' . zen_output_string_protected($whos_online->fields['last_page_url']) . '</u>' . '</a>';
                    if (preg_match('/^(.*)' . zen_session_name() . '=[a-f,0-9]+[&]*(.*)/i', $whos_online->fields['last_page_url'], $array)) {
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
               <td colspan="8"><?php echo zen_draw_separator('pixel_trans.gif', '1', '3'); ?></td>
              </tr>

<?php
  $whos_online->MoveNext();
  }
  if (!$d) {
    $d=0;
  }
  $total_dupes = $d;
  $ip_unique = sizeof($ip_array);
  $total_cust = $total_sess - $total_dupes;
?>
              <tr>
                <td colspan="8"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
// repeat legend when whois >=
  if ($whos_online->RecordCount() >= WHOIS_REPEAT_LEGEND_BOTTOM) {
?>
              <tr>
                <td class="smallText" colspan="8">Legend: <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . " Active cart &nbsp;&nbsp;" . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . " Inactive cart &nbsp;&nbsp;" . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . " Active no cart &nbsp;&nbsp;" .  zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . " Inactive no cart " . "<br />Inactive is Last Click >= " . WHOIS_TIMER_INACTIVE . "s" . " &nbsp; || Inactive since arrival > " . WHOIS_TIMER_DEAD . "s will be removed";?></td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="8"><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $total_sess); ?><br />
                <?php echo TEXT_DUPLICATE_IPS . $total_dupes; ?><br />
                <?php echo TEXT_TOTAL_UNIQUE_USERS . $total_cust;?>.</td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  if ($info) {
    $heading[] = array('text' => '<b>' . TABLE_HEADING_SHOPPING_CART . '</b>');
    $tag = 0;
    $session_data = '';
    $result = $db->Execute("select value from " . TABLE_SESSIONS . "
                            WHERE sesskey = '" . $info . "'");
    $session_data = trim($result->fields['value']);

    $hardenedStatus = FALSE;
    $suhosinExtension = extension_loaded('suhosin');
    $suhosinSetting = strtoupper(@ini_get('suhosin.session.encrypt'));

//    if (!$suhosinExtension) {
      if (strpos($session_data, 'cart|O') == 0) $session_data = base64_decode($session_data);
      if (strpos($session_data, 'cart|O') == 0) $session_data = '';
//    }

    // uncomment the following line if you have suhosin enabled and see errors on the cart-contents sidebar
    //$hardenedStatus = ($suhosinExtension == TRUE || $suhosinSetting == 'On' || $suhosinSetting == 1) ? TRUE : FALSE;
    if ($session_data != '' && $hardenedStatus == TRUE) $session_data = '';

    if ($length = strlen($session_data)) {
      $start_id = (int)strpos($session_data, 'customer_id|s');
      $start_currency = (int)strpos($session_data, 'currency|s');
      $start_country = (int)strpos($session_data, 'customer_country_id|s');
      $start_zone = (int)strpos($session_data, 'customer_zone_id|s');
      $start_cart = (int)strpos($session_data, 'cart|O');
      $end_cart = (int)strpos($session_data, '|', $start_cart+6);
      $end_cart = (int)strrpos(substr($session_data, 0, $end_cart), ';}');

      $session_data_id = substr($session_data, $start_id, (strpos($session_data, ';', $start_id) - $start_id + 1));
      $session_data_cart = substr($session_data, $start_cart, ($end_cart - $start_cart+2));
      $session_data_currency = substr($session_data, $start_currency, (strpos($session_data, ';', $start_currency) - $start_currency + 1));
      $session_data_country = substr($session_data, $start_country, (strpos($session_data, ';', $start_country) - $start_country + 1));
      $session_data_zone = substr($session_data, $start_zone, (strpos($session_data, ';', $start_zone) - $start_zone + 1));

      session_decode($session_data_id);
      session_decode($session_data_currency);
      session_decode($session_data_country);
      session_decode($session_data_zone);
      session_decode($session_data_cart);

      if (is_object($_SESSION['cart'])) {
        $contents[] = array('text' => $full_name . ' - ' . $ip_address . '<br />' . $info);
        $products = $_SESSION['cart']->get_products();
        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
          $contents[] = array('text' => $products[$i]['quantity'] . ' x ' . '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . zen_get_product_path($products[$i]['id']) . '&pID=' . $products[$i]['id']) . '">' . $products[$i]['name'] . '</a>');
        }

        if (sizeof($products) > 0) {
          $contents[] = array('text' => zen_draw_separator('pixel_black.gif', '100%', '1'));
          $contents[] = array('align' => 'right', 'text'  => TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($_SESSION['cart']->show_total(), true, $_SESSION['currency']));
        } else {
          $contents[] = array('text' => TEXT_EMPTY_CART);
        }
      }
    }
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>