import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Empresas.css'

function Empresas() {
  const [empresas, setEmpresas] = useState([])
  const [inactivas, setInactivas] = useState([])
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
    id_representante_legal: '', // ID del representante legal
    logo: null // Logo
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
    console.log(`Request made to: ${url} with method: ${method}`);
    console.log("Request data: ", data);
  }

  const logResponse = (url, status, responseData) => {
    console.log(`Response from: ${url} with status: ${status}`);
    console.log("Response data: ", responseData);
  }

  const normalizeArray = (payload) =>
    Array.isArray(payload) ? payload : (payload?.data ?? [])

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchEmpresas()
      fetchInactivas()
    }).catch(err => logAxiosError(err, 'CSRF'))
  }, [])

  const fetchEmpresas = async () => {
    try {
      logRequest('/api/empresas', 'GET', {})
      const res = await axios.get('/api/empresas')
      logResponse('/api/empresas', res.status, res.data)
      const lista = normalizeArray(res.data)
      setEmpresas(lista)
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

  const cambiarEstado = async (id, estado) => {
    try {
      const res = await axios.patch(`/api/empresas/${id}/reactivar`, { estado })
      logResponse(`/api/empresas/${id}/reactivar`, res.status, res.data)
      fetchEmpresas()
      fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'cambiarEstado')
    }
  }

  const handleInputChange = e => {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  const handleFileChange = e => {
    setFormData({ ...formData, logo: e.target.files[0] })
  }

  const crearEmpresa = async () => {
    setLoading(true)
    const formDataWithFile = new FormData()
    for (const [key, value] of Object.entries(formData)) {
      formDataWithFile.append(key, value)
    }

    logRequest('/api/empresas', 'POST', formData)
    try {
      const res = await axios.post('/api/empresas', formDataWithFile, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      logResponse('/api/empresas', res.status, res.data)
      fetchEmpresas()
    } catch (error) {
      logAxiosError(error, 'crearEmpresa')
    } finally {
      setLoading(false)
    }
  }

  const actualizarEmpresa = async () => {
    if (!empresaEditando) return
    setLoading(true)
    const formDataWithFile = new FormData()
    for (const [key, value] of Object.entries(formData)) {
      formDataWithFile.append(key, value)
    }

    logRequest(`/api/empresas/${empresaEditando}`, 'PUT', formData)
    try {
      const res = await axios.put(`/api/empresas/${empresaEditando}`, formDataWithFile, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      logResponse(`/api/empresas/${empresaEditando}`, res.status, res.data)
      fetchEmpresas()
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
      fetchEmpresas()
      fetchInactivas()
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
      fetchEmpresas()
      fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'reactivarEmpresa')
    }
  }

  const iniciarEdicion = empresa => {
    setFormVisible(true)
    setEmpresaEditando(empresa.id)
    setFormData({
      razon_social: empresa.razon_social,
      nombre_comercial: empresa.nombre_comercial,
      nit: empresa.nit,
      matricula_comercio: empresa.matricula_comercio,
      direccion_fiscal: empresa.direccion_fiscal,
      telefono: empresa.telefono,
      email: empresa.email,
      municipio: empresa.municipio,
      departamento: empresa.departamento,
      id_representante_legal: empresa.id_representante_legal, // Asignar representante legal
    })
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
      <h2 className="empresas-title">Empresas</h2>
      <div className="toggle-container">
        <button className={`toggle-btn ${!mostrarInactivas ? 'active' : ''}`} onClick={() => setMostrarInactivas(false)}>Activas</button>
        <button className={`toggle-btn ${mostrarInactivas ? 'active' : ''}`} onClick={() => setMostrarInactivas(true)}>Inactivas</button>
      </div>

      <div className="empresas-cards">
        {empresasMostradas.map(empresa => (
          <div className="empresa-card" key={empresa.id}>
            {empresa.logo && <img className="empresa-logo" src={`data:image/png;base64,${empresa.logo}`} alt="Logo" />}
            <h3>{empresa.razon_social}</h3>
            <p>{empresa.nombre_comercial}</p>
            <p>{empresa.nit}</p>
            <p>{empresa.direccion_fiscal}</p>
            <p>{empresa.telefono}</p>
            <p>{empresa.email}</p>
            {empresa.estado === true ? (
              <>
                <button onClick={() => iniciarEdicion(empresa)}>Editar</button>
                <button onClick={() => eliminarEmpresa(empresa.id)}>Desactivar</button>
              </>
            ) : (
              <button onClick={() => reactivarEmpresa(empresa.id)}>Reactivar</button>
            )}
          </div>
        ))}
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
            <input type="text" name="id_representante_legal" value={formData.id_representante_legal} onChange={handleInputChange} />
            <label>Logo</label>
            <input type="file" name="logo" onChange={handleFileChange} />
            <div className="form-actions">
              <button onClick={empresaEditando ? actualizarEmpresa : crearEmpresa}>
                {loading ? 'Cargando...' : 'Crear Empresa'}
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
