<?php
/**
 * currencies Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */

/**
 * currencies Class.
 * Class to handle currency definitions, conversions, and decorations/formatting
 *
 * @package classes
 */
class currencies extends base
{
    public $currencies = [];

    public function __construct($currencies_array = null)
    {
        $this->setDefinedCurrencies($currencies_array);
    }

    protected function setDefinedCurrencies(array $currencies_array = null)
    {
        if ($currencies_array !== null) {
            $this->currencies = $currencies_array;
            return;
        }

        global $db;
        $currencies_query = "select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value
                             from " . TABLE_CURRENCIES;

        $currencies = $db->Execute($currencies_query);

        foreach ($currencies as $row) {
            $this->currencies[$row['code']] = [
                'title'           => $row['title'],
                'symbol_left'     => $row['symbol_left'],
                'symbol_right'    => $row['symbol_right'],
                'decimal_point'   => $row['decimal_point'],
                'thousands_point' => $row['thousands_point'],
                'decimal_places'  => (int)$row['decimal_places'],
                'value'           => $row['value'],
            ];
        }
    }

    /**
     * Round and decorate the $number according to the defined rules of the specified currency code
     *
     * @param float $number
     * @param bool $calculate_currency_value
     * @param string $currency_code
     * @param float $currency_value (optional override of currency value)
     * @return string
     */
    public function format($number, $calculate_currency_value = true, $currency_code = '', $currency_value = null)
    {
        // handle Display-no-prices-during-maintenance setting
        if (IS_ADMIN_FLAG === false && DOWN_FOR_MAINTENANCE == 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF == 'true' && !strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE,
                $_SERVER['REMOTE_ADDR'])
        ) {
            return '';
        }

        // determine which currency to operate on
        if (empty($currency_code)) {
            $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
        }

        $rate = 1;
        if ($calculate_currency_value === true) {
            $rate = (!empty($currency_value)) ? $currency_value : $this->currencies[$currency_code]['value'];
        }

        // build formatted string from prefix/suffix and calculated value
        $format_string = $this->currencies[$currency_code]['symbol_left'];
        $format_string .= number_format(zen_round($number * $rate, $this->currencies[$currency_code]['decimal_places']), $this->currencies[$currency_code]['decimal_places'], $this->currencies[$currency_code]['decimal_point'], $this->currencies[$currency_code]['thousands_point']);
        $format_string .= $this->currencies[$currency_code]['symbol_right'];

        // Special Case: if the selected currency is in the european euro-conversion and the default currency is euro,
        // then the currency will displayed in both the national currency and euro currency
        if ($rate !== 1 && DEFAULT_CURRENCY == 'EUR' && in_array($currency_code, ['DEM', 'BEF', 'LUF', 'ESP', 'FRF', 'IEP', 'ITL', 'NLG', 'ATS', 'PTE', 'FIM', 'GRD'])) {
            $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
        }

        return $format_string;
    }

    /**
     * Calculated converted amount based on exchange rate
     * and return rounded amount according to specified currency's defined number of decimal places
     *
     * @param $number
     * @param bool $calculate_currency_value
     * @param string $currency_code
     * @param float $currency_value (optional exchange rate override)
     * @return float|string
     */
    public function value($number, $calculate_currency_value = true, $currency_code = '', $currency_value = null)
    {
        if (empty($currency_code)) {
            $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
        }

        $rate = 1;
        if ($calculate_currency_value == true) {
            if ($currency_code == DEFAULT_CURRENCY) {
                $rate = (!empty($currency_value)) ? $currency_value : 1 / $this->currencies[$_SESSION['currency']]['value'];
            } else {
                $rate = (!empty($currency_value)) ? $currency_value : $this->currencies[$currency_code]['value'];
            }
        }

        return zen_round($number * $rate, $this->currencies[$currency_code]['decimal_places']);
    }

    /**
     * Change the decimals-indicator of the incoming value to a "." so we can calculate properly
     *
     * @param $valueIn
     * @param string $currency_code
     * @return mixed
     */
    public function normalizeValue($valueIn, $currency_code = null)
    {
        if (!empty($currency_code)) {
            $currency_code = (isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY);
        }

        return str_replace($this->currencies[$currency_code]['decimal_point'], '.', $valueIn);
    }

    /**
     * Does specified currency code exist in defined list of supported currencies
     *
     * @param string $code
     * @return bool
     */
    public function exists($code)
    {
        return (isset($this->currencies[$code]) && zen_not_null($this->currencies[$code]));
    }

    /**
     * @deprecated since v1.6.0 - use exists() instead
     */
    public function is_set($code)
    {
        $this->exists($code);
    }

    /**
     * Return the exchange rate of the specified currency
     *
     * @param string $code
     * @return mixed
     */
    public function get_value($code)
    {
        return $this->currencies[$code]['value'];
    }

    /**
     * Return the number of decimal places for the specified currency
     *
     * @param string $code
     * @return mixed
     */
    public function get_decimal_places($code)
    {
        return $this->currencies[$code]['decimal_places'];
    }

    /**
     * multiply the price by qty, and also by tax rate, then return the decorated/formatted value
     * (assumes using default/session currency)
     *
     * @param float $product_price
     * @param float $product_tax_rate
     * @param int $quantity
     * @return string
     */
    public function display_price($product_price, $product_tax_rate, $quantity = 1)
    {
        return $this->format(zen_add_tax($product_price * $quantity, $product_tax_rate));
    }
}
