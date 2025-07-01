import { Routes, Route, Navigate } from 'react-router-dom'
import Login from './LoginConDosPasos'
import UserDashboard from './pages/UserDashboard'
import AdminDashboard from './pages/AdminDashboard'
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

  // Diccionario de componentes disponibles según ruta
  const rutasComponentes = {
    '/admin/usuarios': <Usuarios />,
    '/admin/roles': <Roles />
    // puedes agregar más rutas dinámicas aquí sin tocar JSX
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

      <Route path="/dashboard" element={
        <PrivateRoute isAuthenticated={isAuthenticated} userPermisos={permisos} allowedPermisos={['/dashboard']}>
          <UserDashboard />
        </PrivateRoute>
      } />

      <Route path="/admin" element={
        <PrivateRoute isAuthenticated={isAuthenticated} userPermisos={permisos} allowedPermisos={['/admin']}>
          <AdminDashboard setAuth={setIsAuthenticated} setRole={() => { }} />
        </PrivateRoute>
      }>
        {permisos
          .filter(p => p.startsWith('/admin/') && p !== '/admin')
          .map(ruta => {
            const subPath = ruta.replace('/admin/', '')
            const componente = rutasComponentes[ruta]
            return componente ? (
              <Route
                key={ruta}
                path={subPath}
                element={
                  <PrivateRoute
                    isAuthenticated={isAuthenticated}
                    userPermisos={permisos}
                    allowedPermisos={[ruta]}
                  >
                    {componente}
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
