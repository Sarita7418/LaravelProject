import { Routes, Route, Navigate } from 'react-router-dom'

import Login from '../components/LoginConDosPasos'
import Dashboard from '../pages/Dashboard'
import Administracion from '../pages/Administracion'
import Usuarios from '../components/Usuarios'
import Roles from '../components/Roles'
import Personas from '../components/Personas'

import PrivateRoute from './PrivateRoute'
import { useEffect, useState } from 'react'
import axios from '../lib/axios'

import LayoutDashboard from '../components/LayoutDashboard'

export default function Router() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [loading, setLoading] = useState(true)
  const [permisos, setPermisos] = useState([])
  const [pendingTwoFactor, setPendingTwoFactor] = useState(false)

  const componentesDisponibles = {
    Dashboard: Dashboard,
    Administracion: Administracion,
    Usuarios: Usuarios,
    Roles: Roles,
    Personas: Personas
  }

  useEffect(() => {
    const verificarSesion = () => {
      axios.get('/api/user', { withCredentials: true })
        .then(res => {
          setIsAuthenticated(true)
          setPermisos(res.data.permisos)
        })
        .catch(() => {
          setIsAuthenticated(false)
          setPermisos([])
        })
        .finally(() => setLoading(false))
    }

    verificarSesion(); // al montar

    const handlePopState = () => {
      verificarSesion(); // al retroceder con el navegador
    };

    window.addEventListener('popstate', handlePopState)
    return () => window.removeEventListener('popstate', handlePopState)
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

      {/* ✅ Rutas protegidas bajo /dashboard con Layout persistente */}
      <Route path="/dashboard" element={
        <PrivateRoute
          isAuthenticated={isAuthenticated}
          userPermisos={permisos.map(p => typeof p === 'string' ? p : p.ruta)}
          allowedPermisos={['/dashboard']}
        >
          <LayoutDashboard />
        </PrivateRoute>
      }>
        {permisos.map(p => {
          const ruta = p.ruta || p
          const nombreComponente = p.componente || ruta.split('/').pop()?.charAt(0).toUpperCase() + ruta.split('/').pop()?.slice(1)
          const Componente = componentesDisponibles[nombreComponente]

          // ⚠️ subrutas relativas, por ejemplo 'administracion/usuarios'
          const subruta = ruta.replace('/dashboard/', '')

          return Componente ? (
            <Route
              key={ruta}
              path={subruta}
              element={
                <PrivateRoute
                  isAuthenticated={isAuthenticated}
                  userPermisos={permisos.map(p => typeof p === 'string' ? p : p.ruta)}
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
