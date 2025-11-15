import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import '../../css/CRUD.css';

const ConfiguracionMalla = () => {
    const [turnos, setTurnos] = useState([
        { nombre: 'Mañana', hora_inicio: '06:00', hora_fin: '12:00', duracion_bloque_minutos: 60 },
        { nombre: 'Tarde', hora_inicio: '13:00', hora_fin: '19:00', duracion_bloque_minutos: 60 },
    ]);
    const [diasSemana, setDiasSemana] = useState([1, 2, 3, 4, 5]); // Lunes a Viernes
    const [franjas, setFranjas] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [validationErrors, setValidationErrors] = useState([]);
    const [mostrarFormulario, setMostrarFormulario] = useState(true);

    const diasNombres = {
        1: 'Lunes',
        2: 'Martes',
        3: 'Miércoles',
        4: 'Jueves',
        5: 'Viernes',
        6: 'Sábado',
        7: 'Domingo',
    };

    // Cargar franjas existentes al iniciar
    useEffect(() => {
        console.log('ConfiguracionMalla montado, cargando franjas...');
        cargarFranjas();
    }, []);

    const cargarFranjas = async () => {
        try {
            setLoading(true);
            const response = await api.get('/malla-horaria');
            
            const data = response.data.data || response.data || [];
            setFranjas(data);
            setError(null);
        } catch (err) {
            console.error('Error al cargar franjas:', err.response?.data || err.message);
            setError(`Error al cargar las franjas horarias: ${err.response?.data?.error || err.response?.data?.message || err.message}`);
        } finally {
            setLoading(false);
        }
    };

    const handleChangeTurno = (index, field, value) => {
        const nuevosTurnos = [...turnos];
        nuevosTurnos[index][field] = value;
        setTurnos(nuevosTurnos);
    };

    const agregarTurno = () => {
        setTurnos([
            ...turnos,
            { nombre: '', hora_inicio: '06:00', hora_fin: '12:00', duracion_bloque_minutos: 60 }
        ]);
    };

    const eliminarTurno = (index) => {
        setTurnos(turnos.filter((_, i) => i !== index));
    };

    const toggleDia = (dia) => {
        if (diasSemana.includes(dia)) {
            setDiasSemana(diasSemana.filter(d => d !== dia));
        } else {
            setDiasSemana([...diasSemana, dia].sort());
        }
    };

    const generarMalla = async () => {
        try {
            setLoading(true);
            setError(null);
            setValidationErrors([]);

            // Validar que todos los turnos tengan nombre
            const turnosSinNombre = turnos.filter(t => !t.nombre || t.nombre.trim() === '');
            if (turnosSinNombre.length > 0) {
                setError('Todos los turnos deben tener un nombre');
                setLoading(false);
                return;
            }

            // Validar que haya al menos un día seleccionado
            if (diasSemana.length === 0) {
                setError('Debe seleccionar al menos un día de la semana');
                setLoading(false);
                return;
            }

            const response = await api.post(
                '/malla-horaria',
                {
                    turnos: turnos,
                    dias_semana: diasSemana,
                }
            );

            setSuccess(`✓ Malla horaria generada exitosamente: ${response.data.franjas_creadas} franjas creadas`);
            setMostrarFormulario(false);
            
            // Recargar franjas después de generar
            setTimeout(() => {
                cargarFranjas();
            }, 1000);

        } catch (err) {
            console.error('Error al generar malla:', err);
            setError(err.response?.data?.error || 'Error al generar la malla horaria');
        } finally {
            setLoading(false);
        }
    };

    const validarFranjas = async () => {
        try {
            setLoading(true);
            const response = await api.post(
                '/malla-horaria/validar',
                {}
            );

            if (response.data.valido) {
                setSuccess(`✓ Validación exitosa: ${response.data.total_franjas} franjas sin solapamientos`);
                setValidationErrors([]);
            } else {
                setValidationErrors(response.data.errores);
                setError('Se encontraron solapamientos en la malla horaria');
            }
        } catch (err) {
            console.error('Error al validar franjas:', err);
            setError('Error al validar las franjas');
        } finally {
            setLoading(false);
        }
    };

    const eliminarMalla = async () => {
        if (!window.confirm('¿Está seguro de que desea eliminar toda la malla horaria?')) {
            return;
        }

        try {
            setLoading(true);
            await api.delete('/malla-horaria-eliminar');

            setSuccess('✓ Malla horaria eliminada exitosamente');
            setFranjas([]);
            setMostrarFormulario(true);
            setError(null);
        } catch (err) {
            console.error('Error al eliminar malla:', err);
            setError('Error al eliminar la malla horaria');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="container">
            <h1>CU10: Configurar Malla Horaria</h1>
            
            {/* Mensajes de estado */}
            {error && <div className="alert alert-error">{error}</div>}
            {success && <div className="alert alert-success">{success}</div>}
            {validationErrors.length > 0 && (
                <div className="alert alert-warning">
                    <strong>Errores de validación:</strong>
                    <ul>
                        {validationErrors.map((err, i) => (
                            <li key={i}>{err}</li>
                        ))}
                    </ul>
                </div>
            )}

            {/* Sección: Generar Malla */}
            {mostrarFormulario && franjas.length === 0 && (
                <div className="form-section">
                    <h2>Generar Nueva Malla Horaria</h2>

                    {/* Turnos */}
                    <div className="form-group">
                        <h3>Turnos</h3>
                        {turnos.map((turno, index) => (
                            <div key={index} className="turno-row" style={{ marginBottom: '20px', padding: '10px', border: '1px solid #ddd', borderRadius: '5px' }}>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: '10px' }}>
                                    <input
                                        type="text"
                                        placeholder="Nombre del turno"
                                        value={turno.nombre}
                                        onChange={(e) => handleChangeTurno(index, 'nombre', e.target.value)}
                                        style={{ padding: '8px' }}
                                    />
                                    <input
                                        type="time"
                                        value={turno.hora_inicio}
                                        onChange={(e) => handleChangeTurno(index, 'hora_inicio', e.target.value)}
                                        style={{ padding: '8px' }}
                                    />
                                    <input
                                        type="time"
                                        value={turno.hora_fin}
                                        onChange={(e) => handleChangeTurno(index, 'hora_fin', e.target.value)}
                                        style={{ padding: '8px' }}
                                    />
                                    <input
                                        type="number"
                                        placeholder="Duración (min)"
                                        value={turno.duracion_bloque_minutos}
                                        onChange={(e) => handleChangeTurno(index, 'duracion_bloque_minutos', parseInt(e.target.value))}
                                        style={{ padding: '8px' }}
                                    />
                                </div>
                                {turnos.length > 1 && (
                                    <button
                                        onClick={() => eliminarTurno(index)}
                                        style={{
                                            marginTop: '10px',
                                            padding: '5px 10px',
                                            backgroundColor: '#dc3545',
                                            color: 'white',
                                            border: 'none',
                                            borderRadius: '4px',
                                            cursor: 'pointer',
                                        }}
                                    >
                                        Eliminar
                                    </button>
                                )}
                            </div>
                        ))}
                        <button
                            onClick={agregarTurno}
                            style={{
                                padding: '8px 15px',
                                backgroundColor: '#007bff',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer',
                                marginBottom: '15px',
                            }}
                        >
                            + Agregar Turno
                        </button>
                    </div>

                    {/* Días de la Semana */}
                    <div className="form-group">
                        <h3>Días de la Semana</h3>
                        <div style={{ display: 'flex', gap: '15px', flexWrap: 'wrap' }}>
                            {Object.entries(diasNombres).map(([numero, nombre]) => (
                                <label key={numero} style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                                    <input
                                        type="checkbox"
                                        checked={diasSemana.includes(parseInt(numero))}
                                        onChange={() => toggleDia(parseInt(numero))}
                                    />
                                    {nombre}
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* Botones de Acción */}
                    <div style={{ display: 'flex', gap: '10px', marginTop: '20px' }}>
                        <button
                            onClick={generarMalla}
                            disabled={loading}
                            style={{
                                padding: '10px 20px',
                                backgroundColor: '#28a745',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: loading ? 'not-allowed' : 'pointer',
                                opacity: loading ? 0.6 : 1,
                            }}
                        >
                            {loading ? 'Generando...' : 'Generar Malla'}
                        </button>
                    </div>
                </div>
            )}

            {/* Sección: Ver Franjas */}
            {franjas.length > 0 && (
                <div className="form-section" style={{ marginTop: '30px' }}>
                    <h2>Malla Horaria Actual</h2>
                    
                    <div style={{ marginBottom: '20px' }}>
                        <strong>Total de franjas: {franjas.length}</strong>
                    </div>

                    {/* Agrupar franjas por turno y día */}
                    {Object.entries(
                        franjas.reduce((acc, franja) => {
                            const key = `${franja.turno}_${franja.dias_semana}`;
                            if (!acc[key]) acc[key] = [];
                            acc[key].push(franja);
                            return acc;
                        }, {})
                    ).sort().map(([key, franjasDia]) => (
                        <div key={key} style={{ marginBottom: '20px', padding: '10px', backgroundColor: '#f8f9fa', borderRadius: '5px' }}>
                            <h4>{franjasDia[0].turno} - {diasNombres[franjasDia[0].dias_semana]}</h4>
                            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                <thead>
                                    <tr style={{ backgroundColor: '#e9ecef' }}>
                                        <th style={{ padding: '8px', border: '1px solid #dee2e6', textAlign: 'left' }}>Inicio</th>
                                        <th style={{ padding: '8px', border: '1px solid #dee2e6', textAlign: 'left' }}>Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {franjasDia.map((franja, i) => (
                                        <tr key={i} style={{ backgroundColor: i % 2 === 0 ? '#fff' : '#f8f9fa' }}>
                                            <td style={{ padding: '8px', border: '1px solid #dee2e6' }}>{franja.hora_inicio}</td>
                                            <td style={{ padding: '8px', border: '1px solid #dee2e6' }}>{franja.hora_fin}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ))}

                    {/* Botones de Acción */}
                    <div style={{ display: 'flex', gap: '10px', marginTop: '20px' }}>
                        <button
                            onClick={validarFranjas}
                            disabled={loading}
                            style={{
                                padding: '10px 20px',
                                backgroundColor: '#007bff',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: loading ? 'not-allowed' : 'pointer',
                                opacity: loading ? 0.6 : 1,
                            }}
                        >
                            {loading ? 'Validando...' : 'Validar Franjas'}
                        </button>
                        <button
                            onClick={() => {
                                setMostrarFormulario(true);
                                setError(null);
                                setSuccess(null);
                            }}
                            style={{
                                padding: '10px 20px',
                                backgroundColor: '#17a2b8',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer',
                            }}
                        >
                            Regenerar Malla
                        </button>
                        <button
                            onClick={eliminarMalla}
                            disabled={loading}
                            style={{
                                padding: '10px 20px',
                                backgroundColor: '#dc3545',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: loading ? 'not-allowed' : 'pointer',
                                opacity: loading ? 0.6 : 1,
                            }}
                        >
                            {loading ? 'Eliminando...' : 'Eliminar Malla'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ConfiguracionMalla;
