import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import './AuthPassword.css'

export default function RecuperarContrasena() {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [error, setError] = useState('')

  const handleSubmit = (e) => {
    e.preventDefault()

    if (!email) {
      setError('Debes ingresar tu correo electrónico.')
      return
    }

    navigate(`/cambiar-contrasena?email=${encodeURIComponent(email)}`)
  }

  return (
    <div className="login-container">
      <div className="login-card">
        <h2 className="login-title">Recuperar contraseña</h2>

        <form className="login-form" onSubmit={handleSubmit}>
          <input
            className="auth-input"
            type="email"
            placeholder="Correo electrónico"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
        />


          <button className="login-btn" type="submit">
            Aceptar
          </button>
        </form>

        {error && <p className="error-message">{error}</p>}
      </div>
    </div>
  )
}

