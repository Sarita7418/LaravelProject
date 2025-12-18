<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $representanteLegal = Persona::create([
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'González', // Puedes dejarlo como null si no es necesario
            'ci' => '12345671',  // Asegúrate de que el CI sea único
            'telefono' => '+591 4 4605000',
            'fecha_nacimiento' => '1980-01-01',  // Asignar una fecha de nacimiento
            'estado' => true,  // Activo
        ]);

        // Puedes crear más personas si es necesario
        Persona::create([
            'nombres' => 'Mauricio',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'González', // Puedes dejarlo como null si no es necesario
            'ci' => '123456477',  // Asegúrate de que el CI sea único
            'telefono' => '+591 4 46055000',
            'fecha_nacimiento' => '1980-01-01',  // Asignar una fecha de nacimiento
            'estado' => true,  // Activo
        ]);

        Persona::create([
            'nombres' => 'Cristhian',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'González', // Puedes dejarlo como null si no es necesario
            'ci' => '123456738',  // Asegúrate de que el CI sea único
            'telefono' => '+591 4 46305000',
            'fecha_nacimiento' => '1980-01-01',  // Asignar una fecha de nacimiento
            'estado' => true,  // Activo
        ]);
    }
}
