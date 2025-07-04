// LayoutDashboard.jsx
import React from 'react'
import { Outlet } from 'react-router-dom'
import MenuJerarquico from './MenuJerarquico'
import './LayoutDashboard.css' 
const LayoutDashboard = () => {
  return (
    <div className="layout-dashboard">
      <aside className="sidebar">
        <MenuJerarquico />
      </aside>
      <main className="contenido">
        <Outlet />
      </main>
    </div>
  )
}

export default LayoutDashboard
