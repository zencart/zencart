<?php

function quote_zztestcli_currency(string $currencyCode = '', string $base = DEFAULT_CURRENCY): string
{
    return (new \Zencart\Plugins\Catalog\ZenTestCurrencyPlugin\CurrencyRates\TestProvider())->quote($currencyCode, $base);
}

function quote_zztestbackup_currency(string $currencyCode = '', string $base = DEFAULT_CURRENCY): string
{
    return (new \Zencart\Plugins\Catalog\ZenTestCurrencyPlugin\CurrencyRates\TestProvider())->quote($currencyCode, $base);
}
