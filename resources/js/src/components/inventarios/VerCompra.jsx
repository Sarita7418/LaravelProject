import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from '../../lib/axios';
import './VerCompra.css';

const VerCompra = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [compra, setCompra] = useState(null);
    const [loading, setLoading] = useState(true);
    const [mostrarAnular, setMostrarAnular] = useState(false);
    const [motivoAnulacion, setMotivoAnulacion] = useState('');

    useEffect(() => {
        cargarCompra();
    }, [id]);

    const cargarCompra = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/compras/${id}`);
            if (response.data?.success) {
                setCompra(response.data.data || null);
            } else {
                setCompra(null);
            }
        } catch (error) {
            console.error('Error cargando compra:', error);
            setCompra(null);
        } finally {
            setLoading(false);
        }
    };

    const anularCompra = async () => {
        if (!motivoAnulacion.trim()) {
            alert('Por favor ingrese un motivo para la anulación');
            return;
        }

        if (window.confirm('¿Está seguro de anular esta compra? Esta acción no se puede deshacer.')) {
            try {
                const response = await axios.post(`/api/compras/${id}/anular`, { motivo: motivoAnulacion });
                if (response.data?.success) {
                    alert(response.data.message || 'Compra anulada exitosamente');
                    setMostrarAnular(false);
                    cargarCompra();
                } else {
                    alert(response.data?.message || 'Error al anular compra');
                }
            } catch (error) {
                alert('Error al anular compra: ' + (error.response?.data?.message || error.message));
            }
        }
    };

    const eliminarCompra = async () => {
        if (window.confirm('¿Está seguro de eliminar esta compra? Esta acción no se puede deshacer.')) {
            try {
                const response = await axios.delete(`/api/compras/${id}`);
                if (response.data?.success) {
                    alert(response.data.message || 'Compra eliminada exitosamente');
                    navigate('/dashboard/compras');
                } else {
                    alert(response.data?.message || 'Error al eliminar compra');
                }
            } catch (error) {
                alert('Error al eliminar compra: ' + (error.response?.data?.message || error.message));
            }
        }
    };

    const formatearFecha = (fecha) => {
        try {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (error) {
            return 'Fecha inválida';
        }
    };

    const formatearMoneda = (monto) => {
        try {
            return new Intl.NumberFormat('es-BO', {
                style: 'currency',
                currency: 'BOB'
            }).format(monto || 0);
        } catch (error) {
            return 'Bs. 0.00';
        }
    };

    const getEstadoColor = (estado) => {
        if (!estado) return '#10b981';
        switch(estado) {
            case 'BORRADOR': return '#f59e0b';
            case 'CONFIRMADO': return '#10b981';
            case 'ANULADO': return '#ef4444';
            default: return '#6b7280';
        }
    };

    if (loading) {
        return <div className="loading">Cargando compra...</div>;
    }

    if (!compra) {
        return <div className="error">Compra no encontrada</div>;
    }

    return (
        <div className="ver-compra-container">
            <div className="compra-header">
                <div className="compra-info-header">
                    <h1 className="compra-title">Compra #{compra.nro_factura || compra.id}</h1>
                    <div className="compra-subtitle">
                        <span>Fecha: {formatearFecha(compra.fecha_compra)}</span>
                        <span className="estado-badge" style={{ 
                            backgroundColor: getEstadoColor(compra.estadoCompra?.descripcion) 
                        }}>
                            {compra.estadoCompra?.descripcion || 'Confirmado'}
                        </span>
                    </div>
                </div>

                <div className="compra-acciones">
                    {compra.id_estado_compra === 24 && (
                        <>
                            <button 
                                onClick={() => navigate(`/dashboard/compras/${id}/editar`)}
                                className="btn-editar"
                            >
                                Editar
                            </button>
                            <button 
                                onClick={eliminarCompra}
                                className="btn-eliminar"
                            >
                                Eliminar
                            </button>
                        </>
                    )}

                    {compra.id_estado_compra === 25 && (
                        <button 
                            onClick={() => setMostrarAnular(true)}
                            className="btn-anular"
                        >
                            Anular
                        </button>
                    )}

                    <button 
                        onClick={() => navigate('/dashboard/compras')}
                        className="btn-volver"
                    >
                        Volver
                    </button>
                </div>
            </div>

            <div className="compra-info-grid">
                <div className="info-card">
                    <h3>Proveedor</h3>
                    <p><strong>Razón Social:</strong> {compra.proveedor?.razon_social || 'No especificado'}</p>
                    <p><strong>NIT:</strong> {compra.proveedor?.nit || 'No especificado'}</p>
                    <p><strong>Teléfono:</strong> {compra.proveedor?.telefono || 'No especificado'}</p>
                    <p><strong>Dirección:</strong> {compra.proveedor?.direccion_fiscal || 'No especificado'}</p>
                </div>

                <div className="info-card">
                    <h3>Empresa</h3>
                    <p><strong>Empresa:</strong> {compra.proveedor?.razon_social || 'No especificado'}</p>
                    <p><strong>Sucursal:</strong> {compra.sucursal?.nombre || 'No especificado'}</p>
                    <p><strong>Usuario:</strong> {compra.usuario?.name || 'No especificado'}</p>
                </div>

                <div className="info-card">
                    <h3>Totales</h3>
                    <p><strong>Subtotal:</strong> {formatearMoneda(compra.subtotal)}</p>
                    <p><strong>Descuento:</strong> {formatearMoneda(compra.descuento_total)}</p>
                    <p className="total-final"><strong>Total:</strong> {formatearMoneda(compra.total_compra)}</p>
                </div>
            </div>

            {compra.observacion && (
                <div className="observaciones-card">
                    <h3>Observaciones</h3>
                    <p>{compra.observacion}</p>
                </div>
            )}

            <div className="detalles-section">
                <h3>Productos Comprados</h3>
                <table className="detalles-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {Array.isArray(compra.detalles) && compra.detalles.length > 0 ? (
                            compra.detalles.map((detalle, index) => (
                                <tr key={index}>
                                    <td>{detalle.producto?.nombre || 'Producto sin nombre'}</td>
                                    <td>{detalle.cantidad || 0}</td>
                                    <td>{formatearMoneda(detalle.precio_unitario)}</td>
                                    <td>{formatearMoneda(detalle.subtotal)}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="4" className="text-center">
                                    No hay detalles de productos
                                </td>
                            </tr>
                        )}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colSpan="3" className="text-right"><strong>Total:</strong></td>
                            <td><strong>{formatearMoneda(compra.total_compra)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {mostrarAnular && (
                <div className="modal-overlay">
                    <div className="modal-content">
                        <h3>Anular Compra</h3>
                        <p>¿Está seguro de anular esta compra? Esta acción no se puede deshacer.</p>
                        
                        <div className="form-group">
                            <label>Motivo de anulación *</label>
                            <textarea
                                value={motivoAnulacion}
                                onChange={(e) => setMotivoAnulacion(e.target.value)}
                                className="form-input"
                                rows="4"
                                placeholder="Describa el motivo de la anulación..."
                                required
                            />
                        </div>

                        <div className="modal-actions">
                            <button 
                                onClick={() => setMostrarAnular(false)}
                                className="btn-cancelar"
                            >
                                Cancelar
                            </button>
                            <button 
                                onClick={anularCompra}
                                className="btn-anular"
                            >
                                Confirmar Anulación
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default VerCompra;