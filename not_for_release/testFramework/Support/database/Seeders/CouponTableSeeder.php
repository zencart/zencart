<?php

namespace Seeders;

use Tests\Support\Database\TestDb;

class CouponTableSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        TestDb::truncate('coupons');
        TestDb::truncate('coupons_description');

        $couponId = TestDb::insert('coupons', [
            'coupon_type' => 'G',
            'coupon_code' => 'VALID10',
            'coupon_amount' => 10,
            'uses_per_user' => 1,
            'coupon_start_date' => date('Y-m-d H:i:s'),
            'coupon_expire_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
        ]);

        TestDb::insert('coupons_description', [
            'coupon_id' => $couponId,
            'language_id' => 1,
            'coupon_name' => 'VALID10',
            'coupon_description' => 'VALID10',
        ]);

        TestDb::insert('coupon_email_track', [
            'unique_id' => 1,
            'coupon_id' => $couponId,
        ]);
    }
}
