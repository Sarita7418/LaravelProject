import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { Link } from 'react-router-dom';

import axios from '../lib/axios'
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
    setError('') 

    try {
      await axios.get('/sanctum/csrf-cookie')
      const loginResponse = await axios.post(
        '/api/login',
        { email, password },
        { withCredentials: true }
      )

      if (loginResponse.data.error) {
        setError(loginResponse.data.error)
        return
      }

      const res = await axios.get('/api/user')

      const permisos = res.data.permisos
      const rutas = extraerRutasDesdePermisos(permisos)

      setUsuarioEmail(res.data.email)
      setMostrarDosPasos(true)
      if (setPendingTwoFactor) {
        setPendingTwoFactor(true)
      }

    } catch (err) {
      if (err.response) {
        if (err.response.status === 403) {
          setError(err.response.data.message || 'Tu cuenta está inactiva.')
        } else if (err.response.status === 422) {
          setError('Credenciales inválidas. Verifica tu email y contraseña.')
        } else {
          setError(err.response.data.message || 'Error al iniciar sesión.')
        }
      } else {
        setError('Error de conexión. Verifica tu conexión a internet.')
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

    try {
      const res = await axios.get('/api/user')
      const permisos = res.data.permisos
      const rutas = extraerRutasDesdePermisos(permisos)

      completarLogin(rutas)
    } catch (error) {
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
      {error && <div style={{ color: 'red', margin: '10px 0' }}>{error}</div>}
      <form onSubmit={handleLogin}>
        <input
          type="email"
          placeholder="Correo"
          value={email}
          onChange={handleEmailChange}
          required
        /><br />
        <input
          type="password"
          placeholder="Contraseña"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        /><br />
        <button type="submit">Entrar</button>
      </form>
      <label
        style={{ color: 'gray', cursor: 'pointer', marginLeft: '10px', userSelect: 'none' }}
        onClick={() => {
          const emailParam = encodeURIComponent(email)
          navigate(`/cambiar-contrasena?email=${emailParam}`)
        }}
      >
        ¿Olvidaste tu contraseña?
      </label>

      <p className="registro-enlace">
        ¿No tienes cuenta? <Link to="/registro">Regístrate aquí</Link>
      </p>

    </div>
  )
}
