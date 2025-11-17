import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Adjuntar token si existe
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    console.log('🔵 API Request:', config.method.toUpperCase(), config.url);
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
        console.log('🔑 Token incluido en la petición');
    } else {
        console.log('⚠️ No hay token en localStorage');
    }
    return config;
});

// Manejo de errores - NO redirigir automáticamente
api.interceptors.response.use(
    response => {
        console.log('✅ API Response:', response.config.url, '- Status:', response.status);
        
        // WORKAROUND: Si la respuesta es un string que empieza con "7", quitar el "7"
        if (typeof response.data === 'string' && response.data.startsWith('7')) {
            console.warn('⚠️ Detectado "7" al inicio de la respuesta, limpiando...');
            try {
                response.data = JSON.parse(response.data.substring(1));
                console.log('✅ Respuesta parseada correctamente:', response.data);
            } catch (e) {
                console.error('❌ Error al parsear respuesta limpia:', e);
            }
        }
        
        return response;
    },
    error => {
        console.error('❌ API Error:', error.config?.url, '- Status:', error.response?.status);
        console.error('Error completo:', error);
        // Solo loggear el error, no redirigir automáticamente
        if (error.response?.status === 401) {
            console.warn('⚠️ Error 401 - No autorizado');
        }
        return Promise.reject(error);
    }
);

export default api;
