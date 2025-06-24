// PrivateRoute.jsx
import { Navigate } from 'react-router-dom'

export default function PrivateRoute({ isAuthenticated, userRole, allowedRoles, children }) {
  if (!isAuthenticated) {
    return <Navigate to="/login" />
  }

  if (!allowedRoles.includes(userRole)) {
    return <Navigate to="/unauthorized" />
  }

  return children
}
