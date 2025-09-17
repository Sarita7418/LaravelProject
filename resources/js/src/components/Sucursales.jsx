import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Sucursales.css'

function Sucursales() {
  const [sucursales, setSucursales] = useState([])
  const [inactivas, setInactivas] = useState([])
  const [empresasOpt, setEmpresasOpt] = useState([])

  const [formVisible, setFormVisible] = useState(false)
  const [sucursalEditando, setSucursalEditando] = useState(null)
  const [mostrarInactivas, setMostrarInactivas] = useState(false)
  const [loading, setLoading] = useState(false)
  const [accionesPermitidas, setAccionesPermitidas] = useState([])

  const [formData, setFormData] = useState({
    id_empresa: '',
    nombre: '',
    codigo_sucursal: '',
    direccion: '',
    telefono: '',
    email: ''
  })

  // Helpers
  const normalizeArray = (payload) =>
    Array.isArray(payload) ? payload : (payload?.data ?? [])

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchSucursales()
      fetchInactivas()
      fetchAccionesUsuario()
      fetchEmpresasActivas()
    })
  }, [])

  const fetchAccionesUsuario = async () => {
    try {
      const userRes = await axios.get('/api/user')
      const userId = userRes.data.id
      const accionesRes = await axios.get(`/api/acciones/${userId}`)
      const accionesFiltradas = accionesRes.data
        .filter(a => a.menu_item === 'Sucursales')
        .map(a => a.accion)
      setAccionesPermitidas(accionesFiltradas)
    } catch (error) {
      console.error('Error al obtener las acciones del usuario:', error)
    }
  }

  const puede = (accion) => accionesPermitidas.includes(accion)

  // Empresas activas para el combo
  const fetchEmpresasActivas = async () => {
    try {
      const res = await axios.get('/api/empresas') // devuelve solo activas
      const lista = normalizeArray(res.data)
      setEmpresasOpt(lista)
    } catch (error) {
      console.error('Error al obtener empresas activas:', error)
      setEmpresasOpt([])
    }
  }

  // Sucursales
  const fetchSucursales = async () => {
    try {
      const res = await axios.get('/api/sucursales')
      const lista = normalizeArray(res.data)
      setSucursales(lista)
    } catch (error) {
      console.error('Error al obtener sucursales:', error)
      setSucursales([])
    }
  }

  const fetchInactivas = async () => {
    try {
      const res = await axios.get('/api/sucursales-inactivas')
      const lista = normalizeArray(res.data)
      setInactivas(lista)
    } catch (error) {
      console.error('Error al obtener sucursales inactivas:', error)
      setInactivas([])
    }
  }

  // CRUD
  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  const crearSucursal = async () => {
    setLoading(true)
    try {
      await axios.post('/api/sucursales', { ...formData, estado: 1 })
      resetFormulario()
      fetchSucursales()
    } catch (error) {
      console.error('Error al crear sucursal:', error)
    } finally {
      setLoading(false)
    }
  }

  const actualizarSucursal = async () => {
    if (!sucursalEditando) return
    setLoading(true)
    try {
      await axios.put(`/api/sucursales/${sucursalEditando}`, formData)
      resetFormulario()
      fetchSucursales()
    } catch (error) {
      console.error('Error al actualizar sucursal:', error)
    } finally {
      setLoading(false)
    }
  }

  const eliminarSucursal = async (id) => {
    if (!window.confirm('¿Estás seguro de que quieres desactivar esta sucursal?')) return
    try {
      await axios.delete(`/api/sucursales/${id}`)
      fetchSucursales()
      fetchInactivas()
    } catch (error) {
      console.error('Error al desactivar sucursal:', error)
    }
  }

  const reactivarSucursal = async (id) => {
    if (!window.confirm('¿Deseas reactivar esta sucursal?')) return
    try {
      await axios.patch(`/api/sucursales/${id}/reactivar`)
      fetchSucursales()
      fetchInactivas()
    } catch (error) {
      console.error('Error al reactivar sucursal:', error)
    }
  }

  const iniciarEdicion = (s) => {
    setFormVisible(true)
    setSucursalEditando(s.id)
    setFormData({
      id_empresa: String(s.id_empresa ?? ''), // asegurar string para el <select>
      nombre: s.nombre ?? '',
      codigo_sucursal: s.codigo_sucursal ?? '',
      direccion: s.direccion ?? '',
      telefono: s.telefono ?? '',
      email: s.email ?? ''
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
      email: ''
    })
  }

  const sucursalesMostradas = mostrarInactivas ? inactivas : sucursales

  return (
    <div className="sucursales-container">
      <h2 className="sucursales-title">Sucursales</h2>

      <div className="toggle-container">
        <button
          className={`toggle-btn ${!mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(false)}
        >
          Activas ({sucursales.length})
        </button>
        <button
          className={`toggle-btn ${mostrarInactivas ? 'active' : ''}`}
          onClick={() => setMostrarInactivas(true)}
        >
          Inactivas ({inactivas.length})
        </button>
      </div>

      <table className="sucursales-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Empresa</th>
            <th>Nombre</th>
            <th>Código</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {sucursalesMostradas.map(s => (
            <tr key={s.id}>
              <td>{s.id}</td>
              <td>
                {/* Muestra la razón social si la tenemos en opciones */}
                {empresasOpt.find(e => e.id === s.id_empresa)?.razon_social || s.id_empresa}
              </td>
              <td>{s.nombre}</td>
              <td>{s.codigo_sucursal}</td>
              <td>{s.direccion}</td>
              <td>{s.telefono}</td>
              <td>
                <span className={`status ${s.estado ? 'active' : 'inactive'}`}>
                  {s.estado ? 'Activo' : 'Inactivo'}
                </span>
              </td>
              <td>
                {mostrarInactivas ? (
                  puede('reactivar') && (
                    <button className="reactivate-btn" onClick={() => reactivarSucursal(s.id)}>
                      Reactivar
                    </button>
                  )
                ) : (
                  <>
                    {puede('editar') && (
                      <button className="edit-btn" onClick={() => iniciarEdicion(s)}>
                        Editar
                      </button>
                    )}
                    {puede('eliminar') && (
                      <button className="delete-btn" onClick={() => eliminarSucursal(s.id)}>
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
          Añadir Sucursal
        </button>
      ) : !mostrarInactivas && formVisible ? (
        <div className="form-container">
          {/* Empresa (combo) */}
          <label className="form-label">Empresa</label>
          <select
            className="form-input"
            name="id_empresa"
            value={formData.id_empresa}
            onChange={handleInputChange}
          >
            <option value="">Seleccione una empresa</option>
            {empresasOpt.map(emp => (
              <option key={emp.id} value={emp.id}>
                {emp.razon_social} (NIT: {emp.nit})
              </option>
            ))}
          </select>

          <label className="form-label">Nombre</label>
          <input
            className="form-input"
            name="nombre"
            value={formData.nombre}
            onChange={handleInputChange}
          />

          <label className="form-label">Código de Sucursal</label>
          <input
            className="form-input"
            name="codigo_sucursal"
            value={formData.codigo_sucursal}
            onChange={handleInputChange}
          />

          <label className="form-label">Dirección</label>
          <input
            className="form-input"
            name="direccion"
            value={formData.direccion}
            onChange={handleInputChange}
          />

          <label className="form-label">Teléfono</label>
          <input
            className="form-input"
            name="telefono"
            value={formData.telefono}
            onChange={handleInputChange}
          />

          <label className="form-label">Correo</label>
          <input
            className="form-input"
            name="email"
            value={formData.email}
            onChange={handleInputChange}
          />

          <div className="form-actions">
            <button
              className="create-btn"
              onClick={sucursalEditando ? actualizarSucursal : crearSucursal}
              disabled={loading}
            >
              {loading
                ? (sucursalEditando ? 'Actualizando...' : 'Creando...')
                : (sucursalEditando ? 'Actualizar' : 'Crear Sucursal')}
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

export default Sucursales
