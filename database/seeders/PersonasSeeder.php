<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonasSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
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
=======
        Persona::firstOrCreate(
            ['ci' => '12345678'],
            [
                'nombres' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'González',
                'telefono' => '+591 4 4605000',
                'fecha_nacimiento' => '1980-01-01',
                'estado' => true,
            ]
        );

        Persona::firstOrCreate(
            ['ci' => '87654321'],
            [
                'nombres' => 'Mauricio',
                'apellido_paterno' => 'Lopez',
                'apellido_materno' => 'Rojas',
                'telefono' => '+591 2 2845000',
                'fecha_nacimiento' => '1978-05-10',
                'estado' => true,
            ]
        );
>>>>>>> 3285c9960249b3394b7fac2c78eddd598ea8080a

        Persona::firstOrCreate(
            ['ci' => '11223344'],
            [
                'nombres' => 'Cristhian',
                'apellido_paterno' => 'Vargas',
                'apellido_materno' => 'Mamani',
                'telefono' => '+591 2 2110000',
                'fecha_nacimiento' => '1985-03-15',
                'estado' => true,
            ]
        );

        Persona::firstOrCreate(
            ['ci' => '55667788'],
            [
                'nombres' => 'Andrea',
                'apellido_paterno' => 'Salinas',
                'apellido_materno' => 'Torrez',
                'telefono' => '+591 2 2799000',
                'fecha_nacimiento' => '1982-08-22',
                'estado' => true,
            ]
        );

        Persona::firstOrCreate(
            ['ci' => '99887766'],
            [
                'nombres' => 'Fernando',
                'apellido_paterno' => 'Arce',
                'apellido_materno' => 'Quispe',
                'telefono' => '+591 4 4250000',
                'fecha_nacimiento' => '1975-12-02',
                'estado' => true,
            ]
        );
    }
}
