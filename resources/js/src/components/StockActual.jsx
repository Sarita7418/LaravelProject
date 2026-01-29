import React, { useEffect, useState } from 'react';
import axios from "../lib/axios";
import './StockActual.css'; // Importamos el archivo de estilos

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
            const response = await axios.get('/api/stock-actual');
            setInventario(response.data);
            setCargando(false);
        } catch (error) {
            console.error("Error cargando inventario:", error);
            setCargando(false);
        }
    };

    const datosProcesados = inventario
        .filter(item => 
            item.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
            (item.codigo_interno && item.codigo_interno.toLowerCase().includes(busqueda.toLowerCase()))
        )
        .sort((a, b) => {
            if (orden === 'nombre') {
                return a.nombre.localeCompare(b.nombre);
            } else if (orden === 'bajo_stock') {
                const difA = (a.cantidad || 0) - (a.stock_minimo || 0);
                const difB = (b.cantidad || 0) - (b.stock_minimo || 0);
                return difA - difB;
            }
            return 0;
        });

    if (cargando) return <div className="loading-container">Cargando inventario...</div>;

    return (
        <div className="stock-container">
            <div className="stock-header">
                <h2 className="stock-title">Inventario Actual</h2>
                <span className="stock-subtitle">
                    Total productos: {datosProcesados.length}
                </span>
            </div>

            {/* BARRA DE HERRAMIENTAS */}
            <div className="stock-controls">
                <div className="control-group search-group">
                    <label>Buscar</label>
                    <input
                        type="text"
                        placeholder="Nombre o código..."
                        className="stock-input"
                        value={busqueda}
                        onChange={(e) => setBusqueda(e.target.value)}
                    />
                </div>
                
                <div className="control-group sort-group">
                    <label>Ordenar vista</label>
                    <select 
                        className="stock-select"
                        value={orden}
                        onChange={(e) => setOrden(e.target.value)}
                    >
                        <option value="nombre">Alfabético (A-Z)</option>
                        <option value="bajo_stock">Prioridad: Stock Bajo</option>
                    </select>
                </div>
            </div>

            {/* TABLA DE DATOS */}
            <div className="table-responsive">
                <table className="stock-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th className="text-center">Código</th>
                            <th className="text-center">Mínimo</th>
                            <th className="text-center">Existencia</th>
                            <th className="text-right">Precio</th>
                            <th className="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        {datosProcesados.map((item) => {
                            const cantidad = item.cantidad || 0;
                            const minimo = item.stock_minimo || 0;
                            const precio = parseFloat(item.precio_salida || 0).toFixed(2);
                            const esCritico = cantidad <= minimo;

                            return (
                                <tr key={item.id} className={esCritico ? 'row-critical' : 'row-normal'}>
                                    <td className="product-name">
                                        {item.nombre}
                                    </td>
                                    <td className="text-center text-muted">
                                        {item.codigo_interno || '-'}
                                    </td>
                                    <td className="text-center text-muted">
                                        {minimo}
                                    </td>
                                    <td className={`text-center font-bold ${esCritico ? 'qty-low' : 'qty-ok'}`}>
                                        {cantidad}
                                    </td>
                                    <td className="text-right product-price">
                                        {precio} Bs.
                                    </td>
                                    <td className="text-center">
                                        <span className={`status-badge ${esCritico ? 'badge-danger' : 'badge-success'}`}>
                                            {esCritico ? 'Reponer' : 'Normal'}
                                        </span>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>

                {datosProcesados.length === 0 && (
                    <div className="empty-state">
                        No se encontraron coincidencias.
                    </div>
                )}
            </div>
        </div>
    );
};

export default StockActual;