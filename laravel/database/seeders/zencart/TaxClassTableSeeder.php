<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxClassTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('tax_class')->truncate();

        DB::table('tax_class')->insert(array(
            0 =>
                array(
                    'date_added' => '2023-06-29 12:14:04',
                    'last_modified' => NULL,
                    'tax_class_description' => 'The following types of products are included: non-food, services, etc',
                    'tax_class_id' => 1,
                    'tax_class_title' => 'Taxable Goods',
                ),
        ));


    }
}
