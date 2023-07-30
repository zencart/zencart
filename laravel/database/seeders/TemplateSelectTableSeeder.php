<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TemplateSelectTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('template_select')->truncate();

        Capsule::table('template_select')->insert(array(
            0 =>
                array(
                    'template_dir' => 'responsive_classic',
                    'template_id' => 1,
                    'template_language' => '0',
                ),
        ));


    }
}
