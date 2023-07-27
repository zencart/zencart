<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: pRose on charmes 2023 Feb 02 Modified in v1.5.8a $
 */


////
// Parse search string into individual objects
function zen_parse_search_string($search_str = '', &$objects = array()) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = preg_split('/[[:space:]]+/', $search_str);
    $objects = [];
    $tmpstring = '';
//    $flag = ''; // Not needed based on method of implementation.

    for ($k = 0, $p_count = count($pieces); $k < $p_count; $k++) {
        while (substr($pieces[$k], 0, 1) == '(' && strpos($pieces[$k], ')', 1) == false) {
            $objects[] = '(';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 1);
            } else {
                $pieces[$k] = '';
            }
        }

        $post_objects = [];

        while (substr($pieces[$k], -1) == ')' && strpos($pieces[$k], '(') == false)  {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
                $pieces[$k] = '';
            }
        }

// Check individual words

        if ((substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"')) {
            $objects[] = trim($pieces[$k]);

            for ($j = 0, $n = count($post_objects); $j < $n; $j++) {
                $objects[] = $post_objects[$j];
            }
        } else {
            /* This means that the $piece is either the beginning or the end of a string.
               So, we'll slurp up the $pieces and stick them together until we get to the
               end of the string or run out of pieces.
            */

// Add this word to the $tmpstring, starting the $tmpstring
            $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
            if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
//                $flag = 'off'; // Not needed in this loop and ignored later.

                $objects[] = trim($pieces[$k]);

                for ($j = 0, $n = count($post_objects); $j < $n; $j++) {
                    $objects[] = $post_objects[$j];
                }

                unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
                continue;
            }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
//            $flag = 'on'; // Setting this to 'on' supports at least one iteration of below. Not needed

// Move on to the next word
            $k++;

// Keep reading until the end of the string as long as the $flag is on

            while ($k < $p_count) {
                while (substr($pieces[$k], -1) == ')') {
                    $post_objects[] = ')';
                    if (strlen($pieces[$k]) > 1) {
                        $pieces[$k] = substr($pieces[$k], 0, -1);
                    } else {
                        $pieces[$k] = '';
                    }
                }

// If the word doesn't end in double quotes, append it to the $tmpstring.
                if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
                    $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
                    $k++;
                    continue;
                }
                /* If the $piece ends in double quotes, strip the double quotes, tack the
                   $piece onto the tail of the string, push the $tmpstring onto the $haves,
                   kill the $tmpstring, turn the $flag "off", and return.
                */
                $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for, trimming again, in case the above
// statement added a leading space.
                $objects[] = trim($tmpstring);

                for ($j = 0, $n = count($post_objects); $j < $n; $j++) {
                    $objects[] = $post_objects[$j];
                }

                unset($tmpstring);

// Turn off the flag to exit the loop
                break;
            }
        }
    }

// add default logical operators if needed
    $temp = [];
    for ($i = 0, $j = count($objects) - 1; $i < $j; $i++) {
        $temp[] = $objects[$i];
        if (!in_array($objects[$i], [
            'and',
            'or',
            '(',
            ], true)

            &&

            !in_array($objects[$i + 1], [
            'and',
            'or',
            ')',
            ], true)) {
            $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
        }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for ($i = 0; $i < $j + 1; $i++) {
        if ($objects[$i] === '(') {
            $balance--;
            continue;
        }
        if ($objects[$i] === ')') {
            $balance++;
            continue;
        }
        if (($objects[$i] === 'and') || ($objects[$i] === 'or')) {
            $operator_count++;
            continue;
        }
        if ((is_string($objects[$i]) && $objects[$i] == '0') || $objects[$i] && !in_array($objects[$i], ['(', ')'])) {
            $keyword_count++;
        }
    }

    if ($operator_count < $keyword_count && $balance < 1) {
        return true;
    }

    return false;
}

function zen_build_keyword_where_clause($fields, $string, $startWithWhere = false)
{
    global $db, $zco_notifier;

    $zco_notifier->notify('NOTIFY_BUILD_KEYWORD_SEARCH', '', $fields, $string);
    if (!zen_parse_search_string(stripslashes($string), $search_keywords)) {
        return '';
    }
    $where_str = ' AND (';
    if ($startWithWhere) {
        $where_str = ' WHERE (';
    }
    for ($i = 0, $n = count($search_keywords); $i < $n; $i++) {
        switch ($search_keywords[$i]) {
            case '(':
            case ')':
                break;
            case 'and':
            case 'or':
                $where_str .= ' ' . strtoupper($search_keywords[$i]) . ' ';
                break;
            default:
                $sql_add = ' (';
                $sql_or = ' ';
                foreach ($fields as $field_name) {
                    if ($is_id = strpos($field_name, '_id') && (int)$search_keywords[$i] === 0) {
                        continue;
                    }
                    $sql_add .= ($sql_or . ':field_name');
                    if ($is_id) {
                        $sql_add .= ' = :numeric_keyword';
                    } else {
                        $sql_add .= " LIKE '%:keyword%'";
                    }
                    $sql_add = $db->bindVars($sql_add, ':field_name', $field_name, 'noquotestring');
                    $sql_or = ' OR ';
                }
                $sql_add .= ') ';

                $where_str .= $sql_add;

                $where_str = $db->bindVars($where_str, ':keyword', addslashes($search_keywords[$i]), 'noquotestring');
                $where_str = $db->bindVars($where_str, ':numeric_keyword', $search_keywords[$i], 'integer');
                break;
        }
    }
    $where_str .= ' )';
    if (substr($where_str, -7) === '( ()  )') {
        return ' ';
    }
    return $where_str;
}
