<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuItemRol;

class MenuItemRolSeeder extends Seeder
{
    public function run(): void
    {
        $administracion = MenuItem::where('item', 'Administración')->first();
        $usuarios = MenuItem::where('ruta', '/usuarios')->first();
        $roles = MenuItem::where('ruta', '/roles')->first();

        // ADMIN (ID 1) accede a todo
        foreach ([$administracion, $usuarios, $roles] as $menuItem) {
            MenuItemRol::create([
                'id_rol' => 1,
                'id_menu_item' => $menuItem->id,
            ]);
        }

        // USER (ID 2) accede a Administración y Usuarios
        foreach ([$administracion, $usuarios] as $menuItem) {
            MenuItemRol::create([
                'id_rol' => 2,
                'id_menu_item' => $menuItem->id,
            ]);
        }
    }
}
