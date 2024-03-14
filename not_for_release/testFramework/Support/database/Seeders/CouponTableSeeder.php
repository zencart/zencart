<?php

namespace Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CouponTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Capsule::table('coupons')->truncate();
        Capsule::table('coupons_description')->truncate();

        Capsule::table('coupons')->insert(array(
            0 =>
                array(
                    'coupon_type' => 'G',
                    'coupon_code' => 'VALID10',
                    'coupon_amount' => 10,
                    'uses_per_user' => 1,
                    'coupon_start_date' => Carbon::now(),
                    'coupon_expire_date' => Carbon::now()->addDays(5),
                ),
        ));

        Capsule::table('coupons_description')->insert(array(
            0 =>
                array(
                    'coupon_id' => 1,
                    'language_id' => 1,
                    'coupon_name' => 'VALID10',
                    'coupon_description' => 'VALID10',
                ),
        ));

        Capsule::table('coupon_email_track')->insert(array(
            0 =>
                array(
                    'unique_id' => 1,
                    'coupon_id' => 1,
                ),
        ));
    }
}
