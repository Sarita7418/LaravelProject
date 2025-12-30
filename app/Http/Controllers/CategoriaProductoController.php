<?php
namespace App\Http\Controllers;

use App\Models\Subdominio;
use Illuminate\Http\Request;

class CategoriaProductoController extends Controller
{
    public function index(Request $request)
    {
        $categorias = Subdominio::where('id_dominio', 5)->get();
        
        return response()->json($categorias);
    }

    public function show($id)
    {
        $categoria = Subdominio::findOrFail($id);
        
        return response()->json($categoria);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:100|unique:subdominios,descripcion,NULL,id,id_dominio,5'
        ]);

        $categoria = Subdominio::create([
            'id_dominio' => 5, // Categoría producto
            'descripcion' => $validated['descripcion']
        ]);

        return response()->json($categoria, 201);
    }

    public function update(Request $request, $id)
    {
        $categoria = Subdominio::findOrFail($id);
        
        $validated = $request->validate([
            'descripcion' => 'required|string|max:100|unique:subdominios,descripcion,'.$id.',id,id_dominio,5'
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    public function destroy($id)
    {
        $categoria = Subdominio::findOrFail($id);
        
        // Verificar si hay productos usando esta categoría
        $productos = \App\Models\Producto::where('id_categoria', $id)->count();
        
        if ($productos > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la categoría porque tiene productos asociados.'
            ], 400);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada']);
    }

    public function create()
    {
        // Para vista de creación
        return response()->json(['status' => 'create_view']);
    }

    public function edit($id)
    {
        // Para vista de edición
        $categoria = Subdominio::findOrFail($id);
        return response()->json($categoria);
    }
}