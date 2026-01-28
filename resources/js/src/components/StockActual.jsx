import React, { useEffect, useState } from 'react';
// IMPORTANTE: Verifica que esta ruta apunte a tu archivo de configuración de Axios
import api from '../api'; 

const StockActual = () => {
    const [inventario, setInventario] = useState([]);
    const [busqueda, setBusqueda] = useState('');
    const [orden, setOrden] = useState('nombre'); 
    const [cargando, setCargando] = useState(true);

    useEffect(() => {
        fetchStock();
    }, []);

    const fetchStock = async () => {
        try {
            // Petición al endpoint que definimos en Laravel
            const response = await api.get('/api/stock-actual');
            setInventario(response.data);
            setCargando(false);
        } catch (error) {
            console.error("Error cargando inventario:", error);
            setCargando(false);
        }
    };

    // Lógica de Filtrado y Orden
    const datosProcesados = inventario
        .filter(item => 
            // Filtro insensible a mayúsculas/minúsculas
            item.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
            (item.codigo_interno && item.codigo_interno.toLowerCase().includes(busqueda.toLowerCase()))
        )
        .sort((a, b) => {
            if (orden === 'nombre') {
                return a.nombre.localeCompare(b.nombre);
            } else if (orden === 'bajo_stock') {
                // Cálculo: Cuánto me falta o sobra respecto al mínimo
                const difA = (a.cantidad || 0) - (a.stock_minimo || 0);
                const difB = (b.cantidad || 0) - (b.stock_minimo || 0);
                return difA - difB; // Menor diferencia (más crítico) va primero
            }
            return 0;
        });

    if (cargando) return <div className="p-8 text-center text-gray-500">Cargando datos del sistema...</div>;

    return (
        <div className="bg-white rounded-lg shadow-sm p-6 m-4 border border-gray-100">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Inventario Actual</h2>
                <div className="text-sm text-gray-500">
                    Total productos listados: {datosProcesados.length}
                </div>
            </div>

            {/* BARRA DE HERRAMIENTAS */}
            <div className="flex flex-col md:flex-row gap-4 mb-6 justify-between bg-gray-50 p-4 rounded-lg">
                <div className="w-full md:w-1/2">
                    <label className="text-xs font-bold text-gray-500 uppercase mb-1 block">Buscar</label>
                    <input
                        type="text"
                        placeholder="Nombre del medicamento o código..."
                        className="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        value={busqueda}
                        onChange={(e) => setBusqueda(e.target.value)}
                    />
                </div>
                
                <div className="w-full md:w-auto">
                    <label className="text-xs font-bold text-gray-500 uppercase mb-1 block">Ordenar vista</label>
                    <select 
                        className="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 bg-white"
                        value={orden}
                        onChange={(e) => setOrden(e.target.value)}
                    >
                        <option value="nombre">Alfabético (A-Z)</option>
                        <option value="bajo_stock">Prioridad: Stock Bajo</option>
                    </select>
                </div>
            </div>

            {/* TABLA DE DATOS */}
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm text-left">
                    <thead className="bg-gray-100 text-gray-600 font-medium uppercase">
                        <tr>
                            <th className="py-3 px-4 rounded-tl-lg">Producto</th>
                            <th className="py-3 px-4 text-center">Código</th>
                            <th className="py-3 px-4 text-center">Stock Mínimo</th>
                            <th className="py-3 px-4 text-center">Existencia Real</th>
                            <th className="py-3 px-4 text-right">Precio Venta</th>
                            <th className="py-3 px-4 text-center rounded-tr-lg">Estado</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {datosProcesados.map((item) => {
                            // Validamos que sean números para evitar errores
                            const cantidad = item.cantidad || 0;
                            const minimo = item.stock_minimo || 0;
                            const precio = parseFloat(item.precio_salida || 0).toFixed(2);
                            const esCritico = cantidad <= minimo;

                            return (
                                <tr key={item.id} className="hover:bg-blue-50 transition-colors duration-150">
                                    <td className="py-3 px-4 font-medium text-gray-800">
                                        {item.nombre}
                                    </td>
                                    <td className="py-3 px-4 text-center text-gray-500">
                                        {item.codigo_interno || '-'}
                                    </td>
                                    <td className="py-3 px-4 text-center text-gray-500">
                                        {minimo}
                                    </td>
                                    <td className={`py-3 px-4 text-center font-bold text-lg ${esCritico ? 'text-red-500' : 'text-green-600'}`}>
                                        {cantidad}
                                    </td>
                                    <td className="py-3 px-4 text-right font-medium text-gray-700">
                                        {precio} Bs.
                                    </td>
                                    <td className="py-3 px-4 text-center">
                                        {esCritico ? (
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                ⚠ Reponer
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✔ Normal
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>

                {datosProcesados.length === 0 && (
                    <div className="text-center py-10 bg-gray-50 rounded-b-lg text-gray-400">
                        No se encontraron coincidencias.
                    </div>
                )}
            </div>
        </div>
    );
};

export default StockActual;