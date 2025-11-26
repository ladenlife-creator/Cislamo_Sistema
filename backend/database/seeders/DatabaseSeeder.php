<?php

namespace Database\Seeders;

use Database\Seeders\Library\LibrarySeeder;
use Database\Seeders\Financial\FinancialSeeder;
use Database\Seeders\permissions\LibraryPermissionsSeeder;
use Database\Seeders\permissions\FinancialPermissionsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DocumentSeeder::class,
            EventSeeder::class,
            EventParticipantSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
    }
}
