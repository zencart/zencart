<?php
/**
 * Sniffer Class.
 *
 * @package classes
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sniffer.php 5645 2007-01-21 00:40:03Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Sniffer Class.
 * This class is used to collect information on the system that Zen Cart is running on
 * and to return error reports
 *
 * @package classes
 */
class sniffer extends base {

  function sniffer() {
    $this->browser = Array();
    $this->php = Array();
    $this->server = Array();
    $this->database = Array();
    $this->phpBB = Array();
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
      // echo 'fields found='.$result->fields['Field'].'<br />';
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
      // echo 'fields found='.$result->fields['Field'].'<br />';
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
}
?>