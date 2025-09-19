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
        // Validación de los datos
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validar imagen
            'id_entidad' => 'required|exists:empresas,id|exists:sucursales,id', // Validar que la entidad exista
            'tipo_entidad' => ['required', Rule::in(['empresa', 'sucursal'])],
        ]);

        try {
            // Leer el archivo de logo en binario
            $logoBinary = file_get_contents($request->file('logo')->getRealPath());

            // Crear el logo asociado a la entidad (empresa o sucursal)
            Logo::create([
                'id_entidad' => $request->id_entidad,
                'tipo_entidad' => $request->tipo_entidad,
                'logo' => $logoBinary,
            ]);

            return response()->json(['message' => 'Logo creado exitosamente'], 201);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Error al crear el logo'], 500);
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
            $logos = Logo::with(['entidad'])->get();

            // Convertir el logo de BLOB a Base64
            foreach ($logos as $logo) {
                $logo->logo = base64_encode($logo->logo); // Convertir el logo a base64
            }

            return response()->json($logos);
        } catch (QueryException $e) {
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
    public function update(Request $request, $id): JsonResponse
    {
        // Validación de los datos
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validar imagen (opcional)
            'id_entidad' => 'nullable|exists:empresas,id|exists:sucursales,id', // Validar que la entidad exista
            'tipo_entidad' => ['nullable', Rule::in(['empresa', 'sucursal'])],
        ]);

        try {
            // Buscar el logo por ID
            $logo = Logo::findOrFail($id);

            // Si el logo es actualizado, obtenemos el archivo nuevo
            if ($request->hasFile('logo')) {
                $logoBinary = file_get_contents($request->file('logo')->getRealPath());
                $logo->logo = $logoBinary; // Actualizamos el archivo binario
            }

            // Actualizamos los campos de la entidad si se pasa un nuevo valor
            if ($request->has('id_entidad')) {
                $logo->id_entidad = $request->id_entidad;
            }

            if ($request->has('tipo_entidad')) {
                $logo->tipo_entidad = $request->tipo_entidad;
            }

            $logo->save(); // Guardamos los cambios

            return response()->json(['message' => 'Logo actualizado exitosamente']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Error al actualizar el logo'], 500);
        }
    }

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
