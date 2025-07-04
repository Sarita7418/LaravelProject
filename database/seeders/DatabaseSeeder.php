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
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
             UrlsSeeder::class,
            MenuItemSeeder::class,
            PermisoSeeder::class,       
            PermisoRolSeeder::class,    
        ]);
    }
}
