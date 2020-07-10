<?php
/**
 * payer_auth_verifier page
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2005 CardinalCommerce
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 30 Modified in v1.5.6b $
 */
/**
 * Purpose:
 * This is the page that the Card Issuer's Access Control Server (ACS)
 * will return the customer to. The Card Issuer will POST the results
 * of the authentication to this page. The authentication data will be
 * verified using the Cardinal Centinel code in the payment module.
 * The response will contain the XID, CAVV, ECI, Authentication Status
 * and Signature values.
 *
 * Checking these values will determine what the next step in the flow
 * should be. If the authentication is successful, then the CAVV, ECI,
 * and XID values are used by the next stages of the payment module.
 *
 * If the authentication was unsuccessful or resulted in an error, the
 * business rules ('Proceed without Chargeback Protection') in the
 * module configuration will be used to determine if the transaction
 * should continue.
 *
 * A configuration value of 'Yes', will allow both authenticated and
 * transactions that are unable to be authenticated to complete and
 * proceed to authorization. This will result in liability protection
 * for only those transactions that were authenticated.
 *
 * A configuration value of 'No', will require all consumers to
 * successfully authenticate themselves prior to completing the
 * purchase. This will result in liability protection granted by the
 * Verified by Visa, MasterCard SecureCode, and JCB J/Secure programs
 * on all completed transactions. By using this configuration value,
 * you may prevent consumers from using certain credit cards
 * (commercial credit cards, prepaid credit cards) that are simply not
 * eligible for the program. In the event that a consumer attempts to
 * use a credit card that is not eligible for authentication, they will
 * be redirected to the payment details page and prompted for another
 * form of payment.
 */

// if the customer is not logged on, redirect them to the login page
  if (!zen_is_logged_in()) {
    die(WARNING_SESSION_TIMEOUT);
  }

// Get the MD back and set it as the session id
  if (isset($_REQUEST["MD"]) && $_REQUEST["MD"] != null && strlen($_REQUEST["MD"]) != 0) {
    session_id($_REQUEST["MD"]);
  }

  // load all enabled modules
  if (!isset($_SESSION['payment']) || $_SESSION['payment'] == '') zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($_SESSION['payment']);
  $payment_module = $_SESSION['payment'];
  unset($_SESSION['3Dsecure_acsURL']);
  unset($_SESSION['3Dsecure_payload']);
  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;
  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
  $order_totals = $order_total_modules->process();

  if ($_SESSION['3Dsecure_transactionId'] == '') {
    // validate if the card enrolled lookup requirements have been met
    if ($_SESSION['3Dsecure_requires_lookup'] == true && strcasecmp('Y', $_SESSION['3Dsecure_enroll_lookup_attempted']) != 0) {
      // enrollment lookup was required for the card type, but was not completed
      $error = ERROR_PAYMENT_FAILURE_TEXT;
      $messageStack->add_session('checkout_payment', $error . '<!-- ['.$payment_module->code.'] -->', 'error');
      $redirectPage = zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false);
    } else {
      // enrollment lookup was either not required for the card type or was required and completed
      $redirectPage = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true);
    }

  } else {

    /////////////////////////////////////////////////////////////////////////////////////////
    // Retrieve the PaRes and MD values from the Card Issuer's Form POST to this Term URL page.
    // If you like, the MD data passed to the Card Issuer could contain the TransactionId
    // that would enable you to reestablish the transaction session. This would be the
    // alternative to using the Client Session Cookies
    /////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////
    // If the PaRes is Not Empty then process the cmpi_authenticate message
    /////////////////////////////////////////////////////////////////////////////////////////

    if (isset($_POST["PaRes"]) && $_POST["PaRes"] != '') {
      $authenticate_data_array = array('transaction_id' => $_SESSION['3Dsecure_transactionId'],
                                       'payload' => $_POST["PaRes"]);

      if (is_object(${$payment_module})) {
        $authenticate_resp_array = ${$payment_module}->get3DSecureAuthenticateResponse($authenticate_data_array);
      }

      $shouldContinue = $authenticate_resp_array['continue_flag'];
      $auth_status = $authenticate_resp_array['auth_status'];
      $sig_status = $authenticate_resp_array['sig_status'];
      $error_no = $authenticate_resp_array['error_no'];
      $error_desc = $authenticate_resp_array['error_desc'];
      $auth_xid = $authenticate_resp_array['auth_xid'];
      $auth_cavv = $authenticate_resp_array['auth_cavv'];
      $auth_eci = $authenticate_resp_array['auth_eci'];

      $_POST['paypalwpp_cc_number'] = $authenticate_resp_array['cc3d_card_number'];
      $_POST['paypalwpp_cc_checkcode'] = $authenticate_resp_array['cc3d_checkcode'];
      $_POST['paypalwpp_cc_expires_month'] = $authenticate_resp_array['cc3d_exp_month'];
      $_POST['paypalwpp_cc_expires_year'] = $authenticate_resp_array['cc3d_exp_year'];
      $merchantData = unserialize($_SESSION['3Dsecure_merchantData']);
      $_POST['paypalwpp_cc_issue_month'] = $merchantData['im'];
      $_POST['paypalwpp_cc_issue_year'] = $merchantData['iy'];
      $_POST['paypalwpp_cc_issuenumber'] = $merchantData['in'];
      $_POST['paypalwpp_cc_firstname'] = $merchantData['fn'];
      $_POST['paypalwpp_cc_lastname'] = $merchantData['ln'];

      $_SESSION['3Dsecure_auth_status'] = $auth_status;
      $_SESSION['3Dsecure_auth_xid'] = $auth_xid;
      $_SESSION['3Dsecure_auth_cavv'] = $auth_cavv;
      $_SESSION['3Dsecure_auth_eci'] = $auth_eci;

      unset($_SESSION['3Dsecure_transactionId']);

      /////////////////////////////////////////////////////////////////////////////////////////
      // Determine if the result was Successful or Error
      //
      // If the Authentication results (PAResStatus) is a Y or A, and the SignatureVerification is Y, then
      // the Payer Authentication was successful. The Authorization Message should be processed,
      // and the User taken to a Order Confirmation location.
      //

      /////////////////////////////////////////////////////////////////////////////////////////
      // If the following condition is met, then the authentication result was acceptable.
      /////////////////////////////////////////////////////////////////////////////////////////

      if (strcasecmp("Y", $shouldContinue) == 0) {
        ////////////////////////////////////////////////////////////////////
        // Business rules are set to continue to authorization
        ////////////////////////////////////////////////////////////////////
        $redirectPage = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false);

      } else {
        ////////////////////////////////////////////////////////////////////
        // Business rules are set to prompt for another form of payment
        ////////////////////////////////////////////////////////////////////
        $error = ${$payment_module}->get_authentication_error();

        $messageStack->add_session('checkout_payment', $error . '<!-- ['.${$payment_module}->code.'] -->', 'error');
        $redirectPage = zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false);
      }

    } else {
      ////////////////////////////////////////////////////////////////////
      // Business rules are set to continue to authorization
      ////////////////////////////////////////////////////////////////////
      $redirectPage = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true, false);
    }
  }

header("Cache-Control: max-age=1");  // stores for only 1 second, which prevents page from being re-displayed
?>
<html>
<head>
<title>Payer Authentication Window</title>
<script type="text/javascript">
  function onLoadHandler(){
    setTimeout(document.frmResultPage.submit(), 2);
  }
</script>
</head>
<body onLoad="onLoadHandler();">
<center>
<form name="frmResultPage" target="_top" method="post" action="<?php echo $redirectPage; ?>">
<br />Processing. Please wait.
<br /><br /><em>This may take a few moments...</em>
<br /><br /><?php echo zen_image(DIR_WS_IMAGES.'3ds/pleasewait.gif');?>

<?php
  // Call pre_confirmation_check on the underlying payment module.
  ${$payment_module}->pre_confirmation_check();
  // output the appropriate POST vars so form can be processed for submission to gateway
  echo ${$payment_module}->process_button();
?>
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
