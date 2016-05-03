<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  May 2 2016 Modified in v1.5.5a $
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
  global $db, $messageStack;
  zen_set_time_limit(600);
  $currency = $db->Execute("select currencies_id, code, title, decimal_places from " . TABLE_CURRENCIES);
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
      if ($rate != 1 && defined('CURRENCY_UPLIFT_RATIO') && (int)CURRENCY_UPLIFT_RATIO != 0) {
        $rate = (string)((float)$rate * (float)CURRENCY_UPLIFT_RATIO);
      }

      // special handling for currencies which don't support decimal places
      if ($currency->fields['decimal_places'] == '0') {
        $rate = (int)$rate;
      }

      if (zen_not_null($rate) && $rate > 0) {
        $db->Execute("update " . TABLE_CURRENCIES . "
                            set value = '" . (float)$rate . "', last_updated = now()
                            where currencies_id = '" . (int)$currency->fields['currencies_id'] . "'");
        $msg = sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $rate, $server_used);
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
  zen_record_admin_activity('Currency exchange rates updated.', 'info');
}

function quote_ecb_currency($currencyCode = '', $base = DEFAULT_CURRENCY)
{
  // NOTE: checks via file() ... may fail if php file Wrapper disabled.
  if ($currencyCode == $base) return 1;
  static $XMLContent;
  $url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
  $data = '';
  if (!isset($XMLContent) || !is_array($XMLContent) || sizeof($XMLContent) < 1) {
    $XMLContent = file($url);
    if (! is_object($XMLContent) && function_exists('curl_init')) {
      // check via CURL instead.
      $XMLContent = doCurlCurrencyRequest('POST', $url, $data);
      $XMLContent = explode("\n", $XMLContent);
    }
  }
  $currencyArray = array();
  $currencyArray['EUR'] = 1; // this is the ECB bank, so EUR always = 1
  $rate = 1;
  $line = '';
  foreach ($XMLContent as $line) {
    if (preg_match("/currency='([[:alpha:]]+)'/", $line, $reg)) {
      if (preg_match("/rate='([[:graph:]]+)'/", $line, $rate)) {
        $currencyArray[$reg[1]] = (float)$rate[1];
      }
    }
  }
  if (!isset($currencyArray[DEFAULT_CURRENCY]) || 0 == $currencyArray[DEFAULT_CURRENCY]) return ''; // no valid value, so abort
  $rate = (string)((float)$currencyArray[$currencyCode] / $currencyArray[DEFAULT_CURRENCY]);
  return $rate;
}

function quote_boc_currency($currencyCode = '', $base = DEFAULT_CURRENCY)
{
  if ($currencyCode == $base) return 1;
  static $CSVContent;
  $requested = $currencyCode;
  $url = 'http://www.bankofcanada.ca/stats/assets/csv/fx-seven-day.csv';
  $currencyArray = array();
  $currencyArray['CAD'] = 1;
  if (!isset($CSVContent) || $CSVContent == '') {
    $CSVContent = file($url);
    if (! is_object($CSVContent) && function_exists('curl_init')) {
      $CSVContent = doCurlCurrencyRequest('GET', $url);
      $CSVContent = explode("\n", $CSVContent);
    }
  }
  foreach ($CSVContent as $line) {
    if (substr($line, 0, 1) == '#' || substr($line, 0, 4) == 'Date' || trim($line) == '') continue;
    $data = explode(',', $line); // make an array, where each value is a separate column from the CSV
    $curName = substr(trim($data[1]), 0, 3); // take only first 3 chars of currency code (ie: removes "_NOON" suffix, or whatever future suffix BOC adds)
    $curRate = trim($data[sizeof($data)-1]);  // grab the value from the last column
    // if the value isn't already set and isn't (basically) zero, update it in the array
    if (!isset($currencyArray[trim($curName)]) || $currencyArray[trim($curName)] < 0.00001) $currencyArray[trim($curName)] = (float)$curRate;
  }
  // sanity checks
  if (!isset($currencyArray[$requested])) return false; // $requested not found
  if ($currencyArray[$requested] == 0) return false; // can't divide by zero

  $rate = (string)($currencyArray[DEFAULT_CURRENCY]/(float)$currencyArray[$requested]);
  return $rate;
}


  function doCurlCurrencyRequest($method, $url, $vars = '') {
    //echo '-----------------<br />';
    //echo 'URL: ' . $url . ' VARS: ' . $vars . '<br />';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//  curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
//  curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
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
    } else {
      return $error;
    }
  }
