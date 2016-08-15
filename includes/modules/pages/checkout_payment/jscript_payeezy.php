<?php
/**
 * Javascript to prep functionality for Payeezy payment module
 *
 * @package payeezy
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   New in v1.5.5 $
 */
if (!defined(MODULE_PAYMENT_PAYEEZYJSZC_STATUS) || MODULE_PAYMENT_PAYEEZYJSZC_STATUS != 'True' || (!defined('MODULE_PAYMENT_PAYEEZYJSZC_JSSECURITY_KEY') && !defined('MODULE_PAYMENT_PAYEEZYJSZC_JSSECURITY_KEY_SANDBOX') )) {
	return false;
}
?>
<script type="text/javascript"><!--

var Payeezy = function() {
    function e(e) {
        var t = {
            visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
            mastercard: /^5[1-5][0-9]{14}$/,
            amex: /^3[47][0-9]{13}$/,
            diners: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
            discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
            jcb: /^(?:2131|1800|35\d{3})\d{11}$/
        };
        for (var n in t) {
            if (t[n].test(e)) {
                return n
            }
        }
    }

    function t() {
        var e = [];
        var t = document.getElementsByTagName("input");
        var n = document.getElementsByTagName("select");
        for (i = 0; i < t.length; i++) {
            var r = t[i];
            var s = r.getAttribute("payeezy-data");
            if (s) {
                e[s] = r.value
            }
        }
        for (i = 0; i < n.length; i++) {
            var r = n[i];
            var s = r.getAttribute("payeezy-data");
            if (s) {
                e[s] = r.value
            }
        }
        return e
    }
    return {
        createToken: function(e) {
            this["clientCallback"] = e;
            var r = t();
            var i = 0;
            var s = {};
            var o = 0;
            var u = [];
            if (!this.apikey) {
                i = 400;
                u[o] = {
                    description: "Please set the API Key"
                };
                o++
            }
            if (!this.js_security_key) {
                i = 400;
                u[o] = {
                    description: "Please set the js_security_key"
                }
            }
            if (!this.ta_token) {
                i = 400;
                u[o] = {
                    description: "Please set the ta_token"
                }
            }
            if (!this.auth) {
                i = 400;
                u[o] = {
                    description: "Please set auth value"
                }
            }
            if (u.length > 0) {
                s["error"] = {
                    messages: u
                };
                e(i, s);
                return false
            }

            var a = "https://" + this.apiEndpoint + "/v1/securitytokens?apikey=" + this.apikey + "&js_security_key=" + this.js_security_key 
                  + "&callback=Payeezy.callback&auth=" + this.auth + "&ta_token=" + this.ta_token + "&type=FDToken&credit_card.type=" + encodeURIComponent(r["card_type"]) 
                  + "&credit_card.cardholder_name=" + encodeURIComponent(r["cardholder_name"]) + "&credit_card.card_number=" + r["cc_number"].replace(/[^0-9]/g,'') 
                  + "&credit_card.exp_date=" + r["exp_month"].replace(/[^0-9]/g,'') + r["exp_year"].replace(/[^0-9]/g,'') 
                  + "&credit_card.cvv=" + r["cvv_code"].replace(/[^0-9]/g,'');

            if (r["currency"]        != undefined) a = a + "&currency=" + encodeURIComponent(r["currency"]);
            if (r["billing.street"]  != undefined) a = a + "&billing_address.street=" + encodeURIComponent(r["billing.street"]);
            if (r["billing.city"]    != undefined) a = a + "&billing_address.city=" + encodeURIComponent(r["billing.city"]);
            if (r["billing.state"]   != undefined) a = a + "&billing_address.state_province=" + encodeURIComponent(r["billing.state"]);
            if (r["billing.country"] != undefined) a = a + "&billing_address.country=" + encodeURIComponent(r["billing.country"]);
            if (r["billing.zip"]     != undefined) a = a + "&billing_address.zip_postal_code=" + encodeURIComponent(r["billing.zip"]);
            if (r["billing.email"]   != undefined) a = a + "&billing_address.email=" + encodeURIComponent(r["billing.email"]);
            if (r["billing.phone"]   != undefined) a = a + "&billing_address.phone.number=" + encodeURIComponent(r["billing.phone"]);

            var f = document.createElement("script");
            f.src = a;
            console.log(a);
            document.getElementsByTagName("head")[0].appendChild(f)
        },
        setApiKey: function(e) {
            this["apikey"] = e
        },
        setJs_Security_Key: function(e) {
            this["js_security_key"] = e
        },
        setTa_token: function(e) {
            this["ta_token"] = e
        },
        setAuth: function(e){
            this["auth"] = e
        },
        setApiEndpoint: function(e){
            this["apiEndpoint"] = e
        },
        callback: function(e) {
            this["clientCallback"](e.status, e.results)
        }
    }
}();

var responseHandler = function(status, response) {
    var $form = $('form[name="checkout_payment"]');

    // alert('Status = ' + status);

    if (status != 201) {
        console.error(response);
        if (response.Error && status != 400) {
            var errormsg = response.Error.messages;
            var errorcode = JSON.stringify(errormsg[0].code, null, 4);
            var errorMessages = JSON.stringify(errormsg[0].description, null, 4);
            alert('Error: Unable to process. Please report this message: Error Code:' + errorcode + ', Messages:' + errorMessages);
        }
        if (status == 400 || status == 500) {
            var errormsg = response.Error.messages;
            var errorMessages = "";
            for(var i in errormsg){
                var ecode = errormsg[i].code;
                var eMessage = errormsg[i].description;
                errorMessages = errorMessages + 'Error Code:' + ecode + ', Error Messages:' + eMessage;
            }
            alert('Error: Unable to process. Please report these messages: ' + errorMessages);
        }
        $form.find('button').prop('disabled', false);
    } else {
        // alert('success');
        console.log(response);
        var result = response.token.value;
        $('#payeezyjszc_fdtoken').val(result);

        // alert('FDToken:' + $('#payeezyjszc_fdtoken').val());

        $form.unbind();
        $form.off();

        var cc_num = $('#payeezyjszc_cc-number').val();
        var lastFour = cc_num.substr(cc_num.length - 4)
        var firstFour = cc_num.substr(0, 4)
        var new_cc_num = firstFour + '-XXXX-XXXX-' + lastFour;
        $('#payeezyjszc_cc-number').val(new_cc_num);

        // delay for DOM update
        setTimeout($form.submit(), 800);
    }
};
var apiKey = '<?php echo constant('MODULE_PAYMENT_PAYEEZYJSZC_API_KEY' . (MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE == 'Sandbox' ? '_SANDBOX' : '')); ?>';
var js_security_key = '<?php echo constant('MODULE_PAYMENT_PAYEEZYJSZC_JSSECURITY_KEY' . (MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE == 'Sandbox' ? '_SANDBOX' : '')); ?>';
var ta_token = '<?php echo MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE == 'Sandbox' ? 'NOIW' : MODULE_PAYMENT_PAYEEZYJSZC_TATOKEN; ?>';
var auth = 'true';
var apiEndpoint = '<?php echo MODULE_PAYMENT_PAYEEZYJSZC_TESTING_MODE == 'Sandbox' ? 'api-cert.payeezy.com' : 'api.payeezy.com'; ?>';

jQuery(function($) {
    $('form[name="checkout_payment"]').submit(function(e) {
        if($('#pmt-payeezyjszc').is(':checked') || this['payment'].value == 'payeezyjszc') {
            var $form = $(this);
            $form.find('button').prop('disabled', true);
            e.preventDefault();

            Payeezy.setApiKey(apiKey);
            Payeezy.setJs_Security_Key(js_security_key);
            Payeezy.setTa_token(ta_token);
            Payeezy.setAuth(auth);
            Payeezy.setApiEndpoint(apiEndpoint);
            Payeezy.createToken(responseHandler);
            return false;
        }
    });
});

--></script>
