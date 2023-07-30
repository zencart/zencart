<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class GetTermsToFilterTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('get_terms_to_filter')->truncate();

        Capsule::table('get_terms_to_filter')->insert(array(
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
