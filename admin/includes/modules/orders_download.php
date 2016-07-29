<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: orders_download.php drbyte  Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
  $orders_download = $order->downloads;

// only display if there are downloads to display
  if (sizeof($orders_download) > 0) {
?>
      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><?php echo TEXT_LEGEND; ?></td>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_AVAILABLE . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT); ?></td>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_EXPIRED . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED); ?></td>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MISSING . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_MISSING); ?></td>
          <tr>
            <td colspan="4" class="smallText" align="center"><strong><?php echo TEXT_DOWNLOAD_TITLE; ?></strong></td>
          </tr>
          <tr>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_STATUS; ?></td>
            <td class="smallText" align="left"><?php echo TEXT_DOWNLOAD_FILENAME; ?></td>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MAX_DAYS; ?></td>
            <td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MAX_COUNT; ?></td>
          </tr>
<?php
// add legend
    foreach($orders_download as $download) {
      // $order->info['date_purchased'] . ' vs ' . (zen_date_diff($order->info['date_purchased'], date('Y-m-d')) > $download['download_maxdays'] ? 'NO' : 'YES') . ' vs ' .
      switch (true) {
        case ($download['download_maxdays'] <= 0 && $download['download_count'] <= 0):
          $zc_file_status = TEXT_INFO_EXPIRED_DATE . '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        case ($download['download_maxdays'] != 0 && (zen_date_diff($order->info['date_purchased'], date('Y-m-d')) > $download['download_maxdays'])):
          $zc_file_status = TEXT_INFO_EXPIRED_DATE . '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        case ($download['download_maxdays'] == 0):
          $zc_file_status = '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_off=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT) . '</a>';
          break;
        case ($download['download_maxdays'] > 0 and $download['download_count'] > 0):
          $zc_file_status = '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_off=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT) . '</a>';
          break;
/*
        case ($download['download_maxdays'] <= 1 or $download['download_count'] <= 1):
          $zc_file_status = TEXT_INFO_EXPIRED_COUNT . '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
*/
        case ($download['download_maxdays'] !=0 && $download['download_count'] <= 1):
          $zc_file_status = TEXT_INFO_EXPIRED_COUNT . '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        default:
          $zc_file_status = '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_GET['oID'] . '&action=edit&download_reset_on=' . $download['orders_products_download_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
          break;
      }

// if not on server show red
      if (!zen_verify_download_file_is_valid($download['orders_products_filename'])) {
        $zc_file_status = zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF);
      }
?>
          <tr>
            <td class="smallText" align="center"><?php echo $zc_file_status; ?></td>
            <td class="smallText" align="left"><?php echo $download['orders_products_filename']; ?></td>
            <td class="smallText" align="center"><?php echo $download['download_maxdays']; ?></td>
            <td class="smallText" align="center"><?php echo $download['download_count']; ?></td>
          </tr>
<?php
    }
?>
      </table></td>
    </tr>
<?php
  } // only display if there are downloads to display
