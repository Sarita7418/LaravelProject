import React from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../axios'; // Usa tu instancia de Axios

const Dashboard = ({ setAuth, setRole }) => {
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      // Paso 1: CSRF cookie
      await axios.get('/sanctum/csrf-cookie');

      // Paso 2: Logout
      await axios.post('/api/logout');

      // Paso 3: Limpiar sesión
      setAuth(false);
      setRole(null);

      // Paso 4: Redirigir a login
      navigate('/login');
    } catch (err) {
      console.error('Error al cerrar sesión', err);
    }
  };

    return (
      <div>
        <h1>Bienvenido Usuario</h1>
        <p>Esta es la vista del usuario normal.</p>
        <button onClick={handleLogout}>Cerrar sesión</button>
      </div>
    );
};

export default Dashboard;