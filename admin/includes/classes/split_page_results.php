<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 04 Modified in v2.0.0-rc1 $
 */

class splitPageResults
{
    /**
     * @var int
     */
    protected $num_pages;
    /**
     * @var string
     */
    protected $page_sql = '';
    /**
     * @var array
     */
    protected $pages_array = [];
    /**
     * @var int
     */
    protected $nextPage;
    /**
     * @var int
     */
    protected $previousPage;
    /**
     * @var string
     */
    protected $sql_after = '';
    /**
     * @var string
     */
    protected $sql_before = '';
    /**
     * @var string
     */
    protected $raw_sql_query = '';
    /**
     * Paginate-by-letter mode
     * Enabled by setting $letterGroupColumn, and optionally $letterGrouplength
     *
     * @var bool
     */
    protected $paginateByLetter = false;
    /**
     * If a field name is passed here, then pages will not be numbered, but rather grouped by results starting with same the first letter.
     * (Set $letterGroupLength to > 1 to group on more than 1 character)
     *
     * @var string
     */
    protected $letterGroupColumn = '';
    /**
     * Specifies the number of characters to group pages by if $letterGroupColumn is specified.
     * Default is -1 for no grouping. Or 1 if $letterGroupColumn is set but no length specified.
     * @var int
     */
    protected $letterGroupLength = 1;

    /**
     * @var int
     */
    protected $totalByLetter = 0;
    /**
     * The current page number
     * @var int
     */
    protected $current_page_number;
   /**
     * The total number of rows
     * @var int
     */
    protected $number_of_rows;
    /**
     * The maximum number of rows to display on a page
     * @var int
     */
    protected $number_of_rows_per_page;

    /**
     * @param int $current_page_number
     * @param int $max_rows_per_page
     * @param string $sql_query
     * @param int $query_num_rows
     * @param string $letterGroupColumn column name to build letter-nav from (optional)
     * @param int $letterGroupLength number of characters to sort/filter on for letterGroupColumn
     */
    public function __construct(&$current_page_number, $max_rows_per_page, &$sql_query, &$query_num_rows, $letterGroupColumn = '', $letterGroupLength = 0)
    {
        global $db;

        $this->letterGroupColumn = zen_db_input($letterGroupColumn);
        if (!empty($letterGroupColumn)) {
            $this->paginateByLetter = true;
            if (empty($letterGroupLength)) $letterGroupLength = 1;
        }
        $this->letterGroupLength = (int)$letterGroupLength;

        $sql_query = preg_replace("/\n\r|\r\n|\n|\r/", " ", $sql_query);
        $this->raw_sql_query = $sql_query;

        $pos_to = strlen($sql_query);

        // find from clause
        $query_upper = strtoupper($sql_query);
        $pos_from = strpos($query_upper, ' FROM', 0);

        $pos_where = strrpos($query_upper, ' WHERE', $pos_from);
        if ($pos_where) {
            $where_sql = " AND ";
            $pos_search = $pos_where;
        } else {
            $where_sql = " WHERE ";
            $pos_search = $pos_from;
        }

        // find end of where clause
        $pos_group_by = strpos($query_upper, ' GROUP BY', $pos_search);
        if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

        $pos_having = strpos($query_upper, ' HAVING', $pos_search);
        if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

        $pos_order_by = strpos($query_upper, ' ORDER BY', $pos_search);
        if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

        $query_num_rows = $this->numberRows($sql_query);

        // Default behaviour
        if ($this->paginateByLetter === false) {
            if (empty($max_rows_per_page)) $max_rows_per_page = $query_num_rows;
            if ($query_num_rows == 0) $max_rows_per_page = 1;

            if (empty($current_page_number)) $current_page_number = 1;
            $current_page_number = (int)$current_page_number;

            $num_pages = ceil($query_num_rows / $max_rows_per_page);
            if ($current_page_number > $num_pages) {
                $current_page_number = $num_pages;
            }
            $offset = ($max_rows_per_page * ($current_page_number - 1));

            $this->sql_before = $sql_query;
            $this->sql_after = '';

            // fix offset error on some versions
            if ($offset < 0) {
                $offset = 0;
            }

            $sql_query .= " LIMIT " . $offset . ", " . $max_rows_per_page;

            for ($i = 1; $i <= $num_pages; $i++) {
                $this->pages_array[] = ['id' => $i, 'text' => $i];
            }

            $this->num_pages = $num_pages;

            if ($current_page_number > 1) $this->previousPage = $current_page_number - 1;
            if ($current_page_number < $num_pages) $this->nextPage = $current_page_number + 1;

        } else {
            // first store the total results number for display later.
            $this->totalByLetter = $query_num_rows;
            // store the find-page-by-letter version of the query.
            $this->page_sql = "SELECT DISTINCT UCASE(SUBSTRING(" . $letterGroupColumn . ", 1, " . $letterGroupLength . ")) AS letter";
            $this->page_sql .= substr($sql_query, $pos_from, ($pos_to - $pos_from));

            $sql = $this->page_sql . " ORDER BY letter";
            $pages_by_letter = $db->Execute($sql);

            $this->page_sql .= $where_sql;

            $this->num_pages = $num_pages = $pages_by_letter->RecordCount();

            if (empty($current_page_number) && $current_page_number !== '0') $current_page_number = $pages_by_letter->fields['letter'];

            foreach ($pages_by_letter as $page) {
                if ($page['letter'] < $current_page_number) {
                    $this->previousPage = $page['letter'];
                }
                if ($page['letter'] > $current_page_number && empty($this->nextPage)) {
                    $this->nextPage = $page['letter'];
                }
                $this->pages_array[] = ['id' => $page['letter'], 'text' => $page['letter']];
            }

            $sql = substr($sql_query, 0, $pos_to) . $where_sql;
            $this->sql_before = $sql;
            if (isset($current_page_number)) {
                $sql .= "SUBSTRING(" . $this->letterGroupColumn . ", 1, " . $letterGroupLength . ") = '" . $current_page_number . "' ";
            } else {
                $sql .= $this->letterGroupColumn . " IS NULL ";
            }
            $this->sql_after = substr($sql_query, $pos_to + 1);
            $sql .= $this->sql_after;

            $sql_query = $sql;
            $query_num_rows = $this->numberRows($sql_query);
        }
    }
    /**
     * NOTE:  Takes a query and counts the number of rows in that query.
     *
     * @param string $sql
     * @return int
     */

    private function numberRows(string $sql) {
        global $db;
        // the following line makes use of a CTE which is only available with mysql 8 or mariadb-10.x
//        $countSQL = 'WITH countresults AS (' . $sql . ') SELECT count(*) as total FROM countresults';
        $countSQL = 'SELECT count(*) as total FROM (' . $sql . ') countresults';

        try {
            $count_result = $db->Execute($countSQL);
        } catch (Throwable $e) {
            trigger_error('exception-> ' . json_encode($e));
        }
        if ((is_object($count_result) && $count_result->EOF) || !empty($e)) {
            return 0;
        }
        return (int) $count_result->fields['total'];
    }

    /**
     * NOTE: This causes the queries to be run multiple times on the same page. Could be slow with very complex joins or large numbers of records in results.
     *
     * @param int $current_page_number
     * @param int $max_rows_per_page
     * @param string $sql_query
     * @param string $criteria_field
     * @param string $criteria_value
     */
    public function findPage(&$current_page_number, $max_rows_per_page, &$sql_query, $criteria_field, $criteria_value)
    {
        global $db;
        if ($this->paginateByLetter === true) {
            // Calculate the required page from the required record
            $sql = $this->page_sql . ' ' . zen_db_input($criteria_field) . "='" . zen_db_input($criteria_value) . "'";
            $check_page = $db->Execute($sql);
            $letter = $check_page->fields['letter'];
            $sql_query = $this->sql_before;
            if (!empty($letter)) {
                $sql_query .= "SUBSTRING(" . $this->letterGroupColumn . ", 1, " . $this->letterGroupLength . ") = '" . $letter . "' ";
            } else {
                $sql_query .= $this->letterGroupColumn . " IS NULL ";
            }
            $current_page_number = $letter;
            $sql_query .= $this->sql_after;
        } else {
            // The full query is run here because the page number of a record is controlled by the 'order by' clause, and therefore
            // there is no simple count(*) SQL which will do it easier than by counting all the retrieved records and comparing each.
            $check_page = $db->Execute($this->raw_sql_query);
            $check_count = 0;

            // strip any table reference from the criteria field
            $cfield = strrchr($criteria_field, '.');
            if (false === $cfield) {
                $cfield = $criteria_field;
            } else {
                $cfield = substr($cfield, 1);
            }

            if (empty($max_rows_per_page)) $max_rows_per_page = 2;

            if ($check_page->RecordCount() > $max_rows_per_page) {
                foreach ($check_page as $page) {
                    if ($page[$cfield] == $criteria_value) {
                        break;
                    }
                    $check_count++;
                }
            }
            $sql_query = $this->sql_before;
            $current_page_number = ceil($check_count / $max_rows_per_page) + 1;
            $offset = ($max_rows_per_page * ($current_page_number - 1));
            $sql_query .= " LIMIT " . $offset . ", " . $max_rows_per_page;
        }
    }


    /**
     * @param int $query_numrows
     * @param int $max_rows_per_page
     * @param int $max_page_links
     * @param int $current_page_number
     * @param string $parameters form URI parameters
     * @param string $page_name $_GET param for page number
     * @return string
     */
    public function display_links($query_numrows, $max_rows_per_page, $max_page_links, $current_page_number, $parameters = '', $page_name = 'page')
    {
        global $PHP_SELF;

        $displayAsDropdown = (!$this->paginateByLetter || $this->letterGroupLength !== 1);

        if (!empty($parameters) && substr($parameters, -1) != '&') $parameters .= '&';

        if ($this->num_pages > 1) {
            $display_links = '';
            if ($displayAsDropdown) $display_links .= zen_draw_form('pages', basename($PHP_SELF, '.php'), '', 'get');

            if (!empty($this->previousPage)) {
                $display_links .= '<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $this->previousPage) . '" class="splitPageLink">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';
            } else {
                $display_links .= '<span style="visibility:hidden;">' . PREVNEXT_BUTTON_PREV . '&nbsp;&nbsp;</span>';
            }

            if ($displayAsDropdown) {
                $dropdown = zen_draw_pull_down_menu($page_name, $this->pages_array, $current_page_number, 'onChange="this.form.submit();"');
                $display_links .= $dropdown;
//                $display_links .= sprintf(TEXT_RESULT_PAGE, $dropdown, $this->num_pages);
            } else {
                foreach ($this->pages_array as $page_id) {
                    $display_links .= '<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $page_id['id']) . '" class="splitPageLink' . ($page_id['id'] === $current_page_number ? ' splitPageLinkCurrent' : '') . '">' . $page_id['id'] . '</a>&nbsp;&nbsp;';
                }
            }

            if (!empty($this->nextPage)) {
                $display_links .= '&nbsp;&nbsp;<a href="' . zen_href_link(basename($PHP_SELF), $parameters . $page_name . '=' . $this->nextPage) . '" class="splitPageLink">' . PREVNEXT_BUTTON_NEXT . '</a>';
            } else {
                $display_links .='<span style="visibility:hidden;">' .  '&nbsp;&nbsp;' . PREVNEXT_BUTTON_NEXT . '</span>';
            }
            if ($displayAsDropdown) {
                if (!empty($parameters)) {
                    $parameters = rtrim($parameters, '&');
                    $pairs = explode('&', $parameters);
                    foreach ($pairs as $pair) {
                        [$key, $value] = explode('=', $pair);
                        $display_links .= zen_draw_hidden_field(rawurldecode($key), rawurldecode($value));
                    }
                }

                if (defined('SID') && !empty(SID)) $display_links .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

                $display_links .= '</form>';
            }
        } else {
            $display_links = sprintf(TEXT_RESULT_PAGE, $this->num_pages, $this->num_pages);
        }

        return $display_links;
    }

    /**
     * @param int $query_numrows
     * @param int $max_rows_per_page
     * @param int $current_page_number
     * @param string $text_output
     * @return string
     */
    public function display_count($query_numrows, $max_rows_per_page, $current_page_number, $text_output)
    {
        if ($this->paginateByLetter) {
            return sprintf($text_output, $query_numrows, $this->totalByLetter);
        }

        $current_page_number = (int)$current_page_number;
        if ($max_rows_per_page == 0) $max_rows_per_page = 20;
        $to_num = ($max_rows_per_page * $current_page_number);
        if ($to_num > $query_numrows) $to_num = $query_numrows;
        $from_num = ($max_rows_per_page * ($current_page_number - 1));
        if ($to_num === 0) {
            $from_num = 0;
        } else {
            $from_num++;
        }

        return sprintf($text_output, $from_num, $to_num, $query_numrows);
    }
}
