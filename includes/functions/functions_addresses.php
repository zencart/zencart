<?php
/**
 * Address functions
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.8 $
 */


/**
 * Returns an array with countries, suitable for pulldown
 */
function zen_get_countries_for_admin_pulldown($default_text = '')
{
    global $db;
    $countries_array = [];
    if ($default_text) {
        $countries_array[] = [
            'id' => '',
            'text' => $default_text,
            'status' => '',
        ];
    }
    $sql = "SELECT countries_id, countries_name, status
            FROM " . TABLE_COUNTRIES . "
            ORDER BY countries_name";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $countries_array[] = [
            'id' => $result['countries_id'],
            'text' => $result['countries_name'],
            'status' => $result['status'],
        ];
    }

    return $countries_array;
}


/**
 * Returns an array with countries
 *
 * @param int $country_id If set limits to a single country
 * @param bool $with_iso_codes whether to add the iso codes to the array
 * @param bool $activeOnly
 * @return array
 */
function zen_get_countries(int $country_id = 0, bool $with_iso_codes = false, bool $activeOnly = true)
{
    global $db;
    $countries_array = [];

    $sql = "SELECT countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, status
            FROM " . TABLE_COUNTRIES;

    if (!empty($country_id)) {
        $sql .= " WHERE countries_id = " . (int)$country_id;
        if ($activeOnly) $sql .= " AND status != 0 ";
    } else {
        if ($activeOnly) $sql .= " WHERE status != 0 ";
    }
    $sql .= " ORDER BY countries_name";
    $results = $db->Execute($sql);

    if (!empty($country_id)) {
        $countries_array['countries_name'] = '';

        if ($with_iso_codes == true) {
            $countries_array['countries_iso_code_2'] = '';
            $countries_array['countries_iso_code_3'] = '';
            if (!$results->EOF) {
                $countries_array = [
                    'countries_name' => $results->fields['countries_name'],
                    'countries_iso_code_2' => $results->fields['countries_iso_code_2'],
                    'countries_iso_code_3' => $results->fields['countries_iso_code_3']
                ];
            }
        } else if (!$results->EOF) {
            $countries_array = ['countries_name' => $results->fields['countries_name']];
        }
    } else {
        foreach ($results as $result) {
            $countries_array[] = [
                'countries_id' => $result['countries_id'],
                'countries_name' => $result['countries_name']
            ];
        }
    }

    return $countries_array;
}

/*
 *  Alias function to zen_get_countries()
 */
function zen_get_country_name($country_id, $activeOnly = true)
{
    $country_array = zen_get_countries($country_id, false, $activeOnly);
    return $country_array['countries_name'];
}


/**
 * Alias function to zen_get_countries, which also returns the countries iso codes
 *
 * @param int $country_id If set limits to a single country
 */
function zen_get_countries_with_iso_codes($country_id, $activeOnly = TRUE)
{
    return zen_get_countries($country_id, true, $activeOnly);
}


/**
 * returns a pulldown array with zones defined for the specified country
 * used by zen_prepare_country_zones_pull_down()
 *
 * @param int $country_id
 * @return array for pulldown
 */
function zen_get_country_zones($country_id)
{
    global $db;
    $zones_array = array();
    $zones = $db->Execute("SELECT zone_id, zone_name
                           FROM " . TABLE_ZONES . "
                           WHERE zone_country_id = " . (int)$country_id . "
                           ORDER BY zone_name");
    foreach ($zones as $zone) {
        $zones_array[] = array('id' => $zone['zone_id'], 'text' => $zone['zone_name']);
    }

    return $zones_array;
}

/**
 * Return the zone (State/Province) name
 * @param int $country_id
 * @param int $zone_id
 * @param string|null $default_zone
 * @return string
 */
function zen_get_zone_name(int $country_id, int $zone_id, ?string $default_zone = '')
{
    global $db;
    $sql = "SELECT zone_name
            FROM " . TABLE_ZONES . "
            WHERE zone_country_id = " . (int)$country_id . "
            AND zone_id = " . (int)$zone_id;

    $result = $db->Execute($sql);

    if ($result->RecordCount()) {
        return $result->fields['zone_name'];
    }
    return $default_zone;
}


/**
 * Returns the zone (State/Province) code
 * @param int $country_id
 * @param int $zone_id
 * @param string|null $default_zone
 * @return string
 */
function zen_get_zone_code(int $country_id, int $zone_id, ?string $default_zone = '')
{
    global $db;
    $sql = "SELECT zone_code
            FROM " . TABLE_ZONES . "
            WHERE zone_country_id = " . (int)$country_id . "
            AND zone_id = " . (int)$zone_id;

    $result = $db->Execute($sql);

    if ($result->RecordCount() > 0) {
        return $result->fields['zone_code'];
    }
    return $default_zone;
}

/**
 * Build an array for pulldown use, including padding for browser-specific constraints
 *
 * @TODO - rework to remove unnecessary code for Mozilla/IE concerns
 *
 * @param string $country_id
 * @return array
 */
function zen_prepare_country_zones_pull_down($country_id = '')
{
// preset the width of the drop-down for Netscape
    $pre = '';
    if ((!zen_browser_detect('MSIE')) && (zen_browser_detect('Mozilla/4'))) {
        for ($i = 0; $i < 45; $i++) $pre .= '&nbsp;';
    }

    $zones = zen_get_country_zones($country_id);

    if (count($zones) > 0) {
        $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
        $zones = array_merge($zones_select, $zones);
    } else {
        $zones = array(array('id' => '', 'text' => TYPE_BELOW));
// create dummy options for Netscape to preset the height of the drop-down
        if ((!zen_browser_detect('MSIE')) && (zen_browser_detect('Mozilla/4'))) {
            for ($i = 0; $i < 9; $i++) {
                $zones[] = array('id' => '', 'text' => $pre);
            }
        }
    }

    return $zones;
}


/**
 * Get array of address_format_ids, suitable for a dropdown
 */
function zen_get_address_formats()
{
    global $db;
    $sql = "SELECT address_format_id
            FROM " . TABLE_ADDRESS_FORMAT . "
            ORDER BY address_format_id";
    $results = $db->Execute($sql);

    $address_format_array = [];
    foreach ($results as $result) {
        $address_format_array[] = [
            'id' => $result['address_format_id'],
            'text' => $result['address_format_id']
        ];
    }
    return $address_format_array;
}


