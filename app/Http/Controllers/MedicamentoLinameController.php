<?php

namespace App\Http\Controllers;

use App\Models\MedicamentoLiname;
use App\Models\ClasificacionLiname;
use App\Models\ProductoComercial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicamentoLinameController extends Controller
{
    /**
     * Obtener todos los grupos (nivel 1 de clasificaciones)
     */
    public function getGrupos()
    {
        try {
            $grupos = ClasificacionLiname::where('nivel', 1)
                ->whereNull('padre_id')
                ->orderBy('codigo')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $grupos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getGrupos:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar grupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener subgrupos de un grupo específico
     */
    public function getSubgrupos($grupoId)
    {
        try {
            $subgrupos = ClasificacionLiname::where('nivel', 2)
                ->where('padre_id', $grupoId)
                ->orderBy('codigo')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $subgrupos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getSubgrupos:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar subgrupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener medicamentos por clasificación (subgrupo) - CON TODA LA INFO
     */
    public function getMedicamentosPorClasificacion($clasificacionId)
    {
        try {
            $medicamentos = MedicamentoLiname::with([
                'generico', 
                'clasificacion.padre'
            ])
                ->where('clasificacion_id', $clasificacionId)
                ->get()
                ->map(function($med) {
                    // Determinar si la clasificación ES un grupo o ES un subgrupo
                    $esGrupo = ($med->clasificacion->nivel == 1);
                    $grupo = null;
                    $subgrupo = null;
                    
                    if ($esGrupo) {
                        // Si la clasificación es nivel 1, ES el grupo (no tiene padre)
                        $grupo = $med->clasificacion;
                        $subgrupo = null;
                    } else {
                        // Si la clasificación es nivel 2, ES el subgrupo (tiene padre)
                        $subgrupo = $med->clasificacion;
                        $grupo = $med->clasificacion->padre;
                    }
                    
                    return [
                        'id' => $med->id,
                        'codigo_completo' => $med->codigo_completo,
                        
                        // Genérico
                        'generico_nombre' => $med->generico->nombre ?? 'Sin nombre',
                        'generico_codigo_atq' => $med->generico->codigo_atq ?? null,
                        
                        // Presentación
                        'forma_farmaceutica' => $med->forma_farmaceutica,
                        'concentracion' => $med->concentracion,
                        'uso_restringido' => $med->uso_restringido,
                        'correlativo_go' => $med->correlativo_go,
                        
                        // Clasificación (puede ser grupo o subgrupo)
                        'clasificacion_id' => $med->clasificacion->id,
                        'clasificacion_codigo' => $med->clasificacion->codigo,
                        'clasificacion_nombre' => $med->clasificacion->nombre,
                        'clasificacion_nivel' => $med->clasificacion->nivel,
                        
                        // Grupo (Nivel 1)
                        'grupo_id' => $grupo?->id,
                        'grupo_codigo' => $grupo?->codigo,
                        'grupo_nombre' => $grupo?->nombre,
                        
                        // Subgrupo (Nivel 2) - solo si existe
                        'subgrupo_id' => $subgrupo?->id,
                        'subgrupo_codigo' => $subgrupo?->codigo,
                        'subgrupo_nombre' => $subgrupo?->nombre,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $medicamentos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getMedicamentosPorClasificacion:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar medicamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar medicamentos por término (búsqueda simple)
     */
    public function buscar(Request $request)
    {
        try {
            $termino = $request->get('search', '');
            
            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $medicamentos = MedicamentoLiname::with(['generico', 'clasificacion'])
                ->where(function($query) use ($termino) {
                    $query->where('codigo_completo', 'LIKE', "%{$termino}%")
                          ->orWhere('forma_farmaceutica', 'LIKE', "%{$termino}%")
                          ->orWhere('concentracion', 'LIKE', "%{$termino}%")
                          ->orWhereHas('generico', function($q) use ($termino) {
                              $q->where('nombre', 'LIKE', "%{$termino}%");
                          });
                })
                ->limit(20)
                ->get()
                ->map(function($med) {
                    return [
                        'id' => $med->id,
                        'codigo_completo' => $med->codigo_completo,
                        'generico_nombre' => $med->generico->nombre ?? 'Sin nombre',
                        'forma_farmaceutica' => $med->forma_farmaceutica,
                        'concentracion' => $med->concentracion,
                        'uso_restringido' => $med->uso_restringido,
                        'label' => "{$med->generico->nombre} - {$med->forma_farmaceutica} {$med->concentracion}",
                        'value' => $med->id
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $medicamentos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscar medicamentos:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar medicamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NUEVO: Búsqueda completa con toda la información jerárquica
     */
    public function buscarCompleto(Request $request)
    {
        try {
            $termino = $request->get('search', '');
            
            if (strlen($termino) < 3) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $medicamentos = MedicamentoLiname::with([
                'generico', 
                'clasificacion.padre'
            ])
                ->where(function($query) use ($termino) {
                    $query->where('codigo_completo', 'LIKE', "%{$termino}%")
                          ->orWhere('forma_farmaceutica', 'LIKE', "%{$termino}%")
                          ->orWhere('concentracion', 'LIKE', "%{$termino}%")
                          ->orWhereHas('generico', function($q) use ($termino) {
                              $q->where('nombre', 'LIKE', "%{$termino}%")
                                ->orWhere('codigo_atq', 'LIKE', "%{$termino}%");
                          });
                })
                ->limit(30)
                ->get()
                ->map(function($med) {
                    // Determinar si la clasificación ES un grupo o ES un subgrupo
                    $esGrupo = ($med->clasificacion->nivel == 1);
                    $grupo = null;
                    $subgrupo = null;
                    
                    if ($esGrupo) {
                        // Si la clasificación es nivel 1, ES el grupo (no tiene padre)
                        $grupo = $med->clasificacion;
                        $subgrupo = null;
                    } else {
                        // Si la clasificación es nivel 2, ES el subgrupo (tiene padre)
                        $subgrupo = $med->clasificacion;
                        $grupo = $med->clasificacion->padre;
                    }
                    
                    return [
                        'id' => $med->id,
                        'codigo_completo' => $med->codigo_completo,
                        
                        // Genérico
                        'generico_nombre' => $med->generico->nombre ?? 'Sin nombre',
                        'generico_codigo_atq' => $med->generico->codigo_atq ?? null,
                        
                        // Presentación
                        'forma_farmaceutica' => $med->forma_farmaceutica,
                        'concentracion' => $med->concentracion,
                        'uso_restringido' => $med->uso_restringido,
                        'correlativo_go' => $med->correlativo_go,
                        
                        // Clasificación (puede ser grupo o subgrupo)
                        'clasificacion_id' => $med->clasificacion->id,
                        'clasificacion_codigo' => $med->clasificacion->codigo,
                        'clasificacion_nombre' => $med->clasificacion->nombre,
                        'clasificacion_nivel' => $med->clasificacion->nivel,
                        
                        // Grupo (Nivel 1)
                        'grupo_id' => $grupo?->id,
                        'grupo_codigo' => $grupo?->codigo,
                        'grupo_nombre' => $grupo?->nombre,
                        
                        // Subgrupo (Nivel 2) - solo si existe
                        'subgrupo_id' => $subgrupo?->id,
                        'subgrupo_codigo' => $subgrupo?->codigo,
                        'subgrupo_nombre' => $subgrupo?->nombre,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $medicamentos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscarCompleto:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar medicamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NUEVO: Obtener nombres comerciales de un medicamento genérico
     */
    public function getNombresComerciales($id)
    {
        try {
            $medicamento = MedicamentoLiname::with('generico')->findOrFail($id);
            
            // Buscar productos comerciales asociados al genérico
            $nombresComerciales = ProductoComercial::where('producto_generico_id', $medicamento->producto_generico_id)
                ->select('id', 'nombre_comercial', 'laboratorio_fabricante', 'empresa_proveedora')
                ->distinct('nombre_comercial')
                ->orderBy('nombre_comercial')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $nombresComerciales
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getNombresComerciales:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron nombres comerciales',
                'error' => $e->getMessage(),
                'data' => []
            ], 200); // Retornamos 200 porque no es un error crítico
        }
    }

    /**
     * Obtener detalle de un medicamento específico
     */
    public function show($id)
    {
        try {
            $medicamento = MedicamentoLiname::with(['generico', 'clasificacion.padre'])
                ->findOrFail($id);
            
            $grupo = $medicamento->clasificacion->padre ?? null;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $medicamento->id,
                    'codigo_completo' => $medicamento->codigo_completo,
                    'correlativo_go' => $medicamento->correlativo_go,
                    
                    'generico' => [
                        'id' => $medicamento->generico->id,
                        'nombre' => $medicamento->generico->nombre,
                        'codigo_atq' => $medicamento->generico->codigo_atq
                    ],
                    
                    'clasificacion' => [
                        'id' => $medicamento->clasificacion->id,
                        'codigo' => $medicamento->clasificacion->codigo,
                        'nombre' => $medicamento->clasificacion->nombre
                    ],
                    
                    'grupo' => $grupo ? [
                        'id' => $grupo->id,
                        'codigo' => $grupo->codigo,
                        'nombre' => $grupo->nombre
                    ] : null,
                    
                    'forma_farmaceutica' => $medicamento->forma_farmaceutica,
                    'concentracion' => $medicamento->concentracion,
                    'uso_restringido' => $medicamento->uso_restringido
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en show medicamento:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Medicamento no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}