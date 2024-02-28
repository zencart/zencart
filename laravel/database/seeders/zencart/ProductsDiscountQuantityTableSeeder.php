<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsDiscountQuantityTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_discount_quantity')->truncate();

        DB::table('products_discount_quantity')->insert(array(
            0 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '10.0000',
                    'discount_qty' => 12.0,
                    'products_id' => 127,
                ),
            1 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '8.0000',
                    'discount_qty' => 9.0,
                    'products_id' => 127,
                ),
            2 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '7.0000',
                    'discount_qty' => 6.0,
                    'products_id' => 127,
                ),
            3 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '10.0000',
                    'discount_qty' => 3.0,
                    'products_id' => 8,
                ),
            4 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '5.0000',
                    'discount_qty' => 3.0,
                    'products_id' => 127,
                ),
            5 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '10.0000',
                    'discount_qty' => 12.0,
                    'products_id' => 130,
                ),
            6 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '8.0000',
                    'discount_qty' => 9.0,
                    'products_id' => 130,
                ),
            7 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '7.0000',
                    'discount_qty' => 6.0,
                    'products_id' => 130,
                ),
            8 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '5.0000',
                    'discount_qty' => 3.0,
                    'products_id' => 130,
                ),
            9 =>
                array(
                    'discount_id' => 9,
                    'discount_price' => '10.0000',
                    'discount_qty' => 9.0,
                    'products_id' => 175,
                ),
            10 =>
                array(
                    'discount_id' => 8,
                    'discount_price' => '9.0000',
                    'discount_qty' => 8.0,
                    'products_id' => 175,
                ),
            11 =>
                array(
                    'discount_id' => 7,
                    'discount_price' => '8.0000',
                    'discount_qty' => 7.0,
                    'products_id' => 175,
                ),
            12 =>
                array(
                    'discount_id' => 6,
                    'discount_price' => '7.0000',
                    'discount_qty' => 6.0,
                    'products_id' => 175,
                ),
            13 =>
                array(
                    'discount_id' => 5,
                    'discount_price' => '6.0000',
                    'discount_qty' => 5.0,
                    'products_id' => 175,
                ),
            14 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '5.0000',
                    'discount_qty' => 4.0,
                    'products_id' => 175,
                ),
            15 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '4.0000',
                    'discount_qty' => 3.0,
                    'products_id' => 175,
                ),
            16 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '3.0000',
                    'discount_qty' => 2.0,
                    'products_id' => 175,
                ),
            17 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '11.0000',
                    'discount_qty' => 10.0,
                    'products_id' => 175,
                ),
            18 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '4.0000',
                    'discount_qty' => 3.0,
                    'products_id' => 178,
                ),
            19 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '3.0000',
                    'discount_qty' => 2.0,
                    'products_id' => 178,
                ),
            20 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '11.0000',
                    'discount_qty' => 10.0,
                    'products_id' => 178,
                ),
            21 =>
                array(
                    'discount_id' => 6,
                    'discount_price' => '30.0000',
                    'discount_qty' => 36.0,
                    'products_id' => 177,
                ),
            22 =>
                array(
                    'discount_id' => 5,
                    'discount_price' => '30.0000',
                    'discount_qty' => 48.0,
                    'products_id' => 176,
                ),
            23 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '20.0000',
                    'discount_qty' => 36.0,
                    'products_id' => 176,
                ),
            24 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '10.0000',
                    'discount_qty' => 24.0,
                    'products_id' => 176,
                ),
            25 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '5.0000',
                    'discount_qty' => 12.0,
                    'products_id' => 176,
                ),
            26 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '40.0000',
                    'discount_qty' => 60.0,
                    'products_id' => 176,
                ),
            27 =>
                array(
                    'discount_id' => 5,
                    'discount_price' => '20.0000',
                    'discount_qty' => 24.0,
                    'products_id' => 177,
                ),
            28 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '10.0000',
                    'discount_qty' => 12.0,
                    'products_id' => 177,
                ),
            29 =>
                array(
                    'discount_id' => 3,
                    'discount_price' => '5.0000',
                    'discount_qty' => 6.0,
                    'products_id' => 177,
                ),
            30 =>
                array(
                    'discount_id' => 2,
                    'discount_price' => '50.0000',
                    'discount_qty' => 60.0,
                    'products_id' => 177,
                ),
            31 =>
                array(
                    'discount_id' => 1,
                    'discount_price' => '40.0000',
                    'discount_qty' => 48.0,
                    'products_id' => 177,
                ),
            32 =>
                array(
                    'discount_id' => 4,
                    'discount_price' => '5.0000',
                    'discount_qty' => 4.0,
                    'products_id' => 178,
                ),
            33 =>
                array(
                    'discount_id' => 5,
                    'discount_price' => '6.0000',
                    'discount_qty' => 5.0,
                    'products_id' => 178,
                ),
            34 =>
                array(
                    'discount_id' => 6,
                    'discount_price' => '7.0000',
                    'discount_qty' => 6.0,
                    'products_id' => 178,
                ),
            35 =>
                array(
                    'discount_id' => 7,
                    'discount_price' => '8.0000',
                    'discount_qty' => 7.0,
                    'products_id' => 178,
                ),
            36 =>
                array(
                    'discount_id' => 8,
                    'discount_price' => '9.0000',
                    'discount_qty' => 8.0,
                    'products_id' => 178,
                ),
            37 =>
                array(
                    'discount_id' => 9,
                    'discount_price' => '10.0000',
                    'discount_qty' => 9.0,
                    'products_id' => 178,
                ),
        ));


    }
}
