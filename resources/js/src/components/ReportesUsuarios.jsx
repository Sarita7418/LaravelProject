import React from 'react'
import axios from '../lib/axios'

const ReportesUsuarios = () => {
  const handleExport = async () => {
    try {
      const response = await axios.get('/api/reportes/usuarios/excel', {
        responseType: 'blob', // 👈 necesario para descargar archivos
      })

      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', 'usuarios.xlsx') // nombre del archivo
      document.body.appendChild(link)
      link.click()
    } catch (error) {
      console.error('Error al exportar usuarios:', error)
      alert('No se pudo generar el reporte.')
    }
  }

  return (
    <div className="reportes-container">
      <h2>Reporte de Usuarios</h2>
      <button onClick={handleExport}>Exportar a Excel</button>
    </div>
  )
}

export default ReportesUsuarios
