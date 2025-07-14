<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Menú raíz: Dashboard (requerido para acceso general)
        $admin = MenuItem::create([
            'item' => 'Administración',
            'ruta' => '/dashboard',
            'nivel' => 1,
            'orden' => 0,
        ]);

        // Submenú: Usuarios
        MenuItem::create([
            'item' => 'Usuarios',
            'ruta' => '/dashboard/usuarios',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 1,
        ]);

        // Submenú: Roles
        MenuItem::create([
            'item' => 'Roles',
            'ruta' => '/dashboard/roles',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 2,
        ]);
        // Submenú: Personas
        MenuItem::create([
            'item' => 'Personas',
            'ruta' => '/dashboard/personas',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 3,
        ]);

        // Submenú: Protocolos
        MenuItem::create([
            'item' => 'Protocolos',
            'ruta' => '/dashboard/protocolos',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 4,
        ]);

        // Submenú: Reportes
        MenuItem::create([
            'item' => 'Reportes',
            'ruta' => '/dashboard/reportes',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 5,
        ]);
    }
}
