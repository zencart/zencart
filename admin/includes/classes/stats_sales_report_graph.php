<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @author inspired from sales_report_graphs.php,v 0.01 2002/11/27 19:02:22 cwi Exp  Released under the GNU General Public License $
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Jul 19 Modified in v2.1.0-alpha1 $
 */
class statsSalesReportGraph
{
    const HOURLY_VIEW = 1;
    const DAILY_VIEW = 2;
    const WEEKLY_VIEW = 3;
    const MONTHLY_VIEW = 4;
    const QUARTERLY_VIEW = 6;
    const YEARLY_VIEW = 5;

    /** @var int Number of years to look backward in yearly mode */
    const LOOKBACK_YEARS = 10;

    protected
        $mode = self::MONTHLY_VIEW,
        $globalStartDate,
        $startDate,
        $endDate,
        $startDates = [],
        $endDates = [];
    public
        $info = [],
        $previous = '',
        $next = '',
        $filter = '',
        $filter_link,
        $filter_sql = '',
        $status_available = [],
        $status_available_size = 0,
        $size = 0;

    /**
     * statsSalesReportGraph constructor.
     *
     * startDate and endDate have to be a unix timestamp. Use mktime or strtotime!
     * if set then both have to be valid startDate and endDate
     *
     * @param integer $mode number indicating report format
     * @param string $startDate unix timestamp
     * @param string $endDate unix timestamp
     * @param string $filter filter string
     */
    public function __construct($mode, $startDate = '', $endDate = '', $filter = '')
    {
        global $db;

        $this->mode = $mode;
        // get date of first sale
        $first = $db->Execute("SELECT UNIX_TIMESTAMP(MIN(date_purchased)) as first FROM " . TABLE_ORDERS);
        $this->globalStartDate = mktime(0, 0, 0, date('m', $first->fields['first']), date('d', $first->fields['first']), date('Y', $first->fields['first']));

        // get all possible status for filter
        $ordersStatus = zen_getOrdersStatuses();
        $this->status_available = $ordersStatus['orders_statuses'];
        $this->status_available_size = count($ordersStatus['orders_statuses']);

        // -----
        // If supplied, the $startDate and $endDate are expected to be either:
        //
        // - UNIX-timestamp-formatted integer-strings, as specified by the 'link' parameter returned by this class' 'query' method.
        // - Integer values, as submitted by the SalesReportDashboardWidget.  In this case, the value must be >= 0.
        //
        // If neither, the report starts with all times today and earlier.
        //
        if ($this->validDateInput($startDate) === false || $this->validDateInput($endDate) === false) {
            // set startDate to nothing
            $dateGiven = false;
            $startDate = 0;
            // endDate is today
            $this->endDate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        } else {
            $dateGiven = true;
            if ($endDate > mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
                $this->endDate = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
            } else {
                // set endDate to the given Date with "round" on days
                $this->endDate = mktime(0, 0, 0, date('m', $endDate), date('d', $endDate) + 1, date('Y', $endDate));
            }
        }
        switch ($this->mode) {
            case self::HOURLY_VIEW:
                if ($dateGiven === true) {
                    // "round" to midnight
                    $this->startDate = mktime(0, 0, 0, date('m', $startDate), date('d', $startDate), date('Y', $startDate));
                    $this->endDate = mktime(0, 0, 0, date('m', $startDate), date('d', $startDate) + 1, date('Y', $startDate));
                    // size to number of hours
                    $this->size = 24;
                } else {
                    // startDate to start of this day
                    $this->startDate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $this->endDate = mktime(date("G") + 1, 0, 0, date('m'), date('d'), date('Y'));
                    // size to number of hours
                    $this->size = date('G') + 1;
                    if ($this->startDate < $this->globalStartDate) {
                        $this->startDate = $this->globalStartDate;
                    }
                }
                for ($i = 0; $i < $this->size; $i++) {
                    $this->startDates[$i] = mktime($i, 0, 0, date('m', $this->startDate), date('d', $this->startDate), date('Y', $this->startDate));
                    $this->endDates[$i] = mktime($i + 1, 0, 0, date('m', $this->startDate), date('d', $this->startDate), date('Y', $this->startDate));
                }
                break;

            case self::DAILY_VIEW:
                if ($dateGiven === true) {
                    // "round" to day
                    $this->startDate = mktime(0, 0, 0, date('m', $startDate), date('d', $startDate), date('Y', $startDate));
                    // size to number of days
                    $this->size = ($this->endDate - $this->startDate) / (60 * 60 * 24);
                } else {
                    // startDate to start of this week
                    $this->startDate = mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y'));
                    $this->endDate = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
                    // size to number of days
                    $this->size = date('w') + 1;
                    if ($this->startDate < $this->globalStartDate) {
                        $this->startDate = $this->globalStartDate;
                    }
                }
                for ($i = 0; $i < $this->size; $i++) {
                    $this->startDates[$i] = mktime(0, 0, 0, date('m', $this->startDate), date('d', $this->startDate) + $i, date('Y', $this->startDate));
                    $this->endDates[$i] = mktime(0, 0, 0, date('m', $this->startDate), date('d', $this->startDate) + ($i + 1), date('Y', $this->startDate));
                }
                break;

            case self::WEEKLY_VIEW:
                if ($dateGiven === true) {
                    $this->startDate = mktime(0, 0, 0, date('m', $startDate), date('d', $startDate) - date('w', $startDate), date('Y', $startDate));
                } else {
                    // startDate to beginning of first week of this month
                    $firstDayOfMonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
                    $this->startDate = mktime(0, 0, 0, date('m'), 1 - date('w', $firstDayOfMonth), date('Y'));
                }
                if ($this->startDate < $this->globalStartDate) {
                    $this->startDate = $this->globalStartDate;
                }
                // size to the number of weeks in this month till endDate
                $this->size = ceil((($this->endDate - $this->startDate + 1) / (60 * 60 * 24)) / 7);
                for ($i = 0; $i < $this->size; $i++) {
                    $this->startDates[$i] = mktime(0, 0, 0, date('m', $this->startDate), date('d', $this->startDate) +  $i * 7, date('Y', $this->startDate));
                    $this->endDates[$i] = mktime(0, 0, 0, date('m', $this->startDate), date('d', $this->startDate) + ($i + 1) * 7, date('Y', $this->startDate));
                }
                break;

            case self::MONTHLY_VIEW:
                if ($dateGiven === true) {
                    $this->startDate = mktime(0, 0, 0, date('m', $startDate), 1, date('Y', $startDate));
                    // size to number of days
                } else {
                    // startDate to first day of the first month of this year
                    $this->startDate = mktime(0, 0, 0, 1, 1, date('Y'));
                    // size to number of months in this year
                }
                if ($this->startDate < $this->globalStartDate) {
                    $this->startDate = mktime(0, 0, 0, date('m', $this->globalStartDate), 1, date('Y', $this->globalStartDate));
                }
                $this->size = (date('Y', $this->endDate) - date('Y', $this->startDate)) * 12 + (date('m', $this->endDate) - date('m', $this->startDate)) + 1;
                $tmpMonth = date('m', $this->startDate);
                $tmpYear = date('Y', $this->startDate);
                for ($i = 0; $i < $this->size; $i++) {
                    // the first of the $tmpMonth + $i
                    $this->startDates[$i] = mktime(0, 0, 0, $tmpMonth + $i, 1, $tmpYear);
                    // the first of the $tmpMonth + $i + 1 month
                    $this->endDates[$i] = mktime(0, 0, 0, $tmpMonth + $i + 1, 1, $tmpYear);
                }
                break;

            case self::YEARLY_VIEW:
                if ($dateGiven === true) {
                    $this->startDate = mktime(0, 0, 0, 1, 1, date('Y', $startDate));
                    $this->endDate = mktime(0, 0, 0, 1, 1, date('Y', $endDate) + 1);
                } else {
                    // startDate to first of current year minus self::LOOKBACK_YEARS
                    $this->startDate = mktime(0, 0, 0, 1, 1, date('Y') - self::LOOKBACK_YEARS);
                    // endDate to today
                    $this->endDate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                }
                if ($this->startDate < $this->globalStartDate) {
                    $this->startDate = $this->globalStartDate;
                }
                $this->size = date('Y', $this->endDate) - date('Y', $this->startDate) + 1;
                $tmpYear = date('Y', $this->startDate);
                for ($i = 0; $i < $this->size; $i++) {
                    $this->startDates[$i] = mktime(0, 0, 0, 1, 1, $tmpYear + $i);
                    $this->endDates[$i] = mktime(0, 0, 0, 1, 1, $tmpYear + $i + 1);
                }
                break;
        }

        if (in_array((int)$this->mode, [self::HOURLY_VIEW, self::DAILY_VIEW, self::WEEKLY_VIEW], false)) {
            // set previous to start - diff
            $tmpDiff = $this->endDate - $this->startDate;
            if ($this->size == 0) {
                $tmpUnit = 0;
            } else {
                $tmpUnit = $tmpDiff / $this->size;
            }

            switch($this->mode) {
                case self::HOURLY_VIEW:
                    $tmp1 =  24 * 60 * 60;
                    break;
                case self::DAILY_VIEW:
                    $tmp1 = 7 * 24 * 60 * 60;
                    break;
                case self::WEEKLY_VIEW:
                    $tmp1 = 30 * 24 * 60 * 60;
                    break;
            }
            $tmp = ceil($tmpDiff / $tmp1);
            if ($tmp > 1) {
                $tmpShift = ($tmp * $tmpDiff) + $tmpUnit;
            } else {
                $tmpShift = $tmp1 + $tmpUnit;
            }

            $tmpStart = $this->startDate - $tmpShift + $tmpUnit;
            $tmpEnd = $this->startDate - $tmpUnit;
            if ($tmpStart >= $this->globalStartDate) {
                $this->previous = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd;
            }
            $tmpStart = $this->endDate;
            $tmpEnd = $this->endDate + $tmpShift - 2 * $tmpUnit;
            if ($tmpEnd < mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
                $this->next = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd;
            } else {
                if ($tmpEnd - $tmpDiff < mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
                    $tmpEnd = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $this->next = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd;
                }
            }
        } elseif ((int)$this->mode == self::MONTHLY_VIEW) { 
            // compute previous link if data is there 
            $year = date('Y', $this->startDate) - 1; 
            $tmpStart = mktime(0,0,0,1,1,$year); 
            $tmpEnd = mktime(0,0,0,12,1,$year); 
            if (date('Y', $tmpStart) >= date('Y', $this->globalStartDate)) {
               $this->previous = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd;
            }

            // compute next link if data is there 
            $year = date('Y', $this->startDate) + 1; 
            $tmpStart = mktime(0,0,0,1,1,$year); 
            $tmpEnd = mktime(0,0,0,12,1,$year); 
            if (date('Y', $tmpEnd) <= date('Y')) {
               $this->next= "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd;
            }
        }

        // handle filters
        // submit the filters that way:
        // 01001 means use filter for status 2 and 5 set.
        $tmp = '';
        $tmp1 = '';
        if (is_string($filter) && strlen($filter) > 0) {
            for ($i = 0; $i < $this->status_available_size; $i++) {
                if (substr($filter, $i, 1) === '1') {
                    $tmp1 .= '1';
                    if (strlen($tmp) === 0) {
                        $tmp = "o.orders_status <> " . $this->status_available[$i]['id'];
                    } else {
                        $tmp .= " and o.orders_status <> " . $this->status_available[$i]['id'];
                    }
                } else {
                    $tmp1 .= '0';
                }
            }
        }
        $this->filter_sql = $tmp;
        $this->filter = $tmp1;
        $this->filter_link = "report=" . $this->mode . "&startDate=" . $startDate . "&endDate=" . $endDate;

        $this->query();
    }

    // -----
    // Called during class construction to validate the $startDate and $endDate submitted.  Each value is
    // expected to be either:
    //
    // - A UNIX-timestamp-formatted integer-string, as specified by the 'link' parameter returned by this class' 'query' method.
    // - Integer values, as submitted by the SalesReportDashboardWidget.  In this case, the value must be >= 0.
    //
    // Returns a boolean indication as to whether the value is (true) or is not (false) valid.
    //
    protected function validDateInput($date)
    {
        return ((is_int($date) && $date >= 0) || (is_string($date) && ctype_digit($date)));
    }

    protected function query()
    {
        global $db, $zcDate;

        $tmp_query =
            "SELECT SUM(ot.value) AS value, AVG(ot.value) AS avg, COUNT(ot.value) AS count
               FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o
              WHERE ot.orders_id = o.orders_id
                AND ot.class = 'ot_subtotal'";
        if (strlen($this->filter_sql) > 0) {
            $tmp_query .= " AND (" . $this->filter_sql . ")";
        }
        for ($i = 0; $i < $this->size; $i++) {
            $report = $db->Execute($tmp_query . " AND o.date_purchased >= '" . zen_db_input(date("Y-m-d\TH:i:s", $this->startDates[$i])) . "' AND o.date_purchased < '" . zen_db_input(date("Y-m-d\TH:i:s", $this->endDates[$i])) . "'", false, true, 1800);
            $this->info[$i]['sum'] = $report->fields['value'] ?? 0;
            $this->info[$i]['avg'] = $report->fields['avg'] ?? 0;
            $this->info[$i]['count'] = $report->fields['count'];
            $this->info[$i]['startDates'] = $this->startDates[$i];
            $this->info[$i]['endDates'] = $this->endDates[$i];
            switch ($this->mode) {
                case self::HOURLY_VIEW:
                    $this->info[$i]['link'] = '';
                    break;
                case self::DAILY_VIEW:
                    $this->info[$i]['link'] = "report=" . self::HOURLY_VIEW . "&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date('m', $this->endDates[$i]), date('d', $this->endDates[$i]) + 1, date('Y', $this->endDates[$i]));
                    break;
                case self::WEEKLY_VIEW:
                    $this->info[$i]['link'] = "report=" . self::DAILY_VIEW . "&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date('m', $this->endDates[$i]), date('d', $this->endDates[$i]) - 1, date('Y', $this->endDates[$i]));
                    break;
                case self::MONTHLY_VIEW:
                    $this->info[$i]['link'] = "report=" . self::WEEKLY_VIEW . "&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date('m', $this->endDates[$i]), date('d', $this->endDates[$i]) - 1, date('Y', $this->endDates[$i]));
                    break;
                case self::YEARLY_VIEW:
                    $this->info[$i]['link'] = "report=" . self::MONTHLY_VIEW . "&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date('m', $this->endDates[$i]) - 1, date('d', $this->endDates[$i]), date('Y', $this->endDates[$i]));
                    break;
            }
        }
        $tmp_query =  "SELECT SUM(ot.value) AS shipping FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o WHERE ot.orders_id = o.orders_id AND ot.class = 'ot_shipping'";
        for ($i = 0; $i < $this->size; $i++) {
            $report = $db->Execute($tmp_query . " AND o.date_purchased >= '" . zen_db_input(date("Y-m-d\TH:i:s", $this->startDates[$i])) . "' AND o.date_purchased < '" . zen_db_input(date("Y-m-d\TH:i:s", $this->endDates[$i])) . "'", false,true, 1800);
            $this->info[$i]['shipping'] = (isset($report->fields['shipping'])) ? $report->fields['shipping'] : 0;
        }
    }
}
