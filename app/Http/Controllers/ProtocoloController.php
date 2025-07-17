<?php

namespace App\Http\Controllers;

use App\Models\Protocolo;
use App\Models\Especialidad;
use App\Models\Subdominio;
use App\Models\AreaImpacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Exception;

class ProtocoloController extends Controller
{
    public function index()
    {
        try {
            $protocolos = Protocolo::with(['especialidad', 'estado', 'areasImpacto'])->get();

            return response()->json($protocolos);
        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los protocolos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'resumen' => 'nullable|string',
            'objetivo_general' => 'nullable|string',
            'justificacion' => 'nullable|string',
            'metodologia' => 'nullable|string',
            'id_especialidad' => 'nullable|exists:especialidades,id',
            'areas_impacto' => 'nullable|array',
        ]);

        try {
            $estadoEnRevision = Subdominio::where('descripcion', 'En Revisión')
                ->orWhere('descripcion', 'En Revision')
                ->first();

            if (!$estadoEnRevision) {
                return response()->json([
                    'mensaje' => 'Error: No se encontró el estado "En Revisión" en el sistema',
                ], 500);
            }

            $protocolo = new Protocolo($request->only([
                'titulo',
                'resumen',
                'objetivo_general',
                'justificacion',
                'metodologia',
                'id_especialidad',
            ]));

            $protocolo->id_usuario_creador = Auth::id();
            $protocolo->fecha_creacion = Carbon::now();
            $protocolo->id_estado = $estadoEnRevision->id; 
            $protocolo->save();

            $areasImpactoIds = [];

            if ($request->filled('areas_impacto')) {
                foreach ($request->input('areas_impacto') as $area) {
                    if (isset($area['nueva_area'])) {
                        $nuevaArea = AreaImpacto::create([
                            'nombre' => $area['nueva_area']['nombre'],
                            'descripcion' => $area['nueva_area']['descripcion'],
                        ]);
                        $areasImpactoIds[] = $nuevaArea->id;
                    } elseif (isset($area['id_area_impactos'])) {
                        $areasImpactoIds[] = $area['id_area_impactos'];
                    }
                }
                $protocolo->areasImpacto()->sync($areasImpactoIds);
            }

            return response()->json([
                'mensaje' => 'Protocolo creado exitosamente con estado "En Revisión"',
                'protocolo' => $protocolo->load(['especialidad', 'estado', 'areasImpacto']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear el protocolo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string',
            'resumen' => 'nullable|string',
            'objetivo_general' => 'nullable|string',
            'justificacion' => 'nullable|string',
            'metodologia' => 'nullable|string',
            'id_especialidad' => 'nullable|exists:especialidades,id',
            'id_estado' => 'nullable|exists:subdominios,id',
            'areas_impacto' => 'nullable|array',
        ]);

        try {
            $protocolo = Protocolo::findOrFail($id);

            $protocolo->fill($request->only([
                'titulo',
                'resumen',
                'objetivo_general',
                'justificacion',
                'metodologia',
                'id_especialidad',
                'id_estado',
            ]));

            $protocolo->save();

            $areasImpactoIds = [];

            if ($request->filled('areas_impacto')) {
                foreach ($request->input('areas_impacto') as $area) {
                    if (isset($area['nueva_area'])) {
                        $nuevaArea = AreaImpacto::create([
                            'nombre' => $area['nueva_area']['nombre'],
                            'descripcion' => $area['nueva_area']['descripcion'],
                        ]);
                        $areasImpactoIds[] = $nuevaArea->id;
                    } elseif (isset($area['id_area_impactos'])) {
                        $areasImpactoIds[] = $area['id_area_impactos'];
                    }
                }
            }

            $protocolo->areasImpacto()->sync($areasImpactoIds);

            return response()->json([
                'mensaje' => 'Protocolo actualizado exitosamente',
                'protocolo' => $protocolo->load(['especialidad', 'estado', 'areasImpacto']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar protocolo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function archivar($id)
    {
        try {
            $protocolo = Protocolo::findOrFail($id);
            $estadoArchivado = Subdominio::where('descripcion', 'Archivado')->first();

            if (!$estadoArchivado) {
                return response()->json(['mensaje' => 'Estado Archivado no encontrado'], 404);
            }

            $protocolo->id_estado = $estadoArchivado->id;
            $protocolo->save();

            return response()->json(['mensaje' => 'Protocolo archivado correctamente']);
        } catch (Exception $e) {
            return response()->json(['mensaje' => 'Error al archivar protocolo', 'error' => $e->getMessage()], 500);
        }
    }

    public function reactivar($id)
    {
        try {
            $protocolo = Protocolo::findOrFail($id);
            $estadoActivo = Subdominio::where('descripcion', 'Activo')->first();

            if (!$estadoActivo) {
                return response()->json(['mensaje' => 'Estado Activo no encontrado'], 404);
            }

            $protocolo->id_estado = $estadoActivo->id;
            $protocolo->save();

            return response()->json(['mensaje' => 'Protocolo reactivado correctamente']);
        } catch (Exception $e) {
            return response()->json(['mensaje' => 'Error al reactivar protocolo', 'error' => $e->getMessage()], 500);
        }
    }
    public function catalogos()
    {
        try {
            $especialidades = Especialidad::all();
            $estados = Subdominio::where('id_dominio', 1)->get();
            $areasImpacto = AreaImpacto::all();

            return response()->json([
                'especialidades' => $especialidades,
                'estados' => $estados,
                'areasImpacto' => $areasImpacto,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'Error al cargar catálogos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}