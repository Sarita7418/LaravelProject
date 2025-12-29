<?php
namespace App\Http\Controllers\Crud;

use App\Models\Sucursal;
use App\Models\Persona;  // Importamos el modelo Persona
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class SucursalCrudController extends Controller
{
    /**
     * Listar solo sucursales activas (estado = 1), con filtro por empresa si se envía.
     */
    public function index(Request $request)
    {
        return Sucursal::query()
            ->where('estado', 1)
            ->when($request->empresa_id, function ($query) use ($request) {
                return $query->where('id_empresa', $request->empresa_id);
            })
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * Crear una nueva sucursal.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_empresa'      => ['required', 'exists:empresas,id'],
            'nombre'          => ['required', 'string', 'max:255'],
            'codigo_sucursal' => ['nullable', 'string', 'max:50'],
            'direccion'       => ['required', 'string', 'max:255'],
            'telefono'        => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'string', 'max:150'],
            'id_sucursal_padre' => ['nullable', 'exists:sucursales,id'], // Validar que la sucursal madre exista
            'id_representante_legal' => ['nullable', 'exists:personas,id'], // Validar que el representante legal exista
        ]);

        try {
            \DB::beginTransaction();

            // Crear la sucursal
            $sucursal = Sucursal::create(array_merge($data, ['estado' => 1]));

            // Si se pasa un representante legal, lo asociamos
            if ($request->has('id_representante_legal')) {
                $sucursal->id_representante_legal = $request->id_representante_legal;
                $sucursal->save();
            }

            \DB::commit();

            return response()->json([
                'message'  => 'Sucursal creada exitosamente',
                'sucursal' => $sucursal
            ], 201);
        } catch (QueryException $qe) {
            \DB::rollBack();
            return response()->json([
                'message'   => 'Error de base de datos al crear la sucursal',
                'sql_state' => $qe->errorInfo[0] ?? null,
                'sql_code'  => $qe->errorInfo[1] ?? null,
                'sql_msg'   => $qe->errorInfo[2] ?? $qe->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error inesperado al crear la sucursal',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar los detalles de una sucursal.
     */
    public function show($id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada'], 404);
        }
        return $sucursal;
    }

    /**
     * Actualizar datos de una sucursal.
     */
    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada'], 404);
        }

        $data = $request->validate([
            'id_empresa'      => ['sometimes', 'required', 'exists:empresas,id'],
            'nombre'          => ['sometimes', 'required', 'string', 'max:255'],
            'codigo_sucursal' => ['nullable', 'string', 'max:50'],
            'direccion'       => ['sometimes', 'required', 'string', 'max:255'],
            'telefono'        => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'string', 'max:150'],
            'estado'          => ['nullable', 'boolean'],
            'id_sucursal_padre' => ['nullable', 'exists:sucursales,id'], // Validar la sub-sucursal
            'id_representante_legal' => ['nullable', 'exists:personas,id'], // Validar el representante legal
        ]);

        $sucursal->update($data);

        // Si se pasa un nuevo representante legal, lo actualizamos
        if ($request->has('id_representante_legal')) {
            $sucursal->id_representante_legal = $request->id_representante_legal;
            $sucursal->save();
        }

        return response()->json([
            'message'  => 'Sucursal actualizada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    /**
     * Desactivar sucursal (eliminación lógica).
     */
    public function destroy($id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada'], 404);
        }

        try {
            $sucursal->estado = 0;
            $sucursal->save();

            return response()->json(['message' => 'Sucursal desactivada correctamente']);
        } catch (QueryException $qe) {
            return response()->json([
                'message'   => 'Error de base de datos al desactivar la sucursal',
                'sql_state' => $qe->errorInfo[0] ?? null,
                'sql_code'  => $qe->errorInfo[1] ?? null,
                'sql_msg'   => $qe->errorInfo[2] ?? $qe->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar sucursales inactivas.
     */
    public function inactivos(Request $request)
    {
        return Sucursal::where('estado', 0)
            ->when($request->empresa_id, function ($query) use ($request) {
                return $query->where('id_empresa', $request->empresa_id);
            })
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Reactivar sucursal.
     */
    public function reactivar($id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada'], 404);
        }

        $sucursal->estado = 1;
        $sucursal->save();

        return response()->json(['message' => 'Sucursal reactivada correctamente']);
    }
}
