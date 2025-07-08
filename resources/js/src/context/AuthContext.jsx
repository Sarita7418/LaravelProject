// src/context/AuthContext.jsx
import { createContext, useContext, useState } from 'react';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [auth, setAuth] = useState(false); // false por defecto
  const [role, setRole] = useState(null);  // null o "" si prefieres

  return (
    <AuthContext.Provider value={{ auth, setAuth, role, setRole }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
