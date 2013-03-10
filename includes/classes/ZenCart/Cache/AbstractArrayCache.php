<?php
/**
 * AbstractArrayCache
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
abstract class AbstractArrayCache implements CacheInterface {

  /**
   * @var array
   */
  private $guids = array();

  private $cachedData = array();


  /**
   * Does a key exist?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function exists($key) {
    return isset($this->cachedData[$this->generateId($key)]);
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
    unset($this->cachedData[$this->generateId($key)]);
    return true;
  }

  /**
   * Store data
   *
   * @param string $key  an identifier
   * @param mixed  $data the data to store
   * @return boolean true if successful
   */
  public function store($key, $data) {
    $this->cachedData[$this->generateId($key)] = $data;
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
      return $this->cachedData[$this->generateId($key)];
    }
  }

  /**
   * Reset the cache
   *
   * @return boolean true if successful
   */
  public function flush() {
    $this->cachedData = array();
    return true;
  }

  /**
   * Generate a unique cache id
   *
   * @param string $key the key to identify
   * @return string
   */
  protected function generateId($key) {
    if (!isset($this->guids[$key])) {
      $this->guids[$key] = md5($key . rand(0, strlen($key)));
    }
    return $this->guids[$key];
  }

}
