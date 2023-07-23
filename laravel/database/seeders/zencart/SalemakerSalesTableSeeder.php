<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalemakerSalesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('salemaker_sales')->truncate();

        DB::table('salemaker_sales')->insert(array(
            0 =>
                array(
                    'sale_categories_all' => ',25,28,45,47,58,',
                    'sale_categories_selected' => '25,28,45,47,58',
                    'sale_date_added' => '2003-12-23',
                    'sale_date_end' => '2008-02-21',
                    'sale_date_last_modified' => '2004-05-18',
                    'sale_date_start' => '2003-12-23',
                    'sale_date_status_change' => '2023-06-29',
                    'sale_deduction_type' => 1,
                    'sale_deduction_value' => '10.0000',
                    'sale_id' => 1,
                    'sale_name' => '10% off Sale',
                    'sale_pricerange_from' => '1.0000',
                    'sale_pricerange_to' => '1000.0000',
                    'sale_specials_condition' => 2,
                    'sale_status' => 0,
                ),
            1 =>
                array(
                    'sale_categories_all' => ',9,',
                    'sale_categories_selected' => '9',
                    'sale_date_added' => '2003-12-31',
                    'sale_date_end' => '2004-04-21',
                    'sale_date_last_modified' => '2003-12-31',
                    'sale_date_start' => '2003-12-24',
                    'sale_date_status_change' => '2004-04-25',
                    'sale_deduction_type' => 1,
                    'sale_deduction_value' => '20.0000',
                    'sale_id' => 3,
                    'sale_name' => 'Mice 20%',
                    'sale_pricerange_from' => '1.0000',
                    'sale_pricerange_to' => '1000.0000',
                    'sale_specials_condition' => 2,
                    'sale_status' => 0,
                ),
            2 =>
                array(
                    'sale_categories_all' => ',27,',
                    'sale_categories_selected' => '27',
                    'sale_date_added' => '2004-01-04',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-01-05',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-04',
                    'sale_deduction_type' => 0,
                    'sale_deduction_value' => '5.0000',
                    'sale_id' => 6,
                    'sale_name' => '$5.00 off',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 2,
                    'sale_status' => 1,
                ),
            3 =>
                array(
                    'sale_categories_all' => ',31,',
                    'sale_categories_selected' => '31',
                    'sale_date_added' => '2004-01-04',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-05-18',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-04',
                    'sale_deduction_type' => 1,
                    'sale_deduction_value' => '10.0000',
                    'sale_id' => 7,
                    'sale_name' => '10% Skip Specials',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 1,
                    'sale_status' => 1,
                ),
            4 =>
                array(
                    'sale_categories_all' => ',32,',
                    'sale_categories_selected' => '32',
                    'sale_date_added' => '2004-01-05',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-05-18',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-05',
                    'sale_deduction_type' => 1,
                    'sale_deduction_value' => '10.0000',
                    'sale_id' => 8,
                    'sale_name' => '10% Apply to Price',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 0,
                    'sale_status' => 1,
                ),
            5 =>
                array(
                    'sale_categories_all' => ',46,',
                    'sale_categories_selected' => '46',
                    'sale_date_added' => '2004-01-06',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-01-07',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-06',
                    'sale_deduction_type' => 2,
                    'sale_deduction_value' => '100.0000',
                    'sale_id' => 9,
                    'sale_name' => 'New Price $100',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 2,
                    'sale_status' => 1,
                ),
            6 =>
                array(
                    'sale_categories_all' => ',51,',
                    'sale_categories_selected' => '51',
                    'sale_date_added' => '2004-01-07',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-01-07',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-07',
                    'sale_deduction_type' => 2,
                    'sale_deduction_value' => '100.0000',
                    'sale_id' => 10,
                    'sale_name' => 'New Price $100 Skip Special',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 1,
                    'sale_status' => 1,
                ),
            7 =>
                array(
                    'sale_categories_all' => ',52,',
                    'sale_categories_selected' => '52',
                    'sale_date_added' => '2004-01-24',
                    'sale_date_end' => '0001-01-01',
                    'sale_date_last_modified' => '2004-01-24',
                    'sale_date_start' => '0001-01-01',
                    'sale_date_status_change' => '2004-01-24',
                    'sale_deduction_type' => 0,
                    'sale_deduction_value' => '5.0000',
                    'sale_id' => 11,
                    'sale_name' => '$5.00 off Skip Specials',
                    'sale_pricerange_from' => '0.0000',
                    'sale_pricerange_to' => '0.0000',
                    'sale_specials_condition' => 1,
                    'sale_status' => 1,
                ),
        ));


    }
}
