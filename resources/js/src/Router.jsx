import { Routes, Route, Navigate } from 'react-router-dom'
import Login from './LoginConDosPasos'
import Dashboard from './pages/Dashboard'
import Administracion from './pages/Administracion'
import Usuarios from './components/Usuarios'
import Roles from './components/Roles'

import PrivateRoute from './PrivateRoute'
import { useEffect, useState } from 'react'
import axios from './axios'

export default function Router() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [loading, setLoading] = useState(true)
  const [permisos, setPermisos] = useState([])
  const [pendingTwoFactor, setPendingTwoFactor] = useState(false) // Agregado para manejar 2FA

  // Diccionario de componentes disponibles según nombre de BDD
  const componentesDisponibles = {
    Dashboard: Dashboard,
    Administracion: Administracion,
    Usuarios: Usuarios,
    Roles: Roles
  }


  useEffect(() => {
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
  }, [])

  if (loading) return <div>Cargando...</div>

  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" />} />

      <Route path="/login" element={
        <Login
          setAuth={setIsAuthenticated}
          setPermisos={setPermisos}
          setPendingTwoFactor={setPendingTwoFactor} // Pasado como prop
        />
      } />

      {permisos.map(p => {
        const ruta = p.ruta || p // soporta tanto array de strings como objetos { ruta }
        const nombreComponente = p.componente || ruta.split('/').pop()?.charAt(0).toUpperCase() + ruta.split('/').pop()?.slice(1)

        const Componente = componentesDisponibles[nombreComponente]

        return Componente ? (
          <Route
            key={ruta}
            path={ruta}
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

      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}
