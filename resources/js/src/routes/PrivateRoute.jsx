import { Navigate, useLocation } from 'react-router-dom'

export default function PrivateRoute({ isAuthenticated, userPermisos, children }) {
  const location = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" />
  }

  const tienePermiso = userPermisos.includes(location.pathname);

  if (!tienePermiso) {
    console.warn(`Ruta "${location.pathname}" no autorizada para el usuario.`);
    return <Navigate to="/unauthorized" />
  }

  return children;
}

