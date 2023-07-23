<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsAttributesDownloadTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_attributes_download')->truncate();

        DB::table('products_attributes_download')->insert(array(
            0 =>
                array(
                    'products_attributes_filename' => 'unreal.zip',
                    'products_attributes_id' => 26,
                    'products_attributes_maxcount' => 3,
                    'products_attributes_maxdays' => 7,
                ),
            1 =>
                array(
                    'products_attributes_filename' => 'test.zip',
                    'products_attributes_id' => 1040,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            2 =>
                array(
                    'products_attributes_filename' => 'test2.zip',
                    'products_attributes_id' => 1041,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            3 =>
                array(
                    'products_attributes_filename' => 'test2.zip',
                    'products_attributes_id' => 1042,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            4 =>
                array(
                    'products_attributes_filename' => 'test.zip',
                    'products_attributes_id' => 1043,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            5 =>
                array(
                    'products_attributes_filename' => 'test.zip',
                    'products_attributes_id' => 1044,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            6 =>
                array(
                    'products_attributes_filename' => 'ms_word_sample.zip',
                    'products_attributes_id' => 1088,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            7 =>
                array(
                    'products_attributes_filename' => 'pdf_sample.zip',
                    'products_attributes_id' => 1089,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            8 =>
                array(
                    'products_attributes_filename' => 'test.zip',
                    'products_attributes_id' => 1093,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            9 =>
                array(
                    'products_attributes_filename' => 'test2.zip',
                    'products_attributes_id' => 1094,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            10 =>
                array(
                    'products_attributes_filename' => 'ms_word_sample.zip',
                    'products_attributes_id' => 1100,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            11 =>
                array(
                    'products_attributes_filename' => 'pdf_sample.zip',
                    'products_attributes_id' => 1103,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
            12 =>
                array(
                    'products_attributes_filename' => 'pdf_sample.zip',
                    'products_attributes_id' => 1105,
                    'products_attributes_maxcount' => 5,
                    'products_attributes_maxdays' => 7,
                ),
        ));


    }
}
