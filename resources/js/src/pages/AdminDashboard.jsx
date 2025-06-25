import React from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../axios' 

const AdminDashboard = ({ setAuth, setRole }) => {
  const navigate = useNavigate()

  const handleLogout = async () => {
    try {
      await axios.get('/sanctum/csrf-cookie')
      await axios.post('/api/logout')
      setAuth(false)
      setRole(null)
      navigate('/login')
    } catch (err) {
      console.error('Error al cerrar sesión', err)
    }
  }

  return (
    <div>
      <h1>Bienvenido Administrador</h1>
      <p>Esta es la vista del panel de administración.</p>
      <button onClick={handleLogout}>Cerrar sesión</button>
    </div>
  )
}

export default AdminDashboard
