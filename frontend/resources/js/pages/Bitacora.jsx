import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './Bitacora.css';

const Bitacora = () => {
    const [bitacoras, setBitacoras] = useState([]);
    const [loading, setLoading] = useState(false);
    const [pagination, setPagination] = useState({});
    const [currentPage, setCurrentPage] = useState(1);
    const [error, setError] = useState(null);

    // Cargar datos iniciales y auto-refresh
    useEffect(() => {
        cargarBitacora(1);
        
        // Auto-refresh cada 5 segundos para actualizaciones automáticas
        const interval = setInterval(() => {
            cargarBitacora(currentPage);
        }, 5000);
        
        return () => clearInterval(interval);
    }, [currentPage]);

    // Cargar bitácora (solo lectura, sin filtros)
    const cargarBitacora = async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const response = await api.get('/bitacora', { 
                params: { page, per_page: 50 } 
            });
            
            if (response.data.success) {
                setBitacoras(response.data.data);
                setPagination(response.data.pagination);
            } else {
                setError(response.data.message || 'Error al cargar bitácora');
            }
        } catch (error) {
            console.error('Error al cargar bitácora:', error);
            if (error.response?.status === 401) {
                setError('No autenticado. Por favor, inicia sesión.');
            } else if (error.response?.status === 403) {
                setError('No tienes permiso para ver la bitácora.');
            } else {
                setError(error.response?.data?.message || 'Error al conectar con el servidor');
            }
        } finally {
            setLoading(false);
        }
    };

    // Cambiar página
    const cambiarPagina = (newPage) => {
        setCurrentPage(newPage);
    };

    return (
        <div className="bitacora-container">
            {/* Encabezado */}
            <div className="bitacora-header">
                <h1>Bitácora</h1>
                <p className="subtitle">
                    {loading ? 'Actualizando...' : 'Actualizado automáticamente cada 5 segundos'}
                </p>
            </div>

            {/* Mostrar errores */}
            {error && (
                <div style={{
                    backgroundColor: '#fee2e2',
                    border: '1px solid #fca5a5',
                    color: '#b91c1c',
                    padding: '12px 16px',
                    borderRadius: '6px',
                    marginBottom: '20px'
                }}>
                    <strong>Error:</strong> {error}
                </div>
            )}

            {/* Tabla simple */}
            <div className="bitacora-table-container">
                <table className="bitacora-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Módulo</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        {bitacoras.length > 0 ? (
                            bitacoras.map((bitacora) => (
                                <tr key={bitacora.id_bit} className="bitacora-row">
                                    <td className="id-cell">{bitacora.id_bit}</td>
                                    <td className="usuario-cell">
                                        {bitacora.usuario?.nombre_usuario || 'Sistema'}
                                    </td>
                                    <td className="accion-cell">
                                        {bitacora.accion}
                                    </td>
                                    <td className="modulo-cell">
                                        {bitacora.modulo}
                                    </td>
                                    <td className="fecha-cell">
                                        {new Date(bitacora.fecha_accion).toLocaleString('es-ES')}
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="5" className="sin-datos">
                                    No hay registros
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Paginación simple */}
            {pagination.last_page > 1 && (
                <div className="paginacion">
                    <button
                        onClick={() => cambiarPagina(1)}
                        disabled={pagination.current_page === 1}
                        className="btn-paginacion"
                        title="Primera página"
                    >
                        ⏮
                    </button>

                    <button
                        onClick={() => cambiarPagina(pagination.current_page - 1)}
                        disabled={pagination.current_page === 1}
                        className="btn-paginacion"
                        title="Página anterior"
                    >
                        ◀
                    </button>

                    <span className="pagina-info">
                        Página {pagination.current_page} de {pagination.last_page}
                    </span>

                    <button
                        onClick={() => cambiarPagina(pagination.current_page + 1)}
                        disabled={pagination.current_page === pagination.last_page}
                        className="btn-paginacion"
                        title="Siguiente página"
                    >
                        ▶
                    </button>

                    <button
                        onClick={() => cambiarPagina(pagination.last_page)}
                        disabled={pagination.current_page === pagination.last_page}
                        className="btn-paginacion"
                        title="Última página"
                    >
                        ⏭
                    </button>
                </div>
            )}
        </div>
    );
};

export default Bitacora;
