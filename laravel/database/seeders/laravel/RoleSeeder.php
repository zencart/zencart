<?php

namespace Database\Seeders\laravel;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'dashboard.user']);
        Role::create(['name' => 'developer.user']);
        Role::create(['name' => 'superadmin']);

        $users = User::all();
        foreach ($users as $user) {
            if ($user->user_type === 'developer') {
                $user->assignRole('developer.user');
            } else {
                $user->assignRole('dashboard.user');
            }
        }
    }
}
