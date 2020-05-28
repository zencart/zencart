<?php
/**
 * Javascript to prep functionality for Square payment module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Dec 17 Modified in v1.5.7 $
 */
if (!defined('MODULE_PAYMENT_SQUARE_STATUS') || MODULE_PAYMENT_SQUARE_STATUS != 'True' || (!defined('MODULE_PAYMENT_SQUARE_APPLICATION_ID') || MODULE_PAYMENT_SQUARE_ACCESS_TOKEN == '')) {
    return false;
}
if ($payment_modules->in_special_checkout() || empty($square) || !$square->enabled) {
    return false;
}

$jsurl = 'https://js.squareup.com/v2/paymentform';
if (MODULE_PAYMENT_SQUARE_TESTING_MODE === 'Sandbox') {
    $jsurl = 'https://js.squareupsandbox.com/v2/paymentform';
}

?>
<script type="text/javascript" src="<?php echo $jsurl; ?>" title="square js"></script>


<script type="text/javascript" title="square">
    var cardNonce;
    var paymentForm = new SqPaymentForm({
        applicationId: '<?php echo MODULE_PAYMENT_SQUARE_APPLICATION_ID; ?>',
        inputClass: 'paymentInput',
        inputStyles: [
            {
                fontSize: '14px',
                padding: '7px 12px',
                backgroundColor: "white"
            }
        ],
        cardNumber: {
            elementId: 'square_cc-number',
            placeholder: '•••• •••• •••• ••••'
        },
        cvv: {
            elementId: 'square_cc-cvv',
            placeholder: 'CVV'
        },
        expirationDate: {
            elementId: 'square_cc-expires',
            placeholder: 'MM/YY'
        },
        postalCode: {
            elementId: 'square_cc-postcode',
            placeholder: '11111'
        },
        callbacks: {
            cardNonceResponseReceived: function (errors, nonce, cardData) {
                if (errors) {
                    console.error("Encountered errors:");
                    var error_html = ""
                    errors.forEach(function (error) {
                        console.error('  ' + error.message);
                        error_html += "<li> " + error.message + " </li>";
                    });
                    document.getElementById('card-errors').innerHTML = '<ul>' + error_html + '</ul>';
                    $('#paymentSubmitButton').disabled = false;
                } else {
                    // success
                    $('#paymentSubmitButton').disabled = true;
                    $("#card-errors").empty()
                    document.getElementById('card-nonce').value = nonce;
                    document.getElementById('card-type').value = cardData.card_brand;
                    document.getElementById('card-four').value = cardData.last_4;
                    document.getElementById('card-exp').value = ('0'+cardData.exp_month.toString()).substr(-2) + cardData.exp_year.toString().substr(-2);
                    document.getElementsByName('checkout_payment')[0].submit();
                }

            },
            unsupportedBrowserDetected: function () {
                document.getElementById('card-errors').innerHTML = '<p class="error alert">This browser is not supported for Square Payments. Please contact us to let us know!  Meanwhile, please pay using an alternate method; or shop using a different browser such as FireFox or Chrome.</p>';
                paymentForm.destroy();
            },

            inputEventReceived: function (inputEvent) {
                switch (inputEvent.eventType) {
                    case 'focusClassAdded':
                        methodSelect('pmt-square');
                        break;
                    case 'cardBrandChanged':
                        document.getElementById('sq-card-brand').innerHTML = inputEvent.cardBrand;
                        break;
                }
            },
            paymentFormLoaded: function () {
                paymentForm.setPostalCode('<?php echo $order->billing['postcode']; ?>');
            }
        }
    });

    $(function () {
        $.ajaxSetup({
            headers: {"X-CSRFToken": "<?php echo $_SESSION['securityToken']; ?>"}
        });
        $('form[name="checkout_payment"]').submit(function(e) {
            if($('#pmt-square').is(':checked') || this['payment'].value == 'square' || document.getElementById('pmt-square').checked == true) {
                e.preventDefault();
                paymentForm.requestCardNonce();
            }
        });
    });
</script>
<style title="square styles">
.paymentInput {display:inline;font-size:1em;margin:0 0.1em 10px 0;height:35px;padding-left:5px;width:50%;}
.paymentInput {background-color: white;border:3px solid #ccc;}
.paymentInput--error {color: red; border-color: red;}
</style>
