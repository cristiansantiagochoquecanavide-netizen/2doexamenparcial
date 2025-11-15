import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Infraestructura() {
    const [infraestructuras, setInfraestructuras] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        nombre_infr: '',
        ubicacion: '',
        estado: '',
    });

    useEffect(() => {
        fetchInfraestructura();
    }, []);

    const fetchInfraestructura = async () => {
        try {
            setLoading(true);
            const response = await api.get('/infraestructura');
            console.log('Response:', response);
            // Extraer datos de la respuesta paginada
            const data = response.data.data || response.data || [];
            console.log('Extracted data:', data);
            setInfraestructuras(data);
            setError(null);
        } catch (err) {
            console.error('Full error:', err);
            console.error('Error response:', err.response);
            setError('Error al cargar las infraestructuras');
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
                await api.put(`/infraestructura/${editingId}`, formData);
                setError(null);
            } else {
                await api.post('/infraestructura', formData);
                setError(null);
            }
            setFormData({ nombre_infr: '', ubicacion: '', estado: '' });
            setEditingId(null);
            setShowForm(false);
            fetchInfraestructura();
        } catch (err) {
            let msg = 'Error al guardar la infraestructura';
            if (err.response && err.response.data && err.response.data.error) {
                msg += ': ' + err.response.data.error;
            } else if (err.message) {
                msg += ': ' + err.message;
            }
            setError(msg);
            console.error('Error:', err);
        }
    };

    const handleEdit = (infra) => {
        setFormData({
            nombre_infr: infra.nombre_infr,
            ubicacion: infra.ubicacion || '',
            estado: infra.estado || '',
        });
        setEditingId(infra.id_infraestructura);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar esta infraestructura?')) {
            try {
                await api.delete(`/infraestructura/${id}`);
                setError(null);
                fetchInfraestructura();
            } catch (err) {
                setError('Error al eliminar la infraestructura');
                console.error('Error:', err);
            }
        }
    };

    const handleCancel = () => {
        setFormData({ nombre_infr: '', ubicacion: '', estado: '' });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando infraestructuras...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Infraestructura</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nueva Infraestructura'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="nombre_infr">Nombre *</label>
                        <input
                            type="text"
                            id="nombre_infr"
                            name="nombre_infr"
                            value={formData.nombre_infr}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: Edificio A, Pabellón B"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="ubicacion">Ubicación</label>
                        <input
                            type="text"
                            id="ubicacion"
                            name="ubicacion"
                            value={formData.ubicacion}
                            onChange={handleInputChange}
                            placeholder="Ej: Planta 1, Zona Sur"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="estado">Estado</label>
                        <input
                            type="text"
                            id="estado"
                            name="estado"
                            value={formData.estado}
                            onChange={handleInputChange}
                            placeholder="Ej: Activo, Disponible"
                        />
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Infraestructura
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
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {infraestructuras.length > 0 ? (
                            infraestructuras.map((infra) => (
                                <tr key={infra.id_infraestructura}>
                                    <td>{infra.id_infraestructura}</td>
                                    <td>{infra.nombre_infr}</td>
                                    <td>{infra.ubicacion || '-'}</td>
                                    <td>{infra.estado || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(infra)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(infra.id_infraestructura)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="5" className="text-center">
                                    No hay infraestructuras registradas
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Infraestructura;
