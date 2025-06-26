import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from './axios'

export default function Login({ setAuth, setPermisos }) {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')

  const handleLogin = async (e) => {
    e.preventDefault()

    try {
      // Paso 1: solicitar token CSRF
      await axios.get('/sanctum/csrf-cookie')

      // Paso 2: login
      await axios.post('/api/login', { email, password }, { withCredentials: true })

      // Paso 3: obtener usuario autenticado
      const res = await axios.get('/api/user')
      console.log('Respuesta completa del backend:', res.data)

      const permisos = res.data.permisos

      console.log('Permisos del usuario:', permisos)

      // Actualiza estado global
      setAuth(true)
      setPermisos(permisos)

      // Redireccionar
      if (permisos.includes('ver_dashboard')) {
        navigate('/admin')
      } else if (permisos.includes('ver_dashboard')) {
        navigate('/dashboard')
      } else {
        navigate('/unauthorized')
      }

    } catch (err) {
      console.error('Error al iniciar sesión', err)
      setError('Credenciales inválidas o error de red.')
    }
  }

  return (
    <div>
      <h2>Iniciar Sesión</h2>
      <form onSubmit={handleLogin}>
        <input
          type="email"
          placeholder="Correo"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        /><br />
        <input
          type="password"
          placeholder="Contraseña"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        /><br />
        <button type="submit">Entrar</button>
        {error && <p style={{ color: 'red' }}>{error}</p>}
      </form>
    </div>
  );
}
