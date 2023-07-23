<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GetTermsToFilterTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('get_terms_to_filter')->truncate();

        DB::table('get_terms_to_filter')->insert(array(
            0 =>
                array(
                    'get_term_name' => 'manufacturers_id',
                    'get_term_name_field' => 'manufacturers_name',
                    'get_term_table' => 'TABLE_MANUFACTURERS',
                ),
            1 =>
                array(
                    'get_term_name' => 'music_genre_id',
                    'get_term_name_field' => 'music_genre_name',
                    'get_term_table' => 'TABLE_MUSIC_GENRE',
                ),
            2 =>
                array(
                    'get_term_name' => 'record_company_id',
                    'get_term_name_field' => 'record_company_name',
                    'get_term_table' => 'TABLE_RECORD_COMPANY',
                ),
        ));


    }
}
