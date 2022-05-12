<?php
/**
 * zen_addr_pulldowns
 *
 * handles pulldown menu dependencies for state/country selection; required by various
 * pages' jscript_addr_pulldowns.php.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Jun 03 Modified in v1.5.7 $
 */
// -----
// If the site does NOT require a 'State' entry in an address or has NOT enabled states to
// be displayed as dropdowns, nothing to be done here!
//
if (ACCOUNT_STATE !== 'true' || ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN !== 'true') {
    return;
}

// -----
// If the current site has at least one country enabled that uses zones, a JSON-encoded array of
// countries-to-zones will be created for use by the jQuery section.
//
$countries = $db->Execute(
    "SELECT DISTINCT zone_country_id
       FROM " . TABLE_ZONES . "
            INNER JOIN " . TABLE_COUNTRIES . "
                ON countries_id = zone_country_id
               AND status = 1
   ORDER BY zone_country_id"
);

$c2z = [];
foreach ($countries as $next_country) {
    $current_country_id = $next_country['zone_country_id'];
    $c2z[$current_country_id] = [];

    $states = $db->Execute(
        "SELECT zone_name, zone_id
           FROM " . TABLE_ZONES . "
          WHERE zone_country_id = $current_country_id
       ORDER BY zone_name"
    );
    foreach ($states as $next_state) {
        $c2z[$current_country_id][$next_state['zone_id']] = $next_state['zone_name'];
    }
}

// -----
// If none of the currently-enabled countries use zones, nothing to do here.
//
if (count($c2z) === 0) {
    return;
}
?>
<script>
jQuery(document).ready(function() {
<?php
    echo '    var country_zones = \'' . addslashes(json_encode($c2z)) . '\';' . PHP_EOL;

// -----
// Notes:
//
// 1. When the site is set to display states as dropdowns, it's **assumed** that the '#stateLabel' label's
// text has been set to an empty string, thus unneeded.  Since this processing is **only** invoked when
// dropdown states are enabled, start by hiding that label.  The '#stBreak' <br> is also never needed/wanted
// since it will 'throw' the state-field input underneath the state/zone label.
//
// 2. Initialize the display for the dropdown vs. hand-entry of the state fields.  If the initially-selected
// country doesn't have zones, the dropdown will contain only 1 element ('Type a choice below ...').
//
// 3. There can be unwanted whitespace, e.g. an &nbsp; prior to the (optional) <span class="alert"> following
// the 'stateZone' dropdown.  In that case, when the <span> is hidden for unzoned countries, the state input
// field is slightly offset from the other input fields.
//
?>
    jQuery('#stateLabel, #stBreak').hide();
    if (jQuery('#stateZone > option').length > 1) {
        jQuery('#state').hide();
        jQuery('#stateZone').show();
        jQuery('#stateZone').next('span.alert').show();
    } else {
        jQuery('#state').show();
        jQuery('#stateZone').hide();
        jQuery('#stateZone').next('span.alert').hide();
    }

    var pleaseSelect = '<?php echo PLEASE_SELECT; ?>';
<?php
    // -----
    // This function provides the processing needed when a country has been changed.  It makes
    // use of the country_zones (countries-to-zones) array, built above.  Normally invoked
    // by the template's 'onchange=update_form(this.form)' parameter applied to the countries'
    // dropdown.
    //
?>
    update_zone = function(theForm)
    {
        var countryHasZones = false;
        var countryZones = '<option selected="selected" value="0">' + pleaseSelect + '</option>';
        var selected_country = jQuery('#country option:selected').val();
        jQuery.each(jQuery.parseJSON(country_zones), function(country_id, country_zones) {
            if (selected_country == country_id) {
                countryHasZones = true;
                jQuery.each(country_zones, function(zone_id, zone_name) {
                    countryZones += "<option value='" + zone_id + "'>" + zone_name + "</option>";
                });
            }
        });
        if (countryHasZones) {
            jQuery('#state').hide();
            jQuery('#stateZone').html(countryZones);
            jQuery('#stateZone').show();
            jQuery('#stateZone').next('span.alert').show();
        } else {
            jQuery('#state').show();
            jQuery('#stateZone').hide();
            jQuery('#stateZone').next('span.alert').hide();
        }
    }
});
</script>