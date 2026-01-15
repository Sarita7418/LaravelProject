<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DominiosSeeder extends Seeder
{
    public function run()
    {
        // Es recomendable limpiar la tabla antes para evitar errores de duplicados
        // DB::table('dominios')->truncate(); 

        DB::table('dominios')->insert([
            ['id' => 1, 'descripcion' => 'estado_protocolo'],
            ['id' => 2, 'descripcion' => 'tipo_cuenta'],
            ['id' => 3, 'descripcion' => 'grupo_estado_financiero'],
            ['id' => 4, 'descripcion' => 'cuenta_ajuste_presupuesto'],
            ['id' => 5, 'descripcion' => 'categoria_producto'],
            ['id' => 6, 'descripcion' => 'unidad_medida'],
            ['id' => 7, 'descripcion' => 'estado_producto'],
            ['id' => 8, 'descripcion' => 'estado_compra'], 
            ['id' => 9, 'descripcion' => 'estado_lote'],    
            ['id' => 10, 'descripcion' => 'tipo_movimiento'],  
            ['id' => 11, 'descripcion' => 'tipo_precio'],
            ['id' => 12, 'descripcion' => 'tipo_conversion'],
            ['id' => 13, 'descripcion' => 'categoria_farmacia'],
        ]);
    }
}