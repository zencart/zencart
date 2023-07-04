<?php
/**
 * Sniffer Class.
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: dbltoe 2022 Nov 10 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Sniffer Class.
 * This class is used to collect information on the system that Zen Cart is running on
 * and to return error reports
 *
 */
class sniffer extends base {

    private
        $browser,
        $database,
        $php,
        $server;

  function __construct() {
    $this->browser = Array();
    $this->php = Array();
    $this->server = Array();
    $this->database = Array();
  }

  function table_exists($table_name) {
    global $db;
    $found_table = false;
    // Check to see if the requested Zen Cart table exists
    $sql = "SHOW TABLES like '".$table_name."'";
    $tables = $db->Execute($sql);
    //echo 'tables_found = '. $tables->RecordCount() .'<br>';
    if ($tables->RecordCount() > 0) {
      $found_table = true;
    }
    return $found_table;
  }

  function field_exists($table_name, $field_name) {
    global $db;
    $sql = "show fields from " . $table_name;
    $result = $db->Execute($sql);
    while (!$result->EOF) {
      // echo 'fields found='.$result->fields['Field'].'<br>';
      if  ($result->fields['Field'] == $field_name) {
        return true; // exists, so return with no error
      }
      $result->MoveNext();
    }
    return false;
  }

  function field_type($table_name, $field_name, $field_type, $return_found = false) {
    global $db;
    $sql = "show fields from " . $table_name;
    $result = $db->Execute($sql);
    while (!$result->EOF) {
      // echo 'fields found='.$result->fields['Field'].'<br>';
      if  ($result->fields['Field'] == $field_name) {
        if  ($result->fields['Type'] == $field_type) {
          return true; // exists and matches required type, so return with no error
        } elseif ($return_found) {
          return $result->fields['Type']; // doesn't match, so return what it "is", if requested
        }
      }
      $result->MoveNext();
    }
    return false;
  }

    /**
     * Return true if the specified row exists in the table.
     *
     * @param string $table_name The table to query.
     * @param string $key_name The key to check.
     * @param int    $key_value The value that key_name must equal.
     * @return void
     */
    public function rowExists(string $table_name, string $key_name, int $key_value): bool
    {
        global $db;
        $sql = 'SELECT COUNT(*) AS cc FROM :table_name WHERE :key_name = :key_value;';
        $sql = $db->bindVars($sql, ':key_name', $key_name, 'passthru');
        $sql = $db->bindVars($sql, ':key_value', $key_value, 'integer');
        $sql = $db->bindVars($sql, ':table_name', $table_name, 'passthru');
        $rs = $db->Execute($sql);
        return $rs->fields['cc'] != 0;
    }

    /**
     * Return true if the specified row exists in the table.
     * Key column names taken from $key_names are matched against equivalent
     * key values in $key_values.
     *
     * @param string $table_name The table to query.
     * @param array  $key_names The array of keys to check.
     * @param array  $key_values The array of values that key_names must equal.
     * @return void
     */
    public function rowExistsComposite(string $table_name, array $key_names, array $key_values): bool
    {
        global $db;
        $sql = 'SELECT COUNT(*) AS cc FROM :table_name WHERE ';
        $sql .= join(' AND ', array_map(
            function ($key, $value) {
                global $db;
                $bit = ':key = :value';
                $bit = $db->bindVars($bit, ':key', $key, 'passthru');
                $bit = $db->bindVars($bit, ':value', $value, 'integer');
                return $bit;
            },
            $key_names,
            $key_values
        ));
        $sql = $db->bindVars($sql, ':table_name', $table_name, 'passthru');
        $rs = $db->Execute($sql);
        return $rs->fields['cc'] != 0;
    }
}