import { useState, useEffect } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import axios from '../lib/axios'
import AutenticacionDosPasos from './AutenticacionDosPasos'

export default function CambiarContrasena() {
  const navigate = useNavigate()
  const location = useLocation()

  // Extrae email de la query string ?email=
  const queryParams = new URLSearchParams(location.search)
  const correoInicial = queryParams.get('email') || ''

  const [email] = useState(correoInicial)
  const [password, setPassword] = useState('')
  const [passwordConfirm, setPasswordConfirm] = useState('')
  const [error, setError] = useState('')
  const [mostrarDosPasos, setMostrarDosPasos] = useState(!!correoInicial)
  const [verificacionExitosa, setVerificacionExitosa] = useState(false)
  const [correoOculto, setCorreoOculto] = useState('')

  useEffect(() => {
    const prepararSesion = async () => {
      try {
        await axios.post('/api/logout')
        await axios.get('/sanctum/csrf-cookie')
        
        // Si hay un email, iniciamos el proceso de recuperación
        if (correoInicial) {
          try {
            const response = await axios.post(
              '/api/reset-password/enviar-codigo', 
              { email: correoInicial },
              { withCredentials: true }
            )
            setCorreoOculto(response.data.correo_parcial || correoInicial)
          } catch (err) {
            console.error('Error al enviar código:', err.response?.data || err)
            setError('Error al enviar código de verificación')
          }
        }
      } catch (err) {
        console.error('Error al preparar sesión:', err)
      }
    }
    prepararSesion()
  }, [correoInicial])

  const manejarVerificacionExitosa = () => {
    setMostrarDosPasos(false)
    setVerificacionExitosa(true)
  }

  const manejarCancelarDosPasos = () => {
    setMostrarDosPasos(false)
    setError('')
    navigate('/login')
  }

  const handleCambiarContrasena = async (e) => {
    e.preventDefault()
    setError('')

    if (!password || !passwordConfirm) {
      setError('Debes ingresar y confirmar la contraseña.')
      return
    }
    if (password !== passwordConfirm) {
      setError('Las contraseñas no coinciden.')
      return
    }

    try {
      const response = await axios.post(
        '/api/reset-password',
        {
          email,
          password,
          password_confirmation: passwordConfirm,
          token: '',
        },
        { withCredentials: true }
      )
      
      alert(response.data.message || 'Contraseña cambiada correctamente, por favor inicia sesión.')
      navigate('/login')
    } catch (err) {
      setError(err.response?.data?.message || 'Error al cambiar la contraseña.')
      console.error('Error al cambiar contraseña:', err.response?.data || err)
    }
  }

  if (mostrarDosPasos) {
    return (
      <AutenticacionDosPasos
        correoUsuario={correoOculto || email}
        onVerificacionExitosa={manejarVerificacionExitosa}
        onCancelar={manejarCancelarDosPasos}
        esRecuperacion={true}
      />
    )
  }

  if (verificacionExitosa) {
    return (
      <div>
        <h2>Cambiar Contraseña</h2>
        <form onSubmit={handleCambiarContrasena}>
          <input
            type="password"
            placeholder="Nueva contraseña"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <br />
          <input
            type="password"
            placeholder="Confirmar nueva contraseña"
            value={passwordConfirm}
            onChange={(e) => setPasswordConfirm(e.target.value)}
            required
          />
          <br />
          <button type="submit">Cambiar contraseña</button>
          {error && <p style={{ color: 'red' }}>{error}</p>}
        </form>
      </div>
    )
  }

  return (
    <div>
      <h2>Cambiar Contraseña</h2>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <p>No se recibió correo para la recuperación.</p>
    </div>
  )
}