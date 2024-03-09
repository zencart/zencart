<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 29 Modified in v2.0.0-rc1 $
 */
class zcDate extends base
{
    protected
        $useIntlDate = false,
        $useStrftime = false,
        $locale,                //- Only used when $this->useIntlDate is true
        $strftime2date,         //- Only used when $this->useStrftime is false
        $strftime2intl,         //- Only used when $this->useStrftime is false
        $debug = false,
        $dateObject;

    // -----
    // Initial construction; initializes the conversion arrays and determines which PHP
    // base function will be used by the output method.
    //
    // The $zen_date_debug is a "soft" configuration setting that can be forced (defaults to false)
    // via the site's /includes/extra_datafiles/site_specific_overrides.php
    //
    public function __construct()
    {
        global $zen_date_debug;

        if (isset($zen_date_debug) && $zen_date_debug === true) {
            $this->debug = true;
        }

        if (version_compare(phpversion(), '8.1', '<')) {
            $this->useStrftime = true;
        } else {
            if (function_exists('datefmt_create')) {
                $this->useIntlDate = true;
            }
            $this->initializeConversionArrays();
        }
        $this->debug('zcDate construction: ' . PHP_EOL . var_export($this, true));
    }

    // -----
    // Initializes the class-based arrays that define the format conversions
    // from their strftime format (the input requirement) and the formats used
    // by either the 'date' function or the IntlDateFormatter class.
    //
    // Each array's keys start out as the strftime format and a key's value is the converted format.
    // These arrays are then converted into a 'from' and a 'to' array that's used by the
    // method convertFormat's processing (essentially a str_replace on the submitted format string).
    //
    protected function initializeConversionArrays()
    {
        $strftime2date = [
            '%a' => 'D',
            '%A' => 'l',
            '%b' => 'M',
            '%B' => 'F',
            '%d' => 'd',
            '%H' => 'H',
            '%m' => 'm',
            '%M' => 'i',
            '%S' => 's',
            '%T' => 'H:i:s',
            '%x' => defined('DATE_FORMAT') ? DATE_FORMAT : 'm/d/Y',
            '%X' => 'H:i:s',
            '%y' => 'y',
            '%Y' => 'Y',
            '%z' => 'ZZZZ',
            '%Z' => 'ZZZZ',
        ];
        $this->strftime2date = [
            'from' => array_keys($strftime2date),
            'to' => array_values($strftime2date)
        ];

        if ($this->useIntlDate === true) {
            // -----
            // First, save the current locale; it's set by the main language file's (presumed) call to the
            // setlocale function.
            //
            $this->locale = setlocale(LC_TIME, 0);

            // -----
            // Using the current locale, retrieve the locale-specific 'short' date and time
            // formats.
            //
            $format = new IntlDateFormatter(
                $this->locale,
                IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE
            );
            $date_short = $format->getPattern();

            $format = new IntlDateFormatter(
                $this->locale,
                IntlDateFormatter::NONE,
                IntlDateFormatter::SHORT
            );
            $time_short = $format->getPattern();

            $strftime2intl = [
                '%a' => 'E',
                '%A' => 'EEEE',
                '%b' => 'MMM',
                '%B' => 'MMMM',
                '%d' => 'dd',
                '%H' => 'HH',
                '%m' => 'MM',
                '%M' => 'mm',
                '%S' => 'ss',
                '%T' => 'HH:mm:ss',
                '%x' => $date_short,
                '%X' => $time_short,
                '%y' => 'yy',
                '%Y' => 'y',
                '%z' => 'ZZZZ',
                '%Z' => 'ZZZZ',
            ];
            $this->strftime2intl = [
                'from' => array_keys($strftime2intl),
                'to' => array_values($strftime2intl)
            ];
        }
    }

    // -----
    // A couple of public functions to control whether or not the class' debug
    // processing is to be enabled or disabled.
    //
    public function enableDebug()
    {
        $this->debug = true;
        $this->debug('Debug enabled: ' . PHP_EOL . var_export($this, true));
    }
    public function disableDebug()
    {
        $this->debug = false;
    }

    /**
     * @param string $format  output method should start with a strftime-format string
     * @param int    $timestamp
     * @param string|null $calendar_locale Optional calendar-related locale. eg: 'ja_JP@calendar=japanese'
     *
     * @return false|string
     */
    public function output(string $format, int $timestamp = 0, ?string $calendar_locale = null)
    {
        if ($timestamp === 0) {
            $timestamp = time();
        }

        // -----
        // If the to-be-used function is strftime, format the requested string.
        //
        if ($this->useStrftime === true) {
            $converted_format = $format;
            $output = strftime($format, $timestamp);
        // -----
        // Otherwise, if there's no international date support, format the requested string using date.
        //
        } elseif ($this->useIntlDate === false) {
            $converted_format = $this->convertFormat($format, $this->strftime2date);
            $output = date($converted_format, $timestamp);
        // -----
        // Otherwise, the string is to be formatted using the IntlDateFormatter ...
        //
        } else {
            // -----
            // If the locale has changes (as it might between the class construction and
            // this method, re-initialize the conversion arrays for the current locale.
            //
            if ($this->locale !== setlocale(LC_TIME, 0)) {
                $this->initializeConversionArrays();
            }

            $calendar = IntlDateFormatter::GREGORIAN;
            if (!empty($calendar_locale)) {
                $calendar = IntlCalendar::createInstance(null, $calendar_locale);
            }

            $converted_format = $this->convertFormat($format, $this->strftime2intl);
            $this->dateObject = datefmt_create(
                $this->locale,
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                date_default_timezone_get(),
                $calendar,
                $converted_format
            );
            $output = $this->dateObject->format($timestamp);
            if ($output === false) {
                trigger_error(sprintf("Formatting error using '%s': %s (%d)", $converted_format, $this->dateObject->getErrorMessage(), $this->dateObject->getErrorCode()), E_USER_WARNING);
            }
        }

        $additional_message = ($format === $converted_format) ? '' : ", with format converted to '$converted_format'";
        $this->debug("zcDate output for '$format' with timestamp ($timestamp)" . $additional_message . ": '" . json_encode($output) . "'");

        return $output;
    }

    protected function convertFormat(string $format, array $replacements)
    {
        return str_replace($replacements['from'], $replacements['to'], $format);
    }

    /**
     * @param string $date  The date to be validated, according to the same rules as strtotime.
     *
     * @return bool  Indicates whether/not the supplied date is valid
     */
    public static function validateDate(string $date): bool
    {
        ['year' => $year, 'month' => $month, 'day' => $day, 'warning_count' => $warning_count, 'error_count' => $error_count] = date_parse($date);

        return ($year !== false && $month !== false && $day !== false && (($warning_count + $error_count) === 0));
    }

    protected function debug(string $message)
    {
        if ($this->debug === true) {
            error_log($message . PHP_EOL);
        }
    }
}
