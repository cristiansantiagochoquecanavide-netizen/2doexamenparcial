import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import api from '../utils/api';
import './RegistroAsistencia.css';

const RegistroAsistencia = () => {
  const { token } = useParams();
  const [sesion, setSesion] = useState(null);
  const [asignacion, setAsignacion] = useState(null);
  const [tiempoInfo, setTiempoInfo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [formData, setFormData] = useState({
    token: token,
    id_asignacion: '',
    observaciones: ''
  });

  useEffect(() => {
    cargarSesion();
  }, [token]);

  const cargarSesion = async () => {
    try {
      setLoading(true);
      const { data } = await api.get(`/asistencia/registro/${token}`);
      
      if (data.success) {
        setSesion(data.data.sesion);
        setAsignacion(data.data.asignacion);
        setTiempoInfo(data.data.tiempo);
        setFormData(prev => ({
          ...prev,
          id_asignacion: data.data.asignacion.id_asignacion
        }));
      }
    } catch (err) {
      if (err.response?.status === 404) {
        setError('Sesión de asistencia no encontrada');
      } else if (err.response?.status === 410) {
        setError('La sesión de asistencia ha expirado');
      } else {
        setError('Error al cargar la sesión');
      }
      console.error('Error:', err);
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
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validar si el QR está expirado (más de 25 minutos)
    if (!tiempoInfo?.permitido) {
      setError('⏰ El tiempo para registrar la asistencia ha expirado. El QR ya no es válido.');
      return;
    }

    try {
      setSubmitting(true);
      const { data } = await api.post('/asistencia/registrar', formData);
      
      if (data.success) {
        setSuccess(true);
        setError('');
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      }
    } catch (err) {
      const errorMsg = err.response?.data?.message || 'Error al registrar la asistencia';
      setError(errorMsg);
      console.error('Error:', err);
    } finally {
      setSubmitting(false);
    }
  };

  // Determinar el color del estado según tiempo transcurrido
  const getStatusColor = () => {
    if (!tiempoInfo) return '#666';
    if (tiempoInfo.minutos_transcurridos <= 15) return '#10b981'; // Verde
    if (tiempoInfo.minutos_transcurridos <= 25) return '#f59e0b'; // Naranja
    return '#ef4444'; // Rojo
  };

  // Determinar el ícono del estado
  const getStatusIcon = () => {
    if (!tiempoInfo) return '⏱️';
    if (tiempoInfo.minutos_transcurridos <= 15) return '✅'; // Presente
    if (tiempoInfo.minutos_transcurridos <= 25) return '⚠️'; // Retraso
    return '❌'; // Falta
  };

  if (loading) {
    return (
      <div className="registro-asistencia">
        <div className="loading">
          <div className="spinner"></div>
          <p>Cargando sesión de asistencia...</p>
        </div>
      </div>
    );
  }

  if (error && !success) {
    return (
      <div className="registro-asistencia">
        <div className="error-container">
          <h2>❌ Error</h2>
          <p>{error}</p>
          <button onClick={() => window.history.back()}>Volver</button>
        </div>
      </div>
    );
  }

  if (success) {
    return (
      <div className="registro-asistencia">
        <div className="success-container">
          <h2>✅ Asistencia Registrada</h2>
          <p>Tu asistencia ha sido registrada exitosamente.</p>
          <div className="success-details">
            <p><strong>Docente:</strong> {asignacion?.docente}</p>
            <p><strong>Materia:</strong> {asignacion?.materia}</p>
            <p><strong>Grupo:</strong> {asignacion?.grupo}</p>
            <p><strong>Aula:</strong> {asignacion?.aula}</p>
            <p><strong>Estado Registrado:</strong> {tiempoInfo?.estado_temporal === 'ASISTIO' ? '✅ Presente' : tiempoInfo?.estado_temporal === 'RETRASO' ? '⚠️ Retraso' : '❌ Falta'}</p>
          </div>
          <p className="redirect-message">Redirigiendo en 3 segundos...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="registro-asistencia">
      <div className="container">
        <div className="header">
          <h1>📋 Registro de Asistencia</h1>
          <p>Completa el formulario para registrar tu asistencia</p>
        </div>

        {/* Información de Estado Temporal */}
        <div className="estado-temporal" style={{ borderLeftColor: getStatusColor() }}>
          <div className="estado-header">
            <span className="estado-icon">{getStatusIcon()}</span>
            <span className="estado-title">Estado Temporal</span>
          </div>
          <div className="estado-details">
            <p className="minutos">
              <strong>Minutos transcurridos:</strong> {tiempoInfo?.minutos_transcurridos}
            </p>
            <p className="rango" style={{ color: getStatusColor() }}>
              <strong>Rango:</strong> {tiempoInfo?.rango}
            </p>
            <p className="mensaje">
              <strong>Mensaje:</strong> {tiempoInfo?.mensaje}
            </p>
            {!tiempoInfo?.permitido && (
              <div className="alerta-expiracion">
                ⏰ <strong>ADVERTENCIA:</strong> El tiempo para registrar la asistencia ha expirado. El QR ya no es válido.
              </div>
            )}
          </div>
        </div>

        <div className="info-card">
          <h3>ℹ️ Información de la Sesión</h3>
          <div className="info-grid">
            <div className="info-item">
              <span className="label">Docente:</span>
              <span className="value">{asignacion?.docente}</span>
            </div>
            <div className="info-item">
              <span className="label">Materia:</span>
              <span className="value">{asignacion?.materia}</span>
            </div>
            <div className="info-item">
              <span className="label">Grupo:</span>
              <span className="value">{asignacion?.grupo}</span>
            </div>
            <div className="info-item">
              <span className="label">Aula:</span>
              <span className="value">{asignacion?.aula}</span>
            </div>
            <div className="info-item">
              <span className="label">Hora de Inicio:</span>
              <span className="value">{asignacion?.hora_inicio}</span>
            </div>
            <div className="info-item">
              <span className="label">Tiempo Restante de Sesión:</span>
              <span className="value">{sesion?.tiempo_restante} min</span>
            </div>
          </div>
        </div>

        {!tiempoInfo?.permitido ? (
          <div className="form-disabled">
            <h3>❌ No se puede registrar</h3>
            <p>El tiempo para registrar la asistencia ha expirado. No puedes registrar tu asistencia en este momento.</p>
            <button onClick={() => window.history.back()} className="btn btn-secondary">
              Volver
            </button>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="form">
            <div className="form-group">
              <label>Estado de Asistencia *</label>
              <div className="estado-info">
                <strong>Estado calculado automáticamente:</strong> {getStatusIcon()} 
                {tiempoInfo?.estado_temporal === 'ASISTIO' ? ' PRESENTE (0-15 min)' : 
                 tiempoInfo?.estado_temporal === 'RETRASO' ? ' RETRASO (16-25 min)' : 
                 ' FALTA (Más de 25 min)'}
              </div>
              <input
                type="hidden"
                name="estado"
                value={tiempoInfo?.estado_temporal}
              />
            </div>

            <div className="form-group">
              <label htmlFor="observaciones">Observaciones (Opcional)</label>
              <textarea
                id="observaciones"
                name="observaciones"
                value={formData.observaciones}
                onChange={handleChange}
                placeholder="Ingresa cualquier observación relevante..."
                className="form-control"
                rows="4"
              />
            </div>

            <button 
              type="submit" 
              className="btn btn-primary"
              disabled={submitting}
            >
              {submitting ? 'Registrando...' : '✅ Registrar Asistencia'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
};

export default RegistroAsistencia;
