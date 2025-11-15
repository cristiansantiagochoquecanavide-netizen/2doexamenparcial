import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Grupos() {
    const [grupos, setGrupos] = useState([]);
    const [materias, setMaterias] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        codigo_grupo: '',
        codigo_mat: '',
        capacidad_de_grupo: '',
    });

    useEffect(() => {
        fetchGruposAndMaterias();
    }, []);

    const fetchGruposAndMaterias = async () => {
        try {
            setLoading(true);
            const [gruposRes, materiasRes] = await Promise.all([
                api.get('/grupos'),
                api.get('/materias'),
            ]);
            // Extraer datos de respuestas paginadas
            setGrupos(gruposRes.data.data || gruposRes.data || []);
            setMaterias(materiasRes.data.data || materiasRes.data || []);
            setError(null);
        } catch (err) {
            setError('Error al cargar los datos');
            console.error('Error:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await api.put(`/grupos/${editingId}`, formData);
                setError(null);
            } else {
                await api.post('/grupos', formData);
                setError(null);
            }
            setFormData({
                codigo_grupo: '',
                codigo_mat: '',
                capacidad_de_grupo: '',
            });
            setEditingId(null);
            setShowForm(false);
            fetchGruposAndMaterias();
        } catch (err) {
            setError('Error al guardar el grupo');
            console.error('Error:', err);
        }
    };

    const handleEdit = (grupo) => {
        setFormData({
            codigo_grupo: grupo.codigo_grupo,
            codigo_mat: grupo.codigo_mat || '',
            capacidad_de_grupo: grupo.capacidad_de_grupo || '',
        });
        setEditingId(grupo.codigo_grupo);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar este grupo?')) {
            try {
                await api.delete(`/grupos/${id}`);
                setError(null);
                fetchGruposAndMaterias();
            } catch (err) {
                setError('Error al eliminar el grupo');
                console.error('Error:', err);
            }
        }
    };

    const handleCancel = () => {
        setFormData({
            codigo_grupo: '',
            codigo_mat: '',
            capacidad_de_grupo: '',
        });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando grupos...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Grupos</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nuevo Grupo'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="codigo_grupo">Código *</label>
                        <input
                            type="text"
                            id="codigo_grupo"
                            name="codigo_grupo"
                            value={formData.codigo_grupo}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: GRP-101A"
                            disabled={editingId ? true : false}
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="codigo_mat">Materia *</label>
                        <select
                            id="codigo_mat"
                            name="codigo_mat"
                            value={formData.codigo_mat}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="">Seleccione una materia</option>
                            {materias.map((materia) => (
                                <option key={materia.codigo_mat} value={materia.codigo_mat}>
                                    {materia.nombre_mat} ({materia.codigo_mat})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="capacidad_de_grupo">Cantidad de Estudiantes</label>
                        <input
                            type="number"
                            id="capacidad_de_grupo"
                            name="capacidad_de_grupo"
                            value={formData.capacidad_de_grupo}
                            onChange={handleInputChange}
                            min="0"
                            placeholder="Ej: 30"
                        />
                    </div>

                    {!editingId && (
                        <div className="form-group">
                            <label htmlFor="descripcion">Descripción</label>
                            <textarea
                                id="descripcion"
                                name="descripcion"
                                value={formData.descripcion || ''}
                                onChange={handleInputChange}
                                placeholder="Descripción del grupo"
                                rows="3"
                            />
                        </div>
                    )}

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Grupo
                        </button>
                        <button
                            type="button"
                            className="btn btn-secondary"
                            onClick={handleCancel}
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            )}

            <div className="table-responsive">
                <table className="crud-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Materia</th>
                            <th>Estudiantes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {grupos.length > 0 ? (
                            grupos.map((grupo) => (
                                <tr key={grupo.codigo_grupo}>
                                    <td>{grupo.codigo_grupo}</td>
                                    <td>{grupo.materia?.nombre_mat || grupo.codigo_mat || '-'}</td>
                                    <td>{grupo.capacidad_de_grupo || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(grupo)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(grupo.codigo_grupo)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="4" className="text-center">
                                    No hay grupos registrados
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Grupos;
