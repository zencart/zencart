<?php
/**
 * split_page_results Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Jan 9 13:13:41 2016 -0500 Modified in v1.5.5 $
 */

namespace Zencart\Paginator;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Split Page Result Class
 *
 * An sql paging class, that allows for sql result to be shown over a number of pages using simple navigation system
 * Overhaul scheduled for subsequent release
 *
 * @package classes
 */
class Paginator extends \base {
  var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $number_of_rows_per_page, $page_name;

  /* class constructor */
  function __construct($query, $max_rows, $count_key = '*', $page_holder = 'page', $debug = false, $countQuery = "") {
    global $db;
    $this->cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
    $max_rows = ($max_rows == '' || $max_rows == 0) ? 20 : $max_rows;

    $this->sql_query = preg_replace("/\n\r|\r\n|\n|\r/", " ", $query);
    if ($countQuery != "") $countQuery = preg_replace("/\n\r|\r\n|\n|\r/", " ", $countQuery);
    $this->countQuery = ($countQuery != "") ? $countQuery : $this->sql_query;
    $this->page_name = $page_holder;

    if ($debug) {
      echo '<br /><br />';
      echo 'original_query=' . $query . '<br /><br />';
      echo 'original_count_query=' . $countQuery . '<br /><br />';
      echo 'sql_query=' . $this->sql_query . '<br /><br />';
      echo 'count_query=' . $this->countQuery . '<br /><br />';
    }
    if (isset($_GET[$page_holder])) {
      $page = $_GET[$page_holder];
    } elseif (isset($_POST[$page_holder])) {
      $page = $_POST[$page_holder];
    } else {
      $page = '';
    }

    if (empty($page) || !is_numeric($page)) $page = 1;
    $this->current_page_number = $page;

    $this->number_of_rows_per_page = $max_rows;

    $pos_to = strlen($this->countQuery);

    $query_lower = strtolower($this->countQuery);
    $pos_from = strpos($query_lower, ' from', 0);

    $pos_group_by = strpos($query_lower, ' group by', $pos_from);
    if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

    $pos_having = strpos($query_lower, ' having', $pos_from);
    if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

    $pos_order_by = strrpos($query_lower, ' order by', $pos_from);
    if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

    if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
      $count_string = 'distinct ' . zen_db_input($count_key);
    } else {
      $count_string = zen_db_input($count_key);
    }
    $count_query = "select count(" . $count_string . ") as total " . substr($this->countQuery, $pos_from, ($pos_to - $pos_from));
    if ($debug) {
      echo 'count_query=' . $count_query . '<br /><br />';
    }
    $count = $db->Execute($count_query);

    $this->number_of_rows = $count->fields['total'];

    $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

    if ($this->current_page_number > $this->number_of_pages) {
      $this->current_page_number = $this->number_of_pages;
    }

    $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    // fix offset error on some versions
    if ($offset <= 0) { $offset = 0; }

    $this->sql_query .= " limit " . ($offset > 0 ? $offset . ", " : '') . $this->number_of_rows_per_page;

  }

  /* class functions */

  // display split-page-number-links
  function display_links($max_page_links, $parameters = '', $outputAsUnorderedList = false, $navElementLabel = '') {
    global $request_type;
    if ($max_page_links == '') $max_page_links = 1;

    if ($this->number_of_pages <= 1) return;

    $display_links_string = $ul_elements = '';
    $counter_actual_page_links = 0;

    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

    // previous button - not displayed on first page
    $link = '<a href="' . zen_href_link($this->cmd, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title="' . PREVNEXT_TITLE_PREVIOUS_PAGE . '">' . PREVNEXT_BUTTON_PREV . '</a>';
    if ($this->current_page_number > 1) {
      $display_links_string .= $link . '&nbsp;&nbsp;';
      $ul_elements .= '  <li class="pagination-previous" aria-label="' . ARIA_PAGINATION_PREVIOUS_PAGE . '">' . $link . '</li>' . "\n";
    } else {
      // $ul_elements .= '  <li class="disabled pagination-previous">' . $link . '</li>' . "\n";
    }


    // check if number_of_pages > $max_page_links
    $cur_window_num = intval($this->current_page_number / $max_page_links);
    if ($this->current_page_number % $max_page_links) $cur_window_num++;

    $max_window_num = intval($this->number_of_pages / $max_page_links);
    if ($this->number_of_pages % $max_page_links) $max_window_num++;

    // previous group of pages
    $link = '<a href="' . zen_href_link($this->cmd, $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" title="' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . '" aria-label="' . ARIA_PAGINATION_ELLIPSIS_PREVIOUS . '">...</a>';
    if ($cur_window_num > 1) {
      $display_links_string .= $link;
      $ul_elements .= '  <li class="ellipsis">' . $link . '</li>' . "\n";
    } else {
      // $ul_elements .= '  <li class="ellipsis" aria-hidden="true">' . $link . '</li>' . "\n";
    }

    // page nn button
    for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
      if ($jump_to_page == $this->current_page_number) {
        $display_links_string .= '&nbsp;<strong class="current" aria-current="true" aria-label="' . ARIA_PAGINATION_CURRENT_PAGE . ', ' . sprintf(ARIA_PAGINATION_PAGE_NUM, $jump_to_page) . '">' . $jump_to_page . '</strong>&nbsp;';
        $ul_elements .= '  <li class="current active">' . $jump_to_page . '</li>' . "\n";
        $counter_actual_page_links++;
      } else {
        $link = '<a href="' . zen_href_link($this->cmd, $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" title="' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . '" aria-label="' . ARIA_PAGINATION_GOTO . sprintf(ARIA_PAGINATION_PAGE_NUM, $jump_to_page) . '">' . $jump_to_page . '</a>';
        $display_links_string .= '&nbsp;' . $link . '&nbsp;';
        $ul_elements .= '  <li>' . $link . '</li>' . "\n";
        $counter_actual_page_links++;
      }
    }

    // next group of pages
    if ($cur_window_num < $max_window_num) {
      $link = '<a href="' . zen_href_link($this->cmd, $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" title="' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . '" aria-label="' . ARIA_PAGINATION_ELLIPSIS_NEXT . '">...</a>';
      $display_links_string .= $link . '&nbsp;';
      $ul_elements .= '  <li class="ellipsis">' . $link . '</li>' . "\n";
    } else {
      // $ul_elements .= '  <li class="ellipsis" aria-hidden="true">' . $link . '</li>' . "\n";
    }

    // next button
    if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) {
      $link = '<a href="' . zen_href_link($this->cmd, $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title="' . PREVNEXT_TITLE_NEXT_PAGE . '" aria-label="' . ARIA_PAGINATION_NEXT_PAGE . '">' . PREVNEXT_BUTTON_NEXT . '</a>';
      $display_links_string .= '&nbsp;' . $link . '&nbsp;';
      $ul_elements .= '  <li class="pagination-next">' . $link . '</li>' . "\n";
    } else {
      // $ul_elements .= '  <li class="disabled pagination-next">' . $link . '</li>' . "\n";
    }

    // if no pagination needed, return blank
    if ($counter_actual_page_links == 0) return;

    // return <nav><ul> format with a-hrefs wrapped in <li>
    // not setting role="navigation" because we're using a <nav> element already.
    if ($outputAsUnorderedList) {
        $aria_label = empty($navElementLabel) ? ARIA_PAGINATION_ROLE_LABEL_GENERAL : sprintf(ARIA_PAGINATION_ROLE_LABEL_FOR, zen_output_string_protected($navElementLabel));
        $aria_label .= sprintf(ARIA_PAGINATION_CURRENTLY_ON, $this->current_page_number);
        return  '<nav class="pagination" aria-label="' . $aria_label . '">' . "\n" .
            '<ul class="pagination">' . "\n" .
            $ul_elements .
            '</ul>' . "\n" .
            '</nav>';
    }
    // return unformatted collection of a-hrefs
    return $display_links_string;
  }

  // display number of total products found
  function display_count($text_output) {
    $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
    if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

    $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    if ($to_num == 0) {
      $from_num = 0;
    } else {
      $from_num++;
    }

    if ($to_num <= 1) {
      // don't show count when 1
      return '';
    } else {
      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }
  }

  public function getSqlQuery()
  {
      return $this->sql_query;
  }
}
