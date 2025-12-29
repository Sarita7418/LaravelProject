<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Persona::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Lopez',
        'ci' => '87654321',
        'telefono' => '78945612',
        'fecha_nacimiento' => '1990-01-01',
        'estado' => 1,
    ]);
    }
}
