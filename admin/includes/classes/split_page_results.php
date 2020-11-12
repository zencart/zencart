<?php
/**
 * @package admin
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: split_page_results.php 5617 2007-01-18 05:58:50Z drbyte $
 */

/*

	Default behaviour is to be exactly the same as the original split results 'class'
	if you set a a2zColumn then the pages are split on the distict values of the first
	(by defaut) character of that column or the $len characters if that is specified.

*/

  class splitPageResults {
	  var $pages_array;

    function splitPageResults(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows, $a2zColumn='', $len=1) {
      global $db;
	  $this->raw_sql_query = $sql_query;
	  // len of -1 means use default behavior
	  if( $a2zColumn == '' ) $len = -1;
	  $this->len = $len;
	  $this->a2zColumn = $a2zColumn;
      $pos_to = strlen($sql_query);


	 // find from clase
     $query_lower = strtolower($sql_query);
     $pos_from = strpos($query_lower, ' from', 0);

     $pos_distinct_start = strpos($query_lower, ' distinct', 0);
     $pos_distinct_end = strpos(substr($query_lower, $pos_distinct_start), ',', 0);

	 $pos_where =  strrpos($query_lower, ' where', $pos_from);
	if( $pos_where )
	{
		$where_sql = " and ";
                $pos_search = $pos_where;
	}
	else
	{
		$where_sql = " where ";
                $pos_search = $pos_from;
	}


		// find end of where clause
     $pos_group_by = strpos($query_lower, ' group by', $pos_search);
     if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

     $pos_having = strpos($query_lower, ' having', $pos_search);
     if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

     $pos_order_by = strpos($query_lower, ' order by', $pos_search);
     if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

      $sql = ($pos_distinct_start == 0) ? "select count(*) as total " : "select count(distinct " . substr($sql_query, $pos_distinct_start+9, $pos_distinct_end-9) . ") as total ";
      $sql .= substr($sql_query, $pos_from, ($pos_to - $pos_from));
      $reviews_count = $db->Execute($sql);

      $query_num_rows = $reviews_count->fields['total'];
	  $this->pages_array = array();

	  if( $len == -1 )
	  {
		// Defualt behaviour
        if (empty($current_page_number)) $current_page_number = 1;
		$num_pages = ceil($query_num_rows / $max_rows_per_page);
		if ($current_page_number > $num_pages) {
			$current_page_number = $num_pages;
		}
		$offset = ($max_rows_per_page * ($current_page_number - 1));

// fix offset error on some versions
		$this->sql_before = $sql_query;
		$this->sql_after = '';

		if ($offset < 0) { $offset = 0; }

		$sql_query .= " limit " . $offset . ", " . $max_rows_per_page;
	    for ($i=1; $i<=$num_pages; $i++) 
		{
		    $this->pages_array[] = array('id' => $i, 'text' => $i);
		}
		if( $current_page_number > 1) $this->previousPage = $current_page_number - 1;
		if( $current_page_number < $num_pages) $this->nextPage = $current_page_number + 1;
		$this->num_pages = $num_pages;
		$this->page_number_query = $sql . $where_sql;
	  }
	  else
	  {
		// store the find page version of the query.
		
		$this->page_sql = "Select distinct ucase(substring( $a2zColumn, 1, $len)) as letter";
		$this->page_sql .= substr($sql_query, $pos_from, ($pos_to - $pos_from));
		
		$sql = $this->page_sql . " order by letter";
		$page_count = $db->Execute($sql);
		$this->page_sql .= $where_sql;


		$num_pages = $page_count->RecordCount();
		$this->num_pages = $num_pages;

		if (empty($current_page_number) and $current_page_number !== '0' ) $current_page_number = $page_count->fields['letter'];


		  while (!$page_count->EOF) 
		  {
			if( $page_count->fields['letter'] < $current_page_number )
			{
			  $this->previousPage = $page_count->fields['letter']; 
			}
			if( $page_count->fields['letter'] > $current_page_number and empty($this->nextPage))
			{
			  $this->nextPage = $page_count->fields['letter']; 
			}
			$this->pages_array[] = array('id' => $page_count->fields['letter'], 'text' => $page_count->fields['letter']);

			$page_count->MoveNext();
		  }


		$sql = substr($sql_query, 0, $pos_to) . $where_sql;
		$this->sql_before = $sql;
		if( isset( $current_page_number ) )
		{
			$sql .= "substring( $this->a2zColumn, 1, $len) = '$current_page_number' ";
		}
		else
		{
			$sql .= "$this->a2zColumn is null ";
		}
		$this->sql_after = substr($sql_query, $pos_to + 1);
		$sql .= $this->sql_after;

		$sql_query = $sql;

	  }
    }

	function findPage( &$current_page_number, $max_rows_per_page,  &$sql_query,  $criteria_field, $criteria_value  )
	{
        global $db;
		if( $this->len != -1 )
		{
			// We can easily calculate the required page from the required recrord
			$sql = $this->page_sql . ' '. $criteria_field .'='. $criteria_value;
		    $check_page = $db->Execute($sql);
			$letter = $check_page->fields['letter'];
			$sql_query = $this->sql_before;
			if( !empty( $letter ) )
			{
				$sql_query .= "substring( $this->a2zColumn, 1, $this->len) = '$letter' ";
			}
			else
			{
				$sql_query .= "$this->a2zColumn is null ";
			}
			$current_page_number = $letter;
			$sql_query .= $this->sql_after;
		}
		else
		{
		  // could not think of a better way to do this as the page number of a record is controlled by the 'order by' clause
		  // so there is no simple count(*) sql which will do it easierly. So we have to count the records
		  $check_page = $db->Execute($this->raw_sql_query);
		  $check_count=0;

		  // strip any table reference from the criteria field which is used in the other search method above but not included in field names by the php function

		  $cfield = strrchr($criteria_field, '.'); 
		  if(!$cfield)
		  {
			$cfield = $criteria_field;
		  }
		  else
		  {
			  $cfield = substr( $cfield, 1 );
		  }


		  if ($check_page->RecordCount() > $max_rows_per_page) 
		  {
			while (!$check_page->EOF) 
			{
			  if ($check_page->fields[$cfield] == $criteria_value ) 
			  {
				break;
			  }
			  $check_count++;
			  $check_page->MoveNext();
			}
		  }
	      $sql_query = $this->sql_before;
			$current_page_number = floor($check_count/$max_rows_per_page) + 1;
			$offset = ($max_rows_per_page * ($current_page_number - 1));
			$sql_query .= " limit " . $offset . ", " . $max_rows_per_page;
		}
}


    function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page') {
      global $PHP_SELF;

	  $dropDown = false;
	  if( $this->len != 1 )
		  $dropDown = true;

	  if ( zen_not_null($parameters) && (substr($parameters, -1) != '&') ) $parameters .= '&';

// calculate number of pages needing links


      if ($this->num_pages > 1) {
		$display_links ='';
		if( $dropDown ) $display_links = zen_draw_form('pages', basename($PHP_SELF), '', 'get');

        if (!empty($this->previousPage)) {
          $display_links .= '<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $this->previousPage, 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';
        } else {
          $display_links .= PREVNEXT_BUTTON_PREV . '&nbsp;&nbsp;';
        }

		if( $dropDown )
		{
		  $display_links .= sprintf(TEXT_RESULT_PAGE, zen_draw_pull_down_menu($page_name, $this->pages_array, $current_page_number, 'onChange="this.form.submit();"'), $this->num_pages);
		}
		else
		{
		  foreach( $this->pages_array as $pageLetter )
		  {
			$display_links .= '<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $pageLetter['id'], 'NONSSL') . '" class="splitPageLink">' . $pageLetter['id'] . '</a>&nbsp;&nbsp;';
		  }
		}

        if (!empty($this->nextPage)) {
          $display_links .= '&nbsp;&nbsp;<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $this->nextPage, 'NONSSL') . '" class="splitPageLink">' . PREVNEXT_BUTTON_NEXT . '</a>';
        } else {
          $display_links .= '&nbsp;&nbsp;' . PREVNEXT_BUTTON_NEXT;
        }
		if( $dropDown )
		{
			if ($parameters != '') 
			{
			  if (substr($parameters, -1) == '&') $parameters = substr($parameters, 0, -1);
			  $pairs = explode('&', $parameters);
			  while (list(, $pair) = each($pairs)) 
			  {
				list($key,$value) = explode('=', $pair);
				$display_links .= zen_draw_hidden_field(rawurldecode($key), rawurldecode($value));
			  }
			}

			if (SID) $display_links .= zen_draw_hidden_field(zen_session_name(), zen_session_id());
			$display_links .= '</form>';
		}
      } else {
        $display_links = sprintf(TEXT_RESULT_PAGE, $this->num_pages,$this->num_pages);
      }
      return $display_links;
    }

    function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output) 
	{
	  if( $this->len == -1 )
	  {
		  $current_page_number = (int)$current_page_number;
		  $to_num = ($max_rows_per_page * $current_page_number);
		  if ($to_num > $query_numrows) $to_num = $query_numrows;
		  $from_num = ($max_rows_per_page * ($current_page_number - 1));
		  if ($to_num == 0) 
		  {
			$from_num = 0;
		  } 
		  else 
		  {
			$from_num++;
		  }
		  return sprintf($text_output, $from_num, $to_num, $query_numrows);
		}
		else
		{
		  return sprintf($text_output, $current_page_number, $current_page_number, $query_numrows);
		}
    }
  }
?>