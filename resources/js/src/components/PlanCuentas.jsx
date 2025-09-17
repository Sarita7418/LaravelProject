import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './PlanCuentas.css'

function PlanCuentas() {
    const [cuentas, setCuentas] = useState([])
    const [cuentasExpandidas, setCuentasExpandidas] = useState(new Set())
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)
    const [accionesPermitidas, setAccionesPermitidas] = useState([])
    const [subdominios, setSubdominios] = useState([])
    const [cuentasPadre, setCuentasPadre] = useState([])
    
    // Estados para modales
    const [mostrarModal, setMostrarModal] = useState(false)
    const [tipoModal, setTipoModal] = useState('crear') // 'crear' | 'editar' | 'eliminar'
    const [cuentaSeleccionada, setCuentaSeleccionada] = useState(null)
    const [guardando, setGuardando] = useState(false)
    
    // Estados para formulario
    const [formulario, setFormulario] = useState({
        id_padre: '',
        descripcion: '',
        tipo: '',
        grupo_estado_financiero: '',
        cuenta_ajuste: '',
        cuenta_presupuesto: ''
    })
    
    // Estados para filtros de subdominios por dominio
    const [subdominionsTipo, setSubdominiosTipo] = useState([])
    const [subdominiosGrupo, setSubdominiosGrupo] = useState([])
    const [subdominiosAjuste, setSubdominiosAjuste] = useState([])
    const [subdominiosPresupuesto, setSubdominiosPresupuesto] = useState([])

    useEffect(() => {
        inicializarDatos()
    }, [])

    const inicializarDatos = async () => {
        try {
            setLoading(true)
            setError(null)
            console.log('Iniciando carga de datos...')

            // Obtener cookie CSRF
            try {
                await axios.get('/sanctum/csrf-cookie')
                console.log('Cookie CSRF obtenida correctamente')
            } catch (csrfError) {
                console.warn('Error obteniendo cookie CSRF (continuando):', csrfError.message)
            }

            // Cargar datos necesarios
            await Promise.allSettled([
                cargarCuentas(),
                cargarAccionesUsuario(),
                cargarSubdominios(),
                cargarCuentasPadre()
            ])
        } catch (error) {
            console.error('Error en inicialización:', error)
            setError(`Error de inicialización: ${error.message}`)
        } finally {
            setLoading(false)
        }
    }

    const cargarCuentas = async () => {
        try {
            console.log('Cargando cuentas...')
            const response = await axios.get('/api/plan-cuentas', {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                timeout: 10000
            })

            console.log('Respuesta cuentas:', {
                status: response.status,
                dataType: typeof response.data,
                isArray: Array.isArray(response.data),
                length: response.data?.length || 0
            })

            if (response.data && Array.isArray(response.data)) {
                setCuentas(response.data)
                console.log(`✅ Cuentas cargadas: ${response.data.length}`)
                if (response.data.length > 0) {
                    console.log('Primera cuenta:', response.data[0])
                }
            } else {
                console.warn('Datos de cuentas no válidos:', response.data)
                setCuentas([])
            }
        } catch (error) {
            console.error('❌ Error cargando cuentas:', error)
            let mensajeError = 'Error al cargar las cuentas'
            
            if (error.response) {
                const status = error.response.status
                const data = error.response.data
                console.error('Response error:', { status, data })
                
                switch (status) {
                    case 401:
                        mensajeError = 'Sesión expirada - verifica tu autenticación'
                        break
                    case 403:
                        mensajeError = 'Sin permisos para acceder a las cuentas'
                        break
                    case 404:
                        mensajeError = 'Endpoint de cuentas no encontrado'
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
                (a) => a.menu_item === 'Plan de Cuentas'
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
            const res = await axios.get('/api/plan-cuentas/subdominios')
            const subdominiosData = Array.isArray(res.data) ? res.data : []
            setSubdominios(subdominiosData)
            
            console.log('Subdominios recibidos:', subdominiosData)
            
            // Filtrar subdominios por id_dominio según tu estructura de datos
            setSubdominiosTipo(subdominiosData.filter(s => s.id_dominio === 2)) // Dominio 2: Tipo (Grupo, Detalle)
            setSubdominiosGrupo(subdominiosData.filter(s => s.id_dominio === 3)) // Dominio 3: Estado Financiero
            setSubdominiosAjuste(subdominiosData.filter(s => s.id_dominio === 4)) // Dominio 4: Cuenta Ajuste
            setSubdominiosPresupuesto(subdominiosData.filter(s => s.id_dominio === 4)) // Dominio 4: Cuenta Presupuesto (reutiliza)
            
            console.log('✅ Subdominios procesados:', {
                total: subdominiosData.length,
                tipo: subdominiosData.filter(s => s.id_dominio === 2).length,
                grupo: subdominiosData.filter(s => s.id_dominio === 3).length,
                ajuste: subdominiosData.filter(s => s.id_dominio === 4).length,
                presupuesto: subdominiosData.filter(s => s.id_dominio === 4).length
            })
        } catch (error) {
            console.warn('⚠️ Error cargando subdominios (no crítico):', error.message)
            setSubdominios([])
            setSubdominiosTipo([])
            setSubdominiosGrupo([])
            setSubdominiosAjuste([])
            setSubdominiosPresupuesto([])
        }
    }

    const cargarCuentasPadre = async () => {
        try {
            console.log('Cargando cuentas padre...')
            const res = await axios.get('/api/plan-cuentas/padres')
            const cuentasPadreData = Array.isArray(res.data) ? res.data : []
            setCuentasPadre(cuentasPadreData)
            console.log('✅ Cuentas padre cargadas:', cuentasPadreData.length)
        } catch (error) {
            console.warn('⚠️ Error cargando cuentas padre (no crítico):', error.message)
            setCuentasPadre([])
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
    const abrirModalCrear = (cuentaPadre = null) => {
        setTipoModal('crear')
        setFormulario({
            id_padre: cuentaPadre ? cuentaPadre.id : '',
            descripcion: '',
            tipo: '',
            grupo_estado_financiero: '',
            cuenta_ajuste: '',
            cuenta_presupuesto: ''
        })
        setCuentaSeleccionada(cuentaPadre)
        setMostrarModal(true)
    }

    const abrirModalEditar = (cuenta) => {
        setTipoModal('editar')
        setFormulario({
            id_padre: cuenta.id_padre || '',
            descripcion: cuenta.descripcion || '',
            tipo: cuenta.tipo_id || '',
            grupo_estado_financiero: cuenta.grupo_estado_financiero_id || '',
            cuenta_ajuste: cuenta.cuenta_ajuste_id || '',
            cuenta_presupuesto: cuenta.cuenta_presupuesto_id || ''
        })
        setCuentaSeleccionada(cuenta)
        setMostrarModal(true)
    }

    const abrirModalEliminar = (cuenta) => {
        setTipoModal('eliminar')
        setCuentaSeleccionada(cuenta)
        setMostrarModal(true)
    }

    const cerrarModal = () => {
        setMostrarModal(false)
        setTipoModal('crear')
        setCuentaSeleccionada(null)
        setFormulario({
            id_padre: '',
            descripcion: '',
            tipo: '',
            grupo_estado_financiero: '',
            cuenta_ajuste: '',
            cuenta_presupuesto: ''
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
            const response = await axios.post('/api/plan-cuentas', formulario)
            console.log('Cuenta creada:', response.data)
            
            // Recargar cuentas
            await cargarCuentas()
            
            cerrarModal()
            alert('Cuenta creada exitosamente')
        } catch (error) {
            console.error('Error creando cuenta:', error)
            let mensaje = 'Error al crear la cuenta'
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
            const response = await axios.put(`/api/plan-cuentas/${cuentaSeleccionada.id}`, formulario)
            console.log('Cuenta actualizada:', response.data)
            
            // Recargar cuentas
            await cargarCuentas()
            
            cerrarModal()
            alert('Cuenta actualizada exitosamente')
        } catch (error) {
            console.error('Error actualizando cuenta:', error)
            let mensaje = 'Error al actualizar la cuenta'
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
            const response = await axios.delete(`/api/plan-cuentas/${cuentaSeleccionada.id}`)
            console.log('Cuenta eliminada:', response.data)
            
            // Recargar cuentas
            await cargarCuentas()
            
            cerrarModal()
            alert('Cuenta desactivada exitosamente')
        } catch (error) {
            console.error('Error eliminando cuenta:', error)
            let mensaje = 'Error al desactivar la cuenta'
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

    const construirJerarquia = (cuentasArray) => {
        if (!Array.isArray(cuentasArray) || cuentasArray.length === 0) {
            console.log('No hay cuentas para procesar en jerarquía')
            return []
        }

        console.log('Construyendo jerarquía con', cuentasArray.length, 'cuentas')

        const construirNivel = (padreId = null, nivelActual = 1) => {
            const hijos = cuentasArray.filter(cuenta => {
                if (nivelActual === 1) {
                    return !cuenta.id_padre || cuenta.id_padre === 0 || cuenta.id_padre === null
                }
                return cuenta.id_padre === padreId
            })

            return hijos
                .sort((a, b) => (a.codigo || '').localeCompare(b.codigo || ''))
                .map(cuenta => ({
                    ...cuenta,
                    nivel: nivelActual,
                    hijos: construirNivel(cuenta.id, nivelActual + 1)
                }))
        }

        const jerarquia = construirNivel()
        console.log('Jerarquía construida:', jerarquia.length, 'cuentas de nivel 1')
        return jerarquia
    }

    const toggleExpansion = (cuentaId) => {
        const nuevasExpandidas = new Set(cuentasExpandidas)
        if (nuevasExpandidas.has(cuentaId)) {
            nuevasExpandidas.delete(cuentaId)
        } else {
            nuevasExpandidas.add(cuentaId)
        }
        setCuentasExpandidas(nuevasExpandidas)
    }

    const renderFilaCuenta = (cuenta, nivel = 1) => {
        const tieneHijos = cuenta.hijos && cuenta.hijos.length > 0
        const estaExpandida = cuentasExpandidas.has(cuenta.id)
        const indentacion = (nivel - 1) * 30
        const esNivel1 = nivel === 1
        const esNivel2EnAdelante = nivel >= 2

        return (
            <React.Fragment key={`cuenta-${cuenta.id}`}>
                <tr className={`cuenta-row cuenta-nivel-${nivel} ${tieneHijos ? 'cuenta-padre' : 'cuenta-hija'}`}>
                    <td style={{ paddingLeft: `${indentacion}px` }}>
                        <div className="cuenta-codigo-container">
                            {tieneHijos && (
                                <button
                                    className={`expand-btn ${estaExpandida ? 'expanded' : ''}`}
                                    onClick={() => toggleExpansion(cuenta.id)}
                                    title={estaExpandida ? 'Contraer' : 'Expandir'}
                                >
                                    {estaExpandida ? '▼' : '▶'}
                                </button>
                            )}
                            <span className="cuenta-codigo">{cuenta.codigo || 'N/A'}</span>
                        </div>
                    </td>
                    <td className="cuenta-descripcion">{cuenta.descripcion || 'Sin descripción'}</td>
                    <td className="cuenta-tipo">{cuenta.tipo || '-'}</td>
                    <td className="cuenta-nivel-col">Nivel {nivel}</td>
                    <td className="cuenta-grupo">
                        {obtenerDescripcionSubdominio(cuenta.grupo_estado_financiero)}
                    </td>
                    <td className="cuenta-ajuste">
                        {obtenerDescripcionSubdominio(cuenta.cuenta_ajuste)}
                    </td>
                    <td className="cuenta-presupuesto">
                        {obtenerDescripcionSubdominio(cuenta.cuenta_presupuesto)}
                    </td>
                    <td>
                        <span className={`status ${(cuenta.estado === 'ACTIVO' || cuenta.estado === 1 || cuenta.estado === '1') ? 'active' : 'inactive'}`}>
                            {cuenta.estado === 1 || cuenta.estado === '1' ? 'ACTIVO' : cuenta.estado === 0 || cuenta.estado === '0' ? 'INACTIVO' : cuenta.estado || 'N/A'}
                        </span>
                    </td>
                    <td className="acciones-cell">
                        {/* Nivel 1: Solo editar y añadir hijos */}
                        {esNivel1 && puede('editar') && (
                            <>
                                <button 
                                    className="edit-btn" 
                                    title="Editar"
                                    onClick={() => abrirModalEditar(cuenta)}
                                >
                                    ✏️
                                </button>
                                <button 
                                    className="add-child-btn" 
                                    title="Añadir cuenta hija"
                                    onClick={() => abrirModalCrear(cuenta)}
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
                                        title="Añadir cuenta hija"
                                        onClick={() => abrirModalCrear(cuenta)}
                                    >
                                        ➕
                                    </button>
                                )}
                                {puede('editar') && (
                                    <button 
                                        className="edit-btn" 
                                        title="Editar"
                                        onClick={() => abrirModalEditar(cuenta)}
                                    >
                                        ✏️
                                    </button>
                                )}
                                {puede('eliminar') && (
                                    <button 
                                        className="delete-btn" 
                                        title="Desactivar"
                                        onClick={() => abrirModalEliminar(cuenta)}
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
                {tieneHijos && estaExpandida && cuenta.hijos.map(hijo =>
                    renderFilaCuenta(hijo, nivel + 1)
                )}
            </React.Fragment>
        )
    }

    const reintentar = () => {
        console.log('Reintentando carga de datos...')
        inicializarDatos()
    }

    const cuentasJerarquicas = construirJerarquia(cuentas)

    // Renderizar modal
    const renderModal = () => {
        if (!mostrarModal) return null

        return (
            <div className="modal-overlay" onClick={cerrarModal}>
                <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                    <div className="modal-header">
                        <h3>
                            {tipoModal === 'crear' && `Crear nueva cuenta${cuentaSeleccionada ? ` (Hija de: ${cuentaSeleccionada.codigo} - ${cuentaSeleccionada.descripcion})` : ''}`}
                            {tipoModal === 'editar' && `Editar cuenta: ${cuentaSeleccionada?.codigo}`}
                            {tipoModal === 'eliminar' && `Confirmar eliminación`}
                        </h3>
                        <button className="modal-close" onClick={cerrarModal}>×</button>
                    </div>
                    
                    <div className="modal-body">
                        {tipoModal === 'eliminar' ? (
                            <div>
                                <p>¿Estás seguro de que deseas desactivar la cuenta?</p>
                                <div className="cuenta-info">
                                    <strong>Código:</strong> {cuentaSeleccionada?.codigo}<br/>
                                    <strong>Descripción:</strong> {cuentaSeleccionada?.descripcion}
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={tipoModal === 'crear' ? manejarSubmitCrear : manejarSubmitEditar}>
                                {/* Campo Padre (solo para crear) */}
                                {tipoModal === 'crear' && !cuentaSeleccionada && (
                                    <div className="form-group">
                                        <label htmlFor="id_padre">Cuenta Padre *</label>
                                        <select
                                            id="id_padre"
                                            name="id_padre"
                                            value={formulario.id_padre}
                                            onChange={manejarCambioFormulario}
                                            required
                                        >
                                            <option value="">Seleccionar cuenta padre...</option>
                                            {cuentasPadre.map(cuenta => (
                                                <option key={cuenta.id} value={cuenta.id}>
                                                    {cuenta.codigo} - {cuenta.descripcion} (Nivel {cuenta.nivel})
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
                                        placeholder="Ingresa la descripción de la cuenta"
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
                                        Tipo de cuenta: {subdominionsTipo.length} opciones disponibles
                                    </small>
                                </div>
                                
                                {/* Grupo Estado Financiero */}
                                <div className="form-group">
                                    <label htmlFor="grupo_estado_financiero">Grupo Estado Financiero</label>
                                    <select
                                        id="grupo_estado_financiero"
                                        name="grupo_estado_financiero"
                                        value={formulario.grupo_estado_financiero}
                                        onChange={manejarCambioFormulario}
                                    >
                                        <option value="">Seleccionar grupo...</option>
                                        {subdominiosGrupo.map(subdominio => (
                                            <option key={subdominio.id} value={subdominio.id}>
                                                {subdominio.descripcion}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="form-help">
                                        Clasificación contable: {subdominiosGrupo.length} opciones disponibles
                                    </small>
                                </div>
                                
                                {/* Cuenta Ajuste */}
                                <div className="form-group">
                                    <label htmlFor="cuenta_ajuste">Cuenta Ajuste</label>
                                    <select
                                        id="cuenta_ajuste"
                                        name="cuenta_ajuste"
                                        value={formulario.cuenta_ajuste}
                                        onChange={manejarCambioFormulario}
                                    >
                                        <option value="">Seleccionar cuenta ajuste...</option>
                                        {subdominiosAjuste.map(subdominio => (
                                            <option key={subdominio.id} value={subdominio.id}>
                                                {subdominio.descripcion}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="form-help">
                                        Aplica ajustes: {subdominiosAjuste.length} opciones disponibles
                                    </small>
                                </div>
                                
                                {/* Cuenta Presupuesto */}
                                <div className="form-group">
                                    <label htmlFor="cuenta_presupuesto">Cuenta Presupuesto</label>
                                    <select
                                        id="cuenta_presupuesto"
                                        name="cuenta_presupuesto"
                                        value={formulario.cuenta_presupuesto}
                                        onChange={manejarCambioFormulario}
                                    >
                                        <option value="">Seleccionar cuenta presupuesto...</option>
                                        {subdominiosPresupuesto.map(subdominio => (
                                            <option key={subdominio.id} value={subdominio.id}>
                                                {subdominio.descripcion}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="form-help">
                                        Control presupuestario: {subdominiosPresupuesto.length} opciones disponibles
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
            <div className="plan-cuentas-container">
                <div className="plan-cuentas-header">
                    <h2 className="plan-cuentas-title">Plan de Cuentas</h2>
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
                        Cargando plan de cuentas...
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
        <div className="plan-cuentas-container">
            <div className="plan-cuentas-header">
                <h2 className="plan-cuentas-title">Plan de Cuentas</h2>
                {puede('crear') && (
                    <button 
                        className="add-btn"
                        onClick={() => abrirModalCrear()}
                    >
                        Añadir Cuenta
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
                <table className="plan-cuentas-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Grupo Estado Financiero</th>
                            <th>Cuenta Ajuste</th>
                            <th>Cuenta Presupuesto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cuentas.length === 0 ? (
                            <tr>
                                <td colSpan="9" style={{ 
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
                                        No se encontraron cuentas
                                    </div>
                                    <div style={{ 
                                        fontSize: '14px', 
                                        marginBottom: '20px', 
                                        color: '#6c757d' 
                                    }}>
                                        Verifica que existan cuentas ACTIVAS en la base de datos
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
                        ) : cuentasJerarquicas.length === 0 ? (
                            <tr>
                                <td colSpan="9" style={{ 
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
                                        ⚠️ Error en la jerarquía de cuentas
                                    </div>
                                    <div style={{ fontSize: '14px', color: '#6c757d' }}>
                                        Se encontraron {cuentas.length} cuentas pero no se pudieron organizar jerárquicamente
                                    </div>
                                </td>
                            </tr>
                        ) : (
                            cuentasJerarquicas.map(cuenta => renderFilaCuenta(cuenta, 1))
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

export default PlanCuentas