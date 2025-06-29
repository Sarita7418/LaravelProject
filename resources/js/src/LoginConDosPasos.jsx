import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from './axios'
import AutenticacionDosPasos from './AutenticacionDosPasos'

export default function Login({ setAuth, setPermisos, setPendingTwoFactor }) {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [mostrarDosPasos, setMostrarDosPasos] = useState(false)
  const [usuarioEmail, setUsuarioEmail] = useState('')

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
      const rutas = permisos.map(p => p.ruta).filter(Boolean)
      const dosPasosHabilitado = res.data.dos_pasos_habilitado

      console.log('Permisos del usuario:', permisos)
      console.log('Dos pasos habilitado:', dosPasosHabilitado)

      if (dosPasosHabilitado) {
        setUsuarioEmail(res.data.email)
        setMostrarDosPasos(true)
        if (setPendingTwoFactor) {
          setPendingTwoFactor(true)
        }
      } else {
        completarLogin(rutas) // ← esto sí es un array de strings: ['/admin', '/dashboard', etc]
      }


    } catch (err) {
      console.error('Error al iniciar sesión', err)
      setError('Credenciales inválidas o error de red.')
    }
  }

  const completarLogin = (rutas) => {
  setAuth(true)
  setPermisos(rutas)

  if (setPendingTwoFactor) {
    setPendingTwoFactor(false)
  }

  // Redireccionar
  if (rutas.includes('/admin')) {
    navigate('/admin')
  } else if (rutas.includes('/dashboard')) {
    navigate('/dashboard')
  } else {
    navigate('/unauthorized')
  }
}




  const manejarVerificacionExitosa = (usuario, permisos) => {
  console.log('Verificación 2FA exitosa')
  const rutas = (permisos || usuario?.permisos || []).map(p => p.ruta).filter(Boolean)
  completarLogin(rutas)
}



  const manejarCancelarDosPasos = async () => {
    setMostrarDosPasos(false)
    setUsuarioEmail('')
    setError('')
    if (setPendingTwoFactor) {
      setPendingTwoFactor(false)
    }

    try {
      await axios.post('/api/logout')
    } catch (error) {
      console.error('Error al cerrar sesión:', error)
    }
  }

  if (mostrarDosPasos) {
    return (
      <AutenticacionDosPasos
        onVerificacionExitosa={manejarVerificacionExitosa}
        correoUsuario={usuarioEmail}
        onCancelar={manejarCancelarDosPasos}
      />
    )
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