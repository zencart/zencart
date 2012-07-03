<?php
/*
 * @package utilities
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: curltester.php 18695 2011-05-04 05:24:19Z drbyte $
 *
 * This utility is simply intended to test whether the host server has the ability to use CURL to connect to external servers in order to send communications, such as for PayPal transactions
 */
  error_reporting(E_ALL);

  $defaultURL = "http://www.zen-cart.com/testcurl.php";
  $useSSL = (isset($_GET['ssl']) && (strtolower($_GET['ssl']) == 'yes' || $_GET['ssl'] == 1)) ? true : false;
  if ($useSSL) $defaultURL = "https://www.zen-cart.com/testcurl.php";
  $url = $defaultURL;
  $proxy = (isset($_GET['proxy'])) ? true : false;
  $proxyAddress = (isset($_GET['proxyaddress'])) ? $_GET['proxyaddress'] : '';

  $testFirstData = ((isset($_GET['firstdata']) && (strtolower($_GET['firstdata']) == 'yes' || $_GET['firstdata'] == 1)) || (isset($_GET['linkpoint']) && (strtolower($_GET['linkpoint']) == 'yes' || $_GET['linkpoint'] == 1))) ? true : false;
  if ($testFirstData) $url = "https://secure.linkpt.net:1129/LSGSXML";

  $testAuthnet = (isset($_GET['authnet']) && (strtolower($_GET['authnet']) == 'yes' || $_GET['authnet'] == 1)) ? true : false;
  if ($testAuthnet) $url = "https://secure.authorize.net/gateway/transact.dll";

  $testPayPal = (isset($_GET['paypal']) && (strtolower($_GET['paypal']) == 'yes' || $_GET['paypal'] == 1)) ? true : false;
  if ($testPayPal) $url = "https://api-3t.paypal.com/nvp";

  $_POST = array();
  if (isset($GLOBALS)) unset($GLOBALS);
  if (isset($_GET)) unset($_GET);
  if (isset($_REQUEST)) unset($_REQUEST);
  $data = "field1=This is a test&statuskey=ready";

  // Send CURL communication
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_VERBOSE, 0);
  if ($data != '') {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  }
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 25);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); /* compatibility for SSL communications on some Windows servers (IIS 5.0+) */
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt ($ch, CURLOPT_SSLVERSION, 3);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart(tm) - CURL TEST');

/**
  if ($proxy && $proxyAddress != '') {
    curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    @curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    curl_setopt ($ch, CURLOPT_PROXY, $proxyAddress);
  }
*/
  $result = curl_exec($ch);
  $errtext = curl_error($ch);
  $errnum = curl_errno($ch);
  $commInfo = @curl_getinfo($ch);
  curl_close ($ch);

// enclose URL in quotes so it doesn't get converted to a clickable link if posted on the forum
  if (isset($commInfo['url'])) $commInfo['url'] = '"' . $commInfo['url'] . '"';

// Handle results
  echo ($errnum != 0 ? '<br />' . $errnum . ' ' . $errtext . '<br />' : '');
  if ($url == $defaultURL) {
    echo $result;
  } else {
    if ($commInfo['http_code'] == 200) echo 'COMMUNICATIONS TEST OKAY.<br />You may see error information below, but that information simply confirms that the server actually responded, which means communications is open.';
  }
  echo '<pre>' . print_r($commInfo, true) . '</pre><br /><br />';
  if ($url != $defaultURL) echo $result . '<br>EOF';

