<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        DB::table('productos')->insert([
            [
                'nombre' => 'Arroz Extra',
                'codigo_interno' => 'PROD-001',
                'codigo_barras' => '1234567890123',
                'id_categoria' => 13, // BIEN
                'rastrea_inventario' => true,
                'id_unidad_medida' => 17, // KILOGRAMO
                'precio_entrada' => 6.50,
                'precio_salida' => 8.00,
                'stock_minimo' => 10,
                'id_estado_producto' => 22, // ACTIVO
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Agua Mineral 2L',
                'codigo_interno' => 'PROD-002',
                'codigo_barras' => '7894561230987',
                'id_categoria' => 13, // BIEN
                'rastrea_inventario' => true,
                'id_unidad_medida' => 18, // LITRO
                'precio_entrada' => 4.00,
                'precio_salida' => 6.00,
                'stock_minimo' => 20,
                'id_estado_producto' => 22, // ACTIVO
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Servicio de Instalación',
                'codigo_interno' => 'SERV-001',
                'codigo_barras' => null,
                'id_categoria' => 14, // SERVICIO
                'rastrea_inventario' => false,
                'id_unidad_medida' => 15, // UNIDAD
                'precio_entrada' => 0.00,
                'precio_salida' => 150.00,
                'stock_minimo' => 0,
                'id_estado_producto' => 22, // ACTIVO
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Caja de Tornillos',
                'codigo_interno' => 'PROD-003',
                'codigo_barras' => '4561237896541',
                'id_categoria' => 13, // BIEN
                'rastrea_inventario' => true,
                'id_unidad_medida' => 16, // CAJA
                'precio_entrada' => 30.00,
                'precio_salida' => 45.00,
                'stock_minimo' => 5,
                'id_estado_producto' => 22, // ACTIVO
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Producto Descontinuado',
                'codigo_interno' => 'PROD-004',
                'codigo_barras' => null,
                'id_categoria' => 13, // BIEN
                'rastrea_inventario' => true,
                'id_unidad_medida' => 15, // UNIDAD
                'precio_entrada' => 12.00,
                'precio_salida' => 18.00,
                'stock_minimo' => 0,
                'id_estado_producto' => 23, // INACTIVO
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
