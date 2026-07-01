<?php
declare(strict_types=1);

/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   Modified in v3.0.0 $
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
     * @since ZC v1.0.3
     */
    function zen_date_raw(string|false|null $date, bool $reverse = false): string
    {
        // sometimes zen_date_short is called with a zero-date value which returns false, which is then passed to $date here, so this just reformats to avoid confusion.
        if (empty($date) || strpos($date, '0001') !== false || strpos($date, '0000') !== false) {
            $emptyDate = DateTime::createFromFormat('!m/d/Y', '01/01/0001');
            $date = $emptyDate === false ? '01/01/0001' : $emptyDate->format(DATE_FORMAT);
        }

        $date = preg_replace('/\D+/', '', $date) ?? '';
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
 * @param ?string $format (optional) needs to be a valid short date format for DateTimeImmutableObject using / or - or nothing as separators
 * @return bool
 * @since ZC v2.0.0
 */
function zen_valid_date(string $date, ?string $format = null): bool
{
    $format ??= defined('DATE_FORMAT') ? (string)DATE_FORMAT : 'm/d/Y';

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
 * Parse a raw YYYY-MM-DD[ HH:MM:SS] string into a Unix timestamp, for the raw date/datetime formatters below.
 * @since ZC v3.0.0
 */
function zen_raw_datetime_to_timestamp(string $raw_datetime, bool $include_seconds = true): int|false
{
    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = $include_seconds ? (int)substr($raw_datetime, 17, 2) : 0;

    return mktime($hour, $minute, $second, $month, $day, $year);
}


/**
 * Output a raw date string in the selected locale date format
 *
 * @param ?string $raw_date accepts YYYY-MM-DD HH:MM:SS, or YYYY-MM-DD (time defaults to 00:00:00)
 * @return false|string
 * @since ZC v1.0.3
 */
function zen_date_long(?string $raw_date): string|false
{
    if (empty($raw_date) || $raw_date <= '0001-01-01 00:00:00') {
        return false;
    }

    $timestamp = zen_raw_datetime_to_timestamp($raw_date);
    if ($timestamp === false) {
        return false;
    }

    /** @var zcDate $zcDate */
    global $zcDate;
    return $zcDate->output(DATE_FORMAT_LONG, $timestamp);
}


/**
 * Output a raw date string in the selected locale date format
 *
 * @param ?string $raw_date accepts YYYY-MM-DD HH:MM:SS, or YYYY-MM-DD (time defaults to 00:00:00)
 * @return string|false
 * @since ZC v1.0.3
 */
function zen_date_short(?string $raw_date): string|false
{
    if (empty($raw_date) || $raw_date <= '0001-01-01 00:00:00') {
        return false;
    }

    $timestamp = zen_raw_datetime_to_timestamp($raw_date);
    if ($timestamp === false) {
        return false;
    }
    return date(DATE_FORMAT, $timestamp);
}


/**
 * @since ZC v1.0.3
 */
function zen_datetime_short(?string $raw_datetime): string|false
{
    if (empty($raw_datetime) || $raw_datetime <= '0001-01-01 00:00:00') {
        return false;
    }

    $timestamp = zen_raw_datetime_to_timestamp($raw_datetime);
    if ($timestamp === false) {
        return false;
    }

    global $zcDate;
    return $zcDate->output(DATE_TIME_FORMAT, $timestamp);
}

/**
 * Return locale-formatted date and time without seconds (ie. 2024/10/01 9:54)
 * @since ZC v2.1.0
 */
function zen_datetime_without_seconds(string $raw_datetime): string|false
{
    if (empty($raw_datetime) || $raw_datetime <= '0001-01-01 00:00:00') {
        return false;
    }

    zen_define_default('DATE_TIME_FORMAT_WITHOUT_SECONDS', '%m/%d/%Y %H:%M');

    $timestamp = zen_raw_datetime_to_timestamp($raw_datetime, false);
    if ($timestamp === false) {
        return false;
    }

    global $zcDate;
    return $zcDate->output(DATE_TIME_FORMAT_WITHOUT_SECONDS, $timestamp);
}

/**
 * $date is a date string, such as 2022-01-15, 20220115, 01/15/2022, etc.
 * $formatIn is the format of the date that is passed in, using lowercase mm/dd/yyyy placeholders to describe the format.
 * $formatOut is the format that the date should be returned in: mysql/raw/raw-reverse; any other value defaults to mysql format
 * @since ZC v1.5.2
 */
function zen_format_date_raw(string $date, string $formatOut = 'mysql', ?string $formatIn = null): string
{
    if ($formatIn === null) {
        $formatIn = defined('DATE_FORMAT_DATE_PICKER')
            ? DATE_FORMAT_DATE_PICKER
            : (defined('DOB_FORMAT_STRING') ? DOB_FORMAT_STRING : 'mm/dd/yyyy');
    }
    if ($date === 'null' || $date === '') {
        return $date;
    }
    $mpos = strpos($formatIn, 'm');
    $dpos = strpos($formatIn, 'd');
    $ypos = strpos($formatIn, 'y');
    if ($mpos === false || $dpos === false || $ypos === false) {
        return $date;
    }
    // This parses based on 2-digit day, 2-digit month, 4-digit year
    $d = substr($date, $dpos, 2);
    $m = substr($date, $mpos, 2);
    $y = substr($date, $ypos, 4);
    return match ($formatOut) {
        'raw' => $y . $m . $d,
        'raw-reverse' => $d . $m . $y,
        default => $y . '-' . $m . '-' . $d, // also 'mysql'
    };
}

/**
 * Check date
 *
 * @param array<int, int> $date_array updated by reference
 * @param-out array<int, int> $date_array
 * @since ZC v1.0.3
 */
function zen_checkdate(string $date_to_check, string $format_string, array &$date_array): bool
{
    $separators = ['-', ' ', '/', '.'];
    $month_abbr = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    $no_of_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) !== strlen($format_string)) {
        return false;
    }

    $size = count($separators);
    for ($i = 0; $i < $size; $i++) {
        $pos_separator = strpos($date_to_check, $separators[$i]);
        if ($pos_separator !== false) {
            $date_separator_idx = $i;
            break;
        }
    }

    for ($i = 0; $i < $size; $i++) {
        $pos_separator = strpos($format_string, $separators[$i]);
        if ($pos_separator !== false) {
            $format_separator_idx = $i;
            break;
        }
    }

    if (!isset($date_separator_idx, $format_separator_idx) || $date_separator_idx !== $format_separator_idx) {
        return false;
    }

    $format_string_array = explode($separators[$date_separator_idx], $format_string);
    if (count($format_string_array) !== 3) {
        return false;
    }

    $date_to_check_array = explode($separators[$date_separator_idx], $date_to_check);
    if (count($date_to_check_array) !== 3) {
        return false;
    }

    $size = count($format_string_array);
    for ($i = 0; $i < $size; $i++) {
        if ($format_string_array[$i] === 'mm' || $format_string_array[$i] === 'mmm') {
            $month = $date_to_check_array[$i];
        }
        if ($format_string_array[$i] === 'dd') {
            $day = $date_to_check_array[$i];
        }
        if (($format_string_array[$i] === 'yyyy') || ($format_string_array[$i] === 'aaaa')) {
            $year = $date_to_check_array[$i];
        }
    }

    if (!isset($year, $month, $day)) {
        return false;
    }

    if (strlen($year) !== 4) {
        return false;
    }

    if (!is_numeric($month)) {
        $month = array_search(strtolower((string)$month), $month_abbr, true);
        if ($month === false) {
            return false;
        }
        ++$month;
    }

    if (!is_numeric($year) || !is_numeric($day)) {
        return false;
    }

    $year = (int)$year;
    $month = (int)$month;
    $day = (int)$day;

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

    $date_array = [$year, $month, $day];

    return true;
}

/**
 * Check if year is a leap year
 * @since ZC v1.0.3
 */
function zen_is_leap_year(int $year): bool
{
    if ($year % 100 === 0) {
        if ($year % 400 === 0) {
            return true;
        }
    } else {
        if (($year % 4) === 0) {
            return true;
        }
    }

    return false;
}


/**
 * compute the days between two dates
 * @since ZC v1.3.9a
 */
function zen_date_diff(string $date1, string $date2): int
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

    $date1_set = mktime(0, 0, 0, (int)$m1, (int)$d1, (int)$y1);
    $date2_set = mktime(0, 0, 0, (int)$m2, (int)$d2, (int)$y2);

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
 * @param array{start?: string, end?: string}|string|null $start1 array with keys 'start' and 'end' or as a raw_datetime or raw_date, or if null then this datetime is considered as in place forever in the past.
 * @param array{start?: string, end?: string}|string|null $start2 array with keys 'start' and 'end' or as a raw_datetime or raw_date, or if null then this datetime is considered as in place forever in the past.
 * @param ?string $end1 raw_datetime, raw_date or effectively blank (if $start1 is array, the value here is replaced, otherwise this datetime is considered eternally effective)
 * @param ?string $end2 raw_datetime, raw_date or effectively blank (if $start2 is array, the value here is replaced, otherwise this datetime is considered eternally effective)
 * @param bool|string $future_only values should be true, false, or 'past'
 * @return bool In error case of array provided without proper keys true returned and warning log also generated
 * @since ZC v1.5.6
 */
function zen_datetime_overlap(array|string|null $start1, array|string|null $start2, ?string $end1 = null, ?string $end2 = null, bool|string $future_only = true): bool
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


/**
 * @since ZC v1.3.0
 */
function zen_count_days(string $start_date, string $end_date, string $lookup = 'm'): float|int
{
    $counter = 0; // also serves as the fallback return value for an unrecognized $lookup
    if ($lookup === 'd') {
        // Returns number of days
        $start_datetime = gmmktime(0, 0, 0, (int)substr($start_date, 5, 2), (int)substr($start_date, 8, 2), (int)substr($start_date, 0, 4));
        $end_datetime = gmmktime(0, 0, 0, (int)substr($end_date, 5, 2), (int)substr($end_date, 8, 2), (int)substr($end_date, 0, 4));
        if ($start_datetime === false || $end_datetime === false) {
            return 0;
        }
        $days = (($end_datetime - $start_datetime) / 86400) + 1;
        $d = $days % 7;
        $w = date("w", $start_datetime);
        $result = floor($days / 7) * 5;
        $counter = $result + $d - (($d + $w) >= 7) - (($d + $w) >= 8) - ($w == 0);
    }
    if ($lookup === 'm') {
        // Returns whole-month-count between two dates
        // courtesy of websafe<at>partybitchez<dot>org
        $start_date_unixtimestamp = strtotime($start_date);
        if ($start_date_unixtimestamp === false) {
            return 0;
        }
        $start_date_month = date("m", $start_date_unixtimestamp);
        $end_date_unixtimestamp = strtotime($end_date);
        if ($end_date_unixtimestamp === false) {
            return 0;
        }
        $end_date_month = date("m", $end_date_unixtimestamp);
        $calculated_date_unixtimestamp = $start_date_unixtimestamp;
        while ($calculated_date_unixtimestamp < $end_date_unixtimestamp) {
            $counter++;
            $next_calculated_date_unixtimestamp = strtotime($start_date . " +{$counter} months");
            if ($next_calculated_date_unixtimestamp === false) {
                return 0;
            }
            $calculated_date_unixtimestamp = $next_calculated_date_unixtimestamp;
        }
        if (($counter == 1) && ($end_date_month == $start_date_month)) {
            --$counter;
        }
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
        if ($dateTime === false) {
            return $dateString;
        }
        $dateTime->setTimezone((new DateTime())->getTimezone());
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
        $localDateTime->setTimezone((new DateTime())->getTimezone());
        return $localDateTime->format($outputFormat);
    }
}
