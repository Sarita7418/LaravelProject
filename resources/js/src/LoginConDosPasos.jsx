import { useState, useEffect } from 'react'
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

  useEffect(() => {
    const limpiarEstado = async () => {
      setMostrarDosPasos(false)
      setUsuarioEmail('')
      setError('')
      if (setPendingTwoFactor) {
        setPendingTwoFactor(false)
      }

      try {
        await axios.post('/api/logout')
      } catch (error) {
      }
    }

    limpiarEstado()
  }, [setPendingTwoFactor])

  const extraerRutasDesdePermisos = (permisos) => {
    if (!Array.isArray(permisos)) return []

    if (typeof permisos[0] === 'string') {
      return permisos
    }

    return permisos.map(p => p.ruta).filter(Boolean)
  }

  const handleLogin = async (e) => {
    e.preventDefault()

    try {
      await axios.get('/sanctum/csrf-cookie')
      await axios.post('/api/login', { email, password }, { withCredentials: true })

      const res = await axios.get('/api/user')
      console.log('Respuesta completa del backend:', res.data)

      const permisos = res.data.permisos
      const rutas = extraerRutasDesdePermisos(permisos)

      setUsuarioEmail(res.data.email)
      setMostrarDosPasos(true)
      if (setPendingTwoFactor) {
        setPendingTwoFactor(true)
      }

    } catch (err) {
      console.error('Error al iniciar sesión', err)
      
      if (err.response && err.response.status === 403) {
        setError(err.response.data.message || 'Tu cuenta está inactiva.')
      } else {
        setError('Credenciales inválidas o error de red.')
      }
    }
  }

  const completarLogin = (rutas) => {
    setAuth(true)
    setPermisos(rutas)

    if (setPendingTwoFactor) {
      setPendingTwoFactor(false)
    }

    if (rutas.includes('/admin')) {
      navigate('/admin')
    } else if (rutas.includes('/dashboard')) {
      navigate('/dashboard')
    } else {
      navigate('/unauthorized')
    }
  }

  const manejarVerificacionExitosa = async () => {
    console.log('Verificación 2FA exitosa')

    try {
      const res = await axios.get('/api/user')
      const permisos = res.data.permisos
      const rutas = extraerRutasDesdePermisos(permisos)

      completarLogin(rutas)
    } catch (error) {
      console.error('Error al obtener usuario después del 2FA:', error)
      setError('Error al cargar los datos del usuario.')
      navigate('/login')
    }
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

  const handleEmailChange = (e) => {
    setEmail(e.target.value)
    if (mostrarDosPasos) {
      setMostrarDosPasos(false)
      setUsuarioEmail('')
      setError('')
      if (setPendingTwoFactor) {
        setPendingTwoFactor(false)
      }
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
          onChange={handleEmailChange}
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