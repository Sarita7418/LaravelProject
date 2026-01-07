import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from '../../lib/axios';
import './Compras.css';

const Compras = () => {
    const [compras, setCompras] = useState([]);
    const [loading, setLoading] = useState(true);
    const [estados, setEstados] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        total: 0,
        per_page: 20
    });
    const [filtros, setFiltros] = useState({ 
        estado: '', 
        search: '', 
        fecha_inicio: '', 
        fecha_fin: '' 
    });
    const navigate = useNavigate();

    useEffect(() => {
        cargarEstados();
        cargarCompras();
    }, [filtros, pagination.current_page]);

    const cargarEstados = async () => {
        if (estados.length > 0) return;
        
        try {
            const response = await axios.get('/api/compras/estados');
            if (response.data?.data) {
                setEstados(Array.isArray(response.data.data) ? response.data.data : []);
            }
        } catch (error) {
            console.error('Error cargando estados:', error);
            setEstados([]);
        }
    };

    const cargarCompras = async () => {
        try {
            setLoading(true);
            const params = {
                ...filtros,
                page: pagination.current_page
            };
            
            const response = await axios.get('/api/compras', { params });
            
            if (response.data && response.data.data) {
                setCompras(Array.isArray(response.data.data.data) ? response.data.data.data : response.data.data || []);
                setPagination({
                    current_page: response.data.data.current_page || 1,
                    total: response.data.data.total || 0,
                    per_page: response.data.data.per_page || 20
                });
            } else {
                setCompras([]);
            }
        } catch (error) {
            console.error('Error:', error);
            setCompras([]);
        } finally {
            setLoading(false);
        }
    };


    const handlePageChange = (page) => {
        setPagination({ ...pagination, current_page: page });
    };

    const getEstadoColor = (idEstado) => {
        const estado = estados.find(e => e.id == idEstado);
        if (!estado) return '#10b981';
        
        switch(estado.descripcion) {
            case 'BORRADOR': return '#f59e0b';
            case 'CONFIRMADO': return '#10b981';
            case 'ANULADO': return '#ef4444';
            default: return '#6b7280';
        }
    };

    const getEstadoTexto = (idEstado) => {
        const estado = estados.find(e => e.id == idEstado);
        return estado ? estado.descripcion : 'Confirmado';
    };

    const formatFecha = (fecha) => {
        try {
            return new Date(fecha).toLocaleDateString('es-ES');
        } catch (error) {
            return 'Fecha inválida';
        }
    };

    const formatMoneda = (monto) => {
        try {
            return new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(monto || 0);
        } catch (error) {
            return '0.00';
        }
    };

    const totalPages = Math.ceil(pagination.total / pagination.per_page);

    return (
        <div className="compras-container">
            <div className="compras-header">
                <h1 className="compras-title">Registro de Compras</h1>
                <Link to="/dashboard/compras/crear" className="btn-primary">
                    Nueva Compra
                </Link>
            </div>

            <div className="filtros-container">
                <div className="filtros-grid">
                    <div className="filtro-group">
                        <label>Estado</label>
                        <select 
                            className="form-select" 
                            value={filtros.estado} 
                            onChange={e => setFiltros({...filtros, estado: e.target.value})}
                        >
                            <option value="">Todos los estados</option>
                            {Array.isArray(estados) && estados.map(e => (
                                <option key={e.id} value={e.id}>
                                    {e.descripcion || `Estado ${e.id}`}
                                </option>
                            ))}
                        </select>
                    </div>
                    
                    <div className="filtro-group">
                        <label>Buscar</label>
                        <input 
                            type="text" 
                            className="form-input" 
                            placeholder="Proveedor, N° Doc, CI/NIT..." 
                            value={filtros.search}
                            onChange={e => setFiltros({...filtros, search: e.target.value})}
                        />
                    </div>
                    
                    <div className="filtro-group">
                        <label>Fecha Inicio</label>
                        <input 
                            type="date" 
                            className="form-input" 
                            value={filtros.fecha_inicio}
                            onChange={e => setFiltros({...filtros, fecha_inicio: e.target.value})}
                        />
                    </div>
                    
                    <div className="filtro-group">
                        <label>Fecha Fin</label>
                        <input 
                            type="date" 
                            className="form-input" 
                            value={filtros.fecha_fin}
                            onChange={e => setFiltros({...filtros, fecha_fin: e.target.value})}
                        />
                    </div>
                </div>
                
                <div className="filtros-acciones">
                    <button 
                        className="btn-secundario" 
                        onClick={() => setFiltros({ estado: '', search: '', fecha_inicio: '', fecha_fin: '' })}
                    >
                        Limpiar Filtros
                    </button>
                </div>
            </div>

            <div className="compras-lista">
                {loading ? (
                    <div className="loading">Cargando compras...</div>
                ) : (
                    <>
                        <table className="compras-table">
                            <thead>
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {Array.isArray(compras) && compras.length > 0 ? (
                                    compras.map(compra => (
                                        <tr key={compra.id}>
                                            <td>{compra.nro_factura || `CMP-${compra.id}`}</td>
                                            <td>{formatFecha(compra.fecha_compra)}</td>
                                            <td>
                                                {compra.proveedor?.razon_social || 'Sin proveedor'}
                                            </td>
                                            <td>Bs. {formatMoneda(compra.total_compra)}</td>
                                            <td>
                                                <span 
                                                    className="estado-badge" 
                                                    style={{ backgroundColor: getEstadoColor(compra.id_estado_compra) }}
                                                >
                                                    {getEstadoTexto(compra.id_estado_compra)}
                                                </span>
                                            </td>
                                            <td>
                                                <div className="acciones">
                                                    <button 
                                                        onClick={() => navigate(`/dashboard/compras/${compra.id}`)} 
                                                        className="btn-ver"
                                                    >
                                                        Detalle
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="text-center">
                                            No se encontraron compras
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                        
                        {totalPages > 1 && (
                            <div className="pagination">
                                <button 
                                    className="btn-secundario"
                                    onClick={() => handlePageChange(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1}
                                >
                                    Anterior
                                </button>
                                
                                <span className="pagination-info">
                                    Página {pagination.current_page} de {totalPages}
                                </span>
                                
                                <button 
                                    className="btn-secundario"
                                    onClick={() => handlePageChange(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= totalPages}
                                >
                                    Siguiente
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
};

export default Compras;