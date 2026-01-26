<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuItemRol;

class MenuItemRolSeeder extends Seeder
{
    public function run(): void
    {
        $administracion = MenuItem::where('ruta', '/dashboard')->first();
        $usuarios = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $roles = MenuItem::where('ruta', '/dashboard/roles')->first();
        $personas = MenuItem::where('ruta', '/dashboard/personas')->first();
        $protocolos = MenuItem::where('ruta', '/dashboard/protocolos')->first();
        $planCuentas = MenuItem::where('ruta', '/dashboard/plan-cuentas')->first();
        $planPresupuestarios = MenuItem::where('ruta', '/dashboard/plan-presupuestarios')->first();
        $sucursales = MenuItem::where('ruta', '/dashboard/sucursales')->first();
        $empresas = MenuItem::where('ruta', '/dashboard/empresas')->first();
        $reportes = MenuItem::where('ruta', '/dashboard/reportes')->first();
        $comprobantes = MenuItem::where('ruta', '/dashboard/comprobantes')->first();
        $facturas = MenuItem::where('ruta', '/dashboard/facturas')->first();
        $catalogoMedicos = MenuItem::where('item', 'Catálogo de Médicos')->first();

        $inventarios = MenuItem::where('item', 'Inventarios')->first();
        $compras = MenuItem::where('ruta', '/dashboard/compras')->first();

        $ventasfacturacion = MenuItem::where('item', 'Ventas y Facturación')->first();
        $ventas = MenuItem::where('ruta', '/dashboard/ventas')->first();
        $historialVentas = MenuItem::where('ruta', '/dashboard/historial-ventas')->first();

        // ADMIN (ID 1) accede a todo
        foreach ([
            $administracion, $usuarios, $roles, $personas, $protocolos, 
            $sucursales, $empresas, $reportes, $comprobantes,
            $planCuentas, $planPresupuestarios,
            $inventarios, $compras, $facturas, $catalogoMedicos, $ventas, $historialVentas, $ventasfacturacion
        ] as $menuItem) {
            if ($menuItem) {
                MenuItemRol::create([
                    'id_rol' => 1,
                    'id_menu_item' => $menuItem->id,
                ]);
            }
        }
        foreach ([
            $administracion, $usuarios, $personas, 
            $planCuentas, $planPresupuestarios,
            $inventarios,$ventasfacturacion
        ] as $menuItem) {
            if ($menuItem) {
                MenuItemRol::create([
                    'id_rol' => 2,
                    'id_menu_item' => $menuItem->id,
                ]);
            }
        }
    }
}