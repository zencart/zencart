/**
 * Javascript - for checkout and some simple helpers
 *
 * NOTE: Depends on jQuery
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zen_general.js $
 */

var selected;

/**
 * Validate requested amount of GV redemption
 * `submitter` is a global var, used by the check_form code in the payment class, once set by this helper function
 */
var submitter = null;
function gvSubmitFunction(gv,total) {
   if (gv >= total) {
     submitter = 1;
   }
}

/**
 * During checkout, this auto-clicks the radio-button next to a given payment module if the user 
 * clicks in any field associated with that module. 
 * This is to avoid leaving the user without a selected payment method even if they filled in data.
 */
function methodSelect(theMethod) {
  if (document.getElementById(theMethod)) {
    document.getElementById(theMethod).checked = 'checked';
  }
}

/**
 * force the btn_submit button to be disabled for 4 seconds after being clicked, while page is redrawn
 * This is to aid in preventing accidental double-submissions
 */
function submitonce()
{
  var button = document.getElementById("btn_submit");
  $("#btn_submit").replaceWith("<img id='btn_submit' src='includes/templates/template_default/images/processing.gif' border='0' alt='Processing' title='Processing'>");
  button.style.cursor="wait";
  button.disabled = true;
  //setTimeout('button_timeout()', 4000);
  return false;
}
function button_timeout() {
  var button = document.getElementById("btn_submit");
  button.style.cursor="pointer";
  button.disabled = false;
}

/* textarea counter for indicating number of remaining characters allowed to enter
 *  field		form field that is being counted
 *  count		form field that will show characters left
 *  maxchars 	maximum number of characters
 */
function characterCount(field, count, maxchars) {
  var realchars = field.value.replace(/\t|\r|\n|\r\n/g,'');
  var excesschars = realchars.length - maxchars;
  if (excesschars > 0) {
    field.value = field.value.substring(0, maxchars);
    alert("Error:\n\n- You are only allowed to enter up to"+maxchars+" characters.");
  } else {
    count.value = maxchars - realchars.length;
  }
}

/**
 * Concatenates fields used during checkout for expiration data entry when ajax is used to redraw the page
 */
function concatExpiresFields(fields) {
    return jQuery(":input[name=" + fields[0] + "]").val() + jQuery(":input[name=" + fields[1] + "]").val();
}

/**
 * Uses ajax to redraw part of the page in order to avoid transmitting collected card data unnecessarily
 */
function collectsCardDataOnsite(paymentValue)
{
  zcJS.ajax({
    url: "ajax.php?act=ajaxPayment&method=doesCollectsCardDataOnsite",
    data: {paymentValue: paymentValue}
  }).done(function( response ) {
  if (response.data === true) {
    var str = jQuery('form[name="checkout_payment"]').serializeArray();

    zcJS.ajax({
      url: "ajax.php?act=ajaxPayment&method=prepareConfirmation",
      data: str
    }).done(function( response ) {
      jQuery('#checkoutPayment').hide();
      jQuery('#navBreadCrumb').html(response.breadCrumbHtml);
      jQuery('#checkoutPayment').before(response.confirmationHtml);
      jQuery(document).attr('title', response.pageTitle);
    });
  } else {
    jQuery('form[name="checkout_payment"]')[0].submit();
  }
});
 return false;
}

