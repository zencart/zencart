<?php
/**
 * functions/audience.php
 * Builds output queries for customer segments
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jul 07 Modified in v1.5.7 $
 */

//
// @TODO turn into a class in later release...

  function get_audiences_list($query_category='email', $display_count='', $lookup_email_address ='') {
  // used to display drop-down list of available audiences in emailing modules:
  // ie: mail, gv_main, coupon_admin... and eventually newsletters too.
  // gets info from query_builder table

  include_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . "/", 'audience.php', 'false'));

  global $db;
  $count_array = array();
  $count = 0;
  if ($display_count == '') $display_count = AUDIENCE_SELECT_DISPLAY_COUNTS;

  // get list of queries in database table, based on category supplied
  $queries_list = $db->Execute("select query_name, query_string from " . TABLE_QUERY_BUILDER . " " .
                  "where query_category like '%" . $query_category . "%'");

  $audience_list = array();
  if ($queries_list->RecordCount() > 1) {  // if more than one query record found
    $audience_list[] = array('id' => '', 'text' => TEXT_SELECT_AN_OPTION); //provide a "not-selected" value
  }

  foreach ($queries_list as $query_list) {
    // if requested, show recordcounts at end of descriptions of each entry
    // This could slow things down considerably, so use sparingly !!!!
    if ($display_count=='true' || $display_count ==true ) {  // if it's literal 'true' or logical true
    $count_array = $db->Execute(parsed_query_string($query_list['query_string']) );
    $count = $count_array->RecordCount();
    }

    // generate an array consisting of 2 columns which are identical. Key and Text are same.
    // Thus, when the array is used in a Select Box, the key is the same as the displayed description
    // The key can then be used to get the actual select SQL statement using the get...addresses_query function, below.
    $audience_list[] = array('id' => $query_list['query_name'], 'text' => $query_list['query_name'] . ' (' . $count . ')');
  }

  //if this is called by an emailing module which offers individual customers as an option, add all customers email addresses as well.
  if ($query_category=='email') {
    $lookup_filter = ($lookup_email_address != '') ? ' AND customers_email_address= :lookupAddress: ' : '';
    $lookup_filter = $db->bindVars($lookup_filter, ':lookupAddress:', $lookup_email_address, 'string');
    $customers_values = $db->Execute("select customers_email_address, customers_firstname, customers_lastname " .
                  "from " . TABLE_CUSTOMERS . " WHERE customers_email_format != 'NONE' " .
                  $lookup_filter .
                  "order by customers_lastname, customers_firstname, customers_email_address");
    foreach ($customers_values as $customers_value) {
      $audience_list[] = array('id' => $customers_value['customers_email_address'],
                 'text' => $customers_value['customers_lastname'] . ', ' . $customers_value['customers_firstname'] . ' (' . $customers_value['customers_email_address'] . ')');
    }
  }
  // send back the array for display in the SELECT drop-down menu
  return $audience_list;
  }

  function get_audience_sql_query($selected_entry, $query_category='email') {
    // This is used to take the query_name selected in the drop-down menu or singular customer email address and
  // generate the SQL Select query to be used to build the list of email addresses to be sent to
  // it only returns a query name and query string (SQL SELECT statement)
  // the query string is then used in a $db->Execute() command for later parsing and emailing.
  global $db;
  $query_name='';
  $queries_list = $db->Execute("select query_name, query_string from " . TABLE_QUERY_BUILDER . " " .
                 "where query_category like '%" . $query_category . "%'");
//                 "where query_category = '" . $query_category . "'");

  foreach ($queries_list as $query_list) {
      if ($selected_entry == $query_list['query_name']) {
      $query_name   = $query_list['query_name'];
        $query_string = parsed_query_string($query_list['query_string']);
//echo 'GET_AUD_EM_ADDR_QRY:<br />query_name='.$query_name.'<br />query_string='.$query_string;
      }
  }
  //if no match found against queries listed in database, then $selected_entry must be an email address
  if ($query_name=='' && $query_category=='email') {
        $cust_email_address = zen_db_prepare_input($selected_entry);
        $query_name   = $cust_email_address;
        $query_string = "select customers_firstname, customers_lastname, customers_email_address
                              from " . TABLE_CUSTOMERS . "
                              where customers_email_address = '" . zen_db_input($cust_email_address) . "'";
    }
  //send back a 1-row array containing the query_name and the SQL query_string
  return array('query_name'=>$query_name, 'query_string'=>$query_string);
}

function parsed_query_string($read_string) {
  $good_string = '';
  // extract table names from sql strings, so that prefixes are supported.
  // this will also in the future be used to reconstruct queries from query_keys_list field in query_builder table.

  $allwords = explode( " ", $read_string );
  foreach( $allwords as $key => $val) {
    // find "{TABLE_" and extract that tablename
    if( substr( $val, 0, 7) == "{TABLE_"  && substr( $val, -1) == "}" ) { //check for leading and trailing {} braces
      $val = substr( $val, 2, strlen($val)-2);  // strip off braces.  Could also use str_replace(array('{','}'),'',$val);
      //now return the value of the CONSTANT with the name that $val has.  ie: TABLE_CUSTOMERS = zen_customers
      $val = constant($val);
    } elseif ( substr( $val, 0, 6) == "TABLE_" ) {
    //return the value of the CONSTANT with the name that $val has.  ie: TABLE_CUSTOMERS = zen_customers
      $val = constant($val);
    } elseif ( substr( $val, 0, 9) == '$SESSION:' ) {
      //return the value of the SESSION var indicated
      $param = str_replace('$SESSION:', '', $val);
      $val = $_SESSION[$param];
    }
    $good_string .= $val.' ';
   }
   return $good_string;
}
