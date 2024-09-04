<?php
/**
 * currencies class
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 08 Modified in v2.1.0-alpha2 $
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
    public $currencies = [];

    protected $debug = false;

    public function __construct()
    {
        global $db;

        $query =
            "SELECT code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, `value`
               FROM " . TABLE_CURRENCIES;
        $results = $db->Execute($query);

        foreach ($results as $result) {
            $this->currencies[$result['code']] = [
                'title' => $result['title'],
                'symbol_left' => $result['symbol_left'],
                'symbol_right' => $result['symbol_right'],
                'decimal_point' => $result['decimal_point'],
                'thousands_point' => $result['thousands_point'],
                'decimal_places' => (int)$result['decimal_places'],
                'value' => $result['value'],
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
    public function format($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = '')
    {
        if (IS_ADMIN_FLAG === false && DOWN_FOR_MAINTENANCE === 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF === 'true' && !zen_is_whitelisted_admin_ip()) {
            return '';
        }

        if (empty($number)) {
            $number = 0;
        }

        $currency_info = $this->getCurrencyInfo($currency_type);

        $formatted_string = $currency_info['symbol_left'] .
            number_format(
                $this->rateAdjusted($number, $calculate_using_exchange_rate, $currency_type, $currency_value),
                $currency_info['decimal_places'],
                $currency_info['decimal_point'],
                $currency_info['thousands_point']
            ) . $currency_info['symbol_right'];

        if ($calculate_using_exchange_rate === true) {
            // Special Case: if the selected currency is in the european euro-conversion and the default currency is euro,
            // then the currency will displayed in both the national currency and euro currency
            if (DEFAULT_CURRENCY === 'EUR' && in_array($currency_type, ['DEM', 'BEF', 'LUF', 'ESP', 'FRF', 'IEP', 'ITL', 'NLG', 'ATS', 'PTE', 'FIM', 'GRD'])) {
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
    public function rateAdjusted($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = null)
    {
        $currency_info = $this->getCurrencyInfo($currency_type);

        if ($calculate_using_exchange_rate === true) {
            $rate = !empty($currency_value) ? $currency_value : $currency_info['value'];
            $number = $number * $rate;
        }

        return zen_round($number, $currency_info['decimal_places']);
    }

    public function value($number, $calculate_using_exchange_rate = true, $currency_type = '', $currency_value = null)
    {
        $currency_info = $this->getCurrencyInfo($currency_type);

        if ($calculate_using_exchange_rate === true) {
            $multiplier = ($currency_type === DEFAULT_CURRENCY) ? 1 / $this->currencies[$_SESSION['currency']]['value'] : $currency_info['value'];
            $rate = !empty($currency_value) ? $currency_value : $multiplier;
            $number = $number * $rate;
        }

        return zen_round($number, $currency_info['decimal_places']);
    }

    /**
     * Normalize "decimal" placeholder to actually use "."
     * @param $valueIn
     * @param string $currencyCode
     * @return string
     */
    public function normalizeValue($valueIn, ?string $currencyCode = null)
    {
        $currency_info = $this->getCurrencyInfo($currencyCode);
        return str_replace($currency_info['decimal_point'], '.', (string)$valueIn);
    }

    public function is_set($code)
    {
        return !empty($this->currencies[$code]);
    }

    /**
     * Retrieve the exchange-rate of a specified currency
     * @param string $code currency code
     * @return float
     */
    public function get_value($code)
    {
        $currency_info = $this->getCurrencyInfo($code);
        return $currency_info['value'];
    }

    /**
     * @param string $code currency code
     * @return int
     */
    public function get_decimal_places($code)
    {
        $currency_info = $this->getCurrencyInfo($code);
        return $currency_info['decimal_places'];
    }

    /**
     * Public function to enable the debug, so that a PHP Notify log is created if
     * an unknown currency-code is auto-created.
     * @param void
     * @return void
     */
    public function setDebugOn()
    {
        $this->debug = true;
    }

    /**
     * Public function to disable the debug.
     * @param void
     * @return void
     */
    public function setDebugOff()
    {
        $this->debug = false;
    }

    /**
     * Protected function that returns an array of 'currency' settings for the specified
     * currency_code.
     *
     * @param string|null $currency_code The currency 'code' information to be returned.
     * @return array
     */
    protected function getCurrencyInfo(?string $currency_code): array
    {
        // -----
        // If the submitted currency-code is 'empty' (i.e. '' or null), default the
        // to-be-returned currency to the session value (if present) or the site's
        // default otherwise.
        //
        if (empty($currency_code)) {
            $currency_code = $_SESSION['currency'] ?? DEFAULT_CURRENCY;
        }

        // -----
        // If the submitted currency-code is not present for the site, a default set of
        // currency settings is created using those associated with the site's default
        // currency.  The difference is that the 'symbol_left' is the submitted currency
        // code and there is no 'symbol-right' character string.
        //
        // This condition can arise, for instance, if a site "used to" accept payments in EUR
        // but no longer does and orders paid in euros have been recorded in the site's database.
        // An amount for this case would be formatted similar to 'EUR 20.00'.
        //
        if (empty($this->currencies[$currency_code])) {
            $this->currencies[$currency_code] = $this->currencies[DEFAULT_CURRENCY];
            $this->currencies[$currency_code]['symbol_left'] = $currency_code . ' ';
            $this->currencies[$currency_code]['symbol_right'] = '';
            if ($this->debug === true) {
                trigger_error("Creating currency settings for $currency_code, based on " . DEFAULT_CURRENCY . " settings.", E_USER_NOTICE);
            }
        }

        // -----
        // Return the settings associated with the specified currency.
        //
        return $this->currencies[$currency_code];
    }

    /**
     * Calculate amount based on $quantity, and format it according to current currency
     * @param $product_price
     * @param $product_tax
     * @param int $quantity
     * @return string
     */
    public function display_price($product_price, $product_tax, $quantity = 1)
    {
        return $this->format(zen_add_tax($product_price, $product_tax) * $quantity);
    }
}
