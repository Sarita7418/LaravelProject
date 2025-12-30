<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    /**
     * Obtener empresas para select en compras (como proveedores)
     */
    public function getParaSelectCompras(Request $request)
    {
        try {
            $term = $request->search ?? '';
            
            $proveedores = Empresa::where('estado', true)
                ->where(function($q) use ($term) {
                    $q->where('razon_social', 'like', "%{$term}%")
                      ->orWhere('nombre_comercial', 'like', "%{$term}%")
                      ->orWhere('nit', 'like', "%{$term}%");
                })
                ->orderBy('razon_social')
                ->limit(20)
                ->get()
                ->map(function($empresa) {
                    return [
                        'value' => $empresa->id,
                        'label' => "{$empresa->razon_social} - NIT: {$empresa->nit}",
                        'empresa' => [
                            'id' => $empresa->id,
                            'razon_social' => $empresa->razon_social,
                            'nombre_comercial' => $empresa->nombre_comercial,
                            'nit' => $empresa->nit,
                            'telefono' => $empresa->telefono,
                            'email' => $empresa->email,
                            'direccion_fiscal' => $empresa->direccion_fiscal
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $proveedores
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getParaSelectCompras de proveedores:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear proveedor desde modal de compras
     */
    public function storeProveedorDesdeCompra(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'nit' => 'required|string|max:20|unique:empresas,nit',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'direccion_fiscal' => 'required|string',
            'nombre_comercial' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'matricula_comercio' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Crear empresa proveedora
            $empresa = Empresa::create([
                'razon_social' => $validated['razon_social'],
                'nombre_comercial' => $validated['nombre_comercial'] ?? $validated['razon_social'],
                'nit' => $validated['nit'],
                'telefono' => $validated['telefono'],
                'email' => $validated['email'],
                'direccion_fiscal' => $validated['direccion_fiscal'],
                'municipio' => $validated['municipio'] ?? null,
                'departamento' => $validated['departamento'] ?? null,
                'matricula_comercio' => $validated['matricula_comercio'] ?? null,
                'estado' => true,
            ]);

            // Crear sucursal principal automáticamente
            $sucursal = Sucursal::create([
                'id_empresa' => $empresa->id,
                'nombre' => 'Casa Matriz',
                'codigo_sucursal' => '0',
                'direccion' => $validated['direccion_fiscal'],
                'telefono' => $validated['telefono'],
                'email' => $validated['email'],
                'estado' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'data' => [
                    'empresa' => $empresa,
                    'sucursal' => $sucursal
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeProveedorDesdeCompra:', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sucursales de un proveedor específico
     */
    public function getSucursalesPorProveedor(Request $request)
    {
        try {
            $request->validate([
                'id_proveedor' => 'required|exists:empresas,id'
            ]);

            $sucursales = Sucursal::where('id_empresa', $request->id_proveedor)
                ->where('estado', true)
                ->orderBy('nombre')
                ->get()
                ->map(function($sucursal) {
                    return [
                        'value' => $sucursal->id,
                        'label' => $sucursal->nombre . 
                                 ($sucursal->codigo_sucursal != '0' ? " ({$sucursal->codigo_sucursal})" : ""),
                        'sucursal' => [
                            'id' => $sucursal->id,
                            'nombre' => $sucursal->nombre,
                            'codigo_sucursal' => $sucursal->codigo_sucursal,
                            'direccion' => $sucursal->direccion,
                            'telefono' => $sucursal->telefono,
                            'email' => $sucursal->email
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sucursales
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getSucursalesPorProveedor:', [
                'error' => $e->getMessage(),
                'proveedor_id' => $request->id_proveedor
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar sucursales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar proveedores por término
     */
    public function buscar(Request $request)
    {
        try {
            $request->validate([
                'search' => 'required|string|min:2'
            ]);

            $proveedores = Empresa::where('estado', true)
                ->where(function($q) use ($request) {
                    $q->where('razon_social', 'like', "%{$request->search}%")
                      ->orWhere('nombre_comercial', 'like', "%{$request->search}%")
                      ->orWhere('nit', 'like', "%{$request->search}%");
                })
                ->orderBy('razon_social')
                ->limit(15)
                ->get(['id', 'razon_social', 'nit', 'telefono']);

            return response()->json([
                'success' => true,
                'data' => $proveedores
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en buscar proveedores:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información completa de un proveedor
     */
    public function show($id)
    {
        try {
            $proveedor = Empresa::with(['sucursales' => function($q) {
                $q->where('estado', true)->orderBy('nombre');
            }])
            ->where('id', $id)
            ->where('estado', true)
            ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $proveedor
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en show de proveedor:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}