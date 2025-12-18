import axios from 'axios';

// Determinar la URL de la API según el ambiente
const getApiUrl = () => {
    // Usar configuración global si está disponible (cargada desde config.js)
    if (window.APP_CONFIG && window.APP_CONFIG.API_URL) {
        console.log('API URL desde config:', window.APP_CONFIG.API_URL);
        return window.APP_CONFIG.API_URL;
    }
    
    // Fallback a variable de entorno de Vite
    const apiUrl = import.meta.env.VITE_API_URL || 'https://twodoexamenparcial.onrender.com/api';
    console.log('API URL configurada:', apiUrl);
    return apiUrl;
};

const api = axios.create({
    baseURL: getApiUrl(),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    timeout: 60000, // 60 segundos timeout (para Aiven cloud)
});

// Adjuntar token si existe - OPTIMIZADO sin logs
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Manejo de errores optimizado
api.interceptors.response.use(
    response => {
        // Sin logs en producción
        return response;
    },
    error => {
        
        // Intentar limpiar el "7" también en errores
        if (error.response && typeof error.response.data === 'string') {
            if (error.response.data.startsWith('7')) {
                try {
                    error.response.data = JSON.parse(error.response.data.substring(1));
                } catch (e) {
                    // Si falla, mantener el error original
                }
            }
        }
        
        // Solo loggear errores críticos
        if (error.response?.status === 401) {
            console.warn('Sesión expirada');
        } else if (error.response?.status >= 500) {
            console.error('Error del servidor:', error.response.data);
        }
        
        return Promise.reject(error);
    }
);

export default api;
