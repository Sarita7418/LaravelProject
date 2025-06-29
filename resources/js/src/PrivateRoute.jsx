import { Navigate, useLocation } from 'react-router-dom'

export default function PrivateRoute({ isAuthenticated, userPermisos, allowedPermisos, children }) {
  const location = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" />
  }

  console.log("userPermisos:", userPermisos)
  console.log("allowedPermisos:", allowedPermisos)

  // userPermisos ya es un array de rutas string: ['/admin', '/dashboard']
  const tienePermiso = allowedPermisos.some(permiso => userPermisos.includes(permiso));

  if (!tienePermiso) {
    console.warn(`Ruta "${location.pathname}" no autorizada para el usuario.`);
    return <Navigate to="/unauthorized" />
  }

  return children;
}
