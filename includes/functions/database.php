<?php
/**
 * database functions and aliases into the $db queryFactory class
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 Modified in v2.0.1 $
 */

/**
 * Alias to $db->insert_ID() to get id of last inserted record
 * @return int
 */
function zen_db_insert_id()
{
    global $db;
    return $db->insert_ID();
}

/**
 * Alias to $db->prepare_input() for sanitizing db inserts
 * @param string $string
 * @return string
 */
function zen_db_input($string)
{
    global $db;
    return (empty($string) ? $string : $db->prepare_input($string));
}

/**
 * @deprecated use zen_output_string_protected() instead
 * @param string $string
 * @return string
 */
function zen_db_output(string $string)
{
    trigger_error('Call to deprecated function zen_db_output. Use zen_output_string_protected() ' . (IS_ADMIN_FLAG ? 'for single encoding or consider htmlspecialchars() to support original double encoding ' : '') . 'instead', E_USER_DEPRECATED);

    if (IS_ADMIN_FLAG) {
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, true);
    }

    return zen_output_string_protected($string);
  }

/**
 * Rudimentary input sanitizer
 * NOTE: SHOULD NOT BE USED FOR DB QUERIES!!!  Use $db->prepare_input() or zen_db_input() instead
 *
 * @param string|null $string
 * @param bool $trimspace
 * @return array|string
 */
function zen_db_prepare_input($string, bool $trimspace = true)
{

    if (!IS_ADMIN_FLAG && is_string($string)) {
        $string = zen_sanitize_string($string);
    }
    if (is_string($string)) {
        if ($trimspace == true) {
            return trim(stripslashes($string));
        } else {
            return stripslashes($string);
        }
    } elseif (is_array($string)) {
        foreach ($string as $key => $value) {
            $string[$key] = zen_db_prepare_input($value);
        }
        return $string;
    } else {
        return $string;
    }
}


/**
 * Performs an INSERT or UPDATE based on a supplied array of field data.
 * (Similar to $db->perform() but with only a 2D array.
 *  If type-cast binding is required, use $db->perform instead.)
 *
 * @param string $tableName table on which to perform the insert/update
 * @param array $tableData key-value pairs -- all will be treated as strings, and will be escaped
 * @param string $performType INSERT or UPDATE
 * @param string $whereCondition condition for UPDATE (exclude the word "WHERE")
 * @return queryFactoryResult
 */
function zen_db_perform(string $tableName, array $tableData, $performType = 'INSERT', string $whereCondition = ''): queryFactoryResult
{
    global $db;
    if (strtolower($performType) === 'insert') {
        $query = 'INSERT INTO ' . $tableName . ' (';
        foreach ($tableData as $columns => $value) {
            $query .= $columns . ', ';
        }
        $query = substr($query, 0, -2) . ') VALUES (';
        foreach ($tableData as $value) {
            $value = (string)$value;
            switch ($value) {
                case 'now()':
                    $query .= 'now(), ';
                    break;
                case 'NULL':
                case 'null':
                    $query .= 'null, ';
                    break;
                default:
                    $query .= '\'' . $db->prepare_input($value) . '\', ';
                    break;
            }
        }
        $query = substr($query, 0, -2) . ')';
    } elseif (strtolower($performType) === 'update') {
        $query = 'UPDATE ' . $tableName . ' SET ';
        foreach ($tableData as $columns => $value) {
            $value = (string)$value;
            switch ($value) {
                case 'now()':
                    $query .= $columns . ' = now(), ';
                    break;
                case 'NULL':
                case 'null':
                    $query .= $columns . ' = null, ';
                    break;
                default:
                    $query .= $columns . ' = \'' . $db->prepare_input($value) . '\', ';
                    break;
            }
        }
        $query = substr($query, 0, -2) . ' WHERE ' . $whereCondition;
    }

    return $db->Execute($query);
}

/**
 * zen_db_perform equiv for language-specific inserts
 *
 * @param string $tableName
 * @param array $tableData
 * @param string $keyIdName
 * @param int $keyId
 * @param int $languageId
 * @return queryFactoryResult
 */
function zen_db_perform_language(string $tableName, array $tableData, string $keyIdName, int $keyId, int $languageId)
{
    global $db;
    $sql = "INSERT INTO " . $tableName . "(" . $db->prepare_input($keyIdName) . ", languages_id, ";
    foreach ($tableData as $columns => $value) {
        $sql .= $columns . ', ';
    }
    $sql = substr($sql, 0, -2) . ') values (' . (int)$keyId . ", " . (int)$languageId . ", ";
    foreach ($tableData as $value) {
        switch ((string)$value) {
            case 'now()':
                $sql .= 'now(), ';
                break;
            case 'null':
                $sql .= 'null, ';
                break;
            default:
                $sql .= '\'' . $db->prepare_input($value) . '\', ';
                break;
        }
    }
    $sql = substr($sql, 0, -2) . ')';
    $sql .= ' ON DUPLICATE KEY UPDATE ';
    foreach ($tableData as $columns => $value) {
        switch ((string)$value) {
            case 'now()':
                $sql .= $columns . ' = now(), ';
                break;
            case 'null':
                $sql .= $columns .= ' = null, ';
                break;
            default:
                $sql .= $columns . ' = \'' . $db->prepare_input($value) . '\', ';
                break;
        }
    }
    $sql = substr($sql, 0, -2);
    return $db->Execute($sql);
}


/** @deprecated
 * Return a random row from a database query
 */
function zen_random_select($query) {
    trigger_error('Call to deprecated function zen_random_select. Use $db->ExecuteRandomMulti() instead', E_USER_DEPRECATED);

    global $db;
    $random_query = $db->Execute($query);
    $num_rows = $random_query->RecordCount();
    if ($num_rows > 1) {
        $random_row = zen_rand(0, ($num_rows - 1));
        $random_query->Move($random_row);
    }
    return $random_query;
}
