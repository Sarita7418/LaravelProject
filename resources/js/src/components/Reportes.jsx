import React from 'react'
import './Reportes.css'

const Reportes = () => {
  const reportesDisponibles = [
    {
      nombre: 'Usuarios',
      excel: '/api/reportes/usuarios/excel',
      pdf: '/api/reportes/usuarios/pdf', // este lo creamos luego
    },
    // Puedes agregar más reportes acá
  ]

  const descargarArchivo = (url) => {
    window.open(url, '_blank')
  }

  return (
    <div className="reportes-contenedor">
      <h2>Reportes</h2>
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
