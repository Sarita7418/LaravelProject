<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\PlanPresupuestario;
use App\Models\Subdominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanPresupuestarioCrudController extends Controller
{
    public function index()
    {
        try {
            $presupuestarios = PlanPresupuestario::with([
                'tipoSubdominio:id,descripcion'
            ])
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get();

            // Transformamos la salida para incluir descripciones
            $presupuestarios = $presupuestarios->map(function ($presupuestario) {
                return [
                    'id' => $presupuestario->id,
                    'id_padre' => $presupuestario->id_padre,
                    'codigo' => $presupuestario->codigo,
                    'descripcion' => $presupuestario->descripcion,
                    'nivel' => $presupuestario->nivel,
                    'estado' => $presupuestario->estado,
                    // ID crudo
                    'tipo_id' => $presupuestario->tipo,
                    // Descripción lista para el frontend
                    'tipo' => $presupuestario->tipoTexto,
                ];
            });

            return response()->json($presupuestarios);
        } catch (\Exception $e) {
            Log::error('Error en index plan presupuestario', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Error al obtener plan presupuestario',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function indexTodas()
    {
        try {
            $presupuestarios = PlanPresupuestario::with([
                'tipoSubdominio:id,descripcion'
            ])
            ->orderBy('codigo')
            ->get();

            $presupuestarios = $presupuestarios->map(function ($presupuestario) {
                return [
                    'id' => $presupuestario->id,
                    'id_padre' => $presupuestario->id_padre,
                    'codigo' => $presupuestario->codigo,
                    'descripcion' => $presupuestario->descripcion,
                    'nivel' => $presupuestario->nivel,
                    'estado' => $presupuestario->estado,
                    'tipo_id' => $presupuestario->tipo,
                    'tipo' => $presupuestario->tipoTexto,
                ];
            });

            return response()->json($presupuestarios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener todos los presupuestarios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function debug()
    {
        try {
            $info = [
                'total_presupuestarios' => PlanPresupuestario::count(),
                'presupuestarios_estado_1' => PlanPresupuestario::where('estado', 1)->count(),
                'presupuestarios_estado_0' => PlanPresupuestario::where('estado', 0)->count(),
                'estructura_tabla' => DB::select('DESCRIBE plan_presupuestarios'),
                'muestra_datos' => PlanPresupuestario::take(5)->get()->toArray(),
                'estados_existentes' => PlanPresupuestario::select('estado')
                    ->groupBy('estado')
                    ->orderBy('estado')
                    ->pluck('estado')
                    ->toArray(),
                'niveles_existentes' => PlanPresupuestario::select('nivel')
                    ->groupBy('nivel')
                    ->orderBy('nivel')
                    ->pluck('nivel')
                    ->toArray(),
                'distribucion_por_padre' => PlanPresupuestario::select('id_padre', DB::raw('count(*) as total'))
                    ->groupBy('id_padre')
                    ->orderBy('id_padre')
                    ->get()
                    ->toArray(),
                'subdominios_debug' => [
                    'total_subdominios' => Subdominio::count(),
                    'por_dominio' => Subdominio::select('id_dominio', DB::raw('count(*) as total'))
                        ->groupBy('id_dominio')
                        ->orderBy('id_dominio')
                        ->get()
                        ->toArray(),
                    'muestra_subdominios' => Subdominio::take(10)->get()->toArray()
                ]
            ];

            return response()->json($info);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en debug',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva cuenta presupuestaria (CRUD completo para nivel 2 en adelante)
     */
    public function store(Request $request)
    {
        try {
            // Validación básica
            $request->validate([
                'id_padre' => 'required|integer|exists:plan_presupuestarios,id',
                'descripcion' => 'required|string|max:255',
                'tipo' => 'nullable|integer|exists:subdominios,id',
            ]);

            // Obtener cuenta padre
            $padre = PlanPresupuestario::findOrFail($request->id_padre);
            
            // Verificar restricciones por nivel
            if ($padre->nivel == 1) {
                // Solo se pueden añadir hijos al nivel 1 (crear nivel 2)
                $nuevoNivel = 2;
            } else if ($padre->nivel >= 2 && $padre->nivel <= 4) {
                // CRUD completo para nivel 2 en adelante
                $nuevoNivel = $padre->nivel + 1;
                
                // Verificar que no exceda nivel 5
                if ($nuevoNivel > 5) {
                    return response()->json([
                        'error' => 'No se pueden crear cuentas más allá del nivel 5'
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'Nivel padre no válido'
                ], 400);
            }

            // Generar código automáticamente
            $nuevoCodigo = $this->generarCodigo($padre, $nuevoNivel);

            if (!$nuevoCodigo) {
                return response()->json([
                    'error' => 'No se pudo generar el código para la nueva cuenta presupuestaria'
                ], 400);
            }

            // Crear la cuenta presupuestaria
            $presupuestario = PlanPresupuestario::create([
                'id_padre' => $request->id_padre,
                'codigo' => $nuevoCodigo,
                'descripcion' => $request->descripcion,
                'nivel' => $nuevoNivel,
                'tipo' => $request->tipo,
                'estado' => 1,
            ]);

            // Retornar con relaciones cargadas
            $presupuestario->load([
                'tipoSubdominio:id,descripcion'
            ]);

            return response()->json([
                'message' => 'Cuenta presupuestaria creada exitosamente',
                'presupuestario' => $presupuestario
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear cuenta presupuestaria', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Error al crear cuenta presupuestaria',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cuenta presupuestaria existente
     */
    public function update(Request $request, $id)
    {
        try {
            $presupuestario = PlanPresupuestario::findOrFail($id);

            // Restricciones por nivel
            if ($presupuestario->nivel == 1) {
                // Nivel 1: Solo se puede modificar descripción y tipo
                $request->validate([
                    'descripcion' => 'required|string|max:255',
                    'tipo' => 'nullable|integer|exists:subdominios,id',
                ]);

                $presupuestario->update([
                    'descripcion' => $request->descripcion,
                    'tipo' => $request->tipo,
                ]);

            } else if ($presupuestario->nivel >= 2) {
                // Nivel 2 en adelante: CRUD completo
                $request->validate([
                    'descripcion' => 'required|string|max:255',
                    'tipo' => 'nullable|integer|exists:subdominios,id',
                ]);

                $presupuestario->update([
                    'descripcion' => $request->descripcion,
                    'tipo' => $request->tipo,
                ]);
            }

            // Retornar con relaciones cargadas
            $presupuestario->load([
                'tipoSubdominio:id,descripcion'
            ]);

            return response()->json([
                'message' => 'Cuenta presupuestaria actualizada exitosamente',
                'presupuestario' => $presupuestario
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar cuenta presupuestaria', [
                'message' => $e->getMessage(),
                'presupuestario_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Error al actualizar cuenta presupuestaria',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cuenta presupuestaria (solo nivel 2 en adelante)
     */
    public function destroy($id)
    {
        try {
            $presupuestario = PlanPresupuestario::findOrFail($id);

            // Restricción: No se puede eliminar nivel 1
            if ($presupuestario->nivel == 1) {
                return response()->json([
                    'error' => 'No se pueden eliminar cuentas presupuestarias de nivel 1'
                ], 400);
            }

            // Verificar si tiene hijos activos
            $tieneHijos = PlanPresupuestario::where('id_padre', $id)
                ->where('estado', 1)
                ->exists();

            if ($tieneHijos) {
                return response()->json([
                    'error' => 'No se puede eliminar una cuenta presupuestaria que tiene cuentas hijas activas'
                ], 400);
            }

            // Desactivar (soft delete)
            $presupuestario->update(['estado' => 0]);

            return response()->json([
                'message' => 'Cuenta presupuestaria desactivada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al desactivar cuenta presupuestaria', [
                'message' => $e->getMessage(),
                'presupuestario_id' => $id
            ]);

            return response()->json([
                'error' => 'Error al desactivar cuenta presupuestaria',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas presupuestarias inactivas
     */
    public function inactivas()
    {
        try {
            $presupuestarios = PlanPresupuestario::with([
                'tipoSubdominio:id,descripcion'
            ])
            ->where('estado', 0)
            ->orderBy('codigo')
            ->get();

            return response()->json($presupuestarios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cuentas presupuestarias inactivas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivar cuenta presupuestaria
     */
    public function reactivar($id)
    {
        try {
            $presupuestario = PlanPresupuestario::findOrFail($id);
            $presupuestario->update(['estado' => 1]);

            return response()->json([
                'message' => 'Cuenta presupuestaria reactivada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al reactivar cuenta presupuestaria',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas presupuestarias que pueden ser padres según el contexto
     */
    public function getCuentasPadre(Request $request)
    {
        try {
            // Si se especifica un nivel, obtener cuentas de ese nivel
            $nivel = $request->get('nivel');
            
            if ($nivel) {
                $cuentasPadre = PlanPresupuestario::where('nivel', $nivel)
                    ->where('estado', 1)
                    ->orderBy('codigo')
                    ->get(['id', 'codigo', 'descripcion', 'nivel']);
            } else {
                // Por defecto, obtener cuentas que pueden tener hijos (niveles 1-4)
                $cuentasPadre = PlanPresupuestario::whereIn('nivel', [1, 2, 3, 4])
                    ->where('estado', 1)
                    ->orderBy('codigo')
                    ->get(['id', 'codigo', 'descripcion', 'nivel']);
            }

            return response()->json($cuentasPadre);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cuentas presupuestarias padre',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener subdominios para los combobox
     */
    public function getSubdominios(Request $request)
    {
        try {
            $dominioRequerido = $request->get('dominio'); // 4 o 5 para plan presupuestario
            
            if ($dominioRequerido) {
                // Filtrar por id_dominio específico si se proporciona
                $subdominios = Subdominio::where('id_dominio', $dominioRequerido)
                    ->orderBy('descripcion')
                    ->get(['id', 'descripcion', 'id_dominio']);
            } else {
                // Obtener subdominios de los dominios relevantes para presupuesto
                $subdominios = Subdominio::whereIn('id_dominio', [4, 5]) // Dominios de plan presupuestario
                    ->orderBy('id_dominio')
                    ->orderBy('descripcion')
                    ->get(['id', 'descripcion', 'id_dominio']);
            }

            Log::info('Subdominios presupuestarios obtenidos', [
                'dominio_solicitado' => $dominioRequerido,
                'total_subdominios' => $subdominios->count(),
                'por_dominio' => $subdominios->groupBy('id_dominio')->map->count(),
                'muestra' => $subdominios->take(5)->toArray()
            ]);

            return response()->json($subdominios);
        } catch (\Exception $e) {
            Log::error('Error al obtener subdominios presupuestarios', [
                'message' => $e->getMessage(),
                'dominio_requerido' => $request->get('dominio'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener subdominios presupuestarios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener subdominios específicos por dominio
     */
    public function getSubdominiosPorDominio($idDominio)
    {
        try {
            $subdominios = Subdominio::where('id_dominio', $idDominio)
                ->orderBy('descripcion')
                ->get(['id', 'descripcion', 'id_dominio']);

            Log::info('Subdominios presupuestarios por dominio obtenidos', [
                'id_dominio' => $idDominio,
                'total' => $subdominios->count(),
                'subdominios' => $subdominios->toArray()
            ]);

            return response()->json($subdominios);
        } catch (\Exception $e) {
            Log::error('Error al obtener subdominios presupuestarios por dominio', [
                'message' => $e->getMessage(),
                'id_dominio' => $idDominio,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener subdominios presupuestarios por dominio',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar código automáticamente basado en el padre usando la lógica de 5 niveles
     */
    private function generarCodigo($padre, $nuevoNivel)
    {
        try {
            $codigoPadre = $padre->codigo;
            
            // Lógica específica para 5 niveles del plan presupuestario
            switch ($nuevoNivel) {
                case 1:
                    // Nivel 1: código fijo "10000" (o el siguiente disponible)
                    $ultimoNivel1 = PlanPresupuestario::where('nivel', 1)
                        ->where('estado', 1)
                        ->orderBy('codigo', 'desc')
                        ->first();
                    
                    if (!$ultimoNivel1) {
                        return '10000';
                    } else {
                        $ultimoCodigo = intval($ultimoNivel1->codigo);
                        return str_pad($ultimoCodigo + 10000, 5, '0', STR_PAD_LEFT);
                    }
                    
                case 2:
                    // Nivel 2: incrementar en miles (11000, 12000, 13000...)
                    $prefijo = substr($codigoPadre, 0, 2); // "10"
                    $ultimoHijo = PlanPresupuestario::where('id_padre', $padre->id)
                        ->where('estado', 1)
                        ->orderBy('codigo', 'desc')
                        ->first();
                    
                    if (!$ultimoHijo) {
                        return $prefijo . '1000'; // 11000
                    } else {
                        $ultimoCodigo = intval($ultimoHijo->codigo);
                        return str_pad($ultimoCodigo + 1000, 5, '0', STR_PAD_LEFT);
                    }
                    
                case 3:
                    // Nivel 3: incrementar en centenas (11100, 11200, 11300...)
                    $prefijo = substr($codigoPadre, 0, 3); // "110"
                    $ultimoHijo = PlanPresupuestario::where('id_padre', $padre->id)
                        ->where('estado', 1)
                        ->orderBy('codigo', 'desc')
                        ->first();
                    
                    if (!$ultimoHijo) {
                        return $prefijo . '00'; // 11000 -> 11100
                    } else {
                        $ultimoCodigo = intval($ultimoHijo->codigo);
                        return str_pad($ultimoCodigo + 100, 5, '0', STR_PAD_LEFT);
                    }
                    
                case 4:
                    // Nivel 4: incrementar en decenas (11110, 11120, 11130...)
                    $prefijo = substr($codigoPadre, 0, 4); // "1110"
                    $ultimoHijo = PlanPresupuestario::where('id_padre', $padre->id)
                        ->where('estado', 1)
                        ->orderBy('codigo', 'desc')
                        ->first();
                    
                    if (!$ultimoHijo) {
                        return $prefijo . '0'; // 11100 -> 11110
                    } else {
                        $ultimoCodigo = intval($ultimoHijo->codigo);
                        return str_pad($ultimoCodigo + 10, 5, '0', STR_PAD_LEFT);
                    }
                    
                case 5:
                    // Nivel 5: incrementar en unidades (11111, 11112, 11113...)
                    $ultimoHijo = PlanPresupuestario::where('id_padre', $padre->id)
                        ->where('estado', 1)
                        ->orderBy('codigo', 'desc')
                        ->first();
                    
                    if (!$ultimoHijo) {
                        $codigoBase = intval($codigoPadre);
                        return str_pad($codigoBase + 1, 5, '0', STR_PAD_LEFT); // 11110 -> 11111
                    } else {
                        $ultimoCodigo = intval($ultimoHijo->codigo);
                        return str_pad($ultimoCodigo + 1, 5, '0', STR_PAD_LEFT);
                    }
                    
                default:
                    return null;
            }
            
        } catch (\Exception $e) {
            Log::error('Error generando código presupuestario', [
                'padre_codigo' => $padre->codigo,
                'nuevo_nivel' => $nuevoNivel,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}