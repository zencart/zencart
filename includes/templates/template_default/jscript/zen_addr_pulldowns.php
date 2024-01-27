<?php
/**
 * zen_addr_pulldowns
 *
 * handles pulldown menu dependencies for state/country selection; required by various
 * pages' jscript_addr_pulldowns.php.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Dec 08 Modified in v2.0.0-alpha1 $
 */
// -----
// If the site does NOT require a 'State' entry in an address, nothing to be done here!
//
if (ACCOUNT_STATE !== 'true') {
    return;
}

// -----
// If the current site is using the state dropdowns, create the array that identifies the various
// zones for the currently-active countries.  When state dropdowns aren't being used, the
// empty $c2z array will 'instruct' the jQuery section to presume that no countries have
// associated zones.
//
$c2z = [];
if (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN === 'true') {
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
    foreach ($countries as $next_country) {
        $current_country_id = $next_country['zone_country_id'];
        $c2z[$current_country_id] = [];

        $states = zen_get_country_zones($current_country_id);
        foreach ($states as $next_state) {
            $c2z[$current_country_id][$next_state['id']] = $next_state['text'];
        }
    }
}
?>
<script>
jQuery(document).ready(function() {
    const country_zones = '<?php echo addslashes(json_encode($c2z)); ?>';
<?php
// -----
// Notes:
//
// 1. The '#stBreak' <br> is also never needed/wanted since it will 'throw' the state-field input underneath
// the state/zone label.
//
// 2. If the '#stateLabel' label is empty, hide it!  It will be when the site uses dropdown states and on
// the 'shipping_estimator' page.
//
// 3. Initialize the display for the dropdown vs. hand-entry of the state fields.  If the initially-selected
// country doesn't have zones, the dropdown will contain only 1 element ('Type a choice below ...').  In that
// case, the dropdown and associated elements will be hidden and the hand-input 'state' field will be shown.
//
// 4. There can be unwanted whitespace, e.g. an &nbsp; prior to the (optional) <span class="alert"> following
// the 'stateZone' dropdown.  In that case, when the <span> is hidden for unzoned countries, the state input
// field is slightly offset from the other input fields.
//
?>
    jQuery('#stBreak').hide();
    if (jQuery('#stateLabel').text().length === 0) {
        jQuery('#stateLabel').hide();
    }
    if (jQuery('#stateZone > option').length > 1) {
        jQuery('#state').hide();
        jQuery('#stateZone').show();
        jQuery('#stateZone').next('span.alert').show();
    } else {
        jQuery('#state').show();
        jQuery('#stateZone').hide();
        jQuery('#stateZone').next('span.alert').hide();
    }
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
        var countryZones = '';
        var selected_country = jQuery('#country option:selected').val();
        jQuery.each(JSON.parse(country_zones), function(country_id, country_zones) {
            if (selected_country === country_id) {
                countryHasZones = true;
                jQuery.each(country_zones, function(zone_id, zone_name) {
                    countryZones += '<option label ="' + zone_name + '" value="' + zone_id + '">' + zone_name + '<' + '/option>';
                });
            }
        });
        if (countryHasZones) {
            var split = countryZones.split('<option').filter(function(el) {return el.length != 0});
            var sorted = split.sort();
            countryZones = '<option selected="selected" value="0"><?php echo addslashes(PLEASE_SELECT); ?><' + '/option><option' + sorted.join('<option');
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