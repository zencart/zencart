<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:39:26 2018 -0500 Modified in v1.5.6 $
 */


  function zen_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    global $db;
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      foreach($data as $columns => $value) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      foreach($data as $value) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . zen_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      foreach($data as $columns => $value) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . zen_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return $db->Execute($query);
  }
  function zen_db_perform_language($table, $data, $keyIdName, $keyId, $languageId, $link = 'db_link') {
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

  function zen_db_output($string) {
    return htmlspecialchars($string, ENT_COMPAT, CHARSET, TRUE);
  }

  function zen_db_input($string) {
    global $db;
    return $db->prepareInput($string);
  }

  function zen_db_prepare_input($string, $trimspace = true) {
    if (is_string($string)) {
      if ($trimspace == true) {
        return trim(stripslashes($string));
      } else {
        return stripslashes($string);
      }
    } elseif (is_array($string)) {
      foreach($string as $key => $value) {
        $string[$key] = zen_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }
