import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Aulas() {
    const [aulas, setAulas] = useState([]);
    const [infraestructuras, setInfraestructuras] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        nro_aula: '',
        tipo: '',
        capacidad: '',
        id_infraestructura: '',
        estado: 'Disponible',
    });

    useEffect(() => {
        fetchAulasAndInfraestructura();
    }, []);

    const fetchAulasAndInfraestructura = async () => {
        try {
            setLoading(true);
            const [aulasRes, infraRes] = await Promise.all([
                api.get('/aulas'),
                api.get('/infraestructura'),
            ]);
            // Extraer datos de respuestas paginadas
            setAulas(aulasRes.data.data || aulasRes.data || []);
            setInfraestructuras(infraRes.data.data || infraRes.data || []);
            setError(null);
        } catch (err) {
            let msg = 'Error al cargar los datos';
            if (err.response) {
                msg += ` (status: ${err.response.status})`;
                if (err.response.data && err.response.data.message) {
                    msg += `: ${err.response.data.message}`;
                }
            } else if (err.message) {
                msg += `: ${err.message}`;
            }
            setError(msg);
            console.error('Error detalle:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData({
            ...formData,
            [name]: type === 'checkbox' ? checked : value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await api.put(`/aulas/${editingId}`, formData);
                setError(null);
            } else {
                await api.post('/aulas', formData);
                setError(null);
            }
            setFormData({
                nro_aula: '',
                tipo: '',
                capacidad: '',
                id_infraestructura: '',
                estado: 'Disponible',
            });
            setEditingId(null);
            setShowForm(false);
            fetchAulasAndInfraestructura();
        } catch (err) {
            setError('Error al guardar el aula');
            console.error('Error:', err);
        }
    };

    const handleEdit = (aula) => {
        setFormData({
            nro_aula: aula.nro_aula,
            tipo: aula.tipo || '',
            capacidad: aula.capacidad || '',
            id_infraestructura: aula.id_infraestructura || '',
            estado: aula.estado || 'Disponible',
        });
        setEditingId(aula.nro_aula);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar esta aula?')) {
            try {
                await api.delete(`/aulas/${id}`);
                setError(null);
                fetchAulasAndInfraestructura();
            } catch (err) {
                setError('Error al eliminar el aula');
                console.error('Error:', err);
            }
        }
    };

    const handleCancel = () => {
        setFormData({
            nro_aula: '',
            tipo: '',
            capacidad: '',
            id_infraestructura: '',
            estado: 'Disponible',
        });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando aulas...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Aulas</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nueva Aula'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="nro_aula">Número *</label>
                        <input
                            type="text"
                            id="nro_aula"
                            name="nro_aula"
                            value={formData.nro_aula}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: 101, A-201"
                            disabled={editingId ? true : false}
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="tipo">Tipo *</label>
                        <input
                            type="text"
                            id="tipo"
                            name="tipo"
                            value={formData.tipo}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: Aula de Informática, Laboratorio de Química"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="capacidad">Capacidad *</label>
                        <input
                            type="number"
                            id="capacidad"
                            name="capacidad"
                            value={formData.capacidad}
                            onChange={handleInputChange}
                            required
                            min="1"
                            placeholder="Ej: 40"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="id_infraestructura">Infraestructura</label>
                        <select
                            id="id_infraestructura"
                            name="id_infraestructura"
                            value={formData.id_infraestructura}
                            onChange={handleInputChange}
                        >
                            <option value="">Seleccione infraestructura</option>
                            {infraestructuras.map((infra) => (
                                <option key={infra.id_infraestructura} value={infra.id_infraestructura}>
                                    {infra.nombre_infr}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="estado">Estado</label>
                        <select
                            id="estado"
                            name="estado"
                            value={formData.estado}
                            onChange={handleInputChange}
                        >
                            <option value="Disponible">Disponible</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Ocupado">Ocupado</option>
                        </select>
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Aula
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
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Capacidad</th>
                            <th>Infraestructura</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {aulas.length > 0 ? (
                            aulas.map((aula) => (
                                <tr key={aula.nro_aula}>
                                    <td>{aula.nro_aula}</td>
                                    <td>{aula.tipo || '-'}</td>
                                    <td>{aula.capacidad}</td>
                                    <td>{aula.infraestructura?.nombre_infr || '-'}</td>
                                    <td>{aula.estado || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(aula)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(aula.nro_aula)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="6" className="text-center">
                                    No hay aulas registradas
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Aulas;
