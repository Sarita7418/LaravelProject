<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('subdominios')->insert([
            ['id' => 1, 'id_dominio' => 1, 'descripcion' => 'Activo'],
            ['id' => 2, 'id_dominio' => 1, 'descripcion' => 'En Revision'],
            ['id' => 3, 'id_dominio' => 1, 'descripcion' => 'Archivado'],

        ]);
    }
}
