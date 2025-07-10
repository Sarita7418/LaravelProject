<?php

namespace Database\Seeders;

use App\Models\Subdominio;
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
            AccionesSeeder::class,
            MenuItemsSeeder::class,
            AccionMenuItemSeeder::class,
            MenuItemRolSeeder::class,
            DominiosSeeder::class,
            EspecialidadSeeder::class,
            SubdominiosSeeder::class,
            AccionMenuItemRolSeeder::class
        ]);
    }
}
