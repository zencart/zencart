<?php
/**
 * Connection
 *
 * @package    Database
 * @subpackage Mysql
 * @copyright  Copyright 2003-2013 Zen Cart Development Team
 * @copyright  Portions Copyright 2003 osCommerce
 * @copyright  http://www.data-diggers.com/
 * @license    http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version    1.6.0
 * @since      1.5.1
 */

namespace ZenCart\Database\Mysql;

use ZenCart\Common\ValueObject;
use ZenCart\Database\ConnectionInterface;
use ZenCart\Database\Result;
use ZenCart\Database\ResultInterface;

/**
 * A Mysql connection
 *
 * @package    Database
 * @subpackage Mysql
 */
class Connection extends \base implements ConnectionInterface {

  const ERROR_MISSING_MYSQLI = 'mysqli is not installed';

  /**
   * @var resource
   */
  private $link;

  /**
   * @var integer
   */
  private $count_queries = 0;

  /**
   * @var integer
   */
  private $total_query_time = 0;

  /**
   * @var boolean
   */
  private $pConnect = true;

  /**
   * @var boolean
   */
  private $connected = false;

  /**
   * @var boolean
   */
  private $dieOnErrors = false;

  /**
   * @var string
   */
  private $currentSql;

  /**
   * @var mixed
   */
  private $result;


  /**
   * Constructor
   *
   * @TODO Register the cache and log as listeners during initialization
   *
   * @return void
   */
  public function __construct() {
    if (!function_exists('mysqli_connect')) {
      throw new \BadMethodCallException(static::ERROR_MISSING_MYSQLI);
    }
    $this->count_queries = 0;
    $this->total_query_time = 0;
  }

  /**
   * Perform a sql query
   *
   * @param string $sql the sql statement
   * @return mixed
   * @throws \RuntimeException
   */
  public function query($sql) {
    if (!$this->link) {
      throw new \RuntimeException(static::ERROR_NO_CONNECTION);
    }

    $this->result     = null;
    $this->currentSql = null;
    $query_time       = 0;

    $this->notify(static::EVENT_QUERY_BEGIN, compact('sql'));

    if (is_null($this->result)) {
      $this->count_queries++;
      $this->currentSql = $sql;

      $time_start = explode(' ', microtime());
      $result     = mysqli_query($this->link, $sql);
      $time_end   = explode(' ', microtime());
      $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];

      if ($this->link->error) {
        $this->set_error(mysqli_errno($this->link), mysqli_error($this->link));
      }

      if (is_a($result, 'mysqli_result')) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
          $rows[] = $row;
        }
        mysqli_free_result($result);
        $this->result = new Result($rows);
      } else {
        $this->result = $result;
      }
    }

    $this->total_query_time += $query_time;
    $this->notify(static::EVENT_QUERY_END, compact('sql', 'query_time'));

    return $this->result;
  }

  /**
   * Allow an observer (i.e. cache) to set the result
   *
   * @param mixed  $result   the current result
   * @param object $observer the observer setting the result
   * @return Connection
   * @throws \InvalidArgumentException
   */
  public function setResult($result, $observer) {
    // hacky work-around for poorly-implemented observer pattern
    $isObserver = false;
    if ($observers = $this->getStaticObserver()) {
      foreach ($observers as $base) {
        if ($base['obs'] === $observer) {
          $isObserver = true;
          break;
        }
      }
    }

    if ($isObserver) {
      $this->result = $result;
    } else {
      throw new \InvalidArgumentException(
        sprintf("Invalid observer [%s]", get_class($observer))
      );
    }

    return $this;
  }

  /**
   * Get the current result
   *
   * @return mixed
   */
  public function getResult() {
    return $this->result;
  }

  /**
   * Initiate a connection
   *
   * @param string  $host        the database host
   * @param string  $user        the database user
   * @param string  $password    the user's password
   * @param string  $database    the database name
   * @param boolean $pconnect    use a persistent connection?
   * @param boolean $dieOnErrors should errors cause the application to die?
   * @param array   $options     configuration options
   * @return boolean
   */
  public function connect($host, $user, $password, $database, $pconnect = true, $dieOnErrors = false, $options = array()) {
    $this->pConnect    = $pconnect;
    $this->dieOnErrors = $dieOnErrors;

    $connectionRetry = 10;
    while (!isset($this->link) || ($this->link == FALSE && $connectionRetry !=0)) {
      $this->simpleConnect($host, $user, $password, $database);
      $connectionRetry--;
    }

    if ($this->connected) {
      if (defined('DB_CHARSET')) {
        $dbCharset = DB_CHARSET;
      } elseif (isset($options['dbCharset'])) {
        $dbCharset = $options['dbCharset'];
      }

      if (isset($dbCharset) && version_compare($this->get_server_info(), '4.1.0', '>=')) {
        $this->query("SET NAMES '$dbCharset'");
        if (function_exists('mysqli_set_charset')) {
          @mysqli_set_charset($this->link, $dbCharset);
        } else {
          $this->query("SET CHARACTER SET '$dbCharset'");
        }
      }

      if (getenv('TZ') && !defined('DISABLE_MYSQL_TZ_SET')) {
        $this->query(
          "SET time_zone = '" . substr_replace(date("O"), ":", -2, 0) . "'"
        );
      }
    }

    return $this->connected;
  }

  /**
   * Initialize a simple connection
   *
   * @param string  $host        the database host
   * @param string  $user        the database user
   * @param string  $password    the user's password
   * @param string  $database    the database name
   * @return boolean
   */
  public function simpleConnect($host, $user, $password, $database) {
    $this->notify(static::EVENT_CONNECT_BEGIN, func_get_args());

    if ($this->link = @mysqli_connect($host, $user, $password, $database)) {
      $this->connected = true;
    } else {
      $this->set_error(mysqli_connect_errno(), mysqli_connect_error());
    }

    $this->notify(
      static::EVENT_CONNECT_END,
      array('connected' => $this->connected)
    );

    return $this->connected;
  }

  /**
   * Select a database
   *
   * @param string $database the database name
   * @return boolean
   */
  public function selectdb($database) {
    if ($result = @mysqli_select_db($this->link, $database)) {
      return $result;
    } else {
      $this->set_error(mysqli_errno($this->link), mysqli_error($this->link));
    }
    return false;
  }

  /**
   * Prepare a string for a sql statement
   *
   * @param string $string the string to prepare
   * @return string
   * @deprecated
   * @see prepareInput()
   */
  public function prepare_input($string) {
    return $this->prepareInput($string);
  }

  /**
   * Close the database connection
   *
   * @return boolean
   */
  public function close() {
    if ($success = @mysqli_close($this->link)) {
      unset($this->link);
      $this->connected = false;
    } elseif (isset($this->link)) {
      $this->set_error(mysqli_connect_errno(), mysqli_connect_error());
    }
    return $success;
  }

  /**
   * Set the error number and text
   *
   * @param integer $num  the error number
   * @param string  $text the error text
   * @return void
   */
  protected function set_error($num, $text) {
    $this->error_number = $num;
    $this->error_text   = $text;
    if ($this->dieOnErrors && $num != 1141) {
      // error 1141 is okay ... should not die on 1141, but just continue on instead
      $this->show_error();
      die();
    }
  }

  /**
   * Render the error
   *
   * @return void
   */
  public function show_error() {
    if ($this->error_number == 0 && $this->error_text == DB_ERROR_NOT_CONNECTED && !headers_sent() && file_exists('nddbc.html') ) include('nddbc.html');
    echo '<div class="systemError">';
    if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)
    {
      echo $this->error_number . ' ' . $this->error_text;
      echo '<br />in:<br />[' . (strstr($this->currentSql, 'db_cache') ? 'db_cache table' : $this->currentSql) . ']<br />';
    } else {
      echo 'WARNING: An Error occurred, please refresh the page and try again.';
    }
    trigger_error($this->error_number . ':' . $this->error_text, E_USER_ERROR);
    if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG == true) {
      echo 'If you were entering information, press the BACK button in your ' .
        'browser and re-check the information you had entered to be sure you ' .
        'left no blank fields.<br />';
    }
    echo '</div>';
  }

  /**
   * Execute a sql statement
   *
   * @param string  $sql   the statement
   * @param integer $limit the maximum number of results [0 = all]
   * @return mixed
   */
  public function Execute($sql, $limit = 0) {
    // bof: collect database queries
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS=='true') {
      global $PHP_SELF, $box_id, $current_page_base;
      if (strtoupper(substr($sql,0,6))=='SELECT' /*&& strstr($sql,'products_id')*/) {
        $f=@fopen(DIR_FS_LOGS.'/query_selects_' . $current_page_base . '_' . time() . '.txt','a');
        if ($f) {
          fwrite($f,  "\n\n" . 'I AM HERE ' . $current_page_base . /*zen_get_all_get_params() .*/ "\n" . 'sidebox: ' . $box_id . "\n\n" . "Explain \n" . $sql.";\n\n");
          fclose($f);
        }
        unset($f);
      }
    }
    // eof: collect products_id queries

    if ((int) $limit > 0) {
      $sql .= ' LIMIT ' . $limit;
    }

    return $this->query($sql);
  }

  /**
   * Execute a randomized statement
   *
   * @param string  $sql   the statement
   * @param integer $limit the maximum number of results [0 = all]
   * @return mixed
   */
  public function ExecuteRandomMulti($sql, $limit = 0) {
    $result = $this->Execute($sql, $limit);
    if ($result instanceof ResultInterface) {
      $result->randomize();
    }
    return $result;
  }

  /**
   * Get the last insert id
   *
   * @return integer
   */
  public function insert_ID() {
    return @mysqli_insert_id($this->link);
  }

  /**
   * Get column meta information for a table
   *
   * @param string $table the table name
   * @return array
   */
  public function metaColumns($table) {
    $result = $this->execute("SHOW COLUMNS from `$table`");
    $columns = array();
    foreach ($result as $column) {
      $columns[strtolower($column['Field'])] = new ValueObject($column);
    }
    return $columns;
  }

  /**
   * Get the mysql version string
   *
   * @return string
   */
  public function get_server_info() {
    if ($this->link) {
      return mysqli_get_server_info($this->link);
    }
    return 'UNKNOWN';
  }

  /**
   * Get the current total number of queries
   *
   * @return integer
   */
  public function queryCount() {
    return $this->count_queries;
  }

  /**
   * Get the current duration of all queries combined in seconds
   *
   * @return integer
   */
  public function queryTime() {
    return $this->total_query_time;
  }

  /**
   * Perform an insert or update statement
   *
   * @param string  $table  the database table
   * @param array   $data   the data to prepare
   * @param string  $type   the statement type
   * @param string  $filter optional where clause
   * @param boolean $debug  debug the prepared statement
   *
   * @return Result
   */
  public function perform($table, array $data, $type=self::PERFORM_TYPE_INSERT, $filter='', $debug=false) {
    $sql = '';
    if ($type == static::PERFORM_TYPE_INSERT) {
      $sql = "INSERT INTO `%s` (`%s`) VALUES (%s)";
      $columns = array();
      $values  = array();
      foreach ($data as $column => $value) {
        if (is_array($value)) {
          $columns[] = $value['fieldName'];
          $values[]  = $this->getBindVarValue($value['value'], $value['type']);
        } else {
          $columns[] = $column;
          $values[]  = $this->getBindVarValue($value, gettype($value));
        }
      }
      $sql = sprintf($sql, $table, join('`, `', $columns), join(', ', $values));
    } else {
      $values = array();
      foreach ($data as $column => $value) {
        if (is_array($value)) {
          $value  = $this->getBindVarValue($value['value'], $value['type']);
          $column = $value['fieldName'];
        } else {
          $value = $this->getBindVarValue($value, gettype($value));
        }
        $values[] = "`$column`=$value";
      }
      $sql = sprintf('UPDATE `%s` SET %s', $table, join(', ', $values));
      if (!empty($filter)) {
        $sql .= ' WHERE ' . $filter;
      }
    }

    if ($debug) {
      if ($this->log) {
        $this->log->debug($sql);
        $this->log->debug(var_export($data, 1));
      } else {
        die(var_dump($sql, $data));
      }
    }

    return $this->query($sql, false);
  }

  /**
   * Prepare a value for binding
   *
   * @param mixed  $value the bind value
   * @param string $type  the conversion type
   * @return mixed
   * @throws \InvalidArgumentException
   */
  protected function getBindVarValue($value, $type) {
    $typeArray = explode(':', $type);
    $type      = strtolower($typeArray[0]);

    switch ($type) {
      case 'csv':
      case 'enum':
      case 'passthru':
        break;
      case 'float':
      case 'currency':
        $value = (float) empty($value) ? 0 : $value;
        break;
      case 'integer':
        $value = (integer) empty($value) ? 0 : $value;
        break;
      case 'date':
      case 'datetime':
      case 'time':
      case 'timestamp':
        if (is_a($value, 'DateTime')) {
          $value = $value->format("Y-m-d H:i:s");
        }
      case 'string':
        $value = '\'' . $this->prepareInput($value) . '\'';
        break;
      case 'regexp':
        $searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
        foreach ($searchArray as $searchTerm) {
          $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
        }
      case 'noquotestring':
        $value = $this->prepareInput($value);
        break;
      default:
        throw new \InvalidArgumentException(
          sprintf('Invalid type: %s (%s)', $type, gettype($value))
        );
    }
    return $value;
  }

  /**
   * Bind a value to a statement parameter
   *
   * @param string $sql    the sql statement
   * @param string $var    the bind parameter
   * @param mixed  $value  the value to bind
   * @param string $type   the value conversion type
   *
   * @return string
   */
  public function bindVars($sql, $var, $value, $type = null) {
    try {
      $prepared = $this->getBindVarValue($value, $type ?: gettype($value));
    } catch (\InvalidArgumentException $e) {
      if ($this->log) {
        $this->log->error($e->getMessage());
        $this->log->error($e->getTraceAsString());
      }
      $prepared = (string) $value;
    }

    return str_replace($var, $prepared, $sql);
  }

  /**
   * Escape a string
   *
   * @param string $string the string to escape
   * @return string
   */
  public function prepareInput($string) {
    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string($this->link, $string);
    } elseif (function_exists('mysqli_escape_string')) {
      return mysqli_escape_string($this->link, $string);
    }
    return addslashes($string);
  }

  /**
   * Ensure connections are closed before destruction
   *
   * @return void
   */
  public function __destruct() {
    $this->close();
  }

}
