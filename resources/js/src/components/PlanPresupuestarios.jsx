import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './PlanPresupuestarios.css'

function PlanPresupuestarios() {
    const [presupuestarios, setPresupuestarios] = useState([])
    const [presupuestariosExpandidos, setPresupuestariosExpandidos] = useState(new Set())
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)
    const [accionesPermitidas, setAccionesPermitidas] = useState([])
    const [subdominios, setSubdominios] = useState([])
    const [presupuestariosPadre, setPresupuestariosPadre] = useState([])
    
    // Estados para modales
    const [mostrarModal, setMostrarModal] = useState(false)
    const [tipoModal, setTipoModal] = useState('crear') // 'crear' | 'editar' | 'eliminar'
    const [presupuestarioSeleccionado, setPresupuestarioSeleccionado] = useState(null)
    const [guardando, setGuardando] = useState(false)
    
    // Estados para formulario
    const [formulario, setFormulario] = useState({
        id_padre: '',
        descripcion: '',
        tipo: ''
    })
    
    // Estados para filtros de subdominios por dominio
    const [subdominionsTipo, setSubdominiosTipo] = useState([])

    useEffect(() => {
        inicializarDatos()
    }, [])

    const inicializarDatos = async () => {
        try {
            setLoading(true)
            setError(null)
            console.log('Iniciando carga de datos de plan presupuestario...')

            // Obtener cookie CSRF
            try {
                await axios.get('/sanctum/csrf-cookie')
                console.log('Cookie CSRF obtenida correctamente')
            } catch (csrfError) {
                console.warn('Error obteniendo cookie CSRF (continuando):', csrfError.message)
            }

            // Cargar datos necesarios
            await Promise.allSettled([
                cargarPresupuestarios(),
                cargarAccionesUsuario(),
                cargarSubdominios(),
                cargarPresupuestariosPadre()
            ])
        } catch (error) {
            console.error('Error en inicialización:', error)
            setError(`Error de inicialización: ${error.message}`)
        } finally {
            setLoading(false)
        }
    }

    const cargarPresupuestarios = async () => {
        try {
            console.log('Cargando presupuestarios...')
            const response = await axios.get('/api/plan-presupuestarios', {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                timeout: 10000
            })

            console.log('Respuesta presupuestarios:', {
                status: response.status,
                dataType: typeof response.data,
                isArray: Array.isArray(response.data),
                length: response.data?.length || 0
            })

            if (response.data && Array.isArray(response.data)) {
                setPresupuestarios(response.data)
                console.log(`✅ Presupuestarios cargados: ${response.data.length}`)
                if (response.data.length > 0) {
                    console.log('Primer presupuestario:', response.data[0])
                }
            } else {
                console.warn('Datos de presupuestarios no válidos:', response.data)
                setPresupuestarios([])
            }
        } catch (error) {
            console.error('❌ Error cargando presupuestarios:', error)
            let mensajeError = 'Error al cargar los presupuestarios'
            
            if (error.response) {
                const status = error.response.status
                const data = error.response.data
                console.error('Response error:', { status, data })
                
                switch (status) {
                    case 401:
                        mensajeError = 'Sesión expirada - verifica tu autenticación'
                        break
                    case 403:
                        mensajeError = 'Sin permisos para acceder a los presupuestarios'
                        break
                    case 404:
                        mensajeError = 'Endpoint de presupuestarios no encontrado'
                        break
                    case 500:
                        mensajeError = `Error del servidor: ${data?.message || 'Error interno'}`
                        break
                    default:
                        mensajeError = data?.message || `Error HTTP ${status}`
                }
            } else if (error.request) {
                mensajeError = 'No se pudo conectar con el servidor'
            } else if (error.code === 'ECONNABORTED') {
                mensajeError = 'Timeout: El servidor tardó demasiado en responder'
            } else {
                mensajeError = error.message
            }
            
            throw new Error(mensajeError)
        }
    }

    const cargarAccionesUsuario = async () => {
        try {
            console.log('Cargando acciones de usuario...')
            const userRes = await axios.get('/api/user')
            const userId = userRes.data.id
            const accionesRes = await axios.get(`/api/acciones/${userId}`)
            const accionesFiltradas = accionesRes.data.filter(
                (a) => a.menu_item === 'Plan Presupuestarios'
            ).map((a) => a.accion)
            setAccionesPermitidas(accionesFiltradas)
            console.log('✅ Acciones cargadas:', accionesFiltradas)
        } catch (error) {
            console.warn('⚠️ Error cargando acciones (no crítico):', error.message)
            setAccionesPermitidas([])
        }
    }

    const cargarSubdominios = async () => {
        try {
            console.log('Cargando subdominios...')
            const res = await axios.get('/api/plan-presupuestarios/subdominios')
            const subdominiosData = Array.isArray(res.data) ? res.data : []
            setSubdominios(subdominiosData)
            
            console.log('Subdominios recibidos:', subdominiosData)
            
            // Filtrar subdominios por id_dominio según tu estructura de datos
            setSubdominiosTipo(subdominiosData.filter(s => s.id_dominio === 4 || s.id_dominio === 5)) // Dominios 4 y 5: Tipo presupuestario
            
            console.log('✅ Subdominios procesados:', {
                total: subdominiosData.length,
                tipo: subdominiosData.filter(s => s.id_dominio === 4 || s.id_dominio === 5).length
            })
        } catch (error) {
            console.warn('⚠️ Error cargando subdominios (no crítico):', error.message)
            setSubdominios([])
            setSubdominiosTipo([])
        }
    }

    const cargarPresupuestariosPadre = async () => {
        try {
            console.log('Cargando presupuestarios padre...')
            const res = await axios.get('/api/plan-presupuestarios/padres')
            const presupuestariosPadreData = Array.isArray(res.data) ? res.data : []
            setPresupuestariosPadre(presupuestariosPadreData)
            console.log('✅ Presupuestarios padre cargados:', presupuestariosPadreData.length)
        } catch (error) {
            console.warn('⚠️ Error cargando presupuestarios padre (no crítico):', error.message)
            setPresupuestariosPadre([])
        }
    }

    const puede = (accion) => accionesPermitidas.includes(accion)

    const obtenerDescripcionSubdominio = (subdominioId) => {
        if (!subdominioId) return '-'
        if (!Array.isArray(subdominios)) {
            console.warn('Subdominios no es un array:', subdominios)
            return subdominioId.toString()
        }
        const subdominio = subdominios.find(s => s && s.id === subdominioId)
        return subdominio ? subdominio.descripcion : subdominioId.toString()
    }

    // Funciones para modales
    const abrirModalCrear = (presupuestarioPadre = null) => {
        setTipoModal('crear')
        setFormulario({
            id_padre: presupuestarioPadre ? presupuestarioPadre.id : '',
            descripcion: '',
            tipo: ''
        })
        setPresupuestarioSeleccionado(presupuestarioPadre)
        setMostrarModal(true)
    }

    const abrirModalEditar = (presupuestario) => {
        setTipoModal('editar')
        setFormulario({
            id_padre: presupuestario.id_padre || '',
            descripcion: presupuestario.descripcion || '',
            tipo: presupuestario.tipo_id || ''
        })
        setPresupuestarioSeleccionado(presupuestario)
        setMostrarModal(true)
    }

    const abrirModalEliminar = (presupuestario) => {
        setTipoModal('eliminar')
        setPresupuestarioSeleccionado(presupuestario)
        setMostrarModal(true)
    }

    const cerrarModal = () => {
        setMostrarModal(false)
        setTipoModal('crear')
        setPresupuestarioSeleccionado(null)
        setFormulario({
            id_padre: '',
            descripcion: '',
            tipo: ''
        })
    }

    const manejarCambioFormulario = (e) => {
        const { name, value } = e.target
        setFormulario(prev => ({
            ...prev,
            [name]: value
        }))
    }

    const manejarSubmitCrear = async (e) => {
        e.preventDefault()
        setGuardando(true)

        try {
            const response = await axios.post('/api/plan-presupuestarios', formulario)
            console.log('Presupuestario creado:', response.data)
            
            // Recargar presupuestarios
            await cargarPresupuestarios()
            
            cerrarModal()
            alert('Presupuestario creado exitosamente')
        } catch (error) {
            console.error('Error creando presupuestario:', error)
            let mensaje = 'Error al crear el presupuestario'
            if (error.response?.data?.message) {
                mensaje = error.response.data.message
            } else if (error.response?.data?.error) {
                mensaje = error.response.data.error
            }
            alert(mensaje)
        } finally {
            setGuardando(false)
        }
    }

    const manejarSubmitEditar = async (e) => {
        e.preventDefault()
        setGuardando(true)

        try {
            const response = await axios.put(`/api/plan-presupuestarios/${presupuestarioSeleccionado.id}`, formulario)
            console.log('Presupuestario actualizado:', response.data)
            
            // Recargar presupuestarios
            await cargarPresupuestarios()
            
            cerrarModal()
            alert('Presupuestario actualizado exitosamente')
        } catch (error) {
            console.error('Error actualizando presupuestario:', error)
            let mensaje = 'Error al actualizar el presupuestario'
            if (error.response?.data?.message) {
                mensaje = error.response.data.message
            } else if (error.response?.data?.error) {
                mensaje = error.response.data.error
            }
            alert(mensaje)
        } finally {
            setGuardando(false)
        }
    }

    const manejarEliminar = async () => {
        setGuardando(true)

        try {
            const response = await axios.delete(`/api/plan-presupuestarios/${presupuestarioSeleccionado.id}`)
            console.log('Presupuestario eliminado:', response.data)
            
            // Recargar presupuestarios
            await cargarPresupuestarios()
            
            cerrarModal()
            alert('Presupuestario desactivado exitosamente')
        } catch (error) {
            console.error('Error eliminando presupuestario:', error)
            let mensaje = 'Error al desactivar el presupuestario'
            if (error.response?.data?.message) {
                mensaje = error.response.data.message
            } else if (error.response?.data?.error) {
                mensaje = error.response.data.error
            }
            alert(mensaje)
        } finally {
            setGuardando(false)
        }
    }

    const construirJerarquia = (presupuestariosArray) => {
        if (!Array.isArray(presupuestariosArray) || presupuestariosArray.length === 0) {
            console.log('No hay presupuestarios para procesar en jerarquía')
            return []
        }

        console.log('Construyendo jerarquía con', presupuestariosArray.length, 'presupuestarios')

        const construirNivel = (padreId = null, nivelActual = 1) => {
            const hijos = presupuestariosArray.filter(presupuestario => {
                if (nivelActual === 1) {
                    return !presupuestario.id_padre || presupuestario.id_padre === 0 || presupuestario.id_padre === null
                }
                return presupuestario.id_padre === padreId
            })

            return hijos
                .sort((a, b) => (a.codigo || '').localeCompare(b.codigo || ''))
                .map(presupuestario => ({
                    ...presupuestario,
                    nivel: nivelActual,
                    hijos: construirNivel(presupuestario.id, nivelActual + 1)
                }))
        }

        const jerarquia = construirNivel()
        console.log('Jerarquía construida:', jerarquia.length, 'presupuestarios de nivel 1')
        return jerarquia
    }

    const toggleExpansion = (presupuestarioId) => {
        const nuevasExpandidas = new Set(presupuestariosExpandidos)
        if (nuevasExpandidas.has(presupuestarioId)) {
            nuevasExpandidas.delete(presupuestarioId)
        } else {
            nuevasExpandidas.add(presupuestarioId)
        }
        setPresupuestariosExpandidos(nuevasExpandidas)
    }

    const renderFilaPresupuestario = (presupuestario, nivel = 1) => {
        const tieneHijos = presupuestario.hijos && presupuestario.hijos.length > 0
        const estaExpandida = presupuestariosExpandidos.has(presupuestario.id)
        const indentacion = (nivel - 1) * 30
        const esNivel1 = nivel === 1
        const esNivel2EnAdelante = nivel >= 2

        return (
            <React.Fragment key={`presupuestario-${presupuestario.id}`}>
                <tr className={`presupuestario-row presupuestario-nivel-${nivel} ${tieneHijos ? 'presupuestario-padre' : 'presupuestario-hija'}`}>
                    <td style={{ paddingLeft: `${indentacion}px` }}>
                        <div className="presupuestario-codigo-container">
                            {tieneHijos && (
                                <button
                                    className={`expand-btn ${estaExpandida ? 'expanded' : ''}`}
                                    onClick={() => toggleExpansion(presupuestario.id)}
                                    title={estaExpandida ? 'Contraer' : 'Expandir'}
                                >
                                    {estaExpandida ? '▼' : '▶'}
                                </button>
                            )}
                            <span className="presupuestario-codigo">{presupuestario.codigo || 'N/A'}</span>
                        </div>
                    </td>
                    <td className="presupuestario-descripcion">{presupuestario.descripcion || 'Sin descripción'}</td>
                    <td className="presupuestario-tipo">{presupuestario.tipo || '-'}</td>
                    <td className="presupuestario-nivel-col">Nivel {nivel}</td>
                    <td>
                        <span className={`status ${(presupuestario.estado === 'ACTIVO' || presupuestario.estado === 1 || presupuestario.estado === '1') ? 'active' : 'inactive'}`}>
                            {presupuestario.estado === 1 || presupuestario.estado === '1' ? 'ACTIVO' : presupuestario.estado === 0 || presupuestario.estado === '0' ? 'INACTIVO' : presupuestario.estado || 'N/A'}
                        </span>
                    </td>
                    <td className="acciones-cell">
                        {/* Nivel 1: Solo editar y añadir hijos */}
                        {esNivel1 && puede('editar') && (
                            <>
                                <button 
                                    className="edit-btn" 
                                    title="Editar"
                                    onClick={() => abrirModalEditar(presupuestario)}
                                >
                                    ✏️
                                </button>
                                <button 
                                    className="add-child-btn" 
                                    title="Añadir presupuestario hijo"
                                    onClick={() => abrirModalCrear(presupuestario)}
                                >
                                    ➕
                                </button>
                            </>
                        )}
                        
                        {/* Nivel 2 en adelante: CRUD completo */}
                        {esNivel2EnAdelante && (
                            <>
                                {puede('crear') && (
                                    <button 
                                        className="add-child-btn" 
                                        title="Añadir presupuestario hijo"
                                        onClick={() => abrirModalCrear(presupuestario)}
                                    >
                                        ➕
                                    </button>
                                )}
                                {puede('editar') && (
                                    <button 
                                        className="edit-btn" 
                                        title="Editar"
                                        onClick={() => abrirModalEditar(presupuestario)}
                                    >
                                        ✏️
                                    </button>
                                )}
                                {puede('eliminar') && (
                                    <button 
                                        className="delete-btn" 
                                        title="Desactivar"
                                        onClick={() => abrirModalEliminar(presupuestario)}
                                    >
                                        🗑️
                                    </button>
                                )}
                            </>
                        )}
                        
                        {!puede('editar') && !puede('crear') && !puede('eliminar') && (
                            <span className="no-actions">-</span>
                        )}
                    </td>
                </tr>
                {tieneHijos && estaExpandida && presupuestario.hijos.map(hijo =>
                    renderFilaPresupuestario(hijo, nivel + 1)
                )}
            </React.Fragment>
        )
    }

    const reintentar = () => {
        console.log('Reintentando carga de datos...')
        inicializarDatos()
    }

    const presupuestariosJerarquicos = construirJerarquia(presupuestarios)

    // Renderizar modal
    const renderModal = () => {
        if (!mostrarModal) return null

        return (
            <div className="modal-overlay" onClick={cerrarModal}>
                <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                    <div className="modal-header">
                        <h3>
                            {tipoModal === 'crear' && `Crear nuevo presupuestario${presupuestarioSeleccionado ? ` (Hijo de: ${presupuestarioSeleccionado.codigo} - ${presupuestarioSeleccionado.descripcion})` : ''}`}
                            {tipoModal === 'editar' && `Editar presupuestario: ${presupuestarioSeleccionado?.codigo}`}
                            {tipoModal === 'eliminar' && `Confirmar eliminación`}
                        </h3>
                        <button className="modal-close" onClick={cerrarModal}>×</button>
                    </div>
                    
                    <div className="modal-body">
                        {tipoModal === 'eliminar' ? (
                            <div>
                                <p>¿Estás seguro de que deseas desactivar el presupuestario?</p>
                                <div className="presupuestario-info">
                                    <strong>Código:</strong> {presupuestarioSeleccionado?.codigo}<br/>
                                    <strong>Descripción:</strong> {presupuestarioSeleccionado?.descripcion}
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={tipoModal === 'crear' ? manejarSubmitCrear : manejarSubmitEditar}>
                                {/* Campo Padre (solo para crear) */}
                                {tipoModal === 'crear' && !presupuestarioSeleccionado && (
                                    <div className="form-group">
                                        <label htmlFor="id_padre">Presupuestario Padre *</label>
                                        <select
                                            id="id_padre"
                                            name="id_padre"
                                            value={formulario.id_padre}
                                            onChange={manejarCambioFormulario}
                                            required
                                        >
                                            <option value="">Seleccionar presupuestario padre...</option>
                                            {presupuestariosPadre.map(presupuestario => (
                                                <option key={presupuestario.id} value={presupuestario.id}>
                                                    {presupuestario.codigo} - {presupuestario.descripcion} (Nivel {presupuestario.nivel})
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                                
                                {/* Descripción */}
                                <div className="form-group">
                                    <label htmlFor="descripcion">Descripción *</label>
                                    <input
                                        type="text"
                                        id="descripcion"
                                        name="descripcion"
                                        value={formulario.descripcion}
                                        onChange={manejarCambioFormulario}
                                        required
                                        placeholder="Ingresa la descripción del presupuestario"
                                    />
                                </div>
                                
                                {/* Tipo */}
                                <div className="form-group">
                                    <label htmlFor="tipo">Tipo</label>
                                    <select
                                        id="tipo"
                                        name="tipo"
                                        value={formulario.tipo}
                                        onChange={manejarCambioFormulario}
                                    >
                                        <option value="">Seleccionar tipo...</option>
                                        {subdominionsTipo.map(subdominio => (
                                            <option key={subdominio.id} value={subdominio.id}>
                                                {subdominio.descripcion}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="form-help">
                                        Tipo de presupuestario: {subdominionsTipo.length} opciones disponibles
                                    </small>
                                </div>
                            </form>
                        )}
                    </div>
                    
                    <div className="modal-footer">
                        <button 
                            type="button" 
                            className="btn-secondary" 
                            onClick={cerrarModal}
                            disabled={guardando}
                        >
                            Cancelar
                        </button>
                        
                        {tipoModal === 'eliminar' ? (
                            <button 
                                type="button" 
                                className="btn-danger" 
                                onClick={manejarEliminar}
                                disabled={guardando}
                            >
                                {guardando ? 'Desactivando...' : 'Desactivar'}
                            </button>
                        ) : (
                            <button 
                                type="button" 
                                className="btn-primary" 
                                onClick={tipoModal === 'crear' ? manejarSubmitCrear : manejarSubmitEditar}
                                disabled={guardando}
                            >
                                {guardando ? 'Guardando...' : (tipoModal === 'crear' ? 'Crear' : 'Actualizar')}
                            </button>
                        )}
                    </div>
                </div>
            </div>
        )
    }

    // Estado de loading mejorado
    if (loading) {
        return (
            <div className="plan-presupuestarios-container">
                <div className="plan-presupuestarios-header">
                    <h2 className="plan-presupuestarios-title">Plan Presupuestario</h2>
                </div>
                <div style={{ 
                    display: 'flex', 
                    justifyContent: 'center', 
                    alignItems: 'center', 
                    height: '400px', 
                    flexDirection: 'column', 
                    background: '#f8f9fa', 
                    border: '1px solid #dee2e6', 
                    borderRadius: '8px' 
                }}>
                    <div style={{ 
                        fontSize: '18px', 
                        fontWeight: 'bold', 
                        marginBottom: '10px', 
                        color: '#495057' 
                    }}>
                        Cargando plan presupuestario...
                    </div>
                    <div style={{ 
                        fontSize: '14px', 
                        color: '#6c757d', 
                        textAlign: 'center' 
                    }}>
                        Obteniendo datos del servidor<br/>
                        <small>Esto puede tardar unos segundos</small>
                    </div>
                </div>
            </div>
        )
    }

    return (
        <div className="plan-presupuestarios-container">
            <div className="plan-presupuestarios-header">
                <h2 className="plan-presupuestarios-title">Plan Presupuestario</h2>
                {puede('crear') && (
                    <button 
                        className="add-btn"
                        onClick={() => abrirModalCrear()}
                    >
                        Añadir Presupuestario
                    </button>
                )}
            </div>

            {error && (
                <div className="error-message" style={{ 
                    background: '#f8d7da', 
                    color: '#721c24', 
                    padding: '15px', 
                    borderRadius: '8px', 
                    marginBottom: '20px', 
                    border: '1px solid #f5c6cb' 
                }}>
                    <div style={{ 
                        display: 'flex', 
                        alignItems: 'center', 
                        marginBottom: '10px' 
                    }}>
                        <span style={{ marginRight: '8px', fontSize: '18px' }}>⚠️</span>
                        <strong>Error:</strong>
                    </div>
                    <div style={{ marginLeft: '26px', marginBottom: '15px' }}>
                        {error}
                    </div>
                    <button 
                        onClick={reintentar} 
                        style={{ 
                            background: '#007bff', 
                            color: 'white', 
                            border: 'none', 
                            padding: '10px 20px', 
                            borderRadius: '5px', 
                            cursor: 'pointer', 
                            fontSize: '14px', 
                            fontWeight: '500' 
                        }}
                    >
                        🔄 Reintentar
                    </button>
                </div>
            )}

            <div className="tabla-container">
                <table className="plan-presupuestarios-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {presupuestarios.length === 0 ? (
                            <tr>
                                <td colSpan="6" style={{ 
                                    textAlign: 'center', 
                                    padding: '60px 20px', 
                                    background: '#fff3cd', 
                                    color: '#856404' 
                                }}>
                                    <div style={{ fontSize: '24px', marginBottom: '10px' }}>📊</div>
                                    <div style={{ 
                                        fontSize: '18px', 
                                        fontWeight: 'bold', 
                                        marginBottom: '8px' 
                                    }}>
                                        No se encontraron presupuestarios
                                    </div>
                                    <div style={{ 
                                        fontSize: '14px', 
                                        marginBottom: '20px', 
                                        color: '#6c757d' 
                                    }}>
                                        Verifica que existan presupuestarios ACTIVOS en la base de datos
                                    </div>
                                    <button 
                                        onClick={reintentar} 
                                        style={{ 
                                            background: '#28a745', 
                                            color: 'white', 
                                            border: 'none', 
                                            padding: '10px 20px', 
                                            borderRadius: '5px', 
                                            cursor: 'pointer', 
                                            fontSize: '14px' 
                                        }}
                                    >
                                        🔄 Recargar
                                    </button>
                                </td>
                            </tr>
                        ) : presupuestariosJerarquicos.length === 0 ? (
                            <tr>
                                <td colSpan="6" style={{ 
                                    textAlign: 'center', 
                                    padding: '40px 20px', 
                                    background: '#f8d7da', 
                                    color: '#721c24' 
                                }}>
                                    <div style={{ 
                                        fontSize: '18px', 
                                        fontWeight: 'bold', 
                                        marginBottom: '8px' 
                                    }}>
                                        ⚠️ Error en la jerarquía de presupuestarios
                                    </div>
                                    <div style={{ fontSize: '14px', color: '#6c757d' }}>
                                        Se encontraron {presupuestarios.length} presupuestarios pero no se pudieron organizar jerárquicamente
                                    </div>
                                </td>
                            </tr>
                        ) : (
                            presupuestariosJerarquicos.map(presupuestario => renderFilaPresupuestario(presupuestario, 1))
                        )}
                    </tbody>
                </table>
            </div>

            <div className="debug-info" style={{ 
                marginTop: '20px', 
                padding: '10px', 
                background: '#f8f9fa', 
                borderRadius: '5px', 
                fontSize: '12px', 
                color: '#6c757d' 
            }}>
                
                {error && (
                    <div style={{ marginTop: '5px', color: '#dc3545' }}>
                        ❌ <strong>Error:</strong> {error}
                    </div>
                )}
            </div>

            {/* Renderizar Modal */}
            {renderModal()}
        </div>
    )
}

export default PlanPresupuestarios