import React, { useEffect, useMemo, useState } from 'react'
import axios from '../lib/axios'
import './Empresas.css' // reutilizo estilos; cambia si tienes Sucursales.css

function Sucursales() {
  const [sucursales, setSucursales] = useState([])
  const [inactivas, setInactivas] = useState([])
  const [empresas, setEmpresas] = useState([])
  const [personas, setPersonas] = useState([])
  const [logos, setLogos] = useState([])

  const [formVisible, setFormVisible] = useState(false)
  const [sucursalEditando, setSucursalEditando] = useState(null)
  const [mostrarInactivas, setMostrarInactivas] = useState(false)
  const [loading, setLoading] = useState(false)

  const [formData, setFormData] = useState({
    id_empresa: '',
    nombre: '',
    codigo_sucursal: '',
    direccion: '',
    telefono: '',
    email: '',
    id_sucursal_padre: '',
    id_representante_legal: '',
    logo: null // File | null (logo de la sucursal)
  })

  // --- utils logs
  const logAxiosError = (error, label) => {
    const status = error?.response?.status
    const data = error?.response?.data
    console.error(`${label} -> RESPONSE ERROR`, { status, data })
  }
  const logRequest = (url, method, data) => {
    console.log(`Request made to: ${url} with method: ${method}`, data)
  }
  const logResponse = (url, status, responseData) => {
    console.log(`Response from: ${url} with status: ${status}`, responseData)
  }

  const normalizeArray = (payload) =>
    Array.isArray(payload) ? payload : (payload?.data ?? [])

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchSucursales()
      fetchInactivas()
      fetchEmpresas()
      fetchPersonas()
      fetchLogos()
    }).catch(e => logAxiosError(e, 'CSRF'))
  }, [])

  // --- fetchers
  const fetchSucursales = async () => {
    try {
      logRequest('/api/sucursales', 'GET', {})
      const res = await axios.get('/api/sucursales')
      logResponse('/api/sucursales', res.status, res.data)
      setSucursales(normalizeArray(res.data))
    } catch (e) {
      logAxiosError(e, 'fetchSucursales')
      setSucursales([])
    }
  }

  const fetchInactivas = async () => {
    try {
      logRequest('/api/sucursales-inactivas', 'GET', {})
      const res = await axios.get('/api/sucursales-inactivas')
      logResponse('/api/sucursales-inactivas', res.status, res.data)
      setInactivas(normalizeArray(res.data))
    } catch (e) {
      logAxiosError(e, 'fetchInactivas')
      setInactivas([])
    }
  }

  const fetchEmpresas = async () => {
    try {
      logRequest('/api/empresas', 'GET', {})
      const res = await axios.get('/api/empresas')
      logResponse('/api/empresas', res.status, res.data)
      setEmpresas(normalizeArray(res.data))
    } catch (e) {
      logAxiosError(e, 'fetchEmpresas')
      setEmpresas([])
    }
  }

  const fetchPersonas = async () => {
    try {
      logRequest('/api/personas', 'GET', {})
      const res = await axios.get('/api/personas')
      logResponse('/api/personas', res.status, res.data)
      setPersonas(normalizeArray(res.data))
    } catch (e) {
      logAxiosError(e, 'fetchPersonas')
      setPersonas([])
    }
  }

  const fetchLogos = async () => {
    try {
      logRequest('/api/logos', 'GET', {})
      const res = await axios.get('/api/logos')
      logResponse('/api/logos', res.status, res.data)
      setLogos(normalizeArray(res.data))
    } catch (e) {
      logAxiosError(e, 'fetchLogos')
      setLogos([])
    }
  }

  // --- helpers de etiquetas
  const personaLabel = (p) =>
    `${p.nombres ?? ''} ${p.apellido_paterno ?? ''} ${p.apellido_materno ?? ''} — CI ${p.ci ?? ''}`.replace(/\s+/g,' ').trim()

  const empresaLabel = (e) =>
    `${e.razon_social ?? ''} (${e.nit ?? '-'})`.replace(/\s+/g,' ').trim()

  const sucursalLabel = (s) =>
    `${s.nombre ?? ''} ${s.codigo_sucursal ? `— Código ${s.codigo_sucursal}` : ''}`.trim()

  // --- logo helpers
  const getSucursalLogoRecord = (sucursalId) =>
    logos.find(l => l.tipo_entidad === 'sucursal' && Number(l.id_entidad) === Number(sucursalId))

  const getSucursalLogoSrc = (sucursalId) => {
    const rec = getSucursalLogoRecord(sucursalId)
    return rec ? `data:image/png;base64,${rec.logo}` : null
  }

  // --- form change
  const handleInputChange = e => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }
  const handleFileChange = e => {
    const file = e.target.files?.[0] ?? null
    setFormData(prev => ({ ...prev, logo: file }))
  }

  // --- logo calls
  const createLogoForSucursal = async (sucursalId, file) => {
    const fd = new FormData()
    fd.append('logo', file)
    fd.append('id_entidad', sucursalId)
    fd.append('tipo_entidad', 'sucursal')
    logRequest('/api/logos', 'POST', fd)
    const res = await axios.post('/api/logos', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    logResponse('/api/logos', res.status, res.data)
  }

  const updateLogoForSucursal = async (logoId, file, sucursalId) => {
    const fd = new FormData()
    fd.append('logo', file)
    fd.append('id_entidad', sucursalId)
    fd.append('tipo_entidad', 'sucursal')
    const spoof = new FormData()
    for (const [k,v] of fd.entries()) spoof.append(k,v)
    spoof.append('_method', 'PUT')
    logRequest(`/api/logos/${logoId}`, 'PUT (spoofed via POST)', spoof)
    const res = await axios.post(`/api/logos/${logoId}`, spoof, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    logResponse(`/api/logos/${logoId}`, res.status, res.data)
  }

  // --- CRUD sucursal
  const sanitizarPayload = (raw) => {
    const p = { ...raw }

    // enteros
    const ints = ['id_empresa', 'id_sucursal_padre', 'id_representante_legal']
    ints.forEach(k => {
      if (p[k] === '' || p[k] == null) delete p[k]
      else {
        const n = Number.parseInt(p[k], 10)
        if (Number.isNaN(n)) delete p[k]; else p[k] = n
      }
    })

    delete p.logo // logo se maneja aparte
    return p
  }

  const crearSucursal = async () => {
    setLoading(true)
    try {
      const payload = sanitizarPayload(formData)
      logRequest('/api/sucursales', 'POST', payload)
      const res = await axios.post('/api/sucursales', payload, {
        headers: { 'Content-Type': 'application/json' }
      })
      logResponse('/api/sucursales', res.status, res.data)

      const suc = res.data?.sucursal ?? res.data
      const sucursalId = suc?.id

      if (sucursalId && formData.logo instanceof File) {
        await createLogoForSucursal(sucursalId, formData.logo)
      }

      await Promise.all([fetchSucursales(), fetchLogos()])
      setFormVisible(false)
      setSucursalEditando(null)
    } catch (e) {
      logAxiosError(e, 'crearSucursal')
    } finally {
      setLoading(false)
    }
  }

  const actualizarSucursal = async () => {
    if (!sucursalEditando) return
    setLoading(true)
    try {
      const payload = sanitizarPayload(formData)
      logRequest(`/api/sucursales/${sucursalEditando}`, 'PUT', payload)
      const res = await axios.put(`/api/sucursales/${sucursalEditando}`, payload, {
        headers: { 'Content-Type': 'application/json' }
      })
      logResponse(`/api/sucursales/${sucursalEditando}`, res.status, res.data)

      if (formData.logo instanceof File) {
        const existing = getSucursalLogoRecord(sucursalEditando)
        if (existing) {
          await updateLogoForSucursal(existing.id, formData.logo, sucursalEditando)
        } else {
          await createLogoForSucursal(sucursalEditando, formData.logo)
        }
      }

      await Promise.all([fetchSucursales(), fetchLogos()])
      setFormVisible(false)
      setSucursalEditando(null)
    } catch (e) {
      logAxiosError(e, 'actualizarSucursal')
    } finally {
      setLoading(false)
    }
  }

  const eliminarSucursal = async id => {
    if (!window.confirm('¿Desactivar esta sucursal?')) return
    try {
      logRequest(`/api/sucursales/${id}`, 'DELETE', {})
      const res = await axios.delete(`/api/sucursales/${id}`)
      logResponse(`/api/sucursales/${id}`, res.status, res.data)
      await Promise.all([fetchSucursales(), fetchInactivas()])
    } catch (e) {
      logAxiosError(e, 'eliminarSucursal')
    }
  }

  const reactivarSucursal = async id => {
    if (!window.confirm('¿Reactivar esta sucursal?')) return
    try {
      logRequest(`/api/sucursales/${id}/reactivar`, 'PATCH', {})
      const res = await axios.patch(`/api/sucursales/${id}/reactivar`)
      logResponse(`/api/sucursales/${id}/reactivar`, res.status, res.data)
      await Promise.all([fetchSucursales(), fetchInactivas()])
    } catch (e) {
      logAxiosError(e, 'reactivarSucursal')
    }
  }

  const iniciarEdicion = suc => {
    setFormVisible(true)
    setSucursalEditando(suc.id)
    setFormData({
      id_empresa: suc.id_empresa != null ? String(suc.id_empresa) : '',
      nombre: suc.nombre ?? '',
      codigo_sucursal: suc.codigo_sucursal ?? '',
      direccion: suc.direccion ?? '',
      telefono: suc.telefono ?? '',
      email: suc.email ?? '',
      id_sucursal_padre: suc.id_sucursal_padre != null ? String(suc.id_sucursal_padre) : '',
      id_representante_legal: suc.id_representante_legal != null ? String(suc.id_representante_legal) : '',
      logo: null // solo seteamos File si el usuario elige
    })
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setSucursalEditando(null)
    setFormData({
      id_empresa: '',
      nombre: '',
      codigo_sucursal: '',
      direccion: '',
      telefono: '',
      email: '',
      id_sucursal_padre: '',
      id_representante_legal: '',
      logo: null
    })
  }

  const listaSucursalesMostradas = mostrarInactivas ? inactivas : sucursales

  // Sucursales disponibles como posibles padres (opcionalmente puedes filtrar por empresa seleccionada)
  const posiblesPadres = useMemo(() => {
    const base = formData.id_empresa
      ? sucursales.filter(s => String(s.id_empresa) === String(formData.id_empresa))
      : sucursales
    return base.filter(s => s.id !== sucursalEditando)
  }, [sucursales, formData.id_empresa, sucursalEditando])

  return (
    <div className="empresas-container">
      <button className="btn-crear-empresa" onClick={() => setFormVisible(true)}>
        Crear Sucursal
      </button>

      <h2 className="empresas-title">Sucursales</h2>

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
        {listaSucursalesMostradas.map(suc => {
          const logoSrc = getSucursalLogoSrc(suc.id)
          const empresa = empresas.find(e => e.id === suc.id_empresa)
          const rep = personas.find(p => p.id === suc.id_representante_legal)
          const padre = sucursales.find(s => s.id === suc.id_sucursal_padre)

          return (
            <div className="empresa-card" key={suc.id}>
              {logoSrc && <img className="empresa-logo" src={logoSrc} alt="Logo" />}
              <h3>{suc.nombre}</h3>
              <p><strong>Empresa:</strong> {empresa ? empresaLabel(empresa) : '—'}</p>
              <p><strong>Código:</strong> {suc.codigo_sucursal || '—'}</p>
              <p><strong>Dirección:</strong> {suc.direccion}</p>
              <p><strong>Teléfono:</strong> {suc.telefono || '—'}</p>
              <p><strong>Email:</strong> {suc.email || '—'}</p>
              <p><strong>Representante:</strong> {rep ? personaLabel(rep) : '—'}</p>
              <p><strong>Padre:</strong> {padre ? sucursalLabel(padre) : '—'}</p>

              {suc.estado === true ? (
                <>
                  <button onClick={() => iniciarEdicion(suc)}>Editar</button>
                  <button onClick={() => eliminarSucursal(suc.id)}>Desactivar</button>
                </>
              ) : (
                <button onClick={() => reactivarSucursal(suc.id)}>Reactivar</button>
              )}
            </div>
          )
        })}
      </div>

      {formVisible && (
        <div className="form-overlay">
          <div className="form-container">
            <label>Empresa</label>
            <select
              name="id_empresa"
              value={formData.id_empresa}
              onChange={handleInputChange}
            >
              <option value="">Seleccione empresa</option>
              {empresas.map(e => (
                <option key={e.id} value={e.id}>
                  {empresaLabel(e)}
                </option>
              ))}
            </select>

            <label>Nombre</label>
            <input type="text" name="nombre" value={formData.nombre} onChange={handleInputChange} />

            <label>Código de sucursal</label>
            <input type="text" name="codigo_sucursal" value={formData.codigo_sucursal} onChange={handleInputChange} />

            <label>Dirección</label>
            <input type="text" name="direccion" value={formData.direccion} onChange={handleInputChange} />

            <label>Teléfono</label>
            <input type="text" name="telefono" value={formData.telefono} onChange={handleInputChange} />

            <label>Email</label>
            <input type="email" name="email" value={formData.email} onChange={handleInputChange} />

            <label>Sucursal padre</label>
            <select
              name="id_sucursal_padre"
              value={formData.id_sucursal_padre}
              onChange={handleInputChange}
            >
              <option value="">Sin padre</option>
              {posiblesPadres.map(s => (
                <option key={s.id} value={s.id}>
                  {sucursalLabel(s)}
                </option>
              ))}
            </select>

            <label>Representante legal</label>
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
              <button onClick={sucursalEditando ? actualizarSucursal : crearSucursal}>
                {loading ? 'Cargando...' : (sucursalEditando ? 'Actualizar Sucursal' : 'Crear Sucursal')}
              </button>
              <button onClick={resetFormulario}>Cancelar</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Sucursales
