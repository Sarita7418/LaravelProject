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
  const [errors, setErrors] = useState({})
  const [modalVisible, setModalVisible] = useState(false)
  const [modalAction, setModalAction] = useState(null)
  const [modalRolId, setModalRolId] = useState(null)
  const [successModal, setSuccessModal] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')
  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchRoles()
      fetchRolesInactivos()
    })
  }, [])
  const validateDescripcion = (value) => {
    const newErrors = {}
    
    if (!value.trim()) {
      newErrors.descripcion = 'La descripción del rol es obligatoria'
    } else if (value.trim().length < 3) {
      newErrors.descripcion = 'La descripción debe tener al menos 3 caracteres'
    } else if (value.length > 100) {
      newErrors.descripcion = 'La descripción no puede exceder 100 caracteres'
    } else if (!/^[a-zA-Z0-9\s]+$/.test(value)) {
      newErrors.descripcion = 'Solo se permiten letras, números y espacios'
    } else {
      const rolExistente = roles.find(rol => 
        rol.descripcion.toLowerCase() === value.trim().toLowerCase() && 
        rol.id !== rolEditando
      )
      if (rolExistente) {
        newErrors.descripcion = 'Este rol ya existe'
      }
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

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

  const handleModalConfirm = async () => {
    if (modalAction === 'desactivar') {
      try {
        await axios.delete(`/api/roles/${modalRolId}`)
        fetchRoles()
        fetchRolesInactivos()
      } catch (error) {
        console.error('Error al desactivar rol:', error)
      }
    } else if (modalAction === 'reactivar') {
      try {
        await axios.put(`/api/roles/${modalRolId}/reactivar`)
        fetchRoles()
        fetchRolesInactivos()
      } catch (error) {
        console.error('Error al reactivar rol:', error)
      }
    }
    setModalVisible(false)
    setModalAction(null)
    setModalRolId(null)
  }

  const eliminarRol = (id) => {
    setModalAction('desactivar')
    setModalRolId(id)
    setModalVisible(true)
  }

  const reactivarRol = (id) => {
    setModalAction('reactivar')
    setModalRolId(id)
    setModalVisible(true)
  }

  const crearRol = async () => {
    const trimmedDescripcion = descripcion.trim()
    if (!validateDescripcion(trimmedDescripcion)) return
    
    setLoading(true)
    try {
      await axios.post('/api/roles', { descripcion: trimmedDescripcion })
      resetFormulario()
      fetchRoles()
      setSuccessMessage('Rol creado exitosamente')
      setSuccessModal(true)
    } catch (error) {
      console.error('Error al crear rol:', error)
    } finally {
      setLoading(false)
    }
  }

  const actualizarRol = async () => {
    const trimmedDescripcion = descripcion.trim()
    if (!validateDescripcion(trimmedDescripcion) || !rolEditando) return
    
    setLoading(true)
    try {
      await axios.put(`/api/roles/${rolEditando}`, { descripcion: trimmedDescripcion })
      resetFormulario()
      fetchRoles()
      setSuccessMessage('Rol actualizado exitosamente')
      setSuccessModal(true)
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
    setErrors({})
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setDescripcion('')
    setRolEditando(null)
    setErrors({})
  }

  const handleDescripcionChange = (e) => {
    const value = e.target.value
    setDescripcion(value)
    validateDescripcion(value)
  }

  const closeModal = () => {
    setModalVisible(false)
    setModalAction(null)
    setModalRolId(null)
  }

  const closeSuccessModal = () => {
    setSuccessModal(false)
    setSuccessMessage('')
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
          <label className="form-label">
            Descripción <span className="required">*</span>
          </label>
          <input
            type="text"
            value={descripcion}
            onChange={handleDescripcionChange}
            placeholder="Ingrese la descripción del rol"
            className={`form-input ${errors.descripcion ? 'error' : ''}`}
          />
          {errors.descripcion && (
            <div className="error-message">{errors.descripcion}</div>
          )}
          <div className="form-actions">
            <button
              className="create-btn"
              onClick={rolEditando ? actualizarRol : crearRol}
              disabled={loading || Object.keys(errors).length > 0 || !descripcion.trim()}
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
      {modalVisible && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3 className="modal-title">Confirmar acción</h3>
            <p className="modal-message">
              {modalAction === 'desactivar' 
                ? '¿Estás seguro de que quieres desactivar este rol?' 
                : '¿Estás seguro de que quieres reactivar este rol?'}
            </p>
            <div className="modal-actions">
              <button className="modal-confirm-btn" onClick={handleModalConfirm}>
                Aceptar
              </button>
              <button className="modal-cancel-btn" onClick={closeModal}>
                Cancelar
              </button>
            </div>
          </div>
        </div>
      )}
      {successModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3 className="modal-title">Éxito</h3>
            <p className="modal-message">{successMessage}</p>
            <div className="modal-actions">
              <button className="modal-confirm-btn" onClick={closeSuccessModal}>
                Aceptar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Roles