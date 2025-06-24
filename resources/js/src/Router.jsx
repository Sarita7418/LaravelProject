import { Routes, Route, Navigate } from 'react-router-dom'
import Login from './Login'
import UserDashboard from './pages/UserDashboard'
import AdminDashboard from './pages/AdminDashboard'
import PrivateRoute from './PrivateRoute'
import { useEffect, useState } from 'react'
import axios from './axios'

export default function Router() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [userRole, setUserRole] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    axios.get('/api/user')
      .then(res => {
        setIsAuthenticated(true)
        setUserRole(res.data.role)
      })
      .catch(() => {
        setIsAuthenticated(false)
        setUserRole(null)
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <div>Cargando...</div>

  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" />} />
      <Route path="/login" element={<Login setAuth={setIsAuthenticated} setRole={setUserRole} />} />
      <Route path="/dashboard" element={
      <PrivateRoute isAuthenticated={isAuthenticated} userRole={userRole} allowedRoles={['user']}>
        <UserDashboard setAuth={setIsAuthenticated} setRole={setUserRole} />
      </PrivateRoute>
      } />
      <Route path="/admin" element={
        <PrivateRoute isAuthenticated={isAuthenticated} userRole={userRole} allowedRoles={['admin']}>
          <AdminDashboard />
        </PrivateRoute>
      } />
      <Route path="/unauthorized" element={<h1>No autorizado</h1>} />
    </Routes>
  )
}
