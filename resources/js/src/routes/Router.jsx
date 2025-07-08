import { Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import axios from '../lib/axios'

import Login from '../components/LoginConDosPasos'
import Dashboard from '../pages/Dashboard'
import Administracion from '../pages/Administracion'
import Usuarios from '../components/Usuarios'
import Roles from '../components/Roles'

import PrivateRoute from './PrivateRoute'
import LayoutDashboard from '../components/LayoutDashboard'

export default function Router() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [loading, setLoading] = useState(true)
  const [menu, setMenu] = useState([])

  const componentesDisponibles = {
    Dashboard: Dashboard,
    Administracion: Administracion,
    Usuarios: Usuarios,
    Roles: Roles
  }

  useEffect(() => {
    axios.get('/api/menu/1')
      .then(res => {
        setIsAuthenticated(true)
        setMenu(res.data)
        console.log("🟢 Permisos cargados:", res.data.permisos)
      })
      .catch(() => {
        setIsAuthenticated(false)
        setMenu([])
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <div>Cargando...</div>

  const rutasPermitidas = menu.map(p => p.ruta)

  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" />} />

      <Route path="/login" element={
        <Login
          setAuth={setIsAuthenticated}
          setPermisos={setMenu}
        />
      } />

      <Route path="/dashboard" element={
        <PrivateRoute
          isAuthenticated={isAuthenticated}
          userPermisos={rutasPermitidas}
          allowedPermisos={['/dashboard']}
        >
          <LayoutDashboard />
        </PrivateRoute>
      }>
        {menu.map(p => {
          const ruta = p.ruta
          const nombreComponente = ruta.split('/').pop()?.charAt(0).toUpperCase() + ruta.split('/').pop()?.slice(1)
          const Componente = componentesDisponibles[nombreComponente]
          const subruta = ruta.replace('/dashboard', '').replace(/^\//, '')


          return Componente ? (
            <Route
              key={ruta}
              path={subruta}
              element={
                <PrivateRoute
                  isAuthenticated={isAuthenticated}
                  userPermisos={rutasPermitidas}
                  allowedPermisos={[ruta]}
                >
                  <Componente />
                </PrivateRoute>
              }
            />
          ) : null
        })}
      </Route>

      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}
