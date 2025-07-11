<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class AccionMenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $roles    = MenuItem::where('ruta', '/dashboard/roles')->first();

        $acciones = Accion::whereIn('nombre', [
            'crear', 'editar', 'activar'
        ])->get();

        foreach ([$usuarios, $roles] as $menu) {
            if (!$menu) continue;
            foreach ($acciones as $accion) {
                DB::table('accion_menu_item')->updateOrInsert(
                    [
                        'id_menu_item' => $menu->id,
                        'id_accion' => $accion->id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}

