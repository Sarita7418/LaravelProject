import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Roles.css'

function Roles() {
  const [roles, setRoles] = useState([])
  const [rolesInactivos, setRolesInactivos] = useState([])
  const [formVisible, setFormVisible] = useState(false)
  const [descripcion, setDescripcion] = useState('')
  const [loading, setLoading] = useState(false)
  const [rolEditando, setRolEditando] = useState(null)
  const [mostrarInactivos, setMostrarInactivos] = useState(false)

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchRoles()
      fetchRolesInactivos()
    })
  }, [])

  const fetchRoles = async () => {
    try {
      const res = await axios.get('/api/roles')
      setRoles(res.data)
    } catch (error) {
      console.error('Error al obtener roles:', error)
    }
  }

  const fetchRolesInactivos = async () => {
    try {
      const res = await axios.get('/api/roles/inactivos')
      setRolesInactivos(res.data)
    } catch (error) {
      console.error('Error al obtener roles inactivos:', error)
    }
  }

  const eliminarRol = async (id) => {
    if (window.confirm('¿Estás seguro de que quieres desactivar este rol?')) {
      try {
        await axios.delete(`/api/roles/${id}`)
        fetchRoles()
        fetchRolesInactivos()
      } catch (error) {
        console.error('Error al desactivar rol:', error)
      }
    }
  }

  const reactivarRol = async (id) => {
    if (window.confirm('¿Estás seguro de que quieres reactivar este rol?')) {
      try {
        await axios.put(`/api/roles/${id}/reactivar`)
        fetchRoles()
        fetchRolesInactivos()
      } catch (error) {
        console.error('Error al reactivar rol:', error)
      }
    }
  }

  const crearRol = async () => {
    if (!descripcion.trim()) return
    setLoading(true)
    try {
      await axios.post('/api/roles', { descripcion })
      resetFormulario()
      fetchRoles()
    } catch (error) {
      console.error('Error al crear rol:', error)
    } finally {
      setLoading(false)
    }
  }

  const actualizarRol = async () => {
    if (!descripcion.trim() || !rolEditando) return
    setLoading(true)
    try {
      await axios.put(`/api/roles/${rolEditando}`, { descripcion })
      resetFormulario()
      fetchRoles()
    } catch (error) {
      console.error('Error al actualizar rol:', error)
    } finally {
      setLoading(false)
    }
  }

  const iniciarEdicion = (rol) => {
    setFormVisible(true)
    setDescripcion(rol.descripcion)
    setRolEditando(rol.id)
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setDescripcion('')
    setRolEditando(null)
  }

  return (
    <div className="roles-container">
      <h2 className="roles-title">Roles</h2>

      <div className="toggle-container">
        <button 
          className={`toggle-btn ${!mostrarInactivos ? 'active' : ''}`}
          onClick={() => setMostrarInactivos(false)}
        >
          Roles Activos ({roles.length})
        </button>
        <button 
          className={`toggle-btn ${mostrarInactivos ? 'active' : ''}`}
          onClick={() => setMostrarInactivos(true)}
        >
          Roles Inactivos ({rolesInactivos.length})
        </button>
      </div>

      <table className="roles-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {(mostrarInactivos ? rolesInactivos : roles).map((rol) => (
            <tr key={rol.id}>
              <td>{rol.id}</td>
              <td>{rol.descripcion}</td>
              <td>
                <span className={`status ${rol.estado ? 'active' : 'inactive'}`}>
                  {rol.estado ? 'Activo' : 'Inactivo'}
                </span>
              </td>
              <td>
                {mostrarInactivos ? (
                  <button className="reactivate-btn" onClick={() => reactivarRol(rol.id)}>
                    Reactivar
                  </button>
                ) : (
                  <>
                    <button className="edit-btn" onClick={() => iniciarEdicion(rol)}>
                      Editar
                    </button>
                    <button className="delete-btn" onClick={() => eliminarRol(rol.id)}>
                      Desactivar
                    </button>
                  </>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {!mostrarInactivos && !formVisible ? (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Rol
        </button>
      ) : !mostrarInactivos ? (
        <div className="form-container">
          <label className="form-label">Descripción</label>
          <input
            type="text"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            placeholder="descripcion"
            className="form-input"
          />
          <div className="form-actions">
            <button
              className="create-btn"
              onClick={rolEditando ? actualizarRol : crearRol}
              disabled={loading}
            >
              {loading
                ? rolEditando
                  ? 'Actualizando...'
                  : 'Creando...'
                : rolEditando
                ? 'Actualizar Rol'
                : 'Crear Rol'}
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

export default Roles