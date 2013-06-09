<?php
/**
 * ConnectionInterface
 *
 * @package   Database
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   1.6.0
 */

namespace ZenCart\Database;

/**
 * A common object contract for database connections
 *
 * @package Database
 * @since   1.6.0
 */
interface ConnectionInterface {

  const PERFORM_TYPE_INSERT = 'INSERT';
  const PERFORM_TYPE_SELECT = 'SELECT';
  const PERFORM_TYPE_UPDATE = 'UPDATE';
  const PERFORM_TYPE_DELETE = 'DELETE';

  const EVENT_CONNECT_BEGIN = 'EVENT_CONNECT_BEGIN';
  const EVENT_CONNECT_END   = 'EVENT_CONNECT_END';
  const EVENT_QUERY_BEGIN   = 'EVENT_QUERY_BEGIN';
  const EVENT_QUERY_END     = 'EVENT_QUERY_END';

  const ERROR_NO_CONNECTION = 'No valid connection available';

  /**
   * Initialize a new connection
   *
   * @param string $host     the database host
   * @param string $user     the database identity
   * @param string $password the database credential
   * @param string $database the database name
   * @return boolean true if successful
   */
  public function connect($host, $user, $password, $database);

  /**
   * Close the connection
   *
   * @return void
   */
  public function close();

  /**
   * Execute a sql statement
   *
   * @param string  $sql   the sql statement
   * @param integer $limit the maximum number of results [0 = all]
   * @return ResultInterface
   */
  public function Execute($sql, $limit = 0);

  /**
   * Execute a randomized statement
   *
   * @param string  $sql   the statement
   * @param integer $limit the maximum number of results [0 = all]
   * @return ResultInterface
   */
  public function ExecuteRandomMulti($sql, $limit = 0);

  /**
   * Get the last insert id
   *
   * @return integer
   */
  public function insert_ID();

  /**
   * Perform a custom query
   *
   * @param string $table  the table to execute against
   * @param array  $data   the data
   * @param string $type   the statement type
   * @param string $filter the where clause
   * @return void
   */
  public function perform($table, array $data, $type = self::PERFORM_TYPE_INSERT, $filter = '');

  /**
   * Prepare a value for a sql statement
   *
   * @param mixed $value the value to prepare
   * @return mixed
   */
  public function prepareInput($value);

  /**
   * Get the current number of queries for this session
   *
   * @return integer
   */
  public function queryCount();

  /**
   * Get the time in miliseconds that queries have consumed
   *
   * @return integer
   */
  public function queryTime();

  /**
   * Allow an observer (i.e. cache) to set the current result
   *
   * @param mixed  $result   the current result
   * @param object $observer the observer
   * @return ConnectionInterface
   * @throws \InvalidArgumentException if $observer is not valid
   */
  public function setResult($result, $observer);

  /**
   * Get the current result
   *
   * @return mixed
   */
  public function getResult();

}
