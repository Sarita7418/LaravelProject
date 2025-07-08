import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../lib/axios'

const MenuJerarquico = ({ idRol = 1 }) => {
  const [menuItems, setMenuItems] = useState([])
  const [abiertos, setAbiertos] = useState({})
  const navigate = useNavigate()

  useEffect(() => {
    axios.get(`/api/menu/${idRol}`)
      .then(res => {
        const ordenados = res.data.sort((a, b) => a.orden - b.orden)
        setMenuItems(ordenados)
      })
      .catch(err => console.error('Error cargando menú:', err))
  }, [idRol])

  const toggleSubmenu = (id) => {
    setAbiertos(prev => ({
      ...prev,
      [id]: !prev[id]
    }))
  }

  // Función para obtener hijos de un ítem
  const obtenerHijos = (padreId) =>
    menuItems.filter(item => item.id_padre === padreId)

  const renderMenu = (padreId = null) => {
    const items = menuItems.filter(item => item.id_padre === padreId)

    return (
      <ul className="submenu">
        {items.map(item => {
          const hijos = obtenerHijos(item.id)
          const tieneHijos = hijos.length > 0

          return (
            <li key={item.id}>
              <div
                className="menu-boton"
                onClick={() => {
                  if (tieneHijos) {
                    toggleSubmenu(item.id)
                  } else if (item.ruta && item.ruta !== '#') {
                    navigate(item.ruta)
                  }
                }}
              >
                {tieneHijos && (
                  <span>{abiertos[item.id] ? '▼' : '▶'}</span>
                )}
                <span>{item.item}</span>
              </div>

              {tieneHijos && abiertos[item.id] && renderMenu(item.id)}
            </li>
          )
        })}
      </ul>
    )
  }

  return (
    <div className="sidebar-menu">
      {renderMenu()}
    </div>
  )
}

export default MenuJerarquico
