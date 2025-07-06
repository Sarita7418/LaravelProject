import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'

import './index.css'
import App from './App.jsx'
import Login from './Login.jsx'
import Dashboard from './pages/Dashboard.jsx'
import Router from './Router' // <-- este maneja todas las rutas
import { AuthProvider } from './context/AuthContext'; // crea este archivo



createRoot(document.getElementById('root')).render(
 <StrictMode>
  <AuthProvider>
    <BrowserRouter>
      <Router />
    </BrowserRouter>
  </AuthProvider>
</StrictMode>
)
