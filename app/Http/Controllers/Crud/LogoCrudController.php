<?php
namespace App\Http\Controllers\Crud;

use App\Models\Logo;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;  // Importar JsonResponse
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogoCrudController extends Controller
{
    /**
     * Crear un nuevo logo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tipo_entidad' => ['required', Rule::in(['empresa', 'sucursal'])],
            'id_entidad' => ['required', 'integer'],
        ]);

        // existencia condicional
        $request->validate([
            'id_entidad' => $data['tipo_entidad'] === 'empresa'
                ? 'exists:empresas,id'
                : 'exists:sucursales,id'
        ]);

        try {
            DB::beginTransaction();

            $logoBinary = file_get_contents($request->file('logo')->getRealPath());

            Logo::create([
                'id_entidad' => $data['id_entidad'],
                'tipo_entidad' => $data['tipo_entidad'],
                'logo' => $logoBinary,
            ]);

            DB::commit();
            return response()->json(['message' => 'Logo creado exitosamente'], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('LOGO STORE DB ERROR', ['sql' => $e->getMessage(), 'info' => $e->errorInfo ?? null]);
            return response()->json([
                'message' => 'Error al crear el logo',
                'sql_code' => $e->errorInfo[1] ?? null,
                'sql_msg' => $e->errorInfo[2] ?? $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('LOGO STORE ERROR', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error inesperado al crear el logo'], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tipo_entidad' => ['nullable', Rule::in(['empresa', 'sucursal'])],
            'id_entidad' => ['nullable', 'integer'],
        ]);

        // si vienen tipo+id, valida existencia condicional
        if ($request->filled('tipo_entidad') && $request->filled('id_entidad')) {
            $request->validate([
                'id_entidad' => $request->tipo_entidad === 'empresa'
                    ? 'exists:empresas,id'
                    : 'exists:sucursales,id'
            ]);
        }

        try {
            $logo = Logo::findOrFail($id);

            if ($request->hasFile('logo')) {
                $logo->logo = file_get_contents($request->file('logo')->getRealPath());
            }
            if ($request->filled('id_entidad'))
                $logo->id_entidad = $request->id_entidad;
            if ($request->filled('tipo_entidad'))
                $logo->tipo_entidad = $request->tipo_entidad;

            $logo->save();

            return response()->json(['message' => 'Logo actualizado exitosamente']);
        } catch (QueryException $e) {
            Log::error('LOGO UPDATE DB ERROR', ['sql' => $e->getMessage(), 'info' => $e->errorInfo ?? null]);
            return response()->json([
                'message' => 'Error al actualizar el logo',
                'sql_code' => $e->errorInfo[1] ?? null,
                'sql_msg' => $e->errorInfo[2] ?? $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('LOGO UPDATE ERROR', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Error inesperado al actualizar el logo'], 500);
        }
    }

    /**
     * Listar todos los logos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Sin ->with('entidad')
            $logos = Logo::query()->get();

            foreach ($logos as $logo) {
                // por si acaso: maneja null o string
                $logo->logo = base64_encode($logo->logo ?? '');
            }

            return response()->json($logos);
        } catch (\Throwable $e) {
            \Log::error('LOGOS INDEX ERROR', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Error al listar los logos'], 500);
        }
    }



    /**
     * Editar un logo existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * Eliminar un logo (borrado absoluto).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Buscar el logo por ID y eliminarlo
            $logo = Logo::findOrFail($id);
            $logo->delete();

            return response()->json(['message' => 'Logo eliminado exitosamente'], 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Error al eliminar el logo'], 500);
        }
    }
}
