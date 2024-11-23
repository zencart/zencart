<?php
/**
 * Address functions
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 13 Modified in v2.1.0 $
 */

/**
 * Returns an array with countries, suitable for pulldown
 * @param string $pre_populated_entry
 * @return array
 */
function zen_get_countries_for_admin_pulldown($pre_populated_entry = '')
{
    global $db;
    $countries_array = [];
    if (!empty($pre_populated_entry)) {
        $countries_array[] = [
            'id' => '',
            'text' => $pre_populated_entry,
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
    $country_array = zen_get_countries((int)$country_id, false, $activeOnly);
    return $country_array['countries_name'] ?? '';
}


/**
 * Alias function to zen_get_countries, which also returns country iso codes
 *
 * @param int $country_id If set limits to a single country
 */
function zen_get_countries_with_iso_codes($country_id, $activeOnly = TRUE)
{
    return zen_get_countries((int)$country_id, true, $activeOnly);
}


/**
 * returns a pulldown array with zones defined for the specified country
 * used by zen_prepare_country_zones_pull_down()
 *
 * @param int|string $country_id
 * @return array for pulldown
 */
function zen_get_country_zones(int|string $country_id): array
{
    global $db;
    $zones_array = array();
    $zones = $db->Execute("SELECT zone_id, zone_name, zone_code
                           FROM " . TABLE_ZONES . "
                           WHERE zone_country_id = " . (int)$country_id . "
                           ORDER BY zone_name");
    foreach ($zones as $zone) {
        $zones_array[] = [
            'id' => $zone['zone_id'],
            'text' => $zone['zone_name'],
            'zone_code' => $zone['zone_code'],
            ];
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
 * Build an array of country zones for pulldown use
 *
 * @param int|string|null $country_id
 * @return array
 */
function zen_prepare_country_zones_pull_down(int|string|null $country_id = 0): array
{
    $zones = zen_get_country_zones($country_id ?? 0);

    if (count($zones) > 0) {
        $zones_select = [['id' => '', 'text' => PLEASE_SELECT]];
        $zones = array_merge($zones_select, $zones);
    } else {
        $zones = [['id' => '', 'text' => TYPE_BELOW]];
    }

    return $zones;
}

/**
 * Get array of address_format_ids, suitable for a dropdown
 */
function zen_get_address_formats(): array
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


/**
 * Returns the address_format_id for the given country_id
 * @param int|null $country_id
 * @return int
 */
function zen_get_address_format_id(?int $country_id)
{
    global $db;
    $sql = "SELECT address_format_id as format_id
            FROM " . TABLE_COUNTRIES . "
            WHERE countries_id = " . (int)$country_id;

    $result = $db->Execute($sql, 1);

    if ($result->RecordCount() > 0) {
        return (int)$result->fields['format_id'];
    }
    return 1;
}

/**
 * Return a formatted address, based on specified formatting pattern id
 * @param int $address_format_id id of format pattern to use
 * @param array $incoming address data
 * @param bool $html format using html
 * @param string $boln begin-of-line prefix
 * @param string $eoln end-of-line suffix
 * @return mixed|string|string[]
 */
function zen_address_format($address_format_id = 1, $incoming = array(), $html = false, $boln = '', $eoln = "\n")
{
    global $db, $zco_notifier;
    $address = array();
    $address['hr'] = $html ? '<hr>' : '----------------------------------------';
    $address['cr'] = $html ? ($boln == '' && $eoln == "\n" ? '<br>' : $eoln . $boln) : $eoln;

    if (ACCOUNT_SUBURB !== 'true') $incoming['suburb'] = '';
    $address['company'] = !empty($incoming['company']) ? zen_output_string_protected($incoming['company']) : '';
    $address['firstname'] = !empty($incoming['firstname']) ? zen_output_string_protected($incoming['firstname']) : (!empty($incoming['name']) ? zen_output_string_protected($incoming['name']) : '');
    $address['lastname'] = !empty($incoming['lastname']) ? zen_output_string_protected($incoming['lastname']) : '';
    $address['street'] = !empty($incoming['street_address']) ? zen_output_string_protected($incoming['street_address']) : '';
    $address['suburb'] = !empty($incoming['suburb']) ? zen_output_string_protected($incoming['suburb']) : '';
    $address['city'] = !empty($incoming['city']) ? zen_output_string_protected($incoming['city']) : '';
    $address['state'] = !empty($incoming['state']) ? zen_output_string_protected($incoming['state']) : '';
    $address['postcode'] = !empty($incoming['postcode']) ? zen_output_string_protected($incoming['postcode']) : '';
    $address['zip'] = $address['postcode'];

    $address['streets'] = !empty($address['suburb']) ? $address['street'] . $address['cr'] . $address['suburb'] : $address['street'];
    $address['statecomma'] = !empty($address['state']) ? $address['state'] . ', ' : '';

    $country = '';
    if (!empty($incoming['country_id'])) {
        $country = zen_get_country_name($incoming['country_id']);
        if (!empty($incoming['zone_id'])) {
            $address['state'] = zen_get_zone_code($incoming['country_id'], $incoming['zone_id'], $address['state']);
        }
    } elseif (!empty($incoming['country'])) {
        if (is_array($incoming['country'])) {
            $country = zen_output_string_protected($incoming['country']['countries_name'] ?? $incoming['country']['title']);
        } else {
            $country = zen_output_string_protected($incoming['country']);
        }
    }
    $address['country'] = $country;

    // add uppercase variants for backward compatibility
    $address['HR'] = $address['hr'];
    $address['CR'] = $address['cr'];

    $sql    = "select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = " . (int)$address_format_id;
    $result = $db->Execute($sql);
    $fmt    = (!$result->EOF ? $result->fields['format'] : '');

    // sort to put longer keys at the top of the array so that longer variants are replaced before shorter ones
    $tmp = array_map('strlen', array_keys($address));
    array_multisort($tmp, SORT_DESC, $address);

    // store translated values into original array, just for the sake of the notifier
    $incoming = array_merge($incoming, $address);

    // convert into $-prefixed keys
    foreach ($address as $key => $value) {
        $address['$' . $key] = $value;
        unset($address[$key]);
    }

    // do the substitutions
    $address_out = str_replace(array_keys($address), array_values($address), $fmt);

    if (ACCOUNT_COMPANY == 'true' && !empty($address['$company']) && false === strpos($fmt, '$company')) {
        $address_out = $address['$company'] . $address['$cr'] . $address_out;
    }
    if (ACCOUNT_SUBURB !== 'true') $address['suburb'] = '';

    // -----
    // "Package up" the various elements of an address and issue a notification that will enable
    // an observer to make modifications if needed.
    //
    $zco_notifier->notify(
        'NOTIFY_END_ZEN_ADDRESS_FORMAT',
        [
            'format' => $fmt,
            'address' => $incoming,
            'firstname' => $address['$firstname'],
            'lastname' => $address['$lastname'],
            'street' => $address['$street'],
            'suburb' => $address['$suburb'],
            'city' => $address['$city'],
            'state' => $address['$state'],
            'country' => $address['$country'],
            'postcode' => $address['$postcode'],
            'company' => $address['$company'],
            'streets' => $address['$streets'],
            'statecomma' => $address['$statecomma'],
            'zip' => $address['$zip'],
            'cr' => $address['$cr'],
            'hr' => $address['$hr'],
        ],
        $address_out
    );

    return $address_out;
}

/**
 * Return a formatted address, based on customer's address's country format
 * @param int $customers_id
 * @param int $address_id
 * @param bool $html format using html
 * @param string $boln begin-of-line prefix
 * @param string $eoln end-of-line suffix
 * @return mixed|string|string[]
 */
function zen_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n")
{
    global $db, $zco_notifier;
    $sql = "SELECT entry_firstname AS firstname, entry_lastname AS lastname,
                   entry_company AS company, entry_street_address AS street_address,
                   entry_suburb AS suburb, entry_city AS city, entry_postcode AS postcode,
                   entry_state AS state, entry_zone_id AS zone_id,
                   entry_country_id AS country_id
            FROM " . TABLE_ADDRESS_BOOK . "
            WHERE customers_id = " . (int)$customers_id . "
            AND address_book_id = " . (int)$address_id;

    $address = $db->Execute($sql);

    $zco_notifier->notify('NOTIFY_ZEN_ADDRESS_LABEL', null, $customers_id, $address_id, $address->fields);

    $format_id = zen_get_address_format_id((int)$address->fields['country_id']);

    return zen_address_format($format_id, $address->fields, $html, $boln, $eoln);
}


