import React from 'react'
import { useNavigate, Outlet } from 'react-router-dom'
import axios from '../axios'
import MenuJerarquico from '../components/MenuJerarquico'
import './Administracion.css'

const Administracion = ({ setAuth, setRole }) => {
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
    <div className="dashboard-container">
      <aside className="sidebar">
        <MenuJerarquico />
        <button className="logout-button" onClick={handleLogout}>
          Cerrar sesión
        </button>
      </aside>

      <main className="main-content">
        <h1 className="title">Bienvenido Administrador</h1>
        <Outlet />
      </main>
    </div>
  )
}

export default Administracion
