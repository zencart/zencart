<?php
/**
 * Standalone Diagnostics/Debug tool for testing CURL communications to common 3rd party services such as USPS and PayPal and Authorize.net and more.
 * Accepted parameters:
 *   d=1 or details=1 -- show CURL connection details -- useful for determining cause of communications problems
 *   r=1 -- show Response obtained from destination server -- this may contain an error message, but usually means communication was okay
 *   i=1 -- in conjunction with [d] or [r], will show the detailed curlinfo certificate data from the host being connected to. Helpful for advanced debugging.
 *
 * @package utilities
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Wed Mar 16 16:12:21 2016 -0500 Modified in v1.5.5 $
 */
// no caching
header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<html><head><meta name="robots" content="noindex, nofollow" /><title>Communications Test</title></head>
<body>
<p>Testing communications to various destinations. This is a simple diagnostic to determine whether your server can connect to common destinations.<br>
<em>For advanced "details" mode, add </em><strong>?details=on</strong><em> to the URL.</em></p>
<p><em>(Another resource you may find useful for testing your server's overall customer-facing SSL configuration: <a href="https://www.ssllabs.com/ssltest/index.html" target="_blank">https://www.ssllabs.com/ssltest/index.html</a> )</em></p>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$showDetails = (isset($_GET['d']) && $_GET['d'] != '0') || (isset($_GET['details']) && $_GET['details'] != '0'); // supports ?d= or ?details= and any value other than '0' turns it on.
set_time_limit(500);
$errorMessage = '<span style="color:red;font-weight:bold">Error </span>';
$goodMessage = '<span style="color:green;font-weight:bold">GOOD: </span>';

echo 'Connecting to Zen Cart Version Server (http) ...<br>';
doCurlTest('http://s3.amazonaws.com/zencart-curltest/endpoint');

echo 'Connecting to Zen Cart Version Server (https) ...<br>';
doCurlTest('https://s3.amazonaws.com/zencart-curltest/endpoint');

echo 'Connecting to Zen Cart Support Server (https) ...<br>';
doCurlTest('https://www.zen-cart.com/testcurl.php');

echo 'Connecting to USPS (port 80)...<br>';
doCurlTest('http://production.shippingapis.com/shippingapi.dll');
if (isset($_GET['old']) && $_GET['old'] == '1') {
  echo '2nd test, using old method: ';
  dofsockTest('production.shippingapis.com', 80);
}

echo 'Connecting to USPS Test/Staging/Sandbox Server (port 80)...<br>';
doCurlTest('http://stg-production.shippingapis.com/ShippingApi.dll');
if (isset($_GET['old']) && $_GET['old'] == '1') {
  echo '2nd test, using old method: ';
  dofsockTest('stg-production.shippingapis.com', 80);
}

echo 'Connecting to UPS (port 80)...<br>';
doCurlTest('http://www.ups.com/using/services/rave/qcostcgi.cgi');
dofsockTest('www.ups.com', 80);

echo 'Connecting to UPSXML (SSL) (wwwcie.ups.com) ...<br>';
doCurlTest('https://wwwcie.ups.com/ups.app/xml/Rate');

echo 'Connecting to UPSXML (SSL) (www.ups.com) ...<br>';
doCurlTest('https://www.ups.com/ups.app/xml/Rate');

echo 'Connecting to UPSXML (SSL) (onlinetools.ups.com) ...<br>';
doCurlTest('https://onlinetools.ups.com/ups.app/xml/Rate');

echo 'Connecting to FedEx (port 80)...<br>';
dofsockTest('fedex.com', 80);

echo 'Connecting to Canada Post REST API (SSL) ...<br>';
doCurlTest('https://ct.soa-gw.canadapost.ca/rs/ship/price');

echo 'Connecting to PayPal IPN (port 443)...<br>';
dofsockTest('www.paypal.com', 443);
doCurlTest('https://www.paypal.com/cgi-bin/webscr');

echo 'Connecting to PayPal IPN (port 443) Sandbox ...<br>';
dofsockTest('www.sandbox.paypal.com', 443);
doCurlTest('https://www.sandbox.paypal.com/cgi-bin/webscr');

//echo 'Connecting to PayPal IPN Postback ...<br>';
//dofsockTest('ipnpb.paypal.com', 443);
//doCurlTest('https://ipnpb.paypal.com');
//
//echo 'Connecting to PayPal IPN Postback (Sandbox)...<br>';
//dofsockTest('ipnpb.sandbox.paypal.com', 443);
//doCurlTest('https://ipnpb.sandbox.paypal.com');

echo 'Connecting to PayPal Express/Pro Server ...<br>';
doCurlTest('https://api-3t.paypal.com/nvp');

echo 'Connecting to PayPal Express/Pro Sandbox ...<br>';
doCurlTest('https://api-3t.sandbox.paypal.com/nvp');

echo 'Connecting to PayPal Payflowpro Server ...<br>';
doCurlTest('https://payflowpro.paypal.com/transaction');

echo 'Connecting to Cardinal Commerce 3D-Secure Server ...<br>';
doCurlTest('https://paypal.cardinalcommerce.com/maps/processormodule.asp');

echo 'Connecting to AuthorizeNet Production Server ...<br>';
doCurlTest('https://secure.authorize.net/gateway/transact.dll');

echo 'Connecting to AuthorizeNet Akamai Secondary Production Server ...<br>';
doCurlTest('https://secure2.authorize.net/gateway/transact.dll');

echo 'Connecting to AuthorizeNet Developer/Sandbox Server ...<br>';
doCurlTest('https://test.authorize.net/gateway/transact.dll');

echo 'Connecting to First Data GGe4 server (SSL)...<br>';
doCurlTest('https://checkout.globalgatewaye4.firstdata.com/payment');

echo 'Connecting to Payeezy Processing Server...<br>';
doCurlTest('https://api.payeezy.com/v1/transactions');

echo 'Connecting to Payeezy Sandbox Server...<br>';
doCurlTest('https://api-cert.payeezy.com/v1/transactions');

?>

<em>Testing completed. See results above.</em>
</body>
</html>

<?php
die();
//////// Processing logic ///////

function doCurlTest($url = 'http://s3.amazonaws.com/zencart-curltest/endpoint', $postdata = "field1=This is a test&statuskey=ready") {
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

  curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
  curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart(tm) - CURL TEST v155');

  if (isset($_GET['i'])) curl_setopt($ch, CURLOPT_CERTINFO, TRUE);

//  curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2); // not directly implemented here, because it is more future-proof and therefore generally more secure to allow Curl to autonegotiate the best mutually-supported protocol, by not specifying CURLOPT_SSLVERSION at all.

//  curl_setopt($ch, CURLOPT_CAINFO, '/local/path/to/cacert.pem'); // for offline testing, this file can be obtained from http://curl.haxx.se/docs/caextract.html ... should never be used in production!


  $result = curl_exec($ch);
  $errtext = curl_error($ch);
  $errnum = curl_errno($ch);
  // check for curl TLS version problem, and resubmit  (common with outdated hosts like HostGator)
  if (in_array($errnum, array(35))) {
    echo $errorMessage . $errnum . ': ' . $errtext;
    echo '<br><p style="color:red;"><strong>Error 35 often means that the TLS/SSL connection capabilities of your server are outdated and your server administrator is behind schedule applying security updates, thus preventing the ability to connect to 3rd-party services using more modern security for communications.</strong></p>';
    echo 'Testing again with less security...<br>';
    curl_setopt($ch, CURLOPT_SSLVERSION, 6); // Using the defined value of 6 instead of CURL_SSLVERSION_TLSv1_2 since these outdated hosts also don't properly implement this constant either.
    $result = curl_exec($ch);
    $errtext = curl_error($ch);
    $errnum = curl_errno($ch);
  }

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

function dofsockTest($url = 's3.amazonaws.com/zencart-curltest/endpoint', $port = 80, $timeout = 5) {
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
