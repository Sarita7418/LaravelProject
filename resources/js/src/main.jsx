import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'

import './index.css'
import App from './App.jsx'
import Login from './components/LoginConDosPasos.jsx'
import Dashboard from './pages/Dashboard.jsx'
import { AuthProvider } from './context/AuthContext'; // crea este archivo
import Router from './routes/Router' // <-- este maneja todas las rutas


createRoot(document.getElementById('root')).render(
 <StrictMode>
  <AuthProvider>
    <BrowserRouter>
      <Router />
    </BrowserRouter>
  </AuthProvider>
</StrictMode>
)
