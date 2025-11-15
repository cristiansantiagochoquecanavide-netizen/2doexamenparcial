import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import AsignacionForm from '../components/AsignacionForm';
import './CargaHoraria.css';

const CargaHoraria = () => {
    const [cargasHorarias, setCargasHorarias] = useState([]);
    const [filteredCargas, setFilteredCargas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [editingCarga, setEditingCarga] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [filterDocente, setFilterDocente] = useState('');
    const [docentes, setDocentes] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage] = useState(10);

    useEffect(() => {
        cargarCargasHorarias();
        cargarDocentes();
    }, []);

    useEffect(() => {
        filtrarCargasHorarias();
    }, [cargasHorarias, searchQuery, filterDocente]);

    const cargarCargasHorarias = async () => {
        try {
            setLoading(true);
            const response = await api.get('/asignaciones');
            // Laravel devuelve datos paginados: { data: [...], meta: {...}, links: {...} }
            const asignaciones = response.data.data || response.data || [];
            setCargasHorarias(asignaciones);
            setError(null);
        } catch (err) {
            setError('Error al cargar las asignaciones: ' + (err.response?.data?.message || err.message));
            console.error(err);
            setCargasHorarias([]);
        } finally {
            setLoading(false);
        }
    };

    const cargarDocentes = async () => {
        try {
            const response = await api.get('/docentes');
            console.log('Respuesta API /docentes:', response);
            // Maneja tanto array directo como respuesta paginada
            const docentes = response.data.data || response.data || [];
            console.log('Docentes procesados:', docentes);
            setDocentes(docentes);
        } catch (err) {
            console.error('Error al cargar docentes:', err);
            setDocentes([]);
        }
    };

    const filtrarCargasHorarias = () => {
        let filtered = cargasHorarias;

        // Filtro por búsqueda de docente, materia o grupo
        if (searchQuery) {
            filtered = filtered.filter(carga => {
                const docenteNombre = carga.docente?.usuario?.persona?.nombre_completo || `Docente ${carga.codigo_doc}` || '';
                const materiaNombre = carga.grupo?.materia?.nombre_mat || '';
                const grupoNombre = carga.grupo?.codigo_grupo || '';

                return (
                    docenteNombre.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    materiaNombre.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    grupoNombre.toLowerCase().includes(searchQuery.toLowerCase())
                );
            });
        }

        // Filtro por docente
        if (filterDocente) {
            filtered = filtered.filter(carga => carga.codigo_doc == filterDocente);
        }

        setFilteredCargas(filtered);
        setCurrentPage(1);
    };

    const handleSubmit = async (formData) => {
        try {
            if (editingCarga) {
                await api.put(`/asignaciones/${editingCarga.id_asignacion}`, formData);
            } else {
                await api.post('/asignaciones', formData);
            }
            setShowForm(false);
            setEditingCarga(null);
            cargarCargasHorarias();
        } catch (err) {
            setError('Error al guardar asignación: ' + (err.response?.data?.message || err.message));
            console.error(err);
        }
    };

    const handleEdit = (carga) => {
        setEditingCarga(carga);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar esta asignación?')) {
            try {
                await api.delete(`/asignaciones/${id}`);
                cargarCargasHorarias();
            } catch (err) {
                setError('Error al eliminar asignación: ' + err.message);
                console.error(err);
            }
        }
    };

    const handleCloseForm = () => {
        setShowForm(false);
        setEditingCarga(null);
    };

    const getNombreDocente = (docente) => {
        if (!docente) return '';
        
        // Primero intentar el campo directo si está disponible
        if (docente.nombre_docente) {
            return docente.nombre_docente;
        }
        
        // Luego intentar la cadena de relaciones
        if (docente.usuario?.persona?.nombre_completo) {
            return docente.usuario.persona.nombre_completo;
        }
        
        // Si no hay nada, retornar fallback
        return `Docente ${docente.codigo_doc}`;
    };

    const formatearHorario = (horario) => {
        if (!horario) return 'N/A';
        
        // Convertir a string si es necesario
        let horaInicio = horario.hora_inicio ? String(horario.hora_inicio).substring(0, 5) : '';
        let horaFin = horario.hora_fin ? String(horario.hora_fin).substring(0, 5) : '';
        
        return horaInicio && horaFin ? `${horaInicio} - ${horaFin}` : 'N/A';
    };

    const totalPages = Math.ceil(filteredCargas.length / perPage);
    const startIndex = (currentPage - 1) * perPage;
    const paginatedCargas = filteredCargas.slice(startIndex, startIndex + perPage);

    if (loading) return <div className="loading">Cargando cargas horarias...</div>;

    return (
        <div className="carga-horaria-container">
            <div className="header">
                <h2>Asignación de Carga Horaria</h2>
                <button className="btn btn-primary" onClick={() => setShowForm(true)}>
                    <i className="fas fa-plus"></i> Nueva Asignación
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            <div className="filters">
                <input
                    type="text"
                    placeholder="Buscar por docente, materia o grupo..."
                    className="search-input"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                />
                <select
                    className="filter-select"
                    value={filterDocente}
                    onChange={(e) => setFilterDocente(e.target.value)}
                >
                    <option value="">Todos los docentes</option>
                    {docentes.map(doc => (
                        <option key={doc.codigo_doc} value={doc.codigo_doc}>
                            {getNombreDocente(doc)}
                        </option>
                    ))}
                </select>
            </div>

            <div className="table-responsive">
                <table className="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Docente</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                            <th>Aula</th>
                            <th>Horario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {paginatedCargas.length > 0 ? (
                            paginatedCargas.map(carga => (
                                <tr key={carga.id_asignacion}>
                                    <td>{carga.id_asignacion}</td>
                                    <td>{getNombreDocente(carga.docente)}</td>
                                    <td>{carga.grupo?.materia?.nombre_mat || 'N/A'}</td>
                                    <td>{carga.grupo?.codigo_grupo || 'N/A'}</td>
                                    <td>{carga.nro_aula}</td>
                                    <td>{formatearHorario(carga.horario)}</td>
                                    <td>
                                        <button 
                                            className="btn btn-sm btn-warning" 
                                            onClick={() => handleEdit(carga)}
                                        >
                                            <i className="fas fa-edit"></i> Editar
                                        </button>
                                        <button 
                                            className="btn btn-sm btn-danger" 
                                            onClick={() => handleDelete(carga.id_asignacion)}
                                        >
                                            <i className="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="7" className="text-center">No hay asignaciones de carga horaria</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {totalPages > 1 && (
                <div className="pagination">
                    <button 
                        disabled={currentPage === 1} 
                        onClick={() => setCurrentPage(currentPage - 1)}
                    >
                        Anterior
                    </button>
                    <span>Página {currentPage} de {totalPages}</span>
                    <button 
                        disabled={currentPage === totalPages} 
                        onClick={() => setCurrentPage(currentPage + 1)}
                    >
                        Siguiente
                    </button>
                </div>
            )}

            {showForm && (
                <AsignacionForm 
                    cargaHoraria={editingCarga}
                    onSubmit={handleSubmit}
                    onClose={handleCloseForm}
                />
            )}
        </div>
    );
};

export default CargaHoraria;
