<?php
/**
 * ValueObject
 *
 * @package    Common
 * @copyright  Copyright 2003-2013 Zen Cart Development Team
 * @license    http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version    1.6.0
 */

namespace ZenCart\Common;

/**
 * A read-only value object for arrays
 *
 * @package Common
 */
class ValueObject {

  /**
   * @var array
   */
  private $data;


  /**
   * Constructor
   *
   * @param array $data the array
   * @return void
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

  /**
   * Magic getter
   *
   * @param string $property the property to get
   * @return mixed
   * @throws \InvalidArgumentException
   */
  public function __get($property) {
    if (key_exists($property, $this->data)) {
      return $this->data[$property];
    }
    throw new \InvalidArgumentException("Invalid property [$property]");
  }

  /**
   * Magic isset
   *
   * @param string $property the property to check
   * @return boolean
   */
  public function __isset($property) {
    return isset($this->data[$property]);
  }

  /**
   * Magic setter
   *
   * @param string $property the property to set
   * @param mixed  $value    the value to set
   * @throws \BadMethodCallException
   */
  public final function __set($property, $value) {
    throw new \BadMethodCallException("Values cannot be modified");
  }

  /**
   * Magic unsetter
   *
   * @param string $property the property to unset
   * @throws \BadMethodCallException
   */
  public final function __unset($property) {
    throw new \BadMethodCallException("Values cannot be modified");
  }

}
