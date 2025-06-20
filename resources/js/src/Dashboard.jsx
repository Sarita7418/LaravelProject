import React, { useEffect, useState } from 'react';
import api from './axios';

export default function Dashboard() {
  const [user, setUser] = useState(null);
  const [error, setError] = useState(null);

  const getUser = async () => {
    try {
      const res = await api.get('/api/user');
      setUser(res.data);
    } catch (err) {
      console.error('No autenticado:', err);
      setError('Sesión expirada o no autenticado');
    }
  };

  const handleLogout = async () => {
    try {
      await api.post('/api/logout');
      window.location.href = '/'; // redirigir a login
    } catch (err) {
      console.error('Error al cerrar sesión', err);
    }
  };

  useEffect(() => {
    getUser();
  }, []);

  if (error) {
    return (
      <div>
        <h2>{error}</h2>
        <a href="/">Volver al login</a>
      </div>
    );
  }

  return (
    <div>
      <h2>Bienvenido al Dashboard</h2>
      {user ? (
        <div>
          <p><strong>Nombre:</strong> {user.name}</p>
          <p><strong>Correo:</strong> {user.email}</p>
          <button onClick={handleLogout}>Cerrar sesión</button>
        </div>
      ) : (
        <p>Cargando usuario...</p>
      )}
    </div>
  );
}
