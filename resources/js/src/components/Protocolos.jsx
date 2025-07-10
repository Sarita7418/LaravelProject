import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Protocolos.css'

function Protocolos() {
  const [protocolos, setProtocolos] = useState([])
  const [catalogos, setCatalogos] = useState(null)
  const [formVisible, setFormVisible] = useState(false)
  const [titulo, setTitulo] = useState('')
  const [resumen, setResumen] = useState('')
  const [objetivoGeneral, setObjetivoGeneral] = useState('')
  const [metodologia, setMetodologia] = useState('')
  const [justificacion, setJustificacion] = useState('')
  const [idEspecialidad, setIdEspecialidad] = useState('')
  const [nuevaEspecialidad, setNuevaEspecialidad] = useState('')
  const [idEstado, setIdEstado] = useState('')
  const [idAreaImpacto, setIdAreaImpacto] = useState('')
  const [nuevaArea, setNuevaArea] = useState('')
  const [loading, setLoading] = useState(false)
  const [protocoloEditando, setProtocoloEditando] = useState(null)
  const [vistaActual, setVistaActual] = useState('activos')

  useEffect(() => {
    axios.get('/sanctum/csrf-cookie').then(() => {
      fetchProtocolos()
      fetchCatalogos()
    })
  }, [])

  const fetchProtocolos = async () => {
    try {
      const res = await axios.get('/api/protocolos')
      setProtocolos(res.data)
    } catch (error) {}
  }

  const fetchCatalogos = async () => {
    try {
      const res = await axios.get('/api/protocolos/catalogos')
      setCatalogos(res.data)
    } catch (error) {}
  }

  const cambiarEstadoProtocolo = async (id, activar) => {
    try {
      if (activar) {
        await axios.put(`/api/protocolos/${id}/reactivar`)
      } else {
        await axios.put(`/api/protocolos/${id}/archivar`)
      }
      await fetchProtocolos()
      alert(`Protocolo ${activar ? 'reactivado' : 'archivado'} correctamente`)
    } catch (error) {
      alert('Error al cambiar estado')
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      const payload = {
        titulo,
        resumen,
        objetivo_general: objetivoGeneral,
        metodologia,
        justificacion,
        id_especialidad: idEspecialidad === 'nueva' ? null : idEspecialidad,
        nueva_especialidad: idEspecialidad === 'nueva' ? nuevaEspecialidad : '',
        id_estado: idEstado,
        id_area_impacto: idAreaImpacto === 'nueva' ? null : idAreaImpacto,
        nueva_area: idAreaImpacto === 'nueva' ? nuevaArea : '',
        id_usuario_creador: catalogos?.usuario_autenticado?.id
      }

      Object.keys(payload).forEach(key => {
        if (payload[key] === '' || payload[key] === null) {
          delete payload[key]
        }
      })

      if (protocoloEditando) {
        await axios.put(`/api/protocolos/${protocoloEditando}`, payload)
      } else {
        await axios.post('/api/protocolos', payload)
      }

      resetFormulario()
      fetchProtocolos()
      alert(`Protocolo ${protocoloEditando ? 'actualizado' : 'creado'} correctamente`)
    } catch (error) {
      if (error.response?.data?.errors) {
        const errores = Object.values(error.response.data.errors).flat()
        alert('Errores de validación:\n' + errores.join('\n'))
      } else {
        alert('Ocurrió un error al guardar el protocolo')
      }
    } finally {
      setLoading(false)
    }
  }

  const iniciarEdicion = (protocolo) => {
    setTitulo(protocolo.titulo)
    setResumen(protocolo.resumen)
    setObjetivoGeneral(protocolo.objetivo_general)
    setMetodologia(protocolo.metodologia)
    setJustificacion(protocolo.justificacion)
    setIdEspecialidad(protocolo.id_especialidad)
    setIdEstado(protocolo.id_estado)
    setIdAreaImpacto(protocolo.id_area_impacto)
    setProtocoloEditando(protocolo.id)
    setFormVisible(true)
  }

  const resetFormulario = () => {
    setFormVisible(false)
    setTitulo('')
    setResumen('')
    setObjetivoGeneral('')
    setMetodologia('')
    setJustificacion('')
    setIdEspecialidad('')
    setNuevaEspecialidad('')
    setIdEstado('')
    setIdAreaImpacto('')
    setNuevaArea('')
    setProtocoloEditando(null)
  }

  const getProtocolosFiltrados = () => {
    if (!protocolos || protocolos.length === 0) return []

    return protocolos.filter((p) => {
      const estado = (p.estado?.descripcion || '').toLowerCase().trim()

      switch (vistaActual) {
        case 'activos':
          return estado === 'activo'
        case 'enRevision':
          return estado === 'en revision' || estado === 'en revisión'
        case 'validados':
          return estado === 'validado'
        case 'archivados':
          return estado === 'archivado'
        default:
          return false
      }
    })
  }

  const contarProtocolos = (tipo) => {
    if (!protocolos || protocolos.length === 0) return 0

    return protocolos.filter((p) => {
      const estado = (p.estado?.descripcion || '').toLowerCase().trim()

      switch (tipo) {
        case 'activos':
          return estado === 'activo'
        case 'enRevision':
          return estado === 'en revision' || estado === 'en revisión'
        case 'validados':
          return estado === 'validado'
        case 'archivados':
          return estado === 'archivado'
        default:
          return false
      }
    }).length
  }

  const protocolosFiltrados = getProtocolosFiltrados()

  return (
    <div className="protocolos-container">
      <h2 className="protocolos-title">Protocolos</h2>

      <div className="toggle-container">
        <button 
          className={`toggle-btn ${vistaActual === 'activos' ? 'active' : ''}`}
          onClick={() => setVistaActual('activos')}
        >
          Activos ({contarProtocolos('activos')})
        </button>
        <button 
          className={`toggle-btn ${vistaActual === 'enRevision' ? 'active' : ''}`}
          onClick={() => setVistaActual('enRevision')}
        >
          En Revisión ({contarProtocolos('enRevision')})
        </button>
        <button 
          className={`toggle-btn ${vistaActual === 'validados' ? 'active' : ''}`}
          onClick={() => setVistaActual('validados')}
        >
          Validados ({contarProtocolos('validados')})
        </button>
        <button 
          className={`toggle-btn ${vistaActual === 'archivados' ? 'active' : ''}`}
          onClick={() => setVistaActual('archivados')}
        >
          Archivados ({contarProtocolos('archivados')})
        </button>
      </div>

      <table className="protocolos-table">
        <thead>
          <tr>
            <th>Título</th>
            <th>Resumen</th>
            <th>Especialidad</th>
            <th>Estado</th>
            <th>Área</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {protocolosFiltrados.length === 0 ? (
            <tr>
              <td colSpan="6" style={{ textAlign: 'center', padding: '20px' }}>
                No hay protocolos para mostrar en {vistaActual}
              </td>
            </tr>
          ) : (
            protocolosFiltrados.map(protocolo => (
              <tr key={protocolo.id}>
                <td>{protocolo.titulo}</td>
                <td>{protocolo.resumen}</td>
                <td>{protocolo.especialidad?.nombre || 'N/A'}</td>
                <td>{protocolo.estado?.descripcion || `Estado ID: ${protocolo.id_estado}`}</td>
                <td>{protocolo.area_impacto?.descripcion || 'N/A'}</td>
                <td>
                  {vistaActual === 'archivados' ? (
                    <button 
                      className="reactivate-btn" 
                      onClick={() => cambiarEstadoProtocolo(protocolo.id, true)}
                    >
                      Reactivar
                    </button>
                  ) : (
                    <>
                      <button 
                        className="edit-btn" 
                        onClick={() => iniciarEdicion(protocolo)}
                      >
                        Editar
                      </button>
                      <button 
                        className="delete-btn" 
                        onClick={() => cambiarEstadoProtocolo(protocolo.id, false)}
                      >
                        Archivar
                      </button>
                    </>
                  )}
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>

      {vistaActual !== 'archivados' && !formVisible && (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Protocolo
        </button>
      )}

      {formVisible && (
        <form className="form-container" onSubmit={handleSubmit}>
          <h3>{protocoloEditando ? 'Editar Protocolo' : 'Nuevo Protocolo'}</h3>
          
          <label className="form-label">Título</label>
          <input
            type="text"
            value={titulo}
            onChange={(e) => setTitulo(e.target.value)}
            className="form-input"
            required
          />

          <label className="form-label">Resumen</label>
          <textarea
            value={resumen}
            onChange={(e) => setResumen(e.target.value)}
            className="form-input"
            required
          />

          <label className="form-label">Objetivo General</label>
          <textarea
            value={objetivoGeneral}
            onChange={(e) => setObjetivoGeneral(e.target.value)}
            className="form-input"
            required
          />

          <label className="form-label">Metodología</label>
          <textarea
            value={metodologia}
            onChange={(e) => setMetodologia(e.target.value)}
            className="form-input"
            placeholder="Ingrese la metodología"
            required
          />

          <label className="form-label">Justificación</label>
          <textarea
            value={justificacion}
            onChange={(e) => setJustificacion(e.target.value)}
            className="form-input"
            required
          />

          <label className="form-label">Especialidad</label>
          <select
            value={idEspecialidad}
            onChange={(e) => setIdEspecialidad(e.target.value)}
            className="form-input"
            required
          >
            <option value="">Seleccione...</option>
            {catalogos?.especialidades?.map(e => (
              <option key={e.id} value={e.id}>{e.nombre}</option>
            ))}
            <option value="nueva">Nueva especialidad...</option>
          </select>
          {idEspecialidad === 'nueva' && (
            <input
              type="text"
              value={nuevaEspecialidad}
              onChange={(e) => setNuevaEspecialidad(e.target.value)}
              className="form-input"
              placeholder="Ingrese nueva especialidad"
              required
            />
          )}

          <label className="form-label">Estado</label>
          <select
            value={idEstado}
            onChange={(e) => setIdEstado(e.target.value)}
            className="form-input"
            required
          >
            <option value="">Seleccione...</option>
            {catalogos?.estados?.map(e => (
              <option key={e.id} value={e.id}>{e.descripcion}</option>
            ))}
          </select>

          <label className="form-label">Área de Impacto</label>
          <select
            value={idAreaImpacto}
            onChange={(e) => setIdAreaImpacto(e.target.value)}
            className="form-input"
            required
          >
            <option value="">Seleccione...</option>
            {catalogos?.areasImpacto?.map(a => (
              <option key={a.id} value={a.id}>{a.descripcion}</option>
            ))}
            <option value="nueva">Nueva área...</option>
          </select>
          {idAreaImpacto === 'nueva' && (
            <input
              type="text"
              value={nuevaArea}
              onChange={(e) => setNuevaArea(e.target.value)}
              className="form-input"
              placeholder="Ingrese nueva área"
              required
            />
          )}

          <div className="form-actions">
            <button
              type="submit"
              className="create-btn"
              disabled={loading}
            >
              {loading ? 'Guardando...' : protocoloEditando ? 'Actualizar' : 'Crear'}
            </button>
            <button 
              type="button"
              className="cancel-btn" 
              onClick={resetFormulario}
              disabled={loading}
            >
              Cancelar
            </button>
          </div>
        </form>
      )}
    </div>
  )
}

export default Protocolos
