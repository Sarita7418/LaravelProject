import React, { useEffect, useState } from 'react';
import axios from '../lib/axios';
import './Protocolos.css';

function Protocolos() {
  const [protocolos, setProtocolos] = useState([]);
  const [catalogos, setCatalogos] = useState(null);
  const [formVisible, setFormVisible] = useState(false);

  const [titulo, setTitulo] = useState('');
  const [resumen, setResumen] = useState('');
  const [objetivoGeneral, setObjetivoGeneral] = useState('');
  const [metodologia, setMetodologia] = useState('');
  const [justificacion, setJustificacion] = useState('');
  const [idEspecialidad, setIdEspecialidad] = useState('');
  const [nuevaEspecialidad, setNuevaEspecialidad] = useState('');
  const [idEstado, setIdEstado] = useState('');
  const [idEstadoOriginal, setIdEstadoOriginal] = useState('');
  const [areasSeleccionadas, setAreasSeleccionadas] = useState([]);
  const [nuevaAreaNombre, setNuevaAreaNombre] = useState('');
  const [nuevaAreaDescripcion, setNuevaAreaDescripcion] = useState('');
  const [areaSeleccionada, setAreaSeleccionada] = useState('');

  const [loading, setLoading] = useState(false);
  const [protocoloEditando, setProtocoloEditando] = useState(null);
  const [vistaActual, setVistaActual] = useState('activos');
  const [protocoloDetalle, setProtocoloDetalle] = useState(null);
  const [error, setError] = useState(null);

  const [mostrarConfirmacionEstado, setMostrarConfirmacionEstado] = useState(false);
  const [estadoTemporal, setEstadoTemporal] = useState('');

  const [accionesPermitidas, setAccionesPermitidas] = useState([]);
  const puede = (accion) => accionesPermitidas.includes(accion);

  useEffect(() => {
    const fetchData = async () => {
      try {
        await axios.get('/sanctum/csrf-cookie');
        await Promise.all([fetchProtocolos(), fetchCatalogos(), fetchAccionesUsuario()]);
      } catch (error) {
        console.error('Error inicializando:', error);
        setError('Error al cargar datos iniciales');
      }
    };
    fetchData();
  }, []);

  const fetchAccionesUsuario = async () => {
    try {
      const userRes = await axios.get('/api/user');
      const userId = userRes.data.id;
      const accionesRes = await axios.get(`/api/acciones/${userId}`);
      const accionesFiltradas = accionesRes.data
        .filter((a) => a.menu_item === 'Protocolos')
        .map((a) => a.accion);
      setAccionesPermitidas(accionesFiltradas);
    } catch (error) {
      console.error('Error al obtener las acciones del usuario:', error);
    }
  };

  const fetchProtocolos = async () => {
    try {
      const { data } = await axios.get('/api/protocolos');
      setProtocolos(data);
    } catch (error) {
      console.error('Error fetching protocolos:', error);
      setError('Error al cargar protocolos');
    }
  };

  const fetchCatalogos = async () => {
    try {
      const { data } = await axios.get('/api/protocolos/catalogos');
      setCatalogos(data);
    } catch (error) {
      console.error('Error fetching catalogos:', error);
      setError('Error al cargar catálogos');
    }
  };

  const cambiarEstadoProtocolo = async (id, activar) => {
    try {
      setLoading(true);
      const endpoint = activar ? 'reactivar' : 'archivar';
      await axios.put(`/api/protocolos/${id}/${endpoint}`);
      await fetchProtocolos();
      alert(`Protocolo ${activar ? 'reactivado' : 'archivado'} correctamente`);
    } catch (error) {
      console.error('Error al cambiar estado:', error);
      alert('Error al cambiar estado');
    } finally {
      setLoading(false);
    }
  };

  const agregarArea = () => {
    if (areaSeleccionada === 'nueva') {
      if (nuevaAreaNombre && nuevaAreaDescripcion) {
        setAreasSeleccionadas([...areasSeleccionadas, {
          tipo: 'nueva',
          nombre: nuevaAreaNombre,
          descripcion: nuevaAreaDescripcion
        }]);
        setNuevaAreaNombre('');
        setNuevaAreaDescripcion('');
      } else {
        alert('Por favor complete nombre y descripción para la nueva área');
      }
    } else if (areaSeleccionada) {
      const areaExistente = catalogos.areasImpacto.find(a => a.id.toString() === areaSeleccionada);
      if (areaExistente && !areasSeleccionadas.some(a => a.id === areaExistente.id)) {
        setAreasSeleccionadas([...areasSeleccionadas, {
          tipo: 'existente',
          id: areaExistente.id,
          nombre: areaExistente.nombre,
          descripcion: areaExistente.descripcion
        }]);
      }
    }
    setAreaSeleccionada('');
  };

  const eliminarArea = (index) => {
    const nuevasAreas = [...areasSeleccionadas];
    nuevasAreas.splice(index, 1);
    setAreasSeleccionadas(nuevasAreas);
  };

  const manejarCambioEstado = (nuevoEstado) => {
    if (protocoloEditando && nuevoEstado !== idEstadoOriginal) {
      setEstadoTemporal(nuevoEstado);
      setMostrarConfirmacionEstado(true);
    } else {
      setIdEstado(nuevoEstado);
    }
  };

  const confirmarCambioEstado = () => {
    setIdEstado(estadoTemporal);
    setMostrarConfirmacionEstado(false);
    setEstadoTemporal('');
  };

  const cancelarCambioEstado = () => {
    setMostrarConfirmacionEstado(false);
    setEstadoTemporal('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const areasPayload = areasSeleccionadas.map(area => {
        if (area.tipo === 'nueva') {
          return { nueva_area: { nombre: area.nombre, descripcion: area.descripcion } };
        } else {
          return { id_area_impactos: area.id };
        }
      });

      const payload = {
        titulo,
        resumen,
        objetivo_general: objetivoGeneral,
        metodologia,
        justificacion,
        id_especialidad: idEspecialidad === 'nueva' ? null : idEspecialidad,
        nueva_especialidad: idEspecialidad === 'nueva' ? nuevaEspecialidad : '',
        areas_impacto: areasPayload,
      };

      if (protocoloEditando) {
        payload.id_estado = idEstado;
      }

      Object.keys(payload).forEach(k => {
        if (
          payload[k] === '' ||
          payload[k] === null ||
          (Array.isArray(payload[k]) && payload[k].length === 0)
        ) {
          delete payload[k];
        }
      });

      if (protocoloEditando) {
        await axios.put(`/api/protocolos/${protocoloEditando}`, payload);
      } else {
        await axios.post('/api/protocolos', payload);
      }

      resetFormulario();
      await fetchProtocolos();
      alert(`Protocolo ${protocoloEditando ? 'actualizado' : 'creado'} correctamente`);
    } catch (err) {
      console.error('Error al guardar protocolo:', err);
      if (err.response?.data?.errors) {
        const errs = Object.values(err.response.data.errors).flat();
        setError('Errores de validación:\n' + errs.join('\n'));
      } else {
        setError('Ocurrió un error al guardar el protocolo');
      }
    } finally {
      setLoading(false);
    }
  };

  const iniciarEdicion = (protocolo) => {
    setTitulo(protocolo.titulo);
    setResumen(protocolo.resumen);
    setObjetivoGeneral(protocolo.objetivo_general);
    setMetodologia(protocolo.metodologia);
    setJustificacion(protocolo.justificacion);
    setIdEspecialidad(protocolo.id_especialidad);
    setIdEstado(protocolo.id_estado);
    setIdEstadoOriginal(protocolo.id_estado);

    const areasFormateadas = protocolo.areas_impacto?.map(area => ({
      tipo: 'existente',
      id: area.id,
      nombre: area.nombre,
      descripcion: area.descripcion
    })) || [];

    setAreasSeleccionadas(areasFormateadas);
    setProtocoloEditando(protocolo.id);
    setFormVisible(true);
  };

  const resetFormulario = () => {
    setFormVisible(false);
    setTitulo('');
    setResumen('');
    setObjetivoGeneral('');
    setMetodologia('');
    setJustificacion('');
    setIdEspecialidad('');
    setNuevaEspecialidad('');
    setIdEstado('');
    setIdEstadoOriginal('');
    setAreasSeleccionadas([]);
    setNuevaAreaNombre('');
    setNuevaAreaDescripcion('');
    setAreaSeleccionada('');
    setProtocoloEditando(null);
    setError(null);
    setMostrarConfirmacionEstado(false);
    setEstadoTemporal('');
  };

  const getProtocolosFiltrados = () => {
    if (!protocolos.length) return [];
    return protocolos.filter((p) => {
      const estado = (p.estado?.descripcion || '').toLowerCase().trim();
      switch (vistaActual) {
        case 'activos':
          return estado === 'activo';
        case 'enRevision':
          return estado === 'en revision' || estado === 'en revisión';
        case 'archivados':
          return estado === 'archivado';
        default:
          return false;
      }
    });
  };

  const contarProtocolos = (tipo) => {
    if (!protocolos.length) return 0;
    return protocolos.filter((p) => {
      const estado = (p.estado?.descripcion || '').toLowerCase().trim();
      switch (tipo) {
        case 'activos':
          return estado === 'activo';
        case 'enRevision':
          return estado === 'en revision' || estado === 'en revisión';
        case 'archivados':
          return estado === 'archivado';
        default:
          return false;
      }
    }).length;
  };

  const protocolosFiltrados = getProtocolosFiltrados();

  if (error) {
    return (
      <div className="protocolos-container">
        <div className="error-message">{error}</div>
        {puede('activar') && (
          <button className="reactivate-btn" onClick={() => window.location.reload()}>
            Recargar
          </button>
        )}
      </div>
    );
  }

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
            <th>Áreas de Impacto</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {protocolosFiltrados.length === 0 ? (
            <tr>
              <td colSpan="4" style={{ textAlign: 'center', padding: '20px' }}>
                No hay protocolos para mostrar en {vistaActual}
              </td>
            </tr>
          ) : (
            protocolosFiltrados.map((p) => (
              <tr key={p.id}>
                <td>{p.titulo}</td>
                <td>
                  {p.areas_impacto?.length > 0 ? (
                    <ul className="areas-lista">
                      {p.areas_impacto.map((area) => (
                        <li key={area.id}>{area.nombre}</li>
                      ))}
                    </ul>
                  ) : (
                    'N/A'
                  )}
                </td>
                <td>{p.estado?.descripcion || `Estado ID: ${p.id_estado}`}</td>
                <td>
                  <button className="edit-btn" onClick={() => setProtocoloDetalle(p)}>
                    Ver más
                  </button>
                  {vistaActual !== 'archivados' ? (
                    <>
                      {puede('editar') && (
                        <button className="edit-btn" onClick={() => iniciarEdicion(p)}>
                          Editar
                        </button>
                      )}
                      {puede('activar') && (
                        <button className="delete-btn" onClick={() => cambiarEstadoProtocolo(p.id, false)}>
                          Archivar
                        </button>
                      )}
                    </>
                  ) : (
                    puede('activar') && (
                      <button className="reactivate-btn" onClick={() => cambiarEstadoProtocolo(p.id, true)}>
                        Reactivar
                      </button>
                    )
                  )}
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>

      {!formVisible && vistaActual !== 'archivados' && puede('crear') && (
        <button className="add-btn" onClick={() => setFormVisible(true)}>
          Añadir Protocolo
        </button>
      )}

      {formVisible && (
        <div className="modal-overlay" onClick={resetFormulario}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <form className="form-container" onSubmit={handleSubmit}>
              <h3>{protocoloEditando ? 'Editar Protocolo' : 'Nuevo Protocolo'}</h3>
              {error && <div className="form-error">{error}</div>}

              <label className="form-label">Título</label>
              <input
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
                {catalogos?.especialidades?.map((e) => (
                  <option key={e.id} value={e.id}>{e.nombre}</option>
                ))}
                <option value="nueva">-- Nueva Especialidad --</option>
              </select>

              {idEspecialidad === 'nueva' && (
                <>
                  <label className="form-label">Nueva Especialidad</label>
                  <input
                    value={nuevaEspecialidad}
                    onChange={(e) => setNuevaEspecialidad(e.target.value)}
                    className="form-input"
                    required
                  />
                </>
              )}

              {protocoloEditando && (
                <>
                  <label className="form-label">Estado</label>
                  <select
                    value={idEstado}
                    onChange={(e) => manejarCambioEstado(e.target.value)}
                    className="form-input"
                    required
                  >
                    <option value="">Seleccione...</option>
                    {catalogos?.estados?.map((e) => (
                      <option key={e.id} value={e.id}>{e.descripcion}</option>
                    ))}
                  </select>
                </>
              )}

              {!protocoloEditando && (
                <div className="info-message" style={{
                  backgroundColor: '#e3f2fd',
                  padding: '10px',
                  borderRadius: '4px',
                  marginBottom: '15px',
                  color: '#1565c0',
                  fontSize: '14px'
                }}>
                  <strong>Nota:</strong> Los protocolos nuevos se crean con estado "En Revisión" por defecto.
                </div>
              )}

              <label className="form-label">Áreas de Impacto</label>
              <select
                value={areaSeleccionada}
                onChange={(e) => setAreaSeleccionada(e.target.value)}
                className="form-input"
              >
                <option value="">Seleccione área...</option>
                {catalogos?.areasImpacto?.map((a) => (
                  <option key={a.id} value={a.id}>{a.nombre}</option>
                ))}
                <option value="nueva">-- Nueva Área de Impacto --</option>
              </select>

              {areaSeleccionada === 'nueva' && (
                <>
                  <label className="form-label">Nombre Nueva Área</label>
                  <input
                    value={nuevaAreaNombre}
                    onChange={(e) => setNuevaAreaNombre(e.target.value)}
                    className="form-input"
                  />
                  <label className="form-label">Descripción Nueva Área</label>
                  <textarea
                    value={nuevaAreaDescripcion}
                    onChange={(e) => setNuevaAreaDescripcion(e.target.value)}
                    className="form-input"
                  />
                </>
              )}

              <button type="button" onClick={agregarArea} className="add-area-btn">
                Añadir Área
              </button>

              {areasSeleccionadas.length > 0 && (
                <ul className="areas-seleccionadas-list">
                  {areasSeleccionadas.map((a, idx) => (
                    <li key={idx}>
                      {a.nombre} {a.tipo === 'nueva' && '(Nueva)'}
                      <button type="button" onClick={() => eliminarArea(idx)} className="remove-area-btn">X</button>
                    </li>
                  ))}
                </ul>
              )}

              <div className="form-buttons">
                <button type="submit" disabled={loading} className="submit-btn">
                  {loading ? 'Guardando...' : protocoloEditando ? 'Actualizar' : 'Crear'}
                </button>
                <button type="button" onClick={resetFormulario} className="cancel-btn">Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {mostrarConfirmacionEstado && (
        <div className="modal-overlay">
          <div className="modal-content" style={{ maxWidth: '400px' }}>
            <h3>Confirmar Cambio de Estado</h3>
            <p>¿Confirma que cambiará el estado del protocolo?</p>
            <p><strong>Nuevo estado:</strong> {catalogos?.estados?.find(e => e.id.toString() === estadoTemporal)?.descripcion}</p>
            <div className="form-buttons">
              <button onClick={confirmarCambioEstado} className="submit-btn">Confirmar</button>
              <button onClick={cancelarCambioEstado} className="cancel-btn">Cancelar</button>
            </div>
          </div>
        </div>
      )}

      {protocoloDetalle && (
        <div className="modal-overlay" onClick={() => setProtocoloDetalle(null)}>
          <div className="modal-content" onClick={e => e.stopPropagation()}>
            <h3>Detalle Protocolo</h3>
            <div className="detalle-item"><strong>Título:</strong><p>{protocoloDetalle.titulo}</p></div>
            <div className="detalle-item"><strong>Resumen:</strong><p>{protocoloDetalle.resumen}</p></div>
            <div className="detalle-item"><strong>Objetivo General:</strong><p>{protocoloDetalle.objetivo_general}</p></div>
            <div className="detalle-item"><strong>Metodología:</strong><p>{protocoloDetalle.metodologia}</p></div>
            <div className="detalle-item"><strong>Justificación:</strong><p>{protocoloDetalle.justificacion}</p></div>
            <div className="detalle-item"><strong>Especialidad:</strong><p>{protocoloDetalle.especialidad?.nombre || 'N/A'}</p></div>
            <div className="detalle-item"><strong>Estado:</strong><p>{protocoloDetalle.estado?.descripcion || 'N/A'}</p></div>
            <div className="detalle-item" style={{ flexDirection: 'column', alignItems: 'flex-start' }}>
              <strong>Áreas de Impacto:</strong>
              {protocoloDetalle.areas_impacto?.length > 0 ? (
                <ul>
                  {protocoloDetalle.areas_impacto.map(a => (
                    <li key={a.id}><strong>{a.nombre}</strong>: {a.descripcion}</li>
                  ))}
                </ul>
              ) : <p>N/A</p>}
            </div>
            <button onClick={() => setProtocoloDetalle(null)} className="close-btn">Cerrar</button>
          </div>
        </div>
      )}
    </div>
  );
}

export default Protocolos;
