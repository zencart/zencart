<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Sep 12 Modified in v1.5.7 $
 */

/**
 * This observer class is intended to allow downloadable files to be served
 * by redirecting the customer's browser page to a temporary symlink on
 * the server; the symlinked file expires to help prevent theft
 */
class zcObserverDownloadsViaRedirect extends base {

  /**
   * Folder where the symlink redirect folders will be generated. The folder requires "writable by PHP" permissions.
   * This is the path from the root of the filesystem.
   * @var string
   */
  private $pubFolder = DIR_FS_DOWNLOAD_PUBLIC;

  /**
   * Folder off the webroot where the "pub" symlinks will be accessible. Usually this is "pub".
   * @var string
   */
  private $wsPubFolder = 'pub';

  /**
   * Number of seconds before garbage-collection purges
   * the leftover symlink folders.
   * Default = 3600 = 1 hour
   *
   * @var integer
   */
  protected $gc_cleanup_time = 3600;

  /**
   * Class constructor
   */
  public function __construct()
  {

    if (DOWNLOAD_BY_REDIRECT != 'true') return false;

    $this->pubFolder = DIR_FS_DOWNLOAD_PUBLIC;
    $this->wsPubFolder = HTTP_SERVER . DIR_WS_DOWNLOAD_PUBLIC;

    // attach listener
    $this->attach($this, array('NOTIFY_DOWNLOAD_READY_TO_REDIRECT'));

    $this->gc_cleanup_time = 0;
    if (defined('SYMLINK_GARBAGE_COLLECTION_THRESHOLD') && (int)SYMLINK_GARBAGE_COLLECTION_THRESHOLD > 300) {
        $this->gc_cleanup_time = (int)SYMLINK_GARBAGE_COLLECTION_THRESHOLD;
    }
  }

  /**
   * This fires when the download module is ready to process redirects
   *
   * @param string $eventID name of the observer event fired
   * @param array $array deprecated BC data
   * @param string $origin_filename (mutable)
   * @param string $browser_filename (mutable)
   * @param string $source_directory (mutable)
   * @param boolean $link_create_status (mutable)
   */
  protected function updateNotifyDownloadReadyToRedirect(&$class, $eventID, $array, &$service, &$origin_filename, &$browser_filename, &$source_directory, &$link_create_status)
  {
    if (!defined('DOWNLOAD_CHMOD')) define('DOWNLOAD_CHMOD', '0777');
    $this->garbageCollectionUnlinkTempFolders($this->pubFolder);
    $tempdir = $this->generateRandomName() . '-' . time();
    umask(0000);
    mkdir($this->pubFolder . $tempdir, octdec(DOWNLOAD_CHMOD));
    $download_link = str_replace(array('/','\\'), '_', $browser_filename);
    $link_create_status = @symlink($source_directory . $origin_filename, $this->pubFolder . $tempdir . '/' . $download_link);

    if ($link_create_status==true) {
      $this->notify('NOTIFY_DOWNLOAD_VIA_SYMLINK___BEGINS', array($download_link, $origin_filename, $tempdir));
      header("HTTP/1.1 303 See Other");
      zen_redirect($this->wsPubFolder . $tempdir . '/' . $download_link, 303);
      zen_exit();
    }
  }

  /**
   * Returns a random name, 16 to 20 characters long
   * There are more than 10^28 combinations
   * This is used to build a random directory foldername. And, the directory is "hidden", ie: starts with '.'
   * @return string
   */
  private function generateRandomName()
  {
    $letters = 'abcdefghijklmnopqrstuvwxyz';
    $dirname = '.';
    if (defined('DOWNLOADS_SKIP_DOT_PREFIX_ON_REDIRECT') && DOWNLOADS_SKIP_DOT_PREFIX_ON_REDIRECT === TRUE) $dirname = '';
    $length = floor(zen_rand(16,20));
    for ($i = 1; $i <= $length; $i++) {
      $q = (int)floor(zen_rand(0,25));
      $dirname .= $letters[$q];
    }
    return $dirname;
  }

  /**
   * Garbage collection for temporary download files/folders
   *
   * Unlinks (deletes) all subdirectories and files in $dir
   * Works only on one subdir level, will not recurse
   *
   * @param string $dir folder whose contents will be inspected for cleanup
   */
  private function garbageCollectionUnlinkTempFolders($dir)
  {
    $h1 = opendir($dir);
    while ($subdir = readdir($h1)) {
      // Ignore non directories
      if (!is_dir($dir . $subdir) || $subdir == '.' || $subdir == '..') continue;
      // Loop and unlink files in subdirectory
      if ($h2 = opendir($dir . $subdir)) {
          list($fn, $exptime) = explode('-', $subdir);
          if ($exptime + $this->gc_cleanup_time > time()) continue;
          while ($file = readdir($h2)) {
              if ($file == '.' || $file == '..') continue;
              @unlink($dir . $subdir . '/' . $file);
          }
          closedir($h2);
      }
      @rmdir($dir . $subdir);
    }
    closedir($h1);
  }

}
