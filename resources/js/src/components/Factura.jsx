import React, { useState } from 'react';
import axios from 'axios'; // Asegúrate de tener axios instalado: npm install axios

const Factura = () => {
  // --- ESTADOS ---
  // Datos de cabecera
  const [clienteId, setClienteId] = useState(1); // Default 1 (Juan Perez)

  // Datos del ítem actual (para agregar al carrito)
  const [productoIdActual, setProductoIdActual] = useState('');
  const [cantidadActual, setCantidadActual] = useState(1);

  // Carrito de compras (Array de objetos)
  const [detalles, setDetalles] = useState([]);

  // Estados de feedback
  const [mensaje, setMensaje] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(false);

  // --- FUNCIONES ---

  // Agregar producto a la lista temporal (Carrito)
  const agregarAlCarrito = (e) => {
    e.preventDefault();
    if (!productoIdActual || cantidadActual <= 0) return;

    // Crear objeto detalle
    const nuevoDetalle = {
      producto_id: parseInt(productoIdActual),
      cantidad: parseInt(cantidadActual),
      // Aquí podrías agregar nombre/precio si los tuvieras cargados para mostrar en la tabla
      temp_id: Date.now() // ID temporal para keys de React
    };

    setDetalles([...detalles, nuevoDetalle]);

    // Limpiar inputs del ítem
    setProductoIdActual('');
    setCantidadActual(1);
  };

  // Eliminar ítem del carrito
  const eliminarDelCarrito = (idTemporal) => {
    setDetalles(detalles.filter(item => item.temp_id !== idTemporal));
  };

  // Enviar todo al Backend (Laravel)
  const emitirFactura = async () => {
    if (detalles.length === 0) {
      setError("El carrito está vacío.");
      return;
    }

    setCargando(true);
    setMensaje(null);
    setError(null);

    try {
      // Estructura que espera tu API Laravel
      const payload = {
        cliente_id: parseInt(clienteId), // <--- Forzar a número
        detalles: detalles.map(d => ({
          producto_id: parseInt(d.producto_id), // <--- Forzar a número
          cantidad: parseInt(d.cantidad)       // <--- Forzar a número
        }))
      };

      // Petición POST
      const response = await axios.post('http://localhost:8000/api/facturas', payload);

      setMensaje(`✅ Venta exitosa! Factura ID: ${response.data.factura_id}`);
      setDetalles([]); // Limpiar carrito
    } catch (err) {
      console.error(err);
      setError(err.response?.data?.error || "❌ Error al procesar la venta.");
    } finally {
      setCargando(false);
    }
  };

  return (
    <div className="p-6 bg-gray-50 min-h-screen">
      <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 className="text-2xl font-bold mb-6 text-gray-800">Nueva Venta (Facturación)</h1>

        {/* --- MENSAJES --- */}
        {mensaje && <div className="mb-4 p-3 bg-green-100 text-green-700 rounded border border-green-300">{mensaje}</div>}
        {error && <div className="mb-4 p-3 bg-red-100 text-red-700 rounded border border-red-300">{error}</div>}

        {/* --- CABECERA --- */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-100 rounded">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">ID Cliente</label>
            <input
              type="number"
              className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              value={clienteId}
              onChange={(e) => setClienteId(e.target.value)}
            />
            <span className="text-xs text-gray-500">Por defecto: 1 (Juan Perez)</span>
          </div>
          <div className="flex items-end">
            <div className="text-sm text-gray-600">
              Fecha: {new Date().toLocaleDateString()}
            </div>
          </div>
        </div>

        {/* --- FORMULARIO DE PRODUCTO --- */}
        <form onSubmit={agregarAlCarrito} className="flex gap-4 items-end mb-6 border-b pb-6">
          <div className="flex-1">
            <label className="block text-sm font-medium text-gray-700 mb-1">ID Producto</label>
            <input
              type="number"
              placeholder="Ej: 1"
              className="w-full p-2 border border-gray-300 rounded"
              value={productoIdActual}
              onChange={(e) => setProductoIdActual(e.target.value)}
            />
          </div>
          <div className="w-32">
            <label className="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
            <input
              type="number"
              min="1"
              className="w-full p-2 border border-gray-300 rounded"
              value={cantidadActual}
              onChange={(e) => setCantidadActual(e.target.value)}
            />
          </div>
          <button
            type="submit"
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition"
          >
            + Agregar
          </button>
        </form>

        {/* --- TABLA DETALLE (CARRITO) --- */}
        <div className="mb-8">
          <h3 className="font-semibold text-lg mb-2">Detalle de Productos</h3>
          {detalles.length === 0 ? (
            <p className="text-gray-500 italic text-center py-4 border rounded bg-gray-50">
              No hay productos agregados. Añade uno arriba. 🐸
            </p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-100 text-gray-600 text-sm">
                    <th className="p-3 border-b">Producto ID</th>
                    <th className="p-3 border-b text-center">Cantidad</th>
                    <th className="p-3 border-b text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {detalles.map((item) => (
                    <tr key={item.temp_id} className="border-b hover:bg-gray-50">
                      <td className="p-3">{item.producto_id}</td>
                      <td className="p-3 text-center">{item.cantidad}</td>
                      <td className="p-3 text-right">
                        <button
                          onClick={() => eliminarDelCarrito(item.temp_id)}
                          className="text-red-500 hover:text-red-700 text-sm font-medium"
                        >
                          Eliminar
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* --- FOOTER / BOTÓN FINAL --- */}
        <div className="flex justify-end">
          <button
            onClick={emitirFactura}
            disabled={cargando || detalles.length === 0}
            className={`px-6 py-3 rounded font-bold text-white shadow transition
              ${cargando || detalles.length === 0
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-green-600 hover:bg-green-700'}`}
          >
            {cargando ? 'Procesando...' : '💾 Finalizar Venta'}
          </button>
        </div>

      </div>
    </div>
  )
}

export default Factura