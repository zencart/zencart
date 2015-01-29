/**
 * jscript_main
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: jscript_main.js  Modified in v1.6.0 $
 */

var submitter = null;

function submitFunction($gv,$total) {
   if ($gv >=$total) {
     submitter = 1;
   }
}

function submitonce()
{
  var button = document.getElementById("btn_submit");
  button.style.cursor="wait";
  button.disabled = true;
  setTimeout('button_timeout()', 4000);
  return false;
}
function button_timeout() {
  var button = document.getElementById("btn_submit");
  button.style.cursor="pointer";
  button.disabled = false;
}



function popupWindow(url) {
	window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150');
}

function couponpopupWindow(url) {
	window.open(url,'couponpopupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150');
}