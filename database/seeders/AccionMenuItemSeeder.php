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
        // Busca los menús por su ruta (ajusta si usas otro campo)
        $usuarios = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $roles    = MenuItem::where('ruta', '/dashboard/roles')->first();

        // Acciones a vincular
        $acciones = Accion::whereIn('nombre', [
            'crear', 'editar', 'activar'
        ])->get();

        foreach ([$usuarios, $roles] as $menu) {
            if (!$menu) continue;
            foreach ($acciones as $accion) {
                // Inserta en la tabla pivote
                DB::table('accion_menu_item')->updateOrInsert([
                    'id_menu_item' => $menu->id,
                    'id_accion' => $accion->id
                ]);
            }
        }
    }
}
