<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: gv_queue.php ajeh  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  $currencies = new currencies();

  if (isset($_GET['order'])) $_GET['order'] = (int)$_GET['order'];
  if (isset($_GET['gid'])) $_GET['gid'] = (int)$_GET['gid'];

// bof: find gv for a particular order and set page
  if ($_GET['order'] != '') {
    $gv_check = $db->Execute("select order_id, unique_id
                                  from " . TABLE_COUPON_GV_QUEUE . "
                                  where order_id = '" . $_GET['order'] . "' and release_flag= 'N' limit 1");

    $_GET['gid'] = $gv_check->fields['unique_id'];

    $gv_page = $db->Execute("select c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.order_id from " . TABLE_CUSTOMERS . " c, " . TABLE_COUPON_GV_QUEUE . " gv where (gv.customer_id = c.customers_id and gv.release_flag = 'N')" . " order by gv.order_id, gv.unique_id");
    $page_cnt=1;
    while (!$gv_page->EOF) {
      if ($gv_page->fields['order_id'] == $_GET['order']) {
        break;
      }
      $page_cnt++;
      $gv_page->MoveNext();
    }
    $_GET['page'] = round(($page_cnt/MAX_DISPLAY_SEARCH_RESULTS));
    zen_redirect(zen_href_link(FILENAME_GV_QUEUE, 'gid=' . $gv_check->fields['unique_id'] . '&page=' . $_GET['page']));
  }
// eof: find gv for a particular order and set page

  if ($_GET['action'] == 'confirmrelease' && isset($_POST['gid'])) {
    $gv_result = $db->Execute("select release_flag
                               from " . TABLE_COUPON_GV_QUEUE . "
                               where unique_id='" . (int)$_POST['gid'] . "'");

    if ($gv_result->fields['release_flag'] == 'N') {
      $gv_resulta = $db->Execute("select customer_id, amount, order_id
                                  from " . TABLE_COUPON_GV_QUEUE . "
                                  where unique_id='" . (int)$_POST['gid'] . "'");

      if ($gv_resulta->RecordCount() > 0) {
      $gv_amount = $gv_resulta->fields['amount'];

  // Begin composing email content
//      //Let's build a message object using the email class
      $mail = $db->Execute("select customers_firstname, customers_lastname, customers_email_address
                           from " . TABLE_CUSTOMERS . "
                           where customers_id = '" . $gv_resulta->fields['customer_id'] . "'");

      $message  = TEXT_REDEEM_GV_MESSAGE_HEADER . "\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n\n" . TEXT_REDEEM_GV_MESSAGE_RELEASED;
      $message .= sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, $currencies->format($gv_amount)) . "\n\n";
      $message .= TEXT_REDEEM_GV_MESSAGE_THANKS . "\n" . STORE_OWNER . "\n\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
      $message .= TEXT_REDEEM_GV_MESSAGE_BODY;
      $message .= TEXT_REDEEM_GV_MESSAGE_FOOTER;
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];
      $html_msg['GV_NOTICE_HEADER']  = TEXT_REDEEM_GV_MESSAGE_HEADER;
      $html_msg['GV_NOTICE_RELEASED']  = TEXT_REDEEM_GV_MESSAGE_RELEASED;
      $html_msg['GV_NOTICE_AMOUNT_REDEEM'] = sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, '<strong>' . $currencies->format($gv_amount) . '</strong>');
      $html_msg['GV_NOTICE_VALUE'] = $currencies->format($gv_amount);
      $html_msg['GV_NOTICE_THANKS'] = TEXT_REDEEM_GV_MESSAGE_THANKS;
      $html_msg['TEXT_REDEEM_GV_MESSAGE_BODY'] = TEXT_REDEEM_GV_MESSAGE_BODY;
      $html_msg['TEXT_REDEEM_GV_MESSAGE_FOOTER'] = TEXT_REDEEM_GV_MESSAGE_FOOTER;

//send the message
        zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $gv_resulta->fields['order_id'] , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'gv_queue');
      // send copy to Admin if enabled
      if (SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO != '') {
        zen_mail('', SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO, SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_SUBJECT . ' ' . TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $gv_resulta->fields['order_id'] , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'gv_queue');
      }

      zen_record_admin_activity('GV Queue entry released in the amount of ' . $gv_amount . ' for ' . $mail->fields['customers_email_address'], 'info');

      $gv_amount=$gv_resulta->fields['amount'];
      $gv_result=$db->Execute("select amount
                               from " . TABLE_COUPON_GV_CUSTOMER . "
                               where customer_id='" . $gv_resulta->fields['customer_id'] . "'");

      $customer_gv=false;
      $total_gv_amount=0;
      if ($gv_result->RecordCount() > 0) {
        $total_gv_amount=$gv_result->fields['amount'];
        $customer_gv=true;
      }
      $total_gv_amount=$total_gv_amount+$gv_amount;
      if ($customer_gv) {
        $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                      set amount='" . $total_gv_amount . "'
                      where customer_id='" . $gv_resulta->fields['customer_id'] . "'");
      } else {
        $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . "
                    (customer_id, amount)
                    values ('" . $gv_resulta->fields['customer_id']. "', '" . $total_gv_amount . "')");
      }
        $db->Execute("update " . TABLE_COUPON_GV_QUEUE . "
                      set release_flag= 'Y'
                      where unique_id='" . (int)$_POST['gid'] . "'");
      }
    }
    // return back to same page after release
    zen_redirect(zen_href_link(FILENAME_GV_QUEUE, 'page=' . (int)$_GET['page']));
  }
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $gv_query_raw = "select c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.order_id from " . TABLE_CUSTOMERS . " c, " . TABLE_COUPON_GV_QUEUE . " gv where (gv.customer_id = c.customers_id and gv.release_flag = 'N')" . " order by gv.order_id, gv.unique_id";
  $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
  $gv_list = $db->Execute($gv_query_raw);
  while (!$gv_list->EOF) {
    if (((!$_GET['gid']) || (@$_GET['gid'] == $gv_list->fields['unique_id'])) && (!$gInfo)) {
      $gInfo = new objectInfo($gv_list->fields);
    }
    if ( (is_object($gInfo)) && ($gv_list->fields['unique_id'] == $gInfo->unique_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_QUEUE, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->unique_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_QUEUE, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list->fields['unique_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $gv_list->fields['customers_firstname'] . ' ' . $gv_list->fields['customers_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $gv_list->fields['order_id']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $currencies->format($gv_list->fields['amount']); ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_datetime_short($gv_list->fields['date_created']); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($gInfo)) && ($gv_list->fields['unique_id'] == $gInfo->unique_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'page=' . $_GET['page'] . '&gid=' . $gv_list->fields['unique_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
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
  switch ($_GET['action']) {
    case 'release':
      $heading[] = array('text' => '[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount));
      $contents[] = array('align' => 'center', 'text' => zen_draw_form('gv_release', FILENAME_GV_QUEUE, 'action=confirmrelease&page=' . $_GET['page']) . zen_image_submit('button_confirm_red.gif', IMAGE_CONFIRM) . '<input type="hidden" name="gid" value="' . $gInfo->unique_id . '" /></form>' . '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'action=cancel&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
//      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'action=confirmrelease&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL') . '">' . zen_image_button('button_confirm_red.gif', IMAGE_CONFIRM) . '</a> <a href="' . zen_href_link('gv_queue.php', 'action=cancel&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      $heading[] = array('text' => '[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount));

      if ($gv_list->RecordCount() == 0) {
        $contents[] = array('align' => 'center','text' => TEXT_GV_NONE);
      } else {
        $contents[] = array('align' => 'center','text' => '<a href="' . zen_href_link(FILENAME_GV_QUEUE,'action=release&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL'). '">' . zen_image_button('button_release_gift.gif', IMAGE_RELEASE) . '</a>');

// quick link to order
        $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','90%','3'));
        $contents[] = array('align' => 'center', 'text' => TEXT_EDIT_ORDER . $gInfo->order_id);
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $gInfo->order_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_order.gif', IMAGE_ORDER) . '</a>');
      }
      break;
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