<?php
/**
 * jscript_main
 *
 * @package page
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   Modified in v1.5.4 $
 */
?>
<script language="javascript" type="text/javascript"><!--
var selected;
var submitter = null;

function concatExpiresFields(fields) {
    return $(":input[name=" + fields[0] + "]").val() + $(":input[name=" + fields[1] + "]").val();
}


function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150')
}
function couponpopupWindow(url) {
  window.open(url,'couponpopupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150')
}
function submitFunction($gv,$total) {
  if ($gv >=$total) {
    submitter = 1;
  }
}

function methodSelect(theMethod) {
  if (document.getElementById(theMethod)) {
    document.getElementById(theMethod).checked = 'checked';
  }
}
function collectsCardDataOnsite(paymentValue)
{
 zcJS.ajax({
  url: "ajax.php?act=ajaxPayment&method=doesCollectsCardDataOnsite",
  data: {paymentValue: paymentValue}
}).done(function( response ) {
  if (response.data == true) {
   var str = $('form[name="checkout_payment"]').serializeArray();

   zcJS.ajax({
    url: "ajax.php?act=ajaxPayment&method=prepareConfirmation",
    data: str
  }).done(function( response ) {
   $('#checkoutPayment').hide();
   $('#navBreadCrumb').html(response.breadCrumbHtml);
   $('#checkoutPayment').before(response.confirmationHtml);
   $(document).attr('title', response.pageTitle);

   });
  } else {
   $('form[name="checkout_payment"]')[0].submit();
}
});
return false;
}

    $(document).ready(function(){
      $('form[name="checkout_payment"]').submit(function() {
          $('.paymentSubmit').attr('disabled', true);
        <?php if ($flagOnSubmit) { ?>
          formPassed = check_form();
          if (formPassed == false) {
              $('.paymentSubmit').attr('disabled', false);
          }
          return formPassed;
        <?php } ?>
      });
    });

//--></script>
