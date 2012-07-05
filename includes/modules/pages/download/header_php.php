<?php
/**
 * download header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 18964 2011-06-22 19:58:38Z drbyte $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_DOWNLOAD');

define('SYMLINK_GARBAGE_COLLECTION_THRESHOLD', 1*60*60); // 1 hour default


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// if the customer is not logged on, redirect them to the time out page
if (!$_SESSION['customer_id']) {
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// Check download.php was called with proper GET parameters
if ((isset($_GET['order']) && !is_numeric($_GET['order'])) || (isset($_GET['id']) && !is_numeric($_GET['id'])) ) {
  // if the paramaters are wrong, redirect them to the time out page
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// Check that order_id, customer_id and filename match
$sql = "SELECT date_format(o.date_purchased, '%Y-%m-%d')
          AS date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_maxdays, opd.orders_products_filename
          FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
          WHERE o.customers_id = customersID
          AND o.orders_id = ordersID
          AND o.orders_id = op.orders_id
          AND op.orders_products_id = opd.orders_products_id
          AND opd.orders_products_download_id = downloadID
          AND opd.orders_products_filename != ''";

$sql = $db->bindVars($sql, 'customersID', $_SESSION['customer_id'], 'integer');
$sql = $db->bindVars($sql, 'downloadID', $_GET['id'], 'integer');
$sql = $db->bindVars($sql, 'ordersID', $_GET['order'], 'integer');
$downloads = $db->Execute($sql);
if ($downloads->RecordCount() <= 0 ) die;

$zco_notifier->notify('NOTIFY_CHECK_DOWNLOAD_HANDLER', array($downloads));

// MySQL 3.22 does not have INTERVAL, so must calculate dates with PHP:
list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads->fields['download_maxdays'], $dt_year);

// Die if time expired (maxdays = 0 means no time limit)
if (($downloads->fields['download_maxdays'] != 0) && ($download_timestamp <= time())) {
  zen_redirect(zen_href_link(FILENAME_DOWNLOAD_TIME_OUT));
}
// Die if remaining count is <=0 (maxdays = 0 means no time limit)
if ($downloads->fields['download_count'] <= 0 and $downloads->fields['download_maxdays'] != 0) {
  zen_redirect(zen_href_link(FILENAME_DOWNLOAD_TIME_OUT));
}

// FIX HERE AND GIVE ERROR PAGE FOR MISSING FILE
// Die if file is not there
if (!file_exists(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename'])) die('Sorry. File not found. Please contact the webmaster to report this error.<br />c/f: ' . $downloads->fields['orders_products_filename']);

// Now decrement counter (probably should skip this if download_maxdays = 0, ie: unlimited) -- move it up to lines 48-54?
$sql = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
          SET download_count = download_count-1
          WHERE orders_products_download_id = :downloadID";

$sql = $db->bindVars($sql, ':downloadID', $_GET['id'], 'integer');
$db->Execute($sql);

// Returns a random name, 16 to 20 characters long
// There are more than 10^28 combinations
// The directory is "hidden", i.e. starts with '.'
function zen_random_name()
{
  $letters = 'abcdefghijklmnopqrstuvwxyz';
  $dirname = '.';
  if (defined('DOWNLOADS_SKIP_DOT_PREFIX_ON_REDIRECT') && DOWNLOADS_SKIP_DOT_PREFIX_ON_REDIRECT === TRUE) $dirname = '';
  $length = floor(zen_rand(16,20));
  for ($i = 1; $i <= $length; $i++) {
    $q = floor(zen_rand(1,26));
    $dirname .= $letters[$q];
  }
  return $dirname;
}

// Unlinks all subdirectories and files in $dir
// Works only on one subdir level, will not recurse
function zen_unlink_temp_dir($dir)
{
  $h1 = opendir($dir);
  while ($subdir = readdir($h1)) {
    // Ignore non directories
    if (!is_dir($dir . $subdir)) continue;
    // Ignore . and .. and .svn
    if ($subdir == '.' || $subdir == '..' || $subdir == '.svn') continue;
    // Loop and unlink files in subdirectory
    $h2 = opendir($dir . $subdir);
    list($fn, $exptime) = explode('-', $subdir);
    if ($exptime + SYMLINK_GARBAGE_COLLECTION_THRESHOLD > time()) continue;
    while ($file = readdir($h2)) {
      if ($file == '.' || $file == '..') continue;
      @unlink($dir . $subdir . '/' . $file);
    }
    closedir($h2);
    @rmdir($dir . $subdir);
  }
  closedir($h1);
}

// disable gzip output buffering if active:
@ob_end_clean();
if (@ini_get('zlib.output_compression')) @ini_set('zlib.output_compression', 'Off');

// determine filename for download
$origin_filename = $downloads->fields['orders_products_filename'];
$browser_filename = str_replace(' ', '_', $origin_filename);
if (strstr($browser_filename, '/')) $browser_filename = substr($browser_filename, strrpos($browser_filename, '/')+1);
if (strstr($browser_filename, '\\')) $browser_filename = substr($browser_filename, strrpos($browser_filename, '\\')+1);
if (substr(DIR_FS_DOWNLOAD, -1) != '/') $origin_filename = '/' . $origin_filename;
if (!file_exists(DIR_FS_DOWNLOAD . $origin_filename)) {
  $msg = 'DOWNLOAD PROBLEM: Problems detected with download for ' . DIR_FS_DOWNLOAD . $origin_filename . ' because the file could not be found on the server. If the file exists, then its permissions are too low for PHP to access it. Contact your hosting company for specific help in determining correct permissions to make the file readable by PHP.';
  zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
}
$downloadFilesize = @filesize(DIR_FS_DOWNLOAD . $origin_filename);
if (!isset($downloadFilesize) || ($downloadFilesize < 1)) {
  $msg = 'DOWNLOAD PROBLEM: Problem detected with download for ' . DIR_FS_DOWNLOAD . $origin_filename . ' because the server is preventing PHP from reading the file size attributes, or the file is actually 0 bytes in size (which suggests the uploaded file is damaged or incomplete). Perhaps its permissions are too low for PHP to access it? Contact your hosting company for specific help in determining correct permissions to allow PHP to stat the file using the filesize() function.';
  zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
}


    /**
     * Browser detection
     * Presently only checks for IE-specific cases.
     */
    $detectedBrowser = '';
    if (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']))
    {
      $version = explode(' ', stristr($_SERVER['HTTP_USER_AGENT'], 'msie'));
      if ((int)$version[1] == 5) $detectedBrowser = 'IE5';
      if ((int)$version[1] == 6) $detectedBrowser = 'IE6';
      if ((int)$version[1] == 7) $detectedBrowser = 'IE7';
      if ((int)$version[1] == 8) $detectedBrowser = 'IE8';
      if ((int)$version[1] == 9) $detectedBrowser = 'IE9';
    }


    /**
     * set notifier point ... we are ready to begin the actual download
     */
    $zco_notifier->notify('NOTIFY_DOWNLOAD_READY_TO_START', $origin_filename, $browser_filename, $downloadFilesize, $_SESSION['customers_host_address']);


    /**
     * Check whether any headers have already been set, because that will cause download problems:
     */
    $hfile = $hline = '';
    if (headers_sent($hfile, $hline)) {
      $msg = 'DOWNLOAD PROBLEM: Cannot begin download for ' . $origin_filename . ' because HTTP headers were already sent. This indicates a PHP error, probably in a language file.  Start by checking ' . $hfile . ' on line ' . $hline . '.';
      zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, $msg, STORE_NAME, EMAIL_FROM);
    }

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
    header("Content-Type: application/force-download");

    header('Content-Disposition: attachment; filename="' . urlencode($browser_filename) . '"');

//     relocated below
//     if ((int)$downloadFilesize > 0) header("Content-Length: " . (string) $downloadFilesize);

    header("Expires: Mon, 22 Jan 2002 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
    switch($detectedBrowser)
    {
      case 'IE5':
      case 'IE6':
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", FALSE);
        header("Cache-Control: max-age=1");  // stores for only 1 second, which helps allow SSL downloads to work more reliably in IE
      break;
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


// Redirect usually will work only on Unix/Linux hosts since Windows hosts can't do symlinking in PHP versions older than 5.3.0
if (DOWNLOAD_BY_REDIRECT == 'true') {
  zen_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
  $tempdir = zen_random_name() . '-' . time();;
  umask(0000);
  mkdir(DIR_FS_DOWNLOAD_PUBLIC . $tempdir, 0777);
  $download_link = str_replace(array('/','\\'),'_',$browser_filename);
  $link_create_status = @symlink(DIR_FS_DOWNLOAD . $origin_filename, DIR_FS_DOWNLOAD_PUBLIC . $tempdir . '/' . $download_link);

  if ($link_create_status==true) {
    $zco_notifier->notify('NOTIFY_DOWNLOAD_VIA_SYMLINK___BEGINS', $download_link, $origin_filename, $tempdir);
    header("HTTP/1.1 303 See Other");
    zen_redirect(DIR_WS_DOWNLOAD_PUBLIC . $tempdir . '/' . $download_link, 303);
  }
}

if (DOWNLOAD_BY_REDIRECT != 'true' or $link_create_status==false ) {
  // not downloading by redirect; instead, we stream it to the browser.
  // This happens if the symlink couldn't happen, or if set as default in Admin

  if ((int)$downloadFilesize > 0) header("Content-Length: " . (string) $downloadFilesize);

  $disabled_funcs = @ini_get("disable_functions");
  if (DOWNLOAD_IN_CHUNKS != 'true' && !strstr($disabled_funcs,'readfile')) {
    $zco_notifier->notify('NOTIFY_DOWNLOAD_WITHOUT_REDIRECT___COMPLETED', $origin_filename);
    // This will work on all systems, but will need considerable resources
    readfile(DIR_FS_DOWNLOAD . $origin_filename);
  } else {
    // override PHP timeout to 25 minutes, if allowed
    @set_time_limit(1500);
    $zco_notifier->notify('NOTIFY_DOWNLOAD_IN_CHUNKS___COMPLETED', $origin_filename);
    // loop with fread($fp, xxxx) to allow streaming in chunk sizes below the PHP memory_limit
    $handle = @fopen(DIR_FS_DOWNLOAD . $origin_filename, "rb");
    if ($handle) {
      while (!@feof($handle)) {
        echo(fread($handle, 4096));
        @flush();
      }
      fclose($handle);
    } else {
      // Throw error condition -- this should never happen!
      $messageStack->add_session('default', 'Please contact store owner.  ERROR: Cannot read file: ' . $origin_filename, 'error');
      zen_mail('', STORE_OWNER_EMAIL_ADDRESS, ERROR_CUSTOMER_DOWNLOAD_FAILURE, "Unable to open file '" . $origin_filename . " for reading.  Check the file permissions.", STORE_NAME, EMAIL_FROM);
    }
    $zco_notifier->notify('NOTIFY_DOWNLOAD_WITHOUT_REDIRECT_VIA_CHUNKS___COMPLETED');
  }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_DOWNLOAD');

// finally, upon completion of the download, the script should end here and not attempt to display any template components etc.
zen_exit();
