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
  const [acciones, setAcciones] = useState([])

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchUsuarios()
      fetchUsuariosInactivos()
      fetchRoles()
      fetchAcciones()
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

  const fetchAcciones = async () => {
    try {
      const res = await axios.get('/api/user')  // Suponiendo que incluye id_rol
      console.log('user:', res.data)
      const rolId = res.data.role_id || res.data.id_rol || res.data.role?.id
      const accionesRes = await axios.get(`/api/acciones/${rolId}`)
      setAcciones(accionesRes.data)
    } catch (error) {
      console.error('Error al obtener acciones del usuario:', error)
    }
  }

  const tieneAccion = (accion) => acciones.includes(accion)

  const eliminarUsuario = async (id) => {
    if (!tieneAccion('activar_usuarios')) return
    if (window.confirm('¿Estás seguro de que quieres desactivar este usuario?')) {
      try {
        await axios.delete(`/api/usuarios/${id}`)
        fetchUsuarios()
        fetchUsuariosInactivos()
      } catch (error) {
        console.error('Error al desactivar usuario:', error)
      }
    }
  }

  const reactivarUsuario = async (id) => {
    if (!tieneAccion('activar_usuarios')) return
    if (window.confirm('¿Estás seguro de que quieres reactivar este usuario?')) {
      try {
        await axios.put(`/api/usuarios/${id}/reactivar`)
        fetchUsuarios()
        fetchUsuariosInactivos()
      } catch (error) {
        console.error('Error al reactivar usuario:', error)
      }
    }
  }

  const crearUsuario = async () => {
    if (!tieneAccion('crear_usuarios')) return
    if (!name.trim() || !email.trim() || !password.trim() || !idRol) {
      alert('Por favor completa todos los campos')
      return
    }
    setLoading(true)
    try {
      await axios.post('/api/usuarios', { name, email, password, id_rol: idRol })
      resetFormulario()
      fetchUsuarios()
      alert('Usuario creado exitosamente')
    } catch (error) {
      console.error('Error al crear usuario:', error)
      alert('Error: ' + (error.response?.data?.message || 'Error al crear usuario'))
    } finally {
      setLoading(false)
    }
  }

  const actualizarUsuario = async () => {
    if (!tieneAccion('editar_usuarios')) return
    if (!name.trim() || !email.trim() || !idRol || !usuarioEditando) {
      alert('Por favor completa todos los campos obligatorios')
      return
    }
    setLoading(true)
    try {
      const data = { name, email, id_rol: idRol }
      if (password.trim()) data.password = password

      await axios.put(`/api/usuarios/${usuarioEditando}`, data)
      resetFormulario()
      fetchUsuarios()
      alert('Usuario actualizado exitosamente')
    } catch (error) {
      console.error('Error al actualizar usuario:', error)
      alert('Error: ' + (error.response?.data?.message || 'Error al actualizar usuario'))
    } finally {
      setLoading(false)
    }
  }

  const iniciarEdicion = (usuario) => {
    if (!tieneAccion('editar_usuarios')) return
    setFormVisible(true)
    setName(usuario.name)
    setEmail(usuario.email)
    setPassword('')
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

      <div className="toggle-container">
        <button className={`toggle-btn ${!mostrarInactivos ? 'active' : ''}`} onClick={() => setMostrarInactivos(false)}>
          Usuarios Activos ({usuarios.length})
        </button>
        <button className={`toggle-btn ${mostrarInactivos ? 'active' : ''}`} onClick={() => setMostrarInactivos(true)}>
          Usuarios Inactivos ({usuariosInactivos.length})
        </button>
      </div>

      <table className="usuarios-table">
        <thead>
          <tr>
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
                  tieneAccion('activar_usuarios') && (
                    <button className="reactivate-btn" onClick={() => reactivarUsuario(usuario.id)}>
                      Reactivar
                    </button>
                  )
                ) : (
                  <>
                    {tieneAccion('editar_usuarios') && (
                      <button className="edit-btn" onClick={() => iniciarEdicion(usuario)}>
                        Editar
                      </button>
                    )}
                    {tieneAccion('activar_usuarios') && (
                      <button className="delete-btn" onClick={() => eliminarUsuario(usuario.id)}>
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

      {!mostrarInactivos && !formVisible && tieneAccion('crear_usuarios') && (
        <button className="add-btn" onClick={() => setFormVisible(true)}>Añadir Usuario</button>
      )}

      {!mostrarInactivos && formVisible && (
        <div className="form-container">
          {/* formulario igual que antes */}
          {/* ...campos y botones de crear/actualizar... */}
        </div>
      )}
    </div>
  )
}

export default Usuarios
