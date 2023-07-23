<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewsDescriptionTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('reviews_description')->truncate();

        DB::table('reviews_description')->insert(array(
            0 =>
                array(
                    'languages_id' => 1,
                    'reviews_id' => 1,
                    'reviews_text' => 'This really is a very funny but old movie!',
                ),
        ));


    }
}
