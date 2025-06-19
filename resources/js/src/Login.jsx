import React, { useState } from 'react';
import api from './axios';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);

  const handleLogin = async (e) => {
    e.preventDefault();

    try {
      // 1. Obtener cookie CSRF (obligatorio antes del login)
      await api.get('/sanctum/csrf-cookie');

      // 2. Enviar credenciales
      const response = await api.post('/api/login', {
        email,
        password
      });

      console.log('Login exitoso:', response.data);
      setError(null);
    } catch (err) {
      console.error('Error en login:', err);
      setError('Credenciales incorrectas o error de conexión.');
    }
  };

  return (
    <div>
      <h2>Iniciar Sesión</h2>
      <form onSubmit={handleLogin}>
        <input
          type="email"
          placeholder="Correo"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        /><br />
        <input
          type="password"
          placeholder="Contraseña"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        /><br />
        <button type="submit">Entrar</button>
        {error && <p style={{ color: 'red' }}>{error}</p>}
      </form>
    </div>
  );
}
