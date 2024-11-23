<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 18 Modified in v2.1.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    exit('Invalid Access');
}

function zen_debug_error_handler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }

    static $last_log_suffix;
    if (!isset($last_log_suffix)) {
        $last_log_suffix = '.log';
    }
    $ignore_dups = false;
    if (IS_ADMIN_FLAG === true) {
        $ignore_dups = (defined('REPORT_ALL_ERRORS_ADMIN') && REPORT_ALL_ERRORS_ADMIN == 'IgnoreDups');
    } else {
        $ignore_dups = (defined('REPORT_ALL_ERRORS_STORE') && REPORT_ALL_ERRORS_STORE == 'IgnoreDups');
    }

    if ($ignore_dups && preg_match('#Constant .* already defined#', $errstr)) {
        return true;
    }

    if (($errno == E_NOTICE || $errno == E_USER_NOTICE) && defined('REPORT_ALL_ERRORS_NOTICE_BACKTRACE') && REPORT_ALL_ERRORS_NOTICE_BACKTRACE == 'No') {
        return false;
    }

    switch ($errno) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error_type = 'Notice';
            $this_log_suffix = '.log';
            break;
        case E_DEPRECATED:
            //@TODO: Remove this after we remove the use of the SID constant from html_output etc
            if (str_contains($errstr, 'Constant SID is deprecated')) return true;
        case E_USER_DEPRECATED:
            $error_type = 'Deprecated';
            $this_log_suffix = '-deprecated.log';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error_type = 'Warning';
            $this_log_suffix = '-warning.log';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error_type = 'Fatal error';
            $this_log_suffix = '-error.log';
            break;
        default:
            return false;      //-Unknown error type, let PHP's built-in handler do its thing.
            break;
    }

    if ($last_log_suffix != $this_log_suffix) {
        $GLOBALS['debug_logfile_path'] = str_replace($last_log_suffix, $this_log_suffix, $GLOBALS['debug_logfile_path']);
        @ini_set('error_log', $GLOBALS['debug_logfile_path']);  // the filename to log errors into
        $last_log_suffix = $this_log_suffix;
    }

    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $backtrace = ob_get_contents();
    ob_end_clean();
    // The following line removes the call to this zen_debug_error_handler function (as it's not relevant)
    $backtrace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $backtrace, 1);
    if (!empty($backtrace)) {
        $backtrace = PHP_EOL . rtrim($backtrace);
    }
    $language_info = ', Language id ' . ($_SESSION['languages_id'] ?? 'not set');
    $message = date('[d-M-Y H:i:s e]') . ' Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'not set') . ', IP address: ' . ($_SERVER['REMOTE_ADDR'] ?? 'not set')  . $language_info . $backtrace;

    $message .= PHP_EOL . "--> PHP $error_type: $errstr in $errfile on line $errline.";

    error_log($message . PHP_EOL . PHP_EOL, 3, $GLOBALS['debug_logfile_path']);

    return true;    //-Indicate that we've handled this error-type.
}

function zen_fatal_error_handler()
{
    $last_error = error_get_last();

    if (!empty($last_error) && in_array($last_error['type'], [E_ERROR, E_USER_ERROR, E_PARSE])) {
        $message = date('[d-M-Y H:i:s e]') . ' Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'not set') . ', IP address: ' . ($_SERVER['REMOTE_ADDR'] ?? 'not set') . PHP_EOL;
        $message_type = ($last_error['type'] == E_PARSE) ? 'Parse' : (($last_error['type'] == E_RECOVERABLE_ERROR) ? 'Catchable Fatal' : 'Fatal');
        $message .= "--> PHP $message_type error: {$last_error['message']} in {$last_error['file']} on line {$last_error['line']}.";
        error_log(PHP_EOL . $message . PHP_EOL, 3, $GLOBALS['debug_logfile_path']);
    }
}

/**
 * The path where the debug log file will be located
 * Default value is: DIR_FS_LOGS . '/myDEBUG-yyyymmdd-hhiiss-xxxxx.log' (storefront) or DIR_FS_LOGS . '/myDEBUG-adm-yyyymmdd-hhiiss-xxxxx.log' (admin)
 * ... which puts it in the /logs/ folder:   /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx.log or /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx.log, respectively.
 *     where:
 *      - yyyy .... is the 4-digit year
 *      - mm ...... is the 2-digit month
 *      - dd ..... is the 2-digit day-of-month
 *      - hh ..... is the 2-digit hour
 *      - ii ..... is the 2-digit minute
 *      - ss ..... is the 2-digit second
 *      - xxxxx ... is the time in milliseconds
 *
 *    (or if you don't have a /logs/ folder, it will use the /cache/ folder instead)
 *
 * The error-handler now groups the logs by severity, based on the log-file's suffix, examples given for the storefront, by
 * the zen_debug_error_handler function (above). For the admin, the '-adm' addition described above is included in the files' name(s).
 *
 * PHP notices:      /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx.log
 * PHP deprecations: /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx-deprecated.log
 * PHP warnings:     /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx-warning.log
 * PHP errors:       /logs/myDEBUG-yyyymmdd-hhiiss-xxxxx-error.log
 */
function zen_set_error_logging_filename(): string
{
    /**
     * Used as a global by registered zen_debug_error_handler and zen_fatal_error_handler functions
     */
    global $debug_logfile_path;

    $log_prefix = (IS_ADMIN_FLAG) ? '/myDEBUG-adm-' : '/myDEBUG-';
    $log_date = new DateTime();
    $debug_logfile_path = DIR_FS_LOGS . $log_prefix . $log_date->format('Ymd-His-u') . '.log';

    @ini_set('error_log', $debug_logfile_path);  // the filename to log errors into

    return $debug_logfile_path;
}

function zen_enable_error_logging(array $pages_to_debug = ['*'], $logging_level = E_ALL & ~E_NOTICE): void
{
    global $current_page_base, $debug_logfile_path;

    if (empty($debug_logfile_path)) {
        $debug_logfile_path = zen_set_error_logging_filename();
    }

    if (in_array('*', $pages_to_debug, true) || in_array($current_page_base, $pages_to_debug, true)) {
        @ini_set('log_errors', 1);          // store to file
        @ini_set('display_errors', 0);      // do not output errors to screen/browser/client
        @ini_set('error_log', $debug_logfile_path);  // the filename to log errors into
        @ini_set('error_reporting', $logging_level ); // log only errors according to defined rules
        set_error_handler('zen_debug_error_handler', $logging_level);
        register_shutdown_function('zen_fatal_error_handler');
    }

    if (defined('IS_CLI') && IS_CLI == 'VERBOSE') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
}
