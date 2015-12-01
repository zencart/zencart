<?php
/**
 * payer_auth_auth page
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2005 CardinalCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 15898 2010-04-12 15:12:09Z drbyte $
 */
  if (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] < 1) {
    die(WARNING_SESSION_TIMEOUT);
  }
// load all enabled modules
  if (!isset($_SESSION['payment']) || $_SESSION['payment'] == '') zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($_SESSION['payment']);
  $payment_module = $_SESSION['payment'];

/**
 * Purpose:
 * Form used to POST the payer authentication request to the Card
 * Issuer's Access Control Server (ACS). The ACS will in turn display
 * the payer authentication window to the consumer within this area.

 * Note that the form field names below are CASE SENSITIVE, and all
 * form fields listed below are required.
 */
header("Cache-Control: max-age=1");  // stores for only 1 second, which prevents page from being re-displayed
?>
<html>
<head>
<script>
  function onLoadHandler(){
    document.frmLaunchACS.submit();
  }
</script>
</head>
<body onLoad="onLoadHandler();">
<br><br><br><br>
<center>
<form name="frmLaunchACS" method="post" action="<?php echo $_SESSION['3Dsecure_acsURL']; ?>">
<input type=hidden name="PaReq" value="<?php echo $_SESSION['3Dsecure_payload']; ?>">
<input type=hidden name="TermUrl" value="<?php echo $_SESSION['3Dsecure_term_url']; ?>">
<input type=hidden name="MD" value="<?php echo zen_session_id(); ?>">
<noscript>
  <br><br>
  <center>
  <font color="red">
  <h1>Processing your Payer Authentication Transaction</h1>
  <h2>JavaScript is currently disabled or is not supported by your browser.<br></h2>
  <h3>Please click Submit to continue the processing of your transaction.</h3>
  </font>
  <input type="submit" value="Submit">
  </center>
</noscript>
</form>
</center>
</body>
</html>
<?php die(); ?>