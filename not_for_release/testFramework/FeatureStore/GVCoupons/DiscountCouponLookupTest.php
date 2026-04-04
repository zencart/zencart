<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\GVCoupons;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\DiscountCouponConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class DiscountCouponLookupTest extends zcInProcessFeatureTestCaseStore
{
    use DiscountCouponConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testDiscountCouponPageRendersLookupForm(): void
    {
        $this->getMainPage('discount_coupon')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Discount Coupon')
            ->assertSee('Look-up Discount Coupon')
            ->assertSee('Your Code:');
    }

    public function testInvalidDiscountCouponShowsHelpfulMessage(): void
    {
        $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'definitely-not-a-real-coupon',
        ])->assertOk()
            ->assertSee('does not appear to be a valid Coupon Redemption Code')
            ->assertSee('definitely-not-a-real-coupon');
    }

    public function testValidDiscountCouponDisplaysCouponDetails(): void
    {
        $this->createCoupon('test10percent');

        $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'test10percent',
        ])->assertOk()
            ->assertSee('Test 10 Percent')
            ->assertSee('Discount Coupon Restrictions')
            ->assertSee('Product Restrictions:');
    }

    public function testInactiveDiscountCouponShowsInactiveMessage(): void
    {
        $this->createCoupon('inactive10percent');

        $response = $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'inactive10percent',
        ])->assertOk()
            ->assertSee('Inactive Coupon');

        self::assertStringNotContainsString('Discount Coupon Restrictions', $response->content);
        self::assertStringNotContainsString('Product Restrictions:', $response->content);
    }

    public function testDiscountCouponDisplaysProductRestrictionDetails(): void
    {
        $couponId = $this->createCoupon('test10percent');
        $productId = (int) TestDb::selectValue(
            "SELECT p.products_id
               FROM products p
               JOIN products_description pd
                 ON pd.products_id = p.products_id
              WHERE pd.language_id = 1
                AND pd.products_name = 'Matrox G400 32MB'
              LIMIT 1"
        );

        $this->assertGreaterThan(0, $couponId);
        $this->assertGreaterThan(0, $productId);

        TestDb::insert('coupon_restrict', [
            'coupon_id' => $couponId,
            'product_id' => $productId,
            'coupon_restrict' => 'N',
        ]);

        $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'test10percent',
        ])->assertOk()
            ->assertSee('Product Restrictions:')
            ->assertSee('Matrox G400 32MB')
            ->assertSee('Valid for this product');
    }

    public function testDiscountCouponDisplaysMinimumOrderAndSaleItemWarnings(): void
    {
        $couponId = $this->createCoupon('test10percent');

        $this->assertGreaterThan(0, $couponId);

        TestDb::update('coupons', [
            'coupon_minimum_order' => '25.0000',
            'coupon_is_valid_for_sales' => 0,
        ], 'coupon_id = ' . (int) $couponId);

        $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'test10percent',
        ])->assertOk()
            ->assertSee('This coupon is not valid for products on sale.')
            ->assertSee('You need to spend');
    }

    public function testDiscountCouponDisplaysCategoryRestrictionDetails(): void
    {
        $couponId = $this->createCoupon('test10percent');
        $product = TestDb::selectOne(
            "SELECT p.master_categories_id, cd.categories_name
               FROM products p
               JOIN products_description pd
                 ON pd.products_id = p.products_id
                AND pd.language_id = 1
               JOIN categories_description cd
                 ON cd.categories_id = p.master_categories_id
                AND cd.language_id = 1
              WHERE pd.products_name = 'Matrox G400 32MB'
              LIMIT 1"
        );

        $this->assertGreaterThan(0, $couponId);
        $this->assertNotNull($product);

        TestDb::insert('coupon_restrict', [
            'coupon_id' => $couponId,
            'category_id' => $product['master_categories_id'],
            'coupon_restrict' => 'N',
        ]);

        $this->postMainPage('discount_coupon', [
            'lookup_discount_coupon' => 'test10percent',
        ])->assertOk()
            ->assertSee('Category Restrictions:')
            ->assertSee((string) $product['categories_name'])
            ->assertSee('Valid for this category');
    }
}
