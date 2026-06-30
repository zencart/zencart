<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class CouponAdminBoundaryValidationTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * These tests POST directly to action=update_confirm (bypassing the
     * action=update_preview step) because that handler builds and saves
     * $sql_data_array with no validation gate of its own - it must not rely
     * on the preview step having run first.
     */
    private function postCouponUpdateConfirm(string $couponCode, array $overrides = []): \Tests\Support\InProcess\FeatureResponse
    {
        $newPage = $this->getAdmin('/admin/index.php?cmd=coupon_admin&action=new')->assertOk();
        $securityToken = $newPage->securityToken();
        $this->assertNotNull($securityToken);

        return $this->postAdmin(
            '/admin/index.php?cmd=coupon_admin&action=update_confirm&oldaction=new',
            array_merge([
                'securityToken' => $securityToken,
                'coupon_name' => [1 => 'Boundary Test Coupon'],
                'coupon_desc' => [1 => 'Created by boundary validation test'],
                'coupon_code' => $couponCode,
                'coupon_amount' => '10',
                'coupon_product_count' => '0',
                'coupon_uses_coupon' => '5',
                'coupon_uses_user' => '1',
                'coupon_min_order' => '0',
                'coupon_products' => '',
                'coupon_categories' => '',
                'coupon_referrer' => '',
                'coupon_zone_restriction' => '0',
                'coupon_calc_base' => '1',
                'coupon_order_limit' => '',
                'coupon_is_valid_for_sales' => '1',
                'coupon_startdate' => '2026-01-01',
                'coupon_finishdate' => '2027-01-01',
            ], $overrides)
        );
    }

    private function fetchCoupon(string $couponCode): ?array
    {
        $couponId = (int) TestDb::selectValue(
            'SELECT coupon_id FROM coupons WHERE coupon_code = :coupon_code LIMIT 1',
            [':coupon_code' => $couponCode]
        );

        $this->assertGreaterThan(0, $couponId);

        return TestDb::selectOne(
            'SELECT coupon_type, coupon_amount, coupon_minimum_order, uses_per_coupon
               FROM coupons
              WHERE coupon_id = :coupon_id
              LIMIT 1',
            [':coupon_id' => $couponId]
        );
    }

    // A percentage-type coupon must be clamped to <= 100%.
    public function testPercentageCouponAmountIsClampedTo100(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'B2BF8-' . uniqid();
        $this->postCouponUpdateConfirm($couponCode, [
            'coupon_amount' => '9999%',
        ]);

        $coupon = $this->fetchCoupon($couponCode);

        $this->assertSame('P', $coupon['coupon_type']);
        $this->assertSame('100.0000', (string) $coupon['coupon_amount']);
    }

    // Non-percentage (flat-amount) coupons are a legitimate business decision
    // and must NOT be clamped, even above 100.
    public function testFlatAmountCouponAboveOneHundredIsNotClamped(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'B2BF8-FLAT-' . uniqid();
        $this->postCouponUpdateConfirm($couponCode, [
            'coupon_amount' => '9999',
        ]);

        $coupon = $this->fetchCoupon($couponCode);

        $this->assertSame('F', $coupon['coupon_type']);
        $this->assertSame('9999.0000', (string) $coupon['coupon_amount']);
    }

    // coupon_minimum_order must be clamped to a minimum of 0.
    public function testNegativeMinimumOrderIsClampedToZero(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'B2BF9-' . uniqid();
        $this->postCouponUpdateConfirm($couponCode, [
            'coupon_min_order' => '-100',
        ]);

        $coupon = $this->fetchCoupon($couponCode);

        $this->assertSame('0.0000', (string) $coupon['coupon_minimum_order']);
    }

    // uses_per_coupon must be clamped to a minimum of 0.
    public function testNegativeUsesPerCouponIsClampedToZero(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'B2BF10-' . uniqid();
        $this->postCouponUpdateConfirm($couponCode, [
            'coupon_uses_coupon' => '-5',
        ]);

        $coupon = $this->fetchCoupon($couponCode);

        $this->assertSame('0', (string) $coupon['uses_per_coupon']);
    }

    // 0 is the documented "unlimited uses" sentinel (see ot_coupon.php's
    // validateCouponMaximumUses()) and must be preserved, not bumped to 1.
    public function testZeroUsesPerCouponIsPreservedAsUnlimitedSentinel(): void
    {
        $this->completeInitialAdminSetup();

        $couponCode = 'B2BF10-ZERO-' . uniqid();
        $this->postCouponUpdateConfirm($couponCode, [
            'coupon_uses_coupon' => '0',
        ]);

        $coupon = $this->fetchCoupon($couponCode);

        $this->assertSame('0', (string) $coupon['uses_per_coupon']);
    }
}
