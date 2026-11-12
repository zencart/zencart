<?php

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\ScriptedDatabase;
use Tests\Support\zcUnitTestCase;

/**
 * shoppingCart::attributes_price() for a single attribute, with and without a special.
 *
 * @see /includes/classes/shopping_cart.php
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class ShoppingCartAttributesPriceTest extends zcUnitTestCase
{
    use ScriptedDatabase;

    private const PRODUCT_ID = 1;
    private const OPTION_ID = 5;
    private const VALUE_ID = 50;
    private const BASE_PRICE = '500.0000';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Product.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Customer.php';
        require_once DIR_FS_CATALOG . 'includes/classes/shopping_cart.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_customers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_products.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_attributes.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_prices.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';

        define('WHOLESALE_PRICING_CONFIG', 'false');
        define('ATTRIBUTES_ENABLED_TEXT_PRICES', 'false');

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
     * The product's line price is its base price plus attributes_price(). For a priced-by-attributes product
     * with a base price, the cart charges the undiscounted base price, so a special must reach the whole
     * base +/- option through the attribute: the line total is discount(base +/- option).
     */
    #[DataProvider('attributeProvider')]
    public function testLineTotal(bool $priced_by_attributes, ?string $special, string $prefix, string $option_price, float $expected_line_total): void
    {
        $this->scriptProduct($priced_by_attributes, $special, $prefix, $option_price);

        $cart = new \shoppingCart();
        $uprid = self::PRODUCT_ID . ':' . md5('test');
        $cart->contents[$uprid] = ['qty' => 1, 'attributes' => [self::OPTION_ID => self::VALUE_ID]];

        // as calculate() and get_products() charge it: the special replaces the base price only when not priced by attributes
        $base = ($special !== null && !$priced_by_attributes) ? (float)$special : (float)self::BASE_PRICE;

        $this->assertEqualsWithDelta($expected_line_total, $base + $cart->attributes_price($uprid), 0.00001);
    }

    public static function attributeProvider(): array
    {
        return [
            // priced by attributes, $500 base, $450 special (10% off)
            'pba, special, - 250' => [true, '450.0000', '-', '250.0000', 225.0],
            'pba, special, - 500' => [true, '450.0000', '-', '500.0000', 0.0],
            'pba, special, + 0' => [true, '450.0000', '+', '0.0000', 450.0],
            'pba, special, + 250' => [true, '450.0000', '+', '250.0000', 675.0],
            // priced by attributes, no special: unchanged
            'pba, no special, - 250' => [true, null, '-', '250.0000', 250.0],
            'pba, no special, + 250' => [true, null, '+', '250.0000', 750.0],
            // not priced by attributes: unchanged
            'not pba, special, - 250' => [false, '450.0000', '-', '250.0000', 225.0],
            'not pba, no special, - 250' => [false, null, '-', '250.0000', 250.0],
        ];
    }

    private function scriptProduct(bool $priced_by_attributes, ?string $special, string $prefix, string $option_price): void
    {
        $this->respondTo('/FROM \S*products_attributes pa\s/', [[
            'products_attributes_id' => '10',
            'products_id' => (string)self::PRODUCT_ID,
            'options_id' => (string)self::OPTION_ID,
            'options_values_id' => (string)self::VALUE_ID,
            'options_values_price' => $option_price,
            'options_values_price_w' => '',
            'price_prefix' => $prefix,
            'product_attribute_is_free' => '0',
            'attributes_discounted' => '1',
            'attributes_display_only' => '0',
            'attributes_price_factor' => '0.0000',
            'attributes_price_factor_offset' => '0.0000',
            'attributes_qty_prices' => '',
            'products_options_type' => '0',
        ]]);
        $this->respondTo('/FROM \S*products p\s/', [[
            'products_id' => (string)self::PRODUCT_ID,
            'products_type' => '1',
            'products_model' => 'TEST-1',
            'products_image' => '',
            'products_price' => self::BASE_PRICE,
            'products_price_w' => '',
            'products_priced_by_attribute' => $priced_by_attributes ? '1' : '0',
            'product_is_free' => '0',
            'master_categories_id' => '0',
            'type_handler' => 'product',
        ]]);
        if ($special !== null) {
            $this->respondTo('/FROM \S*specials\s/', [['specials_new_products_price' => $special]]);
        }
    }
}
