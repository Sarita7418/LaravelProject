import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './Personas.css'

function Personas() {
    const [personas, setPersonas] = useState([])
    const [inactivos, setInactivos] = useState([])
    const [formVisible, setFormVisible] = useState(false)
    const [personaEditando, setPersonaEditando] = useState(null)
    const [mostrarInactivos, setMostrarInactivos] = useState(false)
    const [loading, setLoading] = useState(false)

    const [formData, setFormData] = useState({
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        ci: '',
        telefono: '',
        fecha_nacimiento: ''
    })

    useEffect(() => {
        axios.get('/sanctum/csrf-cookie').then(() => {
            fetchPersonas()
            fetchInactivos()
        })
    }, [])

    const fetchPersonas = async () => {
        try {
            const res = await axios.get('/api/personas')
            setPersonas(res.data)
        } catch (error) {
            console.error('Error al obtener personas:', error)
        }
    }

    const fetchInactivos = async () => {
        try {
            const res = await axios.get('/api/personas/inactivos')
            setInactivos(res.data)
        } catch (error) {
            console.error('Error al obtener personas inactivas:', error)
        }
    }

    const handleInputChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        })
    }

    const crearPersona = async () => {
        setLoading(true)
        try {
            await axios.post('/api/personas', {
                ...formData,
                estado: 1 // <- ¡Aquí está la clave!
            })
            resetFormulario()
            fetchPersonas()
        } catch (error) {
            console.error('Error al crear persona:', error)
        } finally {
            setLoading(false)
        }
    }


    const actualizarPersona = async () => {
        if (!personaEditando) return
        setLoading(true)
        try {
            await axios.put(`/api/personas/${personaEditando}`, formData)
            resetFormulario()
            fetchPersonas()
        } catch (error) {
            console.error('Error al actualizar persona:', error)
        } finally {
            setLoading(false)
        }
    }

    const eliminarPersona = async (id) => {
        if (window.confirm('¿Estás seguro de que quieres desactivar esta persona?')) {
            try {
                await axios.delete(`/api/personas/${id}`)
                fetchPersonas()
                fetchInactivos()
            } catch (error) {
                console.error('Error al desactivar persona:', error)
            }
        }
    }

    const reactivarPersona = async (id) => {
        if (window.confirm('¿Deseas reactivar esta persona?')) {
            try {
                await axios.put(`/api/personas/${id}/reactivar`)
                fetchPersonas()
                fetchInactivos()
            } catch (error) {
                console.error('Error al reactivar persona:', error)
            }
        }
    }

    const iniciarEdicion = (persona) => {
        setFormVisible(true)
        setPersonaEditando(persona.id)
        setFormData({
            nombres: persona.nombres,
            apellido_paterno: persona.apellido_paterno,
            apellido_materno: persona.apellido_materno,
            ci: persona.ci,
            telefono: persona.telefono,
            fecha_nacimiento: persona.fecha_nacimiento
        })
    }

    const resetFormulario = () => {
        setFormVisible(false)
        setPersonaEditando(null)
        setFormData({
            nombres: '',
            apellido_paterno: '',
            apellido_materno: '',
            ci: '',
            telefono: '',
            fecha_nacimiento: ''
        })
    }

    const personasActivas = mostrarInactivos ? inactivos : personas

    return (
        <div className="personas-container">
            <h2 className="personas-title">Personas</h2>

            <div className="toggle-container">
                <button
                    className={`toggle-btn ${!mostrarInactivos ? 'active' : ''}`}
                    onClick={() => setMostrarInactivos(false)}
                >
                    Activas ({personas.length})
                </button>
                <button
                    className={`toggle-btn ${mostrarInactivos ? 'active' : ''}`}
                    onClick={() => setMostrarInactivos(true)}
                >
                    Inactivas ({inactivos.length})
                </button>
            </div>

            <table className="personas-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>CI</th>
                        <th>Teléfono</th>
                        <th>Fecha de nacimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {personasActivas.map((p) => (
                        <tr key={p.id}>
                            <td>{p.id}</td>
                            <td>{`${p.nombres} ${p.apellido_paterno} ${p.apellido_materno}`}</td>
                            <td>{p.ci}</td>
                            <td>{p.telefono}</td>
                            <td>{p.fecha_nacimiento}</td>

                            {/* NUEVA COLUMNA DE ESTADO */}
                            <td>
                                <span className={`status ${p.estado ? 'active' : 'inactive'}`}>
                                    {p.estado ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>

                            <td>
                                {mostrarInactivos ? (
                                    <button className="reactivate-btn" onClick={() => reactivarPersona(p.id)}>
                                        Reactivar
                                    </button>
                                ) : (
                                    <>
                                        <button className="edit-btn" onClick={() => iniciarEdicion(p)}>
                                            Editar
                                        </button>
                                        <button className="delete-btn" onClick={() => eliminarPersona(p.id)}>
                                            Desactivar
                                        </button>
                                    </>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>

            </table>

            {!mostrarInactivos && !formVisible ? (
                <button className="add-btn" onClick={() => setFormVisible(true)}>
                    Añadir Persona
                </button>
            ) : !mostrarInactivos ? (
                <div className="form-container">
                    <label className="form-label">Nombres</label>
                    <input className="form-input" name="nombres" value={formData.nombres} onChange={handleInputChange} />
                    <label className="form-label">Apellido Paterno</label>
                    <input className="form-input" name="apellido_paterno" value={formData.apellido_paterno} onChange={handleInputChange} />
                    <label className="form-label">Apellido Materno</label>
                    <input className="form-input" name="apellido_materno" value={formData.apellido_materno} onChange={handleInputChange} />
                    <label className="form-label">CI</label>
                    <input className="form-input" name="ci" value={formData.ci} onChange={handleInputChange} />
                    <label className="form-label">Teléfono</label>
                    <input className="form-input" name="telefono" value={formData.telefono} onChange={handleInputChange} />
                    <label className="form-label">Fecha de nacimiento</label>
                    <input className="form-input" type="date" name="fecha_nacimiento" value={formData.fecha_nacimiento} onChange={handleInputChange}/>


                    <div className="form-actions">
                        <button
                            className="create-btn"
                            onClick={personaEditando ? actualizarPersona : crearPersona}
                            disabled={loading}
                        >
                            {loading
                                ? personaEditando
                                    ? 'Actualizando...'
                                    : 'Creando...'
                                : personaEditando
                                    ? 'Actualizar'
                                    : 'Crear Persona'}
                        </button>
                        <button className="cancel-btn" onClick={resetFormulario}>
                            Cancelar
                        </button>
                    </div>
                </div>
            ) : null}
        </div>
    )
}

export default Personas
