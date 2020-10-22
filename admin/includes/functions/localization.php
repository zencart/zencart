<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */
/**
 * Dependencies:
 * - DB class must be instantiated as $db (done normally by application_top)
 * - functions_general.php or general.php must be loaded (for zen_set_time_limit() and zen_not_null() to be available) (done normally by application_top)
 * - if any additional currency-update plugins are installed, those plugins' functions must be loaded (done normally by admin application_top)
 * NOTE: admin application_top cannot be loaded successfully without an admin login ID.
 */

function zen_update_currencies($cli_Output = FALSE)
{
  global $db, $messageStack, $zco_notifier;
  zen_set_time_limit(600);
  $currency = $db->Execute("SELECT currencies_id, code, title, decimal_places FROM " . TABLE_CURRENCIES);
  while (!$currency->EOF) {
    $server_used = CURRENCY_SERVER_PRIMARY;
    $rate = '';
    $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
    if (function_exists($quote_function)) $rate = $quote_function($currency->fields['code']);

    if (empty($rate) && (zen_not_null(CURRENCY_SERVER_BACKUP))) {
      // failed to get currency quote from primary server - attempting to use backup server instead
      $msg = sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency->fields['title'], $currency->fields['code']);
      if (is_object($messageStack)) {
        $messageStack->add_session($msg, 'warning');
      } elseif ($cli_Output) {
        echo "$msg\n";
      }
      $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
      if (function_exists($quote_function)) $rate = $quote_function($currency->fields['code']);
      $server_used = CURRENCY_SERVER_BACKUP;
    }
    if (zen_not_null($rate) && $rate > 0) {
      /* Add currency uplift */
      $multiplier = (defined('CURRENCY_UPLIFT_RATIO') && (int)CURRENCY_UPLIFT_RATIO != 0) ? CURRENCY_UPLIFT_RATIO : 0;
      $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATE_MULTIPLIER', $currency->fields['code'], $multiplier, $rate);
      if ($rate != 1 && $multiplier > 0) {
        $rate = (string)((float)$rate * (float)$multiplier);
      }

      // special handling for currencies which don't support decimal places
      if ($currency->fields['decimal_places'] == '0') {
        $rate = (int)$rate;
      }

      if (zen_not_null($rate) && $rate > 0) {
        $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATE_SINGLE', $currency->fields['code'], $rate);
        $db->Execute("UPDATE " . TABLE_CURRENCIES . "
                      SET value = '" . round((float)$rate, 8) . "', last_updated = now()
                      WHERE currencies_id = '" . (int)$currency->fields['currencies_id'] . "'");
        $msg = sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], round((float)$rate, 8), $server_used);
        if (is_object($messageStack)) {
          $messageStack->add_session($msg, 'success');
        } elseif ($cli_Output) {
          echo "$msg\n";
        }
      } else {
        $msg = sprintf(ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used);
        if (is_object($messageStack)) {
          $messageStack->add_session($msg, 'error');
        } elseif ($cli_Output) {
          echo "$msg\n";
        }
      }
    }
    $currency->MoveNext();
  }
  if (function_exists('zen_record_admin_activity')) zen_record_admin_activity('Currency exchange rates updated: ' . $msg, 'info');
  $zco_notifier->notify('ADMIN_CURRENCY_EXCHANGE_RATES_UPDATED', $msg);
}

/**
 * ECB Rates - based on data format in July 2017
 *
 * @param string $currencyCode requested
 * @param string $base currency code
 * @return int|float
 */
function quote_ecb_currency($currencyCode = '', $base = DEFAULT_CURRENCY)
{
  if ($currencyCode == $base) return 1;
  static $XMLContent = array();
  $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
  if (empty($XMLContent)) {
    $XMLContent = @file($url);
    if (empty($XMLContent) && function_exists('curl_init')) {
      $XMLContent = doCurlCurrencyRequest('GET', $url);
      $XMLContent = explode("\n", $XMLContent);
    }
  }
  $currencyArray = array();
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
  if (!isset($currencyArray[$base]) || 0 == $currencyArray[$base]) return ''; // no valid value, so abort
  $rate = (string)((float)$currencyArray[$currencyCode] / $currencyArray[$base]);
  return $rate;
}

/**
 * BOC Rates - based on data format in July 2017
 *
 * @param string $currencyCode requested
 * @param string $base currency code
 * @return bool|float
 */
function quote_boc_currency($currencyCode = '', $base = DEFAULT_CURRENCY)
{
  if ($currencyCode == $base) return 1;
  $requested = $currencyCode;
  $url = 'https://www.bankofcanada.ca/valet/observations/group/FX_RATES_DAILY/json';
  static $BOCdata = array();
  if (empty($BOCdata)) {
    $result = doCurlCurrencyRequest('GET', $url);
    // if still empty, abort
    if (empty($result)) return false;
    $BOCdata = json_decode($result, true);
    if (empty($BOCdata) || empty($BOCdata['observations'])) return false; // no data means unable to continue with updates
  }

  // grab the last date data reported
  $values = array_pop($BOCdata['observations']);
  // if nothing found, attempt to get the next-last item
  if (empty($values)) {
      $values = array_pop($BOCdata['observations']);
  }
  if (empty($values) || !is_array($values)) return false;

  $lookup = 'FX' . strtoupper($requested) . 'CAD';
  $default = 'FX' . strtoupper($base) . 'CAD';

  $values['FXCADCAD']['v'] = 1; // quoting BOC so CAD is always = 1

  if (!empty($values[$default]['v']) && !empty($values[$lookup]['v'])) {
      return (string)($values[$default]['v'] / $values[$lookup]['v']);
  }
  return false;
}


  function doCurlCurrencyRequest($method, $url, $vars = '') {
    //echo '-----------------<br />';
    //echo 'URL: ' . $url . ' VARS: ' . $vars . '<br />';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, empty($_SERVER['HTTP_USER_AGENT']) ? HTTP_CATALOG_SERVER . DIR_WS_CATALOG : $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_REFERER, HTTP_CATALOG_SERVER . DIR_WS_CATALOG);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (strtoupper($method) == 'POST' && $vars != '') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    }
    if (CURL_PROXY_REQUIRED == 'True') {
      $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
      curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
      curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
    }
    $data = curl_exec($ch);
    $error = curl_error($ch);
    //$info=curl_getinfo($ch);
    curl_close($ch);

    if ($error != '') {
      global $messageStack;
      if (is_object($messageStack)) $messageStack->add_session('cURL communication ERROR: ' . $error, 'error');
    }
    //echo 'INFO: <pre>'; print_r($info); echo '</pre><br />';
    //echo 'ERROR: ' . $error . '<br />';
    //print_r($data) ;

    if ($data != '') {
      return $data;
    }
    return $error;
  }

  function zen_datepicker_format_full() {
    // converts mm-dd-yy to MM-DD-YYYY
    return str_replace("YY","YYYY", strtoupper(DATE_FORMAT_DATE_PICKER));
  }

  function zen_datepicker_format_fordate() {
    // converts mm-dd-yy to m-d-Y
    $date = DATE_FORMAT_DATE_PICKER;
    $date = str_replace('mm','m', $date);
    $date = str_replace('dd','d', $date);
    $date = str_replace('yy','Y', $date);
    return $date;
  }

  function zen_datepicker_format_forsql() {
    // converts mm-dd-yy to %m-%d-%Y
    $date = DATE_FORMAT_DATE_PICKER;
    $date = str_replace('mm','%m', $date);
    $date = str_replace('dd','%d', $date);
    $date = str_replace('yy','%Y', $date);
    return $date;
  }
