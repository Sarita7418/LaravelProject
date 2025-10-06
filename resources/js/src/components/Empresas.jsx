import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Empresas.css'

function Empresas() {
  const [empresas, setEmpresas] = useState([])
  const [inactivas, setInactivas] = useState([])
  const [personas, setPersonas] = useState([])
  const [logos, setLogos] = useState([]) // logos crudos (con id, id_entidad, tipo_entidad, logo base64)

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
    logo: null // File | null; en edición puede venir base64 solo para preview
  })

  const logAxiosError = (error, label) => {
    if (error?.response) {
      console.error(`${label} -> RESPONSE ERROR`, {
        status: error.response.status,
        data: error.response.data,
        headers: error.response.headers
      })
    } else if (error?.request) {
      console.error(`${label} -> NO RESPONSE`, error.request)
    } else {
      console.error(`${label} -> ERROR`, error?.message || error)
    }
  }

  const logRequest = (url, method, data) => {
    console.log(`Request made to: ${url} with method: ${method}`)
    console.log('Request data (raw): ', data)
  }

  const logResponse = (url, status, responseData) => {
    console.log(`Response from: ${url} with status: ${status}`)
    console.log('Response data: ', responseData)
  }

  const normalizeArray = (payload) =>
    Array.isArray(payload) ? payload : (payload?.data ?? [])

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchEmpresas()
      fetchInactivas()
      fetchPersonas()
      fetchLogos()
    }).catch(err => logAxiosError(err, 'CSRF'))
  }, [])

  const fetchEmpresas = async () => {
    try {
      logRequest('/api/empresas', 'GET', {})
      const resEmpresas = await axios.get('/api/empresas')
      logResponse('/api/empresas', resEmpresas.status, resEmpresas.data)
      const listaEmpresas = normalizeArray(resEmpresas.data)
      setEmpresas(listaEmpresas)
    } catch (error) {
      logAxiosError(error, 'fetchEmpresas')
      setEmpresas([])
    }
  }

  const fetchInactivas = async () => {
    try {
      logRequest('/api/empresas-inactivas', 'GET', {})
      const res = await axios.get('/api/empresas-inactivas')
      logResponse('/api/empresas-inactivas', res.status, res.data)
      const lista = normalizeArray(res.data)
      setInactivas(lista)
    } catch (error) {
      logAxiosError(error, 'fetchInactivas')
      setInactivas([])
    }
  }

  const fetchPersonas = async () => {
    try {
      logRequest('/api/personas', 'GET', {})
      const res = await axios.get('/api/personas')
      logResponse('/api/personas', res.status, res.data)
      const lista = normalizeArray(res.data)
      setPersonas(lista)
    } catch (error) {
      logAxiosError(error, 'fetchPersonas')
      setPersonas([])
    }
  }

  const fetchLogos = async () => {
    try {
      logRequest('/api/logos', 'GET', {})
      const res = await axios.get('/api/logos')
      logResponse('/api/logos', res.status, res.data)
      const lista = normalizeArray(res.data)
      setLogos(lista)
    } catch (error) {
      logAxiosError(error, 'fetchLogos')
      setLogos([])
    }
  }

  // Helpers
  const personaLabel = (p) =>
    `${p.nombres ?? ''} ${p.apellido_paterno ?? ''} ${p.apellido_materno ?? ''} — CI ${p.ci ?? ''}`
      .replace(/\s+/g, ' ')
      .trim()

  const getEmpresaLogoRecord = (empresaId) =>
    logos.find(l => l.tipo_entidad === 'empresa' && Number(l.id_entidad) === Number(empresaId))

  const getEmpresaLogoSrc = (empresaId) => {
    const rec = getEmpresaLogoRecord(empresaId)
    return rec ? `data:image/png;base64,${rec.logo}` : null
  }

  const cambiarEstado = async (id, estado) => {
    try {
      logRequest(`/api/empresas/${id}/reactivar`, 'PATCH', { estado })
      const res = await axios.patch(`/api/empresas/${id}/reactivar`, { estado })
      logResponse(`/api/empresas/${id}/reactivar`, res.status, res.data)
      await fetchEmpresas()
      await fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'cambiarEstado')
    }
  }

  const handleInputChange = e => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  const handleFileChange = e => {
    const file = e.target.files && e.target.files[0] ? e.target.files[0] : null
    setFormData(prev => ({ ...prev, logo: file }))
    console.log('Logo file selected:', file)
  }

  const appendSanitizedFields = (fd, raw) => {
    const payload = { ...raw }

    if (payload.id_representante_legal !== '' && payload.id_representante_legal != null) {
      const parsed = Number.parseInt(payload.id_representante_legal, 10)
      if (!Number.isNaN(parsed)) {
        payload.id_representante_legal = parsed
      } else {
        delete payload.id_representante_legal
      }
    } else {
      delete payload.id_representante_legal
    }

    for (const [key, value] of Object.entries(payload)) {
      if (key === 'logo') continue
      fd.append(key, value)
    }

    console.log('FormData entries to be sent:')
    for (const [k, v] of fd.entries()) console.log('  ', k, '=>', v)
  }

  // --- LOGO CRUD helpers ---
  const createLogoForEmpresa = async (empresaId, file) => {
    const fd = new FormData()
    fd.append('logo', file)
    fd.append('id_entidad', empresaId)
    fd.append('tipo_entidad', 'empresa')

    logRequest('/api/logos', 'POST', fd)
    const res = await axios.post('/api/logos', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    logResponse('/api/logos', res.status, res.data)
  }

  const updateLogoForEmpresa = async (logoId, file, empresaId) => {
    const fd = new FormData()
    fd.append('logo', file) // el controlador acepta logo opcional
    // opcionalmente reafirmamos id_entidad/tipo_entidad
    fd.append('id_entidad', empresaId)
    fd.append('tipo_entidad', 'empresa')

    logRequest(`/api/logos/${logoId}`, 'PUT', fd)
    const res = await axios.post( // usar POST + _method=PUT para multipart seguro
      `/api/logos/${logoId}`,
      (() => { const tmp = new FormData(); for (const [k,v] of fd.entries()) tmp.append(k,v); tmp.append('_method','PUT'); return tmp })(),
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    logResponse(`/api/logos/${logoId}`, res.status, res.data)
  }

  // --- CREATE empresa + logo (si hay) ---
  const crearEmpresa = async () => {
    setLoading(true)
    try {
      const formDataWithFile = new FormData()
      appendSanitizedFields(formDataWithFile, formData)

      logRequest('/api/empresas', 'POST', formDataWithFile)
      const res = await axios.post('/api/empresas', formDataWithFile, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      logResponse('/api/empresas', res.status, res.data)

      const empresaCreada = res.data?.empresa ?? res.data
      const empresaId = empresaCreada?.id

      // Si se subió logo en el formulario, crear el logo para esta empresa
      if (empresaId && formData.logo instanceof File) {
        await createLogoForEmpresa(empresaId, formData.logo)
      }

      await fetchEmpresas()
      await fetchLogos()
      setFormVisible(false)
      setEmpresaEditando(null)
      console.log('Empresa creada exitosamente.')
    } catch (error) {
      logAxiosError(error, 'crearEmpresa')
    } finally {
      setLoading(false)
    }
  }

  // --- UPDATE empresa (JSON) + logo (si cambió archivo) ---
  const actualizarEmpresa = async () => {
    if (!empresaEditando) return
    setLoading(true)

    try {
      const payload = { ...formData }
      if (payload.id_representante_legal !== '' && payload.id_representante_legal != null) {
        const parsed = Number.parseInt(payload.id_representante_legal, 10)
        if (!Number.isNaN(parsed)) payload.id_representante_legal = parsed
        else delete payload.id_representante_legal
      } else {
        delete payload.id_representante_legal
      }
      delete payload.logo // logo se maneja aparte

      logRequest(`/api/empresas/${empresaEditando}`, 'PUT', payload)
      const res = await axios.put(
        `/api/empresas/${empresaEditando}`,
        payload,
        { headers: { 'Content-Type': 'application/json' } }
      )
      logResponse(`/api/empresas/${empresaEditando}`, res.status, res.data)

      // Si el usuario seleccionó un nuevo archivo de logo, actualizamos/creamos el logo
      if (formData.logo instanceof File) {
        const existing = getEmpresaLogoRecord(empresaEditando)
        if (existing) {
          await updateLogoForEmpresa(existing.id, formData.logo, empresaEditando)
        } else {
          await createLogoForEmpresa(empresaEditando, formData.logo)
        }
      }

      await fetchEmpresas()
      await fetchLogos()
      setFormVisible(false)
      setEmpresaEditando(null)
      console.log('Empresa actualizada exitosamente.')
    } catch (error) {
      logAxiosError(error, 'actualizarEmpresa')
    } finally {
      setLoading(false)
    }
  }

  const eliminarEmpresa = async id => {
    if (!window.confirm('¿Estás seguro de que quieres desactivar esta empresa?')) return
    try {
      logRequest(`/api/empresas/${id}`, 'DELETE', {})
      const res = await axios.delete(`/api/empresas/${id}`)
      logResponse(`/api/empresas/${id}`, res.status, res.data)
      await fetchEmpresas()
      await fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'eliminarEmpresa')
    }
  }

  const reactivarEmpresa = async id => {
    if (!window.confirm('¿Deseas reactivar esta empresa?')) return
    try {
      logRequest(`/api/empresas/${id}/reactivar`, 'PATCH', { estado: true })
      const res = await axios.patch(`/api/empresas/${id}/reactivar`)
      logResponse(`/api/empresas/${id}/reactivar`, res.status, res.data)
      await fetchEmpresas()
      await fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'reactivarEmpresa')
    }
  }

  const iniciarEdicion = empresa => {
    setFormVisible(true)
    setEmpresaEditando(empresa.id)
    setFormData({
      razon_social: empresa.razon_social ?? '',
      nombre_comercial: empresa.nombre_comercial ?? '',
      nit: empresa.nit ?? '',
      matricula_comercio: empresa.matricula_comercio ?? '',
      direccion_fiscal: empresa.direccion_fiscal ?? '',
      telefono: empresa.telefono ?? '',
      email: empresa.email ?? '',
      municipio: empresa.municipio ?? '',
      departamento: empresa.departamento ?? '',
      id_representante_legal: empresa.id_representante_legal != null ? String(empresa.id_representante_legal) : '',
      logo: null // en edición, solo seteamos File si el usuario elige uno
    })
    console.log('Editando empresa:', empresa)
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setEmpresaEditando(null)
    setFormData({
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
  }

  const empresasMostradas = mostrarInactivas ? inactivas : empresas

  return (
    <div className="empresas-container">
      <button className="btn-crear-empresa" onClick={() => setFormVisible(true)}>
        Crear Empresa
      </button>

      <h2 className="empresas-title">Empresas</h2>
      <div className="toggle-container">
        <button
          className={`toggle-btn ${!mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(false)}
        >
          Activas
        </button>
        <button
          className={`toggle-btn ${mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(true)}
        >
          Inactivas
        </button>
      </div>

      <div className="empresas-cards">
        {empresasMostradas.map(empresa => {
          const rep = personas.find(p => p.id === empresa.id_representante_legal)
          const logoSrc = getEmpresaLogoSrc(empresa.id)
          return (
            <div className="empresa-card" key={empresa.id}>
              {logoSrc && <img className="empresa-logo" src={logoSrc} alt="Logo" />}
              <h3>{empresa.razon_social}</h3>
              <p>{empresa.nombre_comercial}</p>
              <p>{empresa.nit}</p>
              <p>{empresa.direccion_fiscal}</p>
              <p>{empresa.telefono}</p>
              <p>{empresa.email}</p>
              <p><strong>Representante:</strong> {rep ? personaLabel(rep) : '—'}</p>

              {empresa.estado === true ? (
                <>
                  <button onClick={() => iniciarEdicion(empresa)}>Editar</button>
                  <button onClick={() => eliminarEmpresa(empresa.id)}>Desactivar</button>
                </>
              ) : (
                <button onClick={() => reactivarEmpresa(empresa.id)}>Reactivar</button>
              )}
            </div>
          )
        })}
      </div>

      {formVisible && (
        <div className="form-overlay">
          <div className="form-container">
            <label>Razón Social</label>
            <input type="text" name="razon_social" value={formData.razon_social} onChange={handleInputChange} />

            <label>Nombre Comercial</label>
            <input type="text" name="nombre_comercial" value={formData.nombre_comercial} onChange={handleInputChange} />

            <label>NIT</label>
            <input type="text" name="nit" value={formData.nit} onChange={handleInputChange} />

            <label>Matrícula Comercio</label>
            <input type="text" name="matricula_comercio" value={formData.matricula_comercio} onChange={handleInputChange} />

            <label>Dirección Fiscal</label>
            <input type="text" name="direccion_fiscal" value={formData.direccion_fiscal} onChange={handleInputChange} />

            <label>Teléfono</label>
            <input type="text" name="telefono" value={formData.telefono} onChange={handleInputChange} />

            <label>Correo</label>
            <input type="email" name="email" value={formData.email} onChange={handleInputChange} />

            <label>Municipio</label>
            <input type="text" name="municipio" value={formData.municipio} onChange={handleInputChange} />

            <label>Departamento</label>
            <input type="text" name="departamento" value={formData.departamento} onChange={handleInputChange} />

            <label>Representante Legal</label>
            <select
              name="id_representante_legal"
              value={formData.id_representante_legal}
              onChange={handleInputChange}
            >
              <option value="">Sin representante</option>
              {personas.map(p => (
                <option key={p.id} value={p.id}>
                  {personaLabel(p)}
                </option>
              ))}
            </select>

            <label>Logo (PNG/JPG)</label>
            <input type="file" accept="image/*" onChange={handleFileChange} />

            <div className="form-actions">
              <button onClick={empresaEditando ? actualizarEmpresa : crearEmpresa}>
                {loading ? 'Cargando...' : (empresaEditando ? 'Actualizar Empresa' : 'Crear Empresa')}
              </button>
              <button onClick={resetFormulario}>Cancelar</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Empresas
