<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoMedicosMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Obtener el orden máximo actual de elementos padre (nivel 1)
        $maxOrden = DB::table('menu_items')
            ->where('nivel', 1)
            ->whereNull('id_padre')
            ->max('orden') ?? 0;

        // 2. Insertar "Catálogo de médicos" como elemento padre
        $menuId = DB::table('menu_items')->insertGetId([
            'item' => 'Catálogo de médicos',
            'ruta' => '#',           // Sin ruta porque es padre
            'id_padre' => null,      // NULL = es elemento padre
            'nivel' => 1,            // Nivel principal
            'orden' => $maxOrden + 1, // Después de los existentes
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Obtener todos los roles
        $roles = DB::table('roles')->pluck('id');

        // 4. Asignar el menú a todos los roles
        foreach ($roles as $rolId) {
            DB::table('menu_item_rol')->insert([
                'menu_item_id' => $menuId,
                'rol_id' => $rolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Menú 'Catálogo de médicos' creado con ID: {$menuId}");
        $this->command->info("Asignado a " . count($roles) . " roles");
    }
}