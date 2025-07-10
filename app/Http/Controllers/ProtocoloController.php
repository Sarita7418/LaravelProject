<?php

namespace App\Http\Controllers;

use App\Models\Protocolo;
use App\Models\Especialidad;
use App\Models\Subdominio;
use App\Models\Dominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Exception;

class ProtocoloController extends Controller
{
    public function index()
    {
        try {
            $protocolos = Protocolo::with(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador'])->get();

            $protocolos->transform(function ($protocolo) {
                $protocolo->activo = (bool) $protocolo->activo;
                return $protocolo;
            });

            return response()->json($protocolos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener protocolos'], 500);
        }
    }

    public function activos()
    {
        try {
            $protocolos = Protocolo::with(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador'])
                ->where('activo', true)
                ->get();

            return response()->json($protocolos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener protocolos activos'], 500);
        }
    }

    public function inactivos()
    {
        try {
            $protocolos = Protocolo::with(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador'])
                ->where('activo', false)
                ->get();

            return response()->json($protocolos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener protocolos inactivos'], 500);
        }
    }

    public function porEstado($estado)
    {
        try {
            $protocolos = Protocolo::with(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador'])
                ->whereHas('estado', function ($query) use ($estado) {
                    $query->where('descripcion', $estado);
                })
                ->where('activo', true)
                ->get();

            return response()->json($protocolos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener protocolos por estado'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'titulo' => 'required|string|max:200',
                'resumen' => 'required|string',
                'objetivo_general' => 'required|string',
                'metodologia' => 'required|string',
                'justificacion' => 'required|string',
                'id_usuario_creador' => 'required|exists:usuarios,id',
                'id_estado' => 'required|exists:subdominios,id',
            ]);

            $idEspecialidad = $request->id_especialidad;
            if ($request->nueva_especialidad) {
                $especialidad = Especialidad::firstOrCreate(['nombre' => $request->nueva_especialidad]);
                $idEspecialidad = $especialidad->id;
            }

            $idAreaImpacto = $request->id_area_impacto;
            if ($request->nueva_area) {
                $dominioArea = Dominio::where('descripcion', 'area_impacto')->first();
                if ($dominioArea) {
                    $area = Subdominio::firstOrCreate([
                        'id_dominio' => $dominioArea->id,
                        'descripcion' => $request->nueva_area
                    ]);
                    $idAreaImpacto = $area->id;
                }
            }

            $protocolo = Protocolo::create([
                'titulo' => $request->titulo,
                'resumen' => $request->resumen,
                'objetivo_general' => $request->objetivo_general,
                'metodologia' => $request->metodologia,
                'justificacion' => $request->justificacion,
                'id_usuario_creador' => $request->id_usuario_creador,
                'id_especialidad' => $idEspecialidad,
                'id_estado' => $request->id_estado,
                'id_area_impacto' => $idAreaImpacto,
                'fecha_creacion' => Carbon::now()->format('Y-m-d'),
                'activo' => true
            ]);

            $protocolo->load(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador']);

            return response()->json($protocolo, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al crear protocolo'], 500);
        }
    }

    public function show($id)
    {
        try {
            $protocolo = Protocolo::with(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador'])->findOrFail($id);
            return response()->json($protocolo);
        } catch (Exception $e) {
            return response()->json(['error' => 'Protocolo no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'titulo' => 'required|string|max:200',
                'resumen' => 'required|string',
                'objetivo_general' => 'required|string',
                'metodologia' => 'required|string',
                'justificacion' => 'required|string',
            ]);

            $protocolo = Protocolo::findOrFail($id);

            $idEspecialidad = $request->id_especialidad;
            if ($request->nueva_especialidad) {
                $especialidad = Especialidad::firstOrCreate(['nombre' => $request->nueva_especialidad]);
                $idEspecialidad = $especialidad->id;
            }

            $idAreaImpacto = $request->id_area_impacto;
            if ($request->nueva_area) {
                $dominioArea = Dominio::where('descripcion', 'area_impacto')->first();
                if ($dominioArea) {
                    $area = Subdominio::firstOrCreate([
                        'id_dominio' => $dominioArea->id,
                        'descripcion' => $request->nueva_area
                    ]);
                    $idAreaImpacto = $area->id;
                }
            }

            $protocolo->update([
                'titulo' => $request->titulo,
                'resumen' => $request->resumen,
                'objetivo_general' => $request->objetivo_general,
                'metodologia' => $request->metodologia,
                'justificacion' => $request->justificacion,
                'id_especialidad' => $idEspecialidad,
                'id_estado' => $request->id_estado,
                'id_area_impacto' => $idAreaImpacto,
            ]);

            $protocolo->load(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador']);

            return response()->json($protocolo);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar protocolo'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $protocolo = Protocolo::findOrFail($id);
            $protocolo->update(['activo' => false]);

            return response()->json(['mensaje' => 'Protocolo desactivado']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al desactivar protocolo'], 500);
        }
    }

    public function archivar($id)
    {
        try {
            $protocolo = Protocolo::findOrFail($id);

            $estadoArchivado = Subdominio::whereHas('dominio', function ($query) {
                $query->where('descripcion', 'estado_protocolo');
            })->where('descripcion', 'Archivado')->first();

            if (!$estadoArchivado) {
                return response()->json(['error' => 'Estado "Archivado" no encontrado'], 400);
            }

            $protocolo->update([
                'activo' => false,
                'id_estado' => $estadoArchivado->id
            ]);

            $protocolo->load(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador']);

            return response()->json([
                'mensaje' => 'Protocolo archivado',
                'protocolo' => $protocolo
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al archivar protocolo'], 500);
        }
    }

    public function reactivar($id)
    {
        try {
            $protocolo = Protocolo::findOrFail($id);

            $estadoActivo = Subdominio::whereHas('dominio', function ($query) {
                $query->where('descripcion', 'estado_protocolo');
            })->where('descripcion', 'Activo')->first();

            if (!$estadoActivo) {
                return response()->json(['error' => 'Estado "Activo" no encontrado'], 400);
            }

            $protocolo->update([
                'activo' => true,
                'id_estado' => $estadoActivo->id
            ]);

            $protocolo->load(['especialidad', 'estado', 'areaImpacto', 'usuarioCreador']);

            return response()->json([
                'mensaje' => 'Protocolo reactivado',
                'protocolo' => $protocolo
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al reactivar protocolo'], 500);
        }
    }

    public function catalogos()
    {
        try {
            $dominioEstado = Dominio::where('descripcion', 'estado_protocolo')->first();
            $dominioArea = Dominio::where('descripcion', 'area_impacto')->first();

            $estadoId = $dominioEstado ? $dominioEstado->id : null;
            $areaId = $dominioArea ? $dominioArea->id : null;

            return response()->json([
                'especialidades' => Especialidad::all(['id', 'nombre']),
                'estados' => $estadoId ? Subdominio::where('id_dominio', $estadoId)->get(['id', 'descripcion']) : [],
                'areasImpacto' => $areaId ? Subdominio::where('id_dominio', $areaId)->get(['id', 'descripcion']) : [],
                'usuario_autenticado' => Auth::user(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener catálogos'], 500);
        }
    }
}
