<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);

        // Execute SQL seed files
        $sqlFiles = [
            'users.sql',
            'cafe_tables.sql',
            'menu_items.sql',
            'inventory_categories.sql',
            'products.sql',
        ];

        foreach ($sqlFiles as $file) {
            $path = database_path('seeders/sql/' . $file);
            
            if (File::exists($path)) {
                $sql = File::get($path);
                DB::unprepared($sql);
                $this->command->info("Seeded: " . $file);
            } else {
                $this->command->error("SQL file not found: " . $file);
            }
        }
    }
}
