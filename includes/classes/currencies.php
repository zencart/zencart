<?php
/**
 * currencies class
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * currencies class
 *
 */
class currencies extends base
{
    var $currencies;

    function __construct()
    {
        global $db;
        $this->currencies = [];

        $query   = "select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, `value`
                    from " . TABLE_CURRENCIES;
        $results = $db->Execute($query);

        foreach ($results as $result) {
            $this->currencies[$result['code']] = [
                'title'           => $result['title'],
                'symbol_left'     => $result['symbol_left'],
                'symbol_right'    => $result['symbol_right'],
                'decimal_point'   => $result['decimal_point'],
                'thousands_point' => $result['thousands_point'],
                'decimal_places'  => (int)$result['decimal_places'],
                'value'           => $result['value'],
            ];
        }
    }

    /**
     * Format the specified number according to the specified currency's rules
     * @param float $number
     * @param bool $calculate_using_exchange_rate
     * @param string $currency_type
     * @param float $currency_value
     * @return string
     */
    function format($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = '')
    {
        if (IS_ADMIN_FLAG === false && (DOWN_FOR_MAINTENANCE == 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF == 'true') && !zen_is_whitelisted_admin_ip()) {
            return '';
        }

        if (empty($number)) $number = 0;

        if (empty($currency_type)) $currency_type = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);

        $formatted_string = $this->currencies[$currency_type]['symbol_left'] .
            number_format(
                $this->rateAdjusted($number, $calculate_using_exchange_rate, $currency_type, $currency_value),
                $this->currencies[$currency_type]['decimal_places'],
                $this->currencies[$currency_type]['decimal_point'],
                $this->currencies[$currency_type]['thousands_point']
            ) . $this->currencies[$currency_type]['symbol_right'];

        if ($calculate_using_exchange_rate == true) {
            // Special Case: if the selected currency is in the european euro-conversion and the default currency is euro,
            // then the currency will displayed in both the national currency and euro currency
            if (DEFAULT_CURRENCY == 'EUR' && in_array($currency_type, ['DEM', 'BEF', 'LUF', 'ESP', 'FRF', 'IEP', 'ITL', 'NLG', 'ATS', 'PTE', 'FIM', 'GRD'])) {
                $formatted_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
            }
        }

        return $formatted_string;
    }

    /**
     * Convert amount based on currency values
     * Or at least round it to the relevant decimal places
     *
     * @param float $number
     * @param bool $calculate_using_exchange_rate
     * @param string $currency_type
     * @param float $currency_value
     * @return float
     */
    function rateAdjusted($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = null)
    {
        if (empty($currency_type)) $currency_type = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);

        if ($calculate_using_exchange_rate == true) {
            $rate   = zen_not_null($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
            $number = $number * $rate;
        }

        return zen_round($number, $this->currencies[$currency_type]['decimal_places']);
    }

    function value($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = null)
    {
        if (empty($currency_type)) $currency_type = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);

        if ($calculate_using_exchange_rate == true) {
            $multiplier = ($currency_type == DEFAULT_CURRENCY) ? 1 / $this->currencies[$_SESSION['currency']]['value'] : $this->currencies[$currency_type]['value'];
            $rate = zen_not_null($currency_value) ? $currency_value : $multiplier;
            $number = $number * $rate;
        }

        return zen_round($number, $this->currencies[$currency_type]['decimal_places']);
    }

    /**
     * Normalize "decimal" placeholder to actually use "."
     * @param $valueIn
     * @param string $currencyCode
     * @return string
     */
    function normalizeValue($valueIn, $currencyCode = null)
    {
        if ($currencyCode === null) $currencyCode = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
        $value = str_replace($this->currencies[$currencyCode]['decimal_point'], '.', $valueIn);

        return $value;
    }

    function is_set($code)
    {
        return isset($this->currencies[$code]) && zen_not_null($this->currencies[$code]);
    }

    /**
     * Retrieve the exchange-rate of a specified currency
     * @param string $code currency code
     * @return float
     */
    function get_value($code)
    {
        return $this->currencies[$code]['value'];
    }

    /**
     * @param string $code currency code
     * @return int
     */
    function get_decimal_places($code)
    {
        return $this->currencies[$code]['decimal_places'];
    }

    /**
     * Calculate amount based on $quantity, and format it according to current currency
     * @param $product_price
     * @param $product_tax
     * @param int $quantity
     * @return string
     */
    function display_price($product_price, $product_tax, $quantity = 1)
    {
        return $this->format(zen_add_tax($product_price, $product_tax) * $quantity);
    }
}
