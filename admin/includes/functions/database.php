<?php

/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Tue Aug 14 14:56:11 2012 +0100 Modified in v1.5.1 $
 */
function zen_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    global $db;
    reset($data);
    if ($action == 'insert') {
        $query = 'insert into ' . $table . ' (';
        while (list($columns, ) = each($data)) {
            $query .= $columns . ', ';
        }
        $query = substr($query, 0, -2) . ') values (';
        reset($data);
        while (list(, $value) = each($data)) {
            switch ((string) $value) {
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
        while (list($columns, $value) = each($data)) {
            switch ((string) $value) {
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

function zen_db_insert_id() {
    global $db;
    return $db->insert_ID();
}

function zen_db_output($string) {
    return htmlspecialchars($string, ENT_COMPAT, CHARSET, TRUE);
}

function zen_db_input($string) {
    return addslashes($string);
}

function zen_db_prepare_input($string, $trimspace = true) {
    if (is_string($string)) {
        if ($trimspace == true) {
            return trim(stripslashes($string));
        } else {
            return stripslashes($string);
        }
    } elseif (is_array($string)) {
        reset($string);
        while (list($key, $value) = each($string)) {
            $string[$key] = zen_db_prepare_input($value);
        }
        return $string;
    } else {
        return $string;
    }
}

/*
 * Checks if a column in the Table exists, also can add the column if needed
 */

function zen_db_check_table_field($tableName, $columnName, $add = false, $column_type = 'VARCHAR(72) NULL default NULL') {
    global $db;
    $return = false;
    $tableFields = $db->metaColumns($tableName);
    $columnNameUpper = strtoupper($columnName);
    foreach ($tableFields as $key => $value) {
        if ($key == $columnNameUpper) {
            $return = true;
        }
    }
    if ($add != false && $return == false) {
        $db->Execute("ALTER TABLE " . $tableName . " ADD " . $columnName . " " . $column_type . ";");
        $return = true;
    }
    return $return;
}
