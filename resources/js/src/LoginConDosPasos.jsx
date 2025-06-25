import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from './axios'
import AutenticacionDosPasos from './AutenticacionDosPasos'

export default function Login({ setAuth, setRole }) {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [mostrarDosPasos, setMostrarDosPasos] = useState(false)
  const [usuarioEmail, setUsuarioEmail] = useState('')

  const handleLogin = async (e) => {
    e.preventDefault()

    // Limpiar estados previos antes de iniciar nuevo login
    setError('')
    setMostrarDosPasos(false)
    setUsuarioEmail('')

    try {
      // Paso 0: Cerrar sesión previa si existe (importante para evitar conflictos)
      await axios.post('/api/logout').catch(() => {}) // Ignorar errores si no hay sesión activa
      
      // Paso 1: solicitar token CSRF
      await axios.get('/sanctum/csrf-cookie')

      // Paso 2: login
      await axios.post('/api/login', { email, password }, { withCredentials: true })

      // Paso 3: obtener usuario autenticado
      const res = await axios.get('/api/user')
      const role = res.data.role?.descripcion
      const dosPasosHabilitado = res.data.dos_pasos_habilitado

      console.log('Respuesta del backend:', res.data);
      console.log('Rol recibido:', res.data.role);
      console.log('2FA habilitado:', dosPasosHabilitado);

      if (dosPasosHabilitado) {
        // Mostrar componente de 2FA
        setUsuarioEmail(res.data.email)
        setMostrarDosPasos(true)
      } else {
        // Login normal sin 2FA
        completarLogin(role)
      }

    } catch (err) {
      console.error('Error al iniciar sesión', err)
      setError('Credenciales inválidas o error de red.')
      // Limpiar estados en caso de error
      setMostrarDosPasos(false)
      setUsuarioEmail('')
    }
  }

  const completarLogin = (role) => {
    // Actualiza estado global
    setAuth(true)
    setRole(role)

    // Redireccionar
    if (role === 'admin') {
      navigate('/admin')
    } else {
      navigate('/dashboard')
    }
  }

  const manejarVerificacionExitosa = (usuario, rol) => {
    console.log('Verificación 2FA exitosa:', usuario, rol)
    completarLogin(rol)
  }

  const manejarCancelarDosPasos = () => {
    setMostrarDosPasos(false)
    setUsuarioEmail('')
    setError('')
    // Cerrar sesión del backend
    axios.post('/api/logout').catch(console.error)
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