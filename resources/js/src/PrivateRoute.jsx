// PrivateRoute.jsx
import { Navigate } from 'react-router-dom'

export default function PrivateRoute({ isAuthenticated, userPermisos, allowedPermisos, children }) {
  if (!isAuthenticated) {
    return <Navigate to="/login" />
  }

  // Si no tiene al menos uno de los permisos requeridos
  const tienePermiso = allowedPermisos.some(permiso => userPermisos.includes(permiso))
  if (!tienePermiso) {
    return <Navigate to="/unauthorized" />
  }

  return children
}
