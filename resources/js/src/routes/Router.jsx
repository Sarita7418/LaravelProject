import { Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import axios from '../lib/axios'

import LoginConDosPasos from '../components/LoginConDosPasos'
import CambiarContrasena from '../components/CambiarContrasena'
import Usuarios from '../components/Usuarios'
import Roles from '../components/Roles'
import Personas from '../components/Personas'
import Protocolos from '../components/Protocolos'
import PlanCuentas from '../components/PlanCuentas'
import PlanPresupuestarios from '../components/PlanPresupuestarios'   // 👈 IMPORTAR AQUÍ
import LayoutDashboard from '../components/LayoutDashboard'
import PrivateRoute from './PrivateRoute'

import Registro from '../components/Registro' 

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
      <Route path="/" element={<Navigate to="/login"/>} />

      <Route path="/login" element={
        <LoginConDosPasos
          setAuth={setIsAuthenticated}
          setPermisos={setPermisos}
          setPendingTwoFactor={setPendingTwoFactor}
        />
      } />

      <Route path="/registro" element={<Registro />} />

      <Route path="/cambiar-contrasena" element={<CambiarContrasena />} />

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

        {permisos.includes("/dashboard/protocolos") && (
          <Route path="protocolos" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/protocolos"]}
            >
              <Protocolos />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/plan-cuentas") && (
          <Route path="plan-cuentas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/plan-cuentas"]}
            >
              <PlanCuentas />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/plan-presupuestarios") && (   // 👈 AÑADIR ESTE BLOQUE
          <Route path="plan-presupuestarios" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/plan-presupuestarios"]}
            >
              <PlanPresupuestarios />
            </PrivateRoute>
          } />
        )}
      </Route>

      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}