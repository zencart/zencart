<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sat Dec 23 12:42:13 2017 -0500 New in v1.5.6 $
 */

/**
 * This observer class is intended to allow downloadable files to be served
 * from Amazon AWS S3 buckets, and also automatically expire the links
 * so that customers can't share them or otherwise steal the files
 *
 */
class zcObserverDownloadsViaAws extends base {

  // this is where you can configure your AWS settings:
  // --------------------------------------------------
  // Alternatively, you can define them as constants in
  // the extra_configures folder: AMAZON_S3_ACCESS_KEY
  // and AMAZON_S3_ACCESS_SECRET
  // --------------------------------------------------
  /**
   * You may set your Amazon AWS S3 Access Key and Secret Key here, as long as you're not committing this file to a version-control system like git.
   * @var string
   */
  private $aws_key = 'MY_AMAZON_S3_ACCESS_KEY';
  /**
   * @var string
   */
  private $aws_secret = 'MY_AMAZON_S3_SECRET_XXXXXXXXX';

  /**
   * This is used to calculate a link that's good for 30 seconds,
   * which is plenty of time for it to get started, & prevents
   * unauthorized sharing and theft. Default is 30 seconds.
   * @var integer
   */
  private $link_expiry_time = 30;

  /**
   * URL to Amazon S3 server
   * @var string URL
   */
  private $aws_server = 'https://s3.amazonaws.com';

  /**
   * Class constructor
   */
  public function __construct() {
    // read config from constants if available
    if ($this->aws_key === 'MY_AMAZON_S3_ACCESS_KEY' && defined('AMAZON_S3_ACCESS_KEY')) $this->aws_key = AMAZON_S3_ACCESS_KEY;
    if ($this->aws_secret === 'MY_AMAZON_S3_SECRET_XXXXXXXXX' && defined('AMAZON_S3_ACCESS_SECRET')) $this->aws_secret = AMAZON_S3_ACCESS_SECRET;

    // if not configured, then don't activate
    if ($this->aws_key === 'MY_AMAZON_S3_ACCESS_KEY' || $this->aws_key === '' || $this->aws_secret === '' || $this->aws_secret === 'MY_AMAZON_S3_SECRET_XXXXXXXXX') return false;

    // attach listener
    $this->attach($this, array('NOTIFY_CHECK_DOWNLOAD_HANDLER', 'NOTIFY_DOWNLOAD_READY_TO_START', 'NOTIFY_MODULE_DOWNLOAD_TEMPLATE_DETAILS', 'NOTIFY_TEST_DOWNLOADABLE_FILE_EXISTS'));
  }


  /**
   * Parse the file details for display on template page
   *
   * @param string $eventID name of the observer event fired
   * @param array $array $download->fields data
   * @param array $data array passed by reference
   */
  protected function updateNotifyModuleDownloadTemplateDetails(&$class, $eventID, $array, &$data)
  {
    // available fields:
    //   $data['service'] = 'local'
    //   $data['filename'] = db query result from orders_products_filename
    //   $data['expiry_timestamp']
    //   $data['expiry']
    //   $data['downloads_remaining']
    //   $data['unlimited_downloads']
    //   $data['file_exists']
    //   $data['is_downloadable']
    //   $data['filesize']
    //   $data['date_purchased_day']
    //   $data['download_maxdays']
    //   $data['products_name']
    //   $data['orders_products_download_id'] = id for URL link
    //   $data['download_count']

    $file_parts = $this->parseFileParts($data['filename']);

    if ($file_parts === false) return;
    if ($file_parts[0] != 'aws') return;

    $data['service'] = $file_parts[0];

    // use just the filename portion, skipping the bucket name for customer-facing display purposes
    $data['filename'] = substr($file_parts[1], strrpos($file_parts[1], '/') + 1);

    $data['filesize'] = isset($file_parts[2]) ? number_format($file_parts[2], 0) : '';
    $data['filesize_units'] = '';

    $data['is_downloadable'] = $data['file_exists'] = $this->testFileExists($data['filename']);
  }

  /**
   * This observer should set $handler to blank if it fails to validate whether $filename exists on the external service.
   * If validation passes, simply set $handler to the service name (first chars before first colon in filename) (or do nothing since it's probably already correct).
   * If there is no way to verify, do nothing to $handler.
   *
   * @param string $eventID name of the observer event fired
   * @param string $filename filename to verify exists
   * @param string $handler  name of external service handler
   */
  protected function updateNotifyTestDownloadableFileExists(&$class, $eventID, $filename, &$handler)
  {
    $result = $this->testFileExists($filename);

    if ($result === false) {
      $handler = '';
    }
  }

  /**
   *
   * @param string $eventID name of the observer event fired
   * @param array $var deprecated array, used only for backward compatibility
   * @param array $fields data feeding all download activities
   * @param string $origin_filename  (mutable)
   * @param string $browser_filename (mutable)
   * @param string $source_directory (mutable)
   * @param boolean $file_exists (mutable)
   */
  protected function updateNotifyCheckDownloadHandler(&$class, $eventID, $var, &$fields, &$origin_filename, &$browser_filename, &$source_directory, &$file_exists, &$service, &$isExpired, &$download_timestamp)
  {
    $file_parts = $this->parseFileParts($origin_filename);
    if ($file_parts[0] == 'aws') {
      $origin_filename  = $file_parts[1];
      $browser_filename = substr($origin_filename, strrpos($origin_filename, '/') + 1);
      $source_directory = $file_parts[0];
      $file_exists = true;
      $service = $file_parts[0];
    }
  }

  /**
   * This fires when the download module wants to redirect to the external download service
   * So, this method parses the passed file, obtains the URL, and does the redirect
   *
   * @param string $eventID name of the observer event fired
   * @param string $ipaddress customer IP
   * @param string $service (mutable)
   * @param string $origin_filename (mutable)
   * @param string $browser_filename (mutable)
   * @param string $source_directory (mutable)
   * @param integer $downloadFilesize (mutable)
   * @param string $mime_type (mutable)
   * @param array $fields  array of data from db query feeding the download page
   * @param string $browser_extra_headers (mutable)
   */
  protected function updateNotifyDownloadReadyToStart(&$class, $eventID, $ipaddress, &$service, &$origin_filename, &$browser_filename, &$source_directory, &$downloadFilesize, $mime_type, $fields, $browser_extra_headers)
  {
    // verify that the passed file is indeed intended for aws
    if ($source_directory != 'aws') {
      $file_parts = $this->parseFileParts($origin_filename);
      if ($file_parts[0] != 'aws') return false;
      $origin_filename  = $file_parts[1];
      $browser_filename = substr($origin_filename, strrpos($origin_filename, '/') + 1);
      $source_directory = $file_parts[0];
      $downloadFilesize = $file_parts[2];
    }

    // prepare AWS URL
    $url = $this->buildRedirectUrl($origin_filename);

    // redirect to external download script
    header("HTTP/1.1 303 See Other");
    zen_redirect($url);

    zen_exit();
  }

  /**
   * parse file details to determine if its download should be handled by AWS
   * If AWS, the filename will use colons as delimiters ... aws:bucket/filename:filesize
   *
   * @param string $filename
   * @return boolean|array
   */
  private function parseFileParts($filename) {

    $file_parts = explode(':', $filename);

    if (count($file_parts) === 1) return false;

    return $file_parts;
  }

  /**
   * Prepare signed expiring URL for AWS redirect
   *
   * @param string $bucketAndFilename
   * @return string $url
   */
  private function buildRedirectUrl($bucketAndFilename) {

    // this calculates a link that's good for 30 seconds, which is plenty of time for it to get started, and prevents theft
    $expires = time() + $this->link_expiry_time;

    $raw_request = "GET\n\n\n" . $expires . "\n/" . $bucketAndFilename;
    $sig = urlencode(base64_encode((hash_hmac("sha1", utf8_encode($raw_request), $this->aws_secret, true))));

    $params = 'AWSAccessKeyId=' . $this->aws_key . '&Expires=' . $expires . '&Signature=' . $sig;

    return $this->aws_server . '/' . $bucketAndFilename . '?' . $params;
  }

  /**
   * Use AWS SDK to test whether the bucket+file (designated by $filename) exists
   * If it does not exist, return false
   *
   * @param string $filename
   * @return boolean Result of SDK test
   */
  private function testFileExists($filename)
  {
    // @TODO: could optionally add an AWS SDK call to actually check that the object (bucket+file) exists
    // but for now we're simply assuming that it does
    return true;
  }

}
