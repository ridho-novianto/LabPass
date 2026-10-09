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
        // Seed in the correct order to satisfy foreign key constraints
        $this->call([
            \Database\Seeders\LabpassSeeder::class,
        ]);
    }
}
