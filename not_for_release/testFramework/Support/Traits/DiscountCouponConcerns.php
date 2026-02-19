<?php

namespace Tests\Support\Traits;

use Illuminate\Support\Carbon;
use Tests\Models\Coupon;
use Tests\Models\CouponDescription;

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
        $coupon = new Coupon($profile['coupon']);
        $coupon->coupon_start_date = Carbon::now()->subDays(5);
        $coupon->coupon_expire_date = Carbon::now()->addDays(5);

        $coupon->save();
        $couponDescription = new CouponDescription($profile['coupon_description']);
        $couponDescription->coupon_id = $coupon->coupon_id;
        $couponDescription->save();
    }
}
