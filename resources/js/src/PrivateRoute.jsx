// PrivateRoute.jsx
import { Navigate } from 'react-router-dom'

export default function PrivateRoute({ children, isAuthenticated, allowedRoles, userRole }) {
  if (!isAuthenticated) return <Navigate to="/login" />
  if (!allowedRoles.includes(userRole)) return <Navigate to="/unauthorized" />
  return children
}
