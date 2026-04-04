<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminCouponLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateAndEditDiscountCoupon(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'ADMIN-LIFECYCLE-' . uniqid();

        $newPage = $this->getAdmin('/admin/index.php?cmd=coupon_admin&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Coupon Name')
            ->assertSee('Coupon Code');

        $preview = $this->submitAdminForm($newPage, 'coupon', [
            'coupon_name' => [1 => 'Lifecycle Coupon'],
            'coupon_desc' => [1 => 'Created by admin feature test'],
            'coupon_amount' => '12.50',
            'coupon_code' => $couponCode,
            'coupon_uses_coupon' => '5',
            'coupon_uses_user' => '2',
            'coupon_min_order' => '20.00',
            'coupon_calc_base' => '1',
            'coupon_is_valid_for_sales' => '1',
            'coupon_product_count' => '0',
            'coupon_zone_restriction' => '0',
            'coupon_order_limit' => '',
            'coupon_startdate_month' => '3',
            'coupon_startdate_day' => '25',
            'coupon_startdate_year' => '2026',
            'coupon_finishdate_month' => '3',
            'coupon_finishdate_day' => '25',
            'coupon_finishdate_year' => '2027',
        ]);

        $preview->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Coupon')
            ->assertSee($couponCode)
            ->assertSee('12.5')
            ->assertSee('20.00');

        $createdPage = $this->submitAdminForm($preview, 'coupon', [
            'coupon_name' => [1 => 'Lifecycle Coupon'],
            'coupon_desc' => [1 => 'Created by admin feature test'],
        ]);

        $createdPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Discount Coupons')
            ->assertSee('Lifecycle Coupon')
            ->assertSee($couponCode);

        $couponId = (int) TestDb::selectValue(
            'SELECT coupon_id FROM coupons WHERE coupon_code = :coupon_code LIMIT 1',
            [':coupon_code' => $couponCode]
        );

        $this->assertGreaterThan(0, $couponId);

        $createdCoupon = TestDb::selectOne(
            'SELECT coupon_amount, coupon_minimum_order, uses_per_coupon, uses_per_user, coupon_calc_base
               FROM coupons
              WHERE coupon_id = :coupon_id
              LIMIT 1',
            [':coupon_id' => $couponId]
        );

        $this->assertNotNull($createdCoupon);
        $this->assertSame('12.5000', (string) $createdCoupon['coupon_amount']);
        $this->assertSame('20.0000', (string) $createdCoupon['coupon_minimum_order']);
        $this->assertSame('5', (string) $createdCoupon['uses_per_coupon']);
        $this->assertSame('2', (string) $createdCoupon['uses_per_user']);
        $this->assertSame('1', (string) $createdCoupon['coupon_calc_base']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=coupon_admin&action=voucheredit&cid=' . $couponId)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Coupon');

        $editPreview = $this->submitAdminForm($editPage, 'coupon', [
            'coupon_name' => [1 => 'Lifecycle Coupon Updated'],
            'coupon_desc' => [1 => 'Updated by admin feature test'],
            'coupon_amount' => '18.75',
            'coupon_uses_coupon' => '7',
            'coupon_uses_user' => '3',
            'coupon_min_order' => '30.00',
            'coupon_calc_base' => '0',
            'coupon_is_valid_for_sales' => '0',
            'coupon_startdate_month' => '4',
            'coupon_startdate_day' => '1',
            'coupon_startdate_year' => '2026',
            'coupon_finishdate_month' => '4',
            'coupon_finishdate_day' => '1',
            'coupon_finishdate_year' => '2027',
        ]);

        $editPreview->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Coupon Updated')
            ->assertSee($couponCode)
            ->assertSee('18.75')
            ->assertSee('30.00');

        $updatedPage = $this->submitAdminForm($editPreview, 'coupon', [
            'coupon_name' => [1 => 'Lifecycle Coupon Updated'],
            'coupon_desc' => [1 => 'Updated by admin feature test'],
        ]);

        $updatedPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Discount Coupons')
            ->assertSee('Lifecycle Coupon Updated')
            ->assertSee($couponCode);

        $updatedCoupon = TestDb::selectOne(
            'SELECT coupon_amount, coupon_minimum_order, uses_per_coupon, uses_per_user, coupon_calc_base, coupon_is_valid_for_sales
               FROM coupons
              WHERE coupon_id = :coupon_id
              LIMIT 1',
            [':coupon_id' => $couponId]
        );
        $updatedDescription = TestDb::selectOne(
            'SELECT coupon_name, coupon_description
               FROM coupons_description
              WHERE coupon_id = :coupon_id
                AND language_id = :language_id
              LIMIT 1',
            [
                ':coupon_id' => $couponId,
                ':language_id' => 1,
            ]
        );

        $this->assertNotNull($updatedCoupon);
        $this->assertNotNull($updatedDescription);
        $this->assertSame('18.7500', (string) $updatedCoupon['coupon_amount']);
        $this->assertSame('30.0000', (string) $updatedCoupon['coupon_minimum_order']);
        $this->assertSame('7', (string) $updatedCoupon['uses_per_coupon']);
        $this->assertSame('3', (string) $updatedCoupon['uses_per_user']);
        $this->assertSame('0', (string) $updatedCoupon['coupon_calc_base']);
        $this->assertSame('0', (string) $updatedCoupon['coupon_is_valid_for_sales']);
        $this->assertSame('Lifecycle Coupon Updated', $updatedDescription['coupon_name']);
        $this->assertSame('Updated by admin feature test', $updatedDescription['coupon_description']);
    }

    protected function completeInitialAdminSetup(): void
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk()
            ->assertSee('Admin Home');
    }
}
