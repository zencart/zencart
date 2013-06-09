<?php
/**
 * AbstractDatabaseCache
 *
 * @package   Cache
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @since     1.6.0
 */

namespace ZenCart\Cache;

use ZenCart\Database\ConnectionInterface;

/**
 * A file-based cache implementation
 *
 * @package Cache
 * @since   1.6.0
 */
abstract class AbstractDatabaseCache implements CacheInterface {

  const TABLE_DEFAULT        = 'db_cache';
  const FIELD_FILTER_DEFAULT = 'cache_entry_name';
  const FIELD_DATA_DEFAULT   = 'cache_data';

  /**
   * @var string
   */
  private $table = self::TABLE_DEFAULT;

  /**
   * @var string
   */
  private $filterField = self::FIELD_FILTER_DEFAULT;

  /**
   * @var string
   */
  private $dataField = self::FIELD_DATA_DEFAULT;

  /**
   * @var ConnectionInterface
   */
  private $connection;

  /**
   * @var array
   */
  private $results = array();


  /**
   * Constructor
   *
   * @param ConnectionInterface $connection  the database connection
   * @param string              $table       the cache table
   * @param string              $filterField the filter field
   * @param string              $dataField   the data field
   * @return void
   */
  public function __construct(
    ConnectionInterface $connection,
    $table       = self::TABLE_DEFAULT,
    $filterField = self::FIELD_FILTER_DEFAULT,
    $dataField   = self::FIELD_DATA_DEFAULT
  ) {
    $this->setConnection($connection)
         ->setTable($table)
         ->setFilterField($filterField)
         ->setDataField($dataField);
  }

  /**
   * Does a key exist?
   *
   * @param string $key an identifier
   * @return boolean
   */
  public function exists($key) {
    $data = $this->select($key);
    return isset($data);
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
    if ($success = $this->connection->Execute(
      sprintf(
        "DELETE FROM %s WHERE %s = '%s'",
        $this->table,
        $this->filterField,
        md5($key)
      )
    )) {
      unset($this->results[$key]);
    }
    return $this->exists($key);
  }

  /**
   * Store data
   *
   * @param string $key  an identifier
   * @param mixed  $data the data to store
   * @return boolean true if successful
   */
  public function store($key, $data) {
    $type = ConnectionInterface::PERFORM_TYPE_INSERT;
    if ($this->exists($key)) {
      $type = ConnectionInterface::PERFORM_TYPE_UPDATE;
    }
    $data = array(
      $this->filterField => md5($key),
      $this->dataField   => serialize($data)
    );
    return ($this->connection->perform($this->table, $data, $type));
  }

  /**
   * Read data
   *
   * @param string $key an identifier
   * @return mixed
   */
  public function read($key) {
    if ($data = $this->select($key)) {
      return unserialize($data->fields[$this->dataField]);
    }
  }

  /**
   * Reset the cache
   *
   * @return boolean true if successful
   */
  public function flush() {
    return $this->connection->Execute(
      sprintf("DELETE FROM %s", $this->table)
    );
  }

  /**
   * Generate a unique cache id
   *
   * @param string $key the key to identify
   * @return string
   */
  protected function select($key) {
    if (!isset($this->results[$key])) {
      $result = $this->connection->Execute(
        sprintf(
          "SELECT %s FROM %s WHERE %s = '%s'",
          $this->dataField,
          $this->table,
          $this->filterField,
          md5($key)
        )
      );
      $this->results[$key] = $result;
    }
    return $this->results[$key];
  }

  /**
   * Get the connection
   *
   * @return ConnectionInterface
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * Set the connection
   *
   * @param ConnectionInterface $connection the database connection
   * @return AbstractDatabaseCache
   */
  public function setConnection(ConnectionInterface $connection) {
    $this->connection = $connection;
    return $this;
  }

  /**
   * Get the table
   *
   * @return string
   */
  public function getTable() {
    return $this->table;
  }

  /**
   * Set the table
   *
   * @param string $table the cache table
   * @return AbstractDatabaseCache
   */
  public function setTable($table) {
    $this->table = (string) $table;
    return $this;
  }

  /**
   * Get the filter field
   *
   * @return string
   */
  public function getFilterField() {
    return $this->filterField;
  }

  /**
   * Set the filter field
   *
   * @param string $field the filter field
   * @return AbstractDatabaseCache
   */
  public function setFilterField($filterField) {
    $this->filterField = (string) $filterField;
    return $this;
  }

  /**
   * Get the data field
   *
   * @return string
   */
  public function getDataField() {
    return $this->dataField;
  }

  /**
   * Set the data field
   *
   * @param string $field the data field
   * @return AbstractDatabaseCache
   */
  public function setDataField($dataField) {
    $this->dataField = (string) $dataField;
    return $this;
  }

}
