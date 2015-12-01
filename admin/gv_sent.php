<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Jul 17 11:18:56 2012 -0400 Modified in v1.5.1 $
 */

require('includes/application_top.php');

$currencies = new currencies();

require('includes/admin_html_head.php');
?>
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDERS_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VOUCHER_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_SENT; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TEXT_HEADING_DATE_REDEEMED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $gv_query_raw = "select c.coupon_amount, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, crt.redeem_date, c.coupon_id
                  from " . TABLE_COUPONS . " c
                  left join " . TABLE_COUPON_REDEEM_TRACK . " crt
                  on c.coupon_id= crt.coupon_id, " . TABLE_COUPON_EMAIL_TRACK . " et
                  where c.coupon_id = et.coupon_id " . "
                  and c.coupon_type = 'G'
                  order by date_sent desc";
  $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
  $gv_list = $db->Execute($gv_query_raw);
  while (!$gv_list->EOF) {
    if (((!$_GET['gid']) || (@$_GET['gid'] == $gv_list->fields['coupon_id'])) && (!$gInfo)) {
    $gInfo = new objectInfo($gv_list->fields);
    }
    if ( (is_object($gInfo)) && ($gv_list->fields['coupon_id'] == $gInfo->coupon_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_SENT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->coupon_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_SENT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list->fields['coupon_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $gv_list->fields['sent_firstname'] . ' ' . $gv_list->fields['sent_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $currencies->format($gv_list->fields['coupon_amount']); ?></td>
                <td class="dataTableContent" align="center"><?php echo $gv_list->fields['coupon_code']; ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_date_short($gv_list->fields['date_sent']); ?></td>
                <td class="dataTableContent" align="right"><?php echo (empty($gv_list->fields['redeem_date']) ? TEXT_INFO_NOT_REDEEMED : zen_date_short($gv_list->fields['redeem_date'])); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($gInfo)) && ($gv_list->fields['coupon_id'] == $gInfo->coupon_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_GV_SENT, 'page=' . $_GET['page'] . '&gid=' . $gv_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $gv_list->MoveNext();
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
                    <td class="smallText" align="right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));
  $redeem = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . "
                          where coupon_id = '" . $gInfo->coupon_id . "'");
  $redeemed = 'No';
  if ($redeem->RecordCount() > 0) $redeemed = 'Yes';
  $contents[] = array('text' => TEXT_INFO_SENDERS_ID . ' ' . $gInfo->customer_id_sent);
  $contents[] = array('text' => TEXT_INFO_AMOUNT_SENT . ' ' . $currencies->format($gInfo->coupon_amount));
  $contents[] = array('text' => TEXT_INFO_DATE_SENT . ' ' . zen_date_short($gInfo->date_sent));
  $contents[] = array('text' => TEXT_INFO_VOUCHER_CODE . ' ' . $gInfo->coupon_code);
  $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to);
  if ($redeemed=='Yes') {
    $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_REDEEMED . ' ' . zen_date_short($redeem->fields['redeem_date']));
    $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $redeem->fields['redeem_ip']);
    $contents[] = array('text' => TEXT_INFO_CUSTOMERS_ID . ' ' . $redeem->fields['customer_id']);
  } else {
    $contents[] = array('text' => '<br />' . TEXT_INFO_NOT_REDEEMED);
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