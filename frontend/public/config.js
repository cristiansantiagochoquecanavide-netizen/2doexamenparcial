// Configuración de la aplicación
// Se carga en tiempo de ejecución, no necesita reconstruir

window.APP_CONFIG = {
  API_URL: import.meta.env.VITE_API_URL || 'https://twodoexamenparcial.onrender.com/api',
  APP_NAME: import.meta.env.VITE_APP_NAME || 'Carga Horaria',
  APP_ENV: import.meta.env.VITE_APP_ENV || 'production'
};
