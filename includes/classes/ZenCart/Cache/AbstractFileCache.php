<?php
/**
 * AbstractFileCache
 *
 * @package   Cache
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @since     1.6.0
 */

namespace ZenCart\Cache;

/**
 * A file-based cache implementation
 *
 * @package Cache
 * @since   1.6.0
 */
abstract class AbstractFileCache implements CacheInterface {

  const PREFIX_DEFAULT    = 'zc_';
  const EXTENSION_DEFAULT = '.sql';

  /**
   * @var string
   */
  private $directory;

  /**
   * @var string
   */
  private $idPrefix = self::PREFIX_DEFAULT;

  /**
   * @var string
   */
  private $fileExtension = self::EXTENSION_DEFAULT;

  /**
   * @var array
   */
  private $filenames = array();


  /**
   * Constructor
   *
   * @param string $directory     the cache directory
   * @param string $idPrefix      the id prefix
   * @param string $fileExtension the file extension
   * @return void
   */
  public function __construct(
    $directory,
    $idPrefix = self::PREFIX_DEFAULT,
    $fileExtension = self::EXTENSION_DEFAULT
  ) {
    $this->setDirectory($directory)
         ->setIdPrefix($idPrefix)
         ->setFileExtension($fileExtension);
  }

  /**
   * Does a key exist?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function exists($key) {
    return file_exists($this->generateFilename($key));
  }

  /**
   * Is a key expired?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function isExpired($key) {
    return !$this->exists($key);
  }

  /**
   * Expire a key
   *
   * @param string $key an identifier
   * @return boolean true if successful
   */
  public function expire($key) {
    return @unlink($this->generateFilename($key));
  }

  /**
   * Store data
   *
   * @param string $key  an identifier
   * @param mixed  $data the data to store
   * @return boolean true if successful
   */
  public function store($key, $data) {
    if ($fp = @fopen($this->generateFilename($key), 'w')) {
      fputs($fp, serialize($data));
      fclose($fp);
    }
    return $this->exists($key);
  }

  /**
   * Read data
   *
   * @param string $key an identifier
   * @return mixed
   */
  public function read($key) {
    if ($this->exists($key)) {
      $data = file($this->generateFilename($key));
      return unserialize(implode('', $data));
    }
  }

  /**
   * Reset the cache
   *
   * @return boolean true if successful
   */
  public function flush() {
    if ($dir = @dir($this->directory)) {
      while ($file = $dir->read()) {
        if (strpos($file, $this->idPrefix) !== false) {
          @unlink($this->directory . '/' . $file);
        }
      }
      return true;
    }
    return false;
  }

  /**
   * Generate a unique cache id
   *
   * @param string $key the key to identify
   * @return string
   */
  protected function generateFilename($key) {
    if (!isset($this->filenames[$key])) {
      $id = $this->idPrefix . md5($key);
      $this->filenames[$key] = $this->directory . "/{$id}{$this->fileExtension}";
    }
    return $this->filenames[$key];
  }

  /**
   * Get the directory
   *
   * @return string
   */
  public function getDirectory() {
    return $this->directory;
  }

  /**
   * Set the directory
   *
   * @param string $directory the cache directory
   * @return AbstractFileCache
   * @throws \InvalidArgumentException
   */
  public function setDirectory($directory) {
    if (!is_dir($directory) || !is_readable($directory) || !is_writable($directory)) {
      throw new \InvalidArgumentException("Invalid directory [$directory]");
    }
    $this->directory = realpath($directory);
    return $this;
  }

  /**
   * Get the id prefix
   *
   * @return string
   */
  public function getIdPrefix() {
    return $this->idPrefix;
  }

  /**
   * Set the id prefix
   *
   * @param string $prefix the id prefix
   * @return AbstractFileCache
   */
  public function setIdPrefix($idPrefix) {
    $this->idPrefix = (string) $idPrefix;
    return $this;
  }

  /**
   * Get the file extension
   *
   * @return string
   */
  public function getFileExtension() {
    return $this->fileExtension;
  }

  /**
   * Set the file extension
   *
   * @param string $suffix the id suffix
   * @return AbstractFileCache
   */
  public function setFileExtension($fileExtension) {
    $this->fileExtension = (string) $fileExtension;
    return $this;
  }

}
