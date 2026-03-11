<?php
/**
 * A data class for Amount types used by the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Zc2Pp;

use PayPalRestful\Common\Logger;

class Amount
{
    protected $amount = [
        'currency_code' => '',
        'value' => '',
    ];
    protected static $defaultCurrencyCode = [
        'value' => '',
        'no_decimals' => false,
        'in_country_only' => false,
    ];
    protected static $supportedCurrencyCodes = [
        'AUD' => [],    //- Australian dollar
        'BRL' => [],    //- Brazilian real
        'CAD' => [],    //- Canadian dollar
        'CHF' => [],    //- Swiss franc
        'CNY' => ['in_country_only' => true],   //- Chinese Renmenbi
        'CZK' => [],    //- Czech koruna
        'DKK' => [],    //- Danish krone
        'EUR' => [],    //- Euro
        'GBP' => [],    //- Pound sterling
        'HKD' => [],    //- Hong Kong dollar
        'HUF' => ['no_decimals' => true],    //- Hungarian forint
        'ILS' => [],    //- Israeli new shekel
        'JPY' => ['no_decimals' => true],    //- Japanese yen
        'MYR' => ['in_country_only' => true],    //- Malaysian ringgit
        'MXN' => [],    //- Mexican peso
        'TWD' => ['no_decimals' => true],    //- New Taiwan dollar
        'NZD' => [],    //- New Zealand dollar
        'NOK' => [],    //- Norwegian krone
        'PHP' => [],    //- Philippine peso
        'PLN' => [],    //- Polish złoty
        'RUB' => [],    //- Russian ruble
        'SGD' => [],    //- Singapore dollar
        'SEK' => [],    //- Swedish krona
        'THB' => [],    //- Thai bhat
        'USD' => [],    //- United States dollar
    ];

    /**
     * Debug interface, shared with the PayPalRestfulApi class.
     */
    protected $log; //- An instance of the Logger class, logs debug tracing information.

    // -----
    // An alias for setDefaultCurrency.
    //
    public function __construct(string $default_currency_code = '')
    {
        $this->log = new Logger();

        if ($default_currency_code !== '') {
            $this->setDefaultCurrency($default_currency_code);
        }

        $this->amount['currency_code'] = $this->getDefaultCurrencyCode();
    }

    public function getSupportedCurrencyCodes(): array
    {
        return array_keys(self::$supportedCurrencyCodes);
    }

    public function get(): array
    {
        return $this->amount;
    }

    public function getCurrencyDecimals(string $currency_code = ''): int
    {
        if ($currency_code === '') {
            $currency_code = $this->amount['currency_code'];
        }
        return (isset(self::$supportedCurrencyCodes[$currency_code]['no_decimals'])) ? 0 : 2;
    }

    public function setDefaultCurrency(string $currency_code)
    {
        if (in_array($currency_code, $this->getSupportedCurrencyCodes())) {
            $default_currency = $currency_code;
        } else {
            $this->log->write("Amount::setDefaultCurrency, requested default ($currency_code) not found, trying backups");
            if (in_array(DEFAULT_CURRENCY, $this->getSupportedCurrencyCodes())) {
                $this->log->write('  --> Using store default currency (' . DEFAULT_CURRENCY . ')');
                $default_currency = DEFAULT_CURRENCY;
            } else {
                $this->log->write('  --> Using configured back-up currency (' . MODULE_PAYMENT_PAYPALR_CURRENCY_FALLBACK . ')');
                $default_currency = MODULE_PAYMENT_PAYPALR_CURRENCY_FALLBACK;
            }
        }

        self::$defaultCurrencyCode = [
            'code' => $default_currency,
            'no_decimals' => isset(self::$defaultCurrencyCode[$default_currency]['no_decimals']),
            'in_country_only' => isset(self::$defaultCurrencyCode[$default_currency]['in_country_only']),
        ];

        $this->amount['currency_code'] = $default_currency;
    }

    public function getDefaultCurrency(): array
    {
        return self::$defaultCurrencyCode;
    }

    public function getDefaultCurrencyCode(): string
    {
        return self::$defaultCurrencyCode['code'];
    }

    public function getValueFromFloat(float $value): string
    {
        $amount = $this->setValue($value);
        return $amount['value'];
    }
    public function getValueFromString(string $value): string
    {
        $amount = $this->setValue((float)$value);
        return $amount['value'];
    }
    public function setValue(float $value): array
    {
        $amount_value = number_format($value, 2, '.', '');
        if (self::$defaultCurrencyCode['no_decimals'] === true && strpos($value, '.00') === false) {
//            $default_currency_code = self::$defaultCurrencyCode['code'];
//            $this->log->write("Amount::setValue, value ($amount_value) has unsupported decimal digits for currency $default_currency_code; value is converted to integer.");
            $amount_value = (string)((int)$value);
        }
        $this->amount['value'] = $amount_value;

        return $this->amount;
    }
}
