<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('medicos')->insert([
            [
                'nombre' => 'Dr. Juan Pérez',
                'matricula' => '12345',
                'id_especialidad' => 1, // Cardiología
            ],
            [
                'nombre' => 'Dra. Ana López',
                'matricula' => '67890',
                'id_especialidad' => 2, // Psiquiatría
            ],
            [
                'nombre' => 'Dr. Carlos Ruiz',
                'matricula' => '54321',
                'id_especialidad' => 3, // Medicina General
            ],
        ]);
    }
}
