<?php
/**
 * ipncheck.php diagnostic tool
 *
 * @package utility
 * @copyright Copyright 2007-2010 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ipncheck.php 15777 2010-04-02 01:59:22Z drbyte $
 *
 * This utility is intended to be used to check whether a Zen Cart store is able to connect TO PayPal in order to RESPOND to an incoming IPN notification.
 * Unfortunately it cannot test whether PayPal's servers can successfully post an IPN *to* your store.  To do that one should test a live transaction.
 *
 * USAGE INSTRUCTIONS:
 * 1. Open your browser
 * 2. Enter the URL for your store, followed by /extras/ipncheck.php
 * 3. ... and press Enter
 * 4. Review the results. If the results are not positive (ie: "OKAY" response), post the results AND the full URL to your /extras/ipncheck.php script of your site ... into a support ticket on the Zen Cart forum.
 */

if (!function_exists('curl_setopt_array')) {
   function curl_setopt_array(&$ch, $curl_options)
   {
       foreach ($curl_options as $option => $value) {
           if (!curl_setopt($ch, $option, $value)) {
               return false;
           }
       }
       return true;
   }
}

$verboseMode = (isset($_GET['verbose']) ? TRUE : FALSE);
$headerMode = (isset($_GET['headers']) ? TRUE : FALSE);
$testBoth = (isset($_GET['both']) && $_GET['both'] == '0') ? FALSE : TRUE;
$checkNoChex = (isset($_GET['nochex'])) ? TRUE : FALSE;
$testSandbox = (isset($_GET['sandbox'])) ? TRUE : FALSE;

$_POST = array();
if (isset($GLOBALS)) unset($GLOBALS);
if (isset($_GET)) unset($_GET);
if (isset($_REQUEST)) unset($_REQUEST);
$_POST['ipn_mode'] = 'communication_test';
if ($testSandbox) $_POST['test_ipn'] = 1;
define('ENABLE_SSL','true');
define('MODULE_PAYMENT_PAYPAL_HANDLER', 'www.paypal.com/cgi-bin/webscr');


echo 'IPNCHECK.PHP - Version 1.3.9';
echo '<br /><br /><pre>';
    $defaultMethod = $altMethod = '';
    $info = '';
    $postdata = '';
    $postback = '';
    $postback_array = array();

    //build post string
    foreach($_POST as $key=>$value) {
      $postdata .= $key . "=" . urlencode(stripslashes($value)) . "&";
      $postback .= $key . "=" . urlencode(stripslashes($value)) . "&";
      $postback_array[$key] = $value;
    }
    $postback .= "cmd=_notify-validate";
    $postback_array['cmd'] = "_notify-validate";
    if ($postdata == '=&') {
      die('IPN NOTICE :: No POST data to process -- Bad IPN data<br /><pre>' . print_r($_POST, true));
    }
    $postdata_array = $_POST;
    ksort($postdata_array);

    if (sizeof($postdata_array) == 0) die('Nothing to process. Please return to home page.');

    // send received data back to PayPal for validation
      $scheme = 'https://';
      //Parse url
      $web = parse_url($scheme . MODULE_PAYMENT_PAYPAL_HANDLER);
      if ($checkNoChex == TRUE) $web = parse_url('https://www.nochex.com/nochex.dll/apc/apc');
      if (isset($_POST['test_ipn']) && $_POST['test_ipn'] == 1) {
        $web = parse_url($scheme . 'www.sandbox.paypal.com/cgi-bin/webscr');
      }
      //Set the port number
      if($web['scheme'] == "https") {
        $web['port']="443";  $web['protocol'] = "ssl://";
      } else {
        $web['port']="80";   $web['protocol'] = "";
      }


      $result = '';
      $data = '';
      if (function_exists('curl_init')) {
        $result = doPayPalIPNCurlPostback($web, $postback, $verboseMode, $headerMode);
        if (in_array($result, array('VERIFIED', 'SUCCESS', 'INVALID'))) {
          echo nl2br('IPN TESTING - Response Received via CURL -- <strong>COMMUNICATIONS OKAY</strong>' . "\n<!--" . $data . '-->');
          $defaultMethod = 'CURL';
          $altMethod = 'FSOCKOPEN';
        }
      } else {
        echo nl2br('CURL not available. Will attempt to connect using fsockopen() instead.' . "\n");
      }

      if (!in_array($result, array('VERIFIED', 'SUCCESS', 'INVALID')) || $testBoth === TRUE) {
        $result = doPayPalIPNFsockopenPostback($web, $postback);
        echo nl2br('IPN TESTING - Confirmation/Validation response with fsockopen(): <strong>' .$result . "</strong>\n<!--" . $info . '-->');
        if ($defaultMethod == '' && $result != 'FAILED') {
          $defaultMethod = 'FSOCKOPEN';
          $altMethod = 'CURL';
        }
      }
if ($defaultMethod != '') {
  echo '<br><br>Default method likely to be used for communications is: <strong>' . $defaultMethod . '</strong>, with the fallback method being <strong>' . $altMethod . '</strong> if possible.';
}
echo '<br><br>Script finished.';


/************************************/

  function doPayPalIPNFsockopenPostback($web, $postback) {
    global $info;
    $header  = "POST " . $web['path'] . " HTTP/1.1\r\n";
    $header .= "Host: " . $web['host'] . "\r\n";
    $header .= "Content-type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-length: " . strlen($postback) . "\r\n";
    $header .= "Connection: close\r\n\r\n";
    $errnum = 0;
    $errstr = '';
    $ssl = $web['protocol'];
    //Create paypal connection
    $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);

    if(!$fp && $ssl == 'ssl://') {
      echo nl2br("\n" . 'IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again with HTTPS over 443 ...");
      $ssl = 'https://';
      $web['port'] = '443';
      $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp && $ssl == 'https://') {
      echo nl2br("\n" . 'IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again directly over 443 ...");
      $ssl = '';
      $web['port'] = '443';
      $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      echo nl2br("\n" . 'IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again with HTTP over port 80 ...");
      $ssl = 'http';
      $web['port'] = '80';
      $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      echo nl2br("\n" . 'IPN ERROR :: Could not establish fsockopen: ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n Trying again without any specified protocol, using port 80 ...");
      $ssl = '';
      $web['port'] = '80';
      $fp=fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);
    }
    if(!$fp) {
      echo nl2br("\n" . 'IPN FATAL ERROR :: Could not establish fsockopen. ' . "\n" . 'Host Details = ' . $ssl . $web['host'] . ':' . $web['port'] . ' (' . $errnum . ') ' . $errstr . "\n");
      die();
    }

    fputs($fp, $header . $postback . "\r\n\r\n");
    $header_data = '';
    $headerdone = false;
    //loop through the response from the server
    while(!feof($fp)) {
      $line = @fgets($fp, 1024);
      if (strcmp($line, "\r\n") == 0) {
        // this is a header row
        $headerdone = true;
        $header_data .= $line;
      } else if ($headerdone) {
        // header has been read. now read the contents
        $info[] = $line;
      }
    }
    //close fp - we are done with it
    fclose($fp);
    //break up results into a string
    $info = implode("", $info);
    $status = (strstr($info,'VERIFIED')) ? 'VERIFIED' : (strstr($info,'SUCCESS')) ? 'SUCCESS' : (strstr($info,'INVALID')) ? 'FSOCKOPEN() RESPONSE RECEIVED - Communications OKAY' : 'FAILED';
    echo "\n\n" . '<!-- IPN INFO - Confirmation/Validation response ' . "\n-------------\n" . $header_data . $info . "\n--------------\n -->";

    return $status;
  }


  function doPayPalIPNCurlPostback($web, $vars, $verboseMode = FALSE, $headerMode = FALSE) {
    $status = 'Attempted connection on: ' .$web['scheme'] . '://' . $web['host'] . $web['path'];
    $ch = curl_init($web['scheme'] . '://' . $web['host'] . $web['path']);
    $curlOpts = array(CURLOPT_URL => $web['scheme'] . '://' . $web['host'] . $web['path'],
                      CURLOPT_POST => TRUE,
                      CURLOPT_POSTFIELDS => $vars,
                      CURLOPT_TIMEOUT => 45,
                      CURLOPT_CONNECTTIMEOUT => 30,
                      CURLOPT_VERBOSE => ($verboseMode ? TRUE : FALSE),
                      CURLOPT_HEADER => ($headerMode ? TRUE : FALSE),
                      CURLOPT_FOLLOWLOCATION => FALSE,
                      CURLOPT_RETURNTRANSFER => TRUE,
                      CURLOPT_SSL_VERIFYPEER => FALSE,
                      CURLOPT_SSL_VERIFYHOST => 2,
                      CURLOPT_FORBID_REUSE => TRUE,
                      CURLOPT_FRESH_CONNECT => TRUE,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_USERAGENT => 'Zen Cart(tm) - IPN Postback TEST',
                      );
    if ($web['port'] != '80') {
      $curlOpts[CURLOPT_PORT] = $web['port'];
    }
/*    if (CURL_PROXY_REQUIRED == 'True') {
      $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
      curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
      curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
    }
*/
    curl_setopt_array($ch, $curlOpts);
    $response = curl_exec($ch);
    $commError = curl_error($ch);
    $commErrNo = curl_errno($ch);
    $errors = ($commErrNo != 0 ? "\n(" . $commErrNo . ') ' . $commError : '');

    if (($response == '' || $errors != '') && ($web['scheme'] != 'http')) {
      if ($verboseMode) echo nl2br("\n\n" . 'VERBOSE output:' . "\n-------------\n<pre>" . htmlspecialchars($response) . "</pre>\n--------------\n");
      echo nl2br('CURL ERROR: ' . $status . $errors . "\n" . 'Trying direct HTTP on port 80 instead ...' . "\n");
      $web['scheme'] = 'http';
      $web['port'] = '80';
      $status = 'Attempted alternate connection on: ' .$web['scheme'] . '://' . $web['host'] . $web['path'] . "\n<br />";
      curl_setopt($ch, CURLOPT_URL, $web['scheme'] . '://' . $web['host'] . $web['path']);
      curl_setopt($ch, CURLOPT_PORT, $web['port']);
      $response = curl_exec($ch);
      $commError = curl_error($ch);
      $commErrNo = curl_errno($ch);
    }
    //$commInfo = @curl_getinfo($ch);
    curl_close($ch);
    //die("\n\n".'data:'.$response);
    if ($verboseMode) echo nl2br("\n\n" . 'VERBOSE output: ' . "\n-------------\n<pre>" . htmlspecialchars($response) . "</pre>\n--------------\n");
    $errors = ($commErrNo != 0 ? "\n(" . $commErrNo . ') ' . $commError : '');
    if ($errors != '') {
      echo nl2br('CURL ERROR: ' . $status . $errors . "\n" . 'ABORTING CURL METHOD ...' . "\n\n");
    }

    $status = (strstr($response,'VERIFIED')) ? 'VERIFIED' : (strstr($response,'SUCCESS')) ? 'SUCCESS' : (strstr($response,'INVALID')) ? 'CURL RESPONSE RECEIVED - Communications OKAY' : 'FAILED';
    echo  $status . '<br />';


    return $response;
  }

