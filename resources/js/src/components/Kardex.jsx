import React, { useState, useEffect } from 'react';
import axios from 'axios';
import Select from 'react-select'; 
import "./Ventas.css"; // Reusamos tus estilos para que se vea igual al resto del sistema

const Kardex = () => {
    // Estados
    const [productos, setProductos] = useState([]);
    const [productoSeleccionado, setProductoSeleccionado] = useState(null);
    const [datosKardex, setDatosKardex] = useState([]);
    const [infoProducto, setInfoProducto] = useState(null);
    const [loading, setLoading] = useState(false);

    // 1. Cargar lista de productos al iniciar para el buscador
    useEffect(() => {
        cargarListaProductos();
    }, []);

    const cargarListaProductos = async () => {
        try {
            const res = await axios.get('/api/productos');
            // Transformamos los datos para que le gusten a React-Select ({value, label})
            // Asumimos que tu API devuelve { data: [...] } o un array directo. Ajusta si es necesario.
            const listaRaw = res.data.data || res.data; 
            const opciones = listaRaw.map(p => ({
                value: p.id,
                label: `${p.codigo_interno} - ${p.nombre}`
            }));
            setProductos(opciones);
        } catch (error) {
            console.error("Error cargando productos:", error);
        }
    };

    // 2. Cuando el usuario elige un producto, vamos al Backend a pedir la "Historia"
    const handleProductoChange = async (selectedOption) => {
        setProductoSeleccionado(selectedOption);
        setDatosKardex([]); // Limpiamos la tabla anterior
        setInfoProducto(null);

        if (!selectedOption) return;

        setLoading(true);
        try {
            // Llamamos a la ruta que creamos en api.php
            const res = await axios.get(`/api/kardex/${selectedOption.value}`);
            setDatosKardex(res.data.kardex);
            setInfoProducto(res.data.producto);
        } catch (error) {
            console.error("Error cargando kardex:", error);
            alert("Error al cargar el Kardex. Revisa la consola.");
        } finally {
            setLoading(false);
        }
    };

    // Estilos para el select (Modo Oscuro/Moderno)
    const customSelectStyles = {
        control: (provided) => ({ ...provided, minHeight: '45px', borderRadius: '8px' }),
        menu: (provided) => ({ ...provided, zIndex: 9999 })
    };

    return (
        <div className="ventas-container">
            <h2 className="ventas-title">Kardex Físico Valorado</h2>
            

            {/* --- FILTRO DE BÚSQUEDA --- */}
            <div className="card" style={{ padding: '20px', marginBottom: '25px' }}>
                <label style={{ fontWeight: 'bold', marginBottom: '8px', display: 'block' }}>
                    🔍 Buscar Producto:
                </label>
                <Select
                    options={productos}
                    value={productoSeleccionado}
                    onChange={handleProductoChange}
                    placeholder="Escribe el nombre o código del producto..."
                    styles={customSelectStyles}
                    isClearable
                />
            </div>

            {/* --- RESULTADOS --- */}
            {productoSeleccionado && (
                <div className="card">
                    {/* Cabecera del Reporte */}
                    {infoProducto && (
                        <div style={{ 
                            marginBottom: '20px', 
                            padding: '15px', 
                            background: '#f8fafc', 
                            borderLeft: '5px solid #2563eb',
                            borderRadius: '4px'
                        }}>
                            <h3 style={{ margin: '0 0 5px 0', color: '#1e293b' }}>{infoProducto.nombre}</h3>
                            <div style={{ display: 'flex', gap: '20px', fontSize: '14px', color: '#475569' }}>
                                <span><strong>Código:</strong> {infoProducto.codigo_interno}</span>
                                <span><strong>Unidad:</strong> {infoProducto.unidad_medida?.descripcion || 'Unidad'}</span>
                                <span><strong>Categoría:</strong> {infoProducto.categoria?.descripcion || 'General'}</span>
                            </div>
                        </div>
                    )}

                    {loading ? (
                        <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                            ⏳ Calculando costos históricos...
                        </div>
                    ) : datosKardex.length === 0 ? (
                        <div style={{ textAlign: 'center', padding: '40px', color: '#999' }}>
                            📂 Este producto no tiene movimientos registrados aún.
                        </div>
                    ) : (
                        <div style={{ overflowX: 'auto' }}>
                            <table className="tabla-carrito" style={{ fontSize: '12px', border: '1px solid #e5e7eb' }}>
                                <thead>
                                    {/* NIVEL 1: AGRUPADORES */}
                                    <tr style={{ background: '#1f2937', color: 'white', textTransform: 'uppercase', fontSize: '11px' }}>
                                        <th rowSpan="2" style={{ verticalAlign: 'middle', width: '90px', padding: '8px' }}>Fecha</th>
                                        <th rowSpan="2" style={{ verticalAlign: 'middle', minWidth: '180px', padding: '8px' }}>Detalle / Concepto</th>
                                        
                                        <th colSpan="3" style={{ textAlign: 'center', background: '#15803d', borderLeft: '1px solid #fff3' }}>
                                            ENTRADAS (Compras)
                                        </th>
                                        <th colSpan="3" style={{ textAlign: 'center', background: '#b91c1c', borderLeft: '1px solid #fff3' }}>
                                            SALIDAS (Ventas)
                                        </th>
                                        <th colSpan="3" style={{ textAlign: 'center', background: '#1d4ed8', borderLeft: '1px solid #fff3' }}>
                                            SALDOS (Existencias)
                                        </th>
                                    </tr>

                                    {/* NIVEL 2: COLUMNAS ESPECÍFICAS */}
                                    <tr style={{ background: '#374151', color: 'white', fontSize: '11px' }}>
                                        {/* Entradas */}
                                        <th style={{width: '60px', textAlign: 'center'}}>Cant.</th>
                                        <th style={{width: '70px', textAlign: 'center'}}>C. Unit</th>
                                        <th style={{width: '80px', textAlign: 'center'}}>Total</th>
                                        
                                        {/* Salidas */}
                                        <th style={{width: '60px', textAlign: 'center'}}>Cant.</th>
                                        <th style={{width: '70px', textAlign: 'center'}}>C. Unit</th>
                                        <th style={{width: '80px', textAlign: 'center'}}>Total</th>
                                        
                                        {/* Saldos */}
                                        <th style={{width: '60px', textAlign: 'center'}}>Cant.</th>
                                        <th style={{width: '70px', textAlign: 'center'}}>C.P.P.</th>
                                        <th style={{width: '80px', textAlign: 'center'}}>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {datosKardex.map((fila) => (
                                        <tr key={fila.id} style={{ borderBottom: '1px solid #f3f4f6' }}>
                                            <td style={{ padding: '8px' }}>{new Date(fila.fecha).toLocaleDateString()}</td>
                                            <td style={{ padding: '8px' }}>{fila.detalle}</td>

                                            {/* ENTRADAS (Verde suave si hay dato) */}
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.entrada_q > 0 ? '#dcfce7' : 'transparent', fontWeight: 'bold', color: '#166534' }}>
                                                {fila.entrada_q > 0 ? fila.entrada_q : '-'}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.entrada_q > 0 ? '#dcfce7' : 'transparent' }}>
                                                {fila.entrada_q > 0 ? parseFloat(fila.entrada_u).toFixed(2) : '-'}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.entrada_q > 0 ? '#dcfce7' : 'transparent' }}>
                                                {fila.entrada_q > 0 ? parseFloat(fila.entrada_t).toFixed(2) : '-'}
                                            </td>

                                            {/* SALIDAS (Rojo suave si hay dato) */}
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.salida_q > 0 ? '#fee2e2' : 'transparent', fontWeight: 'bold', color: '#991b1b' }}>
                                                {fila.salida_q > 0 ? fila.salida_q : '-'}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.salida_q > 0 ? '#fee2e2' : 'transparent' }}>
                                                {fila.salida_q > 0 ? parseFloat(fila.salida_u).toFixed(2) : '-'}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: fila.salida_q > 0 ? '#fee2e2' : 'transparent' }}>
                                                {fila.salida_q > 0 ? parseFloat(fila.salida_t).toFixed(2) : '-'}
                                            </td>

                                            {/* SALDOS (Azul muy suave siempre) */}
                                            <td style={{ textAlign: 'right', padding: '8px', background: '#eff6ff', fontWeight: 'bold' }}>
                                                {fila.saldo_q}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: '#eff6ff', color: '#1e40af' }}>
                                                {parseFloat(fila.saldo_u).toFixed(2)}
                                            </td>
                                            <td style={{ textAlign: 'right', padding: '8px', background: '#eff6ff', fontWeight: 'bold' }}>
                                                {parseFloat(fila.saldo_t).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default Kardex;