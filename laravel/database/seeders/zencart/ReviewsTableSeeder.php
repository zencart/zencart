<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('reviews')->truncate();

        DB::table('reviews')->insert(array(
            0 =>
                array(
                    'customers_id' => 1,
                    'customers_name' => 'Bill Smith',
                    'date_added' => '2003-12-23 03:18:19',
                    'last_modified' => '0001-01-01 00:00:00',
                    'products_id' => 19,
                    'reviews_id' => 1,
                    'reviews_rating' => 5,
                    'reviews_read' => 11,
                    'status' => 1,
                ),
        ));


    }
}
