<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanCuentasImport implements ToCollection, WithHeadingRow
{
    private $batchSize = 500;

    public function collection(Collection $rows)
    {
        dump("Registros recibidos del Excel: " . $rows->count());
        
        DB::transaction(function () use ($rows) {
            $this->procesarConMapeo($rows);
        });
    }

    private function procesarConMapeo(Collection $rows)
    {
        $mapeoIds = [];
        
        $rowsOrdenadas = $rows->sortBy('nivel');
        
        foreach ($rowsOrdenadas as $row) {
            if (empty($row['codigo']) || empty($row['descripcion'])) {
                continue;
            }
            
            $existe = DB::table('plan_cuentas')
                       ->where('codigo', trim($row['codigo']))
                       ->exists();
            
            if ($existe) {
                dump("Ya existe: " . $row['codigo']);
                continue;
            }
            
            $id_padre_excel = $row['id_padre'];
            $id_padre_bd = null;
            
            if (!empty($id_padre_excel) && $id_padre_excel !== 'NULL') {
                $id_padre_bd = $mapeoIds[(int)$id_padre_excel] ?? null;
            }
            
            $nuevoId = DB::table('plan_cuentas')->insertGetId([
                'id_padre' => $id_padre_bd,
                'codigo' => trim($row['codigo']),
                'descripcion' => trim($row['descripcion']),
                'tipo' => (int) $row['tipo'], // Nuevo campo
                'nivel' => (int) $row['nivel'],
                'grupo_estado_financiero' => (int) $row['grupo_estado_financiero'], // Nuevo campo
                'cuenta_ajuste' => (int) $row['cuenta_ajuste'], // Nuevo campo
                'cuenta_presupuesto' => (int) $row['cuenta_presupuesto'], // Nuevo campo
                'estado' => $this->convertirEstado(trim($row['estado'] ?? 'activo')),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $mapeoIds[(int)$row['id']] = $nuevoId;
            
            dump("Insertado: " . $row['codigo'] . " (Excel ID: " . $row['id'] . " -> BD ID: $nuevoId)");
        }
    }

    private function convertirEstado($estado)
    {
        return strtolower($estado) === 'activo' ? true : false;
    }

    private function insertBatch(array $data)
    {
        if (empty($data)) return;

        try {
            DB::table('plan_cuentas')->insert($data);
            dump("Insertados " . count($data) . " registros exitosamente");
        } catch (\Exception $e) {
            dump("Error en inserción masiva: " . $e->getMessage());
            
            $insertados = 0;
            $errores = 0;
            
            foreach ($data as $item) {
                try {
                    DB::table('plan_cuentas')->insert($item);
                    $insertados++;
                } catch (\Exception $ex) {
                    $errores++;
                    dump("Error en código " . $item['codigo'] . ": " . $ex->getMessage());
                }
            }
            
            dump("Resultado individual - Insertados: $insertados, Errores: $errores");
        }
    }
}