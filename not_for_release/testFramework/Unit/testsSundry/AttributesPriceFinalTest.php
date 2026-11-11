<?php

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\ScriptedDatabase;
use Tests\Support\zcUnitTestCase;

/**
 * Storefront attribute-price display: zen_get_attributes_price_final() as called by includes/modules/attributes.php.
 *
 * @see /includes/functions/functions_prices.php
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class AttributesPriceFinalTest extends zcUnitTestCase
{
    use ScriptedDatabase;

    private const PRODUCT_ID = 1;
    private const ATTRIBUTE_ID = 10;
    private const BASE_PRICE = '500.0000';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Product.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Customer.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_products.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_attributes.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_prices.php';

        define('WHOLESALE_PRICING_CONFIG', 'false');

        $GLOBALS['lng'] = new class {
            public function get_language_list(): array
            {
                return [1 => 'en'];
            }
        };
        $_SESSION['languages_id'] = 1;
        $_SESSION['languages_code'] = 'en';

        $this->useScriptedDatabase();
    }

    /**
     * Without a special or sale, the displayed attribute price is the option's own price.
     */
    #[DataProvider('undiscountedProvider')]
    public function testUndiscountedAttributePrice(bool $priced_by_attributes, string $prefix, string $option_price, float $expected): void
    {
        $this->scriptProduct($priced_by_attributes, null);

        $this->assertEqualsWithDelta($expected, $this->storefrontPrice($priced_by_attributes, $prefix, $option_price), 0.00001);
    }

    public static function undiscountedProvider(): array
    {
        return [
            'priced by attributes, - 250' => [true, '-', '250.0000', -250.0],
            'priced by attributes, - 0' => [true, '-', '0.0000', 0.0],
            'priced by attributes, + 250' => [true, '+', '250.0000', 250.0],
            'priced by attributes, + 0' => [true, '+', '0.0000', 0.0],
            'not priced by attributes, - 250' => [false, '-', '250.0000', -250.0],
            'not priced by attributes, - 0' => [false, '-', '0.0000', 0.0],
            'not priced by attributes, + 250' => [false, '+', '250.0000', 250.0],
        ];
    }

    /**
     * With a special, a priced-by-attributes product's displayed attribute price matches what the shopping
     * cart charges for it (includes/classes/shopping_cart.php): -discount(option) for '-' and
     * discount(option + base) - base for '+'.
     */
    #[DataProvider('cartProvider')]
    public function testPricedByAttributesMatchesTheCartWithASpecial(string $prefix, string $option_price, float $expected): void
    {
        $this->scriptProduct(true, '450.0000');

        if ($prefix === '-') {
            $cart = -zen_get_discount_calc(self::PRODUCT_ID, self::ATTRIBUTE_ID, $option_price);
        } else {
            $cart = zen_get_discount_calc(self::PRODUCT_ID, self::ATTRIBUTE_ID, $option_price + self::BASE_PRICE) - self::BASE_PRICE;
        }
        $displayed = $this->storefrontPrice(true, $prefix, $option_price);

        $this->assertEqualsWithDelta($cart, $displayed, 0.00001);
        $this->assertEqualsWithDelta($expected, $displayed, 0.00001);
    }

    public static function cartProvider(): array
    {
        return [
            '- 250' => ['-', '250.0000', -225.0],
            '- 0' => ['-', '0.0000', 0.0],
            '+ 250' => ['+', '250.0000', 175.0],
        ];
    }

    private function scriptProduct(bool $priced_by_attributes, ?string $special_price): void
    {
        $this->respondTo('/FROM \S*products p\s/', [[
            'products_id' => (string)self::PRODUCT_ID,
            'products_type' => '1',
            'products_model' => 'TEST-1',
            'products_image' => '',
            'products_price' => self::BASE_PRICE,
            'products_price_w' => '',
            'products_priced_by_attribute' => $priced_by_attributes ? '1' : '0',
            'master_categories_id' => '0',
            'type_handler' => 'product',
        ]]);
        if ($special_price !== null) {
            $this->respondTo('/FROM \S*specials\s/', [['specials_new_products_price' => $special_price]]);
        }
    }

    private function storefrontPrice(bool $priced_by_attributes, string $prefix, string $option_price): float
    {
        $attribute = new \stdClass();
        $attribute->fields = [
            'products_id' => (string)self::PRODUCT_ID,
            'products_attributes_id' => (string)self::ATTRIBUTE_ID,
            'options_values_price' => $option_price,
            'options_values_price_w' => '',
            'price_prefix' => $prefix,
            'attributes_qty_prices' => '',
            'attributes_price_factor' => '0.0000',
            'attributes_price_factor_offset' => '0.0000',
        ];

        return (float)zen_get_attributes_price_final(self::ATTRIBUTE_ID, 1, $attribute, false, $priced_by_attributes);
    }
}
