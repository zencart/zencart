<?php
/**
 * Module Template
 *
 * NOTE: The clickable download links will appear only if:
 * - Download remaining count is > 0, AND
 * - The file is present in the DOWNLOAD directory, AND EITHER
 * - No expiry date is enforced (maxdays == 0), OR
 * - The expiry date is not reached
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sat Dec 23 12:42:13 2017 -0500 Modified in v1.5.6 $
 */
/**
 * require the downloads module
 */
  require(DIR_WS_MODULES . zen_get_module_directory('downloads.php'));

// if download is not available yet
if ($downloadsNotAvailableYet) {
?>
 <fieldset><?php echo DOWNLOADS_CONTROLLER_ON_HOLD_MSG ?></fieldset>
<?php
  return;
}

if ($numberOfDownloads < 1) {
  return;
}


// download is available
?>

<h4 id="headingDownloads"><?php echo HEADING_DOWNLOAD; ?></h4>
<table id="downloads">
  <tr class="tableHeading">
      <th scope="col" id="dlFileNameHeading"><?php echo TABLE_HEADING_PRODUCT_NAME; ?></th>
      <th scope="col" id="dlByteSize"><?php echo TABLE_HEADING_BYTE_SIZE; ?></th>
      <th scope="col" id="dlFilenameHeading"><?php echo TABLE_HEADING_DOWNLOAD_FILENAME; ?></th>
      <th scope="col" id="dlDateHeading"><?php echo TABLE_HEADING_DOWNLOAD_DATE; ?></th>
      <th scope="col" id="dlCountHeading"><?php echo TABLE_HEADING_DOWNLOAD_COUNT; ?></th>
      <th scope="col" id="dlButtonHeading">&nbsp;</th>
  </tr>
<!-- list of products -->
<?php
    foreach($downloads as $file) {
?>
  <tr class="tableRow">
<!-- left box -->
<?php
  if ($file['is_downloadable']) {
?>
      <td class="downloadProductNameLink"><?php echo '<a href="' . $file['link_url'] . '" download="' . $file['filename'] . '">' . $file['products_name'] . '</a>'; ?></td>
<?php } else { ?>
      <td class="downloadProductName"><?php echo $file['products_name']; ?></td>
<?php
  }
?>
      <td class="downloadFilesize"><?php echo $file['filesize'] . $file['filesize_units']; ?></td>
      <td class="downloadFilename"><?php echo $file['filename']; ?></td>
      <td class="downloadExpiry"><?php echo ($file['unlimited_downloads'] ? TEXT_DOWNLOADS_UNLIMITED : zen_date_short($file['expiry'])); ?></td>
      <td class="downloadCounts centeredContent"><?php echo ($file['unlimited_downloads'] ? TEXT_DOWNLOADS_UNLIMITED_COUNT : $file['download_count']); ?></td>
      <td class="downloadButton centeredContent"><?php echo ($file['is_downloadable']) ? '<a href="' . $file['link_url'] . '" download="' . $file['filename'] . '">' . zen_image_button(BUTTON_IMAGE_DOWNLOAD, BUTTON_DOWNLOAD_ALT) . '</a>' : '&nbsp;'; ?></td>
    </tr>
<?php
    } // end foreach
?>
  </table>

<?php
  if ($show_footer_link_to_my_account) {
?>
<p><?php printf(FOOTER_DOWNLOAD, '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'); ?></p>
<?php
  }
?>

