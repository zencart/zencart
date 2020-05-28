<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
// Default refresh interval (0=off).  NOTE: Using automated refresh may put you in breach of PCI Compliance
$defaultRefreshInterval = 0;

require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

if (!isset($_SESSION['wo_exclude_admins'])) {
  $_SESSION['wo_exclude_admins'] = true;
}
if (isset($_GET['na'])) {
  $_SESSION['wo_exclude_admins'] = $_GET['na'] != 0;
}
if (!isset($_SESSION['wo_exclude_spiders'])) {
  $_SESSION['wo_exclude_spiders'] = true;
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

$selectedView = isset($_GET['q']) ? $_GET['q'] : '';
$wo = new WhosOnline();
$whos_online = $wo->retrieve($selectedView, (empty($_GET['inspect']) ? '' : $_GET['inspect']), $_SESSION['wo_exclude_spiders'], $_SESSION['wo_exclude_admins']);

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
            '<br>' . "\n" . WHOS_ONLINE_LEGEND_TEXT . '&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . '&nbsp;' . WHOS_ONLINE_ACTIVE_NO_CART_TEXT . '&nbsp;&nbsp;' .
            zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . '&nbsp;' . WHOS_ONLINE_INACTIVE_NO_CART_TEXT . '<br>' .
            WHOS_ONLINE_INACTIVE_LAST_CLICK_TEXT . '&nbsp;' . (int)$wo->getTimerInactive() . 's' . '&nbsp;||&nbsp;' .
            WHOS_ONLINE_INACTIVE_ARRIVAL_TEXT . '&nbsp;' . (int)$wo->getTimerDead() . 's&nbsp;' . WHOS_ONLINE_REMOVED_TEXT;
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
          <a class="optionClick<?php echo ($_SESSION['wo_timeout'] == '840') ? ' chosen' : ''; ?>" href="<?php echo $optURL; ?>t=840"><?php echo TEXT_WHOS_ONLINE_TIMER_FREQ7; ?></a>&nbsp;
          <br>

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
        <div class="col-sm-12"><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $wo->getTotalSessions()); ?></div>
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
                    <?php echo (($selectedView == 'full_name-desc' or $selectedView == 'full_name') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_FULL_NAME . '</span>' : TABLE_HEADING_FULL_NAME); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=full_name"; ?>"><?php echo ($selectedView == 'full_name' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=full_name-desc"; ?>"><?php echo ($selectedView == 'full_name-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($selectedView == 'ip_address-desc' or $selectedView == 'ip_address') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_IP_ADDRESS . '</span>' : TABLE_HEADING_IP_ADDRESS); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=ip_address"; ?>"><?php echo ($selectedView == 'ip_address' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=ip_address-desc"; ?>"><?php echo ($selectedView == 'ip_address-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($selectedView == 'session_id-desc' or $selectedView == 'session_id') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_SESSION_ID . '</span>' : TABLE_HEADING_SESSION_ID); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=session_id"; ?>"><?php echo ($selectedView == 'session_id' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=session_id-desc"; ?>"><?php echo ($selectedView == 'session_id-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($selectedView == 'time_entry-desc' or $selectedView == 'time_entry') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_ENTRY_TIME . '</span>' : TABLE_HEADING_ENTRY_TIME); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=time_entry"; ?>"><?php echo ($selectedView == 'time_entry' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=time_entry-desc"; ?>"><?php echo ($selectedView == 'time_entry-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($selectedView == 'time_last_click-desc' or $selectedView == 'time_last_click') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_CLICK . '</span>' : TABLE_HEADING_LAST_CLICK); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=time_last_click"; ?>"><?php echo ($selectedView == 'time_last_click' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=time_last_click-desc"; ?>"><?php echo ($selectedView == 'time_last_click-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                  <th class="dataTableHeadingContentWhois text-center">
                    <?php echo (($selectedView == 'last_page_url-desc' or $selectedView == 'last_page_url') ? '<span class="dataTableHeadingContentWhois">' . TABLE_HEADING_LAST_PAGE_URL . '</span>' : TABLE_HEADING_LAST_PAGE_URL); ?>&nbsp;
                    <br><a href="<?php echo $listingURL . "q=last_page_url"; ?>"><?php echo ($selectedView == 'last_page_url' ? '<span class="dataTableHeadingContentWhois">' . 'Asc' . '</span>' : '<b>' . 'Asc' . '</b>'); ?></a>&nbsp;
                    &nbsp;<a href="<?php echo $listingURL . "q=last_page_url-desc"; ?>"><?php echo ($selectedView == 'last_page_url-desc' ? '<span class="dataTableHeadingContentWhois">' . 'Desc' . '</span>' : '<b>' . 'Desc' . '</b>'); ?></a>&nbsp;
                  </th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $selectedSession = '';
                  foreach ($whos_online as $item) {

                    if (empty($selectedSession) && (empty($_GET['inspect']) || $_GET['inspect'] == $item['session_id'])) {
                      $selectedSession = $item['session_id'];
                    }

                    if ($item['session_id'] == $selectedSession) {
                        echo '              <tr class="' . ($item['is_a_bot'] ? 'dataTableRowSelectedBot' : 'dataTableRowSelectedWhois') .'">' . "\n";
                    } else {
                        echo '              <tr class="' . ($item['is_a_bot'] ? 'dataTableRowBot' : 'dataTableRowWhois') .' whois-listing-row" data-sid="' . $item['session_id'] .'">' . "\n";
                    }

                    // item css classes indicating cart status: 'wo-inactive-empty', 'wo-active-empty', 'wo-inactive-not-empty', 'wo-active-not-empty'
                    ?>
                <td class="dataTableContentWhois <?php echo $item['icon_class']; ?>"><?php echo $item['icon_image'] . '&nbsp;' . gmdate('H:i:s', $item['time_online']); ?></td>
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
                <td class="dataTableContentWhois dataTableButtonCell" align="left" valign="top">
                    <?php
                    $whois_url = 'https://whois.domaintools.com/' . $item['ip_address'];
                    $additional_ipaddress_links = '';
                    $zco_notifier->notify('ADMIN_WHOSONLINE_IP_LINKS', $item, $additional_ipaddress_links, $whois_url);
                    ?>
                    <a href="<?php echo $whois_url; ?>" rel="noreferrer noopener" target="_blank">
                        <?php echo '<i class="fa fa-search"></i> <u>' . $item['ip_address'] . '</u>'; ?>
                    </a>
                    <?php echo $additional_ipaddress_links; ?>
                </td>
                <td>&nbsp;</td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $item['time_entry']); ?></td>
                <td class="dataTableContentWhois" align="center" valign="top"><?php echo date('H:i:s', $item['time_last_click']); ?></td>
                <td class="dataTableContentWhois" colspan="2" valign="top">&nbsp;</td>
                </tr>
                <?php
                // show host name
                if (WHOIS_SHOW_HOST == '1') {
                  if ($item['session_id'] == $selectedSession) {
                    echo '              <tr class="' . ($item['is_a_bot'] ? 'dataTableRowSelectedBot' : 'dataTableRowSelectedWhois') .'">' . "\n";
                  } else {
                    echo '              <tr class="' . ($item['is_a_bot'] ? 'dataTableRowBot' : 'dataTableRowWhois') .' whois-listing-row" data-sid="' . $item['session_id'] .'">' . "\n";
                  }
                  ?>
                  <td class="dataTableContentWhois" colspan=3 valign="top">&nbsp;&nbsp;<?php echo TIME_PASSED_LAST_CLICKED . '<br>&nbsp;&nbsp;&nbsp;&nbsp;' . $item['time_since_last_click']; ?> ago</td>
                  <td class="dataTableContentWhois dataTableButtonCell" colspan=5 valign="top">
                      <?php
                      echo TEXT_SESSION_ID . zen_output_string_protected($item['session_id']) . '<br>' .
                      TEXT_HOST . zen_output_string_protected($item['host_address']) . '<br>' .
                      TEXT_USER_AGENT . zen_output_string_protected($item['user_agent']) . '<br>';

                      $lastURLlink = '<a href="' . zen_output_string_protected($item['last_page_url']) . '" rel="noopener" target="_blank">' . '<u>' . zen_output_string_protected($item['last_page_url']) . '</u>' . '</a>';
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
              ?>
              </tbody>
              <tfoot>
                <?php
                if (count($whos_online) >= 20) { // repeat legend if more than 20 records
                ?>
                  <tr>
                    <td colspan="8">Legend:
                        <?php
                        echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . " Active cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . " Inactive cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . " Active no cart &nbsp;&nbsp;"
                            . zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif') . " Inactive no cart "
                            . "<br>Inactive is Last Click >= " . (int)$wo->getTimerInactive() . "s"
                            . " &nbsp; || Inactive since arrival > " . (int)$wo->getTimerDead() . "s will be removed";
                        ?>
                    </td>
                  </tr>
                <?php
                }
                ?>
                <tr>
                  <td colspan="8">
                    <?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, $wo->getTotalSessions()); ?><br>
                    <?php echo TEXT_DUPLICATE_IPS . $wo->getDuplicates(); ?><br>
                    <?php echo TEXT_TOTAL_UNIQUE_USERS . $wo->getUniques(); ?>.
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
          if (!empty($selectedSession)) {
            $heading[] = ['text' => '<h4>' . TABLE_HEADING_SHOPPING_CART . '</h4>'];

            $cart = isset($whos_online[$selectedSession]['cart']) ? $whos_online[$selectedSession]['cart'] : null;

            if ($cart !== null) {
                $contents[] = ['text' => $whos_online[$selectedSession]['full_name'] . ' - ' . $cart['customer_ip'] . ' (' . $cart['language_code']  . ')<br>' . $selectedSession];

                foreach ($cart['products'] as $product) {
                  $contents[] = ['text' => $product['quantity'] . ' x ' . '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . zen_get_product_path($product['id']) . '&pID=' . $product['id']) . '">' . $product['name'] . '</a>'];
                }

                if (!empty($cart['products'])) {
                  $contents[] = ['text' => zen_draw_separator('pixel_black.gif', '100%', '1')];
                  $contents[] = ['align' => 'right', 'text' => TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $cart['total'] . ' ' . $cart['currency_code']];
                } else {
                  $contents[] = ['text' => TEXT_EMPTY_CART];
                }
                /* Other $cart[] entries which may be available depending on customer stage:
                 * ['total'] => 92.74
                 * ['total_before_discounts'] => 92.74
                 * ['weight'] => 10
                 * ['cartID'] => 123456
                 * ['content_type'] => physical | virtual
                 * ['free_shipping_item'] => 0 | 1
                 * ['free_shipping_weight'] => 0 | 1
                 * ['free_shipping_price'] => 0 | 1
                 * ['download_count'] => integer
                 *
                 * Other $whos_online[$selectedSession][] entries which may or may not be available:
                 * ['currency_code'] 'USD'
                 * ['language_name'] 'english'
                 * ['language_id'] integer
                 * ['language_code'] 'en'
                 * ['customer_ip'] - ip address
                 * ['customer_hostname'] - hostname of ip address
                 * ['customers_email_address']
                 * ['address_default_id'] customer's default address_book ID
                 * ['address_billing_id'] selected address_book ID for billing
                 * ['address_delivery_id'] selected address_book ID for shipping
                 * ['customer_country_id'] countries table country_id of default address_book ID
                 * ['customer_zone_id'] zones table zone_id of default address_book ID
                 * ['shipping_weight'] cart weight
                 * ['shipping'] array of shipping module/code details
                 * ['payment'] string name of payment module selected
                 * ['cot_gv'] coupon/gv code being redeemed
                 * ['cart_errors'] array of error messages in cart
                 * ['checkout_comments'] order comments entered during checkout pages
                 */
            }
          }

          if (!empty($heading) && !empty($contents)) {
            $box = new box;
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
      </div>
      <!-- body_text_eof //-->
      <!-- body_eof //-->
    </div>

    <!--  enable on-page script tools -->
    <script>
        <?php
        $inspectLink = str_replace('&amp;', '&', zen_href_link(FILENAME_WHOS_ONLINE, zen_get_all_get_params(array('inspect', 'action')) . "inspect=[*]"));
        ?>
        jQuery(function () {
            const inspectLink = '<?php echo $inspectLink; ?>';
            jQuery("tr.whois-listing-row td").not('.dataTableButtonCell').on('click', (function() {
                window.location.href = inspectLink.replace('[*]', jQuery(this).parent().attr('data-sid'));
            }));
        })
    </script>

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
