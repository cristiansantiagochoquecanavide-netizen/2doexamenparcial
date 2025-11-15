import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Roles() {
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        nombre: '',
        descripcion: '',
    });

    useEffect(() => {
        fetchRoles();
    }, []);

    const fetchRoles = async () => {
        try {
            setLoading(true);
            const response = await api.get('/roles');
            setRoles(response.data);
            setError(null);
        } catch (err) {
            setError('Error al cargar los roles');
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
                // Actualizar rol existente
                await api.put(`/roles/${editingId}`, formData);
                setError(null);
            } else {
                // Crear nuevo rol
                await api.post('/roles', formData);
                setError(null);
            }
            setFormData({ nombre: '', descripcion: '' });
            setEditingId(null);
            setShowForm(false);
            fetchRoles();
        } catch (err) {
            setError('Error al guardar el rol');
            console.error('Error:', err);
        }
    };

    const handleEdit = (rol) => {
        setFormData({
            nombre: rol.nombre,
            descripcion: rol.descripcion,
        });
        setEditingId(rol.id_rol);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar este rol?')) {
            try {
                await api.delete(`/roles/${id}`);
                setError(null);
                fetchRoles();
            } catch (err) {
                setError('Error al eliminar el rol');
                console.error('Error:', err);
            }
        }
    };

    const handleCancel = () => {
        setFormData({ nombre: '', descripcion: '' });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando roles...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Roles</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nuevo Rol'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="nombre">Nombre del Rol *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value={formData.nombre}
                            onChange={handleInputChange}
                            required
                            placeholder="Ej: Administrador, Docente, Estudiante"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="descripcion">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            value={formData.descripcion}
                            onChange={handleInputChange}
                            placeholder="Descripción del rol"
                            rows="4"
                        />
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Rol
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
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {roles.length > 0 ? (
                            roles.map((rol) => (
                                <tr key={rol.id_rol}>
                                    <td>{rol.id_rol}</td>
                                    <td>{rol.nombre}</td>
                                    <td>{rol.descripcion || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(rol)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(rol.id_rol)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="4" className="text-center">
                                    No hay roles registrados
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Roles;
