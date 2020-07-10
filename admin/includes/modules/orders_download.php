<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Fri Nov 10 08:51:49 2017 +0100 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// select downloads for current order
$orders_download_query = "select * from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id='" . (int)$_GET['oID'] . "'";
$orders_download = $db->Execute($orders_download_query);

// only display if there are downloads to display
if ($orders_download->RecordCount() > 0) {
  ?>
  <table class="table-bordered">
    <tr>
      <td class="smallText"><?php echo TEXT_LEGEND; ?></td>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_AVAILABLE . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT); ?></td>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_EXPIRED . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED); ?></td>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_MISSING . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_MISSING); ?></td>
    <tr>
      <td colspan="4" class="smallText text-center"><strong><?php echo TEXT_DOWNLOAD_TITLE; ?></strong></td>
    </tr>
    <tr>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_STATUS; ?></td>
      <td class="smallText"><?php echo TEXT_DOWNLOAD_FILENAME; ?></td>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_MAX_DAYS; ?></td>
      <td class="smallText text-center"><?php echo TEXT_DOWNLOAD_MAX_COUNT; ?></td>
    </tr>
    <?php
// add legend
    while (!$orders_download->EOF) {
      // $order->info['date_purchased'] . ' vs ' . (zen_date_diff($order->info['date_purchased'], date('Y-m-d')) > $orders_download->fields['download_maxdays'] ? 'NO' : 'YES') . ' vs ' .
      switch (true) {
        case ($orders_download->fields['download_maxdays'] <= 0 && $orders_download->fields['download_count'] <= 0):
          $zc_file_status = TEXT_INFO_EXPIRED_DATE . '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        case ($orders_download->fields['download_maxdays'] != 0 && (zen_date_diff($order->info['date_purchased'], date('Y-m-d')) > $orders_download->fields['download_maxdays'])):
          $zc_file_status = TEXT_INFO_EXPIRED_DATE . '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        case ($orders_download->fields['download_maxdays'] == 0):
          $zc_file_status = '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_off=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT) . '</a>';
          break;
        case ($orders_download->fields['download_maxdays'] > 0 and $orders_download->fields['download_count'] > 0):
          $zc_file_status = '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_off=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT) . '</a>';
          break;
        /*
          case ($orders_download->fields['download_maxdays'] <= 1 or $orders_download->fields['download_count'] <= 1):
          $zc_file_status = TEXT_INFO_EXPIRED_COUNT . '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
         */
        case ($orders_download->fields['download_maxdays'] != 0 && $orders_download->fields['download_count'] <= 1):
          $zc_file_status = TEXT_INFO_EXPIRED_COUNT . '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        default:
          $zc_file_status = '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
      }

// if not on server show red
      if (!zen_orders_products_downloads($orders_download->fields['orders_products_filename'])) {
        $zc_file_status = zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF);
      }
      ?>
      <tr>
        <td class="smallText text-center"><?php echo $zc_file_status; ?></td>
        <td class="smallText"><?php echo $orders_download->fields['orders_products_filename']; ?></td>
        <td class="smallText text-center"><?php echo $orders_download->fields['download_maxdays']; ?></td>
        <td class="smallText text-center"><?php echo $orders_download->fields['download_count']; ?></td>
      </tr>
      <?php
      $orders_download->MoveNext();
    }
    ?>
  </table>
  <?php
} // only display if there are downloads to display
