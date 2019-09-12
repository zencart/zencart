<?php
/**
 * Standalone TLS Test tool for PayPal connection readiness in 2016
 * per https://www.paypal-knowledge.com/infocenter/index?page=content&widgetview=true&id=FAQ1914&viewlocale=en_US
 *
 * Accepted parameters:
 *   i=1 -- to show certificate details
 *
 * @package utilities
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Wed Mar 16 16:12:21 2016 -0500  New in v1.5.5 $
 */
// don't show error messages to browser
ini_set('display_errors', 0);

// no caching
header('Cache-Control: no-cache, no-store, must-revalidate');


$url = 'https://tlstest.paypal.com';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart(tm) - TLS TEST');

if (isset($_GET['i'])) curl_setopt($ch, CURLOPT_CERTINFO, TRUE);

$result = curl_exec($ch);
$errtext = curl_error($ch);
$errnum = curl_errno($ch);
$commInfo = @curl_getinfo($ch);

if (isset($commInfo['url'])) $commInfo['url'] = '"' . $commInfo['url'] . '"';

// Handle results
if ($errnum == 35) {
  echo '<p style="color:red;font-weight: bold;">Error 35 - Your server does not yet support proper auto-negotiation of secure communications protocols. We will try again by downgrading the communications parameters. This means your server administrator still needs to apply some updates to make your server fully compatible with modern security standards.</p><br><br>';
  echo 'Trying again with lesser security:<br><br>';
  curl_setopt($ch, CURLOPT_SSLVERSION, 6); // Using the defined value of 6 instead of CURL_SSLVERSION_TLSv1_2 since these outdated hosts also don't properly implement this constant either.
  $result = curl_exec($ch);
  $errtext = curl_error($ch);
  $errnum = curl_errno($ch);
  $commInfo = @curl_getinfo($ch);
}
curl_close ($ch);

if ($errnum != 0) {
  echo 'Error: ' . $errnum . ': ' . $errtext . '<br><br>';
} else {
  echo 'CURL TLS Connection successful.<br><br>';
  echo '<pre>' . $result . '</pre><br>';
}
echo '<pre>Connection Details:' . "\n" . print_r($commInfo, true) . '</pre><br /><br />';
echo '<br><br><br><em>Advanced use: To also display the certificate chain, add <strong>?i=</strong> to the end of the URL.</em>';
