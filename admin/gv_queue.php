<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

if (isset($_GET['order'])) {
  $_GET['order'] = (int)$_GET['order'];
}
if (isset($_GET['gid'])) {
  $_GET['gid'] = (int)$_GET['gid'];
}
if (!isset($_GET['action'])) {
  $_GET['action'] = '';
}

// bof: find gv for a particular order and set page
if (!empty($_GET['order'])) {
  $gv_check = $db->Execute("SELECT order_id, unique_id
                            FROM " . TABLE_COUPON_GV_QUEUE . "
                            WHERE order_id = " . (int)$_GET['order'] . "
                            AND release_flag= 'N'
                            LIMIT 1");

  $_GET['gid'] = $gv_check->fields['unique_id'];

  $gv_pages = $db->Execute("SELECT c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.order_id
                           FROM " . TABLE_CUSTOMERS . " c,
                                " . TABLE_COUPON_GV_QUEUE . " gv
                           WHERE (gv.customer_id = c.customers_id
                             AND gv.release_flag = 'N')
                           ORDER BY gv.order_id, gv.unique_id");
  $page_cnt = 1;
  foreach ($gv_pages as $gv_page) {
    if ($gv_page['order_id'] == $_GET['order']) {
      break;
    }
    $page_cnt++;
  }
  $_GET['page'] = round(($page_cnt / MAX_DISPLAY_SEARCH_RESULTS));
  zen_redirect(zen_href_link(FILENAME_GV_QUEUE, 'gid=' . $gv_check->fields['unique_id'] . '&page=' . $_GET['page']));
}
// eof: find gv for a particular order and set page

if ($_GET['action'] == 'confirmrelease' && isset($_POST['gid'])) {
  $gv_result = $db->Execute("SELECT release_flag
                             FROM " . TABLE_COUPON_GV_QUEUE . "
                             WHERE unique_id = " . (int)$_POST['gid']);

  if ($gv_result->fields['release_flag'] == 'N') {
    $gv_resulta = $db->Execute("SELECT customer_id, amount, order_id
                                FROM " . TABLE_COUPON_GV_QUEUE . "
                                WHERE unique_id = " . (int)$_POST['gid']);

    if ($gv_resulta->RecordCount() > 0) {
      $gv_amount = $gv_resulta->fields['amount'];

      // Begin composing email content
//      //Let's build a message object using the email class
      $mail = $db->Execute("SELECT customers_firstname, customers_lastname, customers_email_address
                            FROM " . TABLE_CUSTOMERS . "
                            WHERE customers_id = " . (int)$gv_resulta->fields['customer_id']);

      $message = TEXT_REDEEM_GV_MESSAGE_HEADER . "\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n\n" . TEXT_REDEEM_GV_MESSAGE_RELEASED;
      $message .= sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, $currencies->format($gv_amount)) . "\n\n";
      $message .= TEXT_REDEEM_GV_MESSAGE_THANKS . "\n" . STORE_OWNER . "\n\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
      $message .= TEXT_REDEEM_GV_MESSAGE_BODY;
      $message .= TEXT_REDEEM_GV_MESSAGE_FOOTER;
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME'] = $mail->fields['customers_lastname'];
      $html_msg['GV_NOTICE_HEADER'] = TEXT_REDEEM_GV_MESSAGE_HEADER;
      $html_msg['GV_NOTICE_RELEASED'] = TEXT_REDEEM_GV_MESSAGE_RELEASED;
      $html_msg['GV_NOTICE_AMOUNT_REDEEM'] = sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, '<strong>' . $currencies->format($gv_amount) . '</strong>');
      $html_msg['GV_NOTICE_VALUE'] = $currencies->format($gv_amount);
      $html_msg['GV_NOTICE_THANKS'] = TEXT_REDEEM_GV_MESSAGE_THANKS;
      $html_msg['TEXT_REDEEM_GV_MESSAGE_BODY'] = TEXT_REDEEM_GV_MESSAGE_BODY;
      $html_msg['TEXT_REDEEM_GV_MESSAGE_FOOTER'] = TEXT_REDEEM_GV_MESSAGE_FOOTER;

//send the message
      zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $gv_resulta->fields['order_id'], $message, STORE_NAME, EMAIL_FROM, $html_msg, 'gv_queue');

      zen_record_admin_activity('GV Queue entry released in the amount of ' . $gv_amount . ' for ' . $mail->fields['customers_email_address'], 'info');

      $gv_result = $db->Execute("SELECT amount
                                 FROM " . TABLE_COUPON_GV_CUSTOMER . "
                                 WHERE customer_id = " . (int)$gv_resulta->fields['customer_id']);

      $customer_gv = false;
      $total_gv_amount = 0;
      if ($gv_result->RecordCount() > 0) {
        $total_gv_amount = $gv_result->fields['amount'];
        $customer_gv = true;
      }
      $total_gv_amount += $gv_amount;
      if ($customer_gv) {
        $db->Execute("UPDATE " . TABLE_COUPON_GV_CUSTOMER . "
                      SET amount = " . (float)$total_gv_amount . "
                      WHERE customer_id = " . (int)$gv_resulta->fields['customer_id']);
      } else {
        $db->Execute("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount)
                      VALUES (" . (float)$gv_resulta->fields['customer_id'] . ", " . (int)$total_gv_amount . ")");
      }
      $db->Execute("UPDATE " . TABLE_COUPON_GV_QUEUE . "
                    SET release_flag= 'Y'
                    WHERE unique_id = " . (int)$_POST['gid']);
    }
  }
  // return back to same page after release
  zen_redirect(zen_href_link(FILENAME_GV_QUEUE, 'page=' . (int)$_GET['page']));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->

      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover table-striped">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ORDERS_ID; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $gv_query_raw = "SELECT c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.order_id
                               FROM " . TABLE_CUSTOMERS . " c,
                                    " . TABLE_COUPON_GV_QUEUE . " gv
                               WHERE (gv.customer_id = c.customers_id
                                 AND gv.release_flag = 'N')
                               ORDER BY gv.order_id, gv.unique_id";
              $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
              $gv_lists = $db->Execute($gv_query_raw);
              foreach ($gv_lists as $gv_list) {
                if ((!isset($_GET['gid']) || $_GET['gid'] == $gv_list['unique_id']) && (!isset($gInfo))) {
                  $gInfo = new objectInfo($gv_list);
                }
                if (isset($gInfo) && (is_object($gInfo)) && ($gv_list['unique_id'] == $gInfo->unique_id)) {
                  ?>
                  <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link('gv_queue.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->unique_id . '&action=edit'); ?>'">
                  <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link('gv_queue.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list['unique_id']); ?>'">
                  <?php } ?>
                  <td class="dataTableContent"><?php echo $gv_list['customers_firstname'] . ' ' . $gv_list['customers_lastname']; ?></td>
                  <td class="dataTableContent text-center"><?php echo $gv_list['order_id']; ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($gv_list['amount']); ?></td>
                  <td class="dataTableContent text-right"><?php echo zen_datetime_short($gv_list['date_created']); ?></td>
                  <td class="dataTableContent text-right">
                    <?php
                    if (isset($gInfo) && (is_object($gInfo)) && ($gv_list['unique_id'] == $gInfo->unique_id)) {
                      echo zen_icon('caret-right', '', '2x', true);
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'page=' . $_GET['page'] . '&gid=' . $gv_list['unique_id']) . '">' . zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, false) . '</a>';
                    }
                    ?>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
          </table>
          <table class="table">
            <tr>
              <td><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
              <td class="text-right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
          <?php
          $heading = [];
          $contents = [];
          switch ($_GET['action']) {
            case 'release':
              $heading[] = array('text' => '<h4>[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount) . '</h4>');
              $contents = array('form' => zen_draw_form('gv_release', FILENAME_GV_QUEUE, 'action=confirmrelease&page=' . $_GET['page']));
              $contents[] = array('align' => 'text-center', 'text' => zen_draw_hidden_field('gid', $gInfo->unique_id) . '<button type="submit" class="btn btn-primary">' . IMAGE_CONFIRM . '</button>&nbsp;<a href="' . zen_href_link('gv_queue.php', 'action=cancel&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'], 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
//      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link('gv_queue.php', 'action=confirmrelease&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL') . '" class="btn btn-danger" role="button">' . IMAGE_CONFIRM . '</a> <a href="' . zen_href_link('gv_queue.php', 'action=cancel&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'],'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
              break;
            default:
              if (!isset($gInfo) || !is_object($gInfo)) {
                $gInfo = new objectInfo([
                  'unique_id' => 0,
                  'date_created' => '0001-01-01 00:00:00',
                  'amount' => '0.0000',
                        ]
                );
              }
              $heading[] = array('text' => '<h4>[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount) . '</h4>');

              if (empty($gv_list)) {
                $contents[] = array('align' => 'text-center', 'text' => TEXT_GV_NONE);
              } else {
                $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link('gv_queue.php', 'action=release&gid=' . $gInfo->unique_id . '&page=' . $_GET['page'], 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_RELEASE . '</a>');

// quick link to order
                $contents[] = array('align' => 'text-center', 'text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '90%', '3'));
                $contents[] = array('align' => 'text-center', 'text' => TEXT_EDIT_ORDER . $gInfo->order_id);
                $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $gInfo->order_id . '&action=edit', 'NONSSL') . '" class="btn btn-info" role="button">' . IMAGE_ORDER . '</a>');
              }
              break;
          }

          if (!empty($heading) && !empty($contents)) {
            $box = new box();
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
