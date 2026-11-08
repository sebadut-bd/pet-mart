<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            ModuleSeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
