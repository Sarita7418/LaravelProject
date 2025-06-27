import React, { useEffect, useState } from 'react'
import axios from '../axios'
import './Usuarios.css'

function Usuarios() {
  const [usuarios, setUsuarios] = useState([])
  const [roles, setRoles] = useState([])
  const [formVisible, setFormVisible] = useState(false)
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [idRol, setIdRol] = useState('')
  const [loading, setLoading] = useState(false)
  const [usuarioEditando, setUsuarioEditando] = useState(null) // ID del usuario a editar

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchUsuarios()
      fetchRoles()
    })
  }, [])

  const fetchUsuarios = async () => {
    try {
      const res = await axios.get('/api/usuarios')
      setUsuarios(res.data)
    } catch (error) {
      console.error('Error al obtener usuarios:', error)
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

  const eliminarUsuario = async (id) => {
    if (window.confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
      try {
        await axios.delete(`/api/usuarios/${id}`)
        fetchUsuarios()
      } catch (error) {
        console.error('Error al eliminar usuario:', error)
      }
    }
  }

  const crearUsuario = async () => {
    console.log('Creando usuario...', { name, email, password, idRol })
    if (!name.trim() || !email.trim() || !password.trim() || !idRol) {
      alert('Por favor completa todos los campos')
      return
    }
    setLoading(true)
    try {
      const response = await axios.post('/api/usuarios', { 
        name, 
        email, 
        password, 
        id_rol: idRol 
      })
      console.log('Usuario creado:', response.data)
      resetFormulario()
      fetchUsuarios()
      alert('Usuario creado exitosamente')
    } catch (error) {
      console.error('Error al crear usuario:', error)
      if (error.response?.data?.errors) {
        alert('Error: ' + Object.values(error.response.data.errors).flat().join(', '))
      } else if (error.response?.data?.message) {
        alert('Error: ' + error.response.data.message)
      } else {
        alert('Error al crear usuario')
      }
    } finally {
      setLoading(false)
    }
  }

  const actualizarUsuario = async () => {
    console.log('Actualizando usuario...', { name, email, password, idRol, usuarioEditando })
    if (!name.trim() || !email.trim() || !idRol || !usuarioEditando) {
      alert('Por favor completa todos los campos obligatorios')
      return
    }
    setLoading(true)
    try {
      const data = { 
        name, 
        email, 
        id_rol: idRol 
      }
      
      // Solo incluir password si se proporcionó
      if (password.trim()) {
        data.password = password
      }

      const response = await axios.put(`/api/usuarios/${usuarioEditando}`, data)
      console.log('Usuario actualizado:', response.data)
      resetFormulario()
      fetchUsuarios()
      alert('Usuario actualizado exitosamente')
    } catch (error) {
      console.error('Error al actualizar usuario:', error)
      if (error.response?.data?.errors) {
        alert('Error: ' + Object.values(error.response.data.errors).flat().join(', '))
      } else if (error.response?.data?.message) {
        alert('Error: ' + error.response.data.message)
      } else {
        alert('Error al actualizar usuario')
      }
    } finally {
      setLoading(false)
    }
  }

  const iniciarEdicion = (usuario) => {
    setFormVisible(true)
    setName(usuario.name)
    setEmail(usuario.email)
    setPassword('') // Dejar vacío para edición
    setIdRol(usuario.id_rol)
    setUsuarioEditando(usuario.id)
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setName('')
    setEmail('')
    setPassword('')
    setIdRol('')
    setUsuarioEditando(null)
  }

  return (
    <div className="usuarios-container">
      <h2 className="usuarios-title">Usuarios</h2>

      <table className="usuarios-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {usuarios.map((usuario) => (
            <tr key={usuario.id}>
              <td>{usuario.name}</td>
              <td>{usuario.email}</td>
              <td>{usuario.role?.descripcion || 'Sin rol'}</td>
              <td>
                <button className="edit-btn" onClick={() => iniciarEdicion(usuario)}>
                  Editar
                </button>
                <button className="delete-btn" onClick={() => eliminarUsuario(usuario.id)}>
                  Eliminar
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {!formVisible ? (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Usuario
        </button>
      ) : (
        <div className="form-container">
          <label className="form-label">Nombre</label>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Nombre completo"
            className="form-input"
          />

          <label className="form-label">Email</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="correo@ejemplo.com"
            className="form-input"
          />

          <label className="form-label">
            {usuarioEditando ? 'Contraseña (dejar vacío para no cambiar)' : 'Contraseña'}
          </label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder={usuarioEditando ? 'Nueva contraseña (opcional)' : 'Contraseña'}
            className="form-input"
          />

          <label className="form-label">Rol</label>
          <select
            value={idRol}
            onChange={(e) => setIdRol(e.target.value)}
            className="form-select"
          >
            <option value="">Seleccionar rol</option>
            {roles.map((rol) => (
              <option key={rol.id} value={rol.id}>
                {rol.descripcion}
              </option>
            ))}
          </select>

          <div className="form-actions">
            <button
              className="create-btn"
              onClick={usuarioEditando ? actualizarUsuario : crearUsuario}
              disabled={loading}
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
      )}
    </div>
  )
}

export default Usuarios