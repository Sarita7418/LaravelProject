<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear menú padre
        $admin = MenuItem::create([
            'item' => 'Administración',
            'ruta' => '#', // No tiene ruta porque es contenedor
            'nivel' => 1,
            'orden' => 1,
        ]);

        // Submenú: Usuarios
        MenuItem::create([
            'item' => 'Usuarios',
            'ruta' => '/usuarios',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 1,
        ]);

        // Submenú: Roles
        MenuItem::create([
            'item' => 'Roles',
            'ruta' => '/roles',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 2,
        ]);
    }
}
