<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_downloads.php 6374 2007-05-25 20:24:42Z drbyte $
 */
/**
 * require the downloads module
 */
  require(DIR_WS_MODULES . zen_get_module_directory('downloads.php'));
?>

<?php
// download is available
  if ($downloads->RecordCount() > 0) {
?>

<table border="0" width="100%" cellspacing="0" cellpadding="0" id="downloads">
<caption><h4><?php echo HEADING_DOWNLOAD; ?></h4></caption>
  <tr class="tableHeading">
      <th scope="col" id="dlFileNameHeading"><?php echo TABLE_HEADING_PRODUCT_NAME; ?></th>
      <th scope="col" id="dlByteSize"><?php echo TABLE_HEADING_BYTE_SIZE; ?></th>
      <th scope="col" id="dlButtonHeading"><?php echo TABLE_HEADING_DOWNLOAD_FILENAME; ?></th>
      <th scope="col" id="dlDateHeading"><?php echo TABLE_HEADING_DOWNLOAD_DATE; ?></th>
      <th scope="col" id="dlCountHeading"><?php echo TABLE_HEADING_DOWNLOAD_COUNT; ?></th>
      <th scope="col" id="dlButtonHeading">&nbsp;</th>
          </tr>
<!-- list of products -->
<?php
    while (!$downloads->EOF) {
// MySQL 3.22 does not have INTERVAL
      list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
      $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads->fields['download_maxdays'], $dt_year);
      $download_expiry = date('Y-m-d H:i:s', $download_timestamp);

      $is_downloadable = ( (file_exists(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename']) && (($downloads->fields['download_count'] > 0 && $download_timestamp > time()) || $downloads->fields['download_maxdays'] == 0)) ) ? true : false;
      $zv_filesize = filesize (DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename']);
      if ($zv_filesize >= 1024) {
        $zv_filesize = number_format($zv_filesize/1024/1024,2);
        $zv_filesize_units = TEXT_FILESIZE_MEGS;
      } else {
        $zv_filesize = number_format($zv_filesize);
        $zv_filesize_units = TEXT_FILESIZE_BYTES;
      }
?>
          <tr class="tableRow">
<!-- left box -->
<?php
// The link will appear only if:
// - Download remaining count is > 0, AND
// - The file is present in the DOWNLOAD directory, AND EITHER
// - No expiry date is enforced (maxdays == 0), OR
// - The expiry date is not reached

//      if ( ($downloads->fields['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename'])) && ( ($downloads->fields['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
      if  ($is_downloadable) {
?>
      <td class=""><?php echo '<a href="' . zen_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads->fields['orders_products_download_id']) . '">' . $downloads->fields['products_name'] . '</a>'; ?></td>
<?php } else { ?>
      <td class=""><?php echo $downloads->fields['products_name']; ?></td>
<?php
      }
?>
      <td class=""><?php echo $zv_filesize . $zv_filesize_units; ?></td>
      <td class=""><?php echo $downloads->fields['orders_products_filename']; ?></td>
      <td class=""><?php echo ($downloads->fields['download_maxdays'] == 0 ? TEXT_DOWNLOADS_UNLIMITED : zen_date_short($download_expiry)); ?></td>
      <td class="centeredContent"><?php echo ($downloads->fields['download_maxdays'] == 0 ? TEXT_DOWNLOADS_UNLIMITED_COUNT : $downloads->fields['download_count']); ?></td>
      <td class="centeredContent"><?php echo ($is_downloadable) ? '<a href="' . zen_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads->fields['orders_products_download_id']) . '">' . zen_image_button(BUTTON_IMAGE_DOWNLOAD, BUTTON_DOWNLOAD_ALT) . '</a>' : '&nbsp;'; ?></td>
    </tr>
<?php
    $downloads->MoveNext();
    }
?>
  </table>

<?php
// old way
//    if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
// new way
      if (!($_GET['main_page']==FILENAME_ACCOUNT_HISTORY_INFO)) {
?>
<p><?php printf(FOOTER_DOWNLOAD, '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'); ?></p>
<?php } else { ?>
<?php
// other pages if needed
      }
?>

<?php
} // $downloads->RecordCount() > 0
?>

<?php
// download is not available yet
if ($downloads_check_query->RecordCount() > 0 and $downloads->RecordCount() < 1) {
?>
 <fieldset><?php echo DOWNLOADS_CONTROLLER_ON_HOLD_MSG ?></fieldset>
<?php
}
?>
