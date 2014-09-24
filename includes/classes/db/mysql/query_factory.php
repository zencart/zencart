<?php
/**
 * MySQL query_factory Class.
 * Class used for database abstraction to MySQL via mysqli
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions adapted from http://www.data-diggers.com/
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Wed Jul 4 14:44:03 2012 +0100 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Queryfactory - A simple database abstraction layer
 *
 */
class queryFactory extends base {
  var $link, $count_queries, $total_query_time, $dieOnErrors;

  function __construct() {
    $this->count_queries = 0;
    $this->total_query_time = 0;
  }

  function query($link, $query, $remove_from_queryCache = false) {
      global $queryLog;
      global $queryCache;

      if ($remove_from_queryCache && isset($queryCache)) {
        $queryCache->reset($query);
      }

      if( isset($queryCache) && $queryCache->inCache($query) ) {
            $cached_value = $queryCache->getFromCache($query);
            $this->count_queries--;
            return($cached_value);
      }

      if(isset($queryLog)) $queryLog->start($query);
      $result = mysqli_query($link, $query);
      if(isset($queryLog)) $queryLog->stop($query, $result);
      if(isset($queryCache)) $queryCache->cache($query, $result);
      return($result);
  }

  function connect($zf_host, $zf_user, $zf_password, $zf_database, $zf_pconnect = 'false', $dieOnErrors = false, $options = array()) {
    $this->database = $zf_database;
    $this->user = $zf_user;
    $this->host = $zf_host;
    $this->password = $zf_password;
    $this->pConnect = $zf_pconnect;
    $this->dieOnErrors = $dieOnErrors;
    if (defined('DB_CHARSET')) $dbCharset = DB_CHARSET;
    if (isset($options['dbCharset'])) $dbCharset = $options['dbCharset'];
    if (!function_exists('mysqli_connect')) die ('Call to undefined function: mysqli_connect().  Please install the MySQL Connector for PHP');
    $connectionRetry = 10;
    while (!isset($this->link) || ($this->link == FALSE && $connectionRetry !=0) )
    {
      $this->link = mysqli_connect($zf_host, $zf_user, $zf_password, $zf_database, (defined('DB_PORT') ? DB_PORT : NULL), (defined('DB_SOCKET') ? DB_SOCKET : NULL));
      $connectionRetry--;
    }
    if ($this->link) {
      if (mysqli_select_db($this->link, $zf_database)) {
        if (isset($dbCharset) ) {
          mysqli_query($this->link, "SET NAMES '" . $dbCharset . "'");
          if (function_exists('mysqli_set_charset')) {
            mysqli_set_charset($this->link, $dbCharset);
          } else {
            mysqli_query($this->link, "SET CHARACTER SET '" . $dbCharset . "'");
          }
        }
        $this->db_connected = true;
        if (!defined('DISABLE_MYSQL_TZ_SET')) {
          mysqli_query($this->link, "SET time_zone = '" . substr_replace(date("O"),":",-2,0) . "'");
        }
        return true;
      } else {
        $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $dieOnErrors);
        return false;
      }
    } else {
      $this->set_error(mysqli_connect_errno(), mysqli_connect_error(), $dieOnErrors);
      return false;
    }
  }

  function simpleConnect($zf_host, $zf_user, $zf_password, $zf_database) {
    $this->database = $zf_database;
    $this->user = $zf_user;
    $this->host = $zf_host;
    $this->password = $zf_password;
    $this->link = mysqli_connect($zf_host, $zf_user, $zf_password, $zf_database, (defined('DB_PORT') ? DB_PORT : NULL), (defined('DB_SOCKET') ? DB_SOCKET : NULL));
    if ($this->link) {
      $this->db_connected = true;
      return true;
    } else {
      $this->set_error(mysqli_connect_errno(), mysqli_connect_error(), $zp_real);
      return false;
    }
  }

  function selectdb($zf_database) {
    $result = mysqli_select_db($this->link, $zf_database);
    if ($result) return $result;
      $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $zp_real);
     return false;

  }

  function prepare_input($zp_string) {
    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string($this->link, $zp_string);
    } elseif (function_exists('mysqli_escape_string')) {
      return mysqli_escape_string($this->link, $zp_string);
    } else {
      return addslashes($zp_string);
    }
  }

  function close() {
    @mysqli_close($this->link);
    unset($this->link);
  }

  function set_error($zp_err_num, $zp_err_text, $dieOnErrors = true) {
    $this->error_number = $zp_err_num;
    $this->error_text = $zp_err_text;
    if ($dieOnErrors && $zp_err_num != 1141) { // error 1141 is okay ... should not die on 1141, but just continue on instead
      $this->show_error();
      die();
    }
  }

  function show_error() {
    if ($this->error_number == 0 && $this->error_text == DB_ERROR_NOT_CONNECTED && !headers_sent() && file_exists('nddbc.html') ) {
      header("HTTP/1.1 503 Service Unavailable");
      include('nddbc.html');
    }
    echo '<div class="systemError">';
    if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)
    {
      echo $this->error_number . ' ' . $this->error_text;
      echo '<br />in:<br />[' . (strstr($this->zf_sql, 'db_cache') ? 'db_cache table' : $this->zf_sql) . ']<br />';
    } else {
      echo 'WARNING: An Error occurred, please refresh the page and try again.';
    }
    $backtrace_array = debug_backtrace();
    $query_factory_caller = '';
    foreach ($backtrace_array as $current_caller) {
      if (strcmp($current_caller['file'], __FILE__) != 0) {
        $query_factory_caller = ' ==> (as called by) ' . $current_caller['file'] . ' on line ' . $current_caller['line'] . ' <==';
        break;
      }
    }
    trigger_error($this->error_number . ':' . $this->error_text . ' :: ' . $this->zf_sql . $query_factory_caller, E_USER_ERROR);
    if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG==true) echo 'If you were entering information, press the BACK button in your browser and re-check the information you had entered to be sure you left no blank fields.<br />';
    echo '</div>';
  }

  function Execute($zf_sql, $zf_limit = false, $zf_cache = false, $zf_cachetime=0, $remove_from_queryCache = false) {
    // bof: collect database queries
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS=='true') {
      global $PHP_SELF, $box_id, $current_page_base;
      if (strtoupper(substr($zf_sql,0,6))=='SELECT' /*&& strstr($zf_sql,'products_id')*/) {
        $f=@fopen(DIR_FS_LOGS.'/query_selects_' . $current_page_base . '_' . time() . '.txt','a');
        if ($f) {
          fwrite($f,  "\n\n" . 'I AM HERE ' . $current_page_base . /*zen_get_all_get_params() .*/ "\n" . 'sidebox: ' . $box_id . "\n\n" . "Explain \n" . $zf_sql.";\n\n");
          fclose($f);
        }
        unset($f);
      }
    }
    // eof: collect products_id queries
    global $zc_cache;
    $obj = new queryFactoryResult();
    if ($zf_limit) {
      $zf_sql = $zf_sql . ' LIMIT ' . $zf_limit;
      $obj->limit = $zf_limit;
    }
    $this->zf_sql = $zf_sql;
    $obj->sql_query = $zf_sql;
    if ( $zf_cache AND $zc_cache->sql_cache_exists($zf_sql, $zf_cachetime) ) {
      $obj->is_cached = true;
      $zp_result_array = $zc_cache->sql_cache_read($zf_sql);
      $obj->result = $zp_result_array;
      if (sizeof($zp_result_array) > 0 ) {
        $obj->EOF = false;
        while (list($key, $value) = each($zp_result_array[0])) {
          $obj->fields[$key] = $value;
        }
      }
    } elseif ($zf_cache) {
      $zc_cache->sql_cache_expire_now($zf_sql);
      $time_start = explode(' ', microtime());
      if (!$this->db_connected)
      {
        if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real))
        $this->set_error('0', DB_ERROR_NOT_CONNECTED, $this->dieOnErrors);
      }
      $zp_db_resource = $this->query($this->link, $zf_sql, $remove_from_queryCache);
      if (FALSE === $zp_db_resource) {
        $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
      } else {
        $obj->resource = $zp_db_resource;
        $zp_rows = $obj->RecordCount();
        if ($zp_rows > 0) {
          $zp_ii = 0;
          while ($zp_ii < $zp_rows) {
            $zp_result_array = mysqli_fetch_array($zp_db_resource);
            if ($zp_result_array) {
              $obj->result[$zp_ii] = array();
              while (list($key, $value) = each($zp_result_array)) {
                if (!preg_match('/^[0-9]/', $key)) {
                  $obj->result[$zp_ii][$key] = $value;
                }
              }
            } else {
              $obj->limit = $zp_ii;
              break;
            }
            $zp_ii++;
          }
          while (list($key, $value) = each($obj->result[$obj->cursor])) {
            if (!preg_match('/^[0-9]/', $key)) {
              $obj->fields[$key] = $value;
            }
          }
          $obj->EOF = false;
        }
        unset($zp_ii, $zp_result_array, $key, $value);
      }
      $zc_cache->sql_cache_store($zf_sql, $obj->result);
      $obj->is_cached = true;
      $time_end = explode (' ', microtime());
      $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
      $this->total_query_time += $query_time;
      $this->count_queries++;
    } else {
      $time_start = explode(' ', microtime());
      if (!$this->db_connected)
      {
        if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real))
        $this->set_error('0', DB_ERROR_NOT_CONNECTED, $this->dieOnErrors);
      }
      $zp_db_resource = $this->query($this->link, $zf_sql, $remove_from_queryCache);
      if (!$zp_db_resource) {
        if (mysqli_errno($this->link) == 2006) {
          $this->link = FALSE;
          $this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real);
          $zp_db_resource = mysqli_query($this->link, $zf_sql);
        }
      }
      if (FALSE === $zp_db_resource) {
        $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
      } else {
        $obj->resource = $zp_db_resource;
        if ($obj->RecordCount() > 0) {
          $zp_result_array = mysqli_fetch_array($zp_db_resource);
          if ($zp_result_array) {
            while (list($key, $value) = each($zp_result_array)) {
              if (!preg_match('/^[0-9]/', $key)) {
                $obj->fields[$key] = $value;
              }
            }
            $obj->EOF = false;
          }
        }

        $time_end = explode (' ', microtime());
        $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
        $this->total_query_time += $query_time;
        $this->count_queries++;
      }
    }
    return($obj);
  }

  function ExecuteRandomMulti($zf_sql, $zf_limit = 0, $zf_cache = false, $zf_cachetime=0) {
    $this->zf_sql = $zf_sql;
    $time_start = explode(' ', microtime());
    $obj = new queryFactoryResult();
    if (!$this->db_connected)
    {
      if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real))
      $this->set_error('0', DB_ERROR_NOT_CONNECTED, $this->dieOnErrors);
    }
    $zp_db_resource = @$this->query($this->link, $zf_sql, $remove_from_queryCache);
    if (FALSE === $zp_db_resource ){
      $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
    } else {
      $obj->resource = $zp_db_resource;
      $obj->limit = $zf_limit;

      $zp_rows = $obj->RecordCount();
      if ($zp_rows > 0 && $zf_limit > 0) {
        $zp_Start_row = 0;
        if ($zf_limit) {
          $zp_start_row = zen_rand(0, $zp_rows - $zf_limit);
        }
        $obj->Move($zp_start_row);
        $zp_ii = 0;
        while ($zp_ii < $zf_limit) {
          $zp_result_array = @mysqli_fetch_array($zp_db_resource);
          if ($zp_result_array) {
            $obj->result[$zp_ii] = array();
            while (list($key, $value) = each($zp_result_array)) {
              $obj->result[$zp_ii][$key] = $value;
            }
          } else {
            $obj->limit = $zp_ii;
            break;
          }
          $zp_ii++;
        }
        unset($zp_ii, $zp_result_array, $key, $value);
        $obj->EOF = false;

        $obj->result_random = array_rand($obj->result, count($obj->result));
        if(is_array($obj->result_random)) {
          shuffle($obj->result_random);
        }
        else {
          $obj->result_random = array(0 => $obj->result_random);
        }
        $obj->cursor = -1;
        $obj->MoveNextRandom();
      }
    }

    $time_end = explode (' ', microtime());
    $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
    $this->total_query_time += $query_time;
    $this->count_queries++;
    return($obj);
  }

  function insert_ID() {
    return @mysqli_insert_id($this->link);
  }

  function metaColumns($zp_table) {
    $sql = "SHOW COLUMNS from :tableName:";
    $sql = $this->bindVars($sql, ':tableName:', $zp_table, 'noquotestring');
    $res = $this->execute($sql);
    while (!$res->EOF)
    {
      $obj [strtoupper($res->fields['Field'])] = new queryFactoryMeta($res->fields);
      $res->MoveNext();
    }
    return $obj;
  }

  function get_server_info() {
    if ($this->link) {
      return mysqli_get_server_info($this->link);
    } else {
      return UNKNOWN;
    }
  }

  function queryCount() {
    return $this->count_queries;
  }

  function queryTime() {
    return $this->total_query_time;
  }

  function perform ($tableName, $tableData, $performType='INSERT', $performFilter='', $debug=false) {
    switch (strtolower($performType)) {
      case 'insert':
      $insertString = "";
      $insertString = "INSERT INTO " . $tableName . " (";
      foreach ($tableData as $key => $value) {
        if ($debug === true) {
          echo $value['fieldName'] . '#';
        }
        $insertString .= $value['fieldName'] . ", ";
      }
      $insertString = substr($insertString, 0, strlen($insertString)-2) . ') VALUES (';
      reset($tableData);
      foreach ($tableData as $key => $value) {
        $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
        $insertString .= $bindVarValue . ", ";
      }
      $insertString = substr($insertString, 0, strlen($insertString)-2) . ')';
      if ($debug === true) {
        echo $insertString;
        die();
      } else {
        $this->execute($insertString);
      }
      break;
      case 'update':
      $updateString ="";
      $updateString = 'UPDATE ' . $tableName . ' SET ';
      foreach ($tableData as $key => $value) {
        $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
        $updateString .= $value['fieldName'] . '=' . $bindVarValue . ', ';
      }
      $updateString = substr($updateString, 0, strlen($updateString)-2);
      if ($performFilter != '') {
        $updateString .= ' WHERE ' . $performFilter;
      }
      if ($debug === true) {
        echo $updateString;
        die();
      } else {
        $this->execute($updateString);
      }
      break;
    }
  }
  function getBindVarValue($value, $type) {
    $typeArray = explode(':',$type);
    $type = $typeArray[0];
    switch ($type) {
      case 'csv':
        return $value;
      break;
      case 'passthru':
        return $value;
      break;
      case 'float':
        return (!zen_not_null($value) || $value=='' || $value == 0) ? 0 : $value;
      break;
      case 'integer':
        return (int)$value;
      break;
      case 'string':
        if (isset($typeArray[1])) {
          $regexp = $typeArray[1];
        }
        return '\'' . $this->prepare_input($value) . '\'';
      break;
      case 'noquotestring':
        return $this->prepare_input($value);
      break;
      case 'currency':
        return '\'' . $this->prepare_input($value) . '\'';
      break;
      case 'date':
        return '\'' . $this->prepare_input($value) . '\'';
      break;
      case 'enum':
        if (isset($typeArray[1])) {
          $enumArray = explode('|', $typeArray[1]);
        }
        return '\'' . $this->prepare_input($value) . '\'';
      case 'regexp':
        $searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
        foreach ($searchArray as $searchTerm) {
          $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
        }
        return $this->prepare_input($value);
      default:
      die('var-type undefined: ' . $type . '('.$value.')');
    }
  }
/**
 * method to do bind variables to a query
**/
  function bindVars($sql, $bindVarString, $bindVarValue, $bindVarType, $debug = false) {
    $bindVarTypeArray = explode(':', $bindVarType);
    $sqlNew = $this->getBindVarValue($bindVarValue, $bindVarType);
    $sqlNew = str_replace($bindVarString, $sqlNew, $sql);
    return $sqlNew;
  }

  function prepareInput($string) {
    return $this->prepare_input($string);
  }
}

class queryFactoryResult implements Countable, Iterator {
  /**
   * Indicates if the result has reached the last row of data.
   *
   * @var boolean
   */
  public $EOF;

  /**
   * Indicates the current database row.
   *
   * @var int
   */
  public $cursor;

  /**
   * Contains the data for the current database row (fields + values).
   *
   * @var array of field => value pairs
   */
  public $fields;

  /**
   * Indicates if the result is cached.
   *
   * @var boolean
   */
  public $is_cached;

  /**
   * Contains stored results (if any). Typically used by cached results and
   * RandomMulti queries.
   *
   * @var array
   */
  public $result;

  /**
   * An array of randomly selected keys. Typically used by RandomMulti queries.
   *
   * @var array
   */
  public $result_random;

  /**
   * The maximum number of rows allowed to be iterated over.
   *
   * @var int
   */
  public $limit;

  /**
   * The raw result returned by the mysqli call.
   *
   * @var mysqli_result
   */
  public $resource;

  /**
   * Constructs a new Query Factory Result
   */
  function __construct() {
    $this->is_cached = false;
    $this->EOF = true;
    $this->result = array();
    $this->cursor = 0;
  }

 /* (non-PHPdoc)
  * @see Iterator::current()
  */
  public function current() {
    return $this->fields;
  }

  /* (non-PHPdoc)
   * @see Iterator::key()
  */
  public function key() {
    return $this->cursor;
  }

  /* (non-PHPdoc)
   * @see Iterator::next()
   */
  public function next() {
  $this->MoveNext();
  }

  /**
   * Moves the cursor to the next row.
   */
  public function MoveNext() {
    $this->cursor++;
    if (!$this->valid()) {
      $this->EOF = true;
    }
    else if ($this->is_cached) {
      if ($this->cursor >= sizeof($this->result)) {
        $this->EOF = true;
      } else {
        while(list($key, $value) = each($this->result[$this->cursor])) {
          $this->fields[$key] = $value;
        }
      }
    } else {
      $zp_result_array = @mysqli_fetch_array($this->resource);
      if (!$zp_result_array) {
        $this->EOF = true;
      } else {
        while (list($key, $value) = each($zp_result_array)) {
          if (!preg_match('/^[0-9]/', $key)) {
            $this->fields[$key] = $value;
          }
        }
      }
    }
  }

  /**
   * Moves to the next randomized result
   */
  public function MoveNextRandom() {
    $this->cursor++;
    if ($this->cursor < $this->limit) {
      $zp_result_array = $this->result[$this->result_random[$this->cursor]];
      while (list($key, $value) = each($zp_result_array)) {
        if (!preg_match('/^[0-9]/', $key)) {
          $this->fields[$key] = $value;
        }
      }
    } else {
      $this->EOF = true;
    }
  }

  /* (non-PHPdoc)
   * @see Iterator::rewind()
   */
  public function rewind() {
    $this->Move(0);
    $this->EOF = ($this->RecordCount() == 0);
  }

  /* (non-PHPdoc)
   * @see Iterator::valid()
   */
  public function valid() {
    return $this->cursor < $this->RecordCount() && !$this->EOF;
  }

  /* (non-PHPdoc)
   * @see Iterator::count()
   */
  public function count() {
    return $this->RecordCount();
  }

  /**
   * Returns the number of rows (records).
   *
   * @return int
   */
  public function RecordCount() {
    if($this->is_cached) {
      return sizeof($this->result);
    } else if($this->resource !== null && $this->resource !== true) {
      return @mysqli_num_rows($this->resource);
    }
    return 0;
  }

  /**
   * Moves the cursor to the specified row. If the row is not valid,
   * the cursor will be moved past the last row and EOF will be set false.
   *
   * @param int $zp_row the row to move to
   */
  public function Move($zp_row) {
    global $db;
    if ($this->is_cached) {
      if($zp_row >= sizeof($this->result)) {
        $this->cursor = sizeof($this->result);
        $this->EOF = true;
      } else {
        while(list($key, $value) = each($this->result[$zp_row])) {
          $this->fields[$key] = $value;
        }
        $this->cursor = $zp_row;
        $this->EOF = false;
      }
    } else if (@mysqli_data_seek($this->resource, $zp_row)) {
      $zp_result_array = @mysqli_fetch_array($this->resource);
      while (list($key, $value) = each($zp_result_array)) {
        $this->fields[$key] = $value;
      }
      @mysqli_data_seek($this->resource, $zp_row);
      $this->cursor = $zp_row;
      $this->EOF = false;
    } else {
      $this->EOF = true;
      $db->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
    }
  }
}

class queryFactoryMeta {

  function queryFactoryMeta($zp_field) {
    $type = $zp_field['Type'];
    $rgx = preg_match('/^[a-z]*/', $type, $matches);
    $this->type = $matches[0];
    $this->max_length = preg_replace('/[a-z\(\)]/', '', $type);
  }
}
