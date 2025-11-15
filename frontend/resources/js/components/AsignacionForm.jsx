import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../styles/AsignacionForm.css';

const AsignacionForm = ({ cargaHoraria, onSubmit, onClose }) => {
    const [formData, setFormData] = useState({
        codigo_doc: '',
        codigo_grupo: '',
        nro_aula: '',
        id_horario: '',
        periodo_academico: '',
        estado: 'ACTIVO',
    });

    const [docentes, setDocentes] = useState([]);
    const [materias, setMaterias] = useState([]);
    const [grupos, setGrupos] = useState([]);
    const [aulas, setAulas] = useState([]);
    const [horarios, setHorarios] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        cargarDatos();
        if (cargaHoraria) {
            setFormData({
                codigo_doc: cargaHoraria.codigo_doc,
                codigo_grupo: cargaHoraria.codigo_grupo,
                nro_aula: cargaHoraria.nro_aula,
                id_horario: cargaHoraria.id_horario,
                periodo_academico: cargaHoraria.periodo_academico,
                estado: cargaHoraria.estado || 'ACTIVO',
            });
        }
    }, [cargaHoraria]);

    const cargarDatos = async () => {
        try {
            setLoading(true);

            const [docentesRes, gruposRes, aulasRes, horariosRes] = await Promise.all([
                api.get('/docentes'),
                api.get('/grupos'),
                api.get('/aulas'),
                api.get('/horarios'),
            ]);

            // Log de depuración para ver la respuesta real
            console.log('DEBUG - Respuesta API /docentes:', docentesRes);
            const docentes = Array.isArray(docentesRes.data.data) ? docentesRes.data.data : (Array.isArray(docentesRes.data) ? docentesRes.data : []);
            console.log('DEBUG - Docentes procesados:', docentes);
            setDocentes(docentes);

            console.log('Respuesta API /grupos:', gruposRes);
            const grupos = gruposRes.data.data || gruposRes.data || [];
            console.log('Grupos procesados:', grupos);
            setGrupos(grupos);

            console.log('Respuesta API /aulas:', aulasRes);
            const aulas = aulasRes.data.data || aulasRes.data || [];
            console.log('Aulas procesadas:', aulas);
            setAulas(aulas);

            console.log('Respuesta API /horarios:', horariosRes);
            const horarios = horariosRes.data.data || horariosRes.data || [];
            console.log('Horarios procesados:', horarios);
            setHorarios(horarios);
        } catch (error) {
            console.error('Error loading form data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        // Limpiar errores para este campo
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        // Validación básica
        if (!formData.codigo_doc || !formData.codigo_grupo || 
            !formData.nro_aula || !formData.id_horario || !formData.periodo_academico) {
            setErrors({ general: 'Todos los campos son requeridos' });
            return;
        }

        try {
            onSubmit(formData);
        } catch (error) {
            setErrors({ general: error.message });
        }
    };

    // Función para obtener el nombre completo del docente con su materia
    const getNombreDocenteConMateria = (docente) => {
        if (!docente) return 'Desconocido';
        
        // Obtener nombre completo
        let nombreCompleto = docente.usuario?.persona?.nombre_completo;
        
        if (!nombreCompleto) {
            nombreCompleto = docente.nombre_docente || `Docente ${docente.codigo_doc}`;
        }
        
        // Obtener materias asignadas
        let materias = [];
        if (docente.asignaciones && Array.isArray(docente.asignaciones)) {
            materias = docente.asignaciones
                .map(a => a.grupo?.materia?.nombre_mat)
                .filter(Boolean)
                .filter((v, i, a) => a.indexOf(v) === i); // Eliminar duplicados
        }
        
        // Formato: "Juan Pérez (Matemática, Física)"
        if (materias.length > 0) {
            return `${nombreCompleto} (${materias.join(', ')})`;
        }
        
        return nombreCompleto;
    };

    // Función para obtener el grupo con materia y capacidad
    const getGrupoConMateria = (grupo) => {
        if (!grupo) return 'Desconocido';
        
        const codigo = grupo.codigo_grupo || 'Desconocido';
        const materia = grupo.materia?.nombre_mat || grupo.nombre_mat || 'Sin materia';
        const capacidad = grupo.capacidad_de_grupo || 0;
        
        // Formato: "GR001 (Matemática - Capacidad: 30)"
        return `${codigo} (${materia} - Capacidad: ${capacidad})`;
    };

    if (loading) {
        return <div className="modal-overlay"><div className="modal-content"><p>Cargando...</p></div></div>;
    }

    return (
        <div className="modal-overlay" onClick={onClose}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                <div className="modal-header">
                    <h2>{cargaHoraria ? 'Editar Asignación' : 'Nueva Asignación de Carga Horaria'}</h2>
                    <button className="close-btn" onClick={onClose}>&times;</button>
                </div>

                <form onSubmit={handleSubmit} className="form-group">
                    {errors.general && <div className="alert alert-danger">{errors.general}</div>}

                    {/* Depuración visual: mostrar cantidad de docentes */}
                    <div style={{ marginBottom: 8, color: '#888', fontSize: 13 }}>
                        Docentes cargados: {docentes.length}
                    </div>
                    {/* Mensaje si no hay docentes */}
                    {docentes.length === 0 && (
                        <div className="alert alert-warning" style={{ marginBottom: 12 }}>
                            No hay docentes disponibles para seleccionar. Verifique permisos o datos cargados.
                        </div>
                    )}

                    <div className="form-row">
                        <div className="form-col">
                            <label>Docente *</label>
                            <select
                                name="codigo_doc"
                                value={formData.codigo_doc}
                                onChange={handleChange}
                                className={`form-control ${errors.codigo_doc ? 'error' : ''}`}
                            >
                                <option value="">Seleccionar docente</option>
                                {docentes.map(doc => (
                                    <option key={doc.codigo_doc} value={doc.codigo_doc}>
                                        {getNombreDocenteConMateria(doc)}
                                    </option>
                                ))}
                            </select>
                            {errors.codigo_doc && <span className="error-message">{errors.codigo_doc}</span>}
                        </div>

                        <div className="form-col">
                            <label>Período Académico *</label>
                            <input
                                type="text"
                                name="periodo_academico"
                                value={formData.periodo_academico}
                                onChange={handleChange}
                                placeholder="Ej: 2024-1"
                                className={`form-control ${errors.periodo_academico ? 'error' : ''}`}
                            />
                            {errors.periodo_academico && <span className="error-message">{errors.periodo_academico}</span>}
                        </div>
                    </div>

                    <div className="form-row">
                        <div className="form-col">
                            <label>Grupo *</label>
                            <select
                                name="codigo_grupo"
                                value={formData.codigo_grupo}
                                onChange={handleChange}
                                className={`form-control ${errors.codigo_grupo ? 'error' : ''}`}
                            >
                                <option value="">Seleccionar grupo</option>
                                {grupos.map(grp => (
                                    <option key={grp.codigo_grupo} value={grp.codigo_grupo}>
                                        {getGrupoConMateria(grp)}
                                    </option>
                                ))}
                            </select>
                            {errors.codigo_grupo && <span className="error-message">{errors.codigo_grupo}</span>}
                        </div>

                        <div className="form-col">
                            <label>Aula *</label>
                            <select
                                name="nro_aula"
                                value={formData.nro_aula}
                                onChange={handleChange}
                                className={`form-control ${errors.nro_aula ? 'error' : ''}`}
                            >
                                <option value="">Seleccionar aula</option>
                                {aulas.map(aula => (
                                    <option key={aula.nro_aula} value={aula.nro_aula}>
                                        {aula.nro_aula} (Capacidad: {aula.capacidad_aula})
                                    </option>
                                ))}
                            </select>
                            {errors.nro_aula && <span className="error-message">{errors.nro_aula}</span>}
                        </div>
                    </div>

                    <div className="form-row">
                        <div className="form-col">
                            <label>Horario *</label>
                            <select
                                name="id_horario"
                                value={formData.id_horario}
                                onChange={handleChange}
                                className={`form-control ${errors.id_horario ? 'error' : ''}`}
                            >
                                <option value="">Seleccionar horario</option>
                                {horarios.map(hor => (
                                    <option key={hor.id_horario} value={hor.id_horario}>
                                        {hor.dias_semana || hor.dia} - {hor.hora_inicio} a {hor.hora_fin}
                                    </option>
                                ))}
                            </select>
                            {errors.id_horario && <span className="error-message">{errors.id_horario}</span>}
                        </div>

                        <div className="form-col">
                            <label>Estado *</label>
                            <select
                                name="estado"
                                value={formData.estado}
                                onChange={handleChange}
                                className="form-control"
                            >
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button type="submit" className="btn btn-success">
                            <i className="fas fa-save"></i> Guardar
                        </button>
                        <button type="button" className="btn btn-secondary" onClick={onClose}>
                            <i className="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default AsignacionForm;
