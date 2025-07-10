import { useState, useEffect, useRef } from 'react'
import axios from '../lib/axios'
import './AutenticacionDosPasos.css'

export default function AutenticacionDosPasos({ onVerificacionExitosa, correoUsuario, onCancelar }) {
  const [codigo, setCodigo] = useState(['', '', '', '', '', ''])
  const [error, setError] = useState('')
  const [mensaje, setMensaje] = useState('')
  const [correoOculto, setCorreoOculto] = useState('')
  const [tiempoRestante, setTiempoRestante] = useState(600)
  const [enviandoCodigo, setEnviandoCodigo] = useState(false)
  const [verificandoCodigo, setVerificandoCodigo] = useState(false)
  const inputsRef = useRef([])

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
    const codigoCompleto = codigo.join('')
    if (codigoCompleto.length !== 6) {
      setError('El código debe tener 6 dígitos')
      return
    }

    setVerificandoCodigo(true)
    try {
      const response = await axios.post('/api/dos-pasos/verificar-codigo', { codigo: codigoCompleto })
      setMensaje('Código verificado correctamente')
      setError('')

      setTimeout(() => {
        onVerificacionExitosa(response.data.usuario, response.data.rol)
      }, 1000)
    } catch (err) {
      console.error('Error al verificar código:', err)
      setError(err.response?.data?.error || 'Error al verificar el código')
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

  const manejarCambio = (index, valor) => {
    if (!/^\d?$/.test(valor)) return

    const nuevoCodigo = [...codigo]
    nuevoCodigo[index] = valor
    setCodigo(nuevoCodigo)
    setError('')

    if (valor && index < 5) {
      inputsRef.current[index + 1]?.focus()
    }
  }

  const manejarRetroceso = (e, index) => {
    if (e.key === 'Backspace' && !codigo[index] && index > 0) {
      inputsRef.current[index - 1]?.focus()
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
          <div className="codigo-inputs">
            {codigo.map((valor, index) => (
              <input
                key={index}
                type="text"
                maxLength="1"
                value={valor}
                ref={el => inputsRef.current[index] = el}
                onChange={e => manejarCambio(index, e.target.value)}
                onKeyDown={e => manejarRetroceso(e, index)}
                disabled={verificandoCodigo}
              />
            ))}
          </div>

          <button
            onClick={verificarCodigo}
            disabled={codigo.join('').length !== 6 || verificandoCodigo}
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
