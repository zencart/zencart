<?php

namespace Database\Seeders\laravel;

use App\Models\Developer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect(range(1,3))->each(fn($i) => Developer::factory()->inactive()->for(User::factory()->withSequencedEmail($i)->unverified()->create(['user_type' => 'developer']))->create());
    }
}
