<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('subdominios')->insert([
            // área de impacto (id_dominio = 1)
            ['id' => 1, 'id_dominio' => 1, 'descripcion' => 'Judicial'],
            ['id' => 2, 'id_dominio' => 1, 'descripcion' => 'Cientifico'],
            ['id' => 3, 'id_dominio' => 1, 'descripcion' => 'Formativo'],

            // estado protocolo (id_dominio = 2)
            ['id' => 4, 'id_dominio' => 2, 'descripcion' => 'Activo'],
            ['id' => 5, 'id_dominio' => 2, 'descripcion' => 'En Revision'],
            ['id' => 6, 'id_dominio' => 2, 'descripcion' => 'Validado'],
            ['id' => 7, 'id_dominio' => 2, 'descripcion' => 'Archivado'],

        ]);
    }
}
