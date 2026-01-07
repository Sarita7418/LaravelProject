<?php
namespace App\Http\Controllers;

use App\Models\StockActual;
use App\Models\Producto;
use App\Models\Subdominio;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockActual::with(['producto', 'lote', 'ubicacion'])
                           ->where('cantidad', '>', 0);

        if ($request->has('producto')) {
            $query->where('id_producto', $request->producto);
        }

        if ($request->has('ubicacion')) {
            $query->where('id_ubicacion', $request->ubicacion);
        }

        if ($request->has('search')) {
            $query->whereHas('producto', function($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->search}%")
                  ->orWhere('codigo_interno', 'like', "%{$request->search}%");
            });
        }

        // Filtrar productos bajo stock mínimo
        if ($request->has('bajo_minimo')) {
            $query->whereHas('producto', function($q) {
                $q->whereRaw('stock_actual.cantidad <= productos.stock_minimo');
            });
        }

        $stock = $query->orderBy('updated_at', 'desc')->paginate(30);
        $productos = Producto::where('id_estado_producto', 22)
                           ->where('rastrea_inventario', true)
                           ->orderBy('nombre')
                           ->get();
        
        // Resumen general
        $resumen = StockActual::obtenerResumenGeneral();

        return view('dashboard.stock.index', compact('stock', 'productos', 'resumen'));
    }

    public function showProducto($id)
    {
        $producto = Producto::with(['categoria', 'unidadMedida'])->findOrFail($id);
        $stock = StockActual::where('id_producto', $id)
                           ->with(['lote', 'ubicacion'])
                           ->where('cantidad', '>', 0)
                           ->get();
        
        $resumen = StockActual::obtenerResumenStockPorProducto($id);
        $historial = \App\Models\MovimientoInventario::where('id_producto', $id)
            ->with(['tipoMovimiento', 'usuario'])
            ->orderBy('fecha', 'desc')
            ->limit(20)
            ->get();

        return view('dashboard.stock.producto', compact('producto', 'stock', 'resumen', 'historial'));
    }

    public function reporteBajoStock()
    {
        $productosBajoStock = StockActual::obtenerProductosBajoStockMinimo();
        $resumen = StockActual::obtenerResumenGeneral();

        return view('dashboard.stock.bajo-stock', compact('productosBajoStock', 'resumen'));
    }

    public function ajustarStock(Request $request, $id)
    {
        $validated = $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'id_lote' => 'required|exists:lotes,id',
            'id_ubicacion' => 'required|exists:politicos_ubicacion,id',
            'nueva_cantidad' => 'required|integer|min:0',
            'motivo' => 'required|string|max:500',
            'costo_unitario' => 'nullable|numeric|min:0',
        ]);

        try {
            $stock = StockActual::buscarStock(
                $validated['id_producto'],
                $validated['id_lote'],
                $validated['id_ubicacion']
            );

            if (!$stock) {
                $stock = StockActual::crearOActualizarStock(
                    $validated['id_producto'],
                    $validated['id_lote'],
                    $validated['id_ubicacion'],
                    0
                );
            }

            $ajuste = $stock->ajustarStock($validated['nueva_cantidad'], $validated['motivo']);

            // Registrar movimiento de ajuste
            \App\Models\MovimientoInventario::create([
                'fecha' => now(),
                'id_tipo_movimiento' => 32, // AJUSTE
                'referencia' => 'Ajuste manual: ' . $validated['motivo'],
                'id_producto' => $validated['id_producto'],
                'id_lote' => $validated['id_lote'],
                'cantidad_entrada' => $ajuste['diferencia'] > 0 ? $ajuste['diferencia'] : 0,
                'cantidad_salida' => $ajuste['diferencia'] < 0 ? abs($ajuste['diferencia']) : 0,
                'costo_unitario' => $validated['costo_unitario'] ?? $stock->producto->precio_entrada,
                'costo_total' => $validated['costo_unitario'] ?? $stock->producto->precio_entrada * abs($ajuste['diferencia']),
                'id_usuario' => auth()->id(),
            ]);

            return back()->with('success', 'Stock ajustado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }

    public function transferirStock(Request $request)
    {
        $validated = $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'id_lote' => 'required|exists:lotes,id',
            'id_ubicacion_origen' => 'required|exists:politicos_ubicacion,id',
            'id_ubicacion_destino' => 'required|exists:politicos_ubicacion,id|different:id_ubicacion_origen',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $stockOrigen = StockActual::buscarStock(
                $validated['id_producto'],
                $validated['id_lote'],
                $validated['id_ubicacion_origen']
            );

            if (!$stockOrigen || !$stockOrigen->puedeDespachar($validated['cantidad'])) {
                throw new \Exception('Stock insuficiente en la ubicación de origen.');
            }

            $transferencia = $stockOrigen->transferirStock(
                $validated['id_ubicacion_destino'],
                $validated['cantidad']
            );

            // Registrar movimiento de transferencia
            \App\Models\MovimientoInventario::create([
                'fecha' => now(),
                'id_tipo_movimiento' => 31, // SALIDA
                'referencia' => 'Transferencia: ' . ($validated['motivo'] ?? 'Sin motivo'),
                'id_producto' => $validated['id_producto'],
                'id_lote' => $validated['id_lote'],
                'cantidad_salida' => $validated['cantidad'],
                'costo_unitario' => $stockOrigen->producto->precio_entrada,
                'costo_total' => $stockOrigen->producto->precio_entrada * $validated['cantidad'],
                'id_ubicacion_origen' => $validated['id_ubicacion_origen'],
                'id_ubicacion_destino' => $validated['id_ubicacion_destino'],
                'id_usuario' => auth()->id(),
            ]);

            \App\Models\MovimientoInventario::create([
                'fecha' => now(),
                'id_tipo_movimiento' => 30, // COMPRA (entrada)
                'referencia' => 'Transferencia recibida: ' . ($validated['motivo'] ?? 'Sin motivo'),
                'id_producto' => $validated['id_producto'],
                'id_lote' => $validated['id_lote'],
                'cantidad_entrada' => $validated['cantidad'],
                'costo_unitario' => $stockOrigen->producto->precio_entrada,
                'costo_total' => $stockOrigen->producto->precio_entrada * $validated['cantidad'],
                'id_ubicacion_origen' => $validated['id_ubicacion_origen'],
                'id_ubicacion_destino' => $validated['id_ubicacion_destino'],
                'id_usuario' => auth()->id(),
            ]);

            return back()->with('success', 'Transferencia realizada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al transferir stock: ' . $e->getMessage());
        }
    }

    public function exportarReporte(Request $request)
    {
        $stock = StockActual::with(['producto', 'lote', 'ubicacion'])
                           ->where('cantidad', '>', 0)
                           ->get();

        // Implementar lógica de exportación a Excel o PDF
        // Usar librerías como Maatwebsite/Laravel-Excel o DomPDF
        
        return response()->json(['success' => true, 'data' => $stock]);
    }
}