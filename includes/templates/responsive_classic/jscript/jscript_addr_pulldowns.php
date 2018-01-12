<?php
/**
 * jscript_addr_pulldowns
 *
 * handles pulldown menu dependencies for state/country selection
 *
 * @TODO: Convert to ajax
 *
 * @package page
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: jscript_addr_pulldowns.php 4830 2006-10-24 21:58:27Z drbyte $
 */
?>
<script>
/**
 * Hides "State/Province" input field components -- fires when a given country has no state/province choices to select from
 */
function hideStateField(theForm) {
  theForm.state.disabled = true;
  theForm.state.className = 'hiddenField';
  theForm.state.setAttribute('className', 'hiddenField');
  if (document.getElementById("stateLabel")) {
    document.getElementById("stateLabel").className = 'hiddenField';
    document.getElementById("stateLabel").setAttribute('className', 'hiddenField');
  }
  if (document.getElementById("stText")) {
    document.getElementById("stText").className = 'hiddenField';
    document.getElementById("stText").setAttribute('className', 'hiddenField');
  }
  if (document.getElementById("stBreak")) {
    document.getElementById("stBreak").className = 'hiddenField';
    document.getElementById("stBreak").setAttribute('className', 'hiddenField');
  }
}
/**
 * Shows "State/Province" input field components -- fires when a given country has state/province choices to select from
 */
function showStateField(theForm) {
  theForm.state.disabled = false;
  theForm.state.className = 'inputLabel visibleField';
  theForm.state.setAttribute('className', 'visibleField');
  if (document.getElementById("stateLabel")) {
    document.getElementById("stateLabel").className = 'inputLabel visibleField';
    document.getElementById("stateLabel").setAttribute('className', 'inputLabel visibleField');
  }
  if (document.getElementById("stText")) {
    document.getElementById("stText").className = 'alert visibleField';
    document.getElementById("stText").setAttribute('className', 'alert visibleField');
  }
  if (document.getElementById("stBreak")) {
    document.getElementById("stBreak").className = 'clearBoth visibleField';
    document.getElementById("stBreak").setAttribute('className', 'clearBoth visibleField');
  }
}

/**
 * updates state/zone fields based on selected country
 */
function update_zone(theForm) {
  // if there is no zone_id field to update, or if it is hidden from display, then exit performing no updates
  if (!theForm || !theForm.elements["zone_id"]) return;
  if (theForm.zone_id.type == "hidden") return;

  // set initial values
  var SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;
  var SelectedZone = theForm.elements["zone_id"].value;

  // reset the array of pulldown options so it can be repopulated
  var NumState = theForm.zone_id.options.length;
  while(NumState > 0) {
    NumState = NumState - 1;
    theForm.zone_id.options[NumState] = null;
  }
  // build dynamic list of countries/zones for pulldown
<?php echo zen_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

  // if we had a value before reset, set it again
  if (SelectedZone != "") theForm.elements["zone_id"].value = SelectedZone;

}


update_zone(document.addressbook);
update_zone(document.checkout_address);
update_zone(document.create_account);
update_zone(document.no_account);


</script>
