<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 20 New in v2.1.0-beta1 $
 */
// -----
// If the site does NOT require a 'State' entry in an address, nothing to be done here!
//
if (ACCOUNT_STATE !== 'true') {
    return;
}

// -----
// The customer's address entry **always** uses the state dropdown, regardless
// of configuration -- unlike the storefront.
//
$c2z = [];

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

// -----
// If no active countries have zones, nothing to be done.
//
if (count($c2z) === 0) {
    return;
}
?>
<script>
$(function() {
    const country_zones = '<?php echo addslashes(json_encode($c2z)); ?>';

    if ($('#entry_zone_id > option').length > 1) {
        $('#entry_state').hide();
        $('#entry_zone_id').prop('disabled', false).show();
    } else {
        $('#entry_state').show();
        $('#entry_zone_id').prop('disabled', true).hide();
    }
<?php
    // -----
    // This function provides the processing needed when a country has been changed.  It makes
    // use of the country_zones (countries-to-zones) array, built above.
    //
?>
    update_zone = function()
    {
        let countryHasZones = false;
        let countryZones = '';
        let selected_country = $('#entry_country_id option:selected').val();
        $.each(JSON.parse(country_zones), function(country_id, country_zones) {
            if (selected_country === country_id) {
                countryHasZones = true;
                $.each(country_zones, function(zone_id, zone_name) {
                    countryZones += '<option label ="' + zone_name + '" value="' + zone_id + '">' + zone_name + '<' + '/option>';
                });
            }
        });
        if (countryHasZones) {
            let split = countryZones.split('<option').filter(function(el) {
                return el.length != 0
            });

            let sorted = split.sort();
            countryZones = '<option selected="selected" value="0"><?php echo addslashes(PLEASE_SELECT); ?><' + '/option><option' + sorted.join('<option');
            $('#entry_state').hide();
            $('#entry_zone_id').html(countryZones).prop('disabled', false).show();
        } else {
            $('#entry_state').show();
            $('#entry_zone_id').prop('disabled', true).hide();
        }
    }

    $('#entry_country_id').on('change', function() {
        update_zone();
    });
});
</script>
