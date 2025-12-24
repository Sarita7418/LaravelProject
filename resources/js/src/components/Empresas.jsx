import React, { useEffect, useState, useMemo } from 'react'
import axios from '../lib/axios' // Asegúrate que este sea tu cliente configurado
import './Empresas.css'

function Empresas() {
  const [empresas, setEmpresas] = useState([])
  const [inactivas, setInactivas] = useState([])
  const [personas, setPersonas] = useState([])
  const [logos, setLogos] = useState([]) 

  // --- NUEVOS ESTADOS PARA UBICACIÓN ---
  const [ubicacionesRaw, setUbicacionesRaw] = useState([]) // Datos crudos de la API
  const [municipiosDisponibles, setMunicipiosDisponibles] = useState([]) // Lista filtrada según el depto seleccionado
  // -------------------------------------

  const [formVisible, setFormVisible] = useState(false)
  const [empresaEditando, setEmpresaEditando] = useState(null)
  const [mostrarInactivas, setMostrarInactivas] = useState(false)
  const [loading, setLoading] = useState(false)

  const [formData, setFormData] = useState({
    razon_social: '',
    nombre_comercial: '',
    nit: '',
    matricula_comercio: '',
    direccion_fiscal: '',
    telefono: '',
    email: '',
    municipio: '',
    departamento: '',
    id_representante_legal: '',
    logo: null 
  })

  // --- HELPER PARA LOGS ---
  const logAxiosError = (error, label) => {
    if (error?.response) {
      console.error(`${label} -> RESPONSE ERROR`, error.response.data)
    } else {
      console.error(`${label} -> ERROR`, error?.message)
    }
  }

  const normalizeArray = (payload) => Array.isArray(payload) ? payload : (payload?.data ?? [])

  // --- CARGA INICIAL DE DATOS ---
  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchEmpresas()
      fetchInactivas()
      fetchPersonas()
      fetchLogos()
      fetchUbicaciones() // <--- NUEVA LLAMADA
    }).catch(err => logAxiosError(err, 'CSRF'))
  }, [])

  // --- NUEVA FUNCIÓN: Fetch Ubicaciones ---
  const fetchUbicaciones = async () => {
    try {
      // Asumiendo que tu endpoint devuelve: [{ departamento: "LA PAZ", municipio: "EL ALTO" }, ...]
      const res = await axios.get('/api/ubicaciones')
      setUbicacionesRaw(normalizeArray(res.data))
    } catch (error) {
      logAxiosError(error, 'fetchUbicaciones')
    }
  }

  // --- CÁLCULO DINÁMICO: Lista de Departamentos Únicos ---
  // Usamos useMemo para no recalcularlo en cada render
  const listaDepartamentos = useMemo(() => {
    const deptos = ubicacionesRaw.map(u => u.departamento)
    // Eliminamos duplicados usando Set
    return [...new Set(deptos)].sort()
  }, [ubicacionesRaw])

  // --- FUNCIONES CRUD EXISTENTES (Resumidas) ---
  const fetchEmpresas = async () => {
    try {
      const res = await axios.get('/api/empresas')
      setEmpresas(normalizeArray(res.data))
    } catch (error) { logAxiosError(error, 'fetchEmpresas') }
  }

  const fetchInactivas = async () => {
    try {
      const res = await axios.get('/api/empresas-inactivas')
      setInactivas(normalizeArray(res.data))
    } catch (error) { logAxiosError(error, 'fetchInactivas') }
  }

  const fetchPersonas = async () => {
    try {
      const res = await axios.get('/api/personas')
      setPersonas(normalizeArray(res.data))
    } catch (error) { logAxiosError(error, 'fetchPersonas') }
  }

  const fetchLogos = async () => {
    try {
      const res = await axios.get('/api/logos')
      setLogos(normalizeArray(res.data))
    } catch (error) { logAxiosError(error, 'fetchLogos') }
  }

  const personaLabel = (p) => `${p.nombres ?? ''} ${p.apellido_paterno ?? ''} ${p.apellido_materno ?? ''} — CI ${p.ci ?? ''}`.trim()

  const getEmpresaLogoSrc = (empresaId) => {
    const rec = logos.find(l => l.tipo_entidad === 'empresa' && Number(l.id_entidad) === Number(empresaId))
    return rec ? `data:image/png;base64,${rec.logo}` : null
  }

  // --- HANDLERS DEL FORMULARIO ---

  const handleInputChange = e => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  // --- NUEVO HANDLER ESPECÍFICO PARA DEPARTAMENTO ---
  const handleDepartamentoChange = (e) => {
    const nuevoDepto = e.target.value
   
    setFormData(prev => ({
      ...prev,
      departamento: nuevoDepto,
      municipio: '' 
    }))

    // 3. Filtramos los municipios disponibles para este nuevo departamento
    filtrarMunicipios(nuevoDepto)
  }

  // Helper para filtrar la lista de municipios según el departamento
  const filtrarMunicipios = (departamentoSeleccionado) => {
    if (!departamentoSeleccionado) {
      setMunicipiosDisponibles([])
      return
    }
    const filtrados = ubicacionesRaw
      .filter(item => item.departamento === departamentoSeleccionado)
      .map(item => item.municipio)
      .sort()
    
    setMunicipiosDisponibles(filtrados)
  }

  const handleFileChange = e => {
    const file = e.target.files && e.target.files[0] ? e.target.files[0] : null
    setFormData(prev => ({ ...prev, logo: file }))
  }

  // --- VALIDACIÓN ESTRICTA ---
  const validarFormulario = () => {
    if (!formData.razon_social) return alert("La Razón Social es obligatoria")
    if (!formData.nit) return alert("El NIT es obligatorio")
    if (!formData.departamento) return alert("Debe seleccionar un Departamento")
    if (!formData.municipio) return alert("Debe seleccionar un Municipio")
    return true
  }

  // --- LOGICA DE GUARDADO (CREATE / UPDATE) ---
  const appendSanitizedFields = (fd, raw) => {
    const payload = { ...raw }
    // Lógica para ID representante
    if (payload.id_representante_legal) {
      const parsed = parseInt(payload.id_representante_legal)
      if (!isNaN(parsed)) payload.id_representante_legal = parsed
      else delete payload.id_representante_legal
    } else {
      delete payload.id_representante_legal
    }

    for (const [key, value] of Object.entries(payload)) {
      if (key === 'logo') continue
      fd.append(key, value)
    }
  }

  // Helpers de Logos (omitidos por brevedad, se mantienen igual que tu código original)
  const createLogoForEmpresa = async (empresaId, file) => { /* ... tu codigo ... */ 
    const fd = new FormData(); fd.append('logo', file); fd.append('id_entidad', empresaId); fd.append('tipo_entidad', 'empresa');
    await axios.post('/api/logos', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  }
  const updateLogoForEmpresa = async (logoId, file, empresaId) => { /* ... tu codigo ... */ 
    const fd = new FormData(); fd.append('logo', file); fd.append('id_entidad', empresaId); fd.append('tipo_entidad', 'empresa'); fd.append('_method','PUT');
    await axios.post(`/api/logos/${logoId}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  }

  const crearEmpresa = async () => {
    if (!validarFormulario()) return
    setLoading(true)
    try {
      const fd = new FormData()
      appendSanitizedFields(fd, formData)
      const res = await axios.post('/api/empresas', fd, { headers: { 'Content-Type': 'multipart/form-data' }})
      
      const empresaId = res.data?.empresa?.id || res.data?.id
      if (empresaId && formData.logo instanceof File) {
        await createLogoForEmpresa(empresaId, formData.logo)
      }
      
      await fetchEmpresas()
      await fetchLogos()
      resetFormulario()
    } catch (error) { logAxiosError(error, 'crearEmpresa') } 
    finally { setLoading(false) }
  }

  const actualizarEmpresa = async () => {
    if (!validarFormulario()) return
    if (!empresaEditando) return
    setLoading(true)
    try {
      // Preparar payload JSON
      const payload = { ...formData }
      if (payload.id_representante_legal) payload.id_representante_legal = parseInt(payload.id_representante_legal)
      else delete payload.id_representante_legal
      delete payload.logo

      await axios.put(`/api/empresas/${empresaEditando}`, payload)

      // Manejo de Logo
      if (formData.logo instanceof File) {
        const existing = logos.find(l => l.tipo_entidad === 'empresa' && Number(l.id_entidad) === Number(empresaEditando))
        if (existing) await updateLogoForEmpresa(existing.id, formData.logo, empresaEditando)
        else await createLogoForEmpresa(empresaEditando, formData.logo)
      }

      await fetchEmpresas()
      await fetchLogos()
      resetFormulario()
    } catch (error) { logAxiosError(error, 'actualizarEmpresa') } 
    finally { setLoading(false) }
  }

  const eliminarEmpresa = async id => {
    if (!window.confirm('¿Desactivar empresa?')) return
    try {
      await axios.delete(`/api/empresas/${id}`)
      await fetchEmpresas()
      await fetchInactivas()
    } catch (error) { logAxiosError(error, 'eliminar') }
  }
  
  const reactivarEmpresa = async id => {
    if (!window.confirm('¿Reactivar empresa?')) return
    try {
      await axios.patch(`/api/empresas/${id}/reactivar`, { estado: true })
      await fetchEmpresas()
      await fetchInactivas()
    } catch (error) { logAxiosError(error, 'reactivar') }
  }

  // --- CONFIGURAR EDICIÓN (IMPORTANTE) ---
  const iniciarEdicion = empresa => {
    setFormVisible(true)
    setEmpresaEditando(empresa.id)
    
    // 1. Cargamos los datos básicos
    setFormData({
      razon_social: empresa.razon_social ?? '',
      nombre_comercial: empresa.nombre_comercial ?? '',
      nit: empresa.nit ?? '',
      matricula_comercio: empresa.matricula_comercio ?? '',
      direccion_fiscal: empresa.direccion_fiscal ?? '',
      telefono: empresa.telefono ?? '',
      email: empresa.email ?? '',
      // Cargamos Depto y Municipio tal cual vienen de la BD
      municipio: empresa.municipio ?? '',
      departamento: empresa.departamento ?? '',
      id_representante_legal: empresa.id_representante_legal != null ? String(empresa.id_representante_legal) : '',
      logo: null
    })

    // 2. CRUCIAL: Debemos rellenar la lista de municipios disponibles 
    //    basados en el departamento que tiene la empresa guardada.
    if (empresa.departamento) {
      filtrarMunicipios(empresa.departamento)
    } else {
      setMunicipiosDisponibles([])
    }
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setEmpresaEditando(null)
    setMunicipiosDisponibles([]) // Limpiamos municipios
    setFormData({
      razon_social: '', nombre_comercial: '', nit: '', matricula_comercio: '',
      direccion_fiscal: '', telefono: '', email: '', 
      municipio: '', departamento: '', // Reseteamos
      id_representante_legal: '', logo: null
    })
  }

  const empresasMostradas = mostrarInactivas ? inactivas : empresas

  return (
    <div className="empresas-container">
      <button className="btn-crear-empresa" onClick={() => { resetFormulario(); setFormVisible(true); }}>
        Crear Empresa
      </button>

      <h2 className="empresas-title">Empresas</h2>
      
      {/* Toggle Inactivas */}
      <div className="toggle-container">
        <button className={`toggle-btn ${!mostrarInactivas ? 'active' : ''}`} onClick={() => setMostrarInactivas(false)}>Activas</button>
        <button className={`toggle-btn ${mostrarInactivas ? 'active' : ''}`} onClick={() => setMostrarInactivas(true)}>Inactivas</button>
      </div>

      {/* Grid de Cards */}
      <div className="empresas-cards">
        {empresasMostradas.map(empresa => {
          const rep = personas.find(p => p.id === empresa.id_representante_legal)
          const logoSrc = getEmpresaLogoSrc(empresa.id)
          return (
            <div className="empresa-card" key={empresa.id}>
              {logoSrc && <img className="empresa-logo" src={logoSrc} alt="Logo" />}
              <h3>{empresa.razon_social}</h3>
              <p><strong>Comercial:</strong> {empresa.nombre_comercial}</p>
              <p><strong>NIT:</strong> {empresa.nit}</p>
              <p>{empresa.municipio}, {empresa.departamento}</p> {/* Mostramos ubicación */}
              <p><strong>Rep:</strong> {rep ? `${rep.nombres} ${rep.apellido_paterno}` : '—'}</p>

              <div className="card-actions">
                {empresa.estado ? (
                  <>
                    <button onClick={() => iniciarEdicion(empresa)}>Editar</button>
                    <button className="btn-danger" onClick={() => eliminarEmpresa(empresa.id)}>Desactivar</button>
                  </>
                ) : (
                  <button onClick={() => reactivarEmpresa(empresa.id)}>Reactivar</button>
                )}
              </div>
            </div>
          )
        })}
      </div>

      {/* FORMULARIO MODAL */}
      {formVisible && (
        <div className="form-overlay">
          <div className="form-container">
            <h2>{empresaEditando ? 'Editar Empresa' : 'Nueva Empresa'}</h2>
            
            <label>Razón Social *</label>
            <input type="text" name="razon_social" value={formData.razon_social} onChange={handleInputChange} />

            <label>Nombre Comercial</label>
            <input type="text" name="nombre_comercial" value={formData.nombre_comercial} onChange={handleInputChange} />

            <label>NIT *</label>
            <input type="text" name="nit" value={formData.nit} onChange={handleInputChange} />

            {/* --- AQUÍ ESTÁN LOS COMBO BOXES NUEVOS --- */}
            
            <label>Departamento *</label>
            <select 
                name="departamento" 
                value={formData.departamento} 
                onChange={handleDepartamentoChange}
                required
            >
                <option value="">-- Seleccione Departamento --</option>
                {listaDepartamentos.map(dep => (
                    <option key={dep} value={dep}>{dep}</option>
                ))}
            </select>

            <label>Municipio *</label>
            <select 
                name="municipio" 
                value={formData.municipio} 
                onChange={handleInputChange}
                required
                disabled={!formData.departamento} // Se bloquea si no hay depto
                style={{ backgroundColor: !formData.departamento ? '#f0f0f0' : 'white' }}
            >
                <option value="">
                    {formData.departamento ? "-- Seleccione Municipio --" : "-- Primero elija Departamento --"}
                </option>
                {municipiosDisponibles.map(mun => (
                    <option key={mun} value={mun}>{mun}</option>
                ))}
            </select>
            {/* ----------------------------------------- */}

            <label>Dirección Fiscal</label>
            <input type="text" name="direccion_fiscal" value={formData.direccion_fiscal} onChange={handleInputChange} />

            <label>Teléfono</label>
            <input type="text" name="telefono" value={formData.telefono} onChange={handleInputChange} />

            <label>Correo Electrónico</label>
            <input type="email" name="email" value={formData.email} onChange={handleInputChange} />

            <label>Representante Legal</label>
            <select name="id_representante_legal" value={formData.id_representante_legal} onChange={handleInputChange}>
              <option value="">Sin representante</option>
              {personas.map(p => (
                <option key={p.id} value={p.id}>{personaLabel(p)}</option>
              ))}
            </select>

            <label>Logo (Opcional)</label>
            <input type="file" accept="image/*" onChange={handleFileChange} />

            <div className="form-actions">
              <button onClick={empresaEditando ? actualizarEmpresa : crearEmpresa} disabled={loading}>
                {loading ? 'Guardando...' : 'Guardar'}
              </button>
              <button onClick={resetFormulario} className="btn-cancel">Cancelar</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Empresas