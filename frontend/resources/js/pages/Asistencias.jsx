import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';
import GenerarQR from './GenerarQR';
import GestionInasistencias from './GestionInasistencias';

function Asistencias() {
    const [activeTab, setActiveTab] = useState('generar-qr'); // generar-qr | registrar
    const [asistencias, setAsistencias] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        docente_id: '',
        fecha: '',
        hora_entrada: '',
        hora_salida: '',
        estado: 'Presente',
        observaciones: ''
    });

    useEffect(() => {
        fetchAsistencias();
    }, []);

    const fetchAsistencias = async () => {
        try {
            setLoading(true);
            // Simular datos hasta que tengamos la API
            setAsistencias([
                { id: 1, docente: 'Dr. Juan García', fecha: '2025-11-08', hora_entrada: '08:00', hora_salida: '17:00', estado: 'Presente', observaciones: '' },
                { id: 2, docente: 'Dra. María López', fecha: '2025-11-08', hora_entrada: '08:30', hora_salida: '', estado: 'Tarde', observaciones: 'Llegó tarde 30 minutos' }
            ]);
            setError(null);
        } catch (err) {
            setError('Error al cargar las asistencias');
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
                // Actualizar asistencia existente
                setAsistencias(asistencias.map(a => a.id === editingId ? { ...a, ...formData } : a));
            } else {
                // Crear nueva asistencia
                const newAsistencia = {
                    id: Math.max(...asistencias.map(a => a.id || 0), 0) + 1,
                    ...formData
                };
                setAsistencias([...asistencias, newAsistencia]);
            }
            setError(null);
            handleCancel();
            alert(editingId ? 'Asistencia actualizada exitosamente' : 'Asistencia registrada exitosamente');
        } catch (err) {
            setError('Error al guardar la asistencia');
            console.error('Error:', err);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) return;
        try {
            setAsistencias(asistencias.filter(a => a.id !== id));
            alert('Asistencia eliminada exitosamente');
        } catch (err) {
            setError('Error al eliminar la asistencia');
            console.error('Error:', err);
        }
    };

    const handleEdit = (asistencia) => {
        setEditingId(asistencia.id);
        setFormData({
            docente_id: asistencia.docente || '',
            fecha: asistencia.fecha || '',
            hora_entrada: asistencia.hora_entrada || '',
            hora_salida: asistencia.hora_salida || '',
            estado: asistencia.estado || 'Presente',
            observaciones: asistencia.observaciones || ''
        });
        setShowForm(true);
    };

    const handleCancel = () => {
        setFormData({
            docente_id: '',
            fecha: '',
            hora_entrada: '',
            hora_salida: '',
            estado: 'Presente',
            observaciones: ''
        });
        setEditingId(null);
        setShowForm(false);
    };

    if (loading) {
        return <div className="loading">Cargando asistencias...</div>;
    }

    return (
        <div className="crud-container">
            <div className="crud-header">
                <h1>Asistencia Docente</h1>
                <p className="subtitle">Gestiona asistencias, genera QR y registra acceso</p>
            </div>

            {/* Pestañas de Navegación - Solo 2 opciones modernas */}
            <div className="tabs-container-modern">
                <button
                    className={`tab-btn-modern ${activeTab === 'generar-qr' ? 'active' : ''}`}
                    onClick={() => setActiveTab('generar-qr')}
                >
                    <span className="tab-text">Generar QR</span>
                </button>
                <button
                    className={`tab-btn-modern ${activeTab === 'registrar' ? 'active' : ''}`}
                    onClick={() => setActiveTab('registrar')}
                >
                    <span className="tab-text">Registrar Asistencia</span>
                </button>
            </div>

            {error && <div className="alert alert-danger">{error}</div>}

            {/* Pestaña 1: Gestionar Asistencias - OCULTA */}
            {/* SECCIÓN COMENTADA - Para mantener la lógica pero sin mostrar en UI
            {activeTab === 'gestionar' && (
                <div className="tab-content">
                    <div className="section-header">
                        <button
                            className="btn btn-primary"
                            onClick={() => {
                                setShowForm(!showForm);
                                if (editingId) {
                                    handleCancel();
                                }
                            }}
                        >
                            {showForm ? 'Cancelar' : '+ Nueva Asistencia'}
                        </button>
                    </div>

            {showForm && (
                <form onSubmit={handleSubmit} className="crud-form">
                    <div className="form-group">
                        <label htmlFor="docente_id">Docente *</label>
                        <input
                            type="text"
                            id="docente_id"
                            name="docente_id"
                            value={formData.docente_id}
                            onChange={handleInputChange}
                            required
                            placeholder="Nombre del docente"
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="fecha">Fecha *</label>
                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value={formData.fecha}
                            onChange={handleInputChange}
                            required
                        />
                    </div>

                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="hora_entrada">Hora Entrada *</label>
                            <input
                                type="time"
                                id="hora_entrada"
                                name="hora_entrada"
                                value={formData.hora_entrada}
                                onChange={handleInputChange}
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label htmlFor="hora_salida">Hora Salida</label>
                            <input
                                type="time"
                                id="hora_salida"
                                name="hora_salida"
                                value={formData.hora_salida}
                                onChange={handleInputChange}
                            />
                        </div>
                    </div>

                    <div className="form-group">
                        <label htmlFor="estado">Estado *</label>
                        <select
                            id="estado"
                            name="estado"
                            value={formData.estado}
                            onChange={handleInputChange}
                            required
                        >
                            <option value="Presente">Presente</option>
                            <option value="Ausente">Ausente</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Justificado">Justificado</option>
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="observaciones">Observaciones</label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            value={formData.observaciones}
                            onChange={handleInputChange}
                            placeholder="Observaciones adicionales"
                            rows="3"
                        />
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            {editingId ? 'Actualizar' : 'Registrar'} Asistencia
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
                            <th>Docente</th>
                            <th>Fecha</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {asistencias.length > 0 ? (
                            asistencias.map((asistencia) => (
                                <tr key={asistencia.id}>
                                    <td>{asistencia.id}</td>
                                    <td>{asistencia.docente}</td>
                                    <td>{asistencia.fecha}</td>
                                    <td>{asistencia.hora_entrada}</td>
                                    <td>{asistencia.hora_salida || '-'}</td>
                                    <td>
                                        <span className={`badge badge-${asistencia.estado.toLowerCase()}`}>
                                            {asistencia.estado}
                                        </span>
                                    </td>
                                    <td>{asistencia.observaciones || '-'}</td>
                                    <td className="actions">
                                        <button
                                            className="btn btn-sm btn-warning"
                                            onClick={() => handleEdit(asistencia)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleDelete(asistencia.id)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="8" className="text-center">
                                    No hay registros de asistencia
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
                    </div>
                </div>
            )}
            */}

            {/* Pestaña 2: Generar QR */}
            {activeTab === 'generar-qr' && (
                <div className="tab-content">
                    <GenerarQR />
                </div>
            )}

            {/* Pestaña 3: Registrar Asistencia */}
            {activeTab === 'registrar' && (
                <div className="tab-content">
                    <div className="info-box">
                        <h3>📲 Registrar Asistencia por QR</h3>
                        <p>
                            Para registrar tu asistencia, necesitas escanear un código QR generado por un docente o coordinador.
                        </p>
                        <ol>
                            <li>Solicita al docente que genere un código QR</li>
                            <li>Escanea el código QR con tu dispositivo</li>
                            <li>Se abrirá automáticamente el formulario de registro</li>
                            <li>Completa los datos y confirma tu asistencia</li>
                        </ol>
                        <p className="highlight">
                            ℹ️ Si tienes un código QR, haz clic <a href="#" onClick={() => alert('Pide al docente el enlace del QR')}>aquí</a> para registrarte.
                        </p>
                    </div>
                </div>
            )}

            {/* Pestaña 3: Gestionar Inasistencias - OCULTA */}
            {/* SECCIÓN COMENTADA - Para mantener la lógica pero sin mostrar en UI
            {activeTab === 'inasistencias' && (
                <div className="tab-content">
                    <GestionInasistencias />
                </div>
            )}
            */}
        </div>
    );
}

export default Asistencias;
