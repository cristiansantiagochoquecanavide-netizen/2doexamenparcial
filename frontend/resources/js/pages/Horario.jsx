import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

function Horario() {
    const [horarios, setHorarios] = useState([]);
    const [grupos, setGrupos] = useState([]);
    const [docentes, setDocentes] = useState([]);
    const [aulas, setAulas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        id_grupo: '',
        id_docente: '',
        id_aula: '',
        dia: '',
        hora_inicio: '',
        hora_fin: '',
        semana: '',
    });

    const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    useEffect(() => {
        fetchAllData();
    }, []);

    const fetchAllData = async () => {
        try {
            setLoading(true);
            const [horariosRes, gruposRes, docentesRes, aulasRes] = await Promise.all([
                api.get('/horarios'),
                api.get('/grupos'),
                api.get('/docentes'),
                api.get('/aulas'),
            ]);
            setHorarios(horariosRes.data);
            setGrupos(gruposRes.data);
            setDocentes(docentesRes.data);
            setAulas(aulasRes.data);
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
                await api.put(`/horarios/${editingId}`, formData);
                setError(null);
            } else {
                await api.post('/horarios', formData);
                setError(null);
            }
            resetForm();
            fetchAllData();
        } catch (err) {
            setError('Error al guardar el horario');
            console.error('Error:', err);
        }
    };

    const handleEdit = (horario) => {
        setFormData({
            id_grupo: horario.id_grupo,
            id_docente: horario.id_docente,
            id_aula: horario.id_aula,
            dia: horario.dia,
            hora_inicio: horario.hora_inicio,
            hora_fin: horario.hora_fin,
            semana: horario.semana,
        });
        setEditingId(horario.id_horario);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Está seguro de que desea eliminar este horario?')) {
            try {
                await api.delete(`/horarios/${id}`);
                setError(null);
                fetchAllData();
            } catch (err) {
                setError('Error al eliminar el horario');
                console.error('Error:', err);
            }
        }
    };

    const resetForm = () => {
        setFormData({
            id_grupo: '',
            id_docente: '',
            id_aula: '',
            dia: '',
            hora_inicio: '',
            hora_fin: '',
            semana: '',
        });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando horarios...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Gestión de Horarios</h1>
                <button
                    className="btn btn-primary"
                    onClick={() => setShowForm(!showForm)}
                >
                    {showForm ? 'Cancelar' : 'Nuevo Horario'}
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="id_grupo">Grupo *</label>
                        <select
                            id="id_grupo"
                            name="id_grupo"
                            value={formData.id_grupo}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="">Seleccione un grupo</option>
                            {grupos.map((grupo) => (
                                <option key={grupo.id_grupo} value={grupo.id_grupo}>
                                    {grupo.nombre} ({grupo.codigo})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="id_docente">Docente *</label>
                        <select
                            id="id_docente"
                            name="id_docente"
                            value={formData.id_docente}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="">Seleccione un docente</option>
                            {docentes.map((docente) => (
                                <option key={docente.id_docente} value={docente.id_docente}>
                                    {docente.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="id_aula">Aula *</label>
                        <select
                            id="id_aula"
                            name="id_aula"
                            value={formData.id_aula}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="">Seleccione un aula</option>
                            {aulas.map((aula) => (
                                <option key={aula.id_aula} value={aula.id_aula}>
                                    {aula.nombre} (Cap: {aula.capacidad})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="dia">Día *</label>
                        <select
                            id="dia"
                            name="dia"
                            value={formData.dia}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="">Seleccione un día</option>
                            {diasSemana.map((dia) => (
                                <option key={dia} value={dia}>
                                    {dia}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="hora_inicio">Hora de Inicio *</label>
                        <input
                            type="time"
                            id="hora_inicio"
                            name="hora_inicio"
                            value={formData.hora_inicio}
                            onChange={handleInputChange}
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="hora_fin">Hora de Fin *</label>
                        <input
                            type="time"
                            id="hora_fin"
                            name="hora_fin"
                            value={formData.hora_fin}
                            onChange={handleInputChange}
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="semana">Semana</label>
                        <input
                            type="number"
                            id="semana"
                            name="semana"
                            value={formData.semana}
                            onChange={handleInputChange}
                            min="1"
                            max="52"
                            placeholder="Ej: 1"
                        />
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Crear'} Horario
                        </button>
                        <button
                            type="button"
                            className="btn btn-secondary"
                            onClick={resetForm}
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
                            <th>Grupo</th>
                            <th>Docente</th>
                            <th>Aula</th>
                            <th>Día</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Semana</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {horarios.length > 0 ? (
                            horarios.map((horario) => (
                                <tr key={horario.id_horario}>
                                    <td>{horario.id_horario}</td>
                                    <td>{horario.grupo?.nombre || '-'}</td>
                                    <td>{horario.docente?.nombre || '-'}</td>
                                    <td>{horario.aula?.nombre || '-'}</td>
                                    <td>{horario.dia}</td>
                                    <td>{horario.hora_inicio}</td>
                                    <td>{horario.hora_fin}</td>
                                    <td>{horario.semana || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(horario)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(horario.id_horario)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="9" className="text-center">
                                    No hay horarios registrados
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Horario;
