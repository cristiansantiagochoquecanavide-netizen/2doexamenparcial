import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Materias() {
    const [materias, setMaterias] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        nombre_mat: '',
        codigo_mat: '',
        tipo: '',
        nivel: '',
        horas_semanales: '',
    });

    useEffect(() => {
        fetchMaterias();
    }, []);

    const fetchMaterias = async () => {
        try {
            setLoading(true);
            const response = await api.get('/materias');
            // Extraer los datos de la respuesta paginada
            setMaterias(response.data.data || response.data || []);
            setError(null);
        } catch (err) {
            setError('Error al cargar las materias');
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
                await api.put(`/materias/${editingId}`, formData);
                setError(null);
            } else {
                await api.post('/materias', formData);
                setError(null);
            }
            setFormData({ nombre_mat: '', codigo_mat: '', tipo: '', nivel: '', horas_semanales: '' });
            setEditingId(null);
            setShowForm(false);
            fetchMaterias();
        } catch (err) {
            setError('Error al guardar la materia');
            console.error('Error:', err);
        }
    };

    const handleEdit = (materia) => {
        setFormData({
            nombre_mat: materia.nombre_mat,
            codigo_mat: materia.codigo_mat,
            tipo: materia.tipo || '',
            nivel: materia.nivel || '',
            horas_semanales: materia.horas_semanales || '',
        });
        setEditingId(materia.codigo_mat);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar esta materia?')) {
            try {
                await api.delete(`/materias/${id}`);
                setError(null);
                fetchMaterias();
            } catch (err) {
                setError('Error al eliminar la materia');
                console.error('Error:', err);
            }
        }
    };

    const handleCancel = () => {
        setFormData({ nombre_mat: '', codigo_mat: '', tipo: '', nivel: '', horas_semanales: '' });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando materias...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Materias</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nueva Materia'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="nombre_mat">Nombre de la Materia *</label>
                        <input
                            type="text"
                            id="nombre_mat"
                            name="nombre_mat"
                            value={formData.nombre_mat}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: Matemática I, Física General"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="codigo_mat">Código *</label>
                        <input
                            type="text"
                            id="codigo_mat"
                            name="codigo_mat"
                            value={formData.codigo_mat}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: MAT-101"
                            disabled={editingId ? true : false}
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="tipo">Tipo</label>
                        <input
                            type="text"
                            id="tipo"
                            name="tipo"
                            value={formData.tipo}
                            onChange={handleInputChange}
                            placeholder="Ej: Obligatoria, Electiva"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="nivel">Nivel</label>
                        <input
                            type="number"
                            id="nivel"
                            name="nivel"
                            value={formData.nivel}
                            onChange={handleInputChange}
                            min="1"
                            placeholder="Ej: 1, 2, 3"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="horas_semanales">Horas Semanales</label>
                        <input
                            type="number"
                            id="horas_semanales"
                            name="horas_semanales"
                            value={formData.horas_semanales}
                            onChange={handleInputChange}
                            min="0"
                            placeholder="Ej: 4"
                        />
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Materia
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
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Horas Semanales</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {materias.length > 0 ? (
                            materias.map((materia) => (
                                <tr key={materia.codigo_mat}>
                                    <td>{materia.codigo_mat}</td>
                                    <td>{materia.nombre_mat}</td>
                                    <td>{materia.tipo || '-'}</td>
                                    <td>{materia.nivel || '-'}</td>
                                    <td>{materia.horas_semanales || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(materia)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(materia.codigo_mat)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="6" className="text-center">
                                    No hay materias registradas
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Materias;
