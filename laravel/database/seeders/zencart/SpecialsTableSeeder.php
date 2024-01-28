<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('specials')->truncate();

        DB::table('specials')->insert(array(
            0 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 3,
                    'specials_date_added' => '2003-12-23 03:18:19',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 1,
                    'specials_last_modified' => '0001-01-01 00:00:00',
                    'specials_new_products_price' => '39.9900',
                    'status' => 1,
                ),
            1 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 5,
                    'specials_date_added' => '2003-12-23 03:18:19',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 2,
                    'specials_last_modified' => '0001-01-01 00:00:00',
                    'specials_new_products_price' => '30.0000',
                    'status' => 1,
                ),
            2 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 6,
                    'specials_date_added' => '2003-12-23 03:18:19',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 3,
                    'specials_last_modified' => '0001-01-01 00:00:00',
                    'specials_new_products_price' => '30.0000',
                    'status' => 1,
                ),
            3 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 16,
                    'specials_date_added' => '2003-12-23 03:18:19',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 4,
                    'specials_last_modified' => '0001-01-01 00:00:00',
                    'specials_new_products_price' => '29.9900',
                    'status' => 1,
                ),
            4 =>
                array(
                    'date_status_change' => '2023-06-29 12:14:41',
                    'expires_date' => '2008-02-21',
                    'products_id' => 41,
                    'specials_date_added' => '2003-12-25 19:15:47',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 5,
                    'specials_last_modified' => '2004-09-27 13:33:33',
                    'specials_new_products_price' => '90.0000',
                    'status' => 0,
                ),
            5 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 42,
                    'specials_date_added' => '2003-12-25 19:15:57',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 6,
                    'specials_last_modified' => '2004-01-04 13:07:27',
                    'specials_new_products_price' => '95.0000',
                    'status' => 1,
                ),
            6 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 44,
                    'specials_date_added' => '2003-12-25 21:54:50',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 7,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            7 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 46,
                    'specials_date_added' => '2003-12-25 21:55:01',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 8,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            8 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 53,
                    'specials_date_added' => '2003-12-28 23:59:03',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 9,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '20.0000',
                    'status' => 1,
                ),
            9 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 39,
                    'specials_date_added' => '2003-12-31 02:03:59',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 10,
                    'specials_last_modified' => '2004-02-21 00:36:40',
                    'specials_new_products_price' => '75.0000',
                    'status' => 1,
                ),
            10 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 74,
                    'specials_date_added' => '2004-01-02 15:35:30',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 14,
                    'specials_last_modified' => '2004-01-02 17:38:43',
                    'specials_new_products_price' => '399.2000',
                    'status' => 1,
                ),
            11 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 59,
                    'specials_date_added' => '2004-01-03 01:51:50',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 27,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '300.0000',
                    'status' => 1,
                ),
            12 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 76,
                    'specials_date_added' => '2004-01-03 23:09:36',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 28,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '75.0000',
                    'status' => 1,
                ),
            13 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 78,
                    'specials_date_added' => '2004-01-04 01:12:14',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 29,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '112.5000',
                    'status' => 1,
                ),
            14 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 83,
                    'specials_date_added' => '2004-01-04 15:03:07',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 31,
                    'specials_last_modified' => '2004-01-06 10:02:25',
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            15 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 85,
                    'specials_date_added' => '2004-01-04 15:19:59',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 32,
                    'specials_last_modified' => '2004-01-06 09:59:59',
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            16 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 88,
                    'specials_date_added' => '2004-01-05 00:16:22',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 34,
                    'specials_last_modified' => '2004-01-06 09:59:30',
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            17 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 90,
                    'specials_date_added' => '2004-01-05 23:57:20',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 35,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            18 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 94,
                    'specials_date_added' => '2004-01-06 00:07:34',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 36,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            19 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 95,
                    'specials_date_added' => '2004-01-07 02:39:58',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 38,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            20 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 97,
                    'specials_date_added' => '2004-01-07 11:29:03',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 39,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            21 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 100,
                    'specials_date_added' => '2004-01-08 14:07:31',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 40,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '374.2500',
                    'status' => 1,
                ),
            22 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 111,
                    'specials_date_added' => '2004-01-24 16:14:19',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 42,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '90.0000',
                    'status' => 1,
                ),
            23 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'products_id' => 130,
                    'specials_date_added' => '2004-04-28 02:46:44',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 44,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '10.0000',
                    'status' => 1,
                ),
            24 =>
                array(
                    'date_status_change' => '2004-09-28 18:48:42',
                    'expires_date' => '2004-09-28',
                    'products_id' => 173,
                    'specials_date_added' => '2004-09-24 23:57:05',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 45,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '47.5000',
                    'status' => 0,
                ),
            25 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'products_id' => 166,
                    'specials_date_added' => '2004-10-03 20:24:53',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 46,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '3.0000',
                    'status' => 1,
                ),
            26 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'products_id' => 177,
                    'specials_date_added' => '2004-10-05 16:49:33',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 47,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '75.0000',
                    'status' => 1,
                ),
            27 =>
                array(
                    'date_status_change' => NULL,
                    'expires_date' => '0001-01-01',
                    'products_id' => 178,
                    'specials_date_added' => '2004-10-05 16:56:46',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 48,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '50.0000',
                    'status' => 1,
                ),
            28 =>
                array(
                    'date_status_change' => '0001-01-01 00:00:00',
                    'expires_date' => '0001-01-01',
                    'products_id' => 40,
                    'specials_date_added' => '2004-01-08 14:07:31',
                    'specials_date_available' => '0001-01-01',
                    'specials_id' => 50,
                    'specials_last_modified' => NULL,
                    'specials_new_products_price' => '75.0000',
                    'status' => 1,
                ),
        ));


    }
}
