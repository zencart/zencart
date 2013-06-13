<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Jul 1 17:04:00 2012 -0400 Modified in v1.5.1 $
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
  $server_used = CURRENCY_SERVER_PRIMARY;
  zen_set_time_limit(600);
  $currency = $db->Execute("select currencies_id, code, title, decimal_places from " . TABLE_CURRENCIES);
  while (!$currency->EOF) {
    $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
    $rate = $quote_function($currency->fields['code']);

    if (empty($rate) && (zen_not_null(CURRENCY_SERVER_BACKUP))) {
      // failed to get currency quote from primary server - attempting to use backup server instead
      $msg = sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency->fields['title'], $currency->fields['code']);
      if (is_object($messageStack)) {
        $messageStack->add_session($msg, 'warning');
      } elseif ($cli_Output) {
        echo "$msg\n";
      }
      $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
      $rate = $quote_function($currency->fields['code']);
      $server_used = CURRENCY_SERVER_BACKUP;
    }

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
                          set value = '" . $rate . "', last_updated = now()
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
    $currency->MoveNext();
  }
}

function quote_ecb_currency($currencyCode = '', $base = DEFAULT_CURRENCY)
{
  $requested = $currencyCode;
  $url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
  $data = '';
  // check via file() ... may fail if php file Wrapper disabled.
  $XMLContent = @file($url);
  if (! is_object($XMLContent) && function_exists('curl_init')) {
    // check via CURL instead.
    $XMLContent = doCurlCurrencyRequest('POST', $url, $data);
    $XMLContent = explode("\n", $XMLContent);
  }
  $currencyArray = array();
  $rate = 1;
  $line = '';
//  $currencyCode = '';
  $currencyArray['EUR'] = 1;
  foreach ($XMLContent as $line) {
    if (preg_match("/currency='([[:alpha:]]+)'/", $line, $currencyCode)) {
      if (preg_match("/rate='([[:graph:]]+)'/", $line, $rate)) {
        $currencyArray[$currencyCode[1]] = (float)$rate[1];
      }
    }
  }
  if ($requested == $base) {
    $rate = 1;
  } else {
    $rate = (string)((float)$currencyArray[$requested] / $currencyArray[DEFAULT_CURRENCY]);
  }
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
    $data = explode(',', $line);
    $curName = $data[1];
    $curRate = $data[sizeof($data)-1];
    $currencyArray[trim($curName)] = (float)$curRate;
  }
  $rate = (string)($currencyArray[DEFAULT_CURRENCY]/(float)$currencyArray[$requested]);
  return $rate;
}

  function quote_oanda_currency($code, $base = DEFAULT_CURRENCY) {
    $url = 'http://www.oanda.com/convert/fxdaily';
    $data = 'value=1&redirected=1&exch=' . $code .  '&format=CSV&dest=Get+Table&sel_list=' . $base;
    // check via file() ... may fail if php file Wrapper disabled.
    $page = @file($url . '?' . $data);
    if (!is_object($page) && function_exists('curl_init')) {
      // check via cURL instead.  May fail if proxy not set, esp with GoDaddy.
      $page = doCurlCurrencyRequest('POST', $url, $data) ;
      $page = explode("\n", $page);
    }
    if (is_object($page) || $page !='') {
      $match = array();

      preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

      if (sizeof($match) > 0) {
        return $match[3];
      } else {
        return false;
      }
    }
  }

  function quote_xe_currency($to, $from = DEFAULT_CURRENCY) {
    $url = 'http://www.xe.net/ucc/convert.cgi';
    $data = 'Amount=1&From=' . $from . '&To=' . $to;
    // check via file() ... may fail if php file Wrapper disabled.
    $page = @file($url . '?' . $data);
    if (!is_object($page) && function_exists('curl_init')) {
      // check via cURL instead.  May fail if proxy not set, esp with GoDaddy.
      $page = doCurlCurrencyRequest('POST', $url, $data) ;
      $page = explode("\n", $page);
    }
    if (is_object($page) || $page !='') {
      $match = array();

      preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);
      if (sizeof($match) > 0) {
        return $match[1];
      } else {
        return false;
      }
    }
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
