<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run()
    {
        // Crear empresa de ejemplo
        Empresa::create([
            'razon_social' => 'PIL Andina S.A.',
            'nombre_comercial' => 'PIL Andina',
            'nit' => '1025401019',
            'matricula_comercio' => '123456789',
            'direccion_fiscal' => 'Av. Panamericana, km 15, Cochabamba, Bolivia',
            'telefono' => '+591 4 4605000',
            'email' => 'contacto@pilandina.com.bo',
            'municipio' => 'Cochabamba',
            'departamento' => 'Cochabamba',
            'estado' => 1, // Activo
        ]);

        // Crear empresa de ejemplo
        Empresa::create([
            'razon_social' => 'PIL Andina S.A.2',
            'nombre_comercial' => 'PIL Andina2',
            'nit' => '10254010192',
            'matricula_comercio' => '1234567892',
            'direccion_fiscal' => 'Av. Panamericana2, km 15, Cochabamba, Bolivia',
            'telefono' => '+591 4 46050002',
            'email' => 'contacto2@pilandina.com.bo',
            'municipio' => 'Cochabamba',
            'departamento' => 'Cochabamba',
            'estado' => 0, // Inactivo
        ]);
    }
}
