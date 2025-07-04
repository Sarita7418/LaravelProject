import React from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../lib/axios'
import MenuJerarquico from '../components/MenuJerarquico' // <-- importa tu menú dinámico

const Dashboard = ({ setAuth, setRole }) => {
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
    <div style={{ display: 'flex', height: '100vh' }}>
     

      <div style={{ flex: 1, padding: '1rem' }}>
        <h1>Bienvenido</h1>
        <p>Este es tu panel principal. El contenido variará según tus permisos.</p>
        <button onClick={handleLogout}>Cerrar sesión</button>
      </div>
    </div>
  )
}

export default Dashboard
