<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza segura de la tabla
        Schema::disableForeignKeyConstraints();
        DB::table('productos')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Consulta con el nombre de columna correcto: producto_generico_id
        $medicamentos = DB::table('medicamentos_liname')
            ->join('productos_genericos', 'medicamentos_liname.producto_generico_id', '=', 'productos_genericos.id')
            ->select(
                'medicamentos_liname.*', 
                'productos_genericos.nombre as nombre_base'
            )
            ->get();

        if ($medicamentos->isEmpty()) {
            $this->command->warn("No hay datos vinculados. Asegúrate de que 'liname:procesar' haya llenado las tablas.");
            return;
        }

        $productos = [];

        foreach ($medicamentos as $med) {
            // Unimos el nombre del genérico con su concentración
            $nombreCompleto = trim(($med->nombre_base ?? 'SIN NOMBRE') . ' ' . ($med->concentracion ?? ''));

            $productos[] = [
                'nombre'                => $nombreCompleto,
                'codigo_interno'        => $med->codigo_completo ?? $med->id, 
                'id_medicamento_liname' => $med->id,
                'id_unidad_venta'       => 16,     
                'id_categoria'          => 15,     
                'rastrea_inventario'    => true,
                'id_unidad_medida'      => 16,    
                'unidades_empaque'      => 1,
                'precio_entrada'        => 0.00,
                'precio_salida'         => 0.00,
                'stock_minimo'          => 10,
                'id_estado_producto'    => 23,     
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            if (count($productos) >= 100) {
                DB::table('productos')->insert($productos);
                $productos = [];
            }
        }

        if (count($productos) > 0) {
            DB::table('productos')->insert($productos);
        }

        $total = DB::table('productos')->count();
        $this->command->info("¡Listo! Se crearon {$total} productos con nombres correctos.");
    }
}