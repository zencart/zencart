<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Leonard 2024 Oct 02 Modified in v2.1.0 $
 */

// Normally this zen_date_raw function will ONLY be defined here.
// It was formerly inside english.php and sometimes in override files, and you should remove from those places now.
// If you truly need to redefine this function, do that in an extra_configures file so that it takes precedence before this one.
if (!function_exists('zen_date_raw')) {
    /**
     * Return date in raw format
     *
     * $date should be in format mm/dd/yyyy or dd/mm/yyyy
     * raw date is in format YYYYMMDD, or DDMMYYYY or YYYYMMDD
     *
     * @param string $date
     * @param bool $reverse
     * @return string
     */
    function zen_date_raw($date, $reverse = false) {
        // sometimes zen_date_short is called with a zero-date value which returns false, which is then passed to $date here, so this just reformats to avoid confusion.
        if (empty($date) || strpos($date, '0001') || strpos($date, '0000')) {
            $date = DateTime::createFromFormat('!m/d/Y', '01/01/0001')->format(DATE_FORMAT);
        }

		$date = preg_replace('/\D+/', '', $date);
		$date_format = str_replace(['/', '-'], '', DATE_FORMAT);

        if ($date_format === 'dmY') {
            if ($reverse) {
                return substr($date, 0, 2) . substr($date, 2, 2) . substr($date, 4, 4);
            } else {
                return substr($date, 4, 4) . substr($date, 2, 2) . substr($date, 0, 2);
            }
        } elseif ($date_format === 'Ymd') {
            if ($reverse) {
                return substr($date, 6, 2) . substr($date, 4, 2) . substr($date, 0, 4);
            } else {
                return substr($date, 0, 4) . substr($date, 4, 2) . substr($date, 6, 2);
            }
        } elseif ($reverse) {
            return substr($date, 2, 2) . substr($date, 0, 2) . substr($date, 4, 4);
        } else {
            return substr($date, 4, 4) . substr($date, 0, 2) . substr($date, 2, 2);
        }
    }
}


/**
 * Validate a date in the selected locale date format
 *
 * @param string $date
 * @param string $format (optional) needs to be a valid short date format for DateTimeImmutableObject using / or - or nothing as separators
 * @return bool
 */
function zen_valid_date(string $date, string $format = DATE_FORMAT): bool
{
	// Build 3 formats from 1 with 3 possible separators
	$format0 = str_replace('-', '/', $format);
	$format1 = str_replace('/', '-', $format);
    $format2 = str_replace(['/','-'], '', $format);
    $d0 = DateTime::createFromFormat('!' . $format0, $date);
    $d1 = DateTime::createFromFormat('!' . $format1, $date);
    $d2 = DateTime::createFromFormat('!' . $format2, $date);
    return ($d0 && $d0->format($format0) == $date) || ($d1 && $d1->format($format1) == $date) || ($d2 && $d2->format($format2) == $date);
}


/**
 * Output a raw date string in the selected locale date format
 *
 * @param string $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
 * @return bool|false|string
 */
function zen_date_long($raw_date)
{
    if (empty($raw_date) || $raw_date <= '0001-01-01 00:00:00') return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    global $zcDate;
    return $zcDate->output(DATE_FORMAT_LONG, mktime($hour, $minute, $second, $month, $day, $year));
}


/**
 * Output a raw date string in the selected locale date format
 *
 * @param string $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
 * @return bool|false|string|string[]|null
 */
function zen_date_short($raw_date)
{
    if (empty($raw_date) || $raw_date <= '0001-01-01 00:00:00') return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
}


function zen_datetime_short($raw_datetime)
{
    if (empty($raw_datetime) || $raw_datetime <= '0001-01-01 00:00:00') return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    global $zcDate;
    return $zcDate->output(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
}

/**
 * Return locale-formatted date and time without seconds (ie. 2024/10/01 9:54)
 */
function zen_datetime_without_seconds (string $raw_datetime): string
{
    if (empty($raw_datetime) || $raw_datetime <= '0001-01-01 00:00:00') return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);

    zen_define_default('DATE_TIME_FORMAT_WITHOUT_SECONDS', '%m/%d/%Y %H:%M');

    global $zcDate;
    return $zcDate->output(DATE_TIME_FORMAT_WITHOUT_SECONDS, mktime($hour, $minute, 0, $month, $day, $year));
}

/**
 * @param $date
 * @param string $formatOut
 * @param $formatIn
 * @return string
 */
function zen_format_date_raw($date, $formatOut = 'mysql', $formatIn = null)
{
    if ($formatIn === null && defined('DATE_FORMAT_DATEPICKER_ADMIN')) $formatIn = DATE_FORMAT_DATEPICKER_ADMIN;
    if ($date == 'null' || $date == '') return $date;
    $mpos = strpos($formatIn, 'm');
    $dpos = strpos($formatIn, 'd');
    $ypos = strpos($formatIn, 'y');
    $d = substr($date, $dpos, 2);
    $m = substr($date, $mpos, 2);
    $y = substr($date, $ypos, 4);
    switch ($formatOut) {
        case 'raw':
            $mdate = $y . $m . $d;
            break;
        case 'raw-reverse':
            $mdate = $d . $m . $y;
            break;
        case 'mysql':
            $mdate = $y . '-' . $m . '-' . $d;

    }
    return $mdate;
}

/**
 * Check date
 * @param string $date_to_check
 * @param string $format_string
 * @param array $date_array updated by reference
 * @return bool and also updates $date_array by reference
 */
function zen_checkdate($date_to_check, $format_string, &$date_array)
{
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
        return false;
    }

    $size = count($separators);
    for ($i = 0; $i < $size; $i++) {
        $pos_separator = strpos($date_to_check, $separators[$i]);
        if ($pos_separator != false) {
            $date_separator_idx = $i;
            break;
        }
    }

    for ($i = 0; $i < $size; $i++) {
        $pos_separator = strpos($format_string, $separators[$i]);
        if ($pos_separator != false) {
            $format_separator_idx = $i;
            break;
        }
    }

    if (!isset($date_separator_idx, $format_separator_idx) || $date_separator_idx != $format_separator_idx) {
        return false;
    }

    if ($date_separator_idx != -1) {
        $format_string_array = explode($separators[$date_separator_idx], $format_string);
        if (count($format_string_array) != 3) {
            return false;
        }

        $date_to_check_array = explode($separators[$date_separator_idx], $date_to_check);
        if (count($date_to_check_array) != 3) {
            return false;
        }

        $size = count($format_string_array);
        for ($i = 0; $i < $size; $i++) {
            if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
            if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
            if (($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa')) $year = $date_to_check_array[$i];
        }
    } else {
        if (strlen($format_string) == 8 || strlen($format_string) == 9) {
            $pos_month = strpos($format_string, 'mmm');
            if ($pos_month != false) {
                $month = substr($date_to_check, $pos_month, 3);
                $size = count($month_abbr);
                for ($i = 0; $i < $size; $i++) {
                    if ($month == $month_abbr[$i]) {
                        $month = $i;
                        break;
                    }
                }
            } else {
                $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
            }
        } else {
            return false;
        }

        $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
        $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
        return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
        return false;
    }

    if ($month > 12 || $month < 1) {
        return false;
    }

    if ($day < 1) {
        return false;
    }

    if (zen_is_leap_year($year)) {
        $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
        return false;
    }

    $date_array = array($year, $month, $day);

    return true;
}

/**
 * Check if year is a leap year
 * @param int $year
 * @return bool
 */
function zen_is_leap_year($year)
{
    if ($year % 100 == 0) {
        if ($year % 400 == 0) return true;
    } else {
        if (($year % 4) == 0) return true;
    }

    return false;
}


/**
 * compute the days between two dates
 * @param string $date1
 * @param string $date2
 * @return int
 */
function zen_date_diff($date1, $date2)
{
    //$date1  today, or any other day
    //$date2  date to check against

    $d1 = explode("-", substr($date1, 0, 10));
    $y1 = $d1[0];
    $m1 = $d1[1];
    $d1 = $d1[2];

    $d2 = explode("-", substr($date2, 0, 10));
    $y2 = $d2[0];
    $m2 = $d2[1];
    $d2 = $d2[2];

    $date1_set = mktime(0, 0, 0, $m1, $d1, $y1);
    $date2_set = mktime(0, 0, 0, $m2, $d2, $y2);

    return (int)round(($date2_set - $date1_set) / (60 * 60 * 24));
}


/**
 * function to evaluate two date spans and identify if they overlap or not.
 * Returns true (overlap) if:
 *  A datespan is provided as an array and that array does not have the key 'start' nor 'end' (warning log entry also made by trigger_error).
 *  When seeking overlaps in the future:
 *  -  If the date spans both never end, OR
 *  -  If one date span never ends then if the maximum of the two start dates is less than the known to be future end
 *       date where the start date for a forever in the past date range was set to the earliest of the current date or associated end date. OR
 *  -  If the end dates are specified, then if the end dates occur in the future and the maximum start date is less
 *       than the minimum end date where the start date for a forever in the past date range was set to the earliest of the current date or associated end date.
 *  When seeking overlaps in the past:
 *  -  If the date spans both never end and they both started before today, OR
 *  -  If they both started forever in the past
 *  -  If the end dates are specified, then if the start dates occur in the past and the maximum start date is less
 *       than the minimum end date.
 *  Otherwise when seeking the presence of overlap at all (and the basis for the above logic), then basically
 *    if the maximum start date (last date range) is before the earliest end date, then that indicates that the
 *    two were active at the same time.
 *
 * Returns false (no overlap) otherwise:
 *
 * Usage: zen_datetime_overlap(array('start'=>$startdate, 'end'=>$enddate), array('start'=>$startdate, 'end'=>$enddate));
 *        zen_datetime_overlap(array('start'=>$startdate, 'end'=>$enddate), array('start'=>$startdate, 'end'=>$enddate), null, null, {default:true, false, 'past'});
 *        (if dates provided where null is in line above, they will be disregarded because of the array in positions 1 and 2.)
 *        zen_datetime_overlap($startdate1, array('start'=>$startdate, 'end'=>$enddate), $enddate1, null, {default:true, false, 'past'});
 *        zen_datetime_overlap(array('start'=>$startdate, 'end'=>$enddate), $startdate2, null, $enddate2, {default:true, false, 'past'});
 *        zen_datetime_overlap($startdate1, $startdate2, $enddate1, $enddate2, {default:true, false, 'past'});
 *        Providing $future_only of true (or as default not providing anything), the dates are inspected for overlap
 *
 * $start1 array() with keys 'start' and 'end' or as a raw_datetime or raw_date, or if null then this datetime is considered as in place forever in the past.
 * $start2 array() with keys 'start' and 'end' or as a raw_datetime or raw_date, or if null then this datetime is considered as in place forever in the past.
 * $end1 raw_datetime, raw_date or effectively blank (if $start1 is array, the value here is replaced, otherwise this datetime is considered eternally effective)
 * $end2 raw_datetime, raw_date or effectively blank (if $start2 is array, the value here is replaced, otherwise this datetime is considered eternally effective)
 * $future_only boolean or string of 'past': values should be true, false, or 'past'
 * returns a boolean true/false.  In error case of array provided without proper keys true returned and warning log also generated
 */
function zen_datetime_overlap($start1, $start2, $end1 = null, $end2 = null, $future_only = true)
{
    $cur_datetime = date("Y-m-d h:i:s", time());

    // BOF if variable is provided as an array, validate properly setup and if so, assign and replace the other applicable values.
    if (is_array($start1)) {
        if (!array_key_exists('start', $start1) || !array_key_exists('end', $start1)) {
            trigger_error('Missing date/time array key(s) start and/or end.', E_USER_WARNING);
            // array is not properly defined to support further operation, therefore to prevent potential downstream issues fail safe and identify that an overlap has occurred.
            return true;
        } else {
            $end1 = $start1['start'];
            $start1 = $start1['end'];
        }
    }
    if (is_array($start2)) {
        if (!array_key_exists('start', $start2) || !array_key_exists('end', $start2)) {
            trigger_error('Missing date/time array key(s) start and/or end.', E_USER_WARNING);
            // array is not properly defined to support further operation, therefore to prevent potential downstream issues fail safe and identify that an overlap has occurred.
            return true;
        } else {
            $end2 = $start2['start'];
            $start2 = $start2['end'];
        }
    }
    // EOF if variable is provided as an array, validate properly setup and if so, assign and replace the other applicable values.

    // BOF ensure all variables have a non-null value
    if (!isset($start1)) {
        $start1 = '0001-01-01 00:00:00';
    }
    if (!isset($start2)) {
        $start2 = '0001-01-01 00:00:00';
    }
    if (!isset($end1)) {
        $end1 = '0001-01-01 00:00:00';
    }
    if (!isset($end2)) {
        $end2 = '0001-01-01 00:00:00';
    }
    // EOF ensure all variables have a non-null value

    // BOF check for and correct condition where known dates are provided but swapped as in start date happens after the end date.
    if ($start1 > '0001-01-01 00:00:00' && $end1 > '0001-01-01 00:00:00' && $end1 < $start1) {
        $swap = $end1;
        $end1 = $start1;
        $start1 = $swap;
    }
    if ($start2 > '0001-01-01 00:00:00' && $end2 > '0001-01-01 00:00:00' && $end2 < $start2) {
        $swap = $end2;
        $end2 = $start2;
        $start2 = $swap;
    }
    // EOF check for and correct condition where known dates are provided but swapped as in start date happens after the end date.

    // Consider how to use forever start dates with regards to $future only....
    // Area of concern is for example a date span was entered in the past with an end date only.
    //  If later a date span is entered also with an end date only, both spans could be evaluated as overlapping
    //  in the past because they were "always" applicable.  But in regards to e-commerce, they could not be made
    //  effective until they were in the database.  ZC typically considers this ever available in the past condition
    //  for even initial entry and does not "require" that the date be entered of when it was first added and in
    //  some cases will prevent that date from being stored if it results in the event being effective in the past.
    if ($future_only === true && $start1 <= '0001-01-01 00:00:00') {
        $start1 = min($end1, $cur_datetime);
    }
    if ($future_only === true && $start2 <= '0001-01-01 00:00:00') {
        $start2 = min($end2, $cur_datetime);
    }

    // if either date ends in the forever future, evaluate the condition.
    if ($end1 <= '0001-01-01 00:00:00' || $end2 <= '0001-01-01 00:00:00') {
        if (($future_only !== 'past' || $start1 < $cur_datetime && $start2 < $cur_datetime) && $end1 <= '0001-01-01 00:00:00' && $end2 <= '0001-01-01 00:00:00') {
            return true; // both dates extend out to the future and therefore do or will at some point overlap.
        }

        $end = max($end1, $end2); //one date extends out to the future, but overlap only occurs up to the point of the known date.
        if ($future_only === true && $end <= $cur_datetime || $future_only === 'past' && min($start1, $start2) > $cur_datetime) {
            return false; //dates may overlap in the past, but because not in the present when considering future_only do not overlap.
        }
        $overlap = max($start1, $start2) < $end; // if the latest starting date occurs before the earliest known date, then they overlap, if not, then they are disjointed.
    } else {
        if ($future_only === true && max($end1, $end2) <= $cur_datetime || $future_only === 'past' && min($start1, $start2) > $cur_datetime) {
            return false; // with both end dates known, and both on or before today, then when considering future overlaps only an overlap in the future does not exist.
        } else {
            $overlap = max($start1, $start2) < min($end1, $end2); // if the latest starting date occurs before the earliest known date, then they overlap, if not, then they are disjointed.
        }
    }

    return $overlap;
}


function zen_count_days($start_date, $end_date, $lookup = 'm')
{
    if ($lookup == 'd') {
        // Returns number of days
        $start_datetime = gmmktime(0, 0, 0, substr($start_date, 5, 2), substr($start_date, 8, 2), substr($start_date, 0, 4));
        $end_datetime = gmmktime(0, 0, 0, substr($end_date, 5, 2), substr($end_date, 8, 2), substr($end_date, 0, 4));
        $days = (($end_datetime - $start_datetime) / 86400) + 1;
        $d = $days % 7;
        $w = date("w", $start_datetime);
        $result = floor($days / 7) * 5;
        $counter = $result + $d - (($d + $w) >= 7) - (($d + $w) >= 8) - ($w == 0);
    }
    if ($lookup == 'm') {
        // Returns whole-month-count between two dates
        // courtesy of websafe<at>partybitchez<dot>org
        $start_date_unixtimestamp = strtotime($start_date);
        $start_date_month = date("m", $start_date_unixtimestamp);
        $end_date_unixtimestamp = strtotime($end_date);
        $end_date_month = date("m", $end_date_unixtimestamp);
        $calculated_date_unixtimestamp = $start_date_unixtimestamp;
        $counter = 0;
        while ($calculated_date_unixtimestamp < $end_date_unixtimestamp) {
            $counter++;
            $calculated_date_unixtimestamp = strtotime($start_date . " +{$counter} months");
        }
        if (($counter == 1) && ($end_date_month == $start_date_month)) $counter = ($counter - 1);
    }
    return $counter;
}

if (!function_exists('datetime_to_sql_format')) {
    /**
     * Used especially for converting PayPal-IPN dates to a standard format for db storage
     */
    function datetime_to_sql_format(string $dateString, string $format = 'H:i:s M d, Y e'): string
    {
        $dateTime = DateTime::createFromFormat($format, $dateString);
        $dateTime->setTimezone((new DateTime)->getTimezone());
        return $dateTime->format('Y-m-d H:i:s');
    }
}

if (!function_exists('convertToLocalTimeZone')) {
    /** Used primarily to convert a time value from one timezone to another
     *  particularly when no timezone component is included in the time value.
     *  Mainly needed for converting 3rd party Zulu time values to local time
     */
    function convertToLocalTimeZone(string $dateTime, string $fromTz = 'UTC', string $outputFormat = 'Y-m-d H:i:s'): string
    {
        $localDateTime = new DateTime($dateTime, new DateTimeZone($fromTz));
        $localDateTime->setTimezone((new DateTime)->getTimezone());
        return $localDateTime->format($outputFormat);
    }
}
