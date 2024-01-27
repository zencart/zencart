<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();
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
    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDERS_NAME; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_VOUCHER_CODE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_DATE_SENT; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TEXT_HEADING_DATE_REDEEMED; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $gv_query_raw = "SELECT c.coupon_amount, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, crt.redeem_date
                                 FROM " . TABLE_COUPONS . " c
                                 LEFT JOIN " . TABLE_COUPON_REDEEM_TRACK . " crt ON c.coupon_id = crt.coupon_id,
                                      " . TABLE_COUPON_EMAIL_TRACK . " et
                                 WHERE c.coupon_id = et.coupon_id
                                 AND c.coupon_type = 'G'
                                 ORDER BY date_sent desc";
                $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
                $gv_lists = $db->Execute($gv_query_raw);
                foreach ($gv_lists as $gv_list) {
                  if ((empty($_GET['gid']) || (@$_GET['gid'] == $gv_list['coupon_id'])) && !isset($gInfo)) {
                    $gInfo = new objectInfo($gv_list);
                  }
                  if (isset($gInfo) && is_object($gInfo) && $gv_list['coupon_id'] == $gInfo->coupon_id) {
                    ?>
                    <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link('gv_sent.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->coupon_id . '&action=edit'); ?>'">
                    <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link('gv_sent.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list['coupon_id']); ?>'">
                    <?php } ?>
                    <td class="dataTableContent"><?php echo $gv_list['sent_firstname'] . ' ' . $gv_list['sent_lastname']; ?></td>
                    <td class="dataTableContent text-center"><?php echo $currencies->format($gv_list['coupon_amount']); ?></td>
                    <td class="dataTableContent text-center"><?php echo $gv_list['coupon_code']; ?></td>
                    <td class="dataTableContent text-right"><?php echo zen_date_short($gv_list['date_sent']); ?></td>
                    <td class="dataTableContent text-right"><?php echo (empty($gv_list['redeem_date']) ? TEXT_INFO_NOT_REDEEMED : zen_date_short($gv_list['redeem_date'])); ?></td>
                    <td class="dataTableContent text-right">
                      <?php
                      if (isset($gInfo) && (is_object($gInfo)) && ($gv_list['coupon_id'] == $gInfo->coupon_id)) {
                        echo zen_icon('caret-right', '', '2x', true);
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_GV_SENT, 'page=' . $_GET['page'] . '&gid=' . $gv_list['coupon_id']) . '">' . zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, false) . '</a>';
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

            if (isset($gInfo)) {
              $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));
              $redeem = $db->Execute("SELECT *
                                      FROM " . TABLE_COUPON_REDEEM_TRACK . "
                                      WHERE coupon_id = " . (int)$gInfo->coupon_id);
              $redeemed = 'No';
              if ($redeem->RecordCount() > 0)
                $redeemed = 'Yes';
              $contents[] = array('text' => TEXT_INFO_SENDERS_ID . ' ' . $gInfo->customer_id_sent . ' ' . ($gInfo->customer_id_sent != 0 ? zen_get_customer_email_from_id($gInfo->customer_id_sent) : ''));
              $contents[] = array('text' => TEXT_INFO_AMOUNT_SENT . ' ' . $currencies->format($gInfo->coupon_amount));
              $contents[] = array('text' => TEXT_INFO_DATE_SENT . ' ' . zen_date_short($gInfo->date_sent));
              $contents[] = array('text' => TEXT_INFO_VOUCHER_CODE . ' ' . $gInfo->coupon_code);
              $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to);
              if ($redeemed == 'Yes') {
                $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_REDEEMED . ' ' . zen_date_short($redeem->fields['redeem_date']));
                $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $redeem->fields['redeem_ip']);
                $contents[] = array('text' => TEXT_INFO_CUSTOMERS_ID . ' ' . $redeem->fields['customer_id'] . ' ' . ($redeem->fields['customer_id'] != 0 ? zen_get_customer_email_from_id($redeem->fields['customer_id']) : ''));
              } else {
                $contents[] = array('text' => '<br>' . TEXT_INFO_NOT_REDEEMED);
              }

              if (!empty($heading) && !empty($contents)) {
                $box = new box();
                echo $box->infoBox($heading, $contents);
              }
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
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
