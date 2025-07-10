import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../lib/axios'
import './MenuJerarquico.css'

const MenuJerarquico = () => {
  const [menuItems, setMenuItems] = useState([])
  const [abiertos, setAbiertos] = useState({})
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    // Paso 1: obtener el usuario autenticado
    axios.get('/api/user', { withCredentials: true })
      .then(res => {
        const userId = res.data.id
        // Paso 2: obtener el menú correspondiente a ese usuario
        return axios.get(`/api/menu/${userId}`)
      })
      .then(res => {
        const ordenados = res.data.sort((a, b) => a.orden - b.orden)
        setMenuItems(ordenados)
        setError('')
      })
      .catch(() => {
        setMenuItems([])
        setError('No se pudo cargar el menú')
      })
      .finally(() => setLoading(false))
  }, [])

  const toggleSubmenu = (id) => {
    setAbiertos(prev => ({
      ...prev,
      [id]: !prev[id]
    }))
  }

  const obtenerHijos = (padreId) =>
    menuItems.filter(item => item.id_padre === padreId)

  const renderMenu = (padreId = null) => {
    const items = menuItems.filter(item => item.id_padre === padreId)
    if (items.length === 0) {
      return null
    }
    return (
      <ul className="menujerar-submenu">
        {items.map(item => {
          const hijos = obtenerHijos(item.id)
          const tieneHijos = hijos.length > 0

          return (
            <li key={item.id} className={`menujerar-li ${tieneHijos ? 'menujerar-padre' : 'menujerar-hijo'}`}>
              <div
                className={`menujerar-boton ${tieneHijos ? 'menujerar-boton-padre' : 'menujerar-boton-hijo'}`}
                onClick={() => {
                  if (tieneHijos) {
                    toggleSubmenu(item.id)
                  } else if (item.ruta && item.ruta !== '#') {
                    navigate(item.ruta)
                  }
                }}
              >
                <span className={`menujerar-texto ${tieneHijos ? 'menujerar-texto-padre' : ''}`}>
                  {item.item}
                </span>
              </div>
              {tieneHijos && abiertos[item.id] && (
                <div className="menujerar-hijos-seccion">
                  {renderMenu(item.id)}
                </div>
              )}
            </li>
          )
        })}
      </ul>
    )
  }

  if (loading) {
    return <div className="menujerar-vacio">Cargando menú...</div>
  }

  if (error) {
    return <div className="menujerar-vacio">{error}</div>
  }

  if (!menuItems || menuItems.length === 0) {
    return <div className="menujerar-vacio">No hay opciones de menú</div>
  }

  return (
    <nav className="menujerar-sidebar">
      {renderMenu()}
    </nav>
  )
}

export default MenuJerarquico
