<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordCompanyTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('record_company')->truncate();

        DB::table('record_company')->insert(array(
            0 =>
                array(
                    'date_added' => '2004-07-09 14:11:52',
                    'last_modified' => NULL,
                    'record_company_id' => 1,
                    'record_company_image' => NULL,
                    'record_company_name' => 'HMV Group',
                ),
        ));


    }
}
