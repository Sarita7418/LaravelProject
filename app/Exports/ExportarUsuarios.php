<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportarUsuarios implements FromCollection, WithHeadings, WithMapping
{
    protected $usuarios;

    public function __construct($usuarios)
    {
        $this->usuarios = $usuarios;
    }

    public function collection()
    {
        return $this->usuarios;
    }

    public function map($user): array
    {
        $persona = $user->persona;

        return [
            $user->name,
            $user->email,
            optional($user->role)->descripcion ?? 'Sin rol',
            $persona
                ? trim("{$persona->nombres} {$persona->apellido_paterno} {$persona->apellido_materno}")
                : '',
            $persona->telefono ?? '',
            $persona->ci ?? '',
            $user->estado ? 'Activo' : 'Inactivo',
            $user->created_at ? $user->created_at->format('d/m/Y') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Usuario',
            'Email',
            'Rol',
            'Nombre Completo',
            'Teléfono',
            'CI',
            'Estado',
            'Fecha Registro',
        ];
    }
}
