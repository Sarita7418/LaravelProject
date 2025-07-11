import { useState } from 'react'
import axios from '../lib/axios'
import { useNavigate } from 'react-router-dom'
import './Registro.css'

export default function Registro() {
  const [formData, setFormData] = useState({
    nombres: '',
    apellido_paterno: '',
    apellido_materno: '',
    ci: '',
    telefono: '',
    fecha_nacimiento: '',
    email: '',
    password: ''
  })

  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const navigate = useNavigate()

  const handleInputChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)

    try {
      await axios.post('/api/personas', formData)
      alert('Registro exitoso. Ahora puedes iniciar sesión.')
      navigate('/login')
    } catch (err) {
      setError('Hubo un error al registrar. Intenta de nuevo.')
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="registro-container">
      <h2>Registro</h2>
      <form className="registro-form" onSubmit={handleSubmit}>
        <input name="nombres" placeholder="Nombres" value={formData.nombres} onChange={handleInputChange} required />
        <input name="apellido_paterno" placeholder="Apellido paterno" value={formData.apellido_paterno} onChange={handleInputChange} required />
        <input name="apellido_materno" placeholder="Apellido materno" value={formData.apellido_materno} onChange={handleInputChange} />
        <input name="ci" placeholder="CI" value={formData.ci} onChange={handleInputChange} required />
        <input name="telefono" placeholder="Teléfono" value={formData.telefono} onChange={handleInputChange} />
        <input name="fecha_nacimiento" type="date" placeholder="Fecha de nacimiento" value={formData.fecha_nacimiento} onChange={handleInputChange} />

        <input name="email" type="email" placeholder="Correo electrónico" value={formData.email} onChange={handleInputChange} required />
        <input name="password" type="password" placeholder="Contraseña" value={formData.password} onChange={handleInputChange} required />

        {error && <p className="error">{error}</p>}

        <button type="submit" disabled={loading}>
          {loading ? 'Registrando...' : 'Registrarse'}
        </button>
      </form>
    </div>
  )
}
