<?php

namespace App\Imports;

use App\Models\ImportacionLinameRaw; // Asegúrate de que tu modelo se llame así
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LinameImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Si no hay nombre de medicamento, saltamos la fila
        if (!isset($row['medicamento_nombre'])) {
            return null;
        }

        return new ImportacionLinameRaw([
            'codigo_completo'    => $row['codigo_completo'],
            'grupo_co'           => $row['grupo_co'],
            'subgrupo_di'        => $row['subgrupo_di'],
            'correlativo_go'     => $row['correlativo_go'],
            'medicamento_nombre' => $row['medicamento_nombre'],
            'forma'              => $row['forma'],
            'concentracion'      => (string) $row['concentracion'], // Forzamos texto
            'codigo_atq'         => $row['codigo_atq'],
            'uso_restringido'    => $row['uso_restringido'],
        ]);
    }
}