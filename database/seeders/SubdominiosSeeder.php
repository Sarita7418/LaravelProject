<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('subdominios')->insert([
            // Estado protocolo (ya existente)
            ['id' => 1, 'id_dominio' => 1, 'descripcion' => 'Activo'],
            ['id' => 2, 'id_dominio' => 1, 'descripcion' => 'En Revision'],
            ['id' => 3, 'id_dominio' => 1, 'descripcion' => 'Archivado'],
            
            // Tipo de cuenta
            ['id' => 4, 'id_dominio' => 2, 'descripcion' => 'Grupo'],
            ['id' => 5, 'id_dominio' => 2, 'descripcion' => 'Detalle'],
            
            // Grupo Estado Financiero
            ['id' => 6, 'id_dominio' => 3, 'descripcion' => 'ACTIVOS'],
            ['id' => 7, 'id_dominio' => 3, 'descripcion' => 'PASIVOS'],
            ['id' => 8, 'id_dominio' => 3, 'descripcion' => 'PATRIMONIO'],
            ['id' => 9, 'id_dominio' => 3, 'descripcion' => 'INGRESOS'],
            ['id' => 10, 'id_dominio' => 3, 'descripcion' => 'EGRESOS'],
            
            // Cuenta Ajuste
            ['id' => 11, 'id_dominio' => 4, 'descripcion' => 'Sí'],
            ['id' => 12, 'id_dominio' => 4, 'descripcion' => 'No'],
            // Categoría Producto
            ['id' => 13, 'id_dominio' => 5, 'descripcion' => 'BIEN'],
            ['id' => 14, 'id_dominio' => 5, 'descripcion' => 'SERVICIO'],
            
            // Unidad de Medida
            ['id' => 15, 'id_dominio' => 6, 'descripcion' => 'UNIDAD'],
            ['id' => 16, 'id_dominio' => 6, 'descripcion' => 'CAJA'],
            ['id' => 17, 'id_dominio' => 6, 'descripcion' => 'KILOGRAMO'],
            ['id' => 18, 'id_dominio' => 6, 'descripcion' => 'LITRO'],
            ['id' => 19, 'id_dominio' => 6, 'descripcion' => 'METRO'],
            ['id' => 20, 'id_dominio' => 6, 'descripcion' => 'DOCENA'],
            ['id' => 21, 'id_dominio' => 6, 'descripcion' => 'PAQUETE'],
            
            // Estado Producto
            ['id' => 22, 'id_dominio' => 7, 'descripcion' => 'ACTIVO'],
            ['id' => 23, 'id_dominio' => 7, 'descripcion' => 'INACTIVO'],
            
            // Estado Compra
            ['id' => 24, 'id_dominio' => 8, 'descripcion' => 'BORRADOR'],
            ['id' => 25, 'id_dominio' => 8, 'descripcion' => 'CONFIRMADO'],
            ['id' => 26, 'id_dominio' => 8, 'descripcion' => 'ANULADO'],

            // Estado Lote
            ['id' => 27, 'id_dominio' => 9, 'descripcion' => 'ACTIVO'],
            ['id' => 28, 'id_dominio' => 9, 'descripcion' => 'AGOTADO'],
            ['id' => 29, 'id_dominio' => 9, 'descripcion' => 'DAÑADO'],

            // Tipo Movimiento
            ['id' => 30, 'id_dominio' => 10, 'descripcion' => 'COMPRA'],
            ['id' => 31, 'id_dominio' => 10, 'descripcion' => 'SALIDA'],
            ['id' => 32, 'id_dominio' => 10, 'descripcion' => 'AJUSTE'],
        ]);
    }
}
