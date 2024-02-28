<?php

namespace Database\Seeders\laravel;

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
        $s = User::factory()->create(['email' => 'admin@example.com', 'name' => 'dashboard_admin']);
        $s->assignRole('superadmin');
    }
}
