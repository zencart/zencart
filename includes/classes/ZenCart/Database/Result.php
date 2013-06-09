<?php
/**
 * Result
 *
 * @package   Database
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace ZenCart\Database;

/**
 * A query result
 *
 * @package Database
 * @since   1.6.0
 */
class Result implements ResultInterface {

  /**
   * @var integer
   */
  private $cursor = 0;

  /**
   * @var array
   */
  private $data;

  /**
   * @var boolean
   */
  private $randomized = false;

  /**
   * @var boolean
   * @deprecated
   */
  public $EOF = false;

  /**
   * @var array
   * @deprecated
   */
  public $fields = array();


  /**
   * Constructor
   *
   * @param array $data the result data
   * @return void
   */
  public function __construct(array $data) {
    $this->data = $data;
    $this->current();
  }

  /**
   * Seeks to a position
   *
   * @param integer $position The position to seek to
   * @return void
   * @throws \OutOfBoundsException
   */
  public function seek($position) {
    if (key_exists($position, $this->data)) {
      $this->cursor = $position;
    } else {
      throw new \OutOfBoundsException("Invalid position [$position]");
    }
    $this->current();
  }

  /**
   * Return the current element
   *
   * @return mixed
   */
  public function current() {
    if ($this->valid()) {
      $this->fields = $this->data[$this->cursor];
      return $this->fields;
    }
  }

  /**
   * Return the key of the current element
   *
   * @return scalar
   */
  public function key() {
    return $this->cursor;
  }

  /**
   * Move forward to the next element
   *
   * @return void
   */
  public function next() {
    ++$this->cursor;
    $this->current();
  }

  /**
   * Rewind the iterator to the first element
   *
   * @return void
   */
  public function rewind() {
    $this->cursor = 0;
  }

  /**
   * Checks if the current position is valid
   *
   * @return boolean
   */
  public function valid() {
    $this->EOF = ($this->cursor >= $this->count());
    return !$this->EOF;
  }

  /**
   * Count the data
   *
   * @return integer
   */
  public function count() {
    return count($this->data);
  }

  /**
   * Randomize the data
   *
   * @return Result
   */
  public function randomize() {
    if ($this->randomized == false) {
      $this->randomized = shuffle($this->data);
    }
    return $this;
  }

  /**
   * Convert the result to an array
   *
   * @return array
   */
  public function toArray() {
    return $this->data;
  }

  /**
   * Serialize the data
   *
   * @return string
   */
  public function serialize() {
    return serialize(
      array(
        'randomized' => (int) $this->randomized,
        'data'       => $this->data
      )
    );
  }

  /**
   * Unserialize the data
   *
   * @param string $serialized the serialized data
   * @return void
   */
  public function unserialize($serialized) {
    $result = unserialize($serialized);
    $this->randomized = (bool) $result['randomized'];
    $this->data       = $result['data'];
    $this->current();
  }

  /**
   * Alias for next()
   *
   * @deprecated
   * @return void
   */
  function MoveNext() {
    $this->next();
  }

  /**
   * Alias for randomize()
   *
   * @deprecated
   * @return void
   */
  function MoveNextRandom() {
    $this->randomize()->next();
  }

  /**
   * Alias for count()
   *
   * @deprecated
   * @return integer
   */
  function RecordCount() {
    return $this->count();
  }

  /**
   * Alias for seek()
   *
   * @deprecated
   * @return void
   */
  function Move($row) {
    $this->seek($row);
  }

}
