<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressFormatTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('address_format')->truncate();

        DB::table('address_format')->insert(array(
            0 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country',
                    'address_format_id' => 1,
                    'address_summary' => 'Default $city, $postcode / $state, $country',
                ),
            1 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country',
                    'address_format_id' => 2,
                    'address_summary' => 'city, $state $postcode',
                ),
            2 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country',
                    'address_format_id' => 3,
                    'address_summary' => 'Historic $city / $postcode - $statecomma$country',
                ),
            3 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country',
                    'address_format_id' => 4,
                    'address_summary' => 'Historic $city ($postcode)',
                ),
            4 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode $city$cr$country',
                    'address_format_id' => 5,
                    'address_summary' => 'postcode $city',
                ),
            5 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country',
                    'address_format_id' => 6,
                    'address_summary' => '$city / $state / $postcode',
                ),
            6 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country',
                    'address_format_id' => 7,
                    'address_summary' => '$city $state $postcode',
                ),
            7 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city$cr$country',
                    'address_format_id' => 8,
                    'address_summary' => '$city',
                ),
            8 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode $city $state$cr$country',
                    'address_format_id' => 9,
                    'address_summary' => '$postcode $city $state',
                ),
            9 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city $postcode$cr$country',
                    'address_format_id' => 10,
                    'address_summary' => '$city $postcode',
                ),
            10 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city $state$cr$postcode$cr$country',
                    'address_format_id' => 11,
                    'address_summary' => '$city $state / $postcode',
                ),
            11 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode$cr$city $state$cr$country',
                    'address_format_id' => 12,
                    'address_summary' => '$postcode / $city / $state',
                ),
            12 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city $postcode$cr$state$cr$country',
                    'address_format_id' => 13,
                    'address_summary' => '$city $postcode / $state',
                ),
            13 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode $city$cr$state$cr$country',
                    'address_format_id' => 14,
                    'address_summary' => '$postcode $city / $state',
                ),
            14 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode$cr$city$cr$state$cr$country',
                    'address_format_id' => 15,
                    'address_summary' => '$postcode / $city / $state',
                ),
            15 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city $postcode $state$cr$country',
                    'address_format_id' => 16,
                    'address_summary' => ' $city $postcode $state',
                ),
            16 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city$cr$postcode $state$cr$country',
                    'address_format_id' => 17,
                    'address_summary' => ' $city / $postcode $state',
                ),
            17 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$city$cr$state $postcode$cr$country',
                    'address_format_id' => 18,
                    'address_summary' => '$city / $state $postcode',
                ),
            18 =>
                array(
                    'address_format' => '$firstname $lastname$cr$city$cr$streets$cr$postcode$cr$country',
                    'address_format_id' => 19,
                    'address_summary' => '$city $street / $postcode',
                ),
            19 =>
                array(
                    'address_format' => '$firstname $lastname$cr$streets$cr$postcode $city ($state)$cr$country',
                    'address_format_id' => 20,
                    'address_summary' => '$postcode $city ($state)',
                ),
        ));


    }
}
