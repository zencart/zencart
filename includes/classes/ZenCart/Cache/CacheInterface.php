<?php
/**
 * CacheInterface
 *
 * @package   Cache
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @since     1.6.0
 */

namespace ZenCart\Cache;

/**
 * A common cache object contract
 *
 * @package Cache
 * @since   1.6.0
 */
interface CacheInterface {

  /**
   * Does a key exist?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function exists($key);

  /**
   * Is a key expired?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function isExpired($key);

  /**
   * Expire a key
   *
   * @param string $key an identifier
   * @return boolean true if successful
   */
  public function expire($key);

  /**
   * Store data
   *
   * @param string $key  an identifier
   * @param mixed  $data the data to store
   * @return boolean true if successful
   */
  public function store($key, $data);

  /**
   * Read data
   *
   * @param string $key an identifier
   * @return mixed
   */
  public function read($key);

  /**
   * Reset the cache
   *
   * @return boolean true if successful
   */
  public function flush();

  /**
   * Receive a notifier's event
   *
   * @param object $subject the notifier
   * @param string $event   the event
   * @return void
   */
  public function update($subject, $event);

}
