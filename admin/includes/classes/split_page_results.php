<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 01 Modified in v1.5.7 $
 */
class splitPageResults
{
    function __construct(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows)
    {
      global $db;

      if ($max_rows_per_page == 0) $max_rows_per_page = 20;
      $sql_query = preg_replace("/\n\r|\r\n|\n|\r/", " ", $sql_query);

      if (empty($current_page_number)) $current_page_number = 1;
      $current_page_number = (int)$current_page_number;

      $pos_to = strlen($sql_query);

     $query_lower = strtolower($sql_query);
     $pos_from = strpos($query_lower, ' from', 0);

     $pos_distinct_start = strpos($query_lower, ' distinct', 0);
     $pos_distinct_end = strpos(substr($query_lower, $pos_distinct_start), ',', 0);

     $pos_group_by = strpos($query_lower, ' group by', $pos_from);
     if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

     $pos_having = strpos($query_lower, ' having', $pos_from);
     if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

     $pos_order_by = strpos($query_lower, ' order by', $pos_from);
     if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      $sql = ($pos_distinct_start == 0) ? "select count(*) as total " : "select count(distinct " . substr($sql_query, $pos_distinct_start+9, $pos_distinct_end-9) . ") as total ";
      $sql .= substr($sql_query, $pos_from, ($pos_to - $pos_from));
      $reviews_count = $db->Execute($sql);

      $query_num_rows = $reviews_count->fields['total'];

      if ($max_rows_per_page == '') $max_rows_per_page = $query_num_rows;
      if ($query_num_rows == 0) $max_rows_per_page = 1;
      $num_pages = ceil($query_num_rows / $max_rows_per_page);
      if ($current_page_number > $num_pages) {
        $current_page_number = $num_pages;
      }
      $offset = ($max_rows_per_page * ($current_page_number - 1));

// fix offset error on some versions
      if ($offset < 0) { $offset = 0; }

      $sql_query .= " limit " . $offset . ", " . $max_rows_per_page;
    }

    function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page') {
      global $PHP_SELF;
      $current_page_number = (int)$current_page_number;
      if ( zen_not_null($parameters) && (substr($parameters, -1) != '&') ) $parameters .= '&';
      if ($max_rows_per_page == 0) $max_rows_per_page = 20;
      if ($query_numrows == 0) return '';

// calculate number of pages needing links
      if ($max_rows_per_page == '' || $max_rows_per_page == 0) $max_rows_per_page = $query_numrows;
      $num_pages = ceil($query_numrows / $max_rows_per_page);

      $pages_array = array();
      for ($i=1; $i<=$num_pages; $i++) {
        $pages_array[] = array('id' => $i, 'text' => $i);
      }

      if ($num_pages > 1) {
        $display_links = zen_draw_form('pages', basename($PHP_SELF), '', 'get');

        if ($current_page_number > 1) {
          $display_links .= '<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . ($current_page_number - 1), 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';
        } else {
          $display_links .= '<span style="visibility:hidden;">'. PREVNEXT_BUTTON_PREV . '&nbsp;&nbsp;</span>';
        }

        $display_links .= sprintf(TEXT_RESULT_PAGE, zen_draw_pull_down_menu($page_name, $pages_array, $current_page_number, 'onChange="this.form.submit();"'), $num_pages);

        if (($current_page_number < $num_pages) && ($num_pages != 1)) {
          $display_links .= '&nbsp;&nbsp;<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . ($current_page_number + 1), 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_NEXT . '</a>';
        } else {
          $display_links .= '<span style="visibility:hidden;">&nbsp;&nbsp;' . PREVNEXT_BUTTON_NEXT . '</span>';
        }

        if ($parameters != '') {
          if (substr($parameters, -1) == '&') $parameters = substr($parameters, 0, -1);
          $pairs = explode('&', $parameters);
          foreach($pairs as $pair) {
            list($key,$value) = explode('=', $pair);
            $display_links .= zen_draw_hidden_field(rawurldecode($key), rawurldecode($value));
          }
        }

        if (SID) $display_links .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

        $display_links .= '</form>';
      } else {
        $display_links = sprintf(TEXT_RESULT_PAGE, $num_pages, $num_pages);
      }

      return $display_links;
    }

    function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output) {
      $current_page_number = (int)$current_page_number;
      if ($max_rows_per_page == 0) $max_rows_per_page = 20;
      if ($max_rows_per_page == '') $max_rows_per_page = $query_numrows;
      $to_num = ($max_rows_per_page * $current_page_number);
      if ($to_num > $query_numrows) $to_num = $query_numrows;
      $from_num = ($max_rows_per_page * ($current_page_number - 1));
      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $from_num, $to_num, $query_numrows);
    }
  }
