<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Kai',
                'password' => bcrypt('250510'),
                'role' => UserRole::SuperAdmin,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Chips',
                'password' => bcrypt('250510'),
                'role' => UserRole::Admin,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Van',
                'password' => bcrypt('250510'),
                'role' => UserRole::Cashier,
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Kai',
                'password' => bcrypt('250510'),
                'role' => UserRole::Manager,
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Kai',
                'password' => bcrypt('250510'),
                'role' => UserRole::Owner,
            ]
        );


        $tables = [
            ['name' => 'Table 1', 'token' => 'table-1'],
            ['name' => 'Table 2', 'token' => 'table-2'],
            ['name' => 'Table 3', 'token' => 'table-3'],
            ['name' => 'Window Booth', 'token' => 'window-booth'],
        ];

        foreach ($tables as $table) {
            CafeTable::firstOrCreate(['token' => $table['token']], $table);
        }

        $menu = [
            ['name' => 'Country Sourdough', 'description' => '72-hour ferment, crackly crust', 'category' => 'food', 'price' => 8.00, 'emoji' => '🍞'],
            ['name' => 'Butter Croissant', 'description' => 'Flaky layers, European butter', 'category' => 'food', 'price' => 4.00, 'emoji' => '🥐'],
            ['name' => 'Cinnamon Roll', 'description' => 'Cream cheese frosting', 'category' => 'food', 'price' => 5.00, 'emoji' => '🌀'],
            ['name' => 'Berry Tart', 'description' => 'Seasonal fruit, vanilla custard', 'category' => 'food', 'price' => 7.00, 'emoji' => '🫐'],
            ['name' => 'Iced Latte', 'description' => 'Espresso over ice with milk', 'category' => 'drinks', 'price' => 4.50, 'emoji' => '☕'],
            ['name' => 'Hot Chocolate', 'description' => 'Rich cocoa, whipped cream', 'category' => 'drinks', 'price' => 3.50, 'emoji' => '🍫'],
            ['name' => 'Lemonade', 'description' => 'Fresh squeezed, lightly sweet', 'category' => 'drinks', 'price' => 3.00, 'emoji' => '🍋'],
            ['name' => 'Matcha Latte', 'description' => 'Ceremonial grade matcha', 'category' => 'drinks', 'price' => 5.00, 'emoji' => '🍵'],
        ];

        foreach ($menu as $item) {
            MenuItem::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
