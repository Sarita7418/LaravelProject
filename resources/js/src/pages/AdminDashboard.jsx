import React from 'react';
import Roles from '../components/Roles';
import Usuarios from '../components/Usuarios';

const AdminDashboard = () => {
  return (
    <div>
      <h1>Bienvenido Administrador</h1>
      <Roles/>
      <Usuarios/>
    </div>
  );
};

export default AdminDashboard;
