<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\PlanCuenta;
use App\Models\Subdominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanCuentasCrudController extends Controller
{
    public function index()
    {
        try {
            $cuentas = PlanCuenta::with([
                'tipoSubdominio:id,descripcion',
                'grupoEstadoFinancieroSubdominio:id,descripcion',
                'cuentaAjusteSubdominio:id,descripcion',
                'cuentaPresupuestoSubdominio:id,descripcion'
            ])
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get();

            // Transformamos la salida para incluir descripciones
            $cuentas = $cuentas->map(function ($cuenta) {
                return [
                    'id' => $cuenta->id,
                    'id_padre' => $cuenta->id_padre,
                    'codigo' => $cuenta->codigo,
                    'descripcion' => $cuenta->descripcion,
                    'nivel' => $cuenta->nivel,
                    'estado' => $cuenta->estado,
                    // IDs crudos
                    'tipo_id' => $cuenta->tipo,
                    'grupo_estado_financiero_id' => $cuenta->grupo_estado_financiero,
                    'cuenta_ajuste_id' => $cuenta->cuenta_ajuste,
                    'cuenta_presupuesto_id' => $cuenta->cuenta_presupuesto,
                    // Descripciones listas para el frontend
                    'tipo' => $cuenta->tipoTexto,
                    'grupo_estado_financiero' => $cuenta->grupoEstadoTexto,
                    'cuenta_ajuste' => $cuenta->cuentaAjusteTexto,
                    'cuenta_presupuesto' => $cuenta->cuentaPresupuestoTexto,
                ];
            });

            return response()->json($cuentas);
        } catch (\Exception $e) {
            Log::error('Error en index plan cuentas', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Error al obtener plan de cuentas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function indexTodas()
    {
        try {
            $cuentas = PlanCuenta::with([
                'tipoSubdominio:id,descripcion',
                'grupoEstadoFinancieroSubdominio:id,descripcion',
                'cuentaAjusteSubdominio:id,descripcion',
                'cuentaPresupuestoSubdominio:id,descripcion'
            ])
            ->orderBy('codigo')
            ->get();

            $cuentas = $cuentas->map(function ($cuenta) {
                return [
                    'id' => $cuenta->id,
                    'id_padre' => $cuenta->id_padre,
                    'codigo' => $cuenta->codigo,
                    'descripcion' => $cuenta->descripcion,
                    'nivel' => $cuenta->nivel,
                    'estado' => $cuenta->estado,
                    'tipo_id' => $cuenta->tipo,
                    'grupo_estado_financiero_id' => $cuenta->grupo_estado_financiero,
                    'cuenta_ajuste_id' => $cuenta->cuenta_ajuste,
                    'cuenta_presupuesto_id' => $cuenta->cuenta_presupuesto,
                    'tipo' => $cuenta->tipoTexto,
                    'grupo_estado_financiero' => $cuenta->grupoEstadoTexto,
                    'cuenta_ajuste' => $cuenta->cuentaAjusteTexto,
                    'cuenta_presupuesto' => $cuenta->cuentaPresupuestoTexto,
                ];
            });

            return response()->json($cuentas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener todas las cuentas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function debug()
    {
        try {
            $info = [
                'total_cuentas' => PlanCuenta::count(),
                'cuentas_estado_1' => PlanCuenta::where('estado', 1)->count(),
                'cuentas_estado_0' => PlanCuenta::where('estado', 0)->count(),
                'estructura_tabla' => DB::select('DESCRIBE plan_cuentas'),
                'muestra_datos' => PlanCuenta::take(5)->get()->toArray(),
                'estados_existentes' => PlanCuenta::select('estado')
                    ->groupBy('estado')
                    ->orderBy('estado')
                    ->pluck('estado')
                    ->toArray(),
                'niveles_existentes' => PlanCuenta::select('nivel')
                    ->groupBy('nivel')
                    ->orderBy('nivel')
                    ->pluck('nivel')
                    ->toArray(),
                'distribucion_por_padre' => PlanCuenta::select('id_padre', DB::raw('count(*) as total'))
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
     * Crear una nueva cuenta (CRUD completo para nivel 2 en adelante)
     */
    public function store(Request $request)
    {
        try {
            // Validación básica
            $request->validate([
                'id_padre' => 'required|integer|exists:plan_cuentas,id',
                'descripcion' => 'required|string|max:255',
                'tipo' => 'nullable|integer|exists:subdominios,id',
                'grupo_estado_financiero' => 'nullable|integer|exists:subdominios,id',
                'cuenta_ajuste' => 'nullable|integer|exists:subdominios,id',
                'cuenta_presupuesto' => 'nullable|integer|exists:subdominios,id',
            ]);

            // Obtener cuenta padre
            $padre = PlanCuenta::findOrFail($request->id_padre);
            
            // Verificar restricciones por nivel
            if ($padre->nivel == 1) {
                // Solo se pueden añadir hijos al nivel 1 (crear nivel 2)
                $nuevoNivel = 2;
            } else if ($padre->nivel >= 2 && $padre->nivel <= 5) {
                // CRUD completo para nivel 2 en adelante
                $nuevoNivel = $padre->nivel + 1;
                
                // Verificar que no exceda nivel 6
                if ($nuevoNivel > 6) {
                    return response()->json([
                        'error' => 'No se pueden crear cuentas más allá del nivel 6'
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
                    'error' => 'No se pudo generar el código para la nueva cuenta'
                ], 400);
            }

            // Crear la cuenta
            $cuenta = PlanCuenta::create([
                'id_padre' => $request->id_padre,
                'codigo' => $nuevoCodigo,
                'descripcion' => $request->descripcion,
                'nivel' => $nuevoNivel,
                'tipo' => $request->tipo,
                'grupo_estado_financiero' => $request->grupo_estado_financiero,
                'cuenta_ajuste' => $request->cuenta_ajuste,
                'cuenta_presupuesto' => $request->cuenta_presupuesto,
                'estado' => 1,
            ]);

            // Retornar con relaciones cargadas
            $cuenta->load([
                'tipoSubdominio:id,descripcion',
                'grupoEstadoFinancieroSubdominio:id,descripcion',
                'cuentaAjusteSubdominio:id,descripcion',
                'cuentaPresupuestoSubdominio:id,descripcion'
            ]);

            return response()->json([
                'message' => 'Cuenta creada exitosamente',
                'cuenta' => $cuenta
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear cuenta', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Error al crear cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cuenta existente
     */
    public function update(Request $request, $id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);

            // Restricciones por nivel
            if ($cuenta->nivel == 1) {
                // Nivel 1: Solo se puede modificar descripción y subdominios
                $request->validate([
                    'descripcion' => 'required|string|max:255',
                    'tipo' => 'nullable|integer|exists:subdominios,id',
                    'grupo_estado_financiero' => 'nullable|integer|exists:subdominios,id',
                    'cuenta_ajuste' => 'nullable|integer|exists:subdominios,id',
                    'cuenta_presupuesto' => 'nullable|integer|exists:subdominios,id',
                ]);

                $cuenta->update([
                    'descripcion' => $request->descripcion,
                    'tipo' => $request->tipo,
                    'grupo_estado_financiero' => $request->grupo_estado_financiero,
                    'cuenta_ajuste' => $request->cuenta_ajuste,
                    'cuenta_presupuesto' => $request->cuenta_presupuesto,
                ]);

            } else if ($cuenta->nivel >= 2) {
                // Nivel 2 en adelante: CRUD completo
                $request->validate([
                    'descripcion' => 'required|string|max:255',
                    'tipo' => 'nullable|integer|exists:subdominios,id',
                    'grupo_estado_financiero' => 'nullable|integer|exists:subdominios,id',
                    'cuenta_ajuste' => 'nullable|integer|exists:subdominios,id',
                    'cuenta_presupuesto' => 'nullable|integer|exists:subdominios,id',
                ]);

                $cuenta->update([
                    'descripcion' => $request->descripcion,
                    'tipo' => $request->tipo,
                    'grupo_estado_financiero' => $request->grupo_estado_financiero,
                    'cuenta_ajuste' => $request->cuenta_ajuste,
                    'cuenta_presupuesto' => $request->cuenta_presupuesto,
                ]);
            }

            // Retornar con relaciones cargadas
            $cuenta->load([
                'tipoSubdominio:id,descripcion',
                'grupoEstadoFinancieroSubdominio:id,descripcion',
                'cuentaAjusteSubdominio:id,descripcion',
                'cuentaPresupuestoSubdominio:id,descripcion'
            ]);

            return response()->json([
                'message' => 'Cuenta actualizada exitosamente',
                'cuenta' => $cuenta
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar cuenta', [
                'message' => $e->getMessage(),
                'cuenta_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Error al actualizar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cuenta (solo nivel 2 en adelante)
     */
    public function destroy($id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);

            // Restricción: No se puede eliminar nivel 1
            if ($cuenta->nivel == 1) {
                return response()->json([
                    'error' => 'No se pueden eliminar cuentas de nivel 1'
                ], 400);
            }

            // Verificar si tiene hijos activos
            $tieneHijos = PlanCuenta::where('id_padre', $id)
                ->where('estado', 1)
                ->exists();

            if ($tieneHijos) {
                return response()->json([
                    'error' => 'No se puede eliminar una cuenta que tiene cuentas hijas activas'
                ], 400);
            }

            // Desactivar (soft delete)
            $cuenta->update(['estado' => 0]);

            return response()->json([
                'message' => 'Cuenta desactivada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al desactivar cuenta', [
                'message' => $e->getMessage(),
                'cuenta_id' => $id
            ]);

            return response()->json([
                'error' => 'Error al desactivar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas inactivas
     */
    public function inactivas()
    {
        try {
            $cuentas = PlanCuenta::with([
                'tipoSubdominio:id,descripcion',
                'grupoEstadoFinancieroSubdominio:id,descripcion',
                'cuentaAjusteSubdominio:id,descripcion',
                'cuentaPresupuestoSubdominio:id,descripcion'
            ])
            ->where('estado', 0)
            ->orderBy('codigo')
            ->get();

            return response()->json($cuentas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cuentas inactivas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivar cuenta
     */
    public function reactivar($id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);
            $cuenta->update(['estado' => 1]);

            return response()->json([
                'message' => 'Cuenta reactivada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al reactivar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas que pueden ser padres según el contexto
     */
    public function getCuentasPadre(Request $request)
    {
        try {
            // Si se especifica un nivel, obtener cuentas de ese nivel
            $nivel = $request->get('nivel');
            
            if ($nivel) {
                $cuentasPadre = PlanCuenta::where('nivel', $nivel)
                    ->where('estado', 1)
                    ->orderBy('codigo')
                    ->get(['id', 'codigo', 'descripcion', 'nivel']);
            } else {
                // Por defecto, obtener cuentas que pueden tener hijos (niveles 1-5)
                $cuentasPadre = PlanCuenta::whereIn('nivel', [1, 2, 3, 4, 5])
                    ->where('estado', 1)
                    ->orderBy('codigo')
                    ->get(['id', 'codigo', 'descripcion', 'nivel']);
            }

            return response()->json($cuentasPadre);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cuentas padre',
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
            $dominioRequerido = $request->get('dominio'); // 2, 3, 4, etc.
            
            if ($dominioRequerido) {
                // Filtrar por id_dominio específico si se proporciona
                $subdominios = Subdominio::where('id_dominio', $dominioRequerido)
                    ->orderBy('descripcion')
                    ->get(['id', 'descripcion', 'id_dominio']);
            } else {
                // Obtener todos los subdominios de los dominios relevantes
                $subdominios = Subdominio::whereIn('id_dominio', [2, 3, 4]) // Solo dominios de plan de cuentas
                    ->orderBy('id_dominio')
                    ->orderBy('descripcion')
                    ->get(['id', 'descripcion', 'id_dominio']);
            }

            Log::info('Subdominios obtenidos', [
                'dominio_solicitado' => $dominioRequerido,
                'total_subdominios' => $subdominios->count(),
                'por_dominio' => $subdominios->groupBy('id_dominio')->map->count(),
                'muestra' => $subdominios->take(5)->toArray()
            ]);

            return response()->json($subdominios);
        } catch (\Exception $e) {
            Log::error('Error al obtener subdominios', [
                'message' => $e->getMessage(),
                'dominio_requerido' => $request->get('dominio'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener subdominios',
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

            Log::info('Subdominios por dominio obtenidos', [
                'id_dominio' => $idDominio,
                'total' => $subdominios->count(),
                'subdominios' => $subdominios->toArray()
            ]);

            return response()->json($subdominios);
        } catch (\Exception $e) {
            Log::error('Error al obtener subdominios por dominio', [
                'message' => $e->getMessage(),
                'id_dominio' => $idDominio,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener subdominios por dominio',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar código automáticamente basado en el padre
     */
    private function generarCodigo($padre, $nuevoNivel)
    {
        try {
            $codigoPadre = $padre->codigo;
            
            if ($nuevoNivel <= 3) {
                // Niveles 2 y 3: agregar número consecutivo
                $hermanos = PlanCuenta::where('id_padre', $padre->id)
                    ->where('estado', 1)
                    ->count();
                
                $siguienteNumero = $hermanos + 1;
                return $codigoPadre . '.' . $siguienteNumero;
                
            } else if ($nuevoNivel >= 4) {
                // Nivel 4 en adelante: agregar 00N
                $hermanos = PlanCuenta::where('id_padre', $padre->id)
                    ->where('estado', 1)
                    ->count();
                
                $siguienteNumero = $hermanos + 1;
                $numeroFormateado = str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);
                return $codigoPadre . '.' . $numeroFormateado;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error generando código', [
                'padre_codigo' => $padre->codigo,
                'nuevo_nivel' => $nuevoNivel,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}