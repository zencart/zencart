<?php
/**
 * MySQL query_factory class.
 * Class used for database abstraction to MySQL via mysqli
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions adapted from http://www.data-diggers.com/
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 19 Modified in v2.1.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Queryfactory - A simple database abstraction layer
 *
 */
class queryFactory extends base
{
    public $link; // mysqli object
    private $count_queries = 0;
    private float|int $total_query_time = 0;
    public $dieOnErrors = false;
    public $error_number = 0;
    public $error_text = '';

    public ?string $dbDefaultCharacterSet;
    public ?string $dbDefaultCollation;

    private $pConnect;
    /**
     * @var bool
     */
    private $db_connected = false;

    private $host = '';
    private $database = '';
    private $user = '';
    private $password = '';
    private $zf_sql = '';
    private $ignored_error_codes = [
        2002, // connection refused via socket
        2003, // cannot connect to host
        2006, // server has gone away / (MySQL server wait timeout)
        4031, // server has gone away since MySQL 8.0.24
        2013, // lost connection during query
        1040, // too many connections
        1053, // server shutdown in progress
        1141, // grant-type not allowed
        1203, // too many user connections
    ];

    function __construct()
    {
        $this->count_queries = 0;
        $this->total_query_time = 0;
    }

    /**
     * @param string $db_host database server hostname
     * @param string $db_user db username
     * @param string $db_password db password
     * @param string $db_name database name
     * @param string $pconnect unused
     * @param false $dieOnErrors debug flag
     * @param array $options additional configuration
     * @return bool
     */
    public function connect(string $db_host, string $db_user, string $db_password, string $db_name, $pconnect = 'unused', bool $dieOnErrors = false, array $options = []): bool
    {
        $this->database = $db_name;
        $this->user = $db_user;
        $this->host = $db_host;
        $this->password = $db_password;
        $this->pConnect = $pconnect;
        $this->dieOnErrors = $dieOnErrors;

        if (defined('DB_CHARSET')) $dbCharset = DB_CHARSET;
        if (isset($options['dbCharset'])) $dbCharset = $options['dbCharset'];

        if (!function_exists('mysqli_connect')) die ('Call to undefined function: mysqli_connect().  Please install the MySQL Connector for PHP');

        // use default reporting setting, so exceptions aren't thrown, since we attempt to catch errors here procedurally.
        mysqli_report(MYSQLI_REPORT_OFF);

        $connectionRetry = 10;
        while (!isset($this->link) || ($this->link == false && $connectionRetry > 0)) {
            $this->link = mysqli_connect($db_host, $db_user, $db_password, $db_name, (defined('DB_PORT') ? DB_PORT : null), (defined('DB_SOCKET') ? DB_SOCKET : null));

            // handle MySQL connection errors/failures
            if (in_array(mysqli_connect_errno(), $this->ignored_error_codes)) {
                if ($connectionRetry > 1) {
                    // if service is down, try only one more time
                    $connectionRetry = 1;
                }
                $this->dieOnErrors = true;
            }

            $connectionRetry--;
        }

        if ($this->link) {
            if (mysqli_select_db($this->link, $db_name)) {
                if (isset($dbCharset)) {
                    mysqli_query($this->link, "SET NAMES '" . $dbCharset . "'");
                    if (function_exists('mysqli_set_charset')) {
                        mysqli_set_charset($this->link, $dbCharset);
                    } else {
                        mysqli_query($this->link, "SET CHARACTER SET '" . $dbCharset . "'");
                    }
                }
                $this->db_connected = true;

                // Set time zone to match PHP, unless disabled by this constant
                if (!defined('DISABLE_MYSQL_TZ_SET')) {
                    mysqli_query($this->link, "SET time_zone = '" . substr_replace(date("O"), ":", -2, 0) . "'");
                }

                // Set MySQL mode, if one is defined before execution. Ref: https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html (must be only A-Z or _ or , characters)
                if (defined('DB_MYSQL_MODE') && DB_MYSQL_MODE != '') {
                    mysqli_query($this->link, "SET SESSION sql_mode = '" . preg_replace('/[^A-Z_,]/', '', DB_MYSQL_MODE) . "'");
                }

                $result = $this->Execute("SELECT @@character_set_database, @@collation_database");
                $this->dbDefaultCharacterSet = $result->fields['@@character_set_database'] ?? null;
                $this->dbDefaultCollation = $result->fields['@@collation_database'] ?? null;

                return true;
            }

            $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $dieOnErrors);
            return false;
        }

        $this->set_error(mysqli_connect_errno(), mysqli_connect_error(), $dieOnErrors);
        return false;
    }

    /**
     * @param string $db_host database server hostname
     * @param string $db_user db username
     * @param string $db_password db password
     * @param string $db_name database name
     * @return bool
     */
    public function simpleConnect($db_host, $db_user, $db_password, $db_name): bool
    {
        // use default reporting setting, so exceptions aren't thrown, since we attempt to catch errors here procedurally.
        mysqli_report(MYSQLI_REPORT_OFF);

        $this->database = $db_name;
        $this->user = $db_user;
        $this->host = $db_host;
        $this->password = $db_password;

        // temporarily suppress E_WARNING in case of connection failure
        $error_level = error_reporting(E_ERROR | E_PARSE);
        $this->link = mysqli_connect($db_host, $db_user, $db_password, $db_name, (defined('DB_PORT') ? DB_PORT : null), (defined('DB_SOCKET') ? DB_SOCKET : null));
        error_reporting($error_level);

        if ($this->link) {
            $this->db_connected = true;
            return true;
        }

        $this->set_error(mysqli_connect_errno(), mysqli_connect_error(), $this->dieOnErrors);
        return false;
    }

    /**
     * @param string $sqlQuery
     * @param bool $removeFromQueryCache Whether to skip the MySQL resource cache for repeats of the same query string during the same page-load
     * @return bool|mixed|mysqli_result
     */
    protected function runQuery(string $sqlQuery, bool $removeFromQueryCache)
    {
        // ensure db connection
        if (!$this->db_connected) {
            if (!$this->connect($this->host, $this->user, $this->password, $this->database, null, $this->dieOnErrors)) {
                $this->set_error(0, DB_ERROR_NOT_CONNECTED, $this->dieOnErrors);
            }
        }
        // run the query
        $zp_db_resource = $this->query($this->link, $sqlQuery, $removeFromQueryCache);

        // second attempt in case of 2006 response
        if (!$zp_db_resource) {
            if (in_array(mysqli_errno($this->link), [2006, 4031])) {
                $this->link = false;
                $this->connect($this->host, $this->user, $this->password, $this->database, null, $this->dieOnErrors);
                // run the query directly, bypassing the queryCache
                $zp_db_resource = mysqli_query($this->link, $sqlQuery);
            }
        }
        return $zp_db_resource;
    }

    /**
     * Escape SQL query value for binding
     *
     * @param string|null|mixed $string
     * @return string
     */
    public function prepare_input($string): string
    {
        return mysqli_real_escape_string($this->link, $string);
    }

    /**
     * Alias to prepare_input()
     * @param string|null|mixed $string
     * @return string
     * @see $this->prepare_input()
     */
    function prepareInput($string)
    {
        return $this->prepare_input($string);
    }

    /**
     * @param string $sqlQuery
     * @param string|int|null $limit
     * @param bool $enableCaching
     * @param int $cacheSeconds
     * @param bool $removeFromQueryCache
     * @return queryFactoryResult
     */
    public function Execute(string $sqlQuery, $limit = null, bool $enableCaching = false, int $cacheSeconds = 0, bool $removeFromQueryCache = false): \queryFactoryResult
    {
        // do SELECT logging if enabled
        $this->logQuery($sqlQuery);

        global $zc_cache;

        $obj = new queryFactoryResult($this->link);

        $limit = (int)$limit;
        if ($limit) {
            $sqlQuery .= ' LIMIT ' . $limit;
            $obj->limit = $limit;
        }

        $this->zf_sql = $sqlQuery;
        $obj->sql_query = $sqlQuery;

        // Use cached result
        if ($enableCaching && $zc_cache->sql_cache_exists($sqlQuery, $cacheSeconds)) {
            $obj->is_cached = true;
            $zp_result_array = $zc_cache->sql_cache_read($sqlQuery);
            if ($zp_result_array !== false) {
                $obj->result = $zp_result_array;
                if (count($zp_result_array) > 0) {
                    $obj->EOF = false;
                    $obj->fields = array_replace($obj->fields, $zp_result_array[0]);
                }
                return $obj;
            }
        }


        $time_start = microtime(as_float: true);

        // Get MySQL query result
        $zp_db_resource = $this->runQuery($sqlQuery, $removeFromQueryCache);

        // iterate over query results and cache it before returning it
        if ($enableCaching) {
            $zc_cache->sql_cache_expire_now($sqlQuery);

            if (false === $zp_db_resource) {
                $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
            } else {
                $obj->resource = $zp_db_resource;
                $zp_rows = $obj->RecordCount();
                if ($zp_rows > 0) {
                    $zp_ii = 0;
                    while ($zp_ii < $zp_rows) {
                        $obj->result[$zp_ii] = [];
                        $obj->result[$zp_ii] = mysqli_fetch_assoc($zp_db_resource);
                        if (!$obj->result[$zp_ii]) {
                            unset($obj->result[$zp_ii]);
                            $obj->limit = $zp_ii;
                            break;
                        }
                        $zp_ii++;
                    }
                    $obj->fields = array_replace($obj->fields, $obj->result[$obj->cursor]);
                    $obj->EOF = false;
                }
                unset($zp_ii);
            }
            $zc_cache->sql_cache_store($sqlQuery, $obj->result);
            $obj->is_cached = true;
            $time_end = microtime(as_float: true);
            $query_time = $time_end - $time_start;
            $this->total_query_time += $query_time;
            $this->count_queries++;

            return $obj;
        }

        // process query results without caching them

        if (false === $zp_db_resource) {
            $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
        } else {
            $obj->resource = $zp_db_resource;
            if ($obj->RecordCount() > 0) {
                $zp_result_array = mysqli_fetch_assoc($zp_db_resource);
                if ($zp_result_array) {
                    $obj->fields = array_replace($obj->fields, $zp_result_array);
                    $obj->EOF = false;
                }
            }

            $time_end = microtime(as_float: true);
            $query_time = $time_end - $time_start;
            $this->total_query_time += $query_time;
            $this->count_queries++;
        }

        return $obj;
    }

    /**
     * Use this form of the Execute method to ensure that any SELECT result is pulled from the database, bypassing the cache.
     * @param string $sqlQuery
     * @return queryFactoryResult
     */
    function ExecuteNoCache(string $sqlQuery)
    {
        return $this->Execute($sqlQuery, false, false, 0, true);
    }

    /**
     * Execute a SELECT query and return the results in a random order
     * The results should be iterated with MoveNextRandom()
     *
     * @param string $sqlQuery
     * @param int $limit
     * @return queryFactoryResult
     */
    public function ExecuteRandomMulti(string $sqlQuery, $limit = 0): \queryFactoryResult
    {
        $time_start = microtime(as_float: true);
        $this->zf_sql = $sqlQuery;
        $obj = new queryFactoryResult($this->link);
        $obj->sql_query = $sqlQuery;
        $limit = (int)$limit;
        $obj->limit = $limit;

        $zp_db_resource = $this->runQuery($sqlQuery, true);

        if (false === $zp_db_resource) {
            $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
        } else {
            $obj->resource = $zp_db_resource;

            $zp_rows = $obj->RecordCount();
            if (empty($limit)) {
                $limit = $zp_rows;
            }
            if ($zp_rows > 0 && $limit > 0) {
                $zp_start_row = 0;
                if ($limit) {
                    $zp_start_row = zen_rand(0, $zp_rows - $limit);
                }
                mysqli_data_seek($zp_db_resource, $zp_start_row);
                $zp_ii = 0;
                while ($zp_ii < $limit) {
                    $obj->result[$zp_ii] = [];
                    $obj->result[$zp_ii] = mysqli_fetch_assoc($zp_db_resource);
                    if (!$obj->result[$zp_ii]) {
                        unset($obj->result[$zp_ii]);
                        $obj->limit = $zp_ii;
                        break;
                    }
                    $zp_ii++;
                }
                unset($zp_ii);
                $obj->EOF = false;

                $obj->result_random = array_rand($obj->result, count($obj->result));
                if (is_array($obj->result_random)) {
                    shuffle($obj->result_random);
                } else {
                    $obj->result_random = [0 => $obj->result_random];
                }
                $obj->cursor = -1;
                $obj->MoveNextRandom();
            }
        }

        $time_end = microtime(as_float: true);
        $query_time = $time_end - $time_start;
        $this->total_query_time += $query_time;
        $this->count_queries++;
        return $obj;
    }

    /**
     * @deprecated since 1.5.8 use ExecuteRandomMulti
     */
    function ExecuteRandomMultiNoCache($sqlQuery)
    {
        trigger_error('Call to deprecated function ExecuteRandomMultiNoCache. Use ExecuteRandomMulti() instead', E_USER_DEPRECATED);

        return $this->ExecuteRandomMulti($sqlQuery, 0);
    }

    /**
     * Execute the database query, using the queryCache memoization cache to re-use same Resource for repeat queries
     *
     * @param mysqli $link
     * @param string $query
     * @param bool $removeFromQueryCache
     * @return bool|mixed|mysqli_result
     */
    protected function query($link, string $query, bool $removeFromQueryCache = false)
    {
        global $queryCache;

        if (isset($queryCache)) {
            if ($removeFromQueryCache) {
                $queryCache->reset($query);
            }

            if ($queryCache->inCache($query)) {
                $cached_value = $queryCache->getFromCache($query);
                $this->count_queries--;
                return $cached_value;
            }
        }

        $result = mysqli_query($link, $query);

        if (isset($queryCache)) $queryCache->cache($query, $result);

        return $result;
    }

    /**
     * Get ID of last inserted record
     *
     * @return int|string
     */
    public function insert_ID()
    {
        return @mysqli_insert_id($this->link);
    }

    /**
     * Return the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query.
     */
    public function affectedRows()
    {
        return ($this->link) ? $this->link->affected_rows : 0;
    }

    /**
     * Return the number of queries executed since the counter started
     * @return int
     */
    public function queryCount(): int
    {
        return $this->count_queries;
    }

    /**
     * Return the number of seconds elapsed for querying, since the counter started
     */
    public function queryTime(): float
    {
        return (float)$this->total_query_time;
    }

    /**
     * Performs an INSERT or UPDATE based on a supplied array of field data
     *
     * @param string $tableName table on which to perform the insert/update
     * @param array $tableData data to be inserted/deleted containing sub-arrays with fieldName/value/type keys (where type is the BindVar rule to apply)
     * @param string $performType INSERT or UPDATE or INSERTIGNORE or UPDATEIGNORE
     * @param string $whereCondition condition for UPDATE (exclude the word "WHERE")
     * @param false $debug developer use only
     */
    public function perform(string $tableName, array $tableData, string $performType = 'INSERT', string $whereCondition = '', ?bool $debug = false): void
    {
        switch (strtolower($performType)) {
            case 'insertignore':
                $insertString = 'INSERT IGNORE';
            case 'insert':
                $insertString = $insertString ?? 'INSERT';
                $insertString .= " INTO $tableName (";
                foreach ($tableData as $key => $value) {
                    if ($debug === true) {
                        echo $value['fieldName'] . '#';
                    }
                    $insertString .= $value['fieldName'] . ", ";
                }
                $insertString = substr($insertString, 0, -2) . ') VALUES (';
                foreach ($tableData as $key => $value) {
                    $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
                    $insertString .= $bindVarValue . ", ";
                }
                $insertString = substr($insertString, 0, -2) . ')';
                if ($debug === true) {
                    echo $insertString;
                    die();
                }

                $this->Execute($insertString);

                break;

            case 'updateignore':
                $updateString = 'UPDATE IGNORE ';
            case 'update':
                $updateString = $updateString ?? 'UPDATE ';
                $updateString .= " $tableName SET ";
                foreach ($tableData as $key => $value) {
                    $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
                    $updateString .= $value['fieldName'] . '=' . $bindVarValue . ', ';
                }
                $updateString = substr($updateString, 0, -2);
                if (!empty($whereCondition)) {
                    $updateString .= ' WHERE ' . $whereCondition;
                }
                if ($debug === true) {
                    echo $updateString;
                    die();
                }

                $this->Execute($updateString);

                break;
        }
    }

    /**
     * bind variables to a query
     * @param string $sql SQL query fragment to perform binding substitution on
     * @param string $parameterToReplace the string to replace in the origin $sql
     * @param mixed $valueToBind  the variable/value to be bound
     * @param string $bindingRule the pattern to cast the value to
     * @return string original $sql query fragment with patterns substituted
     */
    public function bindVars(string $sql, string $parameterToReplace, $valueToBind, string $bindingRule): string
    {
        $sqlNew = $this->getBindVarValue($valueToBind, $bindingRule);
        $sqlNew = str_replace($parameterToReplace, $sqlNew, $sql);
        return $sqlNew;
    }

    /**
     * Applies binding/sanitization to values in preparation for safe execution
     *
     * @param mixed $value value to be bound/sanitized
     * @param string $type binding rule to apply
     * @return float|int|string
     */
    protected function getBindVarValue($value, string $type)
    {
        $typeArray = explode(':', $type);
        $type = $typeArray[0];
        switch ($type) {
            case 'inConstructInteger':
                $list = explode(',', $value);
                $newList = array_map(function ($value) {
                    return (int)$value;
                }, $list);
                $value = implode(',', $newList);

                return $value;

            case 'inConstructString':
                $list = explode(',', $value);
                $newList = array_map(function ($value) {
                    return '\'' . $this->prepare_input($value) . '\'';
                }, $list);
                $value = implode(',', $newList);

                return $value;

            case 'csv':
                return $value;

            case 'passthru':
                return $value;

            case 'float':
                return (!zen_not_null($value) || $value == '' || $value == 0) ? 0 : (float)$value;

            case 'integer':
                return (int)$value;

            case 'string':
                if (preg_match('/NULL/', $value)) return 'null';
                return '\'' . $this->prepare_input($value) . '\'';

            case 'stringIgnoreNull':
                return '\'' . $this->prepare_input($value) . '\'';

            case 'noquotestring':
                return $this->prepare_input($value);

            case 'currency':
                return '\'' . $this->prepare_input($value) . '\'';

            case 'date':
                if (preg_match('/null/i', $value)) return 'null';
                return '\'' . $this->prepare_input($value) . '\'';

            case 'enum':
                if (isset($typeArray[1])) {
                    $enumArray = explode('|', $typeArray[1]);
                }
                return '\'' . $this->prepare_input($value) . '\'';

            case 'regexp':
                $searchArray = ['[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^'];
                foreach ($searchArray as $searchTerm) {
                    $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
                }
                return $this->prepare_input($value);

            default:
                trigger_error("var-type undefined: $type ($value).", E_USER_ERROR);
        }
    }

    /**
     * @param string $db_name
     * @return bool
     */
    public function selectdb(string $db_name): bool
    {
        $result = mysqli_select_db($this->link, $db_name);
        if ($result) {
            $collationQuery = $this->Execute("SELECT @@character_set_database, @@collation_database");
            $this->dbDefaultCharacterSet = $collationQuery->fields['@@character_set_database'] ?? null;
            $this->dbDefaultCollation = $collationQuery->fields['@@collation_database'] ?? null;
            return true;
        }

        $this->set_error(mysqli_errno($this->link), mysqli_error($this->link), $this->dieOnErrors);
        return false;
    }

    /**
     * Close db connection
     */
    public function close(): void
    {
        if (!$this->link) return;
        // @ suppression is intentional here
        @mysqli_close($this->link);
        unset($this->link);
    }

    /**
     * Close db connection on destroy/shutdown/exit
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Internal queryfactory error handling
     */
    protected function set_error($err_num, $err_text, $dieOnErrors = true): void
    {
        $this->error_number = $err_num;
        $this->error_text = $err_text;
        if ($dieOnErrors && $err_num != 1141) { // error 1141 is okay ... should not die on 1141, but just continue on instead
            $this->show_error();
            if (!defined('DIR_FS_INSTALL')) {
                die();
            }
        }
    }

    /**
     * Display DB Connection Failure error message
     * and trigger error logging
     */
    protected function show_error()
    {
        if (!headers_sent()) {
            header("HTTP/1.1 503 Service Unavailable");
        }

        if (!defined('FILENAME_DATABASE_TEMPORARILY_DOWN')) {
            define('FILENAME_DATABASE_TEMPORARILY_DOWN', (defined('DIR_FS_CATALOG') ? DIR_FS_CATALOG : DIR_FS_ROOT) . '/nddbc.html');
        }
        if (file_exists(FILENAME_DATABASE_TEMPORARILY_DOWN)) {
            if (($this->error_number == 0 && $this->error_text == DB_ERROR_NOT_CONNECTED)
                || in_array($this->error_number, [2002, 2003]))
            {
                include(FILENAME_DATABASE_TEMPORARILY_DOWN);
            }
        }

        // suppress backtrace for MariaDB connection errors: not logging these because they usually come hundreds at a time
        if (in_array($this->error_number, $this->ignored_error_codes) || defined('DIR_FS_INSTALL')) {
            return;
        }

        // display error details if appropriate
        echo '<div class="systemError">';
        if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
            echo $this->error_number . ' ' . $this->error_text;
            echo '<br>in:<br>[' . (strstr($this->zf_sql, 'db_cache') ? 'db_cache table' : $this->zf_sql) . ']<br>';
        } else {
            echo 'WARNING: An Error occurred, please let us know!';
        }
        if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG == true) {
            echo ' If you were entering information, press the BACK button in your browser and re-check the information you had entered to be sure you entered valid data.<br>';
        }
        echo '</div>';

        // logging
        $backtrace_array = debug_backtrace();
        $query_factory_caller = '';
        foreach ($backtrace_array as $current_caller) {
            if (strcmp($current_caller['file'], __FILE__) != 0) {
                $query_factory_caller = ' ==> (as called by) ' . $current_caller['file'] . ' on line ' . $current_caller['line'] . ' <==';
                break;
            }
        }
        trigger_error('MySQL error ' . $this->error_number . ': ' . $this->error_text . ' :: ' . $this->zf_sql . $query_factory_caller, E_USER_ERROR);
    }

    /**
     * Get column properties for a table
     */
    public function metaColumns(string $tablename): array
    {
        $sql = "SHOW COLUMNS FROM `:tableName:`";
        $sql = $this->bindVars($sql, ':tableName:', $tablename, 'noquotestring');
        $res = $this->Execute($sql);
        foreach ($res as $result) {
            $obj [strtoupper($result['Field'])] = new queryFactoryMeta($result);
        }
        return $obj ?? [];
    }

    function get_server_info()
    {
        if ($this->link) {
            return mysqli_get_server_info($this->link);
        }

        return defined('UNKNOWN') ? UNKNOWN : 'UNKNOWN';
    }

    /**
     * If logging is enabled, log SELECT queries for later analysis
     * @param $sqlQuery
     */
    protected function logQuery($sqlQuery)
    {
        if (!defined('STORE_DB_TRANSACTIONS') || STORE_DB_TRANSACTIONS === 'false' || STORE_DB_TRANSACTIONS === false) {
            return;
        }
        global $PHP_SELF, $box_id, $current_page_base;

        if (strtoupper(substr($sqlQuery, 0, 6)) != 'SELECT' /*&& strstr($sqlQuery,'products_id')*/) {
            return;
        }
// optional isolation
//        if (strpos($sqlQuery, 'products_id') === false) {
//            return;
//        }

        $f = @fopen(DIR_FS_LOGS . '/query_selects_' . $current_page_base . '_' . time() . '.txt', 'ab');
        if ($f) {
            $backtrace = '';

            if (STORE_DB_TRANSACTIONS == 'backtrace') {
                ob_start();
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $backtrace = ob_get_clean();
                $backtrace = preg_replace('/^#0\s+' . __FUNCTION__ . '[^\n]*\n/', '', $backtrace, 1);
                $backtrace = 'query trace: ' . "\n" . $backtrace . "\n";
            }

            fwrite($f, "\n\n" . 'I AM HERE ' . $current_page_base . /*zen_get_all_get_params() .*/ "\n" . $backtrace . 'sidebox: ' . $box_id . "\n\n" . "Explain \n" . $sqlQuery . ";\n\n");
            fclose($f);
        }
        unset($f);
    }
}

class queryFactoryResult implements Countable, Iterator
{
    /**
     * Indicates if the result has reached the last row of data.
     *
     * @var boolean
     */
    public $EOF = true;

    /**
     * Indicates the current database row.
     *
     * @var int
     */
    public $cursor = 0;

    /**
     * Contains the data for the current database row (fields + values).
     *
     * @var array of field => value pairs
     */
    public $fields = [];

    /**
     * Indicates if the result is cached.
     *
     * @var boolean
     */
    public $is_cached = false;

    /**
     * Contains stored results of query
     *
     * @var array
     */
    public $result = [];

    /**
     * Contains randomized results if ExecuteRandomMulti was called
     *
     * @var array
     */
    public $result_random = [];

    /**
     * The maximum number of rows allowed to be iterated over.
     *
     * @var int
     */
    public $limit = null;

    /**
     * The raw result returned by the mysqli call.
     *
     * @var mysqli_result
     */
    public $resource;

    /**
     * @var string
     */
    public $sql_query = '';

    /**
     * @var mysqli MySQL connection link
     */
    public $link;

    /**
     * @param mysqli $link
     */
    function __construct($link)
    {
        $this->link = $link;
    }

    /* (non-PHPdoc)
     * @see Iterator::current()
     */
     #[ReturnTypeWillChange]
    public function current()
    {
        return $this->fields;
    }

    /* (non-PHPdoc)
     * @see Iterator::key()
     */
     #[ReturnTypeWillChange]
    public function key()
    {
        return $this->cursor;
    }

    /* (non-PHPdoc)
     * @see Iterator::next()
     */
     #[ReturnTypeWillChange]
    public function next()
    {
        $this->MoveNext();
    }

    /**
     * Moves the cursor to the next row.
     */
    public function MoveNext()
    {
        $this->cursor++;
        if (!$this->valid()) {
            $this->EOF = true;
        } else if ($this->is_cached) {
            if ($this->cursor >= count($this->result)) {
                $this->EOF = true;
            } else {
                $this->fields = array_replace($this->fields, $this->result[$this->cursor]);
            }
        } else if (!empty($this->result_random)) {
            if ($this->cursor < $this->limit) {
                $this->fields = array_replace($this->fields, $this->result[$this->result_random[$this->cursor]]);
            } else {
                $this->EOF = true;
            }
        } else {
            $zp_result_array = @mysqli_fetch_assoc($this->resource);
            $this->fields = array_replace($this->fields, $zp_result_array);
            if (!$zp_result_array) {
                $this->EOF = true;
                unset($this->fields);
            }
        }
    }

    /**
     * Moves to the next randomized result. Typically only used on a result generated by ExecuteRandomMulti
     */
    public function MoveNextRandom()
    {
        $this->cursor++;
        if ($this->cursor < $this->limit) {
            $this->fields = array_replace($this->fields, $this->result[$this->result_random[$this->cursor]]);
        } else {
            $this->EOF = true;
        }
    }

    /* (non-PHPdoc)
     * @see Iterator::rewind()
     */
     #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->EOF = ($this->RecordCount() == 0);
        if ($this->RecordCount() !== 0) {
            $this->Move(0);
        }
    }

    /* (non-PHPdoc)
     * @see Iterator::valid()
     */
     #[ReturnTypeWillChange]
    public function valid()
    {
        return $this->cursor < $this->RecordCount() && !$this->EOF;
    }

    /* (non-PHPdoc)
     * @see Iterator::count()
     */
     #[ReturnTypeWillChange]
    public function count()
    {
        return $this->RecordCount();
    }

    /**
     * Returns the number of rows (records).
     *
     * @return int
     */
    public function RecordCount()
    {
        if ($this->is_cached && is_countable($this->result)) {
            return count($this->result);
        }

        if (!empty($this->resource) && $this->resource instanceof mysqli_result) {
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
    public function Move($zp_row)
    {
        if ($this->is_cached) {
            if ($zp_row >= count($this->result)) {
                $this->cursor = count($this->result);
                $this->EOF = true;
            } else {
                $this->fields = array_replace($this->fields, $this->result[$zp_row]);
                $this->cursor = $zp_row;
                $this->EOF = false;
            }
        } else if (@mysqli_data_seek($this->resource, $zp_row)) {
            $this->fields = array_replace($this->fields, @mysqli_fetch_assoc($this->resource));
            $this->cursor = $zp_row;
            $this->EOF = false;
        } else {
            $this->EOF = true;
        }
    }
}

class queryFactoryMeta extends base
{
    public string $field;
    public string $type;
    public int $max_length;
    public bool $nullable;
    public bool $indexed;
    public ?string $default;
    public ?string $extra;
    public string $nativeType;

    public function __construct($field)
    {
        $this->field = $field['Field'];

        $type = $field['Type'];
        $rgx = preg_match('/^[a-z]*/', $type, $matches);
        $this->type = $matches[0];

        $this->max_length = (int)preg_replace('/[a-z\(\)]/', '', $type);
        if (empty($this->max_length)) {
           switch (strtoupper($type)) {
              case 'DATE':
                  $this->max_length = 10;
                  break;
              case 'DATETIME':
              case 'TIMESTAMP':
                  $this->max_length = 19; // ignores fractional which would be 26
                  break;
              case 'TINYTEXT':
                  $this->max_length = 255;
                  break;
              case 'INT':
                  $this->max_length = 11;
                  break;
              case 'TINYINT':
                  $this->max_length = 4;
                  break;
              case 'SMALLINT':
                  $this->max_length = 4;
                  break;
              default:
                  // This is antibugging code to prevent a fatal error
                  // You should not be here unless you have changed the db
                  $this->max_length = 8;
                  $this->notify('NOTIFY_QUERY_FACTORY_META_DEFAULT', ['field' => $field, 'type' => $type], $this->max_length);
                  break;
           }
        }

        $this->nullable = strtoupper($field['Null']) === 'YES';
        $this->indexed = !empty($field['Key']);
        $this->default = $field['Default'];
        $this->extra = $field['Extra'];

        $this->nativeType = $this->match_native_type($this->type);
        // reasonable to treat tinyint(1) as boolean
        if ($this->type === 'tinyint' && $this->max_length === 1) {
            $this->nativeType = 'bool';
        }
    }

    /**
     * Determine native scalar PHP type which most closely matches the db field type.
     * Basically anything that's not int|float will be treated as string here.
     * (more complex type matching/casting can be done in userland code)
     */
    protected function match_native_type(string $mysql_field_type): string
    {
        $mysql_field_type = strtoupper($mysql_field_type);

        if (preg_match('/(INT|BOOL)/', $mysql_field_type)) {
            return 'int';
        }
        if (preg_match('/(DECIMAL|NUMERIC|FIXED)/', $mysql_field_type)) {
            return 'float';
        }
        if (preg_match('/(FLOAT|DOUBLE)/', $mysql_field_type)) {
            return 'float';
        }
        if (preg_match('/(CHAR|TEXT|JSON|LONG)/', $mysql_field_type)) {
            return 'string';
        }
        if (preg_match('/(BLOB|BINARY)/', $mysql_field_type)) {
            return 'string';
        }
        if (preg_match('/(ENUM|SET)/', $mysql_field_type)) {
            return 'string';
        }
        if (preg_match('/TIME/', $mysql_field_type)) {
            return 'string';
        }
        return 'string';
    }
}
