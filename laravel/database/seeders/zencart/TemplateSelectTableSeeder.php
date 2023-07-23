<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSelectTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('template_select')->truncate();

        DB::table('template_select')->insert(array(
            0 =>
                array(
                    'template_dir' => 'responsive_classic',
                    'template_id' => 1,
                    'template_language' => '0',
                ),
        ));


    }
}
