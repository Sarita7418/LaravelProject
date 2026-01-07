import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import './CrearProducto.css';

const CrearProducto = ({ isOpen, onClose, onProductoCreado }) => {
    const [loading, setLoading] = useState(false);
    const [categorias, setCategorias] = useState([]);
    const [unidades, setUnidades] = useState([]);
    const [codigoGenerado, setCodigoGenerado] = useState('');
    const [cargandoCatalogos, setCargandoCatalogos] = useState(false);
    const [errorCatalogos, setErrorCatalogos] = useState('');
    
    const [formData, setFormData] = useState({
        nombre: '',
        id_categoria: '',
        id_unidad_medida: '',
        codigo_interno: '',
        codigo_barras: '',
        stock_minimo: 0,
        numero_lote: '',
        stock_inicial: 0,
        fecha_vencimiento: ''
    });

    useEffect(() => {
        if (isOpen) {
            cargarCatalogosDesdeBD();
            generarCodigo();
        }
    }, [isOpen]);

    const cargarCatalogosDesdeBD = async () => {
        setCargandoCatalogos(true);
        setErrorCatalogos('');
        
        try {
            console.log('Intentando cargar datos de la base de datos...');
            const responseCat = await axios.get('/api/productos/categorias');
            console.log('Respuesta categorías:', responseCat);
            const responseUni = await axios.get('/api/productos/unidades');
            console.log('Respuesta unidades:', responseUni);
            
            let datosCategorias = [];
            if (responseCat.data && responseCat.data.success === true && Array.isArray(responseCat.data.data)) {
                datosCategorias = responseCat.data.data;
                console.log('Categorías encontradas:', datosCategorias.length);
            } else {
                console.error('Formato inesperado en categorías:', responseCat.data);
                setErrorCatalogos('Formato de respuesta inesperado para categorías');
            }
            
            let datosUnidades = [];
            if (responseUni.data && responseUni.data.success === true && Array.isArray(responseUni.data.data)) {
                datosUnidades = responseUni.data.data;
                console.log('Unidades encontradas:', datosUnidades.length);
            } else {
                console.error('Formato inesperado en unidades:', responseUni.data);
                setErrorCatalogos(prev => prev + ' | Formato inesperado para unidades');
            }
            
            // Guardar en estado
            setCategorias(datosCategorias);
            setUnidades(datosUnidades);
            
            // Establecer valores por defecto si hay datos
            if (datosCategorias.length > 0 && !formData.id_categoria) {
                setFormData(prev => ({
                    ...prev,
                    id_categoria: datosCategorias[0].id
                }));
            }
            
            if (datosUnidades.length > 0 && !formData.id_unidad_medida) {
                setFormData(prev => ({
                    ...prev,
                    id_unidad_medida: datosUnidades[0].id
                }));
            }
            
        } catch (error) {
            console.error('ERROR cargando catálogos:', error);
            
            let mensajeError = 'Error al cargar los catálogos';
            
            if (error.response) {
                console.error('Detalles del error:', {
                    status: error.response.status,
                    data: error.response.data,
                    url: error.config?.url
                });
                
                if (error.response.status === 404) {
                    mensajeError = 'Error 404: Rutas no encontradas';
                } else if (error.response.status === 500) {
                    mensajeError = 'Error 500: Problema en el servidor';
                } else {
                    mensajeError = `Error ${error.response.status}: ${error.response.data?.message || 'Error desconocido'}`;
                }
            } else if (error.request) {
                mensajeError = 'Error de red: No se pudo conectar al servidor';
            } else {
                mensajeError = `Error: ${error.message}`;
            }
            
            setErrorCatalogos(mensajeError);
            setCategorias([]);
            setUnidades([]);
            
        } finally {
            setCargandoCatalogos(false);
        }
    };

    const generarCodigo = () => {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
        const nuevoCodigo = `PROD-${timestamp}${random}`;
        setCodigoGenerado(nuevoCodigo);
        setFormData(prev => ({ ...prev, codigo_interno: nuevoCodigo }));
    };

    const resetForm = () => {
        setFormData({
            nombre: '',
            id_categoria: categorias[0]?.id || '',
            id_unidad_medida: unidades[0]?.id || '',
            codigo_interno: codigoGenerado,
            codigo_barras: '',
            stock_minimo: 0,
            numero_lote: '',
            stock_inicial: 0,
            fecha_vencimiento: ''
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!formData.nombre.trim()) {
            alert("El nombre del producto es obligatorio");
            return;
        }
        
        if (!formData.id_categoria) {
            alert("Seleccione una categoría");
            return;
        }
        
        if (!formData.id_unidad_medida) {
            alert("Seleccione una unidad de medida");
            return;
        }
        
        setLoading(true);
        try {
            const payload = {
                ...formData,
                codigo_interno: formData.codigo_interno || codigoGenerado,
                precio_entrada: 0,
                precio_salida: 0,
                stock_minimo: formData.stock_minimo || 0,
                stock_inicial: formData.stock_inicial || 0
            };

            const response = await axios.post('/api/productos', payload);
            
            onProductoCreado(response.data);
            onClose();
            resetForm();
            
        } catch (error) {
            const errorMsg = error.response?.data?.message || 
                           error.response?.data?.error || 
                           "Error al guardar el producto";
            alert(errorMsg);
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="crear-producto-modal-overlay">
            <div className="crear-producto-modal-content">
                <div className="crear-producto-modal-header">
                    <h2 className="crear-producto-modal-title">Registrar Nuevo Producto</h2>
                    <button 
                        type="button"
                        className="crear-producto-close-btn"
                        onClick={onClose}
                    >
                        ×
                    </button>
                </div>
                
                {cargandoCatalogos && (
                    <div className="loading-catalogos">
                        Cargando catálogos desde la base de datos...
                    </div>
                )}
                
                {errorCatalogos && (
                    <div className="error-catalogos">
                        <div>Error cargando datos</div>
                        <div>{errorCatalogos}</div>
                        <button 
                            onClick={cargarCatalogosDesdeBD}
                            className="btn-reintentar"
                        >
                            Reintentar
                        </button>
                    </div>
                )}
                
                <form className="crear-producto-form" onSubmit={handleSubmit}>
                    <div className="crear-producto-form-group">
                        <label>Código del Producto</label>
                        <input 
                            type="text" 
                            className="crear-producto-form-input" 
                            value={formData.codigo_interno}
                            onChange={e => setFormData({...formData, codigo_interno: e.target.value})}
                            placeholder="Código generado automáticamente"
                        />
                    </div>

                    <div className="crear-producto-form-group">
                        <label>Nombre del Producto *</label>
                        <input 
                            type="text" 
                            className="crear-producto-form-input" 
                            required 
                            value={formData.nombre}
                            onChange={e => setFormData({...formData, nombre: e.target.value})}
                            placeholder="Ej: Arroz Extra, Leche Entera, etc."
                        />
                    </div>

                    <div className="crear-producto-form-grid">
                        <div className="crear-producto-form-group">
                            <label>Categoría *</label>
                            <select 
                                className="crear-producto-form-select" 
                                value={formData.id_categoria}
                                onChange={e => setFormData({...formData, id_categoria: e.target.value})}
                                required
                                disabled={cargandoCatalogos}
                            >
                                {cargandoCatalogos ? (
                                    <option value="">Cargando categorías...</option>
                                ) : categorias.length === 0 ? (
                                    <option value="">No hay categorías disponibles</option>
                                ) : (
                                    <>
                                        <option value="">Seleccione una categoría</option>
                                        {categorias.map(c => (
                                            <option key={c.id} value={c.id}>
                                                {c.descripcion || c.nombre || `Categoría ${c.id}`}
                                            </option>
                                        ))}
                                    </>
                                )}
                            </select>
                        </div>
                        
                        <div className="crear-producto-form-group">
                            <label>Unidad de Medida *</label>
                            <select 
                                className="crear-producto-form-select" 
                                value={formData.id_unidad_medida}
                                onChange={e => setFormData({...formData, id_unidad_medida: e.target.value})}
                                required
                                disabled={cargandoCatalogos}
                            >
                                {cargandoCatalogos ? (
                                    <option value="">Cargando unidades...</option>
                                ) : unidades.length === 0 ? (
                                    <option value="">No hay unidades disponibles</option>
                                ) : (
                                    <>
                                        <option value="">Seleccione una unidad</option>
                                        {unidades.map(u => (
                                            <option key={u.id} value={u.id}>
                                                {u.descripcion || u.nombre || `Unidad ${u.id}`}
                                            </option>
                                        ))}
                                    </>
                                )}
                            </select>
                        </div>
                    </div>

                    <div className="crear-producto-form-grid">
                        <div className="crear-producto-form-group">
                            <label>Código de Barras (Opcional)</label>
                            <input 
                                type="text" 
                                className="crear-producto-form-input" 
                                value={formData.codigo_barras}
                                onChange={e => setFormData({...formData, codigo_barras: e.target.value})}
                                placeholder="Ej: 123456789012"
                            />
                        </div>
                        
                        <div className="crear-producto-form-group">
                            <label>Stock Mínimo</label>
                            <input 
                                type="number" 
                                min="0"
                                className="crear-producto-form-input" 
                                value={formData.stock_minimo}
                                onChange={e => setFormData({...formData, stock_minimo: parseInt(e.target.value) || 0})}
                            />
                        </div>
                    </div>

                    <div className="crear-producto-lote-section">
                        <div className="crear-producto-lote-title">Información de Lote (Opcional)</div>
                        
                        <div className="crear-producto-form-grid">
                            <div className="crear-producto-form-group">
                                <label>Número de Lote</label>
                                <input 
                                    type="text" 
                                    className="crear-producto-form-input" 
                                    value={formData.numero_lote}
                                    onChange={e => setFormData({...formData, numero_lote: e.target.value})}
                                    placeholder="Ej: L-101, BATCH-2024"
                                />
                            </div>
                            
                            <div className="crear-producto-form-group">
                                <label>Fecha de Vencimiento</label>
                                <input 
                                    type="date" 
                                    className="crear-producto-form-input" 
                                    value={formData.fecha_vencimiento}
                                    onChange={e => setFormData({...formData, fecha_vencimiento: e.target.value})}
                                />
                            </div>
                        </div>
                        
                        <div className="crear-producto-form-group">
                            <label>Stock Inicial</label>
                            <input 
                                type="number" 
                                min="0"
                                step="0"
                                className="crear-producto-form-input" 
                                value={formData.stock_inicial}
                                onChange={e => setFormData({...formData, stock_inicial: parseFloat(e.target.value) })}
                            />
                        </div>
                    </div>

                    <div className="crear-producto-form-actions">
                        <button 
                            type="button" 
                            className="crear-producto-btn-cancelar" 
                            onClick={onClose}
                            disabled={loading}
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            className="crear-producto-btn-guardar" 
                            disabled={loading || cargandoCatalogos || categorias.length === 0 || unidades.length === 0}
                        >
                            {loading ? 'Guardando...' : 
                             cargandoCatalogos ? 'Cargando datos...' : 
                             'Crear Producto'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CrearProducto;