<?php
/**
 * functions_taxes
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

/**
 * Returns the tax rate for a zone / class
 * @param int $class_id
 * @param int $country_id
 * @param int $zone_id
 * @return float|int
 */
function zen_get_tax_rate($class_id, $country_id = -1, $zone_id = -1)
{
    global $db, $zco_notifier;

    // -----
    // If the current customer is tax-exempt, unconditionally return a tax-rate of 0.
    //
    if (Customer::isTaxExempt() === true) {
        return 0;
    }

    // Give an observer a chance to override this function's return.
    $tax_rate = false;
    $zco_notifier->notify(
        'NOTIFY_ZEN_GET_TAX_RATE_OVERRIDE',
        [
            'class_id' => $class_id,
            'country_id' => $country_id,
            'zone_id' => $zone_id
        ],
        $tax_rate
    );
    if ($tax_rate !== false) {
        return $tax_rate;
    }

    if ($country_id == -1 && $zone_id == -1) {
        if (zen_is_logged_in()) {
            $country_id = $_SESSION['customer_country_id'];
            $zone_id = $_SESSION['customer_zone_id'];
        } else {
            $country_id = STORE_COUNTRY;
            $zone_id = STORE_ZONE;
        }
    }

    if (STORE_PRODUCT_TAX_BASIS == 'Store') {
        if ($zone_id != STORE_ZONE) return 0;
    }

    $tax_query = "SELECT sum(tax_rate) AS tax_rate
                  FROM " . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
                  WHERE (za.zone_country_id IS null
                        OR za.zone_country_id = 0
                        OR za.zone_country_id = " . (int)$country_id . ")
                  AND (za.zone_id IS null
                        OR za.zone_id = 0
                        OR za.zone_id = " . (int)$zone_id . ")
                  AND tr.tax_class_id = " . (int)$class_id . "
                  GROUP BY tr.tax_priority";

    $tax = $db->Execute($tax_query);

    if ($tax->RecordCount() > 0) {
        $tax_multiplier = 1.0;
        foreach ($tax as $rate) {
            $tax_multiplier *= 1.0 + ($rate['tax_rate'] / 100);
        }

        return ($tax_multiplier - 1.0) * 100;
    }

    return 0;
}

/**
 * Return the tax description for a zone / class
 * @param int $class_id
 * @param int $country_id
 * @param int $zone_id
 * @return false|string
 */
function zen_get_tax_description($class_id, $country_id = -1, $zone_id = -1)
{
    global $db, $zco_notifier;

    // Give an observer the chance to override this function's return.
    $tax_description = '';
    $zco_notifier->notify(
        'NOTIFY_ZEN_GET_TAX_DESCRIPTION_OVERRIDE',
        [
            'class_id' => $class_id,
            'country_id' => $country_id,
            'zone_id' => $zone_id
        ],
        $tax_description
    );
    if ($tax_description != '') {
        return $tax_description;
    }

    if ($country_id == -1 && $zone_id == -1) {
        if (zen_is_logged_in()) {
            $country_id = $_SESSION['customer_country_id'];
            $zone_id = $_SESSION['customer_zone_id'];
        } else {
            $country_id = STORE_COUNTRY;
            $zone_id = STORE_ZONE;
        }
    }

    $tax_query = "SELECT tax_description
                  FROM " . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
                  WHERE (za.zone_country_id IS null OR za.zone_country_id = 0
                        OR za.zone_country_id = " . (int)$country_id . ")
                  AND (za.zone_id IS null
                        OR za.zone_id = 0
                        OR za.zone_id = " . (int)$zone_id . ")
                  AND tr.tax_class_id = " . (int)$class_id . "
                  ORDER BY tr.tax_priority";

    $tax = $db->Execute($tax_query);

    if ($tax->RecordCount() > 0) {
        $tax_description = '';
        foreach ($tax as $rate) {
            $tax_description .= $rate['tax_description'] . ' + ';
        }
        $tax_description = substr($tax_description, 0, -3);

        return $tax_description;
    }

    return TEXT_UNKNOWN_TAX_RATE;
}

/**
 * Return the tax rates for each defined tax for the given class and zone
 * @param int $class_id
 * @param int $country_id
 * @param int $zone_id
 * @param array $tax_description
 * @return array (description => tax_rate)
 */
function zen_get_multiple_tax_rates($class_id, $country_id = -1, $zone_id = -1, $tax_description = [])
{
    global $db, $zco_notifier;

    // Give an observer the chance to override this function's return.
    // It is *intended* to be an empty string; this is not a bug.
    $rates_array = '';
    $zco_notifier->notify(
        'NOTIFY_ZEN_GET_MULTIPLE_TAX_RATES_OVERRIDE',
        [
            'class_id' => $class_id,
            'country_id' => $country_id,
            'zone_id' => $zone_id,
            'tax_description' => $tax_description
        ],
        $rates_array
    );
    if (is_array($rates_array)) {
        return $rates_array;
    }

    $rates_array = [];

    if ($country_id == -1 && $zone_id == -1) {
        if (zen_is_logged_in()) {
            $country_id = $_SESSION['customer_country_id'];
            $zone_id = $_SESSION['customer_zone_id'];
        } else {
            $country_id = STORE_COUNTRY;
            $zone_id = STORE_ZONE;
        }
    }

    $tax_query = "SELECT tax_description, tax_rate, tax_priority
                  FROM " . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
                  WHERE (za.zone_country_id IS null OR za.zone_country_id = 0
                    OR za.zone_country_id = " . (int)$country_id . ")
                  AND (za.zone_id IS null
                    OR za.zone_id = 0
                    OR za.zone_id = " . (int)$zone_id . ")
                  AND tr.tax_class_id = " . (int)$class_id . "
                  ORDER BY tr.tax_priority";
    $results = $db->Execute($tax_query);

    // calculate appropriate tax rate respecting priorities and compounding
    if ($results->RecordCount() > 0) {
        $tax_aggregate_rate = 1;
        $tax_rate_factor = 1;
        $tax_prior_rate = 1;
        $tax_priority = 0;
        foreach ($results as $tax) {
            if ((int)$tax['tax_priority'] > $tax_priority) {
                $tax_priority = $tax['tax_priority'];
                $tax_prior_rate = $tax_aggregate_rate;
                $tax_rate_factor = 1 + ($tax['tax_rate'] / 100);
                $tax_rate_factor *= $tax_aggregate_rate;
                $tax_aggregate_rate = 1;
            } else {
                $tax_rate_factor = $tax_prior_rate * (1 + ($tax['tax_rate'] / 100));
            }
            $rates_array[$tax['tax_description']] = 100 * ($tax_rate_factor - $tax_prior_rate);
            $tax_aggregate_rate += $tax_rate_factor - 1;
        }
    } else {
        // no tax at this level, set rate to 0 and description of unknown
        $rates_array[TEXT_UNKNOWN_TAX_RATE] = 0;
    }
    return $rates_array;
}

/**
 * Add tax to a product's price
 * based on whether we are displaying tax "in" the price
 * @param float|int $price
 * @param float|int $tax_percentage
 * @param bool $force
 * @return float|int
 */
function zen_add_tax($price, $tax_percentage = 0, $force = false)
{
    if (IS_ADMIN_FLAG) {
        if ((DISPLAY_PRICE_WITH_TAX_ADMIN == 'true' && $tax_percentage > 0) || $force) {
            return $price + zen_calculate_tax($price, $tax_percentage);
        }
        return $price;
    }

    if ((DISPLAY_PRICE_WITH_TAX == 'true' && $tax_percentage > 0)) {
        return $price + zen_calculate_tax($price, $tax_percentage);
    }
    return $price;
}

/**
 * Calculates Tax
 * @param float|int $price
 * @param float|int $tax_percentage
 * @return float|int
 */
function zen_calculate_tax($price, $tax_percentage = 1)
{
    return $price * $tax_percentage / 100;
}

/**
 * Output the tax percentage with optional padded decimals
 * @param float $value
 * @param int $padding
 * @return float|string
 */
function zen_display_tax_value($value, $padding = TAX_DECIMAL_PLACES)
{
    if (strpos($value, '.')) {
        $loop = true;
        while ($loop) {
            if (substr($value, -1) == '0') {
                $value = substr($value, 0, -1);
            } else {
                $loop = false;
                if (substr($value, -1) == '.') {
                    $value = substr($value, 0, -1);
                }
            }
        }
    }

    if ($padding > 0) {
        if ($decimal_pos = strpos($value, '.')) {
            $decimals = strlen(substr($value, ($decimal_pos + 1)));
            for ($i = $decimals; $i < $padding; $i++) {
                $value .= '0';
            }
        } else {
            $value .= '.';
            for ($i = 0; $i < $padding; $i++) {
                $value .= '0';
            }
        }
    }

    return $value;
}

/**
 * Get tax rate from tax description
 * @param string $tax_desc
 * @return float
 */
function zen_get_tax_rate_from_desc(string $tax_desc)
{
    global $db;
    $tax_rate = 0.00;

    $tax_descriptions = explode(' + ', $tax_desc);
    foreach ($tax_descriptions as $tax_description) {
        $sql = "SELECT tax_rate
                FROM " . TABLE_TAX_RATES . "
                WHERE tax_description = :taxDescLookup";
        $sql = $db->bindVars($sql, ':taxDescLookup', $tax_description, 'string');

        $result = $db->Execute($sql);

        if (!$result->EOF) {
            $tax_rate += $result->fields['tax_rate'];
        }
    }

    return $tax_rate;
}


/**
 * @param int $tax_class_id
 * @return string
 */
function zen_get_tax_class_title($tax_class_id = 0)
{
    global $db;
    if (empty($tax_class_id)) {
        return TEXT_NONE;
    }

    $sql = "SELECT tax_class_title
            FROM " . TABLE_TAX_CLASS . "
            WHERE tax_class_id = " . (int)$tax_class_id;
    $result = $db->Execute($sql);
    if ($result->EOF) return '';
    return $result->fields['tax_class_title'];
}

function zen_get_tax_locations($store_country = -1, $store_zone = -1)
{
    global $db, $zco_notifier;

    // Give an observer the chance to modify the function's output.
    $tax_address = false;
    $zco_notifier->notify(
        'ZEN_GET_TAX_LOCATIONS',
        [
            'country' => $store_country,
            'zone' => $store_zone
        ],
        $tax_address
    );
    if (is_array($tax_address)) {
        return $tax_address;
    }

    $tax_address = [];
    // PapPal express processing
    // If we're just starting the checkout process via the PPEC button, there's
    // no customer or shipping-address currently defined.  Use the store values for tax calculation.
    if (!zen_is_logged_in())  {
        $tax_address['zone_id'] = (int)STORE_ZONE;
        $tax_address['country_id'] = (int)STORE_COUNTRY;
        return $tax_address;
    }
    switch (STORE_PRODUCT_TAX_BASIS) {
        case 'Shipping':
            $tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id
                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                  LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                  WHERE ab.customers_id = " . (int)$_SESSION['customer_id'] . "
                                  AND ab.address_book_id = " . (int)$_SESSION['sendto'];
            $tax_address_result = $db->Execute($tax_address_query);
            break;
        case 'Billing':

            $tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id
                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                  LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                  WHERE ab.customers_id = " . (int)$_SESSION['customer_id'] . "
                                  AND ab.address_book_id = " . (int)$_SESSION['billto'];
            $tax_address_result = $db->Execute($tax_address_query);
            break;
        case 'Store':
            $tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id
                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                  LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                  WHERE ab.customers_id = " . (int)$_SESSION['customer_id'] . "
                                  AND ab.address_book_id = " . (int)$_SESSION['billto'];
            $tax_address_result = $db->Execute($tax_address_query);

            if ($tax_address_result->fields['entry_zone_id'] !== STORE_ZONE && (!empty($_SESSION['sendto']))) {
                $tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id
                                      FROM " . TABLE_ADDRESS_BOOK . " ab
                                      LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                      WHERE ab.customers_id = " . (int)$_SESSION['customer_id'] . "
                                      AND ab.address_book_id = " . (int)$_SESSION['sendto'];
                $tax_address_result = $db->Execute($tax_address_query);
            }
            break;
    }
    $tax_address['zone_id'] = $tax_address_result->fields['entry_zone_id'] ?? '0';
    $tax_address['country_id'] = $tax_address_result->fields['entry_country_id'] ?? '0';
    return $tax_address;
}

/**
 * @param int $country_id
 * @param int $zone_id
 * @return array|string
 */
function zen_get_all_tax_descriptions($country_id = -1, $zone_id = -1)
{
    global $db, $zco_notifier;

    // Give an observer the chance to override this function's return.
    $tax_descriptions = '';
    $zco_notifier->notify(
        'NOTIFY_ZEN_GET_ALL_TAX_DESCRIPTIONS_OVERRIDE',
        [
            'country_id' => $country_id,
            'zone_id' => $zone_id
        ],
        $tax_descriptions
    );
    if (is_array($tax_descriptions)) {
        return $tax_descriptions;
    }

    if ($country_id == -1 && $zone_id == -1) {
        if (zen_is_logged_in()) {
            $country_id = $_SESSION['customer_country_id'];
            $zone_id = $_SESSION['customer_zone_id'];
        } else {
            $country_id = STORE_COUNTRY;
            $zone_id = STORE_ZONE;
        }
    }

    $sql = "SELECT tr.*
            FROM " . TABLE_TAX_RATES . " tr
            LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
            LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
            WHERE (za.zone_country_id IS null
              OR za.zone_country_id = 0
              OR za.zone_country_id = " . (int)$country_id . ")
            AND (za.zone_id IS null
              OR za.zone_id = 0
              OR za.zone_id = " . (int)$zone_id . ")";
    $results = $db->Execute($sql);
    $taxDescriptions = [];
    foreach ($results as $result) {
        $taxDescriptions[] = $result['tax_description'];
    }
    return $taxDescriptions;
}


// @todo deprecate unless this is needed for different formatting
/**
 * Returns the tax rate for a tax class
 */
function zen_get_tax_rate_value($class_id)
{
    return zen_get_tax_rate($class_id);
}
