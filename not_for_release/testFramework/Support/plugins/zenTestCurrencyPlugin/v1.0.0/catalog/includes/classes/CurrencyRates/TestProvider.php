<?php

namespace Zencart\Plugins\Catalog\ZenTestCurrencyPlugin\CurrencyRates;

class TestProvider
{
    public function quote(string $currencyCode = '', string $base = ''): string
    {
        return '1.25';
    }
}
