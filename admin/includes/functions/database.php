<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:39:26 2018 -0500 Modified in v1.5.6 $
 */


  function zen_db_perform_language($table, $data, $keyIdName, $keyId, $languageId) {
    global $db;
    $sql = "INSERT INTO " . $table . "(" . $keyIdName . ", languages_id, ";
    foreach($data as $columns => $value) {
      $sql .= $columns . ', ';
    }
    $sql = substr($sql, 0, -2) . ') values (' . (int)$keyId . ", " . (int)$languageId . ", ";
    foreach($data as $value) {
      switch ((string)$value) {
        case 'now()':
          $sql .= 'now(), ';
          break;
        case 'null':
          $sql .= 'null, ';
          break;
        default:
          $sql .= '\'' . zen_db_input($value) . '\', ';
          break;
      }
    }
    $sql = substr($sql, 0, -2) . ')';
    $sql .= ' ON DUPLICATE KEY UPDATE ';
    foreach($data as $columns => $value) {
      switch ((string)$value) {
        case 'now()':
          $sql .= $columns . ' = now(), ';
          break;
        case 'null':
          $sql .= $columns .= ' = null, ';
          break;
        default:
          $sql .= $columns . ' = \'' . zen_db_input($value) . '\', ';
          break;
      }
    }
    $sql = substr($sql, 0, -2);
    return $db->Execute($sql);
  }

  function zen_db_insert_id() {
    global $db;
    return $db->insert_ID();
  }
