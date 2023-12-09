<?php

namespace Tests\Support\helpers;

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

        $profile['US-not-florida-basic'] = [
            'firstname' => 'Dirk',
            'lastname' => 'Gently',
            'zone_country_id' => '223',
            'street_address' => '1234 Main St',
            'city' => 'Albuquerque',
            'state' => 'New Mexico',
            'postcode' => '87101',
            'telephone' => '3055551212',
            'email_address' => 'dirk2@example.com',
            'password' => 'password',
            'confirmation' => 'password',
            'zone_id' => '42',
        ];

        return $profile[$profileName];
    }

    public static function getProfileForLogin($profileName)
    {
        $profile = self::getProfile($profileName);
        $emailProfile = [];
        $emailProfile['email_address'] = $profile['email_address'];
        $emailProfile['password'] = $profile['password'];
        return $emailProfile;
    }
}
