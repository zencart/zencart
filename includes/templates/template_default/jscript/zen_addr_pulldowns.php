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
// If the site has NOT enabled states to be displayed as dropdowns, nothing to be done here!
//
if (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN !== 'true') {
    return;
}
?>
<script>
jQuery(document).ready(function() {
<?php
// -----
// Create a JSON-encoded array of countries-to-zones for use by the jQuery section.
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

if (count($c2z) !== 0) {
    echo '    var country_zones = \'' . addslashes(json_encode($c2z)) . '\';' . PHP_EOL;
}

// -----
// Initialize the display for the dropdown vs. hand-entry of the state fields.  If the initially-selected
// country doesn't have zones, the dropdown will contain only 1 element ('Type a choice below ...').
//
?>
    if (jQuery('#stateZone > option').length == 1) {
        jQuery('#stateZone').hide();
        jQuery('#state, #stBreak, #stateLabel').show();
    } else {
        jQuery('#state, #stBreak, #stateLabel').hide();
        jQuery('#stateZone').show();
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
            jQuery('#state, #stBreak, #stateLabel').hide();
            jQuery('#stateZone').html(countryZones);
            jQuery('#stateZone').show();
        } else {
            jQuery('#stateZone').hide();
            jQuery('#state, #stBreak, #stateLabel').show();
        }
    }
<?php
    // -----
    // For the initial update of the form (some pages excluded), locate the name of the form containing the #state input
    // and update the associated zones.
    //
    // Note: Excluding the on-load update for selected pages, where the just-rendered form
    // already has selected the default/selected zone from the dropdown.  Also giving a watching
    // observer the opportunity to add site-specific pages to the exclusion.
    //
    $no_zone_update_pages = [
        FILENAME_ADDRESS_BOOK_PROCESS,
    ];
    $zco_notifier->notify('NOTIFY_ZEN_ADDR_PULLDOWNS_EXCLUDE_PAGE', $current_page_base, $no_zone_update_pages);

    if (!in_array($current_page_base, $no_zone_update_pages)) {
?>
    update_zone(jQuery('#state').closest('form').attr('name'));
<?php
    }
?>
});
</script>