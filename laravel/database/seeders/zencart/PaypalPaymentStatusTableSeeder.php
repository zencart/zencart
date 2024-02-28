<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaypalPaymentStatusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('paypal_payment_status')->truncate();

        DB::table('paypal_payment_status')->insert(array(
            0 =>
                array(
                    'payment_status_id' => 1,
                    'payment_status_name' => 'Completed',
                ),
            1 =>
                array(
                    'payment_status_id' => 2,
                    'payment_status_name' => 'Pending',
                ),
            2 =>
                array(
                    'payment_status_id' => 3,
                    'payment_status_name' => 'Failed',
                ),
            3 =>
                array(
                    'payment_status_id' => 4,
                    'payment_status_name' => 'Denied',
                ),
            4 =>
                array(
                    'payment_status_id' => 5,
                    'payment_status_name' => 'Refunded',
                ),
            5 =>
                array(
                    'payment_status_id' => 6,
                    'payment_status_name' => 'Canceled_Reversal',
                ),
            6 =>
                array(
                    'payment_status_id' => 7,
                    'payment_status_name' => 'Reversed',
                ),
        ));


    }
}
