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

        // Submenú: Empresas
        MenuItem::create([
            'item' => 'Empresas',
            'ruta' => '/dashboard/empresas',
            'id_padre' => 1,
            'nivel' => 3,
            'orden' => 5,
        ]);

        // Submenú: Sucursales
        MenuItem::create([
            'item' => 'Sucursales',
            'ruta' => '/dashboard/sucursales',
            'id_padre' => 1,
            'nivel' => 3,
            'orden' => 6,
        ]);

        // Submenú: Plan de Cuentas
        MenuItem::create([
            'item' => 'Plan de Cuentas',
            'ruta' => '/dashboard/plan-cuentas',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 5,
        ]);

        // Submenú: Plan Presupuestarios
        MenuItem::create([
            'item' => 'Plan Presupuestarios',
            'ruta' => '/dashboard/plan-presupuestarios',
            'id_padre' => $admin->id,
            'nivel' => 3,
            'orden' => 6,
        ]);
    }
}