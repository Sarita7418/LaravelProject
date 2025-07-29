import { useState, useEffect, useRef } from 'react'
import axios from '../lib/axios'
import './AutenticacionDosPasos.css'

export default function AutenticacionDosPasos({ 
  onVerificacionExitosa, 
  correoUsuario, 
  onCancelar,
  esRecuperacion = false 
}) {
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
      const endpoint = esRecuperacion 
        ? '/api/reset-password/enviar-codigo' 
        : '/api/dos-pasos/enviar-codigo'
      
      const data = esRecuperacion ? { email: correoUsuario } : {}
      
      const response = await axios.post(endpoint, data, { withCredentials: true })
      setCorreoOculto(response.data.correo_parcial || correoUsuario)
      setMensaje('Código enviado a tu correo electrónico')
      setError('')
      setTiempoRestante(600)
    } catch (err) {
      setError(err.response?.data?.error || 'Error al enviar el código. Inténtalo nuevamente.')
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
      const endpoint = esRecuperacion
        ? '/api/reset-password/verificar-codigo'
        : '/api/dos-pasos/verificar-codigo'
      
      const data = esRecuperacion
        ? { email: correoUsuario, codigo: codigoCompleto }
        : { codigo: codigoCompleto }

      const response = await axios.post(endpoint, data, { withCredentials: true })
      setMensaje('Código verificado correctamente')
      setError('')
      
      if (esRecuperacion) {
        onVerificacionExitosa()
      } else {
        onVerificacionExitosa(response.data.usuario, response.data.rol)
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Error al verificar el código')
    } finally {
      setVerificandoCodigo(false)
    }
  }

  const cancelarProceso = async () => {
    try {
      if (!esRecuperacion) {
        await axios.post('/api/dos-pasos/deshabilitar', {}, { withCredentials: true })
      }
    } catch (_) {
      // No mostrar nada en consola
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
    <div className="dos-pasos-container">
      <h2>Verificación en Dos Pasos</h2>

      <div className="correo-info">
        <p>📧 Enviaremos el código de verificacion al correo vinculado a tu usuario</p>
      </div>

      {!correoOculto && (
        <button 
          onClick={enviarCodigo} 
          disabled={enviandoCodigo}
          className="btn-enviar"
        >
          {enviandoCodigo ? 'Enviando...' : 'Enviar código'}
        </button>
      )}

      {correoOculto && (
        <>
          <div className="codigo-inputs" onPaste={(e) => {
            const paste = e.clipboardData.getData('text');
            if (/^\d{6}$/.test(paste)) {
              setCodigo(paste.split('').slice(0, 6));
              inputsRef.current[5]?.focus();
            }
          }}>
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
                className="codigo-input"
                autoFocus={index === 0 && !codigo.join('')}
                inputMode="numeric"
              />
            ))}
          </div>

          <button
            onClick={verificarCodigo}
            disabled={codigo.join('').length !== 6 || verificandoCodigo}
            className="btn-verificar"
          >
            {verificandoCodigo ? 'Verificando...' : 'Verificar Código'}
          </button>

          <div className="tiempo-container">
            {tiempoRestante > 0 ? (
              <p>Tiempo restante: {formatearTiempo(tiempoRestante)}</p>
            ) : (
              <button 
                onClick={enviarCodigo} 
                disabled={enviandoCodigo}
                className="btn-reenviar"
              >
                {enviandoCodigo ? 'Enviando...' : 'Reenviar código'}
              </button>
            )}
          </div>
        </>
      )}

      <button 
        onClick={cancelarProceso}
        className="btn-cancelar"
      >
        Cancelar
      </button>

      {error && <div className="mensaje-error">{error}</div>}
      {mensaje && !error && <div className="mensaje-exito">{mensaje}</div>}
    </div>
  )
}
