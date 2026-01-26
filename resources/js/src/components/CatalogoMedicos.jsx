import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './CatalogoMedicos.css'

function CatalogoMedicos() {
    const [medicos, setMedicos] = useState([])
    const [especialidades, setEspecialidades] = useState([])
    const [formVisible, setFormVisible] = useState(false)
    const [medicoEditando, setMedicoEditando] = useState(null)
    const [loading, setLoading] = useState(false)
    const [accionesPermitidas, setAccionesPermitidas] = useState([])

    const [formData, setFormData] = useState({
        nombre: '',
        matricula: '',
        id_especialidad: ''
    })

    useEffect(() => {
        axios.get('/sanctum/csrf-cookie').then(() => {
            fetchMedicos()
            fetchEspecialidades()
            fetchAccionesUsuario()
        })
    }, [])

    const fetchAccionesUsuario = async () => {
        try {
            const userRes = await axios.get('/api/user')
            const userId = userRes.data.id
            const accionesRes = await axios.get(`/api/acciones/${userId}`)
            const accionesFiltradas = accionesRes.data.filter(
                (a) => a.menu_item === 'Catálogo de médicos'
            ).map((a) => a.accion)
            setAccionesPermitidas(accionesFiltradas)
        } catch (error) {
            console.error('Error al obtener las acciones del usuario:', error)
        }
    }

    const puede = (accion) => accionesPermitidas.includes(accion)

    const fetchMedicos = async () => {
        try {
            const res = await axios.get('/api/medicos')
            setMedicos(res.data)
        } catch (error) {
            console.error('Error al obtener médicos:', error)
        }
    }

    const fetchEspecialidades = async () => {
        try {
            const res = await axios.get('/api/especialidades')
            setEspecialidades(res.data)
        } catch (error) {
            console.error('Error al obtener especialidades:', error)
        }
    }

    const handleInputChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        })
    }

    const crearMedico = async () => {
        setLoading(true)
        try {
            await axios.post('/api/medicos', formData)
            resetFormulario()
            fetchMedicos()
        } catch (error) {
            console.error('Error al crear médico:', error)
            alert('Error al crear médico. Verifica que la matrícula no esté duplicada.')
        } finally {
            setLoading(false)
        }
    }

    const actualizarMedico = async () => {
        if (!medicoEditando) return
        setLoading(true)
        try {
            await axios.put(`/api/medicos/${medicoEditando}`, formData)
            resetFormulario()
            fetchMedicos()
        } catch (error) {
            console.error('Error al actualizar médico:', error)
            alert('Error al actualizar médico.')
        } finally {
            setLoading(false)
        }
    }

    const eliminarMedico = async (id) => {
        if (window.confirm('¿Estás seguro de que quieres eliminar este médico?')) {
            try {
                await axios.delete(`/api/medicos/${id}`)
                fetchMedicos()
            } catch (error) {
                console.error('Error al eliminar médico:', error)
                alert('Error al eliminar médico.')
            }
        }
    }

    const iniciarEdicion = (medico) => {
        setFormVisible(true)
        setMedicoEditando(medico.id)
        setFormData({
            nombre: medico.nombre,
            matricula: medico.matricula,
            id_especialidad: medico.id_especialidad
        })
    }

    const resetFormulario = () => {
        setFormVisible(false)
        setMedicoEditando(null)
        setFormData({
            nombre: '',
            matricula: '',
            id_especialidad: ''
        })
    }

    const getNombreEspecialidad = (id) => {
        const esp = especialidades.find(e => e.id === id)
        return esp ? esp.nombre : 'Sin especialidad'
    }

    return (
        <div className="medicos-container">
            <h2 className="medicos-title">Catálogo de Médicos</h2>

            <table className="medicos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Matrícula</th>
                        <th>Especialidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {medicos.map((m) => (
                        <tr key={m.id}>
                            <td>{m.id}</td>
                            <td>{m.nombre}</td>
                            <td>{m.matricula}</td>
                            <td>{getNombreEspecialidad(m.id_especialidad)}</td>
                            <td>
                                {puede('editar') && (
                                    <button className="edit-btn" onClick={() => iniciarEdicion(m)}>
                                        Editar
                                    </button>
                                )}
                                {puede('eliminar') && (
                                    <button className="delete-btn" onClick={() => eliminarMedico(m.id)}>
                                        Eliminar
                                    </button>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            {!formVisible && puede('crear') ? (
                <button className="add-btn" onClick={() => setFormVisible(true)}>
                    Añadir Médico
                </button>
            ) : formVisible ? (
                <div className="form-container">
                    <label className="form-label">Nombre Completo</label>
                    <input 
                        className="form-input" 
                        name="nombre" 
                        value={formData.nombre} 
                        onChange={handleInputChange}
                        placeholder="Dr. Juan Pérez"
                    />

                    <label className="form-label">Matrícula</label>
                    <input 
                        className="form-input" 
                        name="matricula" 
                        value={formData.matricula} 
                        onChange={handleInputChange}
                        placeholder="MP-12345"
                    />

                    <label className="form-label">Especialidad</label>
                    <select 
                        className="form-input" 
                        name="id_especialidad" 
                        value={formData.id_especialidad} 
                        onChange={handleInputChange}
                    >
                        <option value="">Seleccione una especialidad</option>
                        {especialidades.map((esp) => (
                            <option key={esp.id} value={esp.id}>
                                {esp.nombre}
                            </option>
                        ))}
                    </select>

                    <div className="form-actions">
                        <button
                            className="create-btn"
                            onClick={medicoEditando ? actualizarMedico : crearMedico}
                            disabled={loading}
                        >
                            {loading
                                ? medicoEditando
                                    ? 'Actualizando...'
                                    : 'Creando...'
                                : medicoEditando
                                    ? 'Actualizar'
                                    : 'Crear Médico'}
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

export default CatalogoMedicos