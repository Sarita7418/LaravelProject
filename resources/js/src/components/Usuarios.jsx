import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Usuarios.css'

function Usuarios() {
  const [usuarios, setUsuarios] = useState([])
  const [usuariosInactivos, setUsuariosInactivos] = useState([])
  const [roles, setRoles] = useState([])
  const [formVisible, setFormVisible] = useState(false)
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [idRol, setIdRol] = useState('')
  const [loading, setLoading] = useState(false)
  const [usuarioEditando, setUsuarioEditando] = useState(null)
  const [mostrarInactivos, setMostrarInactivos] = useState(false)
  const [errors, setErrors] = useState({})
  const [modalVisible, setModalVisible] = useState(false)
  const [modalAction, setModalAction] = useState(null)
  const [modalUsuarioId, setModalUsuarioId] = useState(null)
  const [successModal, setSuccessModal] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchUsuarios()
      fetchUsuariosInactivos()
      fetchRoles()
    })
  }, [])

  const validateName = (value) => {
    const newErrors = { ...errors }
    
    if (!value.trim()) {
      newErrors.name = 'El nombre es obligatorio'
    } else if (value.trim().length < 2) {
      newErrors.name = 'El nombre debe tener al menos 2 caracteres'
    } else if (value.length > 50) {
      newErrors.name = 'El nombre no puede exceder 50 caracteres'
    } else if (!/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/.test(value)) {
      newErrors.name = 'El nombre solo puede contener letras y espacios'
    } else {
      delete newErrors.name
    }
    
    setErrors(newErrors)
    return !newErrors.name
  }

  const validateEmail = (value) => {
    const newErrors = { ...errors }
    
    if (!value.trim()) {
      newErrors.email = 'El email es obligatorio'
    } else if (value.length > 255) {
      newErrors.email = 'El email no puede exceder 255 caracteres'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      newErrors.email = 'El formato del email no es válido'
    } else {
      const emailExistente = usuarios.find(usuario => 
        usuario.email.toLowerCase() === value.trim().toLowerCase() && 
        usuario.id !== usuarioEditando
      )
      if (emailExistente) {
        newErrors.email = 'Este email ya está registrado'
      } else {
        delete newErrors.email
      }
    }
    
    setErrors(newErrors)
    return !newErrors.email
  }

  const validatePassword = (value) => {
    const newErrors = { ...errors }
    if (usuarioEditando && !value.trim()) {
      delete newErrors.password
      setErrors(newErrors)
      return true
    }
    
    if (!value.trim()) {
      newErrors.password = 'La contraseña es obligatoria'
    } else if (value.length < 8) {
      newErrors.password = 'La contraseña debe tener al menos 8 caracteres'
    } else if (!/(?=.*[a-zA-Z])(?=.*\d)/.test(value)) {
      newErrors.password = 'La contraseña debe contener al menos una letra y un número'
    } else {
      delete newErrors.password
    }
    
    setErrors(newErrors)
    return !newErrors.password
  }

  const validateRol = (value) => {
    const newErrors = { ...errors }
    
    if (!value || value === '') {
      newErrors.rol = 'Debe seleccionar un rol'
    } else {
      const rolExiste = roles.find(rol => rol.id.toString() === value.toString())
      if (!rolExiste) {
        newErrors.rol = 'El rol seleccionado no es válido'
      } else {
        delete newErrors.rol
      }
    }
    
    setErrors(newErrors)
    return !newErrors.rol
  }

  const validateAllFields = () => {
    const nameValid = validateName(name)
    const emailValid = validateEmail(email)
    const passwordValid = validatePassword(password)
    const rolValid = validateRol(idRol)
    
    return nameValid && emailValid && passwordValid && rolValid
  }

  const fetchUsuarios = async () => {
    try {
      const res = await axios.get('/api/usuarios')
      setUsuarios(res.data)
    } catch (error) {
      console.error('Error al obtener usuarios:', error)
    }
  }

  const fetchUsuariosInactivos = async () => {
    try {
      const res = await axios.get('/api/usuarios/inactivos')
      setUsuariosInactivos(res.data)
    } catch (error) {
      console.error('Error al obtener usuarios inactivos:', error)
    }
  }

  const fetchRoles = async () => {
    try {
      const res = await axios.get('/api/usuarios/roles')
      setRoles(res.data)
    } catch (error) {
      console.error('Error al obtener roles:', error)
    }
  }

  const handleModalConfirm = async () => {
    if (modalAction === 'desactivar') {
      try {
        await axios.delete(`/api/usuarios/${modalUsuarioId}`)
        fetchUsuarios()
        fetchUsuariosInactivos()
      } catch (error) {
        console.error('Error al desactivar usuario:', error)
      }
    } else if (modalAction === 'reactivar') {
      try {
        await axios.put(`/api/usuarios/${modalUsuarioId}/reactivar`)
        fetchUsuarios()
        fetchUsuariosInactivos()
      } catch (error) {
        console.error('Error al reactivar usuario:', error)
      }
    }
    setModalVisible(false)
    setModalAction(null)
    setModalUsuarioId(null)
  }

  const eliminarUsuario = (id) => {
    setModalAction('desactivar')
    setModalUsuarioId(id)
    setModalVisible(true)
  }

  const reactivarUsuario = (id) => {
    setModalAction('reactivar')
    setModalUsuarioId(id)
    setModalVisible(true)
  }

  const crearUsuario = async () => {
    if (!validateAllFields()) return
    
    setLoading(true)
    try {
      const trimmedName = name.trim()
      const trimmedEmail = email.trim().toLowerCase()
      
      await axios.post('/api/usuarios', { 
        name: trimmedName, 
        email: trimmedEmail, 
        password, 
        id_rol: idRol 
      })
      resetFormulario()
      fetchUsuarios()
      setSuccessMessage('Usuario creado exitosamente')
      setSuccessModal(true)
    } catch (error) {
      console.error('Error al crear usuario:', error)
      if (error.response?.data?.errors) {
        const serverErrors = error.response.data.errors
        const newErrors = {}
        
        if (serverErrors.name) newErrors.name = serverErrors.name[0]
        if (serverErrors.email) newErrors.email = serverErrors.email[0]
        if (serverErrors.password) newErrors.password = serverErrors.password[0]
        if (serverErrors.id_rol) newErrors.rol = serverErrors.id_rol[0]
        
        setErrors(newErrors)
      }
    } finally {
      setLoading(false)
    }
  }

  const actualizarUsuario = async () => {
    if (!validateAllFields()) return
    
    setLoading(true)
    try {
      const trimmedName = name.trim()
      const trimmedEmail = email.trim().toLowerCase()
      
      const data = { 
        name: trimmedName, 
        email: trimmedEmail, 
        id_rol: idRol 
      }
      if (password.trim()) {
        data.password = password
      }

      await axios.put(`/api/usuarios/${usuarioEditando}`, data)
      resetFormulario()
      fetchUsuarios()
      setSuccessMessage('Usuario actualizado exitosamente')
      setSuccessModal(true)
    } catch (error) {
      console.error('Error al actualizar usuario:', error)
      if (error.response?.data?.errors) {
        const serverErrors = error.response.data.errors
        const newErrors = {}
        
        if (serverErrors.name) newErrors.name = serverErrors.name[0]
        if (serverErrors.email) newErrors.email = serverErrors.email[0]
        if (serverErrors.password) newErrors.password = serverErrors.password[0]
        if (serverErrors.id_rol) newErrors.rol = serverErrors.id_rol[0]
        
        setErrors(newErrors)
      }
    } finally {
      setLoading(false)
    }
  }

  const iniciarEdicion = (usuario) => {
    setFormVisible(true)
    setName(usuario.name)
    setEmail(usuario.email)
    setPassword('')
    setIdRol(usuario.id_rol)
    setUsuarioEditando(usuario.id)
    setErrors({})
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setName('')
    setEmail('')
    setPassword('')
    setIdRol('')
    setUsuarioEditando(null)
    setErrors({})
  }

  const handleNameChange = (e) => {
    const value = e.target.value
    setName(value)
    validateName(value)
  }

  const handleEmailChange = (e) => {
    const value = e.target.value
    setEmail(value)
    validateEmail(value)
  }

  const handlePasswordChange = (e) => {
    const value = e.target.value
    setPassword(value)
    validatePassword(value)
  }

  const handleRolChange = (e) => {
    const value = e.target.value
    setIdRol(value)
    validateRol(value)
  }

  const closeModal = () => {
    setModalVisible(false)
    setModalAction(null)
    setModalUsuarioId(null)
  }

  const closeSuccessModal = () => {
    setSuccessModal(false)
    setSuccessMessage('')
  }

  const isFormValid = () => {
    const hasRequiredFields = name.trim() && email.trim() && idRol && (usuarioEditando || password.trim())
    const hasNoErrors = Object.keys(errors).length === 0
    return hasRequiredFields && hasNoErrors
  }

  return (
    <div className="usuarios-container">
      <h2 className="usuarios-title">Usuarios</h2>

      <div className="toggle-container">
        <button 
          className={`toggle-btn ${!mostrarInactivos ? 'active' : ''}`}
          onClick={() => setMostrarInactivos(false)}
        >
          Usuarios Activos ({usuarios.length})
        </button>
        <button 
          className={`toggle-btn ${mostrarInactivos ? 'active' : ''}`}
          onClick={() => setMostrarInactivos(true)}
        >
          Usuarios Inactivos ({usuariosInactivos.length})
        </button>
      </div>

      <table className="usuarios-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {(mostrarInactivos ? usuariosInactivos : usuarios).map((usuario) => (
            <tr key={usuario.id}>
              <td>{usuario.id}</td>
              <td>{usuario.name}</td>
              <td>{usuario.email}</td>
              <td>{usuario.role?.descripcion || 'Sin rol'}</td>
              <td>
                <span className={`status ${usuario.estado ? 'active' : 'inactive'}`}>
                  {usuario.estado ? 'Activo' : 'Inactivo'}
                </span>
              </td>
              <td>
                {mostrarInactivos ? (
                  <button className="reactivate-btn" onClick={() => reactivarUsuario(usuario.id)}>
                    Reactivar
                  </button>
                ) : (
                  <>
                    <button className="edit-btn" onClick={() => iniciarEdicion(usuario)}>
                      Editar
                    </button>
                    <button className="delete-btn" onClick={() => eliminarUsuario(usuario.id)}>
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
          Añadir Usuario
        </button>
      ) : !mostrarInactivos ? (
        <div className="form-container">
          <label className="form-label">
            Nombre <span className="required">*</span>
          </label>
          <input
            type="text"
            value={name}
            onChange={handleNameChange}
            placeholder="Ingrese el nombre completo"
            className={`form-input ${errors.name ? 'error' : ''}`}
          />
          {errors.name && (
            <div className="error-message">{errors.name}</div>
          )}

          <label className="form-label">
            Email <span className="required">*</span>
          </label>
          <input
            type="email"
            value={email}
            onChange={handleEmailChange}
            placeholder="correo@ejemplo.com"
            className={`form-input ${errors.email ? 'error' : ''}`}
          />
          {errors.email && (
            <div className="error-message">{errors.email}</div>
          )}

          <label className="form-label">
            {usuarioEditando ? 'Contraseña (dejar vacío para no cambiar)' : 'Contraseña'} {!usuarioEditando && <span className="required">*</span>}
          </label>
          <input
            type="password"
            value={password}
            onChange={handlePasswordChange}
            placeholder={usuarioEditando ? 'Nueva contraseña (opcional)' : 'Ingrese la contraseña'}
            className={`form-input ${errors.password ? 'error' : ''}`}
          />
          {errors.password && (
            <div className="error-message">{errors.password}</div>
          )}

          <label className="form-label">
            Rol <span className="required">*</span>
          </label>
          <select
            value={idRol}
            onChange={handleRolChange}
            className={`form-select ${errors.rol ? 'error' : ''}`}
          >
            <option value="">Seleccionar rol</option>
            {roles.map((rol) => (
              <option key={rol.id} value={rol.id}>
                {rol.descripcion}
              </option>
            ))}
          </select>
          {errors.rol && (
            <div className="error-message">{errors.rol}</div>
          )}

          <div className="form-actions">
            <button
              className="create-btn"
              onClick={usuarioEditando ? actualizarUsuario : crearUsuario}
              disabled={loading || !isFormValid()}
              type="button"
            >
              {loading
                ? usuarioEditando
                  ? 'Actualizando...'
                  : 'Creando...'
                : usuarioEditando
                ? 'Actualizar Usuario'
                : 'Crear Usuario'}
            </button>
            <button 
              className="cancel-btn" 
              onClick={resetFormulario}
              type="button"
            >
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
                ? '¿Estás seguro de que quieres desactivar este usuario?' 
                : '¿Estás seguro de que quieres reactivar este usuario?'}
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

export default Usuarios