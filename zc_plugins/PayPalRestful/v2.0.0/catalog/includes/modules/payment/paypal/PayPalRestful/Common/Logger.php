<?php
/**
 * Debug logging class for the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.3.0
 */

namespace PayPalRestful\Common;

use PayPalRestful\Common\Helpers;

class Logger
{
    /**
     * Static variables associated with interface logging;
     *
     * @debugLogFile string
     * @debug bool
     */
    protected static $debug = false;
    protected static $debugLogFile;

    // -----
    // Class constructor.
    //
    public function __construct(string $uniqueName = '')
    {
        global $current_page_base;

        // -----
        // Using the same log-file name for each page-load.
        // If it's already set, simply return.
        //
        if (isset(self::$debugLogFile)) {
            return;
        }
        
        if (!empty($current_page_base) && \str_contains($current_page_base, 'webhook')) {
            $logfile_suffix = 'webhook-' . $uniqueName;
            $logfile_suffix = trim($logfile_suffix, '-');
        } elseif (IS_ADMIN_FLAG === false) {
            $logfile_suffix = 'c-' . ($_SESSION['customer_id'] ?? 'na') . '-' . Helpers::getCustomerNameSuffix();
        } else {
            $logfile_suffix = 'adm-a' . $_SESSION['admin_id'];
            global $order;
            if (isset($order)) {
                $logfile_suffix .= '-o' . $order->info['order_id'];
            }
        }
        self::$debugLogFile = DIR_FS_LOGS . '/paypalr-' . $logfile_suffix . '-' . date('Ymd') . '.log';
    }

    public function enableDebug()
    {
        self::$debug = true;
    }
    public function disableDebug()
    {
        self::$debug = false;
    }

    // -----
    // Format pretty-printed JSON for the debug-log, removing any HTTP Header
    // information (present in the CURL options) and/or the actual access-token as well
    // as obfuscating any credit-card information in the data supplied.
    //
    // Also remove unneeded return values that will just 'clutter up' the logged information,
    // unless requested to keep them.
    //
    public static function logJSON($data, bool $keep_links = false, bool $use_var_export = false): string
    {
        if (is_array($data)) {
            unset(
                $data[CURLOPT_HTTPHEADER],
                $data['access_token'],
                $data['scope'],
                $data['app_id'],
                $data['nonce']
            );
            if (isset($data['payment_source']['card']['number'])) {
                $data['payment_source']['card']['number'] = substr($data['payment_source']['card']['number'], -4);
            }
            if (isset($data['payment_source']['card']['security_code'])) {
                $data['payment_source']['card']['security_code'] = str_repeat('*', strlen($data['payment_source']['card']['security_code']));
            }
            if ($keep_links === false) {
                unset(
                    $data['links'],
                    $data['purchase_units'][0]['payments']['authorizations']['links'],
                    $data['purchase_units'][0]['payments']['captures']['links'],
                    $data['purchase_units'][0]['payments']['refunds']['links']
                );
            }
            foreach (['authorizations', 'captures', 'refunds'] as $next_payment_type) {
                if (!isset($data['purchase_units'][0]['payments'][$next_payment_type])) {
                    continue;
                }
                for ($i = 0, $n = count($data['purchase_units'][0]['payments'][$next_payment_type]); $i < $n; $i++) {
                    unset($data['purchase_units'][0]['payments'][$next_payment_type][$i]['links']);
                }
            }
        }
        return ($use_var_export === true) ? var_export($data, true) : json_encode($data, JSON_PRETTY_PRINT);
    }

    public function write(string $message, bool $include_timestamp = false, string $include_separator = '')
    {
        global $current_page_base;

        if (self::$debug === true) {
            $timestamp = ($include_timestamp === false) ? '' : ("\n" . date('Y-m-d H:i:s: ') . "($current_page_base) ");
            $separator = '';
            $separator_before = '';
            $separator_after = '';
            if ($include_separator !== '') {
                $separator = "************************************************";
                if ($include_separator === 'before') {
                    $separator_before = (strpos($message, "\n") === 0) ? "\n$separator" : "\n$separator\n";
                } else {
                    $separator_after = (substr($message, -1) === "\n") ? "$separator\n" : "\n$separator\n";
                }
            }
            error_log($separator_before . $timestamp . $message . $separator_after, 3, self::$debugLogFile);
        }
    }
}
