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
  const [accionesPermitidas, setAccionesPermitidas] = useState([])

  const [formData, setFormData] = useState({
    razon_social: '',
    nombre_comercial: '',
    nit: '',
    matricula_comercio: '',
    direccion_fiscal: '',
    telefono: '',
    email: '',
    municipio: '',
    departamento: ''
  })

  // ===== Helpers de depuración =====
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

  const normalizeArray = (payload) =>
    Array.isArray(payload) ? payload : (payload?.data ?? [])

  useEffect(() => {
    console.log('[Empresas] Componente montado')
    console.log('[Empresas] axios baseURL:', axios.defaults.baseURL)

    axios.get('/sanctum/csrf-cookie').then(() => {
      console.log('[Empresas] CSRF cookie OK')
      fetchEmpresas()
      fetchInactivas()
      fetchAccionesUsuario()
    }).catch(err => logAxiosError(err, 'CSRF'))
  }, [])

  // Logs cuando cambian los estados
  useEffect(() => {
    console.log('[Empresas] empresas (activas) en estado:', empresas)
  }, [empresas])

  useEffect(() => {
    console.log('[Empresas] empresas inactivas en estado:', inactivas)
  }, [inactivas])

  // ===== Acciones / permisos =====
  const fetchAccionesUsuario = async () => {
    try {
      const userRes = await axios.get('/api/user')
      console.log('[Empresas] GET /api/user:', userRes.status, userRes.data)
      const userId = userRes.data.id

      const accionesRes = await axios.get(`/api/acciones/${userId}`)
      console.log('[Empresas] GET /api/acciones/{id}:', accionesRes.status, accionesRes.data)

      const accionesFiltradas = accionesRes.data
        .filter(a => a.menu_item === 'Empresas')
        .map(a => a.accion)

      console.log('[Empresas] Acciones permitidas:', accionesFiltradas)
      setAccionesPermitidas(accionesFiltradas)
    } catch (error) {
      logAxiosError(error, 'fetchAccionesUsuario')
    }
  }

  const puede = accion => accionesPermitidas.includes(accion)

  // ===== Fetch activos / inactivos (con logs) =====
  const fetchEmpresas = async () => {
    try {
      const res = await axios.get('/api/empresas')
      console.log('[Empresas] GET /api/empresas -> status:', res.status)
      console.log('[Empresas] payload bruto:', res.data)

      const lista = normalizeArray(res.data)
      console.log('[Empresas] normalizado (activas):', lista)
      setEmpresas(lista)
    } catch (error) {
      logAxiosError(error, 'fetchEmpresas')
      setEmpresas([])
    }
  }

  const fetchInactivas = async () => {
    try {
      const res = await axios.get('/api/empresas-inactivas')
      console.log('[Empresas] GET /api/empresas-inactivas -> status:', res.status)
      console.log('[Empresas] payload bruto (inactivas):', res.data)

      const lista = normalizeArray(res.data)
      console.log('[Empresas] normalizado (inactivas):', lista)
      setInactivas(lista)
    } catch (error) {
      logAxiosError(error, 'fetchInactivas')
      setInactivas([])
    }
  }

  // ===== CRUD (con logs) =====
  const handleInputChange = e => {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  const crearEmpresa = async () => {
    setLoading(true)
    console.log('[Empresas] POST /api/empresas -> body:', { ...formData, estado: 1 })
    try {
      const res = await axios.post('/api/empresas', { ...formData, estado: 1 })
      console.log('[Empresas] Respuesta crear:', res.status, res.data)
      resetFormulario()
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
    console.log('[Empresas] PUT /api/empresas/' + empresaEditando, '-> body:', formData)
    try {
      const res = await axios.put(`/api/empresas/${empresaEditando}`, formData)
      console.log('[Empresas] Respuesta actualizar:', res.status, res.data)
      resetFormulario()
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
      console.log('[Empresas] DELETE /api/empresas/' + id)
      const res = await axios.delete(`/api/empresas/${id}`)
      console.log('[Empresas] Respuesta eliminar:', res.status, res.data)
      fetchEmpresas()
      fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'eliminarEmpresa')
    }
  }

  const reactivarEmpresa = async id => {
    if (!window.confirm('¿Deseas reactivar esta empresa?')) return
    try {
      console.log('[Empresas] PATCH /api/empresas/' + id + '/reactivar')
      const res = await axios.patch(`/api/empresas/${id}/reactivar`)
      console.log('[Empresas] Respuesta reactivar:', res.status, res.data)
      fetchEmpresas()
      fetchInactivas()
    } catch (error) {
      logAxiosError(error, 'reactivarEmpresa')
    }
  }

  const iniciarEdicion = empresa => {
    console.log('[Empresas] Editando empresa:', empresa)
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
      departamento: empresa.departamento
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
      departamento: ''
    })
  }

  const empresasMostradas = mostrarInactivas ? inactivas : empresas

  return (
    <div className="empresas-container">
      <h2 className="empresas-title">Empresas</h2>

      <div className="toggle-container">
        <button
          className={`toggle-btn ${!mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(false)}
        >
          Activas ({empresas.length})
        </button>
        <button
          className={`toggle-btn ${mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(true)}
        >
          Inactivas ({inactivas.length})
        </button>
      </div>

      <table className="empresas-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Razón Social</th>
            <th>NIT</th>
            <th>Dirección Fiscal</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {empresasMostradas.map(e => (
            <tr key={e.id}>
              <td>{e.id}</td>
              <td>{e.razon_social}</td>
              <td>{e.nit}</td>
              <td>{e.direccion_fiscal}</td>
              <td>{e.telefono}</td>
              <td>
                <span className={`status ${e.estado ? 'active' : 'inactive'}`}>
                  {e.estado ? 'Activo' : 'Inactivo'}
                </span>
              </td>
              <td>
                {mostrarInactivas ? (
                  puede('reactivar') && (
                    <button className="reactivate-btn" onClick={() => reactivarEmpresa(e.id)}>
                      Reactivar
                    </button>
                  )
                ) : (
                  <>
                    {puede('editar') && (
                      <button className="edit-btn" onClick={() => iniciarEdicion(e)}>
                        Editar
                      </button>
                    )}
                    {puede('eliminar') && (
                      <button className="delete-btn" onClick={() => eliminarEmpresa(e.id)}>
                        Desactivar
                      </button>
                    )}
                  </>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {!mostrarInactivas && !formVisible && puede('crear') ? (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Empresa
        </button>
      ) : !mostrarInactivas && formVisible ? (
        <div className="form-container">
          <label className="form-label">Razón Social</label>
          <input className="form-input" name="razon_social" value={formData.razon_social} onChange={handleInputChange} />
          <label className="form-label">Nombre Comercial</label>
          <input className="form-input" name="nombre_comercial" value={formData.nombre_comercial} onChange={handleInputChange} />
          <label className="form-label">NIT</label>
          <input className="form-input" name="nit" value={formData.nit} onChange={handleInputChange} />
          <label className="form-label">Matrícula Comercio</label>
          <input className="form-input" name="matricula_comercio" value={formData.matricula_comercio} onChange={handleInputChange} />
          <label className="form-label">Dirección Fiscal</label>
          <input className="form-input" name="direccion_fiscal" value={formData.direccion_fiscal} onChange={handleInputChange} />
          <label className="form-label">Teléfono</label>
          <input className="form-input" name="telefono" value={formData.telefono} onChange={handleInputChange} />
          <label className="form-label">Correo</label>
          <input className="form-input" type="email" name="email" value={formData.email} onChange={handleInputChange} />
          <label className="form-label">Municipio</label>
          <input className="form-input" name="municipio" value={formData.municipio} onChange={handleInputChange} />
          <label className="form-label">Departamento</label>
          <input className="form-input" name="departamento" value={formData.departamento} onChange={handleInputChange} />

          <div className="form-actions">
            <button
              className="create-btn"
              onClick={empresaEditando ? actualizarEmpresa : crearEmpresa}
              disabled={loading}
            >
              {loading ? (empresaEditando ? 'Actualizando...' : 'Creando...') : (empresaEditando ? 'Actualizar' : 'Crear Empresa')}
            </button>
            <button className="cancel-btn" onClick={resetFormulario}>
              Cancelar
            </button>
          </div>
        </div>
      ) : null}
    </div>
  )
}

export default Empresas
