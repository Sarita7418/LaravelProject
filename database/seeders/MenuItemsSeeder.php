<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Menú raíz: Administración (Dashboard principal)
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
            'nivel' => 2,
            'orden' => 1,
        ]);

        // Submenú: Roles
        MenuItem::create([
            'item' => 'Roles',
            'ruta' => '/dashboard/roles',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 2,
        ]);

        // Submenú: Personas
        MenuItem::create([
            'item' => 'Personas',
            'ruta' => '/dashboard/personas',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 3,
        ]);

        // Submenú: Protocolos
        MenuItem::create([
            'item' => 'Protocolos',
            'ruta' => '/dashboard/protocolos',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 4,
        ]);

        // Submenú: Reportes
        MenuItem::create([
            'item' => 'Reportes',
            'ruta' => '/dashboard/reportes',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 5,
        ]);

        // Submenú: Empresas
        MenuItem::create([
            'item' => 'Empresas',
            'ruta' => '/dashboard/empresas',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 6,
        ]);

        // Submenú: Sucursales
        MenuItem::create([
            'item' => 'Sucursales',
            'ruta' => '/dashboard/sucursales',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 7,
        ]);

        // Submenú: Plan de Cuentas
        MenuItem::create([
            'item' => 'Plan de Cuentas',
            'ruta' => '/dashboard/plan-cuentas',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 8,
        ]);

        // Submenú: Plan Presupuestarios
        MenuItem::create([
            'item' => 'Plan Presupuestarios',
            'ruta' => '/dashboard/plan-presupuestarios',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 9,
        ]);
        
        // Submenú: Comprobantes
        MenuItem::create([
            'item' => 'Comprobantes',
            'ruta' => '/dashboard/comprobantes',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 10,
        ]);
         MenuItem::create([
            'item' => 'Facturas',
            'ruta' => '/dashboard/facturas',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 11,
        ]);

        // Submenú: Catalogo Médicos
        MenuItem::create([
            'item' => 'Catálogo Médicos',
            'ruta' => '/catalogo-medicos',
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 12,  
        ]);
 
        $inventarios = MenuItem::create([
            'item' => 'Inventarios',
            'ruta' => '#', // Categoría padre sin enlace directo
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 13,
        ]);

        // Submenú: Compras
        MenuItem::create([
            'item' => 'Compras',
            'ruta' => '/dashboard/compras',
            'id_padre' => $inventarios->id,
            'nivel' => 3,
            'orden' => 1,
        ]);


        
        $ventasfacturacion = MenuItem::create([
            'item' => 'Ventas y Facturación',
            'ruta' => '#', // Categoría padre sin enlace directo
            'id_padre' => $admin->id,
            'nivel' => 2,
            'orden' => 14,
        ]);

        MenuItem::create([
            'item' => 'Ventas',
            'ruta' => '/dashboard/ventas',
            'id_padre' => $ventasfacturacion->id,
            'nivel' => 3,
            'orden' => 1,
        ]);
        MenuItem::create([
            'item' => 'Historial Ventas',
            'ruta' => '/dashboard/historial-ventas',
            'id_padre' => $ventasfacturacion->id,
            'nivel' => 3,
            'orden' => 2,
        ]);


    }
}