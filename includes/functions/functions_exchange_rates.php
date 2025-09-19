<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2023 Jul 02 Modified in v2.0.0-alpha1 $
 */

/**
 * Dependencies:
 * - DB class must be instantiated as $db (done normally by application_top)
 * - if any additional currency-update plugins are installed, those plugins' functions must be loaded (done normally by admin application_top)
 * NOTE: admin application_top cannot be loaded successfully without an admin login ID.
 * @since ZC v1.5.5
 */
function zen_update_currencies(bool $outputMessagesToCommandLine = false): void
{
    global $db, $messageStack, $zco_notifier;
    @set_time_limit(600);

    $results = $db->Execute("SELECT currencies_id, code, title, decimal_places FROM " . TABLE_CURRENCIES);

    foreach ($results as $result) {
        $server_used = CURRENCY_SERVER_PRIMARY;
        $rate = '';
        $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
        if (function_exists($quote_function)) {
            $rate = $quote_function($result['code']);
        }

        if (empty($rate) && !empty(CURRENCY_SERVER_BACKUP)) {
            // failed to get currency quote from primary server - attempting to use backup server instead
            $msg = sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $result['title'], $result['code']);
            if (is_object($messageStack)) {
                $messageStack->add_session($msg, 'warning');
            } elseif ($outputMessagesToCommandLine) {
                echo "$msg\n";
            }
            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
            if (function_exists($quote_function)) {
                $rate = $quote_function($result['code']);
            }
            $server_used = CURRENCY_SERVER_BACKUP;
        }
        if (!empty($rate)) {
            /* Add currency uplift, because exchange rates quoted aren't always the same as what your own bank gives you */
            $multiplier = (defined('CURRENCY_UPLIFT_RATIO') && (int)CURRENCY_UPLIFT_RATIO != 0) ? CURRENCY_UPLIFT_RATIO : 0;
            $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATE_MULTIPLIER', $result['code'], $multiplier, $rate);

            if ($rate != 1 && $multiplier > 0) {
                $rate = (string)((float)$rate * (float)$multiplier);
            }

            // special handling for currencies which don't support decimal places; intentionally loosely typed
            if ($result['decimal_places'] == '0') {
                $rate = (int)$rate;
            }

            if (!empty($rate)) {
                $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATE_SINGLE', $result['code'], $rate);
                $db->Execute(
                    "UPDATE " . TABLE_CURRENCIES . "
                      SET value = '" . round((float)$rate, 8) . "', last_updated = now()
                      WHERE currencies_id = '" . (int)$result['currencies_id'] . "'"
                );

                $msg = sprintf(TEXT_INFO_CURRENCY_UPDATED, $result['title'], $result['code'], round((float)$rate, 8), $server_used);
                if (is_object($messageStack)) {
                    $messageStack->add_session($msg, 'success');
                } elseif ($outputMessagesToCommandLine) {
                    echo "$msg\n";
                }

            } else {
                $msg = sprintf(ERROR_CURRENCY_INVALID, $result['title'], $result['code'], $server_used);
                if (is_object($messageStack)) {
                    $messageStack->add_session($msg, 'error');
                } elseif ($outputMessagesToCommandLine) {
                    echo "$msg\n";
                }
            }
        }
    }
    if (function_exists('zen_record_admin_activity')) {
        zen_record_admin_activity('Currency exchange rates updated: ' . $msg, 'info');
    }
    $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATES_UPDATED', $msg);
}

/**
 * ECB Rates - based on data format in July 2017
 *
 * @param string $currencyCode requested
 * @param string $base currency code
 * @return int|float
 * @since ZC v1.5.0
 */
function quote_ecb_currency(string $currencyCode = '', string $base = DEFAULT_CURRENCY): float|int|string
{
    if ($currencyCode === $base) {
        return 1;
    }
    static $XMLContent = [];

    $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    if (empty($XMLContent)) {
        $XMLContent = @file($url);
        if (empty($XMLContent)) {
            $XMLContent = zenDoCurlRequest($url, 'GET');
            $XMLContent = explode("\n", $XMLContent);
        }
    }
    $currencyArray = [];
    $currencyArray['EUR'] = 1; // quoting ECB bank, so EUR is always = 1
    $rate = 1;
    $line = '';
    foreach ($XMLContent as $line) {
        if (preg_match("/currency='([[:alpha:]]+)'/", $line, $reg)) {
            if (preg_match("/rate='([[:graph:]]+)'/", $line, $rateVal)) {
                $currencyArray[$reg[1]] = (float)$rateVal[1];
            }
        }
    }
    // Check for valid data
    if (empty($currencyArray[$base]) || !isset($currencyArray[$currencyCode])) {
        return ''; // no valid value, so abort, else risk divide-by-zero
    }
    $rate = (string)((float)$currencyArray[$currencyCode] / $currencyArray[$base]);
    return $rate;
}

/**
 * BOC Rates - based on data format in July 2017
 *
 * @param string $currencyCode requested
 * @param string $base currency code
 * @return bool|float
 * @since ZC v1.5.0
 */
function quote_boc_currency(string $currencyCode = '', string $base = DEFAULT_CURRENCY): float|bool|int|string
{
    if ($currencyCode === $base) {
        return 1;
    }
    $requested = $currencyCode;
    $url = 'https://www.bankofcanada.ca/valet/observations/group/FX_RATES_DAILY/json';
    static $BOCdata = [];

    if (empty($BOCdata)) {
        $result = zenDoCurlRequest($url, 'GET');
        if (empty($result)) {
            return false;
        }
        $BOCdata = json_decode($result, true);

        // no data means unable to continue with updates
        if (empty($BOCdata) || empty($BOCdata['observations'])) {
            return false;
        }
    }

    // grab the last date data reported
    $values = array_pop($BOCdata['observations']);
    // if nothing found, attempt to get the next-last item
    if (empty($values)) {
        $values = array_pop($BOCdata['observations']);
    }
    if (empty($values) || !is_array($values)) {
        return false;
    }

    $lookup = 'FX' . strtoupper($requested) . 'CAD';
    $default = 'FX' . strtoupper($base) . 'CAD';

    $values['FXCADCAD']['v'] = 1; // quoting BOC, where CAD is always = 1

    if (!empty($values[$default]['v']) && !empty($values[$lookup]['v'])) {
        return (string)($values[$default]['v'] / $values[$lookup]['v']);
    }
    return false;
}


/**
 * @since ZC v1.3.5
 */
function doCurlCurrencyRequest($method, $url, $vars = ''): string
{
    return zenDoCurlRequest($url, $method, $vars);
}
