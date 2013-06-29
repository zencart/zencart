<?php
/**
 * Standalone Diagnostics/Debug tool for testing CURL communications to common 3rd party services such as USPS and PayPal and Authorize.net and more.
 * Accepted parameters:
 *   d=1 or details=1 -- show CURL connection details -- useful for determining cause of communications problems
 *   r=1 -- show Response obtained from destination server -- this may contain an error message, but usually means communication was okay
 *
 * @package utilities
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: curltester.php 18695 2011-05-04 05:24:19Z drbyte $
 */
// no caching
header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<html><head><meta name="robots" content="noindex, nofollow" /><title>Communications Test</title></head>
<body>
<p>Testing communications to various destinations. This is a simple diagnostic to determine whether your server can connect to common destinations.<br>
<em>For advanced "details" mode, add </em><strong>?details=on</strong><em> to the URL.</em></p>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$showDetails = (isset($_GET['d']) && $_GET['d'] != '0') || (isset($_GET['details']) && $_GET['details'] != '0'); // supports ?d= or ?details= and any value other than '0' turns it on.
set_time_limit(400);
$errorMessage = '<span style="color:red;font-weight:bold">Error </span>';
$goodMessage = '<span style="color:green;font-weight:bold">GOOD: </span>';

echo 'Connecting to Zen Cart Support Server (http) ...<br>';
doCurlTest('http://www.zen-cart.com/testcurl.php');

echo 'Connecting to Zen Cart Support Server (https) ...<br>';
doCurlTest('https://www.zen-cart.com/testcurl.php');

echo 'Connecting to USPS (port 80)...<br>';
dofsockTest('production.shippingapis.com', 80);

echo 'Connecting to USPS Test/Staging Server (port 80)...<br>';
dofsockTest('stg-production.shippingapis.com', 80);

echo 'Connecting to UPS (port 80)...<br>';
dofsockTest('www.ups.com', 80);

echo 'Connecting to FedEx (port 80)...<br>';
dofsockTest('fedex.com', 80);

echo 'Connecting to PayPal IPN (port 443)...<br>';
dofsockTest('www.paypal.com', 443);

echo 'Connecting to PayPal Express/Pro Server ...<br>';
doCurlTest('https://api-3t.paypal.com/nvp');

echo 'Connecting to PayPal Payflowpro Server ...<br>';
doCurlTest('https://payflowpro.paypal.com/transaction');

echo 'Connecting to AuthorizeNet Production Server ...<br>';
doCurlTest('https://secure.authorize.net/gateway/transact.dll');

echo 'Connecting to AuthorizeNet Developer Server ...<br>';
doCurlTest('https://test.authorize.net/gateway/transact.dll');

echo 'Connecting to LinkPoint (port 1129)...<br>';
doCurlTest('https://secure.linkpt.net/LSGSXML:1129');

?>

<em>Testing completed. See results above.</em>
</body>
</html>

<?php
die();
//////// Processing logic ///////

function doCurlTest($url = 'http://www.zen-cart.com/testcurl.php', $postdata = "field1=This is a test&statuskey=ready") {
  global $goodMessage, $errorMessage, $showDetails;
  $extraMessage = '';
  $showResult = FALSE;
  if (strpos($url, 'zen-cart.com') && isset($_GET['z']) && $_GET['z'] != '0') $showResult = TRUE;
  if (!strpos($url, 'zen-cart.com') && isset($_GET['r']) && $_GET['r'] != '0') $showResult = TRUE;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  if ($postdata != '') {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
  }
  $val = preg_match('/(.*):([0-9]*)$/', $url, $regs);
  if ($val) {
    curl_setopt($ch, CURLOPT_PORT, $regs[2]);
    curl_setopt($ch, CURLOPT_URL, $regs[1]);
  }
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
  curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart(tm) - CURL TEST');

//  curl_setopt($ch, CURLOPT_CAINFO, '/local/path/to/cacert.pem'); // for offline testing, this file can be obtained from http://curl.haxx.se/docs/caextract.html ... should never be used in production!


  $result = curl_exec($ch);
  $errtext = curl_error($ch);
  $errnum = curl_errno($ch);
  // check for common certificate errors, and resubmit
  if (in_array($errnum, array(60,61))) {
    echo $errorMessage . $errnum . ': ' . $errtext;
    echo '<br><p style="color:red;"><strong>IMPORTANT NOTE: Error 60 or 61 means that this server has an SSL certificate configuration problem. YOU NEED TO ASK YOUR HOSTING COMPANY SERVER ADMIN FOR ASSISTANCE with fixing the server\'s OpenSSL certificate chain. <br>This error has nothing to do with Zen Cart. It is a server configuration issue.</strong><br><br>(If you are running this test on a localhost/PC/dev/standlone server then you can either ignore this until you put the site on a live production server, or temporarily override things by manually configuring the CURLOPT_CAINFO value with a legitimate CA bundle. If you don\'t know what that means, just defer your CURL testing until you are on a live production webserver!)</p>';
    echo 'Testing again with less security...<br>';
    $extraMessage = ' (but without being able to verify certificate chain. Again: this is a <u>server</u> issue, not a Zen Cart issue.)';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $result = curl_exec($ch);
    $errtext = curl_error($ch);
    $errnum = curl_errno($ch);
  }
  $commInfo = @curl_getinfo($ch);
  curl_close ($ch);

  // enclose URL in quotes so it doesn't get converted to a clickable link if posted on the forum
  if (isset($commInfo['url'])) $commInfo['url'] = '"' . $commInfo['url'] . '"';

  // Handle results
  if ($errnum != 0) {
    echo $errorMessage . $errnum . ': ' . $errtext . '<br><br>';
  } else {
    echo $goodMessage . 'CURL Connection successful.' . $extraMessage . '<br><br>';
    if ($showResult && $commInfo['http_code'] == 200) echo '<strong>COMMUNICATIONS TEST OKAY.</strong><br>You may see error information below, but that information simply confirms that the server actually responded, which means communications is open.<br>';
    if ($showResult) echo '<br>' . $result . '<br>';
  }
  if ($showDetails) echo '<pre>Connection Details:' . "\n" . print_r($commInfo, true) . '</pre><br /><br />';

  if ($showDetails) echo '<hr>';

}

function dofsockTest($url = 'www.zen-cart.com/testcurl.php', $port = 80, $timeout = 5) {
  global $goodMessage, $errorMessage, $showDetails;
  /* in case it's not set, set 10-second timeout for fsockopen */
  ini_set("default_socket_timeout", "10");
  $socket = fsockopen($url, $port, $errnum, $errtext, $timeout);
  if ($socket) echo $goodMessage . 'Socket established<br><br>';
  else echo '<br>' .$errorMessage .' Num: ' . $errnum . ', Message: ' . $errtext . '<br><br>';
  if ($showDetails) echo '<hr>';
}


/**
 * FOR DEVELOPERS ONLY:
 * Additional tip about CURLOPT_CAINFO in Development environments  (NOTE: THIS IS NOT SAFE FOR LIVE PRODUCTION SERVERS!!!!!)
 * 1. obtain the cacert.pem file from http://curl.haxx.se/docs/caextract.html
 * 2. place the file on your development server
 * 3. edit your php.ini and set curl.cainfo = '/your/full/path/to/cacert.pem' ... or manually add CURLOPT_CAINFO to every CURL call you do in every php file.
 * NOTE: this opens you up to MITM risks, so should NEVER be done on a live server!!!!!
 */
?>