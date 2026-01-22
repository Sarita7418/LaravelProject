import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import './CrearProducto.css';

const CrearProducto = ({ isOpen, onClose, onProductoCreado }) => {
    const [loading, setLoading] = useState(false);
    const [categorias, setCategorias] = useState([]);
    const [unidades, setUnidades] = useState([]);
    const [unidadesVenta, setUnidadesVenta] = useState([]);
    const [codigoGenerado, setCodigoGenerado] = useState('');
    const [cargandoCatalogos, setCargandoCatalogos] = useState(false);
    const [errorCatalogos, setErrorCatalogos] = useState('');
    
    // Estados para LINAME - Búsqueda General
    const [busquedaGeneral, setBusquedaGeneral] = useState('');
    const [resultadosBusqueda, setResultadosBusqueda] = useState([]);
    const [buscandoGeneral, setBuscandoGeneral] = useState(false);
    
    // Estados para navegación por jerarquía
    const [grupos, setGrupos] = useState([]);
    const [subgrupos, setSubgrupos] = useState([]);
    const [medicamentosLiname, setMedicamentosLiname] = useState([]);
    const [grupoSeleccionado, setGrupoSeleccionado] = useState('');
    const [subgrupoSeleccionado, setSubgrupoSeleccionado] = useState('');
    
    // Medicamento seleccionado final
    const [medicamentoSeleccionado, setMedicamentoSeleccionado] = useState(null);
    const [mostrarNombresComerciales, setMostrarNombresComerciales] = useState(false);
    const [nombresComerciales, setNombresComerciales] = useState([]);
    const [nombreComercialSeleccionado, setNombreComercialSeleccionado] = useState('');
    
    const [formData, setFormData] = useState({
        nombre: '',
        id_categoria: '',
        id_unidad_medida: '',
        id_unidad_venta: '',
        codigo_interno: '',
        stock_minimo: 0,
        unidades_empaque: 1,
        id_medicamento_liname: null
    });

    useEffect(() => {
        if (isOpen) {
            cargarCatalogosDesdeBD();
            cargarGruposLiname();
            generarCodigo();
        }
    }, [isOpen]);

    useEffect(() => {
        if (grupoSeleccionado) {
            cargarSubgrupos(grupoSeleccionado);
            // NUEVO: Cargar medicamentos del grupo automáticamente
            cargarMedicamentosDelGrupo(grupoSeleccionado);
        } else {
            setSubgrupos([]);
            setSubgrupoSeleccionado('');
            setMedicamentosLiname([]);
        }
    }, [grupoSeleccionado]);

    useEffect(() => {
        if (subgrupoSeleccionado) {
            // Si hay subgrupo seleccionado, cargar solo los de ese subgrupo
            cargarMedicamentosLiname(subgrupoSeleccionado);
        } else if (grupoSeleccionado) {
            // Si NO hay subgrupo pero SÍ hay grupo, cargar todos los del grupo
            cargarMedicamentosDelGrupo(grupoSeleccionado);
        } else {
            setMedicamentosLiname([]);
        }
    }, [subgrupoSeleccionado]);

    // Búsqueda general con debounce
    useEffect(() => {
        const timer = setTimeout(() => {
            if (busquedaGeneral && busquedaGeneral.length >= 3) {
                buscarMedicamentoGeneral();
            } else {
                setResultadosBusqueda([]);
            }
        }, 500);

        return () => clearTimeout(timer);
    }, [busquedaGeneral]);

    const buscarMedicamentoGeneral = async () => {
        try {
            setBuscandoGeneral(true);
            const response = await axios.get('/api/medicamentos-liname/buscar-completo', {
                params: { search: busquedaGeneral }
            });
            
            if (response.data?.success) {
                setResultadosBusqueda(response.data.data || []);
            }
        } catch (error) {
            console.error('Error en búsqueda general:', error);
        } finally {
            setBuscandoGeneral(false);
        }
    };

    const cargarGruposLiname = async () => {
        try {
            const response = await axios.get('/api/medicamentos-liname/grupos');
            if (response.data?.success) {
                setGrupos(response.data.data || []);
            }
        } catch (error) {
            console.error('Error cargando grupos LINAME:', error);
        }
    };

    const cargarSubgrupos = async (grupoId) => {
        try {
            const response = await axios.get(`/api/medicamentos-liname/subgrupos/${grupoId}`);
            if (response.data?.success) {
                setSubgrupos(response.data.data || []);
            }
        } catch (error) {
            console.error('Error cargando subgrupos:', error);
        }
    };

    const cargarMedicamentosDelGrupo = async (grupoId) => {
        try {
            const response = await axios.get(`/api/medicamentos-liname/por-grupo/${grupoId}`);
            if (response.data?.success) {
                setMedicamentosLiname(response.data.data || []);
            }
        } catch (error) {
            console.error('Error cargando medicamentos del grupo:', error);
        }
    };

    const cargarMedicamentosLiname = async (subgrupoId) => {
        try {
            const response = await axios.get(`/api/medicamentos-liname/por-clasificacion/${subgrupoId}`);
            if (response.data?.success) {
                setMedicamentosLiname(response.data.data || []);
            }
        } catch (error) {
            console.error('Error cargando medicamentos:', error);
        }
    };

    const seleccionarMedicamentoDeBusqueda = (medicamento) => {
        // Auto-completar campos de clasificación
        if (medicamento.grupo_id) {
            setGrupoSeleccionado(medicamento.grupo_id);
        }
        if (medicamento.clasificacion_id) {
            setSubgrupoSeleccionado(medicamento.clasificacion_id);
        }
        
        seleccionarMedicamento(medicamento);
        setResultadosBusqueda([]);
        setBusquedaGeneral('');
    };

    const seleccionarMedicamento = async (medicamento) => {
        setMedicamentoSeleccionado(medicamento);
        
        // Cargar nombres comerciales si existen
        try {
            const response = await axios.get(`/api/medicamentos-liname/${medicamento.id}/comerciales`);
            if (response.data?.success && response.data.data?.length > 0) {
                setNombresComerciales(response.data.data);
                setMostrarNombresComerciales(true);
            } else {
                setNombresComerciales([]);
                setMostrarNombresComerciales(false);
                // Si no hay nombre comercial, usar el genérico
                construirNombreProducto(medicamento, null);
            }
        } catch (error) {
            console.error('Error cargando nombres comerciales:', error);
            setNombresComerciales([]);
            setMostrarNombresComerciales(false);
            construirNombreProducto(medicamento, null);
        }
    };

    const seleccionarNombreComercial = (nombreComercial) => {
        setNombreComercialSeleccionado(nombreComercial);
        construirNombreProducto(medicamentoSeleccionado, nombreComercial);
        setMostrarNombresComerciales(false);
    };

    const construirNombreProducto = (medicamento, nombreComercial) => {
        let nombreFinal = '';
        
        if (nombreComercial) {
            // Usar nombre comercial si está disponible
            nombreFinal = `${nombreComercial} - ${medicamento.generico_nombre} ${medicamento.forma_farmaceutica} ${medicamento.concentracion}`;
        } else {
            // Usar solo datos del LINAME
            nombreFinal = `${medicamento.generico_nombre} - ${medicamento.forma_farmaceutica} ${medicamento.concentracion}`;
        }
        
        setFormData(prev => ({
            ...prev,
            nombre: nombreFinal,
            id_medicamento_liname: medicamento.id
        }));
    };

    const cargarCatalogosDesdeBD = async () => {
        setCargandoCatalogos(true);
        setErrorCatalogos('');
        
        try {
            const [responseCat, responseUni, responseUniVenta] = await Promise.all([
                axios.get('/api/productos/categorias'),
                axios.get('/api/productos/unidades'),
                axios.get('/api/productos/unidades-venta')
            ]);
            
            if (responseCat.data?.success) {
                setCategorias(responseCat.data.data || []);
            }
            
            if (responseUni.data?.success) {
                setUnidades(responseUni.data.data || []);
            }

            if (responseUniVenta.data?.success) {
                setUnidadesVenta(responseUniVenta.data.data || []);
            }
            
        } catch (error) {
            console.error('Error cargando catálogos:', error);
            setErrorCatalogos('Error al cargar los catálogos');
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
            id_unidad_venta: unidadesVenta[0]?.id || '',
            codigo_interno: codigoGenerado,
            stock_minimo: 0,
            unidades_empaque: 1,
            id_medicamento_liname: null
        });
        setGrupoSeleccionado('');
        setSubgrupoSeleccionado('');
        setBusquedaGeneral('');
        setMedicamentoSeleccionado(null);
        setMostrarNombresComerciales(false);
        setNombresComerciales([]);
        setNombreComercialSeleccionado('');
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

        if (!formData.id_unidad_venta) {
            alert("Seleccione una unidad de compra");
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
                unidades_empaque: formData.unidades_empaque || 1,
                rastrea_inventario: true,
                id_estado_producto: 22
            };

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
            <div className="crear-producto-modal-content" style={{ maxWidth: '850px' }}>
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
                
                {errorCatalogos && (
                    <div className="error-catalogos">
                        <div>Error cargando datos</div>
                        <div>{errorCatalogos}</div>
                        <button onClick={cargarCatalogosDesdeBD} className="btn-reintentar">
                            Reintentar
                        </button>
                    </div>
                )}
                
                <form className="crear-producto-form" onSubmit={handleSubmit}>
                    {/* BÚSQUEDA GENERAL - PRINCIPAL */}
                    <div className="busqueda-general-section">
                        <h3 style={{ marginBottom: '10px', color: '#1f2937', fontSize: '1.1rem' }}>
                            Buscar Medicamento por Nombre Genérico
                        </h3>
                        
                        <div className="crear-producto-form-group">
                            <input 
                                type="text"
                                className="crear-producto-form-input"
                                placeholder="Escribe el nombre del medicamento genérico (ej: Paracetamol, Ibuprofeno...)"
                                value={busquedaGeneral}
                                onChange={e => setBusquedaGeneral(e.target.value)}
                                style={{ 
                                    fontSize: '1rem',
                                    padding: '12px',
                                    border: '2px solid #3b82f6',
                                    borderRadius: '8px'
                                }}
                            />
                            {buscandoGeneral && (
                                <small style={{ color: '#3b82f6', marginTop: '5px', display: 'block' }}>
                                    Buscando...
                                </small>
                            )}
                        </div>

                        {/* Resultados de búsqueda general */}
                        {resultadosBusqueda.length > 0 && (
                            <div className="resultados-busqueda" style={{ 
                                maxHeight: '300px', 
                                overflowY: 'auto', 
                                border: '2px solid #3b82f6', 
                                borderRadius: '8px',
                                marginTop: '10px',
                                backgroundColor: 'white'
                            }}>
                                {resultadosBusqueda.map(med => (
                                    <div 
                                        key={med.id}
                                        onClick={() => seleccionarMedicamentoDeBusqueda(med)}
                                        style={{
                                            padding: '15px',
                                            cursor: 'pointer',
                                            borderBottom: '1px solid #e5e7eb',
                                            transition: 'background-color 0.2s'
                                        }}
                                        onMouseEnter={e => e.currentTarget.style.backgroundColor = '#eff6ff'}
                                        onMouseLeave={e => e.currentTarget.style.backgroundColor = 'white'}
                                    >
                                        <div style={{ fontWeight: '600', color: '#1f2937', marginBottom: '6px', fontSize: '1rem' }}>
                                            {med.generico_nombre}
                                            {med.uso_restringido && (
                                                <span style={{ 
                                                    marginLeft: '8px', 
                                                    backgroundColor: '#ef4444', 
                                                    color: 'white', 
                                                    padding: '2px 8px', 
                                                    borderRadius: '4px', 
                                                    fontSize: '0.75rem',
                                                    fontWeight: '700'
                                                }}>
                                                    {med.uso_restringido}
                                                </span>
                                            )}
                                        </div>
                                        <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                            <strong>Presentación:</strong> {med.forma_farmaceutica} - {med.concentracion}
                                        </div>
                                        {med.grupo_codigo && med.grupo_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Grupo:</strong> {med.grupo_codigo} - {med.grupo_nombre}
                                            </div>
                                        )}
                                        {med.subgrupo_codigo && med.subgrupo_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Subgrupo:</strong> {med.subgrupo_codigo} - {med.subgrupo_nombre}
                                            </div>
                                        )}
                                        {!med.subgrupo_codigo && med.clasificacion_codigo && med.clasificacion_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Clasificación:</strong> {med.clasificacion_codigo} - {med.clasificacion_nombre}
                                            </div>
                                        )}
                                        <div style={{ fontSize: '0.75rem', color: '#9ca3af', marginTop: '6px' }}>
                                            <strong>Código LINAME:</strong> {med.codigo_completo} | <strong>Código ATC:</strong> {med.generico_codigo_atq || 'N/A'}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div style={{ margin: '20px 0', textAlign: 'center', color: '#9ca3af' }}>
                        ── o navega por categorías ──
                    </div>

                    {/* Navegación por Jerarquía */}
                    <div className="liname-section">
                        <h3 style={{ marginBottom: '15px', color: '#1f2937', fontSize: '1.1rem' }}>
                            Grupo Terapéutico
                        </h3>
                        
                        <div className="crear-producto-form-grid">
                            <div className="crear-producto-form-group">
                                <label>Grupo Terapéutico</label>
                                <select 
                                    className="crear-producto-form-select"
                                    value={grupoSeleccionado}
                                    onChange={e => setGrupoSeleccionado(e.target.value)}
                                >
                                    <option value="">Seleccione un grupo</option>
                                    {grupos.map(g => (
                                        <option key={g.id} value={g.id}>
                                            {g.codigo} - {g.nombre}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            
                            <div className="crear-producto-form-group">
                                <label>Subgrupo</label>
                                <select 
                                    className="crear-producto-form-select"
                                    value={subgrupoSeleccionado}
                                    onChange={e => setSubgrupoSeleccionado(e.target.value)}
                                    disabled={!grupoSeleccionado}
                                >
                                    <option value="">Seleccione un subgrupo</option>
                                    {subgrupos.map(s => (
                                        <option key={s.id} value={s.id}>
                                            {s.codigo} - {s.nombre}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Lista de medicamentos del subgrupo */}
                        {medicamentosLiname.length > 0 && (
                            <div className="medicamentos-lista" style={{ 
                                maxHeight: '300px', 
                                overflowY: 'auto', 
                                border: '2px solid #3b82f6', 
                                borderRadius: '8px',
                                marginTop: '15px',
                                backgroundColor: 'white'
                            }}>
                                {medicamentosLiname.map(med => (
                                    <div 
                                        key={med.id}
                                        onClick={() => seleccionarMedicamento(med)}
                                        style={{
                                            padding: '15px',
                                            cursor: 'pointer',
                                            borderBottom: '1px solid #e5e7eb',
                                            backgroundColor: medicamentoSeleccionado?.id === med.id ? '#eff6ff' : 'white',
                                            transition: 'background-color 0.2s'
                                        }}
                                        onMouseEnter={e => {
                                            if (medicamentoSeleccionado?.id !== med.id) {
                                                e.currentTarget.style.backgroundColor = '#f9fafb';
                                            }
                                        }}
                                        onMouseLeave={e => {
                                            if (medicamentoSeleccionado?.id !== med.id) {
                                                e.currentTarget.style.backgroundColor = 'white';
                                            }
                                        }}
                                    >
                                        <div style={{ fontWeight: '600', color: '#1f2937', marginBottom: '6px', fontSize: '1rem' }}>
                                            {med.generico_nombre}
                                            {med.uso_restringido && (
                                                <span style={{ 
                                                    marginLeft: '8px', 
                                                    backgroundColor: '#ef4444', 
                                                    color: 'white', 
                                                    padding: '2px 8px', 
                                                    borderRadius: '4px', 
                                                    fontSize: '0.75rem',
                                                    fontWeight: '700'
                                                }}>
                                                    {med.uso_restringido}
                                                </span>
                                            )}
                                        </div>
                                        <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                            <strong>Presentación:</strong> {med.forma_farmaceutica} - {med.concentracion}
                                        </div>
                                        {med.grupo_codigo && med.grupo_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Grupo:</strong> {med.grupo_codigo} - {med.grupo_nombre}
                                            </div>
                                        )}
                                        {med.subgrupo_codigo && med.subgrupo_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Subgrupo:</strong> {med.subgrupo_codigo} - {med.subgrupo_nombre}
                                            </div>
                                        )}
                                        {!med.subgrupo_codigo && med.clasificacion_codigo && med.clasificacion_nombre && (
                                            <div style={{ fontSize: '0.875rem', color: '#6b7280', marginBottom: '4px' }}>
                                                <strong>Clasificación:</strong> {med.clasificacion_codigo} - {med.clasificacion_nombre}
                                            </div>
                                        )}
                                        <div style={{ fontSize: '0.75rem', color: '#9ca3af', marginTop: '6px' }}>
                                            <strong>Código LINAME:</strong> {med.codigo_completo} | <strong>Código ATC:</strong> {med.generico_codigo_atq || 'N/A'}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Selección de nombre comercial */}
                    {mostrarNombresComerciales && nombresComerciales.length > 0 && (
                        <div style={{ 
                            marginTop: '20px',
                            padding: '15px',
                            backgroundColor: '#fef3c7',
                            border: '1px solid #a7a49d',
                            borderRadius: '8px'
                        }}>
                            <h4 style={{ margin: '0 0 10px 0', color: '#92400e' }}>
                                Seleccione el nombre comercial (opcional):
                            </h4>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                {nombresComerciales.map((nc, idx) => (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => seleccionarNombreComercial(nc.nombre_comercial)}
                                        style={{
                                            padding: '10px',
                                            backgroundColor: nombreComercialSeleccionado === nc.nombre_comercial ? '#059669' : 'white',
                                            color: nombreComercialSeleccionado === nc.nombre_comercial ? 'white' : '#1f2937',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            cursor: 'pointer',
                                            textAlign: 'left',
                                            fontWeight: '500',
                                            transition: 'all 0.2s'
                                        }}
                                        onMouseEnter={e => {
                                            if (nombreComercialSeleccionado !== nc.nombre_comercial) {
                                                e.currentTarget.style.backgroundColor = '#f3f4f6';
                                            }
                                        }}
                                        onMouseLeave={e => {
                                            if (nombreComercialSeleccionado !== nc.nombre_comercial) {
                                                e.currentTarget.style.backgroundColor = 'white';
                                            }
                                        }}
                                    >
                                        {nc.nombre_comercial}
                                        {nc.laboratorio_fabricante && (
                                            <div style={{ fontSize: '0.75rem', opacity: 0.7, marginTop: '4px' }}>
                                                Lab: {nc.laboratorio_fabricante}
                                            </div>
                                        )}
                                    </button>
                                ))}
                                <button
                                    type="button"
                                    onClick={() => {
                                        setMostrarNombresComerciales(false);
                                        construirNombreProducto(medicamentoSeleccionado, null);
                                    }}
                                    style={{
                                        padding: '8px',
                                        backgroundColor: '#6b7280',
                                        color: 'white',
                                        border: 'none',
                                        borderRadius: '6px',
                                        cursor: 'pointer',
                                        fontSize: '0.875rem'
                                    }}
                                >
                                    Usar solo nombre genérico
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Medicamento seleccionado */}
                    {medicamentoSeleccionado && !mostrarNombresComerciales && (
                        <div style={{
                            marginTop: '15px',
                            padding: '15px',
                            backgroundColor: '#f0fdf4',
                            border: '2px solid #86efac',
                            borderRadius: '8px'
                        }}>
                            <div style={{ fontWeight: '600', color: '#15803d', marginBottom: '8px', fontSize: '1.05rem' }}>
                                ✓ Medicamento seleccionado:
                            </div>
                            <div style={{ fontSize: '0.95rem', color: '#166534', marginBottom: '6px' }}>
                                <strong>Genérico:</strong> {medicamentoSeleccionado.generico_nombre}
                            </div>
                            <div style={{ fontSize: '0.875rem', color: '#166534', marginBottom: '4px' }}>
                                <strong>Presentación:</strong> {medicamentoSeleccionado.forma_farmaceutica} {medicamentoSeleccionado.concentracion}
                            </div>
                            <div style={{ fontSize: '0.875rem', color: '#166534', marginBottom: '4px' }}>
                                <strong>Código LINAME:</strong> {medicamentoSeleccionado.codigo_completo}
                            </div>
                            {medicamentoSeleccionado.generico_codigo_atq && (
                                <div style={{ fontSize: '0.875rem', color: '#166534' }}>
                                    <strong>Código ATC:</strong> {medicamentoSeleccionado.generico_codigo_atq}
                                </div>
                            )}
                        </div>
                    )}

                    <hr style={{ margin: '25px 0', border: 'none', borderTop: '2px solid #e5e7eb' }} />

                    {/* Formulario de producto */}
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
                            placeholder="Ej: Paracetamol 500mg Tabletas"
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
                                <option value="">Seleccione una categoría</option>
                                {categorias.map(c => (
                                    <option key={c.id} value={c.id}>{c.label}</option>
                                ))}
                            </select>
                        </div>
                        
                        <div className="crear-producto-form-group">
                            <label>Unidad de Medida (Empaque) *</label>
                            <select 
                                className="crear-producto-form-select" 
                                value={formData.id_unidad_medida}
                                onChange={e => setFormData({...formData, id_unidad_medida: e.target.value})}
                                required
                                disabled={cargandoCatalogos}
                            >
                                <option value="">Seleccione unidad empaque</option>
                                {unidades.map(u => (
                                    <option key={u.id} value={u.id}>{u.label}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="crear-producto-form-grid">
                        <div className="crear-producto-form-group">
                            <label>Unidad de compra *</label>
                            <select 
                                className="crear-producto-form-select" 
                                value={formData.id_unidad_venta}
                                onChange={e => setFormData({...formData, id_unidad_venta: e.target.value})}
                                required
                                disabled={cargandoCatalogos}
                            >
                                <option value="">Seleccione unidad de compra</option>
                                {unidadesVenta.map(u => (
                                    <option key={u.id} value={u.id}>{u.label}</option>
                                ))}
                            </select>
                        </div>

                        <div className="crear-producto-form-group">
                            <label>Unidades por Empaque *</label>
                            <input 
                                type="number" 
                                min="1"
                                className="crear-producto-form-input" 
                                value={formData.unidades_empaque}
                                onChange={e => setFormData({...formData, unidades_empaque: parseInt(e.target.value) || 1})}
                                placeholder="Ej: 30"
                                required
                            />
                            <small style={{ color: '#6b7280', fontSize: '0.75rem', marginTop: '4px' }}>
                                Cantidad de unidades de compra por empaque
                            </small>
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
                            disabled={loading || cargandoCatalogos}
                        >
                            {loading ? 'Guardando...' : 'Crear Producto'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CrearProducto;