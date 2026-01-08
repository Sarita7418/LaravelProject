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
        stock_minimo: 0,
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
            // Cargar categorías (dominio 5 según tus datos)
            const responseCat = await axios.get('/api/productos/categorias');
            const responseUni = await axios.get('/api/productos/unidades');
            
            let datosCategorias = [];
            if (responseCat.data && responseCat.data.success === true && Array.isArray(responseCat.data.data)) {
                datosCategorias = responseCat.data.data;
                console.log('Categorías cargadas:', datosCategorias); // Para depuración
            }
            
            let datosUnidades = [];
            if (responseUni.data && responseUni.data.success === true && Array.isArray(responseUni.data.data)) {
                datosUnidades = responseUni.data.data;
                console.log('Unidades cargadas:', datosUnidades); // Para depuración
            }
            
            // Verificar y formatear los datos correctamente
            const categoriasFormateadas = datosCategorias.map(cat => ({
                id: cat.id,
                value: cat.id,
                label: cat.descripcion || cat.label || `Categoría ${cat.id}`,
                descripcion: cat.descripcion || cat.label || `Categoría ${cat.id}`
            }));
            
            const unidadesFormateadas = datosUnidades.map(uni => ({
                id: uni.id,
                value: uni.id,
                label: uni.descripcion || uni.label || `Unidad ${uni.id}`,
                descripcion: uni.descripcion || uni.label || `Unidad ${uni.id}`
            }));
            
            console.log('Categorías formateadas:', categoriasFormateadas); // Para depuración
            console.log('Unidades formateadas:', unidadesFormateadas); // Para depuración
            
            setCategorias(categoriasFormateadas);
            setUnidades(unidadesFormateadas);
            
            // Establecer valores por defecto si no hay selección actual
            if (categoriasFormateadas.length > 0 && !formData.id_categoria) {
                setFormData(prev => ({
                    ...prev,
                    id_categoria: categoriasFormateadas[0].id
                }));
            }
            
            if (unidadesFormateadas.length > 0 && !formData.id_unidad_medida) {
                setFormData(prev => ({
                    ...prev,
                    id_unidad_medida: unidadesFormateadas[0].id
                }));
            }
            
        } catch (error) {
            console.error('Error cargando catálogos:', error);
            let mensajeError = 'Error al cargar los catálogos';
            
            if (error.response) {
                if (error.response.status === 404) {
                    mensajeError = 'Error 404: Rutas no encontradas';
                } else if (error.response.status === 500) {
                    mensajeError = 'Error 500: Problema en el servidor';
                } else {
                    mensajeError = `Error ${error.response.status}: ${error.response.data?.message || 'Error desconocido'}`;
                }
                
                console.error('Detalles del error:', error.response.data);
            } else if (error.request) {
                mensajeError = 'Error de red: No se pudo conectar al servidor';
            } else {
                mensajeError = `Error: ${error.message}`;
            }
            
            setErrorCatalogos(mensajeError);
            setCategorias([]);
            setUnidades([]);
            
            // Datos de prueba en caso de error (para desarrollo)
            if (process.env.NODE_ENV === 'development') {
                console.log('Usando datos de prueba para desarrollo');
                setCategorias([
                    { id: 13, value: 13, label: 'BIEN', descripcion: 'BIEN' },
                    { id: 14, value: 14, label: 'SERVICIO', descripcion: 'SERVICIO' }
                ]);
                setUnidades([
                    { id: 15, value: 15, label: 'UNIDAD', descripcion: 'UNIDAD' },
                    { id: 16, value: 16, label: 'CAJA', descripcion: 'CAJA' },
                    { id: 17, value: 17, label: 'KILOGRAMO', descripcion: 'KILOGRAMO' },
                    { id: 18, value: 18, label: 'LITRO', descripcion: 'LITRO' },
                    { id: 19, value: 19, label: 'METRO', descripcion: 'METRO' },
                    { id: 20, value: 20, label: 'DOCENA', descripcion: 'DOCENA' },
                    { id: 21, value: 21, label: 'PAQUETE', descripcion: 'PAQUETE' }
                ]);
            }
            
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
            stock_minimo: 0,
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
            };

            console.log('Enviando payload:', payload); // Para depuración
            
            const response = await axios.post('/api/productos', payload);
            
            onProductoCreado(response.data);
            onClose();
            resetForm();
            
        } catch (error) {
            console.error('Error creando producto:', error);
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
                                                {c.label}
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
                                                {u.label}
                                            </option>
                                        ))}
                                    </>
                                )}
                            </select>
                        </div>
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