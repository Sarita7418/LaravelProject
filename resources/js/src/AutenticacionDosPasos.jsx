import { useState, useEffect } from 'react'
import axios from './axios'

export default function AutenticacionDosPasos({ onVerificacionExitosa, correoUsuario, onCancelar }) {
  const [codigo, setCodigo] = useState('')
  const [error, setError] = useState('')
  const [mensaje, setMensaje] = useState('')
  const [correoOculto, setCorreoOculto] = useState('')
  const [tiempoRestante, setTiempoRestante] = useState(600) 
  const [enviandoCodigo, setEnviandoCodigo] = useState(false)
  const [verificandoCodigo, setVerificandoCodigo] = useState(false)

  useEffect(() => {
    if (tiempoRestante > 0) {
      const timer = setTimeout(() => setTiempoRestante(tiempoRestante - 1), 1000)
      return () => clearTimeout(timer)
    }
  }, [tiempoRestante])

  const enviarCodigo = async () => {
    setEnviandoCodigo(true)
    try {
      const response = await axios.post('/api/dos-pasos/enviar-codigo')
      setCorreoOculto(response.data.correo_parcial)
      setMensaje('Código enviado a tu correo electrónico')
      setError('')
      setTiempoRestante(600) 
    } catch (err) {
      console.error('Error al enviar código:', err)
      setError('Error al enviar el código. Inténtalo nuevamente.')
    } finally {
      setEnviandoCodigo(false)
    }
  }

  const verificarCodigo = async () => {
    if (codigo.length !== 6) {
      setError('El código debe tener 6 dígitos')
      return
    }

    setVerificandoCodigo(true)
    try {
      const response = await axios.post('/api/dos-pasos/verificar-codigo', { codigo })
      setMensaje('Código verificado correctamente')
      setError('')

      setTimeout(() => {
        onVerificacionExitosa(response.data.usuario, response.data.rol)
      }, 1000)
    } catch (err) {
      console.error('Error al verificar código:', err)
      if (err.response?.data?.error) {
        setError(err.response.data.error)
      } else {
        setError('Error al verificar el código')
      }
    } finally {
      setVerificandoCodigo(false)
    }
  }

  const cancelarProceso = async () => {
    try {
      await axios.post('/api/dos-pasos/deshabilitar') 
    } catch (err) {
      console.warn('Error al cancelar el código:', err)
    } finally {
      onCancelar() 
    }
  }

  const formatearTiempo = (segundos) => {
    const minutos = Math.floor(segundos / 60)
    const segs = segundos % 60
    return `${minutos}:${segs.toString().padStart(2, '0')}`
  }

  const manejarCambioCodigo = (e) => {
    const valor = e.target.value.replace(/\D/g, '')
    if (valor.length <= 6) {
      setCodigo(valor)
      setError('')
    }
  }

  return (
    <div>
      <h2>Verificación en Dos Pasos</h2>

      <div>
        <p>📧 Enviaremos el código a:</p>
        <p>{correoOculto || correoUsuario}</p>
      </div>

      {!correoOculto && (
        <button onClick={enviarCodigo} disabled={enviandoCodigo}>
          {enviandoCodigo ? 'Enviando...' : 'Enviar código'}
        </button>
      )}

      {correoOculto && (
        <>
          <div>
            <label>Código de 6 dígitos:</label>
            <input
              type="text"
              value={codigo}
              onChange={manejarCambioCodigo}
              placeholder="123456"
              maxLength="6"
              disabled={verificandoCodigo}
            />
          </div>

          <button 
            onClick={verificarCodigo}
            disabled={codigo.length !== 6 || verificandoCodigo}
          >
            {verificandoCodigo ? 'Verificando...' : 'Verificar Código'}
          </button>

          <div>
            {tiempoRestante > 0 ? (
              <p>Tiempo restante: {formatearTiempo(tiempoRestante)}</p>
            ) : (
              <button onClick={enviarCodigo} disabled={enviandoCodigo}>
                {enviandoCodigo ? 'Enviando...' : 'Reenviar código'}
              </button>
            )}
          </div>
        </>
      )}

      <button onClick={cancelarProceso}>Cancelar</button>

      {error && <div style={{ color: 'red' }}>{error}</div>}
      {mensaje && !error && <div style={{ color: 'green' }}>{mensaje}</div>}
    </div>
  )
}
