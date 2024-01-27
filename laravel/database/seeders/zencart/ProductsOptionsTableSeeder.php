<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsOptionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_options')->truncate();

        DB::table('products_options')->insert(array(
            0 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 1,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Color',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 10,
                    'products_options_type' => 2,
                ),
            1 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 2,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Size',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 20,
                    'products_options_type' => 0,
                ),
            2 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 3,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Model',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 30,
                    'products_options_type' => 0,
                ),
            3 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 4,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Memory',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 50,
                    'products_options_type' => 0,
                ),
            4 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 5,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Version',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 40,
                    'products_options_type' => 0,
                ),
            5 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 6,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Media Type',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 60,
                    'products_options_type' => 0,
                ),
            6 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 7,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Logo Back',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 310,
                    'products_options_type' => 4,
                ),
            7 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => 'You may upload your own image file(s)',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 8,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Logo Front',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 300,
                    'products_options_type' => 4,
                ),
            8 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 9,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Line 2',
                    'products_options_rows' => 1,
                    'products_options_size' => 40,
                    'products_options_sort_order' => 410,
                    'products_options_type' => 1,
                ),
            9 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => 'Enter your text up to 64 characters, punctuation and spaces',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 10,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Line 1',
                    'products_options_rows' => 1,
                    'products_options_size' => 40,
                    'products_options_sort_order' => 400,
                    'products_options_type' => 1,
                ),
            10 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 11,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Line 3',
                    'products_options_rows' => 1,
                    'products_options_size' => 40,
                    'products_options_sort_order' => 420,
                    'products_options_type' => 1,
                ),
            11 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 12,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 64,
                    'products_options_name' => 'Line 4',
                    'products_options_rows' => 1,
                    'products_options_size' => 40,
                    'products_options_sort_order' => 430,
                    'products_options_type' => 1,
                ),
            12 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => 'Special Option Options Available:',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 13,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Gift Options',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 70,
                    'products_options_type' => 3,
                ),
            13 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 14,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Amount',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 200,
                    'products_options_type' => 2,
                ),
            14 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '&nbsp;',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 15,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Features',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 700,
                    'products_options_type' => 5,
                ),
            15 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 16,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Irons',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 800,
                    'products_options_type' => 3,
                ),
            16 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => NULL,
                    'products_options_comment_position' => 0,
                    'products_options_id' => 17,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Documentation',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 45,
                    'products_options_type' => 0,
                ),
            17 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 18,
                    'products_options_images_per_row' => 5,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Length',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 70,
                    'products_options_type' => 0,
                ),
            18 =>
                array(
                    'language_id' => 1,
                    'products_options_comment' => '',
                    'products_options_comment_position' => 0,
                    'products_options_id' => 19,
                    'products_options_images_per_row' => 0,
                    'products_options_images_style' => 0,
                    'products_options_length' => 32,
                    'products_options_name' => 'Shipping',
                    'products_options_rows' => 1,
                    'products_options_size' => 32,
                    'products_options_sort_order' => 600,
                    'products_options_type' => 5,
                ),
        ));


    }
}
