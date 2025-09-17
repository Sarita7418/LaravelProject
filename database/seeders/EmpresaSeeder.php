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
            'id_representante_legal' => 1, // Asignamos el representante legal
        ]);

    }
}
