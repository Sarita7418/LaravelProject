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

  // Permisos panel
  const [rolDetalles, setRolDetalles] = useState(null) // {id, descripcion}
  const [menusTodos, setMenusTodos] = useState([]) // árbol de menús y acciones posibles
  const [menusAsignados, setMenusAsignados] = useState([]) // ids de menús activos del rol
  const [accionesAsignadas, setAccionesAsignadas] = useState([]) // [{id_menu_item, id_accion}]
  const [modoEditarAcciones, setModoEditarAcciones] = useState(false)
  const [accionesTmp, setAccionesTmp] = useState([]) // Estado temporal para editar acciones

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

  // --- PANEL DE PERMISOS ---

  const abrirPanelPermisos = async (rol) => {
    setRolDetalles(rol)
    setModoEditarAcciones(false)
    setAccionesTmp([])

    // Carga todos los menús y acciones posibles
    const menusRes = await axios.get('/api/roles/menus-acciones')
    setMenusTodos(menusRes.data)

    // Carga menús activos del rol
    const menusRolRes = await axios.get(`/api/roles/${rol.id}/menus`)
    setMenusAsignados(menusRolRes.data.map(m => m.id))

    // Carga acciones actuales de rol
    const accionesRolRes = await axios.get(`/api/roles/${rol.id}/acciones`)
    setAccionesAsignadas(accionesRolRes.data)
  }

  const cerrarPanelPermisos = () => {
    setRolDetalles(null)
    setMenusTodos([])
    setMenusAsignados([])
    setAccionesAsignadas([])
    setModoEditarAcciones(false)
    setAccionesTmp([])
  }

  // Menús seleccionados
  const handleToggleMenu = (id) => {
    setMenusAsignados(prev =>
      prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
    )
  }

  // Guardar menús
  const guardarMenus = async () => {
    if (!rolDetalles) return
    await axios.put(`/api/roles/${rolDetalles.id}/menus`, { menus: menusAsignados })
    // Opcional: feedback visual
  }

  // Entrar a modo edición de acciones
  const editarAccionesMenu = () => {
    setModoEditarAcciones(true)
    setAccionesTmp(accionesAsignadas)
  }

  // Cambiar acción en modo edición
  const toggleAccion = (id_menu_item, id_accion) => {
    setAccionesTmp(prev => {
      const existe = prev.some(a => a.id_menu_item === id_menu_item && a.id_accion === id_accion)
      if (existe) {
        return prev.filter(a => !(a.id_menu_item === id_menu_item && a.id_accion === id_accion))
      } else {
        return [...prev, { id_menu_item, id_accion }]
      }
    })
  }

  // Guardar acciones
  const guardarAcciones = async () => {
    if (!rolDetalles) return
    await axios.put(`/api/roles/${rolDetalles.id}/acciones`, { acciones: accionesTmp })
    setModoEditarAcciones(false)
    // Recargar acciones asignadas
    const accionesRolRes = await axios.get(`/api/roles/${rolDetalles.id}/acciones`)
    setAccionesAsignadas(accionesRolRes.data)
  }

  // Construye árbol recursivo
  const renderArbol = (menus, padreId = null) => {
    const items = menus.filter(m => m.id_padre === padreId)
    return (
      <ul className="roles-arbol-lista">
        {items.map(menu => {
          const hijos = menus.filter(m => m.id_padre === menu.id)
          const esTerminal = hijos.length === 0

          return (
            <li key={menu.id} className="roles-arbol-item">
              <div className="roles-arbol-row">
                <input
                  type="checkbox"
                  className="roles-arbol-checkbox"
                  checked={menusAsignados.includes(menu.id)}
                  onChange={() => handleToggleMenu(menu.id)}
                  disabled={rolDetalles && rolDetalles.descripcion === 'admin'} // Por ejemplo, admin no editable
                />
                <span className="roles-arbol-texto">{menu.item}</span>
                {modoEditarAcciones && esTerminal && menusAsignados.includes(menu.id) && (
                  <span className="roles-acciones-list">
                    {(menu.acciones || []).map(accion => (
                      <label key={accion.id} className="roles-acciones-checkbox-label">
                        <input
                          type="checkbox"
                          className="roles-acciones-checkbox"
                          checked={accionesTmp.some(a =>
                            a.id_menu_item === menu.id && a.id_accion === accion.id
                          )}
                          onChange={() => toggleAccion(menu.id, accion.id)}
                        />
                        <span className="roles-acciones-texto">{accion.nombre}</span>
                      </label>
                    ))}
                  </span>
                )}
              </div>
              {hijos.length > 0 && renderArbol(menus, menu.id)}
            </li>
          )
        })}
      </ul>
    )
  }

  // Panel central de permisos de rol
  const renderPanelPermisos = () => {
    if (!rolDetalles) return null

    return (
      <div className="roles-panel-permisos">
        <div className="roles-panel-header">
          <span className="roles-panel-titulo">
            Permisos para el rol: <strong>{rolDetalles.descripcion}</strong>
          </span>
          {!modoEditarAcciones && (
            <button className="roles-editar-acciones-btn" onClick={editarAccionesMenu}>
              Editar acciones para el menú
            </button>
          )}
          {modoEditarAcciones && (
            <button className="roles-guardar-acciones-btn" onClick={guardarAcciones}>
              Guardar cambios de acciones
            </button>
          )}
          <button className="roles-cerrar-panel-btn" onClick={cerrarPanelPermisos}>Cerrar</button>
        </div>
        <div className="roles-arbol">
          {menusTodos && menusTodos.length > 0 && renderArbol(menusTodos)}
        </div>
        <div className="roles-panel-botones">
          <button className="roles-guardar-menus-btn" onClick={guardarMenus}>Guardar</button>
        </div>
      </div>
    )
  }

  // ----- COMPONENTE PRINCIPAL -----
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
                    <button className="detalle-btn" onClick={() => abrirPanelPermisos(rol)}>
                      Detalles
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

      {renderPanelPermisos()}
    </div>
  )
}

export default Roles
