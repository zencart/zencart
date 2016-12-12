<?php
/**
 * paypal EC In-Context button display template
 *
 * @package     paypal_incontext
 * @copyright   Copyright 2003-2016 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @copyright   Portions Copyright 2012-2016 mc12345678
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: mc12345678  Wed Aug 24 20:20:09 2016 -0500 Modified in v1.6.0 $
 *
 *   $buttonContainer is either the 'id' for an HREF <a> tag or <button> tag.
 *     it can be represented as a string or as a javascript element.
 */

if (   defined('MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE') && MODULE_PAYMENT_PAYPALWPP_CHECKOUTSTYLE == 'InContext' 
    && defined('MODULE_PAYMENT_PAYPALWPP_MERCHANTID')    && MODULE_PAYMENT_PAYPALWPP_MERCHANTID    != '' 
    && defined('MODULE_PAYMENT_PAYPALWPP_STATUS')        && MODULE_PAYMENT_PAYPALWPP_STATUS        == 'True') {
    
    if (isset($buttonContainer) && zen_not_null($buttonContainer) && $buttonContainer != '') {

        if (isset($_SESSION['payment']) && !empty($_SESSION['payment'])) {
            $payMod = $_SESSION['payment'];
        } else {
            $payMod = null;
        }

        require_once(DIR_WS_CLASSES . 'payment.php');
        $paypalwpp_module = 'paypalwpp';
        // init the payment object
        $classPay = new payment($paypalwpp_module);
        if (zen_not_null($payMod)) {
            $_SESSION['payment'] = $payMod;
        } else {
            unset($_SESSION['payment']);
        }
        unset($payMod);
?><script type="text/javascript">
window.paypalCheckoutReady = function () {
  paypal.checkout.setup('<?php echo MODULE_PAYMENT_PAYPALWPP_MERCHANTID; ?>', {
    locale: '<?php echo ${$paypalwpp_module}->getLanguageCode('incontext'); ?>',
<?php
    echo (MODULE_PAYMENT_PAYPALWPP_SERVER == 'live' ? '' : "\n          environment: 'sandbox',"); ?>
    buttons: [{
      container: '<?php echo $buttonContainer; ?>',
      type: 'checkout',
      color: 'gold', <?php /* possible colors are 'gold', 'blue', or 'silver' echo (defined('MODULE_PAYMENT_PAYPALWPP_INCONTEXTCOLOR') && MODULE_PAYMENT_PAYPALWPP_INCONTEXTCOLOR != '' ? MODULE_PAYMENT_PAYPALWPP_INCONTEXTCOLOR : 'gold'); */ ?>
      size: '<?php echo (defined('MODULE_PAYMENT_PAYPALWPP_INCONTEXTSIZE') && MODULE_PAYMENT_PAYPALWPP_INCONTEXTSIZE != '' ? MODULE_PAYMENT_PAYPALWPP_INCONTEXTSIZE : 'small'); ?>',
      shape: '<?php echo (defined('MODULE_PAYMENT_PAYPALWPP_INCONTEXTSHAPE') && MODULE_PAYMENT_PAYPALWPP_INCONTEXTSHAPE != '' ? MODULE_PAYMENT_PAYPALWPP_INCONTEXTSHAPE : 'pill'); ?>'
    }],
    click: function() {
      paypal.checkout.initXO();

      var action = $.post('/set-express-checkout');

      action.done(function (data) {
        paypal.checkout.startFlow(data.token);
      });

      action.fail(function () {
        paypal.checkout.closeFlow();
      });
    }
  });
};
</script>
<?php 
    }
?>
<script async="async" src="//www.paypalobjects.com/api/checkout.js" type="text/javascript"></script>
<?php
}
