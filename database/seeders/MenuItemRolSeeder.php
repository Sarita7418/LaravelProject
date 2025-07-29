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
        $usuarios       = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $roles          = MenuItem::where('ruta', '/dashboard/roles')->first();
        $personas       = MenuItem::where('ruta', '/dashboard/personas')->first();
        $protocolos     = MenuItem::where('ruta', '/dashboard/protocolos')->first();
        $reportes       = MenuItem::where('ruta', '/dashboard/reportes')->first();


        // ADMIN (ID 1) accede a todo
        foreach ([$administracion, $usuarios, $roles, $personas, $protocolos, $reportes] as $menuItem) {
            if ($menuItem) {
                MenuItemRol::create([
                    'id_rol' => 1,
                    'id_menu_item' => $menuItem->id,
                ]);
            }
        }

        // USER (ID 2) accede a Dashboard y Usuarios
        foreach ([$administracion, $usuarios, $personas] as $menuItem) {
            if ($menuItem) {
                MenuItemRol::create([
                    'id_rol' => 2,
                    'id_menu_item' => $menuItem->id,
                ]);
            }
        }
    }
}
