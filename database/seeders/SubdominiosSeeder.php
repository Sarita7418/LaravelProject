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
        ]);
    }
}
