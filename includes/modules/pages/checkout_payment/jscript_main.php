<?php
/**
 * jscript_main
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 May 29 Modified in v1.5.8-alpha $
 */
?>
<script>
var selected;
var submitter = null;
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
function tAndCChange() {
// function to disable continue if T&C present and not checked.
  var conditionsElement = document.getElementById('conditions');
  var paymentSubmitInputs = document.getElementById('paymentSubmit').getElementsByTagName('input'), i=0, e;
  if(typeof(conditionsElement) != 'undefined' && conditionsElement != null){
    while(e=paymentSubmitInputs[i++]){
      e.disabled = ! conditionsElement.checked;
    }
  }
}
// Add listeners to initially disable the continue button and to reset when T&C changed
document.addEventListener('DOMContentLoaded',  function () {
  var conditionsElement = document.getElementById("conditions");
  if(typeof(conditionsElement) != 'undefined' && conditionsElement != null){
    conditionsElement.addEventListener('change', tAndCChange);
    tAndCChange();
  }
});

</script>
