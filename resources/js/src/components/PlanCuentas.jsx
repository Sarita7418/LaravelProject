import React, { useEffect, useState } from 'react'
import axios from '../lib/axios'
import './PlanCuentas.css'

function PlanCuentas() {
    const [cuentas, setCuentas] = useState([])
    const [cuentasExpandidas, setCuentasExpandidas] = useState(new Set())
    const [loading, setLoading] = useState(true)
    const [accionesPermitidas, setAccionesPermitidas] = useState([])

    useEffect(() => {
        axios.get('/sanctum/csrf-cookie').then(() => {
            fetchCuentas()
            fetchAccionesUsuario()
        })
    }, [])

    const fetchAccionesUsuario = async () => {
        try {
            const userRes = await axios.get('/api/user')
            const userId = userRes.data.id
            const accionesRes = await axios.get(`/api/acciones/${userId}`)
            const accionesFiltradas = accionesRes.data.filter(
                (a) => a.menu_item === 'Plan de Cuentas'
            ).map((a) => a.accion)
            setAccionesPermitidas(accionesFiltradas)
        } catch (error) {
            console.error('Error al obtener las acciones del usuario:', error)
        }
    }

    const puede = (accion) => accionesPermitidas.includes(accion)

    const fetchCuentas = async () => {
        setLoading(true)
        try {
            const res = await axios.get('/api/plan-cuentas')
            console.log('Datos obtenidos:', res.data)
            console.log('Tipo de datos:', typeof res.data)
            console.log('Es array:', Array.isArray(res.data))
            setCuentas(res.data || [])
        } catch (error) {
            console.error('Error al obtener plan de cuentas:', error)
            setCuentas([]) 
        } finally {
            setLoading(false)
        }
    }

    const construirJerarquia = (cuentas, idPadre = null, nivel = 1) => {
        return cuentas
            .filter(cuenta => {
                if (nivel === 1) {
                    return cuenta.id_padre === null || cuenta.id_padre === 0
                }
                return cuenta.id_padre === idPadre
            })
            .sort((a, b) => a.codigo.localeCompare(b.codigo))
            .map(cuenta => ({
                ...cuenta,
                nivel: nivel,
                hijos: construirJerarquia(cuentas, cuenta.id, nivel + 1)
            }))
    }

    const toggleExpansion = (cuentaId) => {
        const nuevasExpandidas = new Set(cuentasExpandidas)
        if (nuevasExpandidas.has(cuentaId)) {
            nuevasExpandidas.delete(cuentaId)
        } else {
            nuevasExpandidas.add(cuentaId)
        }
        setCuentasExpandidas(nuevasExpandidas)
    }

    const formatearMonto = (monto) => {
        if (!monto || monto === 0) return '0.00'
        return parseFloat(monto).toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })
    }

    const renderFilaCuenta = (cuenta, nivel = 1) => {
        const tieneHijos = cuenta.hijos && cuenta.hijos.length > 0
        const estaExpandida = cuentasExpandidas.has(cuenta.id)
        const indentacion = (nivel - 1) * 30
        const esNivel5 = nivel === 5

        return (
            <React.Fragment key={cuenta.id}>
                <tr className={`cuenta-row cuenta-nivel-${nivel} ${tieneHijos ? 'cuenta-padre' : 'cuenta-hija'}`}>
                    <td style={{ paddingLeft: `${indentacion}px` }}>
                        <div className="cuenta-codigo-container">
                            {tieneHijos && (
                                <button
                                    className={`expand-btn ${estaExpandida ? 'expanded' : ''}`}
                                    onClick={() => toggleExpansion(cuenta.id)}
                                >
                                    {estaExpandida ? '▼' : '▶'}
                                </button>
                            )}
                            <span className="cuenta-codigo">{cuenta.codigo}</span>
                        </div>
                    </td>
                    <td className="cuenta-descripcion">{cuenta.descripcion}</td>
                    <td className="saldo-debe">{formatearMonto(cuenta.debe)}</td>
                    <td className="saldo-haber">{formatearMonto(cuenta.haber)}</td>
                    <td className="saldo-total">{formatearMonto(cuenta.saldo)}</td>
                    <td>
                        <span className={`status ${cuenta.estado === 'ACTIVO' ? 'active' : 'inactive'}`}>
                            {cuenta.estado}
                        </span>
                    </td>
                    <td className="acciones-cell">
                        {esNivel5 && puede('editar') && (
                            <button className="edit-btn" title="Editar">
                                ✏️
                            </button>
                        )}
                        {esNivel5 && puede('eliminar') && (
                            <button className="delete-btn" title="Desactivar">
                                🗑️
                            </button>
                        )}
                        {!esNivel5 && !tieneHijos && (
                            <span className="no-actions">-</span>
                        )}
                    </td>
                </tr>
                
                {tieneHijos && estaExpandida && cuenta.hijos.map(hijo => 
                    renderFilaCuenta(hijo, nivel + 1)
                )}
            </React.Fragment>
        )
    }

    const cuentasJerarquicas = construirJerarquia(cuentas)
    console.log('Cuentas raw:', cuentas)
    console.log('Cuentas jerárquicas:', cuentasJerarquicas)
    console.log('Loading:', loading)

    return (
        <div className="plan-cuentas-container">
            <div className="plan-cuentas-header">
                <h2 className="plan-cuentas-title">Plan de Cuentas</h2>
                {puede('crear') && (
                    <button className="add-btn">
                        Añadir Cuenta
                    </button>
                )}
            </div>

            <div className="tabla-container">
                <table className="plan-cuentas-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan="7" className="loading-cell">
                                    Cargando plan de cuentas...
                                </td>
                            </tr>
                        ) : cuentasJerarquicas.length === 0 ? (
                            <tr>
                                <td colSpan="7" className="empty-cell">
                                    No hay cuentas registradas
                                </td>
                            </tr>
                        ) : (
                            cuentasJerarquicas.map(cuenta => 
                                renderFilaCuenta(cuenta, 1)
                            )
                        )}
                    </tbody>
                </table>
            </div>
            <div className="debug-info">
                <button 
                    className="debug-btn" 
                    onClick={() => {
                        console.log('Cuentas obtenidas:', cuentas)
                        console.log('Cuentas jerárquicas:', cuentasJerarquicas)
                        console.log('Total cuentas:', cuentas.length)
                        console.log('Estructura por niveles:')
                        const porNiveles = cuentas.reduce((acc, c) => {
                            const nivel = c.nivel || 'indefinido'
                            acc[nivel] = (acc[nivel] || 0) + 1
                            return acc
                        }, {})
                        console.log(porNiveles)
                    }}
                >
                </button>
            </div>
        </div>
    )
}

export default PlanCuentas