import { Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import axios from '../lib/axios'

import LoginConDosPasos from '../components/LoginConDosPasos'
import CambiarContrasena from '../components/CambiarContrasena'
import Usuarios from '../components/Usuarios'
import Roles from '../components/Roles'
import Personas from '../components/Personas'
import Protocolos from '../components/Protocolos'
import Empresas from '../components/Empresas'
import Sucursales from '../components/Sucursales'
import PlanCuentas from '../components/PlanCuentas'
import PlanPresupuestarios from '../components/PlanPresupuestarios'
import LayoutDashboard from '../components/LayoutDashboard'
import PrivateRoute from './PrivateRoute'
import Comprobante from '../components/Comprobante'
import Reportes from '../components/Reportes'
import Registro from '../components/Registro'
import RecuperarContrasena from '../components/RecuperarContrasena'
import Compras from '../components/inventarios/Compras'
import CrearCompra from '../components/inventarios/CrearCompra'
import VerCompra from '../components/inventarios/VerCompra'
import Factura from '../components/Factura'
import CatalogoMedicos from '../components/CatalogoMedicos'
import Ventas from '../components/Ventas'
import HistorialVentas from '../components/HistorialVentas'


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
        <LoginConDosPasos
          setAuth={setIsAuthenticated}
          setPermisos={setPermisos}
          setPendingTwoFactor={setPendingTwoFactor}
        />
      } />

      <Route path="/registro" element={<Registro />} />

      <Route path="/recuperar-contrasena" element={<RecuperarContrasena />} />
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
              allowedPermisos={["/dashboard/usuarios"]}
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
              allowedPermisos={["/dashboard/roles"]}
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
              allowedPermisos={["/dashboard/personas"]}
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
              allowedPermisos={["/dashboard/protocolos"]}
            >
              <Protocolos />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/reportes") && (
          <Route path="reportes" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/reportes"]}
            >
              <Reportes />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/comprobantes") && (
          <Route path="comprobantes" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/comprobantes"]}
            >
              <Comprobante />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/facturas") && (
          <Route path="facturas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/facturas"]}
            >
              <Factura />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/catalogo-medicos") && (
          <Route path="catalogo-medicos" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/catalogo-medicos"]}
            >
              <CatalogoMedicos />
            </PrivateRoute>
          } />
        )}  

        {permisos.includes("/dashboard/empresas") && (
          <Route path="empresas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/empresas"]}
            >
              <Empresas />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/sucursales") && (
          <Route path="sucursales" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/sucursales"]}
            >
              <Sucursales />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/plan-cuentas") && (
          <Route path="plan-cuentas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/plan-cuentas"]}
            >
              <PlanCuentas />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/plan-presupuestarios") && (
          <Route path="plan-presupuestarios" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/plan-presupuestarios"]}
            >
              <PlanPresupuestarios />
            </PrivateRoute>
          } />
        )}

        {permisos.includes("/dashboard/ventas") && (
          <Route path="ventas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/ventas"]}
            >
              <Ventas />
            </PrivateRoute>
          } />
        )}

        
        {permisos.includes("/dashboard/historial-ventas") && (
          <Route path="historial-ventas" element={
            <PrivateRoute
              isAuthenticated={isAuthenticated}
              userPermisos={permisos}
              allowedPermisos={["/dashboard/historial-ventas"]}
            >
              <HistorialVentas />
            </PrivateRoute>
          } />
        )}


        {permisos.includes("/dashboard/compras") && (
          <>
            <Route path="compras" element={
              <PrivateRoute
                isAuthenticated={isAuthenticated}
                userPermisos={permisos}
                allowedPermisos={["/dashboard/compras"]}
              >
                <Compras />
              </PrivateRoute>
            } />
            <Route path="compras/crear" element={
              <PrivateRoute
                isAuthenticated={isAuthenticated}
                userPermisos={permisos}
                allowedPermisos={["/dashboard/compras"]}
              >
                <CrearCompra />
              </PrivateRoute>
            } />
            <Route path="compras/:id" element={
              <PrivateRoute
                isAuthenticated={isAuthenticated}
                userPermisos={permisos}
                allowedPermisos={["/dashboard/compras"]}
              >
                <VerCompra />
              </PrivateRoute>
            } />
          </>
        )}

      </Route>

      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}