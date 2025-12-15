import React, { useState } from 'react'
import './Reportes.css'

const Reportes = () => {
  const [desde, setDesde] = useState('')
  const [hasta, setHasta] = useState('')

  const reportesDisponibles = [
    {
      nombre: 'Usuarios',
      excel: '/api/reportes/usuarios/excel',
      pdf: '/api/reportes/usuarios/pdf',
    },
    // Agregá más reportes acá si querés
  ]

  const descargarArchivo = (url) => {
    const query = `?desde=${desde}&hasta=${hasta}`
    window.open(url + query, '_blank')
  }

  return (
    <div className="reportes-contenedor">
      <h2>Reportes</h2>

      <div className="filtros-fechas">
        <label>
          Desde:
          <input type="date" value={desde} onChange={(e) => setDesde(e.target.value)} />
        </label>
        <label>
          Hasta:
          <input type="date" value={hasta} onChange={(e) => setHasta(e.target.value)} />
        </label>
      </div>

      <table className="tabla-reportes">
        <thead>
          <tr>
            <th>Reporte</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {reportesDisponibles.map((reporte, index) => (
            <tr key={index}>
              <td>{reporte.nombre}</td>
              <td>
                <button onClick={() => descargarArchivo(reporte.excel)}>📥 Excel</button>
                {reporte.pdf && (
                  <button onClick={() => descargarArchivo(reporte.pdf)}>📄 PDF</button>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

export default Reportes
