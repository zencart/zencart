<?php

namespace Tests\Support;

class ProfileManager
{

    public static function getProfile($profileName)
    {
        $profile = [];
        $profile['admin'] = [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ];
        $profile['florida-basic1'] = [
            'firstname' => 'Dirk',
            'lastname' => 'Gently',
            'zone_country_id' => '223',
            'street_address' => '1234 Main St',
            'city' => 'Miami',
            'state' => 'Florida',
            'postcode' => '33101',
            'telephone' => '3055551212',
            'email_address' => 'dirk@example.com',
            'password' => 'password',
            'confirmation' => 'password',
        ];

        $profile['florida-basic2'] = $profile['florida-basic1'];
        $profile['florida-basic2']['email_address'] = 'dirk1@example.com';
        $profile['florida-basic2']['zone_id'] = '18';

        return $profile[$profileName];
    }
}
