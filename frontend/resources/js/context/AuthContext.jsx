import React, { createContext, useState, useContext, useEffect } from 'react';
import api from '../utils/api';

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth debe ser usado dentro de AuthProvider');
    }
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    useEffect(() => {
        checkAuth();
    }, []);

    const checkAuth = async () => {
        const token = localStorage.getItem('token');
        const storedUser = localStorage.getItem('user');
        
        if (token && storedUser) {
            try {
                const response = await api.get('/auth/me');
                setUser(response.data.usuario);
                setIsAuthenticated(true);
            } catch (error) {
                console.error('Error al verificar autenticación:', error);
                logout();
            }
        }
        setLoading(false);
    };

    const login = async (ci_persona, contrasena) => {
        try {
            const response = await api.post('/auth/login', {
                login: ci_persona,
                contrasena,
            });
            
            const { token, usuario } = response.data;
            
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(usuario));
            setUser(usuario);
            setIsAuthenticated(true);
            
            return { success: true };
        } catch (error) {
            console.error('Error en login:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Error al iniciar sesión',
            };
        }
    };

    const logout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setUser(null);
        setIsAuthenticated(false);
    };

    const hasPermission = (permissionName) => {
        if (!user || !user.rol) return false;
        // Por ahora, simplemente verificamos el rol
        // En una implementación más completa, verificaríamos los permisos específicos
        return true; // Todos tienen permiso por ahora
    };

    const isCoordinador = () => {
        return user?.rol?.nombre === 'Coordinador Académico' || user?.rol?.nombre === 'Administrador';
    };

    const value = {
        user,
        login,
        logout,
        loading,
        isAuthenticated,
        hasPermission,
        isCoordinador,
    };

    return (
        <AuthContext.Provider value={value}>
            {!loading && children}
        </AuthContext.Provider>
    );
};
