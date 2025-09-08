<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\PlanCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanCuentasCrudController extends Controller
{
    public function index()
    {
        try {
            $cuentas = PlanCuenta::where('estado', 'ACTIVO')
                                ->orderBy('codigo')
                                ->get();
            
            Log::info('Plan cuentas obtenidas:', ['count' => $cuentas->count()]);
            
            return response()->json($cuentas);
        } catch (\Exception $e) {
            Log::error('Error en index plan cuentas:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Error al obtener plan de cuentas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|unique:plan_cuentas,codigo',
            'descripcion' => 'required|string',
            'nivel' => 'required|integer|max:5',
            'id_padre' => 'nullable|integer|exists:plan_cuentas,id'
        ]);

        if ($request->nivel != 5) {
            return response()->json([
                'error' => 'Solo se pueden crear cuentas en el nivel 5'
            ], 400);
        }

        try {
            $cuenta = PlanCuenta::create([
                'id_padre' => $request->id_padre,
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'nivel' => $request->nivel,
                'es_movimiento' => $request->es_movimiento ?? 1,
                'estado' => 'ACTIVO', 
                'debe' => $request->debe ?? 0.00,
                'haber' => $request->haber ?? 0.00,
                'saldo' => $request->saldo ?? 0.00
            ]);
            
            return response()->json($cuenta, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);
            if ($cuenta->nivel != 5) {
                return response()->json([
                    'error' => 'Solo se pueden editar cuentas del nivel 5'
                ], 400);
            }

            $request->validate([
                'codigo' => 'required|string|unique:plan_cuentas,codigo,' . $id,
                'descripcion' => 'required|string'
            ]);

            $cuenta->update([
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'debe' => $request->debe ?? $cuenta->debe,
                'haber' => $request->haber ?? $cuenta->haber,
                'saldo' => $request->saldo ?? $cuenta->saldo
            ]);
            
            return response()->json($cuenta);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);
            
            if ($cuenta->nivel != 5) {
                return response()->json([
                    'error' => 'Solo se pueden eliminar cuentas del nivel 5'
                ], 400);
            }

            $cuenta->update(['estado' => 'INACTIVO']); 
            
            return response()->json(['message' => 'Cuenta desactivada correctamente']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al desactivar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function inactivas()
    {
        try {
            $cuentas = PlanCuenta::where('estado', 'INACTIVO')
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

    public function reactivar($id)
    {
        try {
            $cuenta = PlanCuenta::findOrFail($id);
            $cuenta->update(['estado' => 'ACTIVO']); 
            
            return response()->json(['message' => 'Cuenta reactivada correctamente']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al reactivar cuenta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCuentasPadre()
    {
        try {
            $cuentasPadre = PlanCuenta::where('nivel', 4)
                                    ->where('estado', 'ACTIVO')
                                    ->orderBy('codigo')
                                    ->get();
            return response()->json($cuentasPadre);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cuentas padre',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}