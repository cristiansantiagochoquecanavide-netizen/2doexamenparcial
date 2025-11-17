import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const HomeRedirect = () => {
  const { user } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (user && user.rol) {
      // Si es Docente, redirigir a asistencias
      if (user.rol.nombre_rol === 'Docente') {
        navigate('/asistencias', { replace: true });
      } else {
        // Para otros roles, mostrar dashboard
        navigate('/dashboard', { replace: true });
      }
    }
  }, [user, navigate]);

  return (
    <div className="loading">Cargando...</div>
  );
};

export default HomeRedirect;
