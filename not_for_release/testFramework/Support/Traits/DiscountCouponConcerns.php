<?php

namespace Tests\Support\Traits;

use Tests\Support\Database\TestDb;

trait DiscountCouponConcerns
{

    protected array $couponProfiles = [
        'test10percent' => [
            'coupon' => [
                'coupon_type' => 'P',
                'coupon_code' => 'test10percent',
                'coupon_amount' => '10',
                'uses_per_user' => '5',

            ],
            'coupon_description' => [
                'coupon_name' => 'Test 10 Percent',
                'coupon_description' => 'Test 10 Percent',
                'language_id' => 1,
            ]

        ]
    ];

    public function createCoupon($profileName)
    {
        if (!isset($this->couponProfiles[$profileName])) {
            return;
        }
        $profile = $this->couponProfiles[$profileName];
        $coupon = $profile['coupon'];
        $coupon['coupon_start_date'] = date('Y-m-d H:i:s', strtotime('-5 days'));
        $coupon['coupon_expire_date'] = date('Y-m-d H:i:s', strtotime('+5 days'));
        $couponId = TestDb::insert('coupons', $coupon);

        $couponDescription = $profile['coupon_description'];
        $couponDescription['coupon_id'] = $couponId;
        TestDb::insert('coupons_description', $couponDescription);
    }
}
