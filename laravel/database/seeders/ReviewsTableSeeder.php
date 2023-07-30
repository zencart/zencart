<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class ReviewsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('reviews')->truncate();

        Capsule::table('reviews')->insert(array(
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
