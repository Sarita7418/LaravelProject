import { useState, useEffect } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import axios from '../lib/axios'
import './AuthPassword.css'
import AutenticacionDosPasos from './AutenticacionDosPasos'

export default function CambiarContrasena() {
  const navigate = useNavigate()
  const location = useLocation()

  const queryParams = new URLSearchParams(location.search)
  const correoInicial = queryParams.get('email') || ''

  const [email] = useState(correoInicial)
  const [password, setPassword] = useState('')
  const [passwordConfirm, setPasswordConfirm] = useState('')
  const [error, setError] = useState('')
  const [mostrarDosPasos, setMostrarDosPasos] = useState(!!correoInicial)
  const [verificacionExitosa, setVerificacionExitosa] = useState(false)
  const [correoOculto, setCorreoOculto] = useState('')
  const [mostrarModal, setMostrarModal] = useState(false)

  useEffect(() => {
    if (!correoInicial) {
      navigate('/recuperar-contrasena')
    }
  }, [correoInicial, navigate])

  useEffect(() => {
    const prepararSesion = async () => {
      try {
        await axios.post('/api/logout')
        await axios.get('/sanctum/csrf-cookie')

        if (correoInicial) {
          try {
            const response = await axios.post(
              '/api/reset-password/enviar-codigo',
              { email: correoInicial },
              { withCredentials: true }
            )
            setCorreoOculto(response.data.correo_parcial || correoInicial)
          } catch {
            setError('Error al enviar código de verificación')
          }
        }
      } catch {

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
      await axios.post(
        '/api/reset-password',
        {
          email,
          password,
          password_confirmation: passwordConfirm,
          token: '',
        },
        { withCredentials: true }
      )

      setMostrarModal(true)
    } catch (err) {
      setError(err.response?.data?.message || 'Error al cambiar la contraseña.')
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
      <div className="login-container">
        <div className="login-card">
          <h2 className="login-title">Cambiar contraseña</h2>

          <form className="login-form" onSubmit={handleCambiarContrasena}>
            <input
              className="form-input"
              type="password"
              placeholder="Nueva contraseña"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />

            <input
              className="form-input"
              type="password"
              placeholder="Confirmar nueva contraseña"
              value={passwordConfirm}
              onChange={(e) => setPasswordConfirm(e.target.value)}
              required
            />

            <button className="login-btn" type="submit">
              Cambiar contraseña
            </button>

            {error && <p className="error-message">{error}</p>}
          </form>
        </div>

        {mostrarModal && (
          <div className="auth-modal-overlay">
            <div className="auth-modal">
              <h3>Contraseña actualizada</h3>
              <p>
                Tu contraseña fue cambiada correctamente.
                Ahora puedes iniciar sesión.
              </p>
              <button
                onClick={() => {
                  setMostrarModal(false)
                  navigate('/login')
                }}
              >
                Aceptar
              </button>
            </div>
          </div>
        )}
      </div>
    )
  }

  return null
}



