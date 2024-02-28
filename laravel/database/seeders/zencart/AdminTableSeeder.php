<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('admin')->truncate();

        DB::table('admin')->insert(array (
            0 =>
                array (
                    'admin_id' => 1,
                    'admin_name' => 'Admin',
                    'admin_email' => 'admin@localhost',
                    'admin_profile' => 1,
                    'admin_pass' => password_hash('password', PASSWORD_DEFAULT),
                    'prev_pass1' => '',
                    'prev_pass2' => '',
                    'prev_pass3' => '',
                    'reset_token' => '',
                    'last_modified' => Carbon::now()->format('Y-m-d H:i:s'),
                    'last_login_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'pwd_last_change_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'last_login_ip' => '',
                    'failed_logins' => 0,
                    'lockout_expires' => 0,
                    'last_failed_attempt' => '0001-01-01 00:00:00',
                    'last_failed_ip' => '',
                ),
        ));
    }
}
