<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: localization.php 18695 2011-05-04 05:24:19Z drbyte $
 */

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
      $messageStack->add_session('cURL communication ERROR: ' . $error, 'error');
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
