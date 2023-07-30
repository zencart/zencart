<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(1)->create(['email' => 'admin@example.com', 'name' => 'dashboard_admin']);
        User::factory(1)->create(['email' => 'developer@example.com', 'name' => 'dashboard_developer', 'user_type' => 'developer']);
    }
}
