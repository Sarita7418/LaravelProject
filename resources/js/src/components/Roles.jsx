import React, { useEffect, useState } from 'react'
import axios from '../axios'
import './Roles.css'

function Roles() {
  const [roles, setRoles] = useState([])
  const [formVisible, setFormVisible] = useState(false)
  const [descripcion, setDescripcion] = useState('')
  const [loading, setLoading] = useState(false)
  const [rolEditando, setRolEditando] = useState(null) // ID del rol a editar

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchRoles()
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

  const eliminarRol = async (id) => {
    try {
      await axios.delete(`/api/roles/${id}`)
      fetchRoles()
    } catch (error) {
      console.error('Error al eliminar rol:', error)
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

      <table className="roles-table">
        <thead>
          <tr>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {roles.map((rol) => (
            <tr key={rol.id}>
              <td>{rol.descripcion}</td>
              <td>
                <button className="edit-btn" onClick={() => iniciarEdicion(rol)}>
                  Editar
                </button>
                <button className="delete-btn" onClick={() => eliminarRol(rol.id)}>
                  Eliminar
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {!formVisible ? (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Rol
        </button>
      ) : (
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
      )}
    </div>
  )
}

export default Roles
