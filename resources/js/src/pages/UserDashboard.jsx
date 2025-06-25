import React from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../axios'; // Usa tu instancia de Axios

const UserDashboard = ({ setAuth, setRole }) => {
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

export default UserDashboard;
// Este componente es la vista del usuario normal en el dashboard
// Aquí puedes agregar más funcionalidades específicas para el usuario normal
// Por ejemplo, mostrar información del usuario, estadísticas, etc.
// Recuerda que este componente se renderiza solo si el usuario está autenticado y tiene el rol de 'user'
// Puedes personalizarlo según tus necesidades
// Asegúrate de que el componente esté protegido por PrivateRoute en tu Router.jsx
// Esto garantiza que solo los usuarios autenticados y con el rol adecuado puedan acceder a esta vista
// También puedes agregar más lógica para manejar errores, cargar datos del usuario, etc.
// Este componente se usa en Router.jsx dentro de la ruta /dashboard
// Asegúrate de que el componente esté correctamente importado y utilizado en Router.jsx
// Puedes agregar más funcionalidades específicas para el usuario normal aquí
// Por ejemplo, mostrar información del usuario, estadísticas, etc.
// Recuerda que este componente se renderiza solo si el usuario está autenticado y tiene el rol de 'user'
// Puedes personalizarlo según tus necesidades
// Asegúrate de que el componente esté protegido por PrivateRoute en tu Router.jsx
// Esto garantiza que solo los usuarios autenticados y con el rol adecuado puedan acceder a esta vista
// También puedes agregar más lógica para manejar errores, cargar datos del usuario, etc.
// Este componente se usa en Router.jsx dentro de la ruta /dashboard
// Asegúrate de que el componente esté correctamente importado y utilizado en Router.jsx
// Puedes agregar más funcionalidades específicas para el usuario normal aquí
// Por ejemplo, mostrar información del usuario, estadísticas, etc.
// Recuerda que este componente se renderiza solo si el usuario está autenticado y tiene el rol de 'user'
// Puedes personalizarlo según tus necesidades
// Asegúrate de que el componente esté protegido por PrivateRoute en tu Router.jsx