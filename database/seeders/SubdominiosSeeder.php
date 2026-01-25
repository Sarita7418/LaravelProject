<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('subdominios')->insert([
            // ========== 1. ESTADO PROTOCOLO ==========
            ['id' => 1, 'id_dominio' => 1, 'descripcion' => 'Activo'],
            ['id' => 2, 'id_dominio' => 1, 'descripcion' => 'En Revision'],
            ['id' => 3, 'id_dominio' => 1, 'descripcion' => 'Archivado'],

            // ========== 2. TIPO DE CUENTA ==========
            ['id' => 4, 'id_dominio' => 2, 'descripcion' => 'Grupo'],
            ['id' => 5, 'id_dominio' => 2, 'descripcion' => 'Detalle'],

            // ========== 3. GRUPO ESTADO FINANCIERO ==========
            ['id' => 6, 'id_dominio' => 3, 'descripcion' => 'ACTIVOS'],
            ['id' => 7, 'id_dominio' => 3, 'descripcion' => 'PASIVOS'],
            ['id' => 8, 'id_dominio' => 3, 'descripcion' => 'PATRIMONIO'],
            ['id' => 9, 'id_dominio' => 3, 'descripcion' => 'INGRESOS'],
            ['id' => 10, 'id_dominio' => 3, 'descripcion' => 'EGRESOS'],

            // ========== 4. CUENTA AJUSTE ==========
            ['id' => 11, 'id_dominio' => 4, 'descripcion' => 'Sí'],
            ['id' => 12, 'id_dominio' => 4, 'descripcion' => 'No'],

            // ========== 5. CATEGORÍA PRODUCTO ==========
            ['id' => 13, 'id_dominio' => 5, 'descripcion' => 'BIEN'],
            ['id' => 14, 'id_dominio' => 5, 'descripcion' => 'SERVICIO'],

            // ========== 6. UNIDAD DE MEDIDA ==========
            ['id' => 15, 'id_dominio' => 6, 'descripcion' => 'UNIDAD'],
            ['id' => 16, 'id_dominio' => 6, 'descripcion' => 'CAJA'],
            ['id' => 17, 'id_dominio' => 6, 'descripcion' => 'BLISTER'],
            ['id' => 18, 'id_dominio' => 6, 'descripcion' => 'TABLETA'],
            ['id' => 19, 'id_dominio' => 6, 'descripcion' => 'FRASCO'],
            ['id' => 20, 'id_dominio' => 6, 'descripcion' => 'AMPOLLA'],
            ['id' => 21, 'id_dominio' => 6, 'descripcion' => 'SOBRE'],

            // ========== 7. ESTADO PRODUCTO ==========
            ['id' => 22, 'id_dominio' => 7, 'descripcion' => 'ACTIVO'],
            ['id' => 23, 'id_dominio' => 7, 'descripcion' => 'INACTIVO'],

            // ========== 8. ESTADO COMPRA ==========
            ['id' => 24, 'id_dominio' => 8, 'descripcion' => 'BORRADOR'],
            ['id' => 25, 'id_dominio' => 8, 'descripcion' => 'CONFIRMADO'],
            ['id' => 26, 'id_dominio' => 8, 'descripcion' => 'ANULADO'],

            // ========== 9. ESTADO LOTE ==========
            ['id' => 27, 'id_dominio' => 9, 'descripcion' => 'ACTIVO'],
            ['id' => 28, 'id_dominio' => 9, 'descripcion' => 'AGOTADO'],
            ['id' => 29, 'id_dominio' => 9, 'descripcion' => 'DAÑADO'],

            // ========== 10. TIPO MOVIMIENTO ==========
            ['id' => 30, 'id_dominio' => 10, 'descripcion' => 'COMPRA'],
            ['id' => 31, 'id_dominio' => 10, 'descripcion' => 'SALIDA'],
            ['id' => 32, 'id_dominio' => 10, 'descripcion' => 'AJUSTE'],

            // ========== 11. TIPO PRECIO ==========
            ['id' => 33, 'id_dominio' => 11, 'descripcion' => 'ENTRADA'],
            ['id' => 34, 'id_dominio' => 11, 'descripcion' => 'SALIDA'],

            // ========== 12. TIPO DE CONVERSIÓN ==========
            ['id' => 35, 'id_dominio' => 12, 'descripcion' => 'CONVERSION_COMPRA'],  
            ['id' => 36, 'id_dominio' => 12, 'descripcion' => 'CONVERSION_VENTA'],

            // ========== 13. CATEGORÍA FARMACIA ==========
            ['id' => 37, 'id_dominio' => 13, 'descripcion' => 'MEDICAMENTO CONTROLADO'],
            ['id' => 38, 'id_dominio' => 13, 'descripcion' => 'MEDICAMENTO COMÚN'],
            ['id' => 39, 'id_dominio' => 13, 'descripcion' => 'INSUMO MÉDICO'],
        ]);
    }
}
