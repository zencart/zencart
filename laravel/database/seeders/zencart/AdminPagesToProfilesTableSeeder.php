<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPagesToProfilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('admin_pages_to_profiles')->truncate();

        DB::table('admin_pages_to_profiles')->insert(array(
            0 =>
                array(
                    'page_key' => 'currencies',
                    'profile_id' => 2,
                ),
            1 =>
                array(
                    'page_key' => 'customers',
                    'profile_id' => 2,
                ),
            2 =>
                array(
                    'page_key' => 'gvMail',
                    'profile_id' => 2,
                ),
            3 =>
                array(
                    'page_key' => 'gvQueue',
                    'profile_id' => 2,
                ),
            4 =>
                array(
                    'page_key' => 'gvSent',
                    'profile_id' => 2,
                ),
            5 =>
                array(
                    'page_key' => 'invoice',
                    'profile_id' => 2,
                ),
            6 =>
                array(
                    'page_key' => 'mail',
                    'profile_id' => 2,
                ),
            7 =>
                array(
                    'page_key' => 'orders',
                    'profile_id' => 2,
                ),
            8 =>
                array(
                    'page_key' => 'packingslip',
                    'profile_id' => 2,
                ),
            9 =>
                array(
                    'page_key' => 'paypal',
                    'profile_id' => 2,
                ),
            10 =>
                array(
                    'page_key' => 'reportCustomers',
                    'profile_id' => 2,
                ),
            11 =>
                array(
                    'page_key' => 'reportLowStock',
                    'profile_id' => 2,
                ),
            12 =>
                array(
                    'page_key' => 'reportProductsSold',
                    'profile_id' => 2,
                ),
            13 =>
                array(
                    'page_key' => 'reportProductsViewed',
                    'profile_id' => 2,
                ),
            14 =>
                array(
                    'page_key' => 'reportReferrals',
                    'profile_id' => 2,
                ),
            15 =>
                array(
                    'page_key' => 'whosOnline',
                    'profile_id' => 2,
                ),
        ));


    }
}
