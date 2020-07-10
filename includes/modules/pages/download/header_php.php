<?php
/**
 * download header_php.php
 *
 * NOTE: Download-by-Redirect often works only on Unix/Linux hosts since
 *       Windows hosts require special setup (and Windows servers couldn't do any symlinking in PHP versions older than 5.3.0)
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_DOWNLOAD');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// if the customer is not logged on and no email_address set (i.e. guest checkout), redirect the customer to the time out page
if (!zen_is_logged_in() && empty($_SESSION['email_address'])) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT, '', 'SSL'));
}

// Check download.php was called with proper GET parameters
if ((isset($_GET['order']) && !is_numeric($_GET['order'])) || (isset($_GET['id']) && !is_numeric($_GET['id'])) ) {
    // if the paramaters are wrong, redirect them to the time out page
    zen_redirect(zen_href_link(FILENAME_TIME_OUT, '', 'SSL'));
}

if (isset($_SESSION['email_address'])) {
    $lookup_clause = " AND o.customers_email_address = :emailAddress ";
} else {
    $lookup_clause = " AND o.customers_id = :customersID";
}

$sql = "SELECT date_format(o.date_purchased, '%Y-%m-%d')
          AS date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_maxdays, opd.orders_products_filename, o.*
          FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
          WHERE o.orders_id = :ordersID
          " . $lookup_clause . "
          AND o.orders_id = op.orders_id
          AND op.orders_products_id = opd.orders_products_id
          AND opd.orders_products_download_id = :downloadID
          AND opd.orders_products_filename != ''";

$sql = $db->bindVars($sql, ':downloadID', $_GET['id'], 'integer');
$sql = $db->bindVars($sql, ':ordersID', $_GET['order'], 'integer');
if (isset($_SESSION['email_address'])) {
    $sql = $db->bindVars($sql, ':emailAddress', $_SESSION['email_address'], 'string');
} else {
    $sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
}
$downloads = $db->Execute($sql);
if ($downloads->EOF) {
    $zco_notifier->notify('NOTIFY_DOWNLOAD_NO_MATCH_FOUND', $sql);
    exit(1);
}

$source_directory = DIR_FS_DOWNLOAD;
$service = 'local';
$mime_type = 'application/force-download';
$browser_headers_override = '';
$browser_extra_headers = '';

// determine filename for download
$origin_filename = $downloads->fields['orders_products_filename'];
$browser_filename = str_replace(' ', '_', $origin_filename);
if (strpos($browser_filename, '/') !== false) {
    $browser_filename = substr($browser_filename, strrpos($browser_filename, '/')+1);
}
if (strpos($browser_filename, '\\') !== false) {
    $browser_filename = substr($browser_filename, strrpos($browser_filename, '\\')+1);
}
if (substr($source_directory, -1) != '/') {
    $source_directory .= '/';
}

$file_exists = file_exists($source_directory . $origin_filename);
$downloadFilesize = (int)@filesize($source_directory . $origin_filename);

// calculate days
list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + (int)$downloads->fields['download_maxdays'], $dt_year);
// determine limits
$unlimited = (int)$downloads->fields['download_maxdays'] == 0;
$remainingCount = $downloads->fields['download_count'];
$isExpired = !$unlimited && ($download_timestamp <= time() || $remainingCount <= 0);

$zco_notifier->notify('NOTIFY_CHECK_DOWNLOAD_HANDLER', $downloads, $downloads->fields, $origin_filename, $browser_filename, $source_directory, $file_exists, $service, $isExpired, $download_timestamp);

if ($isExpired) {
    zen_redirect(zen_href_link(FILENAME_DOWNLOAD_TIME_OUT, '', 'SSL'));
}

// FIX HERE AND GIVE ERROR PAGE FOR MISSING FILE
// Die if file is not there
if (!$file_exists) {
    die('Sorry. File not found. Please contact the webmaster to report this error.<br />c/f: ' . $origin_filename);
}

// Now decrement counter
if (!isset($downloadsShouldDecrement) || $downloadsShouldDecrement === true) {
    $sql = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
               SET download_count = download_count-1
             WHERE orders_products_download_id = :downloadID
             LIMIT 1";
    $sql = $db->bindVars($sql, ':downloadID', $_GET['id'], 'integer');
    $db->Execute($sql);
}

// disable gzip output buffering if active:
@ob_end_clean();
if (@ini_get('zlib.output_compression')) {
    @ini_set('zlib.output_compression', 'Off');
}

if (!$file_exists) {
    $msg = 'DOWNLOAD PROBLEM: Problems detected with download for ' . $source_directory . $origin_filename . '(' . $service . ')' . ' because the file could not be found on the server. If the file exists, then its permissions are too low for PHP to access it. Contact your hosting company for specific help in determining correct permissions to make the file readable by PHP.';
    zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
}

if ($downloadFilesize < 1 && $service == 'local') {
    $msg = 'DOWNLOAD PROBLEM: Problem detected with download for ' . $source_directory . $origin_filename . ' because the server is preventing PHP from reading the file size attributes, or the file is actually 0 bytes in size (which suggests the uploaded file is damaged or incomplete). Perhaps its permissions are too low for PHP to access it? Contact your hosting company for specific help in determining correct permissions to allow PHP to stat the file using the filesize() function.';
    zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
}

/**
 * Browser detection
 * Presently only checks for IE-specific cases.
 */
$detectedBrowser = '';
preg_match('/msie (.*?)/i', $_SERVER['HTTP_USER_AGENT'], $matches);
if (count($matches) < 2) {
    preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
    $version = (count($matches) > 1) ? $matches[1] : 0;
    if ($version > 5) {
        $detectedBrowser = 'IE' . (int)$version;
    }
}

$zco_notifier->notify('NOTIFY_DOWNLOAD_BROWSER_DETECTION', array(), $detectedBrowser, $_SERVER['HTTP_USER_AGENT'], $version, $browser_headers_override, $browser_extra_headers);

/**
 * Do we need to transform something?
 * An observer class could stamp PDFs or do other pre-processing of the download media.
 */
$zco_notifier->notify('NOTIFY_DOWNLOAD_BEFORE_START', $_SESSION['customers_host_address'], $service, $origin_filename, $browser_filename, $source_directory, $downloadFilesize, $mime_type, $downloads->fields, $browser_headers_override);
$zco_notifier->notify('NOTIFY_DOWNLOAD_READY_TO_START', $_SESSION['customers_host_address'], $service, $origin_filename, $browser_filename, $source_directory, $downloadFilesize, $mime_type, $downloads->fields, $browser_headers_override);


/**
 * Check whether any headers have already been set, because that will cause download problems:
 */
$hfile = $hline = '';
if (headers_sent($hfile, $hline)) {
    $msg = 'DOWNLOAD PROBLEM: Cannot begin download for ' . $origin_filename . ' because HTTP headers were already sent. This indicates a PHP error, probably in a language file.  Start by checking ' . $hfile . ' on line ' . $hline . '.';
    error_log($msg);
    zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
}

if ($browser_headers_override == '') {
  /**
   * Now send the file with header() magic
   * The "must-revalidate" and expiry times are used to prevent caching and fraudulent re-acquiring of files w/o redownloading.
   * Certain browsers require certain header combinations, especially when related to SSL mode and caching
   */

  /**
   * Set mime type
   * These Content-Type headers should cause the downloaded file to trigger a "save as" dialog, instead of opening directly
   * However, some browsers and some software already installed on the PC may cause this to be overridden simply based on the filename. In these cases, the user will have to manually choose "Save As" themselves.
   * Alternatively, simply set the right handler in .htaccess
   */
//    header("Content-Type: application/x-octet-stream");
//    header("Content-Type: application/octet-stream");
//    header("Content-Type: application/download");
//    header("Content-Type: application/force-download");
    header("Content-Type: " . $mime_type);

    header('Content-Disposition: attachment; filename="' . urlencode($browser_filename) . '"');

    header("Expires: Mon, 22 Jan 2002 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
    switch ($detectedBrowser) {
        case 'IE6':
        case 'IE7':
        case 'IE8':
        case 'IE9':
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", FALSE);
            header("Cache-Control: max-age=1");  // stores for only 1 second, which helps allow SSL downloads to work more reliably in IE
            break;
        default:
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            break;
    }
    header("Content-Transfer-Encoding: binary");

} else {
    header($browser_headers_override);
}

if ($browser_extra_headers != '') {
    header($browser_extra_headers);
}

// Attempt to do download-by-redirect. It won't fire if not enabled. If it's disabled or fails setup we will cascade to streaming instead.
$link_create_status = false;
$zco_notifier->notify('NOTIFY_DOWNLOAD_READY_TO_REDIRECT', array(), $service, $origin_filename, $browser_filename, $source_directory, $link_create_status);

// We don't get here unless not downloading by redirect; instead, we stream it to the browser.
// This happens if the symlink couldn't happen, or if set as default in Admin
$zco_notifier->notify('NOTIFY_DOWNLOAD_READY_TO_STREAM', array(), $service, $origin_filename, $browser_filename, $source_directory, $downloadFilesize);

$zco_notifier->notify('NOTIFY_HEADER_END_DOWNLOAD');

// finally, upon completion of the download, the script should end here and not attempt to display any template components etc.
zen_exit();
