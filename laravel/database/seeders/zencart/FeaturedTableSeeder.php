<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeaturedTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('featured')->truncate();

        DB::table('featured')->insert(array(
            0 =>
                array(
                    'date_status_change' => '2004-02-21 16:34:31',
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-02-21 16:34:31',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 1,
                    'featured_last_modified' => '2004-02-21 16:34:31',
                    'products_id' => 34,
                    'status' => 1,
                ),
            1 =>
                array(
                    'date_status_change' => '2004-04-25 22:50:50',
                    'expires_date' => '2004-02-27',
                    'featured_date_added' => '2004-02-21 17:04:54',
                    'featured_date_available' => '2004-02-21',
                    'featured_id' => 2,
                    'featured_last_modified' => '2004-02-21 22:31:52',
                    'products_id' => 8,
                    'status' => 0,
                ),
            2 =>
                array(
                    'date_status_change' => '2004-02-21 17:10:49',
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-02-21 17:10:49',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 3,
                    'featured_last_modified' => '2004-02-21 17:10:49',
                    'products_id' => 12,
                    'status' => 1,
                ),
            3 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-02-21 22:30:53',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 4,
                    'featured_last_modified' => NULL,
                    'products_id' => 27,
                    'status' => 1,
                ),
            4 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-02-21 22:31:24',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 5,
                    'featured_last_modified' => NULL,
                    'products_id' => 26,
                    'status' => 1,
                ),
            5 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-05-13 22:50:33',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 6,
                    'featured_last_modified' => NULL,
                    'products_id' => 40,
                    'status' => 1,
                ),
            6 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-07-12 15:47:22',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 7,
                    'featured_last_modified' => NULL,
                    'products_id' => 171,
                    'status' => 1,
                ),
            7 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-07-12 15:47:29',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 8,
                    'featured_last_modified' => NULL,
                    'products_id' => 172,
                    'status' => 1,
                ),
            8 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-07-12 15:47:37',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 9,
                    'featured_last_modified' => NULL,
                    'products_id' => 168,
                    'status' => 1,
                ),
            9 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'featured_date_added' => '2004-07-12 15:47:45',
                    'featured_date_available' => '0001-01-01',
                    'featured_id' => 10,
                    'featured_last_modified' => NULL,
                    'products_id' => 169,
                    'status' => 1,
                ),
        ));


    }
}
