import React, { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../axios' // 👈 asegúrate de que aquí esté bien configurado
import Roles from '../components/Roles'
import Usuarios from '../components/Usuarios'

const AdminDashboard = ({ setAuth, setRole }) => {
  const navigate = useNavigate()

  // 🔍 Este useEffect consulta el endpoint y muestra los datos del menú
  useEffect(() => {
    axios.get('/api/menu-items') // 👈 asegúrate que esta ruta exista
      .then(res => {
        console.log('✅ Menú anidado:', JSON.stringify(res.data, null, 2))
        // Puedes copiar res.data completo desde la consola del navegador
      })
      .catch(err => console.error('❌ Error al obtener menu-items:', err))
  }, [])

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
      <Roles />
      <Usuarios />
      <button onClick={handleLogout}>Cerrar sesión</button>
    </div>
  )
}

export default AdminDashboard
