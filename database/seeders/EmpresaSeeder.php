<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'razon_social' => 'PIL Andina S.A.',
            'nombre_comercial' => 'PIL Andina',
            'nit' => '1025401019',
            'matricula_comercio' => '123456789',
            'direccion_fiscal' => 'Av. Panamericana, Cochabamba',
            'telefono' => '+591 4 4605000',
            'email' => 'contacto@pilandina.com.bo',
            'municipio' => 'Cochabamba',
            'departamento' => 'Cochabamba',
            'estado' => 1,
            'id_representante_legal' => 1,
        ]);

        Empresa::create([
            'razon_social' => 'Industrias de Alimentos Delizia S.A.',
            'nombre_comercial' => 'Delizia',
            'nit' => '1029384756',
            'matricula_comercio' => 'DEL123456',
            'direccion_fiscal' => 'Zona Industrial El Alto',
            'telefono' => '+591 2 2845000',
            'email' => 'info@delizia.com.bo',
            'municipio' => 'El Alto',
            'departamento' => 'La Paz',
            'estado' => 1,
            'id_representante_legal' => 2,
        ]);

        Empresa::create([
            'razon_social' => 'Empresa Boliviana de Alimentos',
            'nombre_comercial' => 'EBA',
            'nit' => '1023456789',
            'matricula_comercio' => 'EBA987654',
            'direccion_fiscal' => 'Av. Montes',
            'telefono' => '+591 2 2110000',
            'email' => 'contacto@eba.gob.bo',
            'municipio' => 'La Paz',
            'departamento' => 'La Paz',
            'estado' => 1,
            'id_representante_legal' => 3,
        ]);

        Empresa::create([
            'razon_social' => 'Sociedad Boliviana de Cemento S.A.',
            'nombre_comercial' => 'SOBOCE',
            'nit' => '1011121314',
            'matricula_comercio' => 'SBC456789',
            'direccion_fiscal' => 'Av. Ballivián',
            'telefono' => '+591 2 2799000',
            'email' => 'info@soboce.com',
            'municipio' => 'La Paz',
            'departamento' => 'La Paz',
            'estado' => 1,
            'id_representante_legal' => 4,
        ]);

        Empresa::create([
            'razon_social' => 'Cervecería Boliviana Nacional S.A.',
            'nombre_comercial' => 'CBN',
            'nit' => '1098765432',
            'matricula_comercio' => 'CBN112233',
            'direccion_fiscal' => 'Av. Blanco Galindo',
            'telefono' => '+591 4 4250000',
            'email' => 'contacto@cbn.bo',
            'municipio' => 'Cochabamba',
            'departamento' => 'Cochabamba',
            'estado' => 1,
            'id_representante_legal' => 5,
        ]);
    }
}
