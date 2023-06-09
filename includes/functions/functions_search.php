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
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
        while (substr($pieces[$k], 0, 1) == '(' && strpos($pieces[$k], ')', 1) == false) {
            $objects[] = '(';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 1);
            } else {
                $pieces[$k] = '';
            }
        }

        $post_objects = array();

        while (substr($pieces[$k], -1) == ')' && strpos($pieces[$k], '(') == false)  {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
                $pieces[$k] = '';
            }
        }

// Check individual words

        if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
            $objects[] = trim($pieces[$k]);

            for ($j=0, $n=count($post_objects); $j<$n; $j++) {
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
                $flag = 'off';

                $objects[] = trim($pieces[$k]);

                for ($j=0, $n=count($post_objects); $j<$n; $j++) {
                    $objects[] = $post_objects[$j];
                }

                unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
                continue;
            }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
            $flag = 'on';

// Move on to the next word
            $k++;

// Keep reading until the end of the string as long as the $flag is on

            while ( ($flag == 'on') && ($k < count($pieces)) ) {
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
                } else {
                    /* If the $piece ends in double quotes, strip the double quotes, tack the
                       $piece onto the tail of the string, push the $tmpstring onto the $haves,
                       kill the $tmpstring, turn the $flag "off", and return.
                    */
                    $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
                    $objects[] = trim($tmpstring);

                    for ($j=0, $n=count($post_objects); $j<$n; $j++) {
                        $objects[] = $post_objects[$j];
                    }

                    unset($tmpstring);

// Turn off the flag to exit the loop
                    $flag = 'off';
                }
            }
        }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
        $temp[] = $objects[$i];
        if ( ($objects[$i] != 'and') &&
            ($objects[$i] != 'or') &&
            ($objects[$i] != '(') &&
            ($objects[$i+1] != 'and') &&
            ($objects[$i+1] != 'or') &&
            ($objects[$i+1] != ')') ) {
            $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
        }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
        if ($objects[$i] == '(') $balance --;
        if ($objects[$i] == ')') $balance ++;
        if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
            $operator_count ++;
        } elseif ( (is_string($objects[$i]) && $objects[$i] == '0') || ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
            $keyword_count ++;
        }
    }

    if ( $operator_count < $keyword_count && $balance < 1) {
        return true;
    }

    return false;
}

    function zen_build_keyword_where_clause($fields, $string, $startWithWhere = false)
    {
        global $db, $zco_notifier;

        $zco_notifier->notify('NOTIFY_BUILD_KEYWORD_SEARCH', '', $fields, $string);
        if (empty($string)) {
            return '';
        }
        $where_str = '';
        if (zen_parse_search_string(stripslashes($string), $search_keywords)) {
            $where_str = " AND (";
            if ($startWithWhere) {
                $where_str = " WHERE (";
            }
            for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i++) {
                switch ($search_keywords[$i]) {
                    case '(':
                    case ')':
                        break;
                    case 'and':
                    case 'or':
                        $where_str .= " " . strtoupper($search_keywords[$i]) . " ";
                        break;
                    default:
                        $sql_add = " (";
                        $first_field = true;
                        $sql_or = ' ';
                        foreach ($fields as $field_name) {
                            if (!$first_field) {
                                $sql_or = ' OR ';
                            }
                            if (strpos($field_name, '_id')) {
                                if ((int)$search_keywords[$i] != 0) {
                                    $first_field = false;
                                    $sql_add .= $sql_or;
                                    $sql_add .= " :field_name = :numeric_keyword";
                                }
                            } else {
                                $first_field = false;
                                $sql_add .= $sql_or;
                                $sql_add .= " :field_name LIKE '%:keyword%'";
                            }
                            $sql_add = $db->bindVars($sql_add, ':field_name', $field_name, 'noquotestring');
                        }
                        $sql_add .= ") ";

                        $where_str .= $sql_add;

                        $where_str = $db->bindVars($where_str, ':keyword', addslashes($search_keywords[$i]), 'noquotestring');
                        $where_str = $db->bindVars($where_str, ':numeric_keyword', $search_keywords[$i], 'integer');
                        break;
                }
            }
            $where_str .= " )";
        }
        if (substr($where_str, -7) === '( ()  )') {
            return ' ';
        }
        return $where_str;
    }
