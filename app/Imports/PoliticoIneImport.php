<?php

namespace App\Imports;

use App\Models\PoliticoUbicacion;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PoliticoIneImport implements ToCollection, WithHeadingRow
{
    private $batchSize = 500;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            $this->procesarPaises($rows);
            $this->procesarDepartamentos($rows);
            $this->procesarProvincias($rows);
            $this->procesarMunicipios($rows);
            $this->procesarComunidades($rows);
        });
    }

    private function procesarPaises(Collection $rows)
    {
        $paises = $rows->pluck('nombrepais')->unique()->filter();
        
        $datos = $paises->map(function ($nombre) {
            return [
                'id_padre' => null,
                'tipo' => 'Pais',
                'descripcion' => $nombre ?: 'BOLIVIA',
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        $this->insertBatch($datos->toArray());
    }

    private function procesarDepartamentos(Collection $rows)
    {
        $paises = PoliticoUbicacion::where('tipo', 'Pais')
                     ->pluck('id', 'descripcion');
        
        $datos = [];
        
        $rows->groupBy(['nombrepais', 'nombredepartamento'])->each(function ($grupo, $paisNombre) use ($paises, &$datos) {
            foreach ($grupo as $deptoNombre => $filas) {
                $datos[] = [
                    'id_padre' => $paises[$paisNombre] ?? null,
                    'tipo' => 'Departamento',
                    'descripcion' => $deptoNombre ?: 'DESCONOCIDO',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        });

        $this->insertBatch($datos);
    }

    private function procesarProvincias(Collection $rows)
    {
        $deptos = PoliticoUbicacion::where('tipo', 'Departamento')
                         ->pluck('id', 'descripcion');
        
        $datos = [];
        
        $rows->groupBy(['nombredepartamento', 'nombreprovincia'])->each(function ($grupo, $deptoNombre) use ($deptos, &$datos) {
            foreach ($grupo as $provNombre => $filas) {
                $datos[] = [
                    'id_padre' => $deptos[$deptoNombre] ?? null,
                    'tipo' => 'Provincia',
                    'descripcion' => $provNombre ?: 'DESCONOCIDO',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        });

        $this->insertBatch($datos);
    }

    private function procesarMunicipios(Collection $rows)
    {
        $provincias = PoliticoUbicacion::where('tipo', 'Provincia')
                            ->pluck('id', 'descripcion');
        
        $datos = [];
        
        $rows->groupBy(['nombreprovincia', 'nombremunicipio'])->each(function ($grupo, $provNombre) use ($provincias, &$datos) {
            foreach ($grupo as $muniNombre => $filas) {
                $datos[] = [
                    'id_padre' => $provincias[$provNombre] ?? null,
                    'tipo' => 'Municipio',
                    'descripcion' => $muniNombre ?: 'DESCONOCIDO',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        });

        $this->insertBatch($datos);
    }

    private function procesarComunidades(Collection $rows)
    {
        $municipios = PoliticoUbicacion::where('tipo', 'Municipio')
                            ->pluck('id', 'descripcion');
        
        $datos = [];
        
        $rows->each(function ($row) use ($municipios, &$datos) {
            $datos[] = [
                'id_padre' => $municipios[$row['nombremunicipio']] ?? null,
                'tipo' => 'Comunidad',
                'descripcion' => $row['nombrecomunidad'] ?: 'DESCONOCIDO',
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        $chunks = array_chunk($datos, $this->batchSize);
        foreach ($chunks as $chunk) {
            $this->insertBatch($chunk);
        }
    }

    private function insertBatch(array $data)
    {
        if (empty($data)) return;

        try {

            DB::table('politicos_ubicacion')->insertOrIgnore($data);
        } catch (\Exception $e) {

            foreach ($data as $item) {
                try {
                    DB::table('politicos_ubicacion')->insertOrIgnore($item);
                } catch (\Exception $ex) {
                    continue;
                }
            }
        }
    }
}