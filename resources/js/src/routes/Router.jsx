import { Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import axios from '../lib/axios'

import Login from '../components/LoginConDosPasos'
import Dashboard from '../pages/Dashboard'
import Usuarios from '../components/Usuarios'
import Roles from '../components/Roles'
import Personas from '../components/Personas'
import LayoutDashboard from '../components/LayoutDashboard'
import PrivateRoute from './PrivateRoute'

export default function Router() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [loading, setLoading] = useState(true)
  const [permisos, setPermisos] = useState([])
  const [pendingTwoFactor, setPendingTwoFactor] = useState(false)

  useEffect(() => {
    axios.get('/api/user', { withCredentials: true })
      .then(res => {
        setIsAuthenticated(true)
        setPermisos(res.data.permisos.map(p => p.ruta))
      })
      .catch(() => {
        setIsAuthenticated(false)
        setPermisos([])
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <div>Cargando...</div>

  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" />} />

      <Route path="/login" element={
        <Login
          setAuth={setIsAuthenticated}
          setPermisos={setPermisos}
          setPendingTwoFactor={setPendingTwoFactor}
        />
      } />

      <Route path="/dashboard" element={
        <PrivateRoute
          isAuthenticated={isAuthenticated}
          userPermisos={permisos}
          allowedPermisos={["/dashboard"]}
        >
          <LayoutDashboard />
        </PrivateRoute>
      }>
        {permisos.includes("/dashboard/usuarios") && (
          <Route path="usuarios" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/usuarios"]}
            >
              <Usuarios />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/roles") && (
          <Route path="roles" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/roles"]}
            >
              <Roles />
            </PrivateRoute>
          } />
        )}
        {permisos.includes("/dashboard/personas") && (
          <Route path="personas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/personas"]}
            >
              <Personas />
            </PrivateRoute>
          } />
        )}

        {/* Agregar aquí nuevas rutas manualmente si se crean más componentes */}

      </Route>

      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}