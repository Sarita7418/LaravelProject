<?php
namespace App\Http\Controllers\Crud;

use App\Models\Empresa;
use App\Models\Persona; // Agregamos el modelo Persona
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class EmpresaCrudController extends Controller
{
    /**
     * Crear una nueva empresa.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:20', 'unique:empresas,nit'],
            'matricula_comercio' => ['nullable', 'string', 'max:50'],
            'direccion_fiscal' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:150'],
            'municipio' => ['nullable', 'string', 'max:120'],
            'departamento' => ['nullable', 'string', 'max:120'],
            'id_representante_legal' => ['nullable', 'exists:personas,id'], // Validamos el representante legal
        ]);

        try {
            \DB::beginTransaction();

            // Crear la empresa
            $empresa = Empresa::create(array_merge($data, [
                'estado' => 1,
            ]));

            // Si se pasa un representante legal, lo asociamos
            if ($request->has('id_representante_legal')) {
                $empresa->id_representante_legal = $request->id_representante_legal;
                $empresa->save();
            }

            \DB::commit();

            return response()->json([
                'message' => 'Empresa creada exitosamente',
                'empresa' => $empresa
            ], 201);
        } catch (QueryException $qe) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error de base de datos al crear la empresa',
                'sql_state' => $qe->errorInfo[0] ?? null,
                'sql_code' => $qe->errorInfo[1] ?? null,
                'sql_msg' => $qe->errorInfo[2] ?? $qe->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error inesperado al crear la empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar empresa.
     */
    public function update(Request $request, $id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        $data = $request->validate([
            'razon_social' => ['sometimes', 'required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'nit' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('empresas', 'nit')->ignore($empresa->id)],
            'matricula_comercio' => ['nullable', 'string', 'max:50'],
            'direccion_fiscal' => ['sometimes', 'required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:150'],
            'municipio' => ['nullable', 'string', 'max:120'],
            'departamento' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'boolean'],
            'id_representante_legal' => ['nullable', 'integer', 'exists:personas,id'],
        ]);

        // (opcional) evita sobreescribir con nulls involuntarios:
        // $data = array_filter($data, fn($v) => !is_null($v));

        \Log::info('UPDATE payload validado', $data);

        // fill + dirty + save
        $empresa->fill($data);

        $dirty = $empresa->getDirty(); // qué campos realmente cambian
        \Log::info('Atributos que cambiarán', $dirty);

        // Si no hay cambios, igual retornamos el objeto
        if (!empty($dirty)) {
            $empresa->save();
        } else {
            \Log::info('No hay cambios detectados por Eloquent (getDirty vacío).');
        }

        // Devuelve el registro fresco desde la BD
        return response()->json($empresa->fresh());
    }



    /**
     * Desactivar (eliminación lógica).
     */
    public function destroy($id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        try {
            $empresa->estado = 0;
            $empresa->save();

            return response()->json(['message' => 'Empresa desactivada correctamente']);
        } catch (QueryException $qe) {
            return response()->json([
                'message' => 'Error de base de datos al desactivar la empresa',
                'sql_state' => $qe->errorInfo[0] ?? null,
                'sql_code' => $qe->errorInfo[1] ?? null,
                'sql_msg' => $qe->errorInfo[2] ?? $qe->getMessage(),
            ], 500);
        }
    }

    // 1) Index: SOLO activas
    public function index(Request $request)
    {
        return Empresa::query()
            ->where('estado', 1) // <--- clave
            ->when($request->q, function ($query) use ($request) {
                $term = '%' . $request->q . '%';
                $query->where(function ($qq) use ($term) {
                    $qq->where('razon_social', 'like', $term)
                        ->orWhere('nit', 'like', $term)
                        ->orWhere('nombre_comercial', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(15);
    }

    // 2) Inactivas: NUEVO endpoint que usas en el FE
    public function inactivos()
    {
        return Empresa::where('estado', 0)
            ->orderByDesc('id')
            ->get();
    }

    // 3) (opcional) Reactivar: por si aún no lo tienes
    public function reactivar($id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }
        $empresa->estado = 1;
        $empresa->save();
        return response()->json(['message' => 'Empresa reactivada correctamente']);
    }
}
