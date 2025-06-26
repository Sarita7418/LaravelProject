import React, { useEffect, useState } from 'react'
import axios from '../axios'
import './Roles.css'

function Roles() {
  const [roles, setRoles] = useState([])
  const [formVisible, setFormVisible] = useState(false)
  const [descripcion, setDescripcion] = useState('')
  const [loading, setLoading] = useState(false)

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
      setDescripcion('')
      setFormVisible(false)
      fetchRoles()
    } catch (error) {
      console.error('Error al crear rol:', error)
    } finally {
      setLoading(false)
    }
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
                {/* <button className="edit-btn">Editar</button> */}
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
            <button className="create-btn" onClick={crearRol} disabled={loading}>
              {loading ? 'Creando...' : 'Crear Rol'}
            </button>
            <button
              className="cancel-btn"
              onClick={() => {
                setFormVisible(false)
                setDescripcion('')
              }}
            >
              Cancelar
            </button>
          </div>
        </div>
      )}
    </div>
  )
}

export default Roles
