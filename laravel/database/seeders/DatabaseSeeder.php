<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TestSeeder::class);
        $this->call(AddressBookTableSeeder::class);
        $this->call(AddressFormatTableSeeder::class);
    }
}
