<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadesMedicasSeeder extends Seeder
{
   public function run(): void
    {
        //
        DB::table('especialidades_medicas')->insert([
            ['nombre' => 'Cardiología', 'descripcion' => 'Especialista en enfermedades del corazón'],
            ['nombre' => 'Psiquiatría', 'descripcion' => 'Especialista en salud mental y psicotrópicos'],
            ['nombre' => 'Medicina General', 'descripcion' => 'Atención médica general'],
        ]);
    }
}
